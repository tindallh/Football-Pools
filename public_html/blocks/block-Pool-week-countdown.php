<?php

if (eregi("block-Forums.php", $_SERVER['SCRIPT_NAME'])) {
    Header("Location: index.php");
    die();
}
global $user_prefix, $cookie, $prefix, $dbi, $module_name, $db;
$today_date = date("Y-m-d");
$content .= "<script type=\"text/javascript\" src=\"/includes/jscountdown.js\"></script>\n";
$content .= "<script type=\"text/javascript\" src=\"/includes/jscountdown.inc.js\" onload=\"doCountdown()\"></script>\n";
$result = $db->sql_query("SELECT week FROM ".$prefix."_games where date > '$today_date' order by week, date limit 1");
$object = sql_fetch_object($result, $dbi);
if(is_object($object)) {
	$weekID = $object->week;
	$content .= "Picks for this week's next day of games are due before:<br><br>\n";
}	else {
	$result = $db->sql_query("SELECT week,date FROM ".$prefix."_games order by week DESC limit 1");
	$object = sql_fetch_object($result, $dbi);
	if(is_object($object)) {
		$weekID = $object->week;
		$day = date("l", strtotime($date[$game]));
	}
	$seasonover = 1;
	$content .= "<center><font size=+5>Sorry, the season is over.  Join us on the forums, or check out the all the results.</font></center>";
}

#$result_b = sql_query("SELECT date, count(date) 'count' FROM ".$prefix."_games WHERE week = '$weekID' group by date", $dbi);
$result_b = sql_query("SELECT date FROM ".$prefix."_games WHERE week = '$weekID' order by date limit 1", $dbi);
while ($row = $db->sql_fetchrow($result_b)) {
	$date = $row['date'];
	$count = $row['count'];
	$day = date("l", strtotime($date));
#	$content .= "$day: <i>$count games</i><br>\n";
	$content .= "$day: <div id=\"".$date."\"></div>\n";
}
?>
