<?php
/*****************************************************************************/
/* Football Pool module for PHP-Nuke                                         */
/*   written by: Henry B. Tindall, Jr.                                       */
/*****************************************************************************/

$allpools= "Pool-NCAA,Pool-NCAA-nospread,Pool-NCAA-top25,Pool-NCAA-top25-nospread,Pool-NFL,Pool-NFL-nospread";       # Listing of ALL pool directories under modules/ ...

# for testing, these can be used to override the "normal" dates and times
# to simulate mid-season, etc....
#  Here's the formats:
# $today_date = date("Y-m-d"); 
# $now_time = date("Hm"); 
# $this_year = date("Y"); 
# $julian_date = date("z"); 
#
# set them here:
#$today_date = "2007-11-02";
#$now_time = "1300";
#$this_year = "2007";
#$julian_date = date("z",strtotime($today_date));

$testing=0;
$top25=0;
# $debug=1;
?>
