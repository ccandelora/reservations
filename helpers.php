<?
require_once('NexmoMessage.php');
require('definitions.php');

function getOpponents() {
	$usersDropdownArgs = array(
		'role' => 'subscriber',
		'orderby' => 'display_name',
		'fields' => array('ID', 'display_name')
	);
	
	$users = get_users($usersDropdownArgs);
	$selectUsers = array('Izberi nasprotnika');
	foreach ($users as $user) {
		$selectUsers[$user->ID] = $user->display_name;
	}
	
	return $selectUsers;
}

function getHumanReadableHour($hour) {
	if ($hour%100){
		$theHour = floor($hour/100);
		$htmlHour = $theHour.':30';
	} else {
		$theHour = $hour/100;
		$htmlHour = $theHour.':00';
	}
	return $htmlHour;
}

function getProgrammeReadableHour($hour) {
	$hour = explode(':', $hour);
	if ($hour[1]>=30) {
		$hour = ($hour[0]*100) + 50;
	} else {
		$hour = $hour[0]*100;
	}
	
	return $hour;
}

function numberIsValidFormatForSMS($number) {
	if (strlen($number) === 12 && substr($number, 0, 4) === '+386') {
		return true;
	}
	return false;
}

function sendSMS($number, $text) {
	$sms = new NexmoMessage(NEXMO_API_KEY, NEXMO_API_SECRET);
	if (numberIsValidFormatForSMS($number)) {
		return $sms->sendText($number, 'TK Radomlje', $text);
	}
	
	return false;
}
?>