<?php
/*****************************************************************************/
/* Football Pool module for PHP-Nuke                                         */
/*   written by: Henry B. Tindall, Jr.                                       */
/*   version 18.01                                                           */
/*   first written: 15 Aug 2004                                              */
/*   last modified:                                                          */
/* Version 19.01 - 23 Sep 2019 - fixed pop-up schedule error for Neutral     */
/*                 site.
/*         18.01 - 11 Sep 2018 - new versioning to coincide with season,     */
/*                 modded to account for ties in NFL teams records.          */
/*          9.02 - 25 Aug 2016 - cleaning up TV networks, moving to popup    */
/*          9.01 - 22 Aug 2016 - adding logic to account for NFL teams       */
/*                 moving cities, started cleanup to move to Drupal, backing */
/*                 out aberrant Moneyline mods, fixed TeamSchedule links     */
/*          8.01 - 16 Sep 2015 - Started Moneyline mods.                     */
/*          7.03 - 13 Nov 2013 - Added the overlib routines to do the        */
/*                 schedule popups on the "MakePicks" page.                  */
/*          7.02 - 11 Nov 2013 - Added logic to do the Timezone conversion   */
/*                 from MT to CT.                                            */
/*          7.01 - 06 Oct 2012 - Fixed the graph problem, using jpgraph 2.2  */
/*                 and moving around some fonts.                             */
/*          7.00 - 17 Sep 2012 - Added logic for multiple TV networks.       */
/*          6.02 - 26 Nov 2010 - Added tooltips with team names and mascots  */
/*                 on the "show all picks" page for the team logos.          */
/*          6.01 - 26 Nov 2010 - New Graphs, for the overall picker rankings */
/*                 over time.  Also fixed '@'/'vs.' issue in schedules.      */
/*          5.05 - 10 Dec 2009 - Changed font styles                         */
/*          5.04 - 22 Nov 2009 - Added Helmet .gif, team name and mascot to  */
/*                 the Team schedules page, added button for team schedules, */
/*                 and added the Mascot table to the database...             */
/*          5.03 - 04 Sep 2009 - Finally got rid of those annoying zeros     */
/*                 in front of the percentages, even in the Graphs !!!       */
/*          5.02 - 03 Sep 2009 - one missed weekID v. sweekID, fixed DST     */
/*                 issue.                                                    */
/*          5.01 - 23 Aug 2009 - Changed navigation menu from text to        */
/*                 buttons, cleaned up.                                      */
/*          5.00 - 20 Aug 2009 - Bug quashing - mainly the sweekId vs.       */
/*                 weekid problem.                                           */
/*          4.08 - 03 Dec 2008 - added code to eliminate the 0-0 records for */
/*                 the 900-series team_ids.                                  */
/*          4.07 - 14 Nov 2008 - fixed the background in the "make my picks" */
/*                 page when there is a completed game not picked after a    */
/*                 game that was picked.  Also corrected a display error     */
/*                 where rankings weren't displayed for games that had no    */
/*                 spreads posted.                                           */
/*          4.06 - 06 Nov 2008 - fixed an oversight where the match against  */
/*                 the required percentage was ">" instead of ">=".          */
/*          4.05 - 28 Sep 2008 - added code to display pool name in graph,   */
/*                 added logic for proper grammar (possessive of names       */
/*                 ending in "s").                                           */
/*          4.04 - 10 Nov 2007 - fixed a date/time problem.                  */
/*          4.03 - 03 Oct 2007 - added percentages picked to top of "Make my */
/*                 picks" page and fixed the no TV network logo if there     */
/*                 were no spreads for a game in the spreads pool.           */
/*          4.02 - 02 Sep 2007 - added logic in WinnersWeek to make the      */
/*                 percentage of games picked come out correctly when in a   */
/*                 spreads Pool.                                             */
/*          4.01 - 21 Aug 2007 - added 'testing' variable and 'top25' to the */
/*                 config file.  Some other cosmetic changes                 */
/*          4.00 - 29 Jul 2007 - New season, new version.  Changed the way   */
/*                 the pool memberships were determined.  Added the ability  */
/*                 to override the actual date in the config.inc.php file    */
/*                 for testing and such.                                     */
/*          3.03 - 29 Dec 2006 - added the "neutral site" flag to display    */
/*                 "vs." instead of "at" for bowl games, etc. This will make */
/*                 the home and away records accurate.                       */
/*          3.02 - 11 Nov 2006 - Added rankings to the team schedules.       */
/*          3.01 - 29 Aug 2006 - Added the "poolname" variable to            */
/*                 distinguish between multiple pools by using more than one */
/*                 table of picks, I.E. "nuke_pool_picks_private1", and      */
/*                 moved the pool-specific variables into a config.inc.php.  */
/*                 Added the $poolname variable to the page headers.         */
/*          3.00 - 17 Aug 2006 - New season, new version.  From here on out, */
/*                 each new season will start a new Major version.           */
/*                 This year we start with adding a 'none' pick box and the  */
/*                 Station icon if the game is televised.                    */
/*          2.26 - 11 Jan 2006 - found one more sql statement that needed    */
/*                 to have the league added, and made some changes to box    */
/*                 titles and headers to make it obvious which pool the user */
/*                 is in.                                                    */
/*          2.25 - 21 Nov 2005 - Added links on the TeamSchedule page to see */
/*                 the opponents' schedules.                                 */
/*          2.24 - 04 Nov 2005 - fixed the number of games showed in the     */
/*                 overall leaderboard title line.                           */
/*          2.23 - 02 Nov 2005 - finally fixed the "next game is at:" stuff  */
/*          2.22 - 01 Nov 2005 - added rankings to the weekly winners page   */
/*                 and added logic to make it easier to change the minimum   */
/*                 number of games picked to be in the stats.                */
/*          2.21 - 30 Oct 2005 - changed the graphs a bit.                   */
/*          2.20 - 18 Oct 2005 - Added schedules page and records on picks   */
/*                 page.                                                     */
/*          2.14 - 06 Oct 2005 - Changed background display in MakePicks for */
/*                 completed games to make more pleasant and W3C compliant.  */
/*          2.13 - 26 Sep 2005 - Made all picks display more W3C compliant.  */
/*          2.12 - 18 Sep 2005 - Changed output for "Show all picks".        */
/*          2.11 - 09 Sep 2005 - Display CDT or CST for the game times, to   */
/*                 prevent confusion.                                        */
/*          2.10 - 08 Sep 2005 - Added code to enable pools with or without  */
/*                 spreads.                                                  */
/*          2.01 - 05 Sep 2005 - fixed the graphing code and the WinnersAll  */
/*                 module errors.                                            */
/*          2.00 - 06 Jun 2005 - added "season" and "league" fields to       */
/*                 tables and scripts to enable different types of pools     */
/*                 over multiple years.                                      */
/*          1.01 - 28 Nov 2004 - added performance graphs                    */
/*          1.00 - 15 Aug 2004 - Initial Release                             */
/*****************************************************************************/
$version='18.01.8';

global $poolname, $DST_start, $DST_end, $home_wins, $home_losses, $away_wins, $away_losses, $home_ties, $away_ties, $network;

if (!eregi("modules.php", $_SERVER['SCRIPT_NAME'])) {
	die ("You can't access this file directly...");
}

require_once("mainfile.php");
$module_name = basename(dirname(__FILE__));
get_lang($module_name);
if (is_user($user)) {
	cookiedecode($user);
	$uname = $cookie[1];
	$result = $db->sql_query("SELECT user_id, member_of FROM ".$user_prefix."_users WHERE username='$uname'");
	$row = $db->sql_fetchrow($result);
	$user_id = intval($row[user_id]);
	$membership = $row['member_of'];
}
include ('header.php');
$boxContent .= '<DIV id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></DIV>';
$boxContent .= '<script type="text/javascript" src="/js/mini/overlib_mini.js"><!-- overLIB (c) Erik Bosrup --></script>';
#$boxContent .= "\n";

/*      A few variables we use later on....		      */
require_once("config.inc.php");

# check membership:
$subscribed = 0;
$membership = explode(",",$membership);
foreach ($membership as $sub) {
	if (ereg($sub, $poolname)) {
		$subscribed++;
	}
}
if ($subscribed == 0) {
	$boxContent .= "<h3>Sorry, you're not a member of the \"".$poolname."\" pool.</h3>";
	$boxContent .= "If this is incorrect, please <a href=\"modules.php?name=ContactUs\">E-mail Us</a>, and be sure to include your username and the name of the pool.";
	themecenterbox($boxTitle, $boxContent);
	include ('footer.php');
	exit;
}

# $debug = 0;
# If they weren't read in from the config file, do dates and time.
if (!$today_date) { $today_date = date("Y-m-d"); }
if (!$now_time) { 
	$now_time = date("Hi");
	# Get the GMT offset, we have to use it to adjust the server's time to match the Central time used
	# in the database.
	# $now_offset = date("O");
	# if the server is MT (which the hosting ssrvice claims), this should be -6 or -7 depending on DST...
	# since we know CDT is -5 and CST is -6....
	list($ty,$tmo,$td) = explode("-",$today_date);
	$th = substr($time[$game],0,2);
	$tmi = substr($time[$game],2);
	$tDST = localtime(mktime($th,$tmi,0,$tmo,$td,$ty));
	$tday = intval($tDST[7]+1);
	((($tday > $DST_start) && ($tday < $DST_end)) ? $time_offset = -5 : $time_offset = -6 );
	if ($now_time > 2300 ) {
		$now_time = $now_time - 2300;
		# and we have to add one to the DATE
		$today_date = date("Y-m-d", (mktime($th,$tmi,0,$tmo,$td,$ty)+86400));
	} elseif ($now_time == 2300 ) {
		$now_time = "0000";
	} elseif ($now_time < 2300 ) {
		$now_time = $now_time +100;
	}
}

if ( $debug > 0 ) { $boxContent .= "\n\n<!-- Version = $version -->\n"; }
if ( $debug > 0 ) { $boxContent .= "\n\n<!-- HBT \$now_time=\"$now_time\" -->\n\n"; }
if (!$this_year) { $this_year = date("Y"); }
if (!$julian_date) { $julian_date = date("z"); }
if ($julian_date < 90) {
	$this_season = $this_year-1;
} else {
	$this_season = $this_year;
}
$disp_date = date("l, j F, Y");

#echo "<center>Today's Date: $disp_date ";
if (!isset($seasonID)) {
	$seasonID = $this_season;
}

if (isset($weekID)) {
	$weekID = intval($weekID);
} else {
	$sql = "SELECT id, name FROM ".$prefix."_pool_tvnetworks";
	$result = $db->sql_query($sql);
	if ($debug > 0) { $boxContent .= "<!-- HBT \'$sql\' -->\n"; }
	while ($row = $db->sql_fetchrow($result)) {
		$network[$row['id']] = $row['name'];
	}
	$sql = "SELECT week, date, time, tvnetwork, tvnetwork2 FROM ".$prefix."_pool_games";
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE ((date > '$today_date') OR (date = '$today_date' AND time > '$now_time'))";
	$sql .= " AND league = '$leagueID' AND season = ".$seasonID."";
	if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
	$sql .= " ORDER BY date, time limit 1";
    if ($debug > 0) { $boxContent .= "<!-- HBT \'$sql\' -->\n"; }
	$result = $db->sql_query($sql);
	$object = sql_fetch_object($result, $dbi);
	if(is_object($object)) {
		$weekID = $object->week;
		$weekID = intval($weekID);
		$date = $object->date;
		$time = $object->time;
		$lastweek = $weekID-1;
		$tvnetwork = intval($object->tvnetwork);
		$tvnetwork2 = intval($object->tvnetwork2);
		list($y,$m,$d) = explode("-",$date);
		$date = date("l j F, Y", mktime(0,0,0,$m,$d,$y));
		echo "The next game is $date at $time";
		if ($tvnetwork > 0) { 
			echo ", on ".$network[$tvnetwork];
			if ($tvnetwork2 > 0) { 
				echo " and ".$network[$tvnetwork2];
			}
			echo " !<br>\n"; 
		}
		echo "<br>\n";
	} else {
		$sql = "SELECT week,date FROM ".$prefix."_pool_games";
		if ($testing == '1') { $sql .= "_test"; }
		$sql .= " WHERE season = '$seasonID' AND league = '$leagueID'";
		if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
		$sql .= " ORDER by week DESC limit 1";
		$result = $db->sql_query($sql);
		$object = sql_fetch_object($result, $dbi);
		if(is_object($object)) {
			$weekID = $object->week;
			$weekID = intval($weekID);
			$lastweek=$weekID;
#					$sweekID = $weekID;
		}
		$seasonover = 1;
#				echo "<center><font size=+5>Sorry, the season is over. Join us on Facebook, or check out the all the results.</font></center>";
	}
}
$sql = "SELECT id, name FROM ".$prefix."_pool_tvnetworks";
$result = $db->sql_query($sql);
while ($row = $db->sql_fetchrow($result)) {
	$network[$row['id']] = $row['name'];
}
$sql = "SELECT home_score, visitor_score, week FROM ".$prefix."_pool_games";
if ($testing == '1') { $sql .= "_test"; }
$sql .= " WHERE league = '$leagueID' AND season = '$seasonID' AND week = '$weekID'";
if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
$result_check = sql_query($sql, $dbi);
while ($row = $db->sql_fetchrow($result_check)) {
	$h = intval($row['home_score']);
	$v = intval($row['visitor_score']);
	$g = intval($row['week']);
	if ($h > 0 || $v >0) { $results_present = 1; }
}
if ($results_present == 1) {
	$lrweek = $weekID;
} else {
	$lrweek = $weekID-1;
}
$lastweek=$lrweek-1;
if ($op == "MakePicks") {
	DisplayWeek($seasonID, $leagueID, $weekID);
} elseif ($op == "ScoresLast") {
	DisplayWeek($seasonID, $leagueID, $lastweek);
} elseif ($op == "SavePicks") {
	SavePicks($seasonID, $leagueID);
} elseif ($op == "WinnersWeek") {
	WinnersWeek($seasonID, $leagueID, $sweekID);
} elseif ($op == "WinnersAll") {
	WinnersAll($seasonID, $leagueID, $sweekID);
	GraphPos($seasonID, $leagueID);
} elseif ($op == "ShowAllPicks") {
	ShowAllPicks($seasonID, $leagueID, $sweekID, $start_user);
} elseif ($op == "DisplayWeek") {
	DisplayWeek($seasonID, $leagueID, $sweekID);
} elseif ($op == "Trivia") {
	Trivia($seasonID, $leagueID, $weekID);
} elseif ($op == "GraphMe") {
	GraphMe($seasonID, $leagueID, $graphuser);
} elseif ($op == "GraphPos") {
	GraphPos($seasonID, $leagueID);
} elseif ($op == "TeamSchedule") {
	if ($_POST[team]) {
		$team = $_POST[team];
	}
	TeamSchedule($team, $seasonID, $leagueID);
} else {
	Schedule($seasonID, $leagueID);
}

$prev_season = $seasonID-1;
$next_season = $seasonID+1;
if (!$sweekID) { $sweekID = $weekID; }
$prev_week = $sweekID - 1;
$next_week = $sweekID + 1;
$current_week = $lrweek;

#       Nav Buttons !
$boxContent .= "<br>\n<center>\n<table border=0 cellpadding=0 cellspacing=0>\n";
$boxContent .= "\t<tr>\n";
# First (left) cell is the previous week/year nav:
$boxContent .= "\t\t<td><center>\n";
if ($sweekID > 1) {
	$boxContent .= "\t\t\t<a href=\"modules.php?name=$module_name&amp;op=$op&amp;seasonID=$seasonID&amp;sweekID=$prev_week\"><img src=\"images/poollogos/buttons/week-b-on.jpg\" border=2 alt=\"Previous Week\" title=\"Previous Week\"></a><br>\n";
} else {
	$boxContent .= "\t\t\t<img src=\"images/poollogos/buttons/week-b-off.jpg\" border=2 alt=\"Previous Week\" title=\"Previous Week\"><br>\n";
}
if ($seasonID > 2004) {
	$boxContent .= "\t\t\t<a href=\"modules.php?name=$module_name&amp;op=$op&amp;seasonID=$prev_season&amp;sweekID=$sweekID\"><img src=\"images/poollogos/buttons/year-b-on.jpg\" border=2 alt=\"Previous Year\" title=\"Previous Year\"></a>\n";
} else {
	$boxContent .= "\t\t\t<img src=\"images/poollogos/buttons/year-b-off.jpg\" border=2 alt=\"Previous Year\" title=\"Previous Year\">\n";
}
$boxContent .= "\t\t</center></td>\n";

# Second (middle) cell is the good stuff:
$boxContent .= "\t\t<td><center>\n";
$boxContent .= "\t\t\t<a href=\"modules.php?name=$module_name&amp;op=MakePicks&amp;seasonID=$this_season\"><img src=\"images/poollogos/buttons/picks.jpg\" border=2 alt=\"Make My Picks\" title=\"Make My Picks\"></a>\n";
$boxContent .= "\t\t\t<a href=\"modules.php?name=$module_name&amp;op=ShowAllPicks&amp;seasonID=$seasonID&amp;sweekID=$sweekID\"><img src=\"images/poollogos/buttons/scoreboard.jpg\" border=2 alt=\"Scoreboard\" title=\"Scoreboard\"></a><br>\n";
$boxContent .= "\t\t\t<a href=\"modules.php?name=$module_name&amp;op=WinnersWeek&amp;seasonID=$seasonID&amp;sweekID=$sweekID\"><img src=\"images/poollogos/buttons/weekly.jpg\" border=2 alt=\"Weekly Winners\" title=\"Weekly Winners\"></a>\n";
$boxContent .= "\t\t\t<a href=\"modules.php?name=$module_name&amp;op=WinnersAll&amp;seasonID=$seasonID&amp;sweekID=$sweekID\"><img src=\"images/poollogos/buttons/overall.jpg\" border=2 alt=\"Overall Winners\" title=\"Overall Winners\"></a><br>\n";
$boxContent .= "\t\t\t<a href=\"modules.php?name=$module_name&amp;op=Trivia&amp;seasonID=$seasonID\"><img src=\"images/poollogos/buttons/trivia.jpg\" border=2 alt=\"Trivial Statistics\" title=\"Trivial Statistics\"></a>\n";
$boxContent .= "\t\t\t<a href=\"modules.php?name=$module_name\"><img src=\"images/poollogos/buttons/schedule.jpg\" border=2 alt=\"Main Page (schedule)\" title=\"Main Page (schedule)\"></a>\n";
$boxContent .= "\t\t\t<a href=\"modules.php?name=$module_name&amp;op=GraphMe&amp;seasonID=$seasonID&amp;leagueID=$leagueID&amp;graphuser=".$user_id."\"><img src=\"images/poollogos/buttons/graph.jpg\" border=2 alt=\"Graph Results\" title=\"Graph Results\"></a><br>\n";
$boxContent .= "\t\t</center></td>\n";

# Third (right) cell is the next week/year nav:
$boxContent .= "\t\t<td><center>\n";
if ($sweekID < $current_week) {
	$boxContent .= "\t\t\t<a href=\"modules.php?name=$module_name&amp;op=$op&amp;seasonID=$seasonID&amp;sweekID=$next_week\"><img src=\"images/poollogos/buttons/week-f-on.jpg\" border=2 alt=\"Next Week\" title=\"Next Week\"></a><br>\n";
} else {
	$boxContent .= "\t\t\t<img src=\"images/poollogos/buttons/week-f-off.jpg\" border=2 alt=\"Next Week\" title=\"Next Week\"><br>\n";
}
if ($seasonID < $this_season) {
	$boxContent .= "\t\t\t<a href=\"modules.php?name=$module_name&amp;op=$op&amp;seasonID=$next_season&amp;sweekID=$sweekID\"><img src=\"images/poollogos/buttons/year-f-on.jpg\" border=2 alt=\"Next Season\" title=\"Next Season\"></a>\n";
} else {
	$boxContent .= "\t\t\t<img src=\"images/poollogos/buttons/year-f-off.jpg\" border=2 alt=\"Next Season\" title=\"Next Season\">\n";
}
$boxContent .= "\t\t</center></td>\n";
$boxContent .= "\t</tr>\n";
# Kludge add-in of the TeamSchedule button
$boxContent .= "\t<tr>\n";
$boxContent .= "\t\t<td colspan=3>\n\t\t\t<center><a href=\"modules.php?name=$module_name&amp;op=TeamSchedule&amp;seasonID=$seasonID\">\n";
$boxContent .= "\t\t\t<img src=\"images/poollogos/buttons/teamschedule.jpg\" alt=\"Team Schedules\" title=\"Team Schedules\"></img>\n";
$boxContent .= "\t\t\t</a></center>\n\t\t</td>\n\t</tr>\n";
$boxContent .= "</table>\n</center>\n";

themecenterbox($boxTitle, $boxContent);

include ('footer.php');

/*********************************************************/
/* Functions						     */
/*********************************************************/

function Schedule($seasonID, $leagueID) {
	global $poolname, $testing, $top25, $seasonID, $leagueID, $usespreads, $weekID, $lastweek, $boxTitle, $boxContent, $user, $user_id, $uname, $cookie, $prefix, $dbi, $module_name, $db;
	$boxTitle = "$leagueID Schedule -- $seasonID";
	$boxTitle .= " (".$poolname." pool) ";
	if ($usespreads == 0) { $boxTitle .= " (no spreads)"; }
	$boxContent .= "<center><table border=\"0\" cellpadding=\"2\">";
	$sql = "SELECT visitor_score, home_score, week";
	$sql .= " FROM ".$prefix."_pool_games";
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID' AND season = '$seasonID'";
	if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
	$sql .= " ORDER BY week, date,time";
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result)) {
		if ($row['visitor_score'] >0 || $row['home_score'] >0) { $finished_pool_games[$row['week']] = 1; }
	}
	$sql = "SELECT week,date FROM ".$prefix."_pool_games";
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID' AND season = '$seasonID'";
	$sql .= " ORDER by week, date";
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result)) {
		$f_week = $row['week'];
		if (!($gcount[$f_week])) { $f_date[$f_week] = $row['date']; }
		$gcount[$f_week]++;
	}
	foreach ($gcount as $f_week => $count) {
		if ($f_week == $weekID) {
			$boxContent .= "<tr><td><a href=\"modules.php?name=$module_name&amp;op=DisplayWeek&amp;seasonID=".$seasonID."&amp;sweekID=".$f_week;
			$boxContent .= "\"><b>Week $f_week</b></a></td><td align=\"right\">$gcount[$f_week] games, first game on $f_date[$f_week]</td>";
		} else {
			$boxContent .= "<tr><td><a href=\"modules.php?name=$module_name&amp;op=DisplayWeek&amp;seasonID=".$seasonID."&amp;sweekID=".$f_week;
			$boxContent .= "\">Week $f_week</a></td><td align=\"right\">$gcount[$f_week] games, first game on $f_date[$f_week]</td>";
		}
		if ($f_week <= $weekID) {
			$boxContent .= "<td><a href=\"modules.php?name=$module_name&amp;op=ShowAllPicks&amp;seasonID=".$seasonID."&amp;sweekID=".$f_week;
			$boxContent .= "&amp;start_user=1\">Show Picks</a></td>";
		}
		if ($finished_pool_games[$f_week] == 1) { $boxContent .= "<td><a href=\"modules.php?name=$module_name&amp;op=WinnersWeek&amp;seasonID=".$seasonID."&amp;sweekID=".$f_week."\">Winners</a></td>"; }
		$boxContent .= "</tr>\n";
	}
	$boxContent .= "</table></center><br><br>\n";
}

function SavePicks() {
	global $poolname, $testing, $top25, $seasonID, $leagueID, $usespreads, $weekID, $lastweek, $boxTitle, $boxContent, $user, $user_id, $uname, $cookie, $prefix, $dbi, $module_name, $db, $ppercent, $p_ppercent, $thisweek_pct, $allpicked, $futurepicks, $future_pct, $allgames, $pastgames, $thisweekgames;
	$today_date = date("Y-m-d");
	$now_time = date("Hi");
	if (is_user($user)) {
		getusrinfo($user);
		cookiedecode($user);
	}
	$boxTitle = "$uname's $seasonID week $weekID $leagueID Picks";
	$boxTitle .= " (".$poolname." pool) ";
	if ($usespreads == 0) { $boxTitle .= " (no spreads)"; }
	$sql = "SELECT game, pick FROM ".$prefix."_pool_picks_".$poolname;
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID' AND usespreads = '$usespreads'";
	$sql .= " AND season = '$seasonID' AND week='$weekID' AND user_id = '$user_id'";
	$sql .= " ORDER BY game";
	$result_b = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result_b)) {
		$db_game = intval($row['game']);
		$db_pick[$db_game] = $row['pick'];
	}
	#$sql = "SELECT team_id, team_name from ".$prefix."_pool_teams";
	#$sql .= " WHERE league = '$leagueID'";
	$sql = "SELECT team_id, team_name FROM nuke_pool_teams NATURAL JOIN";
	$sql .= " ( SELECT team_id, MAX(season) as season FROM ".$prefix."_pool_teams";
	$sql .= " WHERE season <= '$seasonID' AND league = '$leagueID' AND team_id < 990";
	$sql .= " GROUP BY team_id) latestteam";
	$result = $db->sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result)) {
		$team_id = $row['team_id'];
		$team_name[$team_id] = $row['team_name'];
	}
	$sql = "SELECT date, time, home, home_score, visitor, visitor_score, home_spread, game";
	$sql .= " FROM ".$prefix."_pool_games";
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID' AND season = '$seasonID' AND week = '$weekID'";
	if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
	$sql .= " ORDER BY date,time, game";
	$result = $db->sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result)) {
		$game = intval($row['game']);
		$date[$game] = $row['date'];
		$time[$game] = $row['time'];
		$day = date("l", strtotime($date[$game]));
		$gid[$game] = $game;
		$h_id = $row['home'];
		$home[$game] = $team_name[$h_id];
		$v_id = $row['visitor'];
		$visitor[$game] = $team_name[$v_id];
		$home_spread[$game] = $row['home_spread'];
	}
	foreach ($gid as $game) {
		$pickval[$game] = $_POST[$game];
		/* here's a handy tool */
		(($pickval[$game] == "home") ? $choice = $home[$game] : $choice = $visitor[$game] );
		(($pickval[$game] == "home") ? $nonchoice = $visitor[$game] : $nonchoice = $home[$game] );
		if (($date[$game] == $today_date && $time[$game] > $now_time) || ($date[$game] > $today_date)) {
			if (($pickval[$game] == "home" ) || ($pickval[$game] == "visitor" )) {
				$boxContent .= "<b>$choice</b> over <b>$nonchoice</b>";
				if (($db_pick[$game]) && ($db_pick[$game] != $pickval[$game])) {
					$sql = "UPDATE ".$prefix."_pool_picks_".$poolname;
					if ($testing == '1') { $sql .= "_test"; }
					$sql .= " SET pick = '$pickval[$game]' WHERE (user_id = '$user_id' AND week = '$weekID'";
					$sql .= " AND game = '$game' AND season = '$seasonID' AND league = '$leagueID' AND usespreads = '$usespreads')";
					$update = $db->sql_query($sql, $dbi);
					$boxContent .= " (changed)<br>\n";
				} elseif (!($db_pick[$game])) {
					$sql = "INSERT INTO ".$prefix."_pool_picks_".$poolname;
					if ($testing == '1') { $sql .= "_test"; }
					$sql .= " (user_ID, season, league, usespreads, week, game, pick)";
					$sql .= " VALUES ('$user_id', '$seasonID', '$leagueID', '$usespreads', '$weekID', '$game', '$pickval[$game]')";
					$insert = $db->sql_query($sql, $dbi);
					$boxContent .= " (added)<br>\n";
				} else {
					$boxContent .= "<br>\n";
				}
			} elseif ($pickval[$game] == "none" ) {
				$boxContent .= "<b>NO PICK</b> for      $visitor[$game] at $home[$game]";
				if ((($db_pick[$game] == "home") || ($db_pick[$game] == "visitor")) && ($db_pick[$game] != $pickval[$game])) {
					$sql = "UPDATE ".$prefix."_pool_picks_".$poolname;
					if ($testing == '1') { $sql .= "_test"; }
					$sql .= " SET pick = '$pickval[$game]' WHERE (user_id = '$user_id' AND week = '$weekID' AND game = '$game' AND season = '$seasonID' AND league = '$leagueID' AND usespreads = '$usespreads')";
					$update = $db->sql_query($sql, $dbi);
					$boxContent .= " (changed)<br>\n";
				} else {
					$boxContent .= "<br>\n";
				}
			}
		} else {
			$boxContent .= "<i>Sorry, you're too late to add/change the pick for $visitor[$game] at $home[$game].</i><br>\n";
		}
	}
	# Minpct subroutine used to be inline here.     We needed to do it in more than one place,
	# So what the hell.
	PickPct($seasonID, $leagueID, $weekID);
	# $thisweek_pct, $allpicked, $futurepicks, $future_pct
	if ($ppercent - $thisweek_pct > 0) {
		$hl_on = "<b><font color=darkred>Only ";
		$hl_off = "</font></b>";
	} else {
		$hl_on = $hl_off = '';
	}
	$boxContent .= "<br>Thanks for the picks, $uname.<br>";
	$boxContent .= "If you change your mind, you can go back and change";
	$boxContent .= " any of your picks up until the start of that particular game...<br>\n";
	$boxContent .= "You'll see your old picks already filled in on the form if you go back and refresh it.<br><br>\n";
	$boxContent .= "<b>You've picked $hl_on$thisweek_pct%$hl_off of the games this week.</b>	";
	if ($sweekID > 1) { $boxContent .= " and $past_pct% of the prior games.<br>\n"; }
	if ($leagueID == 'NCAA' && $top25 == '1') {
		$sql = "SELECT week FROM ".$prefix."_pool_games";
		if ($testing == '1') { $sql .= "_test"; }
		$sql .= " WHERE league = '$leagueID' AND season = '$seasonID' ORDER BY week desc limit 1";
		$thelastwk = sql_query($sql, $dbi);
		while ($row = $db->sql_fetchrow($thelastwk)) { $wk = $row['week']; }
		# we're going to re-work this during the off-season;
		# just use 30% as the average percentage of games each week that
		# feature at least one Top 25 team and go from there...
		# there's also a block that looks a lot like this down at line 1080
		# So they both should become a subroutine !!
		$low_gr = 13 * ($wk - $sweekID);
		$high_gr = 25 * ($wk - $sweekID);
		$low_allgames = $pastgames+$thisweekgames+$low_gr;
		#				$boxContent .= "\n<!-- $low_allgames = $pastgames+$thisweekgames+$low_gr; -->";
		$high_allgames = $pastgames+$thisweekgames+$high_gr;
		#				$boxContent .= "\n<!-- $high_allgames = $pastgames+$thisweekgames+$high_gr; -->";
		$low_pgr = number_format((($ppercent/100)*$low_allgames)-$allpicked,0);
		#				$boxContent .= "\n<!-- $low_pgr = number_format((($ppercent/100)*$low_allgames)-$allpicked,0); -->";
		$high_pgr = number_format((($ppercent/100)*$high_allgames)-$allpicked,0);
		#				$boxContent .= "\n<!-- $high_pgr = number_format((($ppercent/100)*$high_allgames)-$allpicked,0); -->";
		$low_f_pct = number_format(($low_pgr/$low_gr)*100,1);
		$high_f_pct = number_format(($high_pgr/$high_gr)*100,1);
		$boxContent .= "You can count on between $low_gr and $high_gr games to pick for the rest of the season;<br>";
		if (($low_pgr <= 0) && (($past_pct < $ppercent) && ($thisweek_pct < $ppercent))) {
			#					$boxContent .= "<!-- ($low_pgr, $high_pgr)) -->";
			$boxContent .= " &nbsp;You might not be able to pick enough games ";
		} else {
			$boxContent .= " so you'll have to pick between $low_pgr ($low_f_pct%) and $high_pgr ($high_f_pct%) more games";
		}
		$boxContent .= " to be in the end-of-season stats.";
	} else {
		if ($future_pct < 0) {
			$boxContent .= "You don't have to pick any more games";
		} elseif ($future_pct > 100) {
			$boxContent .= "Sorry, you won't be able to pick enough games for the rest of the year";
		} elseif ($future_pct >= 95) {
			$boxContent .= "<font color=darkred>BE CAREFULL ! &nbsp;You have very little 'wiggle room'! You must pick $future_pct% of all remaining games";
		} else {
			$boxContent .= "You have to make at least $futurepicks more picks this year, or $future_pct% of the remaining games ";
		}
		$boxContent .= ", to be in the End-of-season stats.<br>";
		if ($usespreads == 1) {
			$boxContent .= "	Keep in mind that some of the remaining games might NOT have spreads, so the percentage you have to pick might actually be higher !";
		}
		if ($future_pct > 95) { $boxContent .= "</font>"; }
	}
	$boxContent .= "<br><br>\n";
}

function WinnersWeek($seasonID, $leagueID, $sweekID) {
	global $poolname, $testing, $top25, $usespreads, $user_prefix, $lastweek, $sweekID, $weekID, $boxTitle, $boxContent, $user, $user_id, $uname, $cookie, $prefix, $dbi, $module_name, $db, $ppercent;
	#			$sweekID = $_POST[sweekID];
	$boxTitle = "$seasonID week $sweekID $leagueID";
	$boxTitle .= " (".$poolname.") ";
	if ($usespreads == 0) { $boxTitle .= " (no spreads)"; }
	$numweeks = 0;
	$sql = "SELECT user_id, game, pick";
	$sql .= " FROM ".$prefix."_pool_picks_".$poolname;
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID' AND usespreads = '$usespreads'";
	$sql .= " AND season = '$seasonID' AND week='$sweekID'";
	$sql .= " ORDER BY user_id";
	#			$boxContent .= "<!-- HBT \$sql='$sql' -->\n";
	$result_b = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result_b)) {
		$uid = intval($row['user_id']);
		$db_game = intval($row['game']);
		$db_pick[$uid][$db_game] = $row['pick'];
		$numpicks++;
	}
	if ($numpicks > 0) {
		$ftw = 0;
		$sql="SELECT user_id, username FROM ".$user_prefix."_users";
		$sql .= " WHERE user_id > 1 ORDER BY user_id";
		$result = sql_query($sql, $dbi);
		while ($row = $db->sql_fetchrow($result)) {
			$db_id = intval($row['user_id']);
			$db_user[$db_id] = $row['username'];
			$db_uid[$db_user[$db_id]] = $db_id;
		}
		$sql = "SELECT home_score, visitor_score, home_spread, game";
		$sql .= " FROM ".$prefix."_pool_games";
		if ($testing == '1') { $sql .= "_test"; }
		$sql .= " WHERE league = '$leagueID' AND season = '$seasonID' AND week = '$sweekID'";
		$sql .= " AND (home_score IS NOT NULL AND visitor_score IS NOT NULL)";
		if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
		if ($usespreads == '1') { $sql .= " AND (home_spread IS NOT NULL)"; }
		$sql .= " ORDER by date,game";
		#				$boxContent .= "<!-- HBT \$sql = '$sql' -->\n";
		$gameresults = sql_query($sql, $dbi);
		while ($row = $db->sql_fetchrow($gameresults)) {
			$ftw++;
			#					$boxContent .= "<!-- \$ftw=$ftw -->\n";
			$g_game = $row['game'];
			$w_game[$g_game] = $g_game;
			$g_home_score = $row['home_score'];
			$g_visitor_score = $row['visitor_score'];
			$g_home_spread = $row['home_spread'];
			if ($usespreads == 0 ) {
				$g_result = $g_home_score - $g_visitor_score;
			} else {
				$g_result = ($g_home_score - $g_home_spread) - $g_visitor_score;
			}
			if ($g_result > 0) {
				$g_winner[$g_game] = "home";
			} elseif ($g_result < 0) {
				$g_winner[$g_game] = "visitor";
			} else {
				$g_winner[$g_game] = "push";
			}
		}
		$rightgames=$wronggames='';
		foreach ($db_uid as $username => $usernum) {
			$games_picked[$usernum]=0;
			foreach ($w_game as $g_num) {
				# next line not very clean....  Fix this.
				if (( $db_pick[$usernum][$g_num] == "home" ) || ( $db_pick[$usernum][$g_num] == "visitor" )) {
					$uname = $db_user[$usernum];		     #testing
					$victor = $g_winner[$g_num];	    #testing
					$games_picked[$usernum]++;
					if ($g_winner[$g_num] == $db_pick[$usernum][$g_num]) {
						$right[$usernum]++;
					} elseif ($g_winner[$g_num] == "push") {
						$push[$usernum]++;
					} else {
						$wrong[$usernum]++;
					}
				}
			}
			if (!($right[$usernum])) { $right[$usernum] = 0; }
			if (!($wrong[$usernum])) { $wrong[$usernum] = 0; }
			if (($right[$usernum] + $wrong[$usernum]) > 0) {
				$pct[$usernum] = ltrim(($right[$usernum] / ($right[$usernum] + $wrong[$usernum])),0);
			} else {
				$pct[$usernum] = 0;
			}
		}
		arsort($pct);
		$i = 1;
		$rank = 1;
		$t_rank = 1;
		$verbage = "is";
		$noun = "winner";
		foreach ($pct as $picker => $pick_pct ) {
			$gp1 = $games_picked[$picker];
			$twpc = ($gp1/$ftw)*100;
			if ($twpc >= $ppercent) {
				if ($i == 1) {
					$ranking[$picker]=$rank;
					$bigwinner = $db_user[$picker];
					$bigpct = ltrim(strval(number_format($pick_pct, 3)),0);
				} else {
					if (ltrim(strval(number_format($pick_pct, 3)),0) === $bigpct) {
						$bigwinner .= ", ".$db_user[$picker];
						$noun = "winners";
						$verbage = "are";
					}
					if ($pick_pct == $prev_pct) {
						$ranking[$picker] = $rank;
						$t_rank++;
					} else {
						$rank = $rank + $t_rank;
						$ranking[$picker] = $rank;
						$t_rank = 1;
					}
				}
				$i++;
				$prev_pct=$pick_pct;
			} else {
				unset ($pct[$picker]);
			}
		}
		$boxContent .= "<h3>$leagueID $seasonID";
		if ($usespreads == 0) { $boxContent .= " (no spreads)"; }
		$boxContent .= " Week $sweekID's big $noun $verbage:</h3><h2>$bigwinner</h2><h3>with a percentage of $bigpct !</h3>\n";
		$boxContent .= "<table cellspacing=\"2\" cellpadding=\"2\">\n";
		$boxContent .= "<tr><td><b>Rank</b></td><td></td><td><b>record</b></td><td><b>percentage</b></td></tr>\n";
		foreach ($pct as $picker => $pick_pct ) {
			$real_user = $db_user[$picker];
			$real_uid = $db_uid[$real_user];
			$boxContent .= "<tr><td align=\"right\"><font class=\"winners\"><b>$ranking[$picker]</b></font></td>";
			#$boxContent .= "<td><b><a href=\"modules.php?name=Private_Messages&amp;file=index&amp;mode=post&amp;u=".$real_uid."\"><font class=\"winners\">".$db_user[$picker]."</font></a></b></td>";
			$boxContent .= "<td><b><font class=\"winners\">".$db_user[$picker]."</font></b></td>";
			$boxContent .= "<td align=\"center\"><font class=\"winners\">".$right[$picker];
			$boxContent .= " - ".$wrong[$picker]."</font></td><td align=\"center\"><font class=\"winners\">".ltrim(number_format($pick_pct, 3),0)."</font></td></tr>\n";
		}
		$boxContent .= "</table><br>\n";
		$boxContent .= "<i>You must have picked at least ".$ppercent."% of the available games for the week to be ";
		$boxContent .= "included in the leaders.</i><br><br>";
	} else {
		$boxContent .= "<h4>Sorry, There are no picks to display yet.   Check back later....</h4>\n";
	}
}

function WinnersAll($seasonID, $leagueID) {
	global $poolname, $testing, $top25, $seasonID, $leagueID, $usespreads, $user_prefix, $weekID, $sweekID, $lastweek, $boxTitle, $boxContent, $user, $user_id, $uname, $cookie, $prefix, $dbi, $module_name, $db, $ppercent, $today_date, $now_time;
	$boxTitle = "Everyone's picks -- $seasonID $leagueID ";
	$boxTitle .= " (".$poolname." pool) ";
	if ($usespreads == 0) { $boxTitle .= " (no spreads)"; }
	$boxTitle .= " week $weekID";
	$total_pool_games=0;
	$numweeks = 0;
	$sql = "SELECT count(*) 'count' FROM ".$user_prefix."_pool_games";
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID' AND season = '$seasonID'";
	$counting = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($counting)) {
		$gamecount = intval($row['count']);
	}
	$sql = "SELECT user_id, game, pick, week";
	$sql .= " FROM ".$prefix."_pool_picks_".$poolname;
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID' AND usespreads = '$usespreads'";
	$sql .= " AND season = '$seasonID' AND week <= $weekID";
	$result_b = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result_b)) {
		$db_week = intval($row['week']);
		$uid = intval($row['user_id']);
		$db_game = intval($row['game']);
		$db_pick[$db_week][$uid][$db_game] = $row['pick'];
		$numweeks++;
	}
	if ($numweeks > 0) {
		$sql = "SELECT user_id, username FROM ".$user_prefix."_users WHERE user_id > 1 ORDER BY user_id";
		$result = sql_query($sql, $dbi);
		while ($row = $db->sql_fetchrow($result)) {
			$db_id = intval($row['user_id']);
			$db_user[$db_id] = $row['username'];
			$db_uid[$db_user[$db_id]] = $db_id;
		}
		$sql = "SELECT home_score, visitor_score, week";
		$sql .= " FROM ".$prefix."_pool_games";
		if ($testing == '1') { $sql .= "_test"; }
		$sql .= " WHERE league = '$leagueID' AND season = '$seasonID' AND week = '$weekID'";
		if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
		if ($usespreads == '1') { $sql .= " AND (home_spread IS NOT NULL)"; }
		$result_check = sql_query($sql, $dbi);
		while ($row = $db->sql_fetchrow($result_check)) {
			$h = intval($row['home_score']);
			$v = intval($row['visitor_score']);
			$g = intval($row['week']);
			if ($h > 0 || $v >0) { $results_present = 1; }
		}
		if ($results_present == 1) {
			$lrweek = $weekID;
		} else {
			$lrweek = $weekID-1;
		}
		$thismonth = $lrweek - 4;
		$sql = "SELECT count(*) count";
		$sql .= " FROM ".$prefix."_pool_games";
		if ($testing == '1') { $sql .= "_test"; }
		$sql .= " WHERE league = '$leagueID' AND season = '$seasonID'";
		$sql .= " AND ((date < '$today_date') or (date = '$today_date' and time < '$now_time'))";
		$sql .= " AND home_score IS NOT NULL AND visitor_score IS NOT NULL";
		if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
		$fini = sql_query($sql, $dbi);
		while ($row = $db->sql_fetchrow($fini)) {
			$fini_games=$row['count'];
		}
		$total_pool_games = 0;
		$sql = "SELECT home_score, visitor_score, home_spread, week, game";
		$sql .= " FROM ".$prefix."_pool_games";
		if ($testing == '1') { $sql .= "_test"; }
		$sql .= " WHERE league = '$leagueID' AND season = '$seasonID' AND week <= '$lrweek'";
		if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
		if ($usespreads == '1') { $sql .= " AND (home_spread IS NOT NULL)"; }
		$sql .= " ORDER by week, date, game";
		$gameresults = sql_query($sql, $dbi);
		while ($row = $db->sql_fetchrow($gameresults)) {
			$total_pool_games++;
			$g_week = intval($row['week']);
			$r_week[$g_week] = $g_week;
			$g_game = intval($row['game']);
			$w_game[$g_game] = $g_game;
			$g_home_score = $row['home_score'];
			$g_visitor_score = $row['visitor_score'];
			$g_home_spread = $row['home_spread'];
			if (( $g_home_score > 0 ) or ($g_visitor_score > 0)) {
				if ($usespreads == 0) {
					$g_result = $g_home_score - $g_visitor_score;
				} else {
					$g_result = ($g_home_score - $g_visitor_score) - $g_home_spread;
				}
			} else {
				$g_result = 0;
			}
			if ($g_result > 0) {
				$g_winner[$g_week][$g_game] = "home";
			} elseif ($g_result < 0) {
				$g_winner[$g_week][$g_game] = "visitor";
			} elseif ($g_result == 0)       {
				$g_winner[$g_week][$g_game] = "push";
			} else {
				$boxContent .= "\n\n<!-- HBT - Serious Error here!!!    Check lines above 613 -->\n\n";
			}
		}
		unset ($t_right);
		unset ($t_wrong);
		unset ($push);
		unset ($games_picked);
		if (count($g_winner) > 0) {
			foreach ($db_uid as $usernum) {
				arsort ($r_week);
				foreach ($r_week as $rweek) {
					foreach ($w_game as $g_num) {
						# next line not very clean....  Fix this.
						if (( $db_pick[$rweek][$usernum][$g_num] == "home" ) || ( $db_pick[$rweek][$usernum][$g_num] == "visitor" )) {
							if ($g_winner[$rweek][$g_num] == $db_pick[$rweek][$usernum][$g_num]) {
								$right[$rweek][$usernum]++;
								$t_right[$usernum]++;
								$games_picked[$usernum]++;
							# next line not very clean....  Fix this.
							} elseif ($g_winner[$rweek][$g_num] == "push") {
								$push[$rweek][$usernum]++;
								$games_picked[$usernum]++;
							} else {
								$wrong[$rweek][$usernum]++;
								$t_wrong[$usernum]++;
								$games_picked[$usernum]++;
							}
						}
					}
					if (!($right[$rweek][$usernum])) { $right[$rweek][$usernum] = 0; }
					if (!($wrong[$rweek][$usernum])) { $wrong[$rweek][$usernum] = 0; }
				}
				if (!($t_right[$usernum])) { $t_right[$usernum] = 0; }
				if (!($t_wrong[$usernum])) { $t_wrong[$usernum] = 0; }
				if (($t_right[$usernum] + $t_wrong[$usernum]) > 0) {
					$pct[$usernum] = ltrim(($t_right[$usernum] / ($t_right[$usernum] + $t_wrong[$usernum])),0);
				} else {
					$pct[$usernum] = 0;
				}
			}
			arsort($pct);
			$i = 1;
			$rank=1;
			$t_rank=1;
			if ($fini_games == $gamecount) { $seasonover=1; }
			foreach ($pct as $picker => $pick_pct ) {
				$gp = $games_picked[$picker];
				$twpc = ($gp/$total_pool_games)*100;
				if ($twpc >= $ppercent) {
					if ($i == 1) {
						$ranking[$picker]=$rank;
						$bigwinner = $db_user[$picker];
						$bigpct = ltrim(number_format($pick_pct, 3),0);
						(($seasonover == 1 ) ? $descript = "winner" : $descript = "leader");
						$verb = "is";
					} else {
						if (ltrim(strval(number_format($pick_pct, 3)),0) === $bigpct) {
							$bigwinner .= ", ".$db_user[$picker];
							(($seasonover == 1 ) ? $descript = "winners" : $descript = "leaders");
							$verb = "are";
						}
						if ($pick_pct == $prev_pct) {
							$ranking[$picker] = $rank;
							$t_rank++;
						} else {
							$rank += $t_rank;
							$t_rank = 1;
							$ranking[$picker] = $rank;
						}
					}
					$i++;
					$prev_pct=$pick_pct;
				} else {
					unset ($pct[$picker]);
				}
			}
			if ($seasonover == 0 ) {
				$boxContent .= "<h2>The $seasonID $leagueID ".(($usespreads == 0) ? "(no spreads) " : "" )."season ".$descript." through $fini_games of $gamecount games ".$verb." $bigwinner, with a percentage of $bigpct !</h2>\n";
			} else {
				$boxContent .= "<h2>The $seasonID $leagueID ".(($usespreads == 0) ? "(no spreads) " : "" )."season ".$descript." ".$verb." $bigwinner, with a percentage of $bigpct !</h2>\n";
			}
			$boxContent .= "<center><table border=0 cellpadding=3 cellspacing=2>\n";
			$boxContent .= "<tr><td><b>Rank</b>&nbsp;&nbsp;</td><td><b>Nickname</b></td><td align=\"center\"><b>Overall<br>record</b></td><td align=\"center\"><b>Overall<br>percentage</b></td>";
			foreach ($r_week as $rweek) {
				$boxContent .= "<td align=\"center\"><a href=\"modules.php?name=$module_name&amp;op=WinnersWeek&amp;seasonID=$seasonID&amp;sweekID=$rweek\">";
				$boxContent .= "week<br>$rweek</a></td>";
			}
			$boxContent .= "</tr>\n";
			foreach ($pct as $picker => $pick_pct ) {
				$real_user = $db_user[$picker];
				$real_uid = $db_uid[$real_user];
				$boxContent .= "<tr><td align=\"right\"><font class=\"winners\"><b>$ranking[$picker]</b></font></td>";
				$boxContent .= "<td nowrap><a href=\"modules.php?name=$module_name&amp;op=GraphMe&amp;seasonID=$seasonID&amp;leagueID=$leagueID&amp;graphuser=".$real_uid."\"><img src=\"images/poollogos/graph.png\" alt=\"Show Graph of ".$db_user[$picker]."'s picks\" title=\"Show Graph of ".$db_user[$picker]."'s picks\"></a>&nbsp;";
				#$boxContent .= "<b><a href=\"modules.php?name=Private_Messages&amp;file=index&amp;mode=post&amp;u=".$real_uid."\"><font class=\"winners\">".$db_user[$picker]."</font></a></b>";
				$boxContent .= "<b><font class=\"winners\">".$db_user[$picker]."</font></b>";
				$boxContent .= "</td><td align=\"center\" nowrap><font class=\"winners\">".$t_right[$picker]." - ".$t_wrong[$picker]."</font></td>";
				$boxContent .= "<td align=\"center\"><font class=\"winners\">".ltrim(number_format($pick_pct, 3),0)."</font></td>";
				foreach ($r_week as $rweek) {
					$boxContent .= "<td border=\"1\" align=\"center\"><font class=\"winners\">".$right[$rweek][$picker]."-".$wrong[$rweek][$picker]."</font></td>";
				}
				$boxContent .= "</tr>\n";
			}
			$boxContent .= "</table></center><br><br>\n";
			$boxContent .= "<i>You must have picked at least ".$ppercent."% of all the games to that point in the season to be ";
			$boxContent .= "included in the overall leaders.</i><br><br>";
		} else {
			$boxContent .= "<h4>Sorry, There are no results to display yet. Check back next week....</h4>\n";
		}
	} else {
		$boxContent .= "<h4>Sorry, There are no results to display yet. Check back next week....</h4>\n";
	}
}

function ShowAllPicks($seasonID, $leagueID, $sweekID, $start_user) {
	global $poolname, $testing, $top25, $seasonID, $leagueID, $usespreads, $tot_users, $columns, $lastweek, $user_prefix, $weekID, $start_user, $boxTitle, $boxContent, $cookie, $prefix, $dbi, $module_name, $db, $network;
	$hrspan = $columns+3;
	$boxTitle = "Everyone's picks -- $seasonID $leagueID ";
	$boxTitle .= " (".$poolname." pool) ";
	if ($usespreads == 0) { $boxTitle .= " (no spreads)"; }
	$boxTitle .= " week $sweekID";
	$sql = "SELECT user_id, game, pick FROM ".$prefix."_pool_picks_".$poolname;
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE user_id > 1 AND league = '$leagueID' AND usespreads = '$usespreads'";
	$sql .= " AND season = '$seasonID' AND week='$sweekID'";
	$result = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result)) {
		$db_game = intval($row['game']);
		$user = $row['user_id'];
		$pick = $row['pick'];
		if ($pick) { $haspicks[$user] = 1; }
	}
	foreach ($haspicks as $user => $flag) {
		if ($userlist) {
			$userlist .= " or user_id = '".$user."'";
		} else {
			$userlist = "user_id = '".$user."'";
		}
	}
	$sql = "SELECT user_id, username FROM ".$user_prefix."_users WHERE (".$userlist.") ORDER BY user_id";
	$grabusertot = sql_query($sql, $dbi);
	$tot_users = 0;
	while ($row = $db->sql_fetchrow($grabusertot)) {
		$db_id = intval($row['user_id']);
		$db_user[$db_id] = $row['username'];
		$tot_users++;
		$realName[$tot_users] = $row['username'];
		$realID[$tot_users] =   $db_id;
		$userIDX[$db_id] = $tot_users;
	}
	if ($tot_users > $start_user+($columns-1)) {
		$end_user = $start_user+($columns-1);
		$moreusers = "true";
	} elseif ($tot_users >= $start_user) {
		$end_user = $tot_users;
		$moreusers = "false";
	}
	$sql = "SELECT user_id, game, pick";
	$sql .= " FROM ".$prefix."_pool_picks_".$poolname;
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID' AND usespreads = '$usespreads'";
	$sql .= " AND season = '$seasonID' AND week='$sweekID'";

	$result_b = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result_b)) {
		$db_game = intval($row['game']);
		$IDX = $userIDX[intval($row['user_id'])];
		$tpick = $row['pick'];
		$db_pick[$IDX][$db_game] = $tpick;
		if (($tpick == 'home') || ($tpick == 'visitor')) { $haspicks[$IDX] = 1;}
	}
	#$sql = "SELECT t.team_id, t.team_name, m.mascot FROM ";
	#$sql .= $prefix."_pool_teams";
	#$sql .= " t LEFT OUTER JOIN ";
	#$sql .= $prefix."_pool_teams_mascots m";
	#$sql .= " ON t.team_id = m.team_id";
	#$sql .= " WHERE league = '$leagueID'";
	$sql = "SELECT t.team_id, t.team_name, m.mascot FROM ";
	$sql .= "( SELECT t.* FROM ".$prefix."_pool_teams t NATURAL JOIN ( SELECT team_id, MAX(season) as season FROM nuke_pool_teams";
	$sql .= " WHERE season <= '$seasonID' AND league = '$leagueID' AND team_id < 990 GROUP BY team_id) latestteam )";
	$sql .= " AS t LEFT OUTER JOIN ".$prefix."_pool_teams_mascots m ON t.team_id = m.team_id";
	$result = $db->sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result)) {
		$team_id = $row['team_id'];
		$team_name[$team_id] = $row['team_name'];
		$mascot[$team_id] = $row['mascot'];
	}
	$sql = "SELECT date, time, home, home_score, home_rank, visitor,";
	$sql .= " visitor_score, visitor_rank, home_spread, game, Title, tvnetwork, tvnetwork2, neutral";
	$sql .= " FROM ".$prefix."_pool_games";
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID'";
	$sql .= " AND season = '$seasonID' AND week = '$sweekID'";
	if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
	$sql .= " ORDER by date, time, game";
	$gameresults = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($gameresults)) {
		$d_gid = intval($row['game']);
		$date[$d_gid] = $row['date'];
		$time[$d_gid] = $row['time'];
		$d_game[$d_gid] = $d_gid;
		$d_title[$d_gid] = $row['Title'];
		$d_h_id[$d_gid] = $row['home'];
		$d_home[$d_gid] = $team_name[$d_h_id[$d_gid]];
		$d_h_mascot[$d_gid] = $mascot[$row['home']];
		$d_v_id[$d_gid] = $row['visitor'];
		$d_visitor[$d_gid] = $team_name[$d_v_id[$d_gid]];
		$d_v_mascot[$d_gid] = $mascot[$row['visitor']];
		$d_home_score[$d_gid] = $row['home_score'];
		$d_visitor_score[$d_gid] = $row['visitor_score'];
		$d_home_spread[$d_gid] = $row['home_spread'];
		$home_rank[$d_gid] = $row['home_rank'];
		$visitor_rank[$d_gid] = $row['visitor_rank'];
		$tvnetwork[$d_gid] = $row['tvnetwork'];
		$tvnetwork2[$d_gid] = $row['tvnetwork2'];
		$d_neutral[$d_gid] = $row['neutral'];
		(($home_rank[$d_gid]) ? $d_h_rank[$d_gid] = "#".$home_rank[$d_gid]." " : $d_h_rank[$d_gid] = "");
		(($visitor_rank[$d_gid]) ? $d_v_rank[$d_gid] = "#".$visitor_rank[$d_gid]." " : $d_v_rank[$d_gid] = "");
		if ($usespreads == 0) {
			$g_result[$d_gid] = $d_home_score[$d_gid] - $d_visitor_score[$d_gid];
		} else {
			$g_result[$d_gid] = ($d_home_score[$d_gid] - $d_visitor_score[$d_gid]) - $d_home_spread[$d_gid];
		}
	}
	$boxContent .= "<center>";
	if ($start_user > $columns) {
		$prev_start_user = $start_user-$columns;
		$boxContent .= "<a href=\"modules.php?name=$module_name&amp;op=ShowAllPicks&amp;seasonID=$seasonID&amp;sweekID=$sweekID&amp;start_user=$prev_start_user\"><b>Previous $columns Players</b></a>";
	}
	if (($start_user > $columns) && ($moreusers == "true")) {
		$boxContent .= "&nbsp;&nbsp;&nbsp;--&nbsp;&nbsp;&nbsp;";
	}
	if ($moreusers == "true" ) {
		$nstart_user = $start_user+$columns;
		$boxContent .= "<a href=\"modules.php?name=$module_name&amp;op=ShowAllPicks&amp;seasonID=$seasonID&amp;sweekID=$sweekID&amp;start_user=$nstart_user\"><b>Next $columns Players</b></a>";
	}
	$boxContent .= "<br><br>";
	$boxContent .= "<table cellpadding=\"2\" cellspacing=\"1\">\n<tr><td></td><td></td><td></td>";
	for ($i = $start_user; $i <= $end_user; $i++) {
		$boxContent .= "<td align=\"center\"><font class=\"tinydark\">".$realName[$i]."</font></td>";
	}
	$boxContent .= "</tr>\n";
	# keep up with how many rows we print, every dozen rows we put the users names again, for clarity.
	$rows_printed=0;
	foreach ($d_game as $g_num) {
		if (($d_home_spread[$g_num]) || ($usespreads == 0)) {
			$rows_printed++;
			if (!(($d_home_score[$g_num] > 0) || ($d_visitor_score[$g_num] > 0))) { $g_result[$g_num] = 0; }
			$boxContent .= "<tr><td>";
			if (($tvnetwork[$g_num]) && ($tvnetwork[$g_num] > 0)) {
				# pushed everything into a popup in vers. 9.01
				$boxContent .= "<img src=\"/images/poollogos/tv.png\" border=0 align=\"left\" usemap=\"#tv-".$g_num."\" />";
				$popContent = "<img src=images/poollogos/tv/".$tvnetwork[$g_num].".png><br>";
				if (($tvnetwork2[$g_num]) && ($tvnetwork2[$g_num] > 0)) {
					$popContent .= "<img src=images/poollogos/tv/".$tvnetwork2[$g_num].".png><br>";
				}
				$popContent .= "<font class=tinydark>".$date[$g_num]." - ".$time[$g_num]."</font>";
				#$boxContent .= "<!-- \"$popContent\" -->";
				$boxContent .= "    <map name=\"tv-".$g_num."\">     <area shape=\"rect\" coords=\"0,0,32,31\" ";
				$boxContent .= ' onMouseOver="return overlib(\''.$popContent.'\', HAUTO, VAUTO)" onMouseOut="return nd();">';
			}
			$boxContent .= "</td><td nowrap><center>";
			if (!($d_title[$g_num] == "")) { $boxContent .= "<font class=\"gtitle\"><b>$d_title[$g_num]</b></font><br>"; }
			$boxContent .= "<font class=\"gmatchup\">";
			$boxContent .= $d_v_rank[$g_num].$d_visitor[$g_num];
			$boxContent .= (($d_neutral[$g_num] == "1") ? " vs." : " at");
			$boxContent .= "<br>".$d_h_rank[$g_num].$d_home[$g_num];
			if ($usespreads > 0) { $boxContent .= " (".$d_home_spread[$g_num].")"; }
			$boxContent .= "</font>";
			$boxContent .= "</center></td>";
			$boxContent .= "<td><b>$d_visitor_score[$g_num]&nbsp;&nbsp;<br>$d_home_score[$g_num]&nbsp;&nbsp;</b></td>";
			for ($i = $start_user; $i <= $end_user; $i++) {
				if (( $db_pick[$i][$g_num] == "home" ) || ( $db_pick[$i][$g_num] == "visitor" )) {
					if ($db_pick[$i][$g_num] == "home") {
						$pick = $d_home[$g_num];
						if ($g_result[$g_num] > 0) {
							$boxContent .= "<td align=\"center\" background='/images/green.png' width='32' valign=\"center\"><img src=\"/images/poollogos/$d_h_id[$g_num].gif\" alt=\"$d_home[$g_num]\" title=\"$d_home[$g_num] $d_h_mascot[$g_num]\"></td>";
						} elseif ($g_result[$g_num] < 0) {
							$boxContent .= "<td align=\"center\" background='/images/red.png' width='32' valign=\"center\"><img src=\"/images/poollogos/$d_h_id[$g_num].gif\" alt=\"$d_home[$g_num]\" title=\"$d_home[$g_num] $d_h_mascot[$g_num]\"></td>";
						} elseif ($g_result[$g_num] == 0) {
							if (($d_home_score[$g_num]) > 0 || ($d_visitor_score[$g_num] > 0)) {
								$boxContent .= "<td align=\"center\" width='32' background='/images/grey.png' valign=\"center\"><img src=\"/images/poollogos/$d_h_id[$g_num].gif\" alt=\"$d_home[$g_num]\" title=\"$d_home[$g_num] $d_h_mascot[$g_num]\"></td>";
							} else {
								$boxContent .= "<td align=\"center\" width='32' valign=\"center\"><img src=\"/images/poollogos/$d_h_id[$g_num].gif\" alt=\"$d_home[$g_num]\" title=\"$d_home[$g_num] $d_h_mascot[$g_num]\"></td>";
							}
						}
					} else {
						$pick = $d_visitor[$g_num];
						if ($g_result[$g_num] < 0) {
							$boxContent .= "<td align=\"center\" background='/images/green.png' width='32' valign=\"center\"><img src=\"/images/poollogos/$d_v_id[$g_num].gif\" alt=\"$d_visitor[$g_num]\" title=\"$d_visitor[$g_num] $d_v_mascot[$g_num]\"></td>";
						} elseif ($g_result[$g_num] > 0)	{
							$boxContent .= "<td align=\"center\" background='/images/red.png' width='32' valign=\"center\"><img src=\"/images/poollogos/$d_v_id[$g_num].gif\" alt=\"$d_visitor[$g_num]\" title=\"$d_visitor[$g_num] $d_v_mascot[$g_num]\"></td>";
						} elseif ($g_result[$g_num] == 0) {
							if (($d_home_score[$g_num]) > 0 || ($d_visitor_score[$g_num] > 0)) {
								$boxContent .= "<td align=\"center\" width='32' background='/images/grey.png' valign=\"center\"><img src=\"/images/poollogos/$d_v_id[$g_num].gif\" alt=\"$d_visitor[$g_num]\" title=\"$d_visitor[$g_num] $d_v_mascot[$g_num]\"></td>";
							} else {
								$boxContent .= "<td align=\"center\" width='32' valign=\"center\"><img src=\"/images/poollogos/$d_v_id[$g_num].gif\" alt=\"$d_visitor[$g_num]\" title=\"$d_visitor[$g_num] $d_v_mascot[$g_num]\"></td>";
							}
						}
					}
				} else {
					$boxContent .= "<td align=\"center\" width='32' valign=\"center\"><i>---</i></td>";
				}
			}
			$boxContent .= "</tr>\n";
			if (($rows_printed % 12) ==0) {
				$boxContent .= "<tr><td colspan=\"3\"></td>\n";
				for ($i = $start_user; $i <= $end_user; $i++) {
					#$boxContent .= "<td align=\"center\"><a href=\"modules.php?name=Private_Messages&amp;file=index&amp;mode=post&amp;u=".$realID[$i]."\"><font class=\"tinydark\">".$realName[$i]."</font></a></td>";
					$boxContent .= "<td align=\"center\"><font class=\"tinydark\">".$realName[$i]."</font></td>";
				}
				$boxContent .= "</tr>\n";
			}
		}
	}
	if ((($rows_printed % 12)*12) >10 ) {
		$boxContent .= "<tr><td colspan=\"3\"></td>\n";
		for ($i = $start_user; $i <= $end_user; $i++) {
			#$boxContent .= "<td align=\"center\"><a href=\"modules.php?name=Private_Messages&amp;file=index&amp;mode=post&amp;u=".$realID[$i]."\"><font class=\"tinydark\">".$realName[$i]."</font></a></td>";
			$boxContent .= "<td align=\"center\"><font class=\"tinydark\">".$realName[$i]."</font></td>";
		}
		$boxContent .= "</tr>\n";
	}
	$boxContent .= "</table><br><br>\n";
	if ($start_user > $columns) {
		$prev_start_user = $start_user-$columns;
		$boxContent .= "<a href=\"modules.php?name=$module_name&amp;op=ShowAllPicks&amp;seasonID=$seasonID&amp;sweekID=$sweekID&amp;start_user=$prev_start_user\"><b>Previous $columns Players</b></a>";
	}
	if (($start_user > $columns) &&($moreusers == "true")) {
		$boxContent .= "&nbsp;&nbsp;&nbsp;--&nbsp;&nbsp;&nbsp;";
	}
	if ($moreusers == "true" ) {
		$start_user = $start_user+$columns;
		$boxContent .= "<a href=\"modules.php?name=$module_name&amp;op=ShowAllPicks&amp;seasonID=$seasonID&amp;sweekID=$sweekID&amp;start_user=$start_user\"><b>Next $columns Players</b></a><br>";
	}
	$boxContent .= "</center>\n";
}

function DisplayWeek($seasonID, $leagueID, $sweekID) {
	global $poolname, $testing, $top25, $seasonID, $leagueID, $usespreads, $boxTitle, $lastweek, $boxContent, $home_wins, $home_losses, $away_wins, $away_losses, $home_ties, $away_ties, $user, $user_id, $uname, $cookie, $prefix, $dbi, $module_name, $db, $seasonover, $DST_start, $DST_end, $network, $ppercent, $p_ppercent, $thisweek_pct, $past_pct, $allpicked, $futurepicks, $future_pct, $allpicked, $allgames, $pastgames, $thisweekgames, $now_time;
	$today_date = date("Y-m-d");
	if ($leagueID == "NCAA") { 
		$lsize = '40';
		$bsize = '500';
	} else { 
		$lsize = '50'; 
		$bsize = '420';
	}
	# $now_time = date("Hi");
	$boxTitle = "<h2>$uname's $leagueID ";
	$boxTitle .= " (".$poolname." pool) ";
	if ($usespreads == 0) { $boxTitle .= " (no spreads)"; }
	$boxTitle .= " picks - $seasonID Week $sweekID</h2>";
	$boxContent .= "<form action=\"modules.php?name=$module_name&amp;op=SavePicks\" method=\"post\">";
	$boxContent .= "<input type=\"hidden\" name=\"seasonID\" value=\"".$seasonID."\">";
	$boxContent .= "<input type=\"hidden\" name=\"weekID\" value=\"".$sweekID."\">";
	unset ($db_hi);
	$sql = "SELECT game, pick FROM ".$prefix."_pool_picks_".$poolname;
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID' AND usespreads = '$usespreads' AND season = '$seasonID' AND week='$sweekID'";
	$sql .= " AND user_id = '$user_id' order by game";
	$result_b = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result_b)) {
		$db_game = $row['game'];
		$db_pick = $row['pick'];
		$db_hi[$db_game][$db_pick] = true;
	}
	$gamestopick=0;
	$gamesfrozen=0;
	$withoutspreads=0;
	$finishedgames=0;
	TeamRecords($seasonID, $leagueID, $sweekID);
	$sql = "SELECT count(game) count FROM ".$prefix."_pool_games";
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID' AND season = '$seasonID' AND week='$sweekID'";
	if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
	$sql .= " ORDER by date, time, visitor";
	$game_count = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($game_count)) {
		$wk_game_count = $row['count'];
	}
	$sql = "SELECT team_id, team_name FROM nuke_pool_teams NATURAL JOIN";
	$sql .= " ( SELECT team_id, MAX(season) as season FROM ".$prefix."_pool_teams";
	$sql .= " WHERE season <= '$seasonID' AND league = '$leagueID' AND team_id < 990";
	$sql .= " GROUP BY team_id ) latestteam";
	$result = $db->sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result)) {
		$team_id = $row['team_id'];
		$team_name[$team_id] = $row['team_name'];
	}
	PickPct($seasonID, $leagueID, $sweekID);
	# $thisweek_pct, $allpicked, $futurepicks, $future_pct
	if ($ppercent - $thisweek_pct > 0) {
		$hl_on = "<b><font color=darkred>Only ";
		$hl_off = "</font></b>";
	} else {
		$hl_on = $hl_off = '';
	}
	# This table is just for the Percentages.
	$boxContent .= "<b>You've picked $hl_on$thisweek_pct%$hl_off of the games this week.</b>	";
	if ($sweekID > 1) { $boxContent .= " and $past_pct% of the prior games.<br>\n"; }
	if ($leagueID == 'NCAA' && $top25 == '1') {
		$sql = "SELECT week FROM ".$prefix."_pool_games";
		if ($testing == '1') { $sql .= "_test"; }
		$sql .= " WHERE league = '$leagueID' AND season = '$seasonID' ORDER BY week desc limit 1";
		$thelastwk = sql_query($sql, $dbi);
		while ($row = $db->sql_fetchrow($thelastwk)) { $wk = $row['week']; }
		# we're going to re-work this during the off-season;
		# just use 30% as the average percentage of games each week that
		# feature at least one Top 25 team and go from there...
		$low_gr = 13 * ($wk - $sweekID);
		$high_gr = 25 * ($wk - $sweekID);
		$low_allgames = $pastgames+$thisweekgames+$low_gr;
		#				$boxContent .= "\n<!-- $low_allgames = $pastgames+$thisweekgames+$low_gr; -->";
		$high_allgames = $pastgames+$thisweekgames+$high_gr;
		#				$boxContent .= "\n<!-- $high_allgames = $pastgames+$thisweekgames+$high_gr; -->";
		$low_pgr = number_format((($ppercent/100)*$low_allgames)-$allpicked,0);
		#				$boxContent .= "\n<!-- $low_pgr = number_format((($ppercent/100)*$low_allgames)-$allpicked,0); -->";
		$high_pgr = number_format((($ppercent/100)*$high_allgames)-$allpicked,0);
		#				$boxContent .= "\n<!-- $high_pgr = number_format((($ppercent/100)*$high_allgames)-$allpicked,0); -->";
		$low_f_pct = number_format(($low_pgr/$low_gr)*100,1);
		$high_f_pct = number_format(($high_pgr/$high_gr)*100,1);
		$boxContent .= "You can count on between $low_gr and $high_gr games to pick for the rest of the season;<br>";
		if ((($low_f_pct >= 100)&&($high_f_pct <= 100)) && (($past_pct < $ppercent) && ($thisweek_pct < $ppercent))) {
			#					$boxContent .= "<!-- ($low_pgr, $high_pgr)) -->";
			$boxContent .= " so you'll have to pick at least $high_pgr ($high_f_pct%) more games;";
			$boxContent .= " You might not be able to pick enough games ";
		} elseif (($low_pgr <= 0) && (($past_pct < $ppercent) && ($thisweek_pct < $ppercent))) {
			$boxContent .= " so you'll have to pick between $low_pgr ($low_f_pct%) and $high_pgr ($high_f_pct%) more games";
		} else {
			$boxContent .= " so you'll have to pick between $low_pgr ($low_f_pct%) and $high_pgr ($high_f_pct%) more games";
		}
		$boxContent .= " to be in the end-of-season stats.";
	} else {
		if ($future_pct < 0) {
			$boxContent .= "You don't have to pick any more games";
		} elseif ($future_pct > 100) {
			$boxContent .= "Sorry, you won't be able to pick enough games for the rest of the year";
		} elseif ($future_pct >= 95) {
			$boxContent .= "<font color=darkred>BE CAREFULL ! &nbsp;You have very little 'wiggle room'! You must pick $future_pct% of all remaining games";
		} else {
			$boxContent .= "You have to make at least $futurepicks more picks this year, or $future_pct% of the remaining games ";
		}
		$boxContent .= " to be in the End-of-season stats.<br>";
		if ($usespreads == 1) {
			$boxContent .= "	Keep in mind that some of the remaining games might NOT have spreads, so the percentage you have to pick might actually be higher !<br>";
		}
		if ($future_pct >= 95) { $boxContent .= "</font>"; }
	}
	$boxContent .= "<table cellspacing=\"2\" border=\"1\" width=\"100%\">\n\t<tr>";
	$sql = "SELECT date, time, home, home_score, visitor,";
	$sql .= " visitor_rank, home_rank, visitor_score, home_spread, game, Title, tvnetwork, tvnetwork2, neutral";
	$sql .= " FROM ".$prefix."_pool_games";
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID'";
	$sql .= " AND season = '$seasonID' AND week='$sweekID'";
	if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
	$sql .= " ORDER by date, time, visitor";
	$result = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result)) {
		$recnum++;
		$game = $row['game'];
		$date[$game] = $row['date'];
		$time[$game] = $row['time'];
		$title[$game] = $row['Title'];
		$day = date("l", strtotime($date[$game]));
		$h_id = $row['home'];
		$home = $team_name[$h_id];
		$v_id = $row['visitor'];
		$visitor = $team_name[$v_id];
		$home_score = $row['home_score'];
		$visitor_score = $row['visitor_score'];
		$home_spread = $row['home_spread'];
		$home_rank = $row['home_rank'];
		$visitor_rank = $row['visitor_rank'];
		$tvnetwork = $row['tvnetwork'];
		$tvnetwork2 = $row['tvnetwork2'];
		$d_neutral[$d_gid] = $row['neutral'];
		$g_counter++;
		(($home_rank) ? $d_h_rank = "#".$home_rank." " : $d_h_rank = "");
		(($visitor_rank) ? $d_v_rank = "#".$visitor_rank." " : $d_v_rank = "");
		# Trying to use "proper" method of determining if score exists:
		if (!(($home_score > 0) || ($visitor_score > 0))) {
		#if (!( array_key_exists('home_score') || array_key_exists('visitor_score') )) {
			if (($date[$game] == $today_date && $time[$game] > $now_time) || ($date[$game] > $today_date)) {
				if ($home_spread || ($usespreads == 0)) {
					$gamestopick++ ;
					$boxContent .= "<td><table cellpadding=\"1\" cellspacing=\"1\" border=\"0\">";
					$boxContent .= "<tr><td colspan=\"4\">";
					if (($tvnetwork) && ($tvnetwork > 0)) {
						$boxContent .= "<img src=\"images/poollogos/tv/".$tvnetwork.".png\" alt=\"on ".$network{$tvnetwork}."\">";
					}
					if (($tvnetwork2[$g_num]) && ($tvnetwork2[$g_num] > 0)) {
						$boxContent .= "<img src=\"images/poollogos/tv/".$tvnetwork2.".png\" alt=\"on ".$network{$tvnetwork2}."\">";
					}
					$boxContent .= "\n\t<tr>";					
					$boxContent .= "</td><td colspan=\"4\" align=\"center\">";
					if (!($title[$game] == "")) { $boxContent .= "<font class=\"gtitle\"><b>$title[$game]</b></font>"; }
					$boxContent .= "</td></tr>\n";
					$boxContent .= "<tr><td valign=\"bottom\">$day,</td>";
					$boxContent .= "<td><input type=\"radio\" name=\"$game\" VALUE=\"visitor\"".($db_hi[$game]['visitor'] ? ' checked' : '')."></td>";
					# Add the popup for the schedule...
					$boxContent .= "<td><img src=\"/images/poollogos/".$v_id.".gif\" border=0 align=\"left\" usemap=\"#".$v_id."\" />";
					$boxContent .= "    <map name=\"".$v_id."\">     <area shape=\"rect\" coords=\"0,0,".$lsize.",".$lsize."\" ";
					$boxContent .=" href=\"modules.php?name=TeamSchedule-".$leagueID."&amp;seasonID=$seasonID&amp;team=$v_id\" target=\"_schedule\"\n";
					$boxContent .= ' onMouseOver="return overlib(\'<iframe src=modules.php?name=SchedPop&amp;leagueID='.$leagueID.'&amp;seasonID='.$seasonID.'&amp;team='.$v_id.' height='.$bsize.'></iframe>\', HAUTO, VAUTO)" onMouseOut="return nd();">';
					$boxContent .= "</td>";
					if ($v_id < 900) {
						$record = " (".intval($home_wins[$v_id]+$away_wins[$v_id])."-".intval($home_losses[$v_id]+$away_losses[$v_id]);
						if ( $home_ties[$v_id] > 0 || $away_ties[$v_id] > 0 ) { $record .= "-".intval($home_ties[$v_id]+$away_ties[$v_id]); }
						$record .= ", road: ".intval($away_wins[$v_id])."-".intval($away_losses[$v_id]);
						if ( $away_ties[$v_id] > 0 ) { $record .= "-".intval($away_ties[$v_id]); }
						$record .= ")";
					} else {
						$record = "&nbsp;";
					}
					$boxContent .= "<td><font class=\"gmatchup\"><b>$d_v_rank$visitor</b><br>$record</font></td></tr>\n";
					$boxContent .= "<tr><td align=\"center\" valign=\"top\">$date[$game]<br>$time[$game]";
					list($y,$mo,$d) = explode("-",$date[$game]);
					$h = substr($time[$game],0,2);
					$mi = substr($time[$game],2);
					$DST = localtime(mktime($h,$mi,0,$mo,$d,$y));
					$game_day = intval($DST[7]+1);
					#$boxContent .= "<!-- game_day = \'$game_day\', DST_start=\'$DST_start\', DST_end=\'$DST_end\' -->\n";			  # testing
					((($game_day > $DST_start) && ($game_day < $DST_end)) ? $timestring = "CDT" : $timestring = "CST" );
					$boxContent .= " $timestring</td>";
					$boxContent .= "<td><input type=\"radio\" name=\"$game\" VALUE=\"home\"".($db_hi[$game]['home'] ? ' checked' : '')."></td>";
					# Add the popup for the schedule...
					$boxContent .= "<td><img src=\"/images/poollogos/".$h_id.".gif\" border=0 align=\"left\" usemap=\"#".$h_id."\" />";
					$boxContent .= "    <map name=\"".$h_id."\">     <area shape=\"rect\" coords=\"0,0,".$lsize.",".$lsize."\" ";
					$boxContent .=" href=\"modules.php?name=TeamSchedule-".$leagueID."&amp;seasonID=$seasonID&amp;team=$h_id\" target=\"_schedule\"\n";
					$boxContent .= ' onMouseOver="return overlib(\'<iframe src=modules.php?name=SchedPop&amp;leagueID='.$leagueID.'&amp;seasonID='.$seasonID.'&amp;team='.$h_id.' height='.$bsize.'></iframe>\', HAUTO, VAUTO)" onMouseOut="return nd();">';
					$boxContent .= "</td>";
					if ($h_id < 900) {
						$record = " (".intval($home_wins[$h_id]+$away_wins[$h_id])."-".intval($home_losses[$h_id]+$away_losses[$h_id]);
						if ( $home_ties[$h_id] > 0 || $away_ties[$h_id] > 0 ) { $record .= "-".intval($home_ties[$h_id]+$away_ties[$h_id]); }
						$record .= ", home: ".intval($away_wins[$h_id])."-".intval($away_losses[$h_id]);
						if ( $home_ties[$h_id] > 0 ) { $record .= "-".intval($home_ties[$h_id]); }
						$record .= ")";
					} else {
						$record = "&nbsp;";
					}
					$boxContent .= "<td><font class=\"gmatchup\">";
					$boxContent .= (($d_neutral[$g_num] == "1") ? " vs." : " at");
					$boxContent .= " <b>$d_h_rank$home</b><br>$record</font></td><td><font class=\"content\">";
					if ($usespreads > 0 ) { $boxContent .= "<i>($home_spread)</i>"; }
					$boxContent .= "\n\t</tr>";
					$boxContent .= "</font></td></tr>";
					$boxContent .= "<tr><td></td><td><input type=\"radio\" name=\"$game\" VALUE=\"none\"</td></tr></table></td>\n";
				} else {
					$withoutspreads++ ;
					$boxContent .= "<td><table cellpadding=\"1\" cellspacing=\"1\">";
					$boxContent .= "<tr><td colspan=\"4\">";
					if (($tvnetwork) && ($tvnetwork > 0)) {
						$boxContent .= "<img src=\"images/poollogos/tv/".$tvnetwork.".png\" alt=\"on ".$network{$tvnetwork}."\">";
					}
					if (($tvnetwork2) && ($tvnetwork2 > 0)) {
						$boxContent .= "<img src=\"images/poollogos/tv/".$tvnetwork2.".png\" alt=\"on ".$network{$tvnetwork2}."\">";
					}
					$boxContent .= "</td><td colspan=\"4\" align=\"center\">";
					if (!($title[$game] == "")) { $boxContent .= "<font class=\"gtitle\"><b>$title[$game]</b><font>"; }
					$boxContent .= "</td></tr>\n";
					$boxContent .= "<tr><td valign=\"bottom\">$day,</td><td></td>";
					if ($v_id < 900) {
						$record = " (".intval($home_wins[$v_id]+$away_wins[$v_id])."-".intval($home_losses[$v_id]+$away_losses[$v_id]);
						if ( $home_ties[$v_id] > 0 || $away_ties[$v_id] > 0 ) { $record .= "-".intval($home_ties[$v_id]+$away_ties[$v_id]); }
						$record .= ", road: ".intval($away_wins[$v_id])."-".intval($away_losses[$v_id]);
						if ( $away_ties[$v_id] > 0 ) { $record .= "-".intval($away_ties[$v_id]); }
						$record .= ")";
					} else {
						$record = "&nbsp;";
					}
					# Add the popup for the schedule...
					$boxContent .= "<td><img src=\"/images/poollogos/".$v_id.".gif\" border=0 align=\"left\" usemap=\"#".$v_id."\" />";
					$boxContent .= "    <map name=\"".$v_id."\">     <area shape=\"rect\" coords=\"0,0,".$lsize.",".$lsize."\" ";
					$boxContent .=" href=\"modules.php?name=TeamSchedule-".$leagueID."&amp;seasonID=$seasonID&amp;team=$v_id\" target=\"_schedule\"\n";
					$boxContent .= ' onMouseOver="return overlib(\'<iframe src=modules.php?name=SchedPop&amp;leagueID='.$leagueID.'&amp;seasonID='.$seasonID.'&amp;team='.$v_id.' height='.$bsize.'></iframe>\', HAUTO, VAUTO)" onMouseOut="return nd();">';
					$boxContent .= "</td>";
					$boxContent .= "<td><font class=\"gmatchup\"><b>$d_v_rank$visitor</b><br>$record</font></td></tr>\n";
					$boxContent .= "<tr><td align=\"center\" valign=\"top\">$date[$game]<br>$time[$game]";
					list($y,$mo,$d) = explode("-",$date[$game]);
					$h = substr($time[$game],0,2);
					$mi = substr($time[$game],2);
					$DST = localtime(mktime($h,$mi,0,$mo,$d,$y));
					$game_day = intval($DST[7]+1);
					# $boxContent .= "<!-- game_day = \'$game_day\', DST_start=\'$DST_start\', DST_end=\'$DST_end\' -->\n";			 # testing
					((($game_day > $DST_start) && ($game_day < $DST_end)) ? $timestring = "CDT" : $timestring = "CST" );
					$boxContent .= " $timestring</td><td></td>";
					# Add the popup for the schedule...
					$boxContent .= "<td><img src=\"/images/poollogos/".$h_id.".gif\" border=0 align=\"left\" usemap=\"#".$h_id."\" />";
					$boxContent .= "    <map name=\"".$h_id."\">     <area shape=\"rect\" coords=\"0,0,".$lsize.",".$lsize."\" ";
					$boxContent .=" href=\"modules.php?name=TeamSchedule-".$leagueID."&amp;seasonID=$seasonID&amp;team=$h_id\" target=\"_schedule\"\n";
					$boxContent .= ' onMouseOver="return overlib(\'<iframe src=modules.php?name=SchedPop&amp;leagueID='.$leagueID.'&amp;seasonID='.$seasonID.'&amp;team='.$h_id.' height='.$bsize.'></iframe>\', HAUTO, VAUTO)" onMouseOut="return nd();">';
					$boxContent .= "</td>";
					if ($h_id < 900) {
						$record = " (".intval($home_wins[$h_id]+$away_wins[$h_id])."-".intval($home_losses[$h_id]+$away_losses[$h_id]);
						if ( $home_ties[$h_id] > 0 || $away_ties[$h_id] > 0 ) { $record .= "-".intval($home_ties[$h_id]+$away_ties[$h_id]); }
						$record .= ", home: ".intval($home_wins[$h_id])."-".intval($home_losses[$h_id]);
						if ( $home_ties[$h_id] > 0 ) { $record .= "-".intval($home_ties[$h_id]); }
						$record .= ")";
					} else {
						$record = "&nbsp;";
					}
					$boxContent .= "<td><font class=\"gmatchup\">";
					$boxContent .= (($d_neutral[$g_num] == "1") ? " vs." : " at");
					$boxContent .= " <b>$d_h_rank$home</b><br>$record</font></td><td></td></tr></table></td>\n";
				}
			} else {
				$gamesfrozen++ ;
				$boxContent .= "<td><table cellpadding=\"1\" cellspacing=\"1\">";
				if (!($title[$game] == "")) { $boxContent .= "<tr><td colspan=\"5\" align=\"center\"><font class=\"gtitle\"><b>$title[$game]</b></font></td></tr>\n"; }
				$boxContent .= "<tr><td valign=\"bottom\">$day,</td><td>".($db_hi[$game]['visitor'] ? "<img src=\"images/poollogos/check.png\">" : '')."</td>";
				# Add the popup for the schedule...
				$boxContent .= "<td><img src=\"/images/poollogos/".$v_id.".gif\" border=0 align=\"left\" usemap=\"#".$v_id."\" />";
				$boxContent .= "    <map name=\"".$v_id."\">     <area shape=\"rect\" coords=\"0,0,".$lsize.",".$lsize."\" ";
				$boxContent .=" href=\"modules.php?name=TeamSchedule-".$leagueID."&amp;seasonID=$seasonID&amp;team=$v_id\" target=\"_schedule\"\n";
				$boxContent .= ' onMouseOver="return overlib(\'<iframe src=modules.php?name=SchedPop&amp;leagueID='.$leagueID.'&amp;seasonID='.$seasonID.'&amp;team='.$v_id.' height='.$bsize.'></iframe>\', HAUTO, VAUTO)" onMouseOut="return nd();">';
				$boxContent .= "</td>";
				if ($v_id < 900) {
					$record = " (".intval($home_wins[$v_id]+$away_wins[$v_id])."-".intval($home_losses[$v_id]+$away_losses[$v_id]);
					if ( $home_ties[$v_id] > 0 || $away_ties[$v_id] > 0 ) { $record .= "-".intval($home_ties[$v_id]+$away_ties[$v_id]); }
					$record .= ", road: ".intval($away_wins[$v_id])."-".intval($away_losses[$v_id]);
					if ( $away_ties[$v_id] > 0 ) { $record .= "-".intval($away_ties[$v_id]); }
					$record .= ")";
				} else {
					$record = "&nbsp;";
				}
				$boxContent .= "<td><font class=\"gmatchup\"><b>$d_v_rank$visitor</b><br>$record</font></td></tr>\n";
				$boxContent .= "<tr><td align=\"center\" valign=\"top\">$date[$game]<br>$time[$game]";
				list($y,$mo,$d) = explode("-",$date[$game]);
				$h = substr($time[$game],0,2);
				$mi = substr($time[$game],2);
				$DST = localtime(mktime($h,$mi,0,$mo,$d,$y));
				$game_day = intval($DST[7]+1);
				 #		       $boxContent .= "<!-- game_day = \'$game_day\', DST_start=\'$DST_start\', DST_end=\'$DST_end\' -->\n";			  # testing
				((($game_day > $DST_start) && ($game_day < $DST_end)) ? $timestring = "CDT" : $timestring = "CST" );
				$boxContent .= " $timestring</td><td>".($db_hi[$game]['home'] ? "<img src=\"images/poollogos/check.png\">" : '')."</td>";
				# Add the popup for the schedule...
				$boxContent .= "<td><img src=\"/images/poollogos/".$h_id.".gif\" border=0 align=\"left\" usemap=\"#".$h_id."\" />";
				$boxContent .= "    <map name=\"".$h_id."\">     <area shape=\"rect\" coords=\"0,0,".$lsize.",".$lsize."\" ";
				$boxContent .=" href=\"modules.php?name=TeamSchedule-".$leagueID."&amp;seasonID=$seasonID&amp;team=$h_id\" target=\"_schedule\"\n";
				$boxContent .= ' onMouseOver="return overlib(\'<iframe src=modules.php?name=SchedPop&amp;leagueID='.$leagueID.'&amp;seasonID='.$seasonID.'&amp;team='.$h_id.' height='.$bsize.'></iframe>\', HAUTO, VAUTO)" onMouseOut="return nd();">';
				$boxContent .= "</td>";
				if ($h_id < 900) {
					$record = " (".intval($home_wins[$h_id]+$away_wins[$h_id])."-".intval($home_losses[$h_id]+$away_losses[$h_id]);
					if ( $home_ties[$h_id] > 0 || $away_ties[$h_id] > 0 ) { $record .= "-".intval($home_ties[$h_id]+$away_ties[$h_id]); }
					$record .= ", home: ".intval($home_wins[$h_id])."-".intval($home_losses[$h_id]);
					if ( $home_ties[$h_id] > 0 ) { $record .= "-".intval($home_ties[$h_id]); }
					$record .= ")";
				} else {
					$record = "&nbsp;";
				}
				$boxContent .= "<td><font class=\"gmatchup\">";
				$boxContent .= (($d_neutral[$g_num] == "1") ? " vs." : " at");
				$boxContent .= " <b>$d_h_rank$home</b><br>$record</font></td><td><font class=\"content\">";
				if ($usespreads > 0 ) { $boxContent .= "<i>($home_spread)</i>"; }
				$boxContent .= "</font></td></tr></table></td>\n";
			}
		} else {
			$finishedgames++ ;
			$hbh=$heh=$vbh=$veh="";
			if ($usespreads == 0) {
				$g_result = ($home_score - $visitor_score);
			} else {
				$g_result = ($home_score - $visitor_score) - ($home_spread);
			}
			if ($g_result > 0) {
				$hbh="<b>";
				$heh="</b>";
				if ($db_hi[$game]['home']) {
					$td_bg = "green";
				} elseif ($db_hi[$game]['visitor']) {
					$td_bg = "red";
				} else {
					$td_bg = "white";
				}
			} elseif ($g_result < 0) {
				$vbh="<b>";
				$veh="</b>";
				if ($db_hi[$game]['visitor']) {
					$td_bg = "green";
				} elseif ($db_hi[$game]['home']) {
					$td_bg = "red";
				} else {
					$td_bg = "white";
				}
			} elseif ($g_result == 0) {
				$td_bg = "grey";
			}
			$boxContent .= "<td background=\"/images/$td_bg.png\"><table cellpadding=\"1\" cellspacing=\"1\">";
			if (!($title[$game] == "")) { $boxContent .= "<tr><td colspan=\"5\" align=\"center\"><font class=\"gtitle\"><b>$title[$game]</b></font></td></tr>\n"; }
			$boxContent .= "<tr><td valign=\"bottom\">$day,</td><td>".$vbh."<font class=\"gscore\">$visitor_score</font>".$veh."</td>";
			# Add the popup for the schedule...
			$boxContent .= "<td><img src=\"/images/poollogos/".$v_id.".gif\" border=0 align=\"left\" usemap=\"#".$v_id."\" />";
			$boxContent .= "    <map name=\"".$v_id."\">     <area shape=\"rect\" coords=\"0,0,".$lsize.",".$lsize."\" ";
			$boxContent .=" href=\"modules.php?name=TeamSchedule-".$leagueID."&amp;seasonID=$seasonID&amp;team=$v_id\" target=\"_schedule\"\n";
			$boxContent .= ' onMouseOver="return overlib(\'<iframe src=modules.php?name=SchedPop&amp;leagueID='.$leagueID.'&amp;seasonID='.$seasonID.'&amp;team='.$v_id.' height='.$bsize.'></iframe>\', HAUTO, VAUTO)" onMouseOut="return nd();">';
			$boxContent .= "</td>";
			if ($v_id < 900) {
				$record = " (".intval($home_wins[$v_id]+$away_wins[$v_id])."-".intval($home_losses[$v_id]+$away_losses[$v_id]);
				if ( $home_ties[$v_id] > 0 || $away_ties[$v_id] > 0 ) { $record .= "-".intval($home_ties[$v_id]+$away_ties[$v_id]); }
				$record .= ", road: ".intval($away_wins[$v_id])."-".intval($away_losses[$v_id]);
				if ( $away_ties[$v_id] > 0 ) { $record .= "-".intval($away_ties[$v_id]); }
				$record .= ")";
			} else {
				$record = "&nbsp;";
			}
			$boxContent .= "<td>".$vbh."<font class=\"gmatchup\">$d_v_rank$visitor</font>".$veh."<br>$record</td></tr>\n";
			$boxContent .= "<tr><td align=\"center\" valign=\"top\">$date[$game]<br>$time[$game]";
			list($y,$mo,$d) = explode("-",$date[$game]);
			$h = substr($time[$game],0,2);
			$mi = substr($time[$game],2);
			$DST = localtime(mktime($h,$mi,0,$mo,$d,$y));
			$game_day = intval($DST[7]+1);
			# $boxContent .= "<!-- game_day = \'$game_day\', DST_start=\'$DST_start\', DST_end=\'$DST_end\' -->\n";			 # testing
			((($game_day > $DST_start) && ($game_day < $DST_end)) ? $timestring = "CDT" : $timestring = "CST" );
			$boxContent .= " $timestring</td><td>".$hbh."<font class=\"gscore\">$home_score</font>".$heh."</td>";
			# Add the popup for the schedule...
			$boxContent .= "<td><img src=\"/images/poollogos/".$h_id.".gif\" border=0 align=\"left\" usemap=\"#".$h_id."\" />";
			$boxContent .= "    <map name=\"".$h_id."\">     <area shape=\"rect\" coords=\"0,0,".$lsize.",".$lsize."\" ";
			$boxContent .=" href=\"modules.php?name=TeamSchedule-".$leagueID."&amp;seasonID=$seasonID&amp;team=$h_id\" target=\"_schedule\"\n";
			$boxContent .= ' onMouseOver="return overlib(\'<iframe src=modules.php?name=SchedPop&amp;leagueID='.$leagueID.'&amp;seasonID='.$seasonID.'&amp;team='.$h_id.' height='.$bsize.'></iframe>\', HAUTO, VAUTO)" onMouseOut="return nd();">';
			$boxContent .= "</td>";
			if ($h_id < 900) {
				$record = " (".intval($home_wins[$h_id]+$away_wins[$h_id])."-".intval($home_losses[$h_id]+$away_losses[$h_id]);
				if ( $home_ties[$h_id] > 0 || $away_ties[$h_id] > 0 ) { $record .= "-".intval($home_ties[$h_id]+$away_ties[$h_id]); }
				$record .= ", road: ".intval($away_wins[$h_id])."-".intval($away_losses[$h_id]);
				if ( $home_ties[$h_id] > 0 ) { $record .= "-".intval($home_ties[$h_id]); }
				$record .= ")";
			} else {
				$record = "&nbsp;";
			}
			$boxContent .= "<td>".$hbh."<font class=\"gmatchup\">";
			$boxContent .= (($d_neutral[$g_num] == "1") ? " vs." : " at");
			$boxContent .= " $d_h_rank$home</font>".$heh."<br>$record</td><td><font class=\"content\">";
			if ($usespreads > 0 ) { $boxContent .= "<i>($home_spread)</i>"; }
			$boxContent .= "</font></td></tr></table></td>\n";
		}
		if ($recnum%3 == 0) {
			$boxContent .= "</tr>\n<tr>";
		} else {
			$boxContent .= "\n";
		}
	}
	$boxContent .= "</td></tr></table><br><center>\n";
	$boxContent .= "<input type=\"submit\" VALUE=\"Submit\">\n";
	$boxContent .= "</center></form><br>\n";
	if ($finishedgames > 0) {
		$boxContent .= "<tr><td colspan=12><i>Winning teams ";
		if ($usespreads > 0) { $boxContent .= "against the spread "; }
		$boxContent .= "are in </i><b>bold</b><i>, ";
		$boxContent .= "your picks are highlighted in Green if correct, Red if wrong, White if you didn't make a pick";
		if ($usespreads > 0) { $boxContent .= ", and both teams are greyed out in the event of a push"; }
		$boxContent .= ".</i></td></tr></table><br><br>\n";
	}
	if ($gamesfrozen == 1) {
		$boxContent .= "One of your games is past the pick deadline, your pick is marked with a \"<img src=\"images/poollogos/check.png\">\".<br><br>\n";
	} elseif ($gamesfrozen > 1) {
		$boxContent .= "$gamesfrozen of your games are past the pick deadline, your picks are marked with a \"<img src=\"images/poollogos/check.png\">\".<br><br>\n";
	}
	if ($withoutspreads > 0) {
		$boxContent .= "As soon as the Spreads are posted, You'll have the chance to pick your team.<br><br>\n";
	}
	if ($gamestopick > 0) {
		$boxContent .= "If you change your mind, you can ";
		$boxContent .= "come back and change any of your picks up until the start of that particular game...<br>";
		$boxContent .= "Once you've saved a choice, it'll be filled in on the form when you bring it up.<br>\n";
	}
	$boxContent .= "</table>";
}

function Trivia($seasonID) {
	global $poolname, $testing, $top25, $seasonID, $leagueID, $usespreads, $user_prefix, $weekID, $lastweek, $boxTitle, $boxContent, $user, $user_id, $uname, $cookie, $prefix, $dbi, $module_name, $db;
	$today_date = date("Y-m-d");
	$sql = "SELECT date,time,home,home_score,visitor,visitor_rank,home_rank,visitor_score,home_spread,game";
	$sql .= " FROM ".$prefix."_pool_games";
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID' AND season = '$seasonID'";
	if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
	$sql .= " ORDER by date, time, visitor";
	#			$boxContent .= "\n<!-- sql = '$sql' -->\n";
	$result = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result)) {
		$game = $row['game'];
		$date[$game] = $row['date'];
		$time[$game] = $row['time'];
		$day = date("l", strtotime($date[$game]));
		$home = $row['home'];
		$visitor = $row['visitor'];
		$home_score = $row['home_score'];
		$visitor_score = $row['visitor_score'];
		$home_spread = $row['home_spread'];
		$h_rank = $row['home_rank'];
		$v_rank = $row['visitor_rank'];
		if ($usespreads > 0) {
			$g_result = ($home_score - $visitor_score) - ($home_spread);
		} else {
			$g_result = $home_score - $visitor_score;
		}
		if ($home_score > 0 || $visitor_score > 0) {
			if ($g_result > 0) {
				$h_victory++;
				$wins[$home]++;
				$losses[$visitor]++;
			} 
			if ($g_result < 0) {
				$v_victory++;
				$wins[$visitor]++;
				$losses[$home]++;
			}
			if ($g_result == 0) {
				$pushes++;
				$t_pushes[$visitor]++;
				$t_pushes[$home]++;
			}
			$t_v_score += $visitor_score;
			$t_h_score += $home_score;
			$t_h_spread += $home_spread;
			$t_pool_games++;
			if ($h_rank > 0 && $v_rank > 0) {
				$b_ranked_count++;
				if ($v_rank > $h_rank) {
					$b_hhr++;
					if ($g_result > 0) { $b_hhr_win++; }
					if ($g_result < 0) { $b_hhr_loss++; }
					if ($g_result == 0) { $b_hhr_push++; }
				}
				if ($v_rank < $h_rank) {
					$b_vhr++;
					if ($g_result > 0) { $b_vhr_win++; }
					if ($g_result < 0) { $b_vhr_loss++; }
					if ($g_result == 0) { $b_vhr_push++; }
				}
			}
			if (($h_rank > 0 && $v_rank < 1 ) || ($h_rank < 1 && $v_rank > 0)){
				$ranked_count++;
				if (!($h_rank)) {$h_rank=26; }
				if (!($v_rank)) {$v_rank=26; }
				if ($v_rank > $h_rank) {
					$hhr++;
					if ($g_result > 0) { $hhr_win++; }
					if ($g_result < 0) { $hhr_loss++; }
					if ($g_result == 0) { $hhr_push++; }
				}
				if ($v_rank < $h_rank) {
					$vhr++;
					if ($g_result > 0) { $vhr_win++; }
					if ($g_result < 0) { $vhr_loss++; }
					if ($g_result == 0) { $vhr_push++; }
				}
			}
		}
	}
	if (($h_victory > 0 ) || ($v_victory > 0) || ($pushes > 0)) {
		foreach ($wins as $team => $tw) {
			if (!($t_pushes[$team])) {$t_pushes[$team] = "0";}
			if (!($losses[$team])) {$losses[$team] = "0";}
			$team_pct[$team] = $tw/($tw+$losses[$team]+$t_pushes[$team]);
		}
		foreach ($losses as $team => $tl) {
			if (!($wins[$team])) {$wins[$team] = "0";}
			if (!($t_pushes[$team])) {$t_pushes[$team] = "0";}
			$team_pct[$team] = $wins[$team]/($wins[$team]+$tl+$t_pushes[$team]);
		}
		foreach ($t_pushes as $team => $tp) {
			if (!($wins[$team])) {$wins[$team] = "0";}
			if (!($losses[$team])) {$losses[$team] = "0";}
			$team_pct[$team] = $wins[$team]/($wins[$team]+$losses[$team]+$tp);
		}
		arsort($team_pct);
		$avg_h_score = strval(number_format(($t_h_score / $t_pool_games), 1));
		$avg_v_score = strval(number_format(($t_v_score / $t_pool_games), 1));
		$avg_spread = strval(number_format(($t_h_spread / $t_pool_games), 1));
		$sql = "SELECT game, week, pick FROM ".$prefix."_pool_picks_".$poolname;
		if ($testing == '1') { $sql .= "_test"; }
		$sql .= " WHERE league = '$leagueID' AND usespreads = '$usespreads' AND season = '$seasonID'";
		$sql .= " ORDER BY week, game";
		$result_b = sql_query($sql, $dbi);
		while ($row = $db->sql_fetchrow($result_b)) {
			$db_game = $row['game'];
			$db_pick = $row['pick'];
			$db_week = $row['week'];
			if ($db_pick == 'home') {
				$h_pool_picks[$week]++;
				$t_h_pool_picks++;
			} elseif ($db_pick == 'visitor') {
				$v_pool_picks[$week]++;
				$t_v_pool_picks++;
			}
		}
		#$sql = "SELECT team_id, team_name FROM ".$prefix."_pool_teams";
		#$sql .= " WHERE league = '$leagueID'";
		$sql = "SELECT team_id, team_name FROM nuke_pool_teams NATURAL JOIN";
		$sql .= " ( SELECT team_id, MAX(season) as season FROM ".$prefix."_pool_teams";
		$sql .= " WHERE season <= '$seasonID' AND league = '$leagueID' AND team_id < 990";
		# if ($leagueID == 'NCAA') { $sql .= " AND division = 'I'"; }
		$sql .= " GROUP BY team_id ) latestteam";
		$result = $db->sql_query($sql, $dbi);
		while ($row = $db->sql_fetchrow($result)) {
			$team_id = $row['team_id'];
			$team_name[$team_id] = $row['team_name'];
		}
		$t_pool_picks = $t_h_pool_picks + $t_v_pool_picks;
		$h_pct = strval(number_format((($t_h_pool_picks / $t_pool_picks) * 100), 1));
		$v_pct = strval(number_format((($t_v_pool_picks / $t_pool_picks) * 100), 1));
		$boxContent .= "<b>Through all the games so far this year...</b><br><br>\n";
		$boxContent .= "The average score was visitor $avg_v_score, home $avg_h_score.<br>\n";
		if ($usespreads > 0) {
			$boxContent .= "The average spread for the home team was $avg_spread.<br>\n";
			if (!$pushes) { $pushes = "0"; }
			$boxContent .= "The home team has beat the spread $h_victory times, visitor $v_victory times, and $pushes pushes.<br><br>\n";
			if ($leagueID == "NCAA") {
				$boxContent .= "<table border=\"1\" cellpadding=\"5\"><tr><td>\n";
				$boxContent .= "In the $ranked_count games where only one<br>team was ranked in the top 25:<br>\n";
				$boxContent .= "<table cellpadding=\"5\">\n";
				$boxContent .= "<tr><td align=\"center\">Higher ranked</td><td align=\"center\">Home</td><td align=\"center\">Visitor</td></tr>\n";
				$boxContent .= "<tr><td align=\"center\"><i>beat spread</i></td><td align=\"center\">$hhr_win</td><td align=\"center\">$vhr_win</td></tr>\n";
				$boxContent .= "<tr><td align=\"center\"><i>missed spread</i></td><td align=\"center\">$hhr_loss</td><td align=\"center\">$vhr_loss</td></tr>\n";
				$boxContent .= "<tr><td align=\"center\"><i>pushed</i></td><td align=\"center\">$hhr_push</td><td align=\"center\">$vhr_push</td></tr>\n";
				$boxContent .= "<tr><td align=\"center\"><i>total</i></td><td align=\"center\">$hhr</td><td align=\"center\">$vhr</td></tr>\n";
				$boxContent .= "</table><br>\n";
				$boxContent .= "</td><td>\n";
				$boxContent .= "In the $b_ranked_count games where both<br>teams were ranked in the top 25:<br>\n";
				$boxContent .= "<table cellpadding=\"5\">\n";
				$boxContent .= "<tr><td align=\"center\">Higher ranked</td><td align=\"center\">Home</td><td align=\"center\">Visitor</td></tr>\n";
				$boxContent .= "<tr><td align=\"center\"><i>beat spread</i></td><td align=\"center\">$b_hhr_win</td><td align=\"center\">$b_vhr_win</td></tr>\n";
				$boxContent .= "<tr><td align=\"center\"><i>missed spread</i></td><td align=\"center\">$b_hhr_loss</td><td align=\"center\">$b_vhr_loss</td></tr>\n";
				$boxContent .= "<tr><td align=\"center\"><i>pushed</i></td><td align=\"center\">$b_hhr_push</td><td align=\"center\">$b_vhr_push</td></tr>\n";
				$boxContent .= "<tr><td align=\"center\"><i>total</i></td><td align=\"center\">$b_hhr</td><td align=\"center\">$b_vhr</td></tr>\n";
				$boxContent .= "</table><br>\n";
				$boxContent .= "</td></tr></table><br>\n";
			}
		} else {
			if ($leagueID == "NCAA") {
				$boxContent .= "<table border=\"1\" cellpadding=\"5\"><tr><td>\n";
				$boxContent .= "In the $ranked_count games where only one<br>team was ranked in the top 25:<br>\n";
				$boxContent .= "<table cellpadding=\"5\">\n";
				$boxContent .= "<tr><td align=\"center\">Higher ranked</td><td align=\"center\">Home</td><td align=\"center\">Visitor</td></tr>\n";
				$boxContent .= "<tr><td align=\"center\"><i>Won</i></td><td align=\"center\">$hhr_win</td><td align=\"center\">$vhr_win</td></tr>\n";
				$boxContent .= "<tr><td align=\"center\"><i>Lost</i></td><td align=\"center\">$hhr_loss</td><td align=\"center\">$vhr_loss</td></tr>\n";
				$boxContent .= "<tr><td align=\"center\"><i>total</i></td><td align=\"center\">$hhr</td><td align=\"center\">$vhr</td></tr>\n";
				$boxContent .= "</table><br>\n";
				$boxContent .= "</td><td>\n";
				$boxContent .= "In the $b_ranked_count games where both<br>teams were ranked in the top 25:<br>\n";
				$boxContent .= "<table cellpadding=\"5\">\n";
				$boxContent .= "<tr><td align=\"center\">Higher ranked</td><td align=\"center\">Home</td><td align=\"center\">Visitor</td></tr>\n";
				$boxContent .= "<tr><td align=\"center\"><i>Won</i></td><td align=\"center\">$b_hhr_win</td><td align=\"center\">$b_vhr_win</td></tr>\n";
				$boxContent .= "<tr><td align=\"center\"><i>Lost</i></td><td align=\"center\">$b_hhr_loss</td><td align=\"center\">$b_vhr_loss</td></tr>\n";
				$boxContent .= "<tr><td align=\"center\"><i>total</i></td><td align=\"center\">$b_hhr</td><td align=\"center\">$b_vhr</td></tr>\n";
				$boxContent .= "</table><br>\n";
				$boxContent .= "</td></tr></table><br>\n";
			}
		}
		$boxContent .= "The pool players picked the home team $h_pct% of the time, visitor $v_pct%.<br><br>\n";
		# Percentage of the group !
		$sql = "SELECT user_id, username FROM ".$user_prefix."_users";
		$sql .= " WHERE user_id > 1 ORDER BY user_id";
		$ures = sql_query($sql, $dbi);
		while ($row = $db->sql_fetchrow($ures)) {
			$db_id = intval($row['user_id']);
			$db_user[$db_id] = $row['username'];
			$db_uid[$db_user[$db_id]] = $db_id;
		}
		$sql = "SELECT week, user_id, game, pick FROM ".$prefix."_pool_picks_".$poolname;
		if ($testing == '1') { $sql .= "_test"; }
		$sql .= " WHERE league = '$leagueID' AND usespreads = '$usespreads'";
		$sql .= " AND season = '$seasonID' AND week <= $weekID";
		$gres = sql_query($sql, $dbi);
		while ($row = $db->sql_fetchrow($gres)) {
			$all_week = intval($row['week']);
			$all_uid = intval($row['user_id']);
			$all_game = intval($row['game']);
			$allpicks[$all_week][$all_uid][$all_game] = $row['pick'];
			$thispick = $allpicks[$all_week][$all_uid][$all_game];
		}
		$sql = "SELECT home_score, visitor_score, home_spread, week, game";
		$sql .= " FROM ".$prefix."_pool_games ";
		if ($testing == '1') { $sql .= "_test"; }
		$sql .= " WHERE league = '$leagueID' AND season = '$seasonID' AND week <= '$weekID'";
		if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
		$sql .= " ORDER by week, date, game";
		$gameresults = sql_query($sql, $dbi);
		while ($row = $db->sql_fetchrow($gameresults)) {
			$total_pool_games++;
			$g_week = intval($row['week']);
			$r_week[$g_week] = $g_week;
			$g_game = intval($row['game']);
			$w_game[$g_game] = $g_game;
			$g_home_score = $row['home_score'];
			$g_visitor_score = $row['visitor_score'];
			$g_home_spread = $row['home_spread'];
			if (( $g_home_score > 0 ) or ($g_visitor_score > 0)) {
				if ($usespreads == 0) {
					$g_result = $g_home_score - $g_visitor_score;
				} else {
					$g_result = ($g_home_score - $g_visitor_score) - $g_home_spread;
				}
			} else {
				$g_result = 0;
			}
			if ($g_result > 0) {
				$g_winner[$g_week][$g_game] = "home";
			} elseif ($g_result < 0) {
				$g_winner[$g_week][$g_game] = "visitor";
			} elseif ($g_result == 0) {
				$g_winner[$g_week][$g_game] = "push";
			}
		}
		foreach ($db_uid as $usernum) {
			arsort ($r_week);
			foreach ($r_week as $rweek) {
				asort($w_game);
				foreach ($w_game as $g_num) {
					$thispick = $allpicks[$rweek][$usernum][$g_num];
					if (( $thispick == "home" ) || ( $thispick == "visitor" )) {
						$winner = $g_winner[$rweek][$g_num];
						if ($g_winner[$rweek][$g_num] == $thispick) {
							$all_right++;
						} elseif ($g_winner[$rweek][$g_num] == "push") {
							$all_push++;
						} else {
							$all_wrong++;
						}
					}
				}
			}
			if (!($all_right)) { $all_right = 0; }
			if (!($all_wrong)) { $all_wrong = 0; }
			if (!($all_push)) { $all_push = 0; }
			if (($all_right + $all_wrong +$all_push) > 0) {
				$all_pct = $all_right / ($all_right + $all_wrong);
			} else {
				$all_pct = 0;
			}
		}
		$boxContent .= "<h3>The Aggregate percentage of all pickers is ".ltrim(number_format($all_pct,3),0)." !</h3>\n";
		if ($usespreads > 0) {
			# Added to eliminate the FCS schools, which only have a few games in our data.
			if ($leagueID == 'NCAA') {
				$sql = "SELECT team_id, max(season) as season, team_name FROM `nuke_pool_conferences` ";
				$sql .= "WHERE league = 'NCAA' and division = 'I'";
				$sql .= "GROUP BY team_id)";
			}
			$boxContent .= "Best/worst teams against the spread...<br>\n";
			$boxContent .= "<table><tr><th>Team</th><th>record</th><th>Percentage</th></tr>\n";
			foreach ($team_pct as $team => $pct) {
				if (!$wins[$team]) { $wins[$team]=0; }
				if (!$losses[$team]) { $losses[$team]=0; }
				if (!$t_pushes[$team]) { $t_pushes[$team]=0; }
				$boxContent .= "<tr><td>".$team_name[$team]."&nbsp;&nbsp;&nbsp;</td><td>($wins[$team]-$losses[$team]-$t_pushes[$team])</td><td>".ltrim(number_format($pct,3),0)."</td></tr>\n";
			}
			$boxContent .= "</table><br>";
		}
	} else {
		$boxContent .= "<h4>Sorry, no results for this year.    Try back next week...</h4>\n";
	}
}
function StripLeadZero($pct) {
	return ltrim(number_format($pct,3),0);
}

function GraphMe($seasonID, $leagueID, $graphuser) {
	global $user, $dbi, $db, $prefix, $cookie, $poolname, $testing, $leagueID, $usespreads, $user_prefix, $weekID, $lastweek, $boxTitle, $boxContent, $user_id, $uname, $module_name, $top25, $seasonID;
	#			$boxContent .= "<!-- HBT Test -->\n";
	if (!($graphuser)) { $graphuser = $user_id; }
	# Here's where we put in the actual data...
	# l1 is the user, l2 is everyone else.
	# datax is the legend for the x axis.
	$sql = "SELECT user_id, game, pick, week FROM ".$prefix."_pool_picks_".$poolname;
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID' AND usespreads = '$usespreads'";
	$sql .= " AND season = '$seasonID' AND week <= '$weekID'";
	$sql .= " ORDER BY week,game";
	#			$boxContent .= "<!-- HBT Graph \$sql='$sql' -->\n";
	$result_b = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result_b)) {
		$db_week = intval($row['week']);
		if ($db_week != $oldweek) { $numweeks++; }
		$oldweek = $db_week;
		$uid = intval($row['user_id']);
		$db_game = intval($row['game']);
		$db_pick[$db_week][$uid][$db_game] = $row['pick'];
	}
	if ($numweeks > 1) {
		$sql = "SELECT user_id, username FROM ".$user_prefix."_users";
		$sql .= " WHERE user_id > 1 ORDER BY user_id";
		#				$boxContent .= "<!-- HBT Graph \$sql='$sql' -->\n";
		$result = sql_query($sql, $dbi);
		while ($row = $db->sql_fetchrow($result)) {
			$db_id = intval($row['user_id']);
			$db_user[$db_id] = $row['username'];
			$db_uid[$db_user[$db_id]] = $db_id;
		}
		$sql = "SELECT home_score, visitor_score, week";
		$sql .= " FROM ".$prefix."_pool_games";
		if ($testing == '1') { $sql .= "_test"; }
		$sql .= " WHERE league = '$leagueID' AND season = '$seasonID' AND week = '$weekID'";
		if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
		#				$boxContent .= "<!-- HBT Graph \$sql='$sql' -->\n";
		$result_check = sql_query($sql, $dbi);
		while ($row = $db->sql_fetchrow($result_check)) {
			$h = intval($row['home_score']);
			$v = intval($row['visitor_score']);
			$g = intval($row['week']);
			if ($h > 0 || $v >0) { $results_present = 1; }
		}
		if ($results_present == 1) {
			$lrweek = $weekID;
		} else {
			$lrweek = $weekID-1;
		}
		$sql = "SELECT home_score, visitor_score, home_spread, week, game";
		$sql .= " FROM ".$prefix."_pool_games";
		if ($testing == '1') { $sql .= "_test"; }
		$sql .= " WHERE league = '$leagueID' AND season = '$seasonID' AND week <= '$lrweek'";
		$sql .= " AND home_score is not NULL AND visitor_score is not NULL";
		if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
		$sql .= " ORDER by week, date, game";
		#				$boxContent .= "\n<!-- HBT Graph \$sql='$sql' -->\n";
		$gameresults = sql_query($sql, $dbi);
		while ($row = $db->sql_fetchrow($gameresults)) {
			$total_pool_games++;
			$g_week = intval($row['week']);
			$r_week[$g_week] = $g_week;
			$g_game = intval($row['game']);
			$w_game[$g_week][$g_game] = $g_game;
			$g_home_score = $row['home_score'];
			$g_visitor_score = $row['visitor_score'];
			$g_home_spread = $row['home_spread'];
			if ($usespreads == 0) {
				$g_result = $g_home_score - $g_visitor_score;
			} else {
				$g_result = ($g_home_score - $g_visitor_score) - $g_home_spread;
			}
			if ($g_result > 0) {
				$h_victory++;
				$g_winner[$g_week][$g_game] = "home";
			} elseif ($g_result < 0) {
				$v_victory++;
				$g_winner[$g_week][$g_game] = "visitor";
			} else {
				$pushes++;
				$g_winner[$g_week][$g_game] = "push";
			}
		}
		if (($h_victory > 0 ) || ($v_victory > 0) || ($pushes > 0)) {
			asort ($r_week);
			asort ($db_uid);
			foreach ($r_week as $rweek) {
				asort($w_game[$rweek]);
				$maxweek = $rweek;
				foreach ($db_uid as $usernum) {
					if ($usernum == $graphuser) {
						$g_uid = 1;
					} else {
						$g_uid = 2;
					}
					foreach ($w_game[$rweek] as $g_num) {
						if (( $db_pick[$rweek][$usernum][$g_num] == "home" ) || ( $db_pick[$rweek][$usernum][$g_num] == "visitor" )) {
							if ($g_winner[$rweek][$g_num] == $db_pick[$rweek][$usernum][$g_num]) {
								$right[$rweek][$g_uid]++;
							} elseif ($g_winner[$rweek][$g_num] == "push") {
								$push[$rweek][$g_uid]++;
							} else {
								$wrong[$rweek][$g_uid]++;
							}
						}
					}
				}
				if (!($right[$rweek][1])) { $right[$rweek][1] = 0; }
				if (!($wrong[$rweek][1])) { $wrong[$rweek][1] = 0; }
				if (!($right[$rweek][2])) { $right[$rweek][2] = 0; }
				if (!($wrong[$rweek][2])) { $wrong[$rweek][2] = 0; }
				$gindex=$rweek-1;
				if ($right[$rweek][1]+$wrong[$rweek][1] > 0) {
					$ud_pct[$gindex] = ltrim(number_format( ($right[$rweek][1] / ($right[$rweek][1]+$wrong[$rweek][1])) ,3),0);
				} else {
					$ud_pct[$gindex] = "-";
				}
				if ($right[$rweek][2]+$wrong[$rweek][2] > 0) {
					$gd_pct[$gindex] = ltrim(number_format( ($right[$rweek][2] / ($right[$rweek][2]+$wrong[$rweek][2])) ,3),0);
				} else {
					$gd_pct[$gindex] = 0;
				}
				$gd_week[$gindex] = $rweek;
			}
			include ("jpgraph/jpgraph.php");
			include ("jpgraph/jpgraph_bar.php");
			include ("jpgraph/jpgraph_line.php");
			include ("jpgraph/jpgraph_error.php");
			include ("jpgraph/jpgraph_canvtools.php");
			include ("jpgraph/jpgraph_canvas.php");
			# include ("jpgraph/jpgraph_mgraph.php");
			# Get the mundane out of the way first.
			$xtitle = "Week";
			$l1datay = $ud_pct;
			$l2datay = $gd_pct;
			$datax = $gd_week;
			$uname=$db_user[$graphuser];
			if (substr($uname,-1) == "s") {
				$title = $uname."' pick percentage against the group";
			} else {
				$title = $uname."'s pick percentage against the group";
			}
			// Create the graph.
			$graph = new Graph(640,480,"auto");
			#$graph->SetBackgroundImage("/home5/footban4/public_html/images/graphlogo.png",BGIMG_FILLPLOT);
			$graph->SetBackgroundImage("/home5/footban4/public_html/images/graphlogo.png",BGIMG_FILLPLOT);
			$graph->SetScale("textlin");
			$graph->img->SetMargin(40,20,50,60);
			$graph->yaxis->SetLabelMargin(2);
			$graph->yaxis->SetLabelFormatCallback('StripLeadZero');
			$graph->yaxis->HideFirstTicklabel();
			$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
			$graph->yaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
			$l1plot=new LinePlot($l1datay);
			$l1plot->SetColor("darkblue");
			$l1plot->SetWeight(2);
			$l1plot->SetLegend("$uname");
			$l1plot->value->SetFormatCallback('StripLeadZero');
			$l1plot->value->Show();
			$l1plot->value->SetColor('darkblue');
			$l1plot->value->SetFont(FF_ARIAL,FS_NORMAL,8);
			$l1plot->value->SetMargin(8);
			$l1plot->mark-> SetTYPE( MARK_IMG, "/home5/footban4/public_html/images/football.png", .10);
			# Create the bar plot
			$l2plot = new LinePlot($l2datay);
			$l2plot->value->SetFormatCallback('StripLeadZero');
			$l2plot->value->Show();
			$l2plot->value->SetMargin(6);
			$l2plot->value->SetColor('blue');
			$l2plot->value->SetFont(FF_VERDANA,FS_NORMAL,8);
			$l2plot->SetFillColor("#0099ff@0.5");
			$l2plot->SetLegend("everyone else");
			# Add the plots to the graph
			$graph->Add($l2plot);
			$graph->Add($l1plot);
			$graph->title->Set("$title");
			$graph->title->SetFont(FF_VERDANA,FS_NORMAL,20);
			$graph->legend->SetFont(FF_VERDANA,FS_NORMAL,10);
			$graph->yaxis->title->SetFont(FF_ARIAL);
			$graph->xaxis->title->SetFont(FF_ARIAL);
			$graph->legend->SetLayout(LEGEND_HOR);
			$graph->legend->Pos(0.5,0.97,"center","bottom");
			$t1 = new Text("Copyright $seasonID\nFootball-Pools.org");
			$t1->SetFont(FF_MARKERFINEPOINT,FS_NORMAL,12);
			$t1->SetColor("darkblue");
			$t1->SetPos(5,430);
			$graph->AddText($t1);
			$t2text = "$leagueID $poolname pool";
			if ($usespreads == 0) { $t2text .= " (no spreads)"; }
			$t2 = new Text("$t2text");
			$t2->SetFont(FF_MARKERFINEPOINT,FS_NORMAL,36);
			$t2->SetColor("darkblue@0.6");
			$t2->SetPos(50,275);
			$graph->AddText($t1);
			$graph->AddText($t2);
			# Display the graph
			$graph->Stroke("/home5/footban4/public_html/images/graphs/$uname.png");
			$boxContent .= "<img src=\"/images/graphs/$uname.png\"><br><br>\n";
		} else {
			$boxContent .= "<h4>Nothing to graph.</h4>\n";
		}
	} else {
		$boxContent .= "<h4>Sorry, the graph wouldn't make much sense after only one week.      Try back next week.</h4>\n";
	}
}

function TeamSchedule($team, $seasonID, $leagueID) {
	global $poolname, $testing, $top25, $team, $prefix, $dbi, $module_name, $db, $home_wins, $home_losses, $away_wins, $away_losses, $home_ties, $away_ties, $boxTitle, $boxContent, $debug;
	# Let's get the conference affiliations first, remembering season is relevant:
	#$sql = "SELECT c.team_id, c.team_name, c.conference, c.division, c.ncaa_div";
	#$sql .= " FROM ( select team_id, MAX(season) as season";
	#$sql .= " FROM ".$prefix."_pool_conferences WHERE season <= '$seasonID' AND league = '$leagueID' AND team_id < 990";
	#$sql .= " GROUP BY team_id ) AS x INNER JOIN ".$prefix."_pool_conferences as c on c.team_id = x.team_id AND c.season = x.season";
	$sql = "SELECT c.team_id, c.team_name, c.conference, c.division";
	if ($leagueID == 'NCAA') { $sql .= ", c.ncaa_div"; }
	$sql .= " FROM ( SELECT t.* FROM nuke_pool_teams t NATURAL JOIN ( SELECT team_id, MAX(season) as season";
	$sql .= " FROM ".$prefix."_pool_teams WHERE season <= '$seasonID' AND league = '$leagueID' AND team_id < 990";
	$sql .= " GROUP BY team_id ) latestteam ) AS x INNER JOIN ".$prefix."_pool_conferences as c on c.team_id = x.team_id";
	if ($debug > 0) {	$boxContent .= "\n<!-- HBT \$sql='$sql' -->\n"; }
	$results = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($results)) {
		# if ($debug > 0) {	$boxContent .= "\n<!. -->\n"; }
		$team_id = $row['team_id'];
		$ncaa_div[$team_id] = strval($row['ncaa_div']);
		$conference[$team_id] = strval($row['conference']);
		$division[$team_id] = strval($row['division']);
		if ($debug > 0) {	$boxContent .= "\n<!-- team_id = '".$row['team_id']."', ncaa_div = '".$row['ncaa_div']."', conference = '".$row['conference']."', division = '".$row['division']."' -->"; }
	}
	# Now get the Team info:
	#$sql = "SELECT `team_id`, `team_name`";
	#$sql .= " FROM ".$prefix."_pool_teams WHERE league = '$leagueID' AND team_id < 990";
	#$sql .= " ORDER BY team_name";
	$sql = "SELECT team_id, team_name FROM nuke_pool_teams NATURAL JOIN";
	$sql .= "( SELECT team_id, MAX(season) as season FROM ".$prefix."_pool_teams";
	$sql .= " WHERE season <= '$seasonID' AND league = '$leagueID' AND team_id < 990";
	$sql .= " GROUP BY team_id) latestteam";
	if ($debug > 0) {	$boxContent .= "\n<!-- HBT \$sql='$sql' -->\n"; }
	$results = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($results)) {
		$team_id = $row['team_id'];
		$teamname[$team_id] = $row['team_name'];
		$team_num[$row['team_name']] = $team_id;
		if ($debug > 0) {	$boxContent .= "\n<!-- team_id = '".$row['team_id']."', teamname = '".$row[team_name]."' -->"; }
	}
	# get the mascots.
	$sql = "SELECT team_id, mascot";
	$sql .= " FROM ".$prefix."_pool_teams_mascots";
	if ($testing == '1') { $sql .= "_test"; }
	$results = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($results)) {
		$mascot[$row['team_id']] = $row['mascot'];
		if ($debug > 0) {	$boxContent .= "\n<!-- team_id = '".$row['team_id']."', mascot = '".$row['mascot']."' -->"; }
	}
	# get the coaches
	if ($debug > 0) {	$boxContent .= "\n<!-- Getting Coaches -->\n"; }
	$sql = "SELECT team_id, name, interim FROM ".$prefix."_pool_coaches";
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " ORDER BY team_id, start, end";
	$results = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($results)) {
		$coach[$row['team_id']] = $row['name'];
		if ($row['interim'] == 1) { $coach[$row['team_id']] .= " <i>(interim)</i>";}
		if ($debug > 0) {	$boxContent .= "\n<!-- team_id = '".$row['team_id']."', name = '".$row['name']."' -->"; }
	}
	# throw up a form to select the team:
	$boxContent .= "<form action=\"modules.php?name=$module_name&amp;op=TeamSchedule\" method=\"post\">";
	$boxContent .= "<input type=\"hidden\" name=\"seasonID\" value=\"".$seasonID."\">";
	$boxContent .= "<input type=\"hidden\" name=\"leagueID\" value=\"".$leagueID."\">";
	$boxContent .= "<select name=\"team\">\n\t<option value=\"".$team."\">".$teamname[$team]."</option>\n";
	foreach ($teamname as $team_id => $t_name) {
		if ($leagueID == "NCAA") {
			if (($team !== $team_id) && ($ncaa_div[$team_id] == "I")) {
				$boxContent .= "\t<option value=\"".$team_id."\">".$t_name."</option>\n";
			}
		} else {
			if ($team !== $team_id) {
				$boxContent .= "\t<option value=\"".$team_id."\">".$t_name."</option>\n";
			}
		}
	}
	$boxContent .= "</select>\n";
	$boxContent .= "<input type=\"submit\" VALUE=\"Submit\">\n";
	$boxContent .= "</form>\n";
	$sql = "SELECT home, home_score, visitor, visitor_score, home_rank, visitor_rank, date, week, game";
	$sql .= " FROM ".$prefix."_pool_games";
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID' AND season = '$seasonID'";
	$sql .= " AND (home = '$team' OR visitor = '$team')";
	$sql .= " ORDER BY week";
	$results = sql_query($sql, $dbi);
	# Let's put in the Helmet and a Team name before the schedule to make it look pretty....
	# $boxContent .= "\n<center>";
	if (!$team) { $team = 0; }
	$boxContent .= "\n<table cellpadding=\"2\" border=\"1\">";
	$boxContent .= "\n\t<tr>\n\t\t<td colspan=3 valign=\"middle\" align=\"center\" bgcolor=\"white\">";
	$boxContent .= "<img src=\"images/poollogos/helmets/$team.gif\" alt=\"$teamname[$team] $mascot[$team]\" title=\"$teamname[$team] $mascot[$team]\"><br>\n";
	$boxContent .= "<h1>$teamname[$team]<br>$mascot[$team]</h1>\n";
	$boxContent .= "<h3><i>Conference:</i> $conference[$team] $division[$team]</h3>";
	$boxContent .= "<h3><i>Head Coach:</i> $coach[$team]</h3>\n";
	$boxContent .= "<tr><th>date</th><th>opponent</th><th>result</th></tr>\n";
	$wins=$losses=$ties=0;
	while ($row = $db->sql_fetchrow($results)) {
		$boxContent .= "<tr><td>".$row[date]."</td>";
		$week = $row['week']-1;
		$home_wins=$away_wins=$home_losses=$away_losses='';
		TeamRecords($seasonID, $leagueID, $week);
		$visitor = $row['visitor'];
		$home = $row['home'];
		$home_score = intval($row[home_score]);
		$visitor_score = intval($row[visitor_score]);
		if (($team == $home) && ($home_score > $visitor_score)) {
			$boxContent .= "<td><a href=\"modules.php?name=$module_name&amp;op=TeamSchedule&amp;seasonID=$seasonID&amp;leagueID=$leagueID&amp;team=".$visitor."\">";
			if ($row[visitor_rank]) { $boxContent .= "#".$row[visitor_rank]." "; }
			$record = " (".intval($home_wins[$visitor]+$away_wins[$visitor])."-".intval($home_losses[$visitor]+$away_losses[$visitor]);
			if ( intval($home_ties[$visitor]+$away_ties[$visitor]) > 0 ) { $record .= "-".intval($home_ties[$visitor]+$away_ties[$visitor]); }
			$record .= ")";
			$boxContent .= $teamname[$visitor]."</a> ".$record;
			$boxContent .= "</td><td><b>W ".$home_score." - ".$visitor_score."</b></td></tr>\n";
			$wins++;
		} elseif (($team == $visitor) && ($home_score < $visitor_score)) {
			$boxContent .= "<td>@ <a href=\"modules.php?name=$module_name&amp;op=TeamSchedule&amp;seasonID=$seasonID&amp;leagueID=$leagueID&amp;team=".$home."\">";
			if ($row[home_rank]) { $boxContent .= "#".$row[home_rank]." "; }
			$record = " (".intval($home_wins[$home]+$away_wins[$home])."-".intval($home_losses[$home]+$away_losses[$home]);
			if ( intval($home_ties[$home]+$away_ties[$home]) > 0 ) { $record .= "-".intval($home_ties[$home]+$away_ties[$home]); }
			$record .= ")";
			$boxContent .= $teamname[$home]."</a> ".$record;
			$boxContent .= "</td><td><b>W ".$visitor_score." - ".$home_score."</b></td></tr>\n";
			$wins++;
		} elseif (($team == $home) && ($home_score < $visitor_score)) {
			$boxContent .= "<td><a href=\"modules.php?name=$module_name&amp;op=TeamSchedule&amp;seasonID=$seasonID&amp;leagueID=$leagueID&amp;team=".$visitor."\">";
			if ($row[visitor_rank]) { $boxContent .= "#".$row[visitor_rank]." "; }
			$record = " (".intval($home_wins[$visitor]+$away_wins[$visitor])."-".intval($home_losses[$visitor]+$away_losses[$visitor]);
			if ( intval($home_ties[$visitor]+$away_ties[$visitor]) > 0 ) { $record .= "-".intval($home_ties[$visitor]+$away_ties[$visitor]); }
			$record .= ")";
			$boxContent .= $teamname[$visitor]."</a> ".$record;
			$boxContent .= "</td><td>L ".$home_score." - ".$visitor_score."</td></tr>\n";
			$losses++;
		} elseif (($team == $visitor) && ($home_score > $visitor_score)) {
			$boxContent .= "<td>@ <a href=\"modules.php?name=$module_name&amp;op=TeamSchedule&amp;seasonID=$seasonID&amp;leagueID=$leagueID&amp;team=".$home."\">";
			if ($row[home_rank]) { $boxContent .= "#".$row[home_rank]." "; }
			$record = " (".intval($home_wins[$home]+$away_wins[$home])."-".intval($home_losses[$home]+$away_losses[$home]);
			if ( intval($home_ties[$home]+$away_ties[$home]) > 0 ) { $record .= "-".intval($home_ties[$home]+$away_ties[$home]); }
			$record .= ")";
			$boxContent .= $teamname[$home]."</a> ".$record;
			$boxContent .= "</td><td>L ".$visitor_score." - ".$home_score."</td></tr>\n";
			$losses++;
		} elseif (($team == $home) && (($home_score > 0) && ( $home_score == $visitor_score))) {
		#} elseif (($team == $home) && (array_key_exists('home_score') && ( $home_score == $visitor_score))) {
			$boxContent .= "<td>vs. <a href=\"modules.php?name=$module_name&amp;op=TeamSchedule&amp;seasonID=$seasonID&amp;leagueID=$leagueID&amp;team=".$visitor."\">";
			if ($row[visitor_rank]) { $boxContent .= "#".$row[visitor_rank]." "; }
			$record = " (".intval($home_wins[$visitor]+$away_wins[$visitor])."-".intval($home_losses[$visitor]+$away_losses[$visitor]);
			if ( intval($home_ties[$visitor]+$away_ties[$visitor]) > 0 ) { $record .= "-".intval($home_ties[$visitor]+$away_ties[$visitor]); }
			$record .= ")";
			$boxContent .= $teamname[$visitor]."</a> ".$record;
			$boxContent .= "</td><td>T ".$visitor_score." - ".$home_score."</td></tr>\n";
			$ties++;
		} elseif (($team == $visitor) && (($visitor_score > 0) && ( $home_score == $visitor_score))) {
		#} elseif (($team == $visitor) && (array_key_exists('visitor_score') && ( $home_score == $visitor_score))) {
			$boxContent .= "<td>vs. <a href=\"modules.php?name=$module_name&amp;op=TeamSchedule&amp;seasonID=$seasonID&amp;leagueID=$leagueID&amp;team=".$home."\">";
			if ($row[home_rank]) { $boxContent .= "#".$row[home_rank]." "; }
			$record = " (".intval($home_wins[$home]+$away_wins[$home])."-".intval($home_losses[$home]+$away_losses[$home]);
			if ( intval($home_ties[$home]+$away_ties[$home]) > 0 ) { $record .= "-".intval($home_ties[$home]+$away_ties[$home]); }
			$record .= ")";
			$boxContent .= $teamname[$home]."</a> ".$record;
			$boxContent .= "</td><td>T ".$visitor_score." - ".$home_score."</td></tr>\n";
			$ties++;
		} elseif (($team == $home) && ($home_score == $visitor_score)) {
		#} elseif (($team == $home) && !(array_key_exists('home_score')) ) {
			$boxContent .= "<td><a href=\"modules.php?name=$module_name&amp;op=TeamSchedule&amp;seasonID=$seasonID&amp;leagueID=$leagueID&amp;team=".$visitor."\">";
			if ($row[visitor_rank]) { $boxContent .= "#".$row[visitor_rank]." "; }
			$record = " (".intval($home_wins[$visitor]+$away_wins[$visitor])."-".intval($home_losses[$visitor]+$away_losses[$visitor]);
			if ( intval($home_ties[$visitor]+$away_ties[$visitor]) > 0 ) { $record .= "-".intval($home_ties[$visitor]+$away_ties[$visitor]); }
			$record .= ")";
			$boxContent .= $teamname[$visitor]."</a> ".$record;
			$boxContent .= "</td><td>&nbsp</td></tr>\n";
		} elseif (($team == $visitor) && ($home_score == $visitor_score)) {
		#} elseif (($team == $visitor) && !(array_key_exists('visitor_score')) ) {
			$boxContent .= "<td>@ <a href=\"modules.php?name=$module_name&amp;op=TeamSchedule&amp;seasonID=$seasonID&amp;leagueID=$leagueID&amp;team=".$home."\">";
			if ($row[home_rank]) { $boxContent .= "#".$row[home_rank]." "; }
			$record = " (".intval($home_wins[$home]+$away_wins[$home])."-".intval($home_losses[$home]+$away_losses[$home]);
			if ( intval($home_ties[$home]+$away_ties[$home]) > 0 ) { $record .= "-".intval($home_ties[$home]+$away_ties[$home]); }
			$record .= ")";
			$boxContent .= $teamname[$home]."</a> ".$record;
			$boxContent .= "</td><td>&nbsp</td></tr>\n";
		}
	}
	$boxContent .= "<tr><td colspan=3><center><h3>Record: $wins-$losses";
	if ($ties > 0) { $boxContent .= "-".$ties; }
	$boxContent .= "</h3></td></tr>\n";
	$boxContent .= "</table>\n<br>\n";
}

function TeamRecords($seasonID, $leagueID, $weekID) {
	global $poolname, $testing, $top25, $prefix, $dbi, $module_name, $db, $home_wins, $home_losses, $away_wins, $away_losses, $home_ties, $away_ties;
	$sql2 = "SELECT home, home_score, visitor, visitor_score, week, game";
	$sql2 .= " FROM ".$prefix."_pool_games";
	if ($testing == '1') { $sql2 .= "_test"; }
	$sql2 .= " WHERE league = '$leagueID' AND season = '$seasonID' AND week <= '$weekID'";
	$sql2 .= " AND (home_score IS NOT NULL and visitor_score IS NOT NULL) ORDER BY home, week";
	$results2 = sql_query($sql2, $dbi);
	while ($row = $db->sql_fetchrow($results2)) {
		$ht = $row['home'];
		$vt = $row['visitor'];
		$hs = intval($row['home_score']);
		$vs = intval($row['visitor_score']);
		$w = $row['week'];
		$g = $row['game'];
		if ($hs > $vs) {
			$home_wins[$ht]++;
			$away_losses[$vt]++;
		} elseif ($hs < $vs) {
			$home_losses[$ht]++;
			$away_wins[$vt]++;
		#} elseif ( array_key_exists('hs') && $hs == $vs) {
		} elseif (($hs > 0) && $hs == $vs) {
			$home_ties[$ht]++;
			$away_ties[$vt]++;
		}
	}
}

function PickPct($seasonID, $leagueID, $weekID) {
	global $user_id, $usespreads, $poolname, $testing, $top25, $prefix, $dbi, $module_name, $db, $ppercent, $p_ppercent, $past_pct, $thisweek_pct, $allpicked, $futurepicks, $future_pct, $allpicked, $allgames, $allpicked, $allgames, $pastgames, $thisweekgames, $testing;
	# figure out how the user stands on the minimum percentage...
	# first get all the games.
	$sql = "SELECT week, game, home_spread";
	$sql .= " FROM ".$prefix."_pool_games";
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID'";
	$sql .= " AND season = '$seasonID'";
	if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
	$sql .= " ORDER BY week, game";
	$result = $db->sql_query($sql, $dbi);
	#			echo "<!-- \$sql='$sql' --><br>\n";
	while ($row = $db->sql_fetchrow($result)) {
		if ($row['week'] < $weekID) {
			if ($usespreads == 1) {
				if ($row['home_spread'] != '') { $pastgames++; }
			} else {
				$pastgames++;
			}
		} elseif ($row['week'] == $weekID) {
			if ($usespreads == 1) {
				if ($row['home_spread'] != '') { $thisweekgames++; }
			} else {
				$thisweekgames++;
			}
		} else {
			$futuregames++;
		}
	}
	# now get the users's picked games.
	$sql = "SELECT week, game";
	$sql .= " FROM ".$prefix."_pool_picks_".$poolname;
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID'";
	$sql .= " AND season = '$seasonID' AND user_id = '$user_id' AND usespreads = '$usespreads'";
	$sql .= " AND (pick = 'home' or pick = 'visitor')";
	$sql .= " ORDER BY week, game";
	#			echo "<!-- user's picked games \$sql='$sql' --><br>\n";
	$result = $db->sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result)) {
		if ($row['week'] < $weekID) {
			$pastpicked++;
		} elseif ($row['week'] == $weekID) {
			$thisweekpicked++;
		}
	}
	# figure past, now, and future (needed) percentages.
	$past_pct=(($pastpicked/$pastgames)*100);
	#			echo "<!-- \$past_pct=((\$pastpicked/\$pastgames)*100) --><br>\n";
	#			echo "<!-- $past_pct=(($pastpicked/$pastgames)*100) --><br>\n";
	$thisweek_pct=number_format(($thisweekpicked/$thisweekgames)*100,1);
	#			echo "<!-- \$thisweek_pct=number_format((\$thisweekpicked/\$thisweekgames)*100,1) --><br>\n";
	#			echo "<!-- $thisweek_pct=number_format(($thisweekpicked/$thisweekgames)*100,1) --><br>\n";
	$allpicked=$thisweekpicked+$pastpicked;
	#			echo "<!-- \$allpicked=\$thisweekpicked+\$pastpicked --><br>\n";
	#			echo "<!-- $allpicked=$thisweekpicked+$pastpicked --><br>\n";
	$allgames=$pastgames+$thisweekgames+$futuregames;
	#			echo "<!-- \$allgames=\$pastgames+\$thisweekgames+\$futuregames --><br>\n";
	#			echo "<!-- $allgames=$pastgames+$thisweekgames+$futuregames --><br>\n";
	if ($weekID > 1) { $past_pct=number_format(($pastpicked/$pastgames)*100,1); }
	#			echo "<!-- \$past_pct=number_format((\$pastpicked/\$pastgames)*100,1) --><br>\n";
	#			echo "<!-- $past_pct=number_format(($pastpicked/$pastgames)*100,1) --><br>\n";
	$futurepicks=number_format((($ppercent/100)*$allgames)-$allpicked,0);
	#			echo "<!-- \$futurepicks=number_format(((\$ppercent/100)*\$allgames)-\$allpicked,0) --><br>\n";
	#			echo "<!-- $futurepicks=number_format((($ppercent/100)*$allgames)-$allpicked,0) --><br>\n";
	$future_pct=number_format(($futurepicks/$futuregames)*100,1);
	#			echo "<!-- \$future_pct=number_format((\$futurepicks/\$futuregames)*100,1) --><br>\n";
	#			echo "<!-- $future_pct=number_format(($futurepicks/$futuregames)*100,1) --><br>\n";
}

function _cb_negate($aVal) {
    return round(-$aVal);
}

function GraphPos($seasonID, $leagueID) {
	global $user, $dbi, $db, $prefix, $cookie, $poolname, $testing, $leagueID, $usespreads, $user_prefix, $weekID, $lastweek, $boxTitle, $boxContent, $user_id, $uname, $module_name, $top25, $seasonID, $debug;
	if ($debug > 0) { $boxContent .= "<!-- HBT Test -->\n"; }
	if (!($graphuser)) { $graphuser = $user_id; }
	$mycolors = array("0000ff","a0a0a0","000000","ff0000","00ff00","ffff00","800000","808000","00ffff","008080","800080","ff00ff","800080","708090","a0522d","dda0dd","cd853f","ffa500","ffe4e1","0fffe0","ffb6c1","ff10ff","ff4500","ffdab9","ffc0cb","d2b48c","d8bfd8","c0c0c0","ff6347","ffe4b5","f5deb3");
	#$debug=1;
	# Find the latest week with results.
	$sql = "SELECT home_score, visitor_score, week";
	$sql .= " FROM ".$prefix."_pool_games";
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID' AND season = '$seasonID'";
	$sql .= " AND home_score IS NOT NULL AND visitor_score IS NOT NULL";
	if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
	$sql .= " ORDER BY week DESC, game DESC LIMIT 1";
	if ($debug > 0) { $boxContent .= "<!-- HBT GraphPos \$sql='$sql' -->\n"; }
	$result_check = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result_check)) {
		$lrweek = intval($row['week']);
	}
	# Here's where we put in the actual data...
	# first have to get all the users, use them for datasets
	# datax is the legend for the x axis.
	$sql = "SELECT  p.week, p.game, p.user_id, p.pick, u.username ";
	$sql .= "FROM ".$prefix."_pool_picks_".$poolname;
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " p LEFT OUTER JOIN ";
	$sql .= $user_prefix."_users";
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " u";
	$sql .= " ON p.user_id = u.user_id";
	$sql .= " WHERE p.league = '$leagueID' AND p.usespreads = '$usespreads'";
	$sql .= " AND p.season = '$seasonID' AND p.week <= '$lrweek'";
	$sql .= " ORDER BY p.week,p.game";
	if ($debug > 0) { $boxContent .= "<!-- HBT Graph \$sql='$sql' -->\n"; }
	$result_b = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result_b)) {
		$db_week = intval($row['week']);
		$oldweek = $db_week;
		$uid = $db_id = intval($row['user_id']);
		$db_game = intval($row['game']);
		$db_pick[$db_week][$uid][$db_game] = $row['pick'];
		# added 
		$db_user[$db_id] = $row['username'];
		$db_uid[$db_user[$db_id]] = $db_id;
	}
	$total_users = count($db_user);
	if ($lrweek > 1) {
		if ($debug > 0) { $boxContent .= "<!-- HBT GraphPos \$lrweek ='$lrweek' -->\n"; }
		# How about we first grab users who've made picks this year,
		# and figure their percentage for each week. Then we loop through the weeks and 
		# figure the rankings.  Probably would be best if we order the graph by the latest leaderboard.
		# probably should put an upper limit on the number of pickers we put on the graph, it might be
		# too busy if we go much above 20, I figure.  Test and adjust as necessary.
		#
		# Here we're getting all the winners & losers (&pushes)
		$sql = "SELECT home_score, visitor_score, home_spread, week, game";
		$sql .= " FROM ".$prefix."_pool_games";
		if ($testing == '1') { $sql .= "_test"; }
		$sql .= " WHERE league = '$leagueID' AND season = '$seasonID' AND week <= '$lrweek'";
		$sql .= " AND home_score is not NULL AND visitor_score is not NULL";
		if ($usespreads == '1') { $sql .= " AND home_spread IS NOT NULL"; }
		if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
		$sql .= " ORDER by week, date, game";
		if ($debug > 0) { $boxContent .= "\n<!-- HBT Graph \$sql='$sql' -->\n"; }
		$gameresults = sql_query($sql, $dbi);
		while ($row = $db->sql_fetchrow($gameresults)) {
			$total_pool_games++;
			$g_week = intval($row['week']);
			$total_week_games[$g_week]++;
			$r_week[$g_week] = $g_week;
			$g_game = intval($row['game']);
			$w_game[$g_week][$g_game] = $g_game;
			$g_home_score = $row['home_score'];
			$g_visitor_score = $row['visitor_score'];
			$g_home_spread = $row['home_spread'];
			$g_result = $g_home_score - $g_visitor_score;
			if ($usespreads > 0) { $g_result = $g_result - $g_home_spread; }
			if ($g_result > 0) {
				$h_victory++;
				$g_winner[$g_week][$g_game] = "home";
			} elseif ($g_result < 0) {
				$v_victory++;
				$g_winner[$g_week][$g_game] = "visitor";
			} else {
				$pushes++;
				$g_winner[$g_week][$g_game] = "push";
			}
		}
		if (($h_victory > 0 ) || ($v_victory > 0) || ($pushes > 0)) {
			asort ($r_week);
			asort ($db_uid);
			foreach ($r_week as $rweek) {
				asort($w_game[$rweek]);
				$maxweek = $rweek;
				$gindex = $rweek-1;
				foreach ($db_uid as $usernum) {
					foreach ($w_game[$rweek] as $g_num) {
						if (( $db_pick[$rweek][$usernum][$g_num] == "home" ) || ( $db_pick[$rweek][$usernum][$g_num] == "visitor" )) {
							# By not tracking the week, we're effectively doing a cumulative total as the weeks go by...
							if ($g_winner[$rweek][$g_num] == $db_pick[$rweek][$usernum][$g_num]) {
								$right[$usernum]++;
								$rightweek[$rweek][$usernum]++;
							} elseif ($g_winner[$rweek][$g_num] == "push") {
								$push[$usernum]++;
								$pushweek[$rweek][$usernum]++;
							} else {
								$wrong[$usernum]++;
								$wrongweek[$rweek][$usernum]++;
							}
						}
					}
					if (!($right[$usernum])) { $right[$usernum] = 0; }
					if (!($wrong[$usernum])) { $wrong[$usernum] = 0; }
					if ($right[$usernum]+$wrong[$usernum] > 0) {
						$ud_pct[$gindex][$usernum] = ltrim(number_format( ($right[$usernum] / ($right[$usernum]+$wrong[$usernum])) ,4),0);
					} else {
						$ud_pct[$gindex][$usernum] = ".000";
					}
					# trying to unset if picker didn't pick enough games to be in the standings.
					# $picks_sofar=$rightweek[$rweek][$usernum]+$wrongweek[$rweek][$usernum]+$pushweek[$rweek][$usernum];
					$picks_sofar[$usernum]=$right[$usernum]+$wrong[$usernum]+$push[$usernum];
					$games_sofar[$usernum]+=$total_week_games[$rweek];
					#$pickratio=$picks_sofar[$username]/$total_week_games[$rweek];
					$pickratio=$picks_sofar[$usernum]/$games_sofar[$usernum];
					if ($pickratio < .4) {
						if ($debug > 0) { $boxContent .= "<!-- \$usernum=\"$usernum\", \$db_user[$usernum]=\"$db_user[$usernum]\",  \$pickratio=\"$pickratio\" -->\n"; }
						unset($ud_pct[$gindex][$usernum]);
						$ranking[$usernum][$gindex]='';
					}
					$dpct = $ud_pct[$gindex][$usernum];
					# if ($debug > 0) { $boxContent .= "<!-- \$gindex=\"$gindex\", \$usernum=\"$usernum\", \$ud_pct[\$gindex][$usernum] = \"$dpct\" -->\n"; }
				}
				$gd_week[$gindex] = $rweek;
				# okay, we have percentages for each user who made picks for each week of the season up to now.
				# now we need to assign each user a rank for the week.
				#
				arsort($ud_pct[$gindex]);
				$i = 1;
				$rank = 1;
				$t_rank = 1;
				foreach ($ud_pct[$gindex] as $picker => $pick_pct ) {
					if ($i == 1) {
						$ranking[$picker][$gindex] = round(-$rank);
					} else {
						if ($pick_pct == $prev_pct) {
							$ranking[$picker][$gindex] = round(-$rank);
							$t_rank++;
						} else {
							$rank += $t_rank;
							$t_rank = 1;
							$ranking[$picker][$gindex] = round(-$rank);
						}
					}	
					$i++;
					$prev_pct=$pick_pct;
					if ($debug > 0) {
						$drk = $ranking[$picker][$gindex];
						$dcpct=$ud_pct[$gindex][$picker];
						$boxContent .= "\t<!--\$rweek=\"$rweek\", \$picker=\"$picker\", \$db_user[$picker]=\"$db_user[$picker]\", \$ud_pct[$gindex][$picker]=\"$dcpct\", \$ranking[$picker][$gindex]=\"$drk\" -->\n";
					}
				}
				if ($debug > 0) { $boxContent .= "<!--  End week -->\n"; }
			}
			#
			# Testing before we graph 
			$cou = count ($mycolors);
			if ($debug > 0) { for ($i=0; $i < $cou; $i++) { $boxContent .= "<!-- \$mycolors[$i] = \"$mycolors[$i]\" -->\n"; } }
			if ($debug > 0) { 
				$i = 0;
				foreach ($db_uid as $usernum) {
					$uname = $db_user[$usernum];
					$j = $i;
					while ($j >= count($mycolors) ) { $j = $j - count($mycolors); }
					$dcol = $mycolors[$j];
					$boxContent .= "<!-- \$uname = \"$uname\"; \$j=\"$j\"; \$dcol = \"$dcol\" -->\n";
					$i++;
				}
			}
			include ("jpgraph/jpgraph.php");
			include ("jpgraph/jpgraph_bar.php");
			include ("jpgraph/jpgraph_line.php");
			# Get the mundane out of the way first.
			$xtitle = "Week";
			$datax = $r_week;
			$title = "Picker Overall Rankings by week";
			// Create the graph.
			# to keep the graph from getting too busy, we stretch it horizontally for more that 16 users.
			if ($total_users > 16) {
				$graph_height=480 + (($total_users-16)*20);
			} else {
				$graph_height=480;
			}
			$graph = new Graph(800,$graph_height,"auto");
			$graph->SetBackgroundImage("/home5/footban4/public_html/images/graphlogo.png",BGIMG_FILLPLOT);
			$graph->SetScale("textint");
			$graph->SetTickDensity(TICKD_DENSE);
			# order on margins is :  Left, right, top, bottom.
			$graph->img->SetMargin(30,120,60,60);
			$graph->yaxis->SetLabelMargin(10);
			$graph->yaxis->SetTextLabelInterval(1);
			$graph->yaxis->SetTextTickInterval(1,1);
			# $graph->yaxis->HideLastTickLabel();
			$graph->yaxis->SetTickSide(SIDE_LEFT); 
			$graph->yaxis->SetLabelFormatCallback("_cb_negate");
			$graph->xaxis->SetTickSide(SIDE_BOTTOM); 
			$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
			$graph->yaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
			# Here we loop, to handle all the users' data.
			$i = 0;
			foreach ($db_uid as $usernum) {
				$uname = $db_user[$usernum];
				$j = $i;
				while ($j >= count($mycolors) ) { $j = $j - count($mycolors); }
				$dcol = $mycolors[$j];
				$lplot[$i]=new LinePlot($ranking[$usernum]);
				$lplot[$i]->SetColor("#$dcol");
				$lplot[$i]->SetWeight(4);
				$lplot[$i]->SetLegend("$uname");
				# $lplot[$i]->value->SetFont(FF_IHATCS,FS_NORMAL,8);
				$lplot[$i]->value->SetMargin(6);
				$lplot[$i]->mark-> SetTYPE( MARK_IMG, "/home5/footban4/public_html/images/football.png", .075);
				$graph->Add($lplot[$i]);
				$i++;
			}
			$graph->title->Set("$title");
			$graph->title->SetFont(FF_VERDANA, FS_NORMAL,20);
			$graph->legend->SetFont(FF_ARIAL,FS_NORMAL,8);
			$graph->yaxis->title->SetFont(FF_ARIAL);
			$graph->xaxis->title->SetFont(FF_ARIAL);
			$graph->legend->SetLayout('LEGEND_VER');
			# $graph->legend->SetWeight(4);
			$graph->legend->Pos(0.01,0.15,"right","top");
			$t1 = new Text("Copyright $seasonID\nFootball-Pools.org");
			$t1->SetFont(FF_MARKERFINEPOINT,FS_NORMAL,10);
			$t1->SetColor("darkblue");
			# $t1->SetPos(5,430);
			$t1->SetPos(0.05,0.97,"left","bottom");
			$graph->AddText($t1);
			$t2text = "$leagueID $poolname pool";
			if ($usespreads == 0) { $t2text .= " (no spreads)"; }
			$t2 = new Text("$t2text");
			$t2->SetFont(FF_MARKERFINEPOINT,FS_NORMAL,24);
			$t2->SetColor("darkblue@0.6");
			$t2->SetPos(0.95,0.97,"right","bottom");
			$graph->AddText($t1);
			$graph->AddText($t2);
			# Display the graph
			$fname = $leagueID."_".$poolname;
			if ($usespreads == 0) { $fname .= "-nospread"; }
			$graph->Stroke("/home5/footban4/public_html/images/graphs/$fname.png");
			$boxContent .= "\n<center><img src=\"/images/graphs/$fname.png\"></center><br>\n";
		} else {
			$boxContent .= "<h4>Nothing to graph.</h4>\n";
		}
	} else {
		$boxContent .= "<h4>Sorry, the graph wouldn't make much sense after only one week.      Try back next week.</h4>\n";
	}
}

function ScoreboardTeamBlock($game, $teamID, $homeflag) {
	global $seasonID, $leagueID, $usespreads, $boxContent, $teamname, $mascot, $home_wins, $home_losses, $away_wins, $away_losses, $home_ties, $away_ties, $dbi, $module_name, $db, $network;
	# Do team records
	if ($teamID < 900) {
		$record = " (".intval($home_wins[$teamID]+$away_wins[$teamID])."-".intval($home_losses[$teamID]+$away_losses[$teamID]);
		if ($homeflag == "0") {
			$record .=", road: ".intval($away_wins[$teamID])."-".intval($away_losses[$teamID]).")";
		} else {
			$record .=", home: ".intval($home_wins[$teamID])."-".intval($home_losses[$teamID]).")";
		}
	} else {
		$record = "&nbsp;";
	}
	# Layout:
	# Ranking (8%), Logo-teamname-<br>-records (72%), Score (20%, right-aligned)
	$dranking = $ranking[$teamID];
	$dteamname = $teamname[$teamID]." ".$mascot[$teamID];
	$boxContent .= "\n\t\t<td width=8%>'$dranking'</td>";
	$boxContent .= "\n\t\t<td width=72%>";
	$boxContent .= "'$dteamname'<br>'$record'";
	$boxContent .= "";
}
?>
