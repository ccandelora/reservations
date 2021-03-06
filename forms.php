<?

function loginForm() {
	$args = array(
		'echo' => false,
		'label_username' => 'Uporabniško ime',
		'label_password' => 'Geslo',
		'label_remember' => 'Zapomni se me',
		'label_log_in' => 'Prijavi se',
		'value_remember' => true
	);
	$form = wp_login_form($args);
	echo substr_replace($form, '<p class="login_form_notice">Za rezerviranje morate biti prijavljeni!</p>', strpos($form, '<p'), 0);
}

function cancelForm() {
	$form = '<form id=cancel_form name="cancel_reservation" method="post">';
	$form .= '<p id=cancel_form_info></p>';
	$form .= '<p><input type="submit" value="Prekliči rezervacijo" name="submit" /><p>';
	$form .= '<p id="cancel_cancellation">Zapri okno</p>';
	$form .= '<input type="hidden" name="reservation_id"/>';
	$form .= wp_nonce_field('cancel_reservation', '_cancel', true, false);
	$form .= '</form>';
	echo $form;
}

function processCancellation() {
	$reservation = get_post($_POST['reservation_id']);
	if ($reservation) {
		global $current_user;
		$current_user = wp_get_current_user();
		if($current_user->ID == $reservation->post_author) {
			sendCancellationSMSes($reservation->ID);
			return wp_delete_post($reservation->ID);
		}
	}
	return false;
}

function getOpponentForm($name, $hide = false) {
	if ($hide) {
		$hide = ' style="display: none; "';
	}

	$opponents = getOpponents();
	$opponentForm = '<p><select name="'.$name.'"'.$hide.'>';
	foreach ($opponents as $key => $opponent) {
		$opponentForm .= '<option value='.$key.'>'.$opponent.'</option>';
	}
	$opponentForm .= '</select></p>';
	
	return $opponentForm;
}

function reservationForm() {
	$courts = get_terms('play_courts', array('hide_empty' => false));
	$courtsForm = '<select name="play_courts">';
	foreach ($courts as $court) {
		$courtsForm .= '<option value="'.$court->slug.'">'.$court->name.'</option>';
	}
	$courtsForm .= '</select></p>';
	
	$form = '<form id=reservation_form name="reservation" method="post">';
	$form .= '<p id="text_reservation_from"></p>';
	$form .= '<p><select name="length"><option value="50">30 min</option><option value="100" selected="selected">1 ura</option><option value="150">1 ura 30 min</option><option value="200">2 uri</option></select>';
	$form .= $courtsForm;
	$form .= getOpponentForm('opponent');
	$form .= getOpponentForm('opponent2', true);
	$form .= getOpponentForm('opponent3', true);
	$form .= '<p><input type="text" name="title" placeholder="Opomba"/></p>';
	$form .= '<input type="hidden" name="from" />';
	$form .= '<input type="hidden" name="date" />';
	$form .= '<p><input type="submit" value="Rezerviraj" name="submit" /><p>';
	$form .= '<p id="cancel_reservation">Prekliči</p>';
	$form .= wp_nonce_field('reservation', '_reservation', true, false);
	$form .= '</form>';
	echo $form;
}

function processReservation() {
	if (!is_user_logged_in() || reservationExists() || invalidReservationValues()) {
		return false;
	}
		
	$reservation = array(
		'post_title' => $_POST['title'],
		'post_status' => 'publish',
		'post_type' => 'reservation'
	);
	
	$post_id = wp_insert_post($reservation);
	
	if ($post_id) {
		$until = $_POST['from'] + $_POST['length'];
		add_post_meta($post_id, 'reservations_date_field_id', $_POST['date'], true);
		add_post_meta($post_id, 'reservations_time_from_field_id', getHumanReadableHour($_POST['from']), true);
		add_post_meta($post_id, 'reservations_time_until_field_id', getHumanReadableHour($until), true);
		add_post_meta($post_id, 'reservations_opponent_field_id', $_POST['opponent'], true);
		add_post_meta($post_id, 'reservations_opponent_field_id2', $_POST['opponent2'], true);
		add_post_meta($post_id, 'reservations_opponent_field_id3', $_POST['opponent3'], true);
		wp_set_post_terms($post_id, reset(get_terms('play_courts', array('hide_empty' => false, 'slug' => $_POST['play_courts'])))->name, 'play_courts');
		
		sendReservationSMSes($post_id);
		return true;
	}
	
	return false;
}

function reservationExists() {
	$return = false;
	$fromReq = $_POST['from'];
	$untilReq = $fromReq + $_POST['length'];
	
	$args = array(
		'post_type' => 'reservation',
		'nopaging' => true,
		'tax_query' => array(
				array(
					'taxonomy' => 'play_courts',
					'field' => 'slug',
					'terms' => $_POST['play_courts']
				)
			),
		'meta_query' => array(
			array(
				'key' => 'reservations_date_field_id',
				'value' => $_POST['date'],
				'compare' => '='
			)
		)
	);

	$loop = new WP_Query($args);
	while ($loop->have_posts()) : $loop->the_post();
		$from = getProgrammeReadableHour(get_post_meta(get_the_ID(), 'reservations_time_from_field_id', true));
		$until = getProgrammeReadableHour(get_post_meta(get_the_ID(), 'reservations_time_until_field_id', true));
		
		if(($from >= $fromReq && $from < $untilReq) || ($until > $fromReq && $until <= $untilReq)) {
			$return = true;
		}
	endwhile;
	wp_reset_postdata();
	
	return $return;
}

function invalidReservationValues() {
	$until = $_POST['from'] + $_POST['length'];
	$court_count = count(get_terms('play_courts', array('hide_empty' => false, 'slug' => $_POST['play_courts'])));
	if ($_POST['from'] >= 700 && $until <= 2300 && $court_count === 1 && is_numeric($_POST['opponent']) && is_numeric($_POST['opponent2']) && is_numeric($_POST['opponent3']) && is_user_logged_in()) {
		return false;
	}
	
	return true;
}

function sendReservationSMSes($postID) {
	$reservation = get_post($postID);
	$reservator = get_user_by('id', $reservation->post_author);
	$reservatorNumber = $reservator->yim;
	$postMeta = get_post_meta($postID);
	$postTerms = wp_get_object_terms($postID, 'play_courts');
	$date = $postMeta['reservations_date_field_id'][0];
	$date = (int)substr($date, 8, 2).'.'.(int)substr($date, 5, 2).'.';
		
	$text =  sprintf('Rezervirali ste %s za %s med %s in %s.', $postTerms[0]->name, $date, $postMeta['reservations_time_from_field_id'][0], $postMeta['reservations_time_until_field_id'][0]);
	sendSMS($reservatorNumber, $text);

	$opponentText = sprintf('%s je za vas rezerviral %s za %s med %s in %s.', $reservator->display_name, $postTerms[0]->name, $date, $postMeta['reservations_time_from_field_id'][0], $postMeta['reservations_time_until_field_id'][0]);	
	
	sendSMSesToOpponents($postMeta, $opponentText);
}

function sendCancellationSMSes($postID) {
	$reservation = get_post($postID);
	$reservator = get_user_by('id', $reservation->post_author);
	$reservatorNumber = $reservator->yim;
	$postMeta = get_post_meta($postID);
	$postTerms = wp_get_object_terms($postID, 'play_courts');
	$date = $postMeta['reservations_date_field_id'][0];
	$date = (int)substr($date, 8, 2).'.'.(int)substr($date, 5, 2).'.';
		
	$text =  sprintf('Preklicali ste rezervacijo: %s za %s med %s in %s.', $postTerms[0]->name, $date, $postMeta['reservations_time_from_field_id'][0], $postMeta['reservations_time_until_field_id'][0]);
	sendSMS($reservatorNumber, $text);

	$opponentText = sprintf('%s je za vas preklical rezervacijo: %s za %s med %s in %s.', $reservator->display_name, $postTerms[0]->name, $date, $postMeta['reservations_time_from_field_id'][0], $postMeta['reservations_time_until_field_id'][0]);
	
	sendSMSesToOpponents($postMeta, $opponentText);
}

function sendSMSesToOpponents($postMeta, $text) {
	$opponents = array(
		get_userdata($postMeta['reservations_opponent_field_id'][0]),
		get_userdata($postMeta['reservations_opponent_field_id2'][0]),
		get_userdata($postMeta['reservations_opponent_field_id3'][0])
	);
	
	foreach ($opponents as $opponent) {
		sendSMS($opponent->yim, $text);
	}
}

?>