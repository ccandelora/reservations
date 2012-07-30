<?
	header('Content-Type: text/html; charset=utf-8');
	if(setcookie('priority_reservations', true, time() + (60 * 60 * 24 * 7 * 365 * 10), '/')) {
		echo 'Piškotek nastavljen!';
	} else {
		echo 'Piškotka se NE DA nastaviti!!!';
	}
?>