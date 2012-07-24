reservations
=============

**reservations** is a WordPress plugin I made for a [local tennis club](http://www.tk-radomlje.si/). It creates its own custom post type (reservation) and taxonomy (play_courts). It can then output a table of reservations through WP shortcode ```[reservations_table]```. 

About
-------

There are many reservation solutions out there but none with a good UX for my case. That is why I had to build one from scratch having ease of use as my our number 1 priority. They have 4 play courts so the table displays all four for the next 7 days at once. You can only reserve for one day in advance (from the front end) but there can be tournaments planned so it's good to see them on the "calendar" too.

Cool, I need just that
-------

Unfortunately the plugin will not work "as is". It's not internationalized, all text is there as plain strings since the language is Slovenian and not English. Also there are many features made just for our case (reservation for next day is possible from 19:00 on this day, play time is from 7:00 - 23:00, 4 play courts, Nexmo SMS service,...) and it's all hard coded.

Well, why is this here than?
-------

I needed to back up my code and wanted to have version control. You are more than welcome to use my code for your project and even more - if you need something similar but don't know how to make it, please [contact me](http://mr.si/) and we will find a solution good for both parties.

License
-------

reservations is developed by [Miha Rekar](http://mr.si/) and licensed under the [GPLv2 License](http://www.gnu.org/licenses/gpl-2.0.html)