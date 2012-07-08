<?
/*
Plugin Name: Reservations
Plugin URI: http://mr.si/reservations
Description: This is a plugin for tennis reservations.
Author: Miha Rekar
Version: 0.1 alpha
Author URI: http://mr.si/
*/
date_default_timezone_set('Europe/Ljubljana');

//reservation post type
require_once('post-type.php');

//reservations table
require_once('reservations-table.php');
add_shortcode('reservations_table', 'reservations_table_handler');

?>