<?php
/*****************************************************************************/
/* Football Pool module for PHP-Nuke                                         */
/*   written by: Henry B. Tindall, Jr.                                       */
/*   version 1.01                                                            */
/*   first written: 15 Aug 2004                                              */
/*   last modified: 03 Sep 2009                                              */
/*****************************************************************************/

$columns = 15;          # Number of columns in the "all picks" page.
$leagueID = "NCAA";      # Capitalization is imPoRtAnT !!!!
$usespreads = 0;        # use the spreads?  (1 = yes, 0 = no)
$poolname = "open_NCAA";       # The name of the pool, for the picks file.
####  The end of Daylight savings time should be re-entered every year...
$DST_start = 69;	# Julian date for 2019 start of DST
$DST_end = 308;	# Julian date for 2018 end of DST
$ppercent = "50";       # The percentage of games that have to be picked to be in the stats.
$debug=0;
?>
