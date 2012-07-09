<?php

function reservations_table_handler() {
	if('POST' == $_SERVER['REQUEST_METHOD']) {
		if(!empty($_POST['_reservation']) && wp_verify_nonce($_POST['_reservation'],'reservation')) {
			if(processReservation()) {
				echo '<div id="reservation_notice" style="color:green">Rezervacija je bila uspešna.</div>';
			} else {
				echo '<div id="reservation_notice" style="color:#f00">Rezervacija NI bila uspešna!<br />Prosim, poizkusite ponovno.</div>';
			}
		} elseif (!empty($_POST['reservation_id']) && wp_verify_nonce($_POST['_cancel'],'cancel_reservation')) {
			if(processCancellation()) {
				echo '<div id="reservation_notice" style="color:green">Preklic rezervacije je bil uspešen.</div>';
			} else {
				echo '<div id="reservation_notice" style="color:#f00">Preklic rezervacije NI bil uspešen!<br />Prosim, poizkusite ponovno.</div>';
			}
		}
	}

	$reservations = getReservations();
	$table = buildTable($reservations);
	wp_enqueue_style('reservation-table', plugins_url('reservations-table.css', __FILE__));
	wp_enqueue_script('jquery');
	wp_enqueue_script('reservation-table', plugins_url('reservations-table.js', __FILE__), 'jquery');
	printTable($table);
	reservationTooltips($reservations);
	reservationForm();
	cancelForm();
	loginForm();
}

function getReservations() {
	$reservations = array();

	//reservations from today on
	$args = array(
		'post_type' => 'reservation',
		'nopaging' => true,
		'meta_query' => array(
			array(
				'key' => 'reservations_date_field_id',
				'value' => date('Y-m-d'),
				'compare' => '>='
			)
		)
	);
	
	$loop = new WP_Query($args);
	while ($loop->have_posts()) : $loop->the_post();
		$from = getProgrammeReadableHour(get_post_meta(get_the_ID(), 'reservations_time_from_field_id', true));
		$until = getProgrammeReadableHour(get_post_meta(get_the_ID(), 'reservations_time_until_field_id', true));
		
		$reservation = array(
			'id' => get_the_ID(),
			'title' => get_the_title(),
			'reservator' => get_the_author(),
			'reservatorID' => get_the_author_meta('ID'),
			'courts' => wp_get_post_terms(get_the_ID(), 'play_courts'),
			'date' => get_post_meta(get_the_ID(), 'reservations_date_field_id', true),
			'opponent' => get_post_meta(get_the_ID(), 'reservations_opponent_field_id', true),
			'from' => $from,
			'until' => $until
		);
		$reservations[] = $reservation;
	endwhile;
	wp_reset_postdata();
	
	return $reservations;
}

function getEmptyTable() {
	$courtsTable = array();
	for ($i = 1; $i <= 	count(get_terms('play_courts', array('hide_empty' => false))); $i++) {
		$courtsTable['i'.$i] = '';
	}

	$date = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
	$emptyTable = array();
	for ($i = 0; $i < 7; $i++) {
		$day = array();
		for ($j = 700; $j < 2300; $j=$j+50) {
			$day[$j] = $courtsTable;
		}
		$emptyTable[date('Y-m-d', $date)] = $day;
		$date += 86400;
	}
	return $emptyTable;
}

function buildTable($reservations) {
	$courts = get_terms('play_courts', array('hide_empty' => false));
	$table = getEmptyTable();
	
	foreach ($reservations as $reservation) {
		if (array_key_exists($reservation['date'], $table)) {
			$today =& $table[$reservation['date']];
			$first = ' first';
			foreach ($today as $hour => $play_courts) {
				if (($hour >= $reservation['from']) && ($hour < $reservation['until'])) {
					foreach ($reservation['courts'] as $court) {
						$today[$hour][$court->slug] = array(
							'id' => $reservation['id'],
							'reservator' => $reservation['reservatorID'],
							'first' => $first
						);
					}
					$first = '';
				}
			}
		}
	}
	return $table;
}

function tableHeader() {
	$html = '<tr><th>Ura</th>';
	$courts = count(get_terms('play_courts', array('hide_empty' => false)));
	$date = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
	
	for ($i = 0; $i < 7; $i++) {
		$html .= '<th colspan="4">'.date('d.m.', $date).'</th>';
		$date += 86400;
	}
	$html .= '</tr><tr><td class="reservation_hour"></td>';
	for ($i = 0; $i < 7; $i++) {
		for ($j = 1; $j <= $courts; $j++) {
			$html .= '<td class="reservation_court">'.$j.'</td>';
		}
	}
	$html .= '</tr>';
		
	return $html;
}

function printTable($table) {
	global $current_user;
	$current_user = wp_get_current_user();
	
	$html = '<table id="reservations">';
	$html .= tableHeader();
	for ($hour = 700; $hour < 2300; $hour=$hour+50) {
		$html .= '<tr><td class="reservation_hour">'.getHumanReadableHour($hour).'</td>';
		$date = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		for ($i = 0; $i < 7; $i++) {
			if(is_user_logged_in()) {
				$canReserve = (userHasNoFutureReservations()) ? ' can_reserve' : '';
			} else {
				$canReserve = ' guest_reserve';
			}

			if ($i === 0) {
				$canReserve = ($hour > date('Gi')) ? $canReserve : '';
			} elseif ($i === 1) {
				$canReserve = (date('Gi') > 1900) ? $canReserve : '';
			} else {
				$canReserve = '';
			}
			
			$reservations = $table[date('Y-m-d', $date)][$hour];
			foreach ($reservations as $court => $reservation) {
				if ($reservation) {
					if($current_user->ID === $reservation['reservator']) {
						$canCancel = ' can_cancel';
					} else {
						$canCancel = '';
					}
					$html .= '<td class="reservation_court reserved'.$reservation['first'].$canCancel.'" data-reservation-id='.$reservation['id'].'></td>';
				} else {
					if ($canReserve != '') {
						$reserveData = ' data-reserve-court='.$court.' data-reserve-hour='.$hour.' data-reserve-date='.date('Y-m-d', $date);
					} else {
						$reserveData = '';
					}
				
					$html .= '<td class="reservation_court'.$canReserve.'"'.$reserveData.'></td>';
				}
			}
			$date += 86400;
		}
		
		$html .= '</tr>';
	}
	$html .= '</table>';
	echo $html;
}

function reservationTooltips($reservations) {
	$html = '<div id="reservation_tooltips">';
	foreach ($reservations as $reservation) {
		$courts = array();
		foreach ($reservation['courts'] as $court) {
			$courts[] = $court->name;
		}

		$html .= '<div id=reservation'.$reservation['id'].' class=reservation_tooltip>';
		$html .= '<p>Rezervirano: '.$reservation['reservator'].' | '.getHumanReadableHour($reservation['from']).' - '.getHumanReadableHour($reservation['until']).'</p>';
		
		if ($reservation['opponent'] > 0) {
			$html .= '<p>Nasprotnik: '.get_userdata($reservation['opponent'])->display_name.'</p>';
		}
		
		if ($reservation['title']) {
			$html .= '<p>Opomba: '.$reservation['title'].'</p>';
		}
		
		$html .= '<p>Igrišče: '.implode(', ', $courts).'</p></div>';
	}
	$html .= '</div>';
	echo $html;
}

function userHasNoFutureReservations() {
	if(current_user_can('edit_others_posts')) {
		return true;
	}

	global $current_user;
	$current_user = wp_get_current_user();

	$args = array(
		'post_type' => 'reservation',
		'author' => $current_user->ID,
		'meta_query' => array(
			array(
				'key' => 'reservations_date_field_id',
				'value' => date('Y-m-d'),
				'compare' => '>='
			)
		)
	);
	
	$loop = new WP_Query($args);
	return !$loop->have_posts();
};

?>