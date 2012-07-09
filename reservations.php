<?
/*
Plugin Name: Reservations
Plugin URI: http://mr.si/reservations
Description: This is a plugin for tennis reservations.
Author: Miha Rekar
Version: 0.1 alpha
Author URI: http://mr.si/
License:

Copyright 2012 Miha Rekar (info@mr.si)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

date_default_timezone_set('Europe/Ljubljana');

//reservation post type
require_once('post-type.php');

require_once('helpers.php');
require_once('forms.php');

require_once('reservations-table.php');
add_shortcode('reservations_table', 'reservations_table_handler');

?>