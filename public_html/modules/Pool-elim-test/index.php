<?php
/********************************************************************************/
/* Football Pool module for PHP-Nuke - Elimination                              */
/*   written by: Henry B. Tindall, Jr.                                          */
/*   first written: 28 Aug 2006                                                 */
/*                                                                              */
/* version 2019.2.0 - 23 Sep 2019 - Added Schedule pop-ups, reconfigured some   */
/*                    debugging output as conditional                           */
/*         2019.1.0 - 11 Sep 2019 - Modified to account for ties.               */
/*             3.00 - 26 Nov 2010 - Added Tooltips with team name & mascot      */
/*             2.00 - 20 Aug 2007 - Added NCAA top 25.  added ifs for testing   */
/*             1.01 - 07 Sep 2006 - Added logic to check if the user has been   */
/*                    in since the first week.                                  */
/*             1.00 - 28 Aug 2006 - Initial Release                             */
/********************************************************************************/
global $testing, $DST_start, $DST_end, $home_wins, $home_losses, $away_wins, $away_losses, $network;

if (!eregi("modules.php", $_SERVER['SCRIPT_NAME'])) {
    die ("You can't access this file directly...");
}

require_once("mainfile.php");
$module_name = basename(dirname(__FILE__));
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

/*  A few variables we use later on....       */
require_once("config.inc.php");

# check membership:
$subscribed = 0;
$membership = explode(",",$membership);
if ( $debug > 2 ) {  $boxContent .= "\n<!-- HBT Memberships --><br>\n"; }
foreach ($membership as $sub) {
	if ( $debug > 1 ) { $boxContent .= "<!-- HBT \$sub='$sub' --><br>\n"; }
	if (ereg($sub, $poolname)) {
		if ( $debug > 1 ) {  $boxContent .= "<!-- HBT \$sub='$sub' is in '$poolname' --><br>\n"; }
		$subscribed++;
	}
}
if ($subscribed == 0) {
	$boxContent .= "<h3>Sorry, you're not a member of the \"".$poolname."\" pool.</h3>";
	$boxContent .= "If this is incorrect, please <a href=\"modules.php?name=ContactUs\">E-mail Us</a>, and be sure to include the name of the pool.";
	themecenterbox($boxTitle, $boxContent);
	include ('footer.php');
	exit;
}

if (!$must_pick) { $must_pick = 1; }
if (!$today_date) { $today_date = date("Y-m-d"); }
if (!$now_time) { $now_time = date("Hm"); }
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
	while ($row = $db->sql_fetchrow($result)) {
		$network[$row['id']] = $row['name'];
	}
	$sql = "SELECT week, date, time, tvnetwork FROM ".$prefix."_pool_games";
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE ((date > '$today_date') OR (date = '$today_date' AND time > '$now_time'))";
	$sql .= " AND league = '$leagueID' AND season = '$seasonID'";
	if ($leagueID == 'NCAA') {
		$sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)";
	}
	$sql .= " ORDER BY date, time limit 1";
	if ( $debug > 1 ) { $boxContent .= "<!-- HBT \'$sql\' -->\n"; }
	$result = $db->sql_query($sql);
	$object = sql_fetch_object($result, $dbi);
	if(is_object($object)) {
		$weekID = $object->week;
		$weekID = intval($weekID);
		$date = $object->date;
		$time = $object->time;
		$lastweek = $weekID-1;
		$tvnetwork = intval($tvnetwork);
		list($y,$m,$d) = explode("-",$date);
		$date = date("l j F, Y", mktime(0,0,0,$m,$d,$y));
		echo "The next game is at $time, $date";
		if ($tvnetwork > 0) { echo ", on ".$network[$tvnetwork]."!<br>\n"; }
		echo "<br>\n";
	}	else {
		$sql = "SELECT week,date FROM ".$prefix."_pool_games";
		if ($testing == '1') { $sql .= "_test"; }
		$sql = " WHERE season = '$seasonID'";
		if ($leagueID == 'NCAA') {
			$sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)";
		}
		$sql .= " AND league = '$leagueID' order by week DESC limit 1";
		$result = $db->sql_query($sql);
		$object = sql_fetch_object($result, $dbi);
		if(is_object($object)) {
			$weekID = $object->week;
			$weekID = intval($weekID);
			$lastweek=$weekID;
#			$sweekID = $weekID;
		}
		$seasonover = 1;
#		echo "<center><font size=+5>Sorry, the season is over.  Join us on the forums, or check out the all the results.</font></center>";
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
if ($leagueID == 'NCAA') {
	$sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)";
}

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

if ($op == "MakePick") {
	DisplayWeek($seasonID, $weekID);
} elseif ($op == "SavePick") {
	SavePick();
} elseif ($op == "WinnersAll") {
	ScoreBoard($seasonID, $weekID);
} elseif ($op == "DisplayWeek") {
	DisplayWeek($seasonID, $weekID);
} elseif ($op == "TeamSchedule") {
	if ($_POST[team]) {
		$team = $_POST[team];
	}
	TeamSchedule($team, $seasonID, $graphuser);
} else {
	ScoreBoard($seasonID, $weekID);
}

$prev_season = $seasonID-1;
$next_season = $seasonID+1;
if (!($op == "MakePick" || ($op == "DisplayWeek"))) { $boxContent .= "<font class=\"content\"><a href=\"modules.php?name=$module_name&amp;op=MakePick&amp;seasonID=$this_season\"><b><i>Make my Pick !</i></b></a></font><br>\n";}
# if ($op != "ShowAllPicks") { $boxContent .= "<font class=\"content\"><a href=\"modules.php?name=$module_name&amp;op=ShowAllPicks&amp;seasonID=$seasonID&amp;weekID=$weekID&amp;start_user=1\"><b>Everyone's Picks</b></a></font><br>\n";}
if (($op != "ScoreBoard") && ($op != "")) { $boxContent .= "<font class=\"content\"><a href=\"modules.php?name=$module_name&amp;op=ScoreBoard&amp;seasonID=$seasonID\"><b>Pool Scoreboard</b></a></font><br>\n";}
if ($op != "TeamSchedule") { $boxContent .= "<font class=\"content\"><a href=\"modules.php?name=$module_name&amp;op=TeamSchedule&amp;seasonID=$seasonID\"><b>Team Schedules</b></a></font><br>\n";}
# if ($op) { $boxContent .= "<br><font class=\"content\"><a href=\"modules.php?name=$module_name\"><b>Go to main Pool page</b></a></font><br>\n";}

$boxContent .= "<br>";
if ($seasonID > 2006) { $boxContent .= "<font class=\"content\"><a href=\"modules.php?name=$module_name&amp;seasonID=$prev_season\"><b>Previous Season</b></a></font><br>\n";}
if ($seasonID < $this_year) { $boxContent .= "<font class=\"content\"><a href=\"modules.php?name=$module_name&amp;seasonID=$next_season\"><b>Next Season</b></a></font><br>\n";}

themecenterbox($boxTitle, $boxContent);

include ('footer.php');

/*********************************************************/
/* Functions                                             */
/*********************************************************/

function SavePick() {
	global $testing, $leagueID, $poolname, $seasonID, $weekID, $boxTitle, $boxContent, $user, $user_id, $uname, $cookie, $prefix, $dbi, $module_name, $db, $ppercent, $p_ppercent, $debug;
	$today_date = date("Y-m-d");
	$now_time = date("Hi");
	if (is_user($user)) {
		getusrinfo($user);
		cookiedecode($user);
	}
	$boxTitle = "$uname's $seasonID week $weekID Elimination Pool";
	$boxTitle .= " (".$poolname." pool) ";
	$sql = "SELECT game, week, pick FROM ".$prefix."_pool_picks_elim_".$poolname;
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID' AND season = '$seasonID' AND user_id = '$user_id' ORDER BY week";
	if ( $debug > 0 ) { $boxContent .= "<!-- HBT \$sql = '$sql' -->\n"; }
	$result = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result)) {
		$db_game = intval($row['game']);
		$week = $row['week'];
		$db_pick[$week] = $row['pick'];
		if ($week <= $weekID) { $used[$db_pick[$week]] = 1; }
	}
	$sql = "SELECT team_id, team_name from ".$prefix."_pool_teams";
	$sql .= " WHERE league = '$leagueID'";
	if ( $debug > 0 ) { $boxContent .= "<!-- HBT \$sql = '$sql' -->\n"; }
	$result = $db->sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result)) {
		$team_id = $row['team_id'];
		$team_name[$team_id] = $row['team_name'];
	}
	$sql = "SELECT date, time, home, home_score, visitor, visitor_score, home_spread, game";
	$sql .= " FROM ".$prefix."_pool_games";
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID'";
	$sql .= " AND season = '$seasonID' AND week='$weekID'";
	if ($leagueID == 'NCAA') {
		$sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)";
	}
	$sql .= " ORDER BY date,time, game";
	if ( $debug > 0 ) { $boxContent .= "<!-- HBT \$sql = '$sql' -->\n"; }
	$result = $db->sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result)) {
		$game = intval($row['game']);
		$date[$game] = $row['date'];
		$time[$game] = $row['time'];
		$day = date("l", strtotime($date[$game]));
		$h_id[$game] = $row['home'];
		$home[$game] = $team_name[$h_id[$game]];
		$v_id[$game] = $row['visitor'];
		$visitor[$game] = $team_name[$v_id[$game]];
		$home_spread[$game] = $row['home_spread'];
		$gid[$h_id[$game]] = $game;
		$gid[$v_id[$game]] = $game;
	}
	$pickval = $_POST['pick'];
	$game = $gid[$pickval]; 
	$choice =  $team_name[$pickval];
	(($h_id[$game] == $pickval) ? $nonchoice = $team_name[$v_id[$game]] : $nonchoice = $team_name[$h_id[$game]] );
	if (($date[$game] == $today_date && $time[$game] > $now_time) || ($date[$game] > $today_date)) {
		$boxContent .= "You picked <b>$choice</b> over <b>$nonchoice</b>.";
		if (($db_pick[$weekID]) && ($db_pick[$weekID] != $pickval)) {
			$sql = "UPDATE ".$prefix."_pool_picks_elim_".$poolname." SET pick = '$pickval', game = '$game'";
			$sql .= " WHERE (user_id = '$user_id' AND week = '$weekID' AND season = '$seasonID'";
			$sql .= " AND league = '$leagueID')";
			$update = $db->sql_query($sql, $dbi);
			$boxContent .= " (changed)<br>\n";
		} elseif (!($db_pick[$weekID])) {
			$sql = "INSERT INTO ".$prefix."_pool_picks_elim_".$poolname;
			if ($testing == '1') { $sql .= "_test"; }
			$sql .= " (user_ID, season, league, week, game, pick)";
			$sql .= " VALUES ('$user_id', '$seasonID', '$leagueID', '$weekID', '$game', '$pickval')";
			$insert = $db->sql_query($sql, $dbi);
			$boxContent .= " (added)<br>\n";
		}
	} else {
		$boxContent .= "<i>Sorry, you're too late to pick $choice over $nonchoice.</i><br>\n";
	}
	$boxContent .= "<br><br>";
}

function ScoreBoard($seasonID, $weekID) {
	global $testing, $poolname, $leagueID, $seasonID, $user_prefix, $weekID, $lastweek, $boxTitle, $boxContent, $user, $user_id, $uname, $cookie, $prefix, $dbi, $module_name, $db, $ppercent, $today_date, $now_time, $debug;
	$boxTitle = "$seasonID Elimination Pool";
	$boxTitle .= " (".$poolname." pool) ";
	$numweeks = 0;
	$sql = "SELECT t.team_id, t.team_name, m.mascot FROM ";
	$sql .= $prefix."_pool_teams";
	$sql .= " t LEFT OUTER JOIN ";
	$sql .= $prefix."_pool_teams_mascots m";
	$sql .= " ON t.team_id = m.team_id";
	$sql .= " WHERE league = '$leagueID'";
	$result = $db->sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result)) {
		$team_id = $row['team_id'];
		$team_name[$team_id] = $row['team_name'];
		$mascot[$team_id] = $row['mascot'];
	}
	$sql = "SELECT user_id, game, pick, week";
	$sql .= " FROM ".$prefix."_pool_picks_elim_".$poolname;
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID'";
	$sql .= " AND season = '$seasonID' ORDER by week, game";
	if ( $debug > 0 ) { $boxContent .= "<!-- HBT \$sql = '$sql' -->\n"; }
	$result = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result)) {
		$db_week = intval($row['week']);
		$uid = intval($row['user_id']);
		$db_game = intval($row['game']);
		$db_pick[$db_week][$uid][$db_game] = intval($row['pick']);
		$numweeks++;
		$currentweek = intval($row['week']);
		if ( $debug > 1 ) { echo "<!-- HBT \$currentweek='$currentweek' -->\n"; }
	}
	if ($numweeks > 0) {
		$sql = "SELECT user_id, username FROM ".$user_prefix."_users";
		$sql .= " WHERE user_id > 1 ORDER BY user_id";
		$result = sql_query($sql, $dbi);
		while ($row = $db->sql_fetchrow($result)) {
			$db_id = intval($row['user_id']);
			$db_user[$db_id] = $row['username'];
			$db_uid[$db_user[$db_id]] = $db_id;
		}
		# Pull scores from the database and figure out who all the game winners are.
		$sql = "SELECT home, visitor, home_score, visitor_score, home_spread, week, game";
		$sql .= " FROM ".$prefix."_pool_games";
		if ($testing == '1') { $sql .= "_test"; }
		$sql .= " WHERE league = '$leagueID' AND season = '$seasonID' AND week <= '$currentweek'";
		if ($leagueID == 'NCAA') {
			$sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)";
		}
		$sql .= " ORDER by week, date, game";
		if ( $debug > 0 ) { $boxContent .= "<!-- HBT \$sql = '$sql' -->\n"; }
		$gameresults = sql_query($sql, $dbi);
		while ($row = $db->sql_fetchrow($gameresults)) {
			$g_week = intval($row['week']);
			$r_week[$g_week] = $g_week;
			$g_game = intval($row['game']);
			$w_game[$g_game] = $g_game;
			$h_or_v[$g_week][$g_game][$row['home']] = "home";
			$h_or_v[$g_week][$g_game][$row['visitor']] = "visitor";
			$visitor_team[$g_week][$g_game] = $row['visitor'];
			$home_team[$g_week][$g_game] = $row['home'];
			$g_home_score = $row['home_score'];
			$g_visitor_score = $row['visitor_score'];
			$g_home_spread = $row['home_spread'];
			if (( $g_home_score > 0 ) or ($g_visitor_score > 0)) {
				$g_result = $g_home_score - $g_visitor_score;
				$g_complete = 1;
			} else {
				$g_result = 0;
				$g_winner[$g_week][$g_game] = "TBD";
				$g_complete = 0;
			}
			if ($g_result > 0) {
				$g_winner[$g_week][$g_game] = "home";
				if ( $debug > 1 ) { $boxContent .= "\n<!-- HBT -- winner week $g_week, game $g_game was home -->\n"; }
			} elseif ($g_result < 0) {
				$g_winner[$g_week][$g_game] = "visitor";
				if ( $debug > 1 ) { $boxContent .= "\n<!-- HBT -- winner week $g_week, game $g_game was visitor -->\n"; }
			} elseif ($g_result == 0 && $g_complete > 0) {
				$g_winner[$g_week][$g_game] = "TIE";
			} elseif ($g_result == 0 && $g_complete == 0) {
				$g_winner[$g_week][$g_game] = "TBD";
			} else  {
			}
		}
		if (count($g_winner) > 0) {
			# Go through pickers to see who's still winning, we'll put them at the top.
			foreach ($db_uid as $usernum) {
				asort($r_week);
				foreach ($r_week as $rweek) {
					foreach ($db_pick[$rweek][$usernum] as $g_num => $pick) {
						$picksmade[$usernum]++;
						# Here we have to change the user's pick from a team number to "home" or "visitor".
						$h_or_v_pick = $h_or_v[$rweek][$g_num][$pick];
						# Now tally how many right...
						if (($g_winner[$rweek][$g_num] == $h_or_v_pick) || ($g_winner[$rweek][$g_num] == "TIE")) {
							$nonwrong[$usernum]++;
						} else {
							$wrong[$usernum]++;
						}
					}
				}
			}
			foreach ($picksmade as $picker => $numpicks) {
				$ppct[$picker] = number_format((($numpicks-$wrong[$picker])/$numpicks)*100,1)+($picksmade[$picker]*100);
				if ($debug > 0) { echo "\n<!-- HBT - \$ppct[$picker] = '$ppct[$picker]' -->\n"; }
			}
			arsort($ppct);
			$boxContent .= "<table cellspacing=\"2\">\n";
			$boxContent .= "<tr><td></td>";
			foreach ($r_week as $rweek) {
				$boxContent .= "<td align=\"center\">week<br>$rweek</td>";
			}
			$boxContent .= "</tr>\n";
			foreach ($ppct as $picker => $ppctage) {
				if ($debug > 0) { $boxContent .= "\n<!-- HBT \$ppct = '$ppct[$picker]' \$picker = '$picker'-->\n"; }
				asort($r_week);
				$real_user = $db_user[$picker];
				$real_uid = $db_uid[$real_user];
				$boxContent .= "<tr><td nowrap><b><a href=\"modules.php?name=Private_Messages&amp;file=index&amp;mode=post&amp;u=".$real_uid."\">".$db_user[$picker]."</a></b>";
				foreach ($r_week as $rweek) {
					# Show the game they picked...   which game did they pick?
					foreach ($db_pick[$rweek][$picker] as $pgame => $pick) {  #  Error log says "invalid argument supplied for foreach()"...  We need to figure out & fix
					  $hischoice = $h_or_v[$rweek][$pgame][$pick];
					  if ($debug > 0) { $boxContent .= "\n<!-- HBT \$db_pick[\$rweek][\$picker][\$pgame] \$rweek='$rweek' \$picker='$picker' \$pgame='$pgame' \$db_pick[$rweek][$picker][$pgame]='$db_pick[$rweek][$picker][$pgame]' \$hischoice='$hischoice' -->\n"; }
						if ($hischoice == "home") {
							# Was the pick right or wrong?
							(($g_winner[$rweek][$pgame] == "home") ? $bgcolor = "green" : $bgcolor = "red" );
							$nonpick = $visitor_team[$rweek][$pgame];
						} elseif ($hischoice == "visitor") {
							(($g_winner[$rweek][$pgame] == "visitor") ? $bgcolor = "green" : $bgcolor = "red" );
							$nonpick = $home_team[$rweek][$pgame];
						} else {
							$boxContent .= "<td border=\"1\" align=\"center\">This is bad.</td>";
						}
						(($g_winner[$rweek][$pgame] == "TBD")? $bgcolor = "white" : $j1 = 0 );
						(($g_winner[$rweek][$pgame] == "TIE")? $bgcolor = "grey" : $j1 = 0 );
						$boxContent .= "<td nowrap border=\"1\" align=\"center\" background=\"/images/".$bgcolor.".png\" valign=\"middle\">";
						$boxContent .= "<img src=\"/images/poollogos/".$pick.".gif\" alt=\"$team_name[$pick] $mascot[$pick]\" title=\"$team_name[$pick] $mascot[$pick]\"><img src=\"/images/over.gif\" /><img src=\"/images/poollogos/".$nonpick.".gif\" alt=\"$team_name[$nonpick] $mascot[$nonpick]\" title=\"$team_name[$nonpick] $mascot[$nonpick]\"></td>\n";
					}
				}
				$boxContent .= "</tr>\n";
			}
			$boxContent .= "</table><br><br>\n";
		} else {
			$boxContent .= "<h4>Sorry, There aren't any results to display yet.  Check back next week....</h4>\n";
		}
	} else {
		$boxContent .= "<h4>Sorry, There are no results to display yet.  Check back next week....</h4>\n";
	}
}

function DisplayWeek($seasonID, $weekID) {
	global $testing, $leagueID, $today_date, $now_time, $poolname, $seasonID, $boxTitle, $boxContent, $home_wins, $home_losses, $away_wins, $away_losses, $user, $user_id, $uname, $cookie, $prefix, $dbi, $module_name, $db, $seasonover, $DST_start, $DST_end, $network, $debug;
	$today_date = date("Y-m-d");
	if ($leagueID == "NCAA") { 
		$lsize = '40';
		$bsize = '500';
	} else { 
		$lsize = '50'; 
		$bsize = '420';
	}
	$now_time = date("Hi");
	$boxTitle = "$seasonID Elimination Pool";
	$boxTitle .= " (".$poolname." pool) ";
	$boxTitle .= " pick - Week $weekID</h2>";
	# First, make sure the user hasn't made any incorrect picks.  If so, he's done.
	# Also, he has to have been in since the beginning !
	$sql = "SELECT week, game, pick";
	$sql .= " FROM ".$prefix."_pool_picks_elim_".$poolname;
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID' AND user_id = '$user_id'";
	$sql .= " AND season = '$seasonID' AND week < '$weekID' ORDER BY week";
	if ( $debug > 0 ) { $boxContent .= "<!-- HBT \$sql = '$sql' -->\n"; }
	$result_b = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result_b)) {
		$db_game = intval($row['game']);
		$pweek= intval($row['week']);
		$tpick = intval($row['pick']);
		$db_pick[$pweek][$db_game] = $tpick;
		$madeapick[$pweek] = 1;
		$used[$tpick] = 1;
		if ($debug > 0) { $boxContent .= "<!-- HBT - \$user_id '$user_id' Picked '$tpick' on week '$pweek' -->\n"; }
	}
	$sql = "SELECT home, visitor, home_score, visitor_score, home_spread, week, game";
	$sql .= " FROM ".$prefix."_pool_games";
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID' AND season = '$seasonID' AND week <= '$weekID'";
	if ($leagueID == 'NCAA') {
		$sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)";
	}
	$sql .= " ORDER BY week";
	if ( $debug > 0 ) { $boxContent .= "<!-- HBT \$sql='$sql' -->\n"; }
	$gameresults = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($gameresults)) {
		$g_week = intval($row['week']);
		$r_week[$g_week] = $g_week;
		$g_game = intval($row['game']);
		$w_game[$g_game] = $g_game;
		$h_or_v[$g_week][$g_game][$row['home']] = "home";
		$h_or_v[$g_week][$g_game][$row['visitor']] = "visitor";
		$visitor_team[$g_week][$g_game] = $row['visitor'];
		$home_team[$g_week][$g_game] = $row['home'];
		$g_home_score = $row['home_score'];
		$g_visitor_score = $row['visitor_score'];
		$g_home_spread = $row['home_spread'];
		if (( $g_home_score > 0 ) or ($g_visitor_score > 0)) {
			$g_result = $g_home_score - $g_visitor_score;
		} else {
			$g_result = 0;
		}
		if ($g_result > 0) {
			$g_winner[$g_week][$g_game] = "home";
		} elseif ($g_result < 0) {
			$g_winner[$g_week][$g_game] = "visitor";
		} else {
			$g_winner[$g_week][$g_game] = "TIE";
		}
	}
	$missedweeks=0;
	$wrong=0;
	for ($i = 1; $i < $weekID; $i++) {
		if (!($madeapick[$i])) {
			$missedweeks++;
			if ($debug > 0) { $boxContent .= "<!-- HBT - missed week '$i' -->\n"; }
		}
		foreach ($db_pick[$i] as $g_num => $pick ) {
			# Here we have to change the user's pick from a team number to "home" or "visitor".
			$h_or_v_pick = $h_or_v[$i][$g_num][$pick];
			# Now check for a wrong pick....
			if ($debug > 0) { $boxContent .= "<!-- HBT - Checking for a wrong pick, week '$i', game '$g_num', pick '$pick' ($h_or_v_pick) -->\n"; }
			if (($g_winner[$i][$g_num] != $h_or_v_pick) && ($g_winner[$i][$g_num] != "TIE")) {
				$wrong++;
				if ( $debug > 1 ) { echo "<!-- HBT - wrong. -->\n"; }
			}
		}
	}
	if ($wrong > 0) {
		$boxContent .= "<h4>Sorry, You made a wrong pick, and now you're out.</h4>\n";
	} elseif ($missedweeks > 0) {
	  if ( $debug > 1 ) { echo "<!-- HBT \$missed='$missed' -->\n"; }
		$boxContent .= "<h4>Sorry, You have to have been in since the beginning of the season to participate...  Try next year.</h4>\n";
	} else {
		# See if the user has made a pick for this week.
		$haspick = 0;
		$toolate = 0;
		$sql = "SELECT game, pick";
		$sql .= " FROM ".$prefix."_pool_picks_elim_".$poolname;
		if ($testing == '1') { $sql .= "_test"; }
		$sql .= " WHERE league = '$leagueID' AND season = '$seasonID' AND week='$weekID' AND user_id = '$user_id'";
		if ( $debug > 0 ) { $boxContent .= "<!-- HBT \$sql = '$sql' -->\n"; }
		$result = $db->sql_query($sql, $dbi);
		while ($row = $db->sql_fetchrow($result)) {
			$gamepicked = $row['game'];
			$haspick = 1;
		}
		if ($haspick == 1) {
			$sql = "SELECT date, time FROM ".$prefix."_pool_games";
			if ($testing == '1') { $sql .= "_test"; }
			$sql .= " WHERE season = '$seasonID' AND league = '$leagueID' AND week='$weekID' AND game='$gamepicked'";
			if ( $debug > 0 ) { $boxContent .= "<!-- HBT \$sql = '$sql' -->\n"; }
			$result = sql_query($sql, $dbi);
			while ($row = $db->sql_fetchrow($result)) {
				$pdate = $row['date'];
				$ptime = $row['time'];
				if (($today_date > $pdate) || (($today_date == $pdate) && ($now_time >= $ptime ))) {
					$toolate = 1;
#					echo "<h4>$today_date - $now_time --- $pdate - $ptime</h4>\n";
				}
			}
		}
		if ($toolate > 0) {
			$boxContent .= "<h4>Sorry, the game you picked has already started...  Too late to change now!</h4>\n";
		} else {
			$boxContent .= "<form action=\"modules.php?name=$module_name&amp;op=SavePick\" method=\"post\">";
			$boxContent .= "<input type=\"hidden\" name=\"seasonID\" value=\"".$seasonID."\">";
			$boxContent .= "<input type=\"hidden\" name=\"weekID\" value=\"".$weekID."\">";
			unset ($db_hi);
			$sql = "SELECT game, pick FROM ".$prefix."_pool_picks_elim_".$poolname;
			if ($testing == '1') { $sql .= "_test"; }
			$sql .= " WHERE league = '$leagueID' AND season = '$seasonID' AND week='$weekID'";
			$sql .= " AND user_id = '$user_id' order by game";
			if ( $debug > 0 ) { $boxContent .= "<!-- HBT \$sql = '$sql' -->\n"; }
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
			TeamRecords($seasonID, $leagueID, $weekID);
			$sql = "SELECT count(game) count FROM ".$prefix."_pool_games";
			if ($testing == '1') { $sql .= "_test"; }
			$sql .= " WHERE league = '$leagueID' AND season = '$seasonID' AND week='$weekID' ORDER by date, time, visitor";
			if ( $debug > 0 ) { $boxContent .= "<!-- HBT \$sql = '$sql' -->\n"; }
			$game_count = sql_query($sql, $dbi);
			while ($row = $db->sql_fetchrow($game_count)) {
				$wk_game_count = $row['count'];
			}
			$sql = "SELECT team_id, team_name from ".$prefix."_pool_teams";
			$sql .= " WHERE league = '$leagueID'";
			if ($debug > 0) { $boxContent .= "<!-- HBT \$sql = '$sql' -->\n"; }
			$result = $db->sql_query($sql, $dbi);
			while ($row = $db->sql_fetchrow($result)) {
				$team_id = $row['team_id'];
				$team_name[$team_id] = $row['team_name'];
			}
			$boxContent .= "<table cellspacing=\"2\" border=\"1\" width=\"100%\">\n\t<tr>";
			$sql = "SELECT date, time, home, home_score, visitor,";
			$sql .= " visitor_rank, home_rank, visitor_score, home_spread, game, Title, tvnetwork";
			$sql .= " FROM ".$prefix."_pool_games";
			if ($testing == '1') { $sql .= "_test"; }
			$sql .= " WHERE league = '$leagueID'";
			$sql .= " AND season = '$seasonID' AND week='$weekID'";
			if ($leagueID == 'NCAA') {
				$sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)";
			}
			$sql .= " ORDER by date, time, visitor";
			if ( $debug > 0 ) { $boxContent .= "<!-- HBT \$sql = '$sql' -->\n"; }
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
				$g_counter++;
				(($home_rank) ? $d_h_rank = "#".$home_rank." " : $d_h_rank = "");
				(($visitor_rank) ? $d_v_rank = "#".$visitor_rank." " : $d_v_rank = "");
				if (!(($home_score > 0) || ($visitor_score > 0))) {
					if (($date[$game] == $today_date && $time[$game] > $now_time) || ($date[$game] > $today_date)) { 
						$gamestopick++ ;
						$boxContent .= "<td><table cellpadding=\"1\" cellspacing=\"1\">";
						$boxContent .= "<tr><td colspan=\"4\">";
						if (($tvnetwork) && ($tvnetwork > 0)) {
							$boxContent .= "<img src=\"images/poollogos/tv/".$tvnetwork.".png\" alt=\"Game broadcast on ".$network{$tvnetwork}."\">";
						}
						$boxContent .= "</td><td colspan=\"3\" align=\"center\">";
						if (!($title[$game] == "")) {
							$boxContent .= "<b>$title[$game]</b>";
						}
						$boxContent .= "</td></tr>\n"; 
						$boxContent .= "<tr><td valign=\"bottom\">$day,</td>";
						if (!($used[$v_id])) {
							$boxContent .= "<td><input type=\"radio\" name=\"pick\" VALUE=\"".$v_id."\"".($db_hi[$game][$v_id] ? ' checked' : '')."></td>";
						} else {
							$boxContent .= "<td></td>";
						}
						# Add the popup for the schedule...
						$boxContent .= "\n\t<td><img src=\"/images/poollogos/".$v_id.".gif\" border=0 align=\"left\" usemap=\"#".$v_id."\" />";
						$boxContent .= "<map name=\"".$v_id."\">     <area shape=\"rect\" coords=\"0,0,".$lsize.",".$lsize."\" ";
						$boxContent .=" href=\"modules.php?name=TeamSchedule-".$leagueID."&amp;seasonID=$seasonID&amp;team=$v_id\" target=\"_schedule\"\n";
						$boxContent .= ' onMouseOver="return overlib(\'<iframe src=modules.php?name=SchedPop&amp;leagueID='.$leagueID.'&amp;seasonID='.$seasonID.'&amp;team='.$v_id.' height='.$bsize.'></iframe>\', HAUTO, VAUTO)" onMouseOut="return nd();">';
						$boxContent .= "</td>\n";
						$record = " (".intval($home_wins[$v_id]+$away_wins[$v_id])."-".intval($home_losses[$v_id]+$away_losses[$v_id]).", road: ".intval($away_wins[$v_id])."-".intval($away_losses[$v_id]).")";
						$boxContent .= "<td><font class=\"content\"><b>$d_v_rank$visitor</b>$record</font></td></tr>\n";
						$boxContent .= "<tr><td align=\"center\" valign=\"top\">$date[$game]<br>$time[$game]";
						list($y,$mo,$d) = explode("-",$date[$game]);
						$h = substr($time[$game],0,2);
						$mi = substr($time[$game],2);
						$DST = localtime(mktime($h,$mi,0,$mo,$d,$y));
						$game_day = $DST[7]+1;
						((($game_day < $DST_start) || ($game_day >= $DST_end)) ? $timestring = "CST" : $timestring = "CDT" );
						$boxContent .= " $timestring</td>";
						if (!($used[$h_id])) {
							$boxContent .= "<td><input type=\"radio\" name=\"pick\" VALUE=\"".$h_id."\"".($db_hi[$game][$h_id] ? ' checked' : '')."></td>";
						} else {
							$boxContent .= "<td></td>";
						}
						# Add the popup for the schedule...
						$boxContent .= "\n\t<td><img src=\"/images/poollogos/".$h_id.".gif\" border=0 align=\"left\" usemap=\"#".$h_id."\" />";
						$boxContent .= "<map name=\"".$h_id."\">     <area shape=\"rect\" coords=\"0,0,".$lsize.",".$lsize."\" ";
						$boxContent .=" href=\"modules.php?name=TeamSchedule-".$leagueID."&amp;seasonID=$seasonID&amp;team=$h_id\" target=\"_schedule\"\n";
						$boxContent .= ' onMouseOver="return overlib(\'<iframe src=modules.php?name=SchedPop&amp;leagueID='.$leagueID.'&amp;seasonID='.$seasonID.'&amp;team='.$h_id.' height='.$bsize.'></iframe>\', HAUTO, VAUTO)" onMouseOut="return nd();">';
						$boxContent .= "</td>\n";
						$record = " (".intval($home_wins[$h_id]+$away_wins[$h_id])."-".intval($home_losses[$h_id]+$away_losses[$h_id]).", home: ".intval($home_wins[$h_id])."-".intval($home_losses[$h_id]).")";
						$boxContent .= "<td><font class=\"content\">at <b>$d_h_rank$home</b>$record</font></td><td><font class=\"content\">";
						$boxContent .= "</font></td></tr>";
						$boxContent .= "</table></td>\n";
					} else {
						$gamesfrozen++ ;
						$boxContent .= "<td><table cellpadding=\"1\" cellspacing=\"1\">";
						if (!($title[$game] == "")) { $boxContent .= "<tr><td colspan=\"5\" align=\"center\"><b>$title[$game]</b></td></tr>\n"; }
						$boxContent .= "<tr><td valign=\"bottom\">$day,</td><td>".($db_hi[$game]['visitor'] ? "<img src=\"images/poollogos/check.png\">" : '')."</td>";
						# Add the popup for the schedule...
						$boxContent .= "\n\t<td><img src=\"/images/poollogos/".$v_id.".gif\" border=0 align=\"left\" usemap=\"#".$v_id."\" />";
						$boxContent .= "<map name=\"".$v_id."\">     <area shape=\"rect\" coords=\"0,0,".$lsize.",".$lsize."\" ";
						$boxContent .=" href=\"modules.php?name=TeamSchedule-".$leagueID."&amp;seasonID=$seasonID&amp;team=$v_id\" target=\"_schedule\"\n";
						$boxContent .= ' onMouseOver="return overlib(\'<iframe src=modules.php?name=SchedPop&amp;leagueID='.$leagueID.'&amp;seasonID='.$seasonID.'&amp;team='.$v_id.' height='.$bsize.'></iframe>\', HAUTO, VAUTO)" onMouseOut="return nd();">';
						$boxContent .= "</td>\n";
						$record = " (".intval($home_wins[$v_id]+$away_wins[$v_id])."-".intval($home_losses[$v_id]+$away_losses[$v_id]).", road: ".intval($away_wins[$v_id])."-".intval($away_losses[$v_id]).")";
						$boxContent .= "<td><font class=\"content\"><b>$d_v_rank$visitor</b>$record</font></td></tr>\n";
						$boxContent .= "<tr><td align=\"center\" valign=\"top\">$date[$game]<br>$time[$game]";
						list($y,$mo,$d) = explode("-",$date[$game]);
						$h = substr($time[$game],0,2);
						$mi = substr($time[$game],2);
						$DST = localtime(mktime($h,$mi,0,$mo,$d,$y));
						$game_day = $DST[7]+1;
						((($game_day < $DST_start) || ($game_day >= $DST_end)) ? $timestring = "CST" : $timestring = "CDT" );
						$boxContent .= " $timestring</td><td>".($db_hi[$game]['home'] ? "<img src=\"images/poollogos/check.png\">" : '')."</td>";
						# Add the popup for the schedule...
						$boxContent .= "\n\t<td><img src=\"/images/poollogos/".$h_id.".gif\" border=0 align=\"left\" usemap=\"#".$h_id."\" />";
						$boxContent .= "<map name=\"".$h_id."\">     <area shape=\"rect\" coords=\"0,0,".$lsize.",".$lsize."\" ";
						$boxContent .=" href=\"modules.php?name=TeamSchedule-".$leagueID."&amp;seasonID=$seasonID&amp;team=$h_id\" target=\"_schedule\"\n";
						$boxContent .= ' onMouseOver="return overlib(\'<iframe src=modules.php?name=SchedPop&amp;leagueID='.$leagueID.'&amp;seasonID='.$seasonID.'&amp;team='.$h_id.' height='.$bsize.'></iframe>\', HAUTO, VAUTO)" onMouseOut="return nd();">';
						$boxContent .= "</td>\n";
						$record = " (".intval($home_wins[$h_id]+$away_wins[$h_id])."-".intval($home_losses[$h_id]+$away_losses[$h_id]).", home: ".intval($home_wins[$h_id])."-".intval($home_losses[$h_id]).")";
						$boxContent .= "<td><font class=\"content\">at <b>$d_h_rank$home</b>$record</font></td><td><font class=\"content\">";
						$boxContent .= "</font></td></tr></table></td>\n";
					}
				} else {
					$finishedgames++ ;
					$hbh=$heh=$vbh=$veh="";
					$g_result = ($home_score - $visitor_score);
					if ($g_result > 0) { 
						$hbh="<b>";
						$heh="</b>";
						if ($db_hi[$game]['home']) {
							$td_bg = "green";
						} elseif ($db_hi[$game]['visitor']) {
							$td_bg = "red";
						}
					} elseif ($g_result < 0) {
						$vbh="<b>";
						$veh="</b>";
						if ($db_hi[$game]['visitor']) {
							$td_bg = "green";
						} elseif ($db_hi[$game]['home']) {
							$td_bg = "red";
						}
					} elseif ($g_result == 0) {
						$td_bg = "grey";
					}
					$boxContent .= "<td background=\"/images/$td_bg.png\"><table cellpadding=\"1\" cellspacing=\"1\">";
					if (!($title[$game] == "")) { $boxContent .= "<tr><td colspan=\"5\" align=\"center\"><b>$title[$game]</b></td></tr>\n"; }
					$boxContent .= "<tr><td valign=\"bottom\">$day,</td><td>".$vbh."<font class=\"content\">$visitor_score</font>".$veh."</td>";
					$boxContent .= "<td><img src=\"/images/poollogos/".$v_id.".gif\"></td>";
					$record = " (".intval($home_wins[$v_id]+$away_wins[$v_id])."-".intval($home_losses[$v_id]+$away_losses[$v_id]).", road: ".intval($away_wins[$v_id])."-".intval($away_losses[$v_id]).")";
					$boxContent .= "<td>".$vbh."<font class=\"content\">$d_v_rank$visitor</font>".$veh."$record</td></tr>\n";
					$boxContent .= "<tr><td align=\"center\" valign=\"top\">$date[$game]<br>$time[$game]";
					list($y,$mo,$d) = explode("-",$date[$game]);
					$h = substr($time[$game],0,2);
					$mi = substr($time[$game],2);
					$DST = localtime(mktime($h,$mi,0,$mo,$d,$y));
					$game_day = $DST[7]+1;
					((($game_day < $DST_start) || ($game_day >= $DST_end)) ? $timestring = "CST" : $timestring = "CDT" );
					$boxContent .= " $timestring</td><td>".$hbh."<font class=\"content\">$home_score</font>".$heh."</td>";
					$boxContent .= "<td><img src=\"/images/poollogos/".$h_id.".gif\"></td>";
					$record = " (".intval($home_wins[$h_id]+$away_wins[$h_id])."-".intval($home_losses[$h_id]+$away_losses[$h_id]).", home: ".intval($home_wins[$h_id])."-".intval($home_losses[$h_id]).")";
					$boxContent .= "<td>".$hbh."<font class=\"content\">at $d_h_rank$home</font>".$heh."$record</td><td><font class=\"content\">";
					$boxContent .= "</font></td></tr></table></td>\n";
				}
				if ($recnum%3 == 0) {
					$boxContent .= "</tr>\n<tr>";
				} else {
					$boxContent .= "\n";
				}
			}
		}
		$boxContent .= "</table></td></tr></table><br><center>\n";
		if ($gamestopick > 0) {
			$boxContent .= "<input type=\"submit\" VALUE=\"Submit\">\n";
			$boxContent .= "</center></form><br>\n";
			$boxContent .= "If you change your mind, you can ";
			$boxContent .= "come back and change your pick up until the start of THAT particular game...<br>";
			$boxContent .= "Once you've saved a choice, it'll be filled in on the form when you bring up the \"make my picks\" page.<br><br>\n";
		} else {
			$boxContent .= "</center></form><br>\n";
		}
		if ($finishedgames > 0) {
			$boxContent .= "<tr><td colspan=12><i>Winning teams ";
			$boxContent .= "are in </i><b>bold</b><i>, ";
			$boxContent .= "your picks are highlighted in Green if correct, Red if wrong";
			$boxContent .= ".</i></td></tr></table><br><br>\n";
		}
		if ($gamesfrozen == 1) {
			$boxContent .= "$gamesfrozen - One of your games is past the pick deadline, your pick is marked with a \"<img src=\"images/poollogos/check.png\">\".<br><br>\n";
		} elseif ($gamesfrozen > 1) {
			$boxContent .= "Some of your games are past the pick deadline, your picks are marked with a \"<img src=\"images/poollogos/check.png\">\".<br><br>\n";
		}
		if ($withoutspreads > 0) {
			$boxContent .= "As soon as the Spreads are posted, You'll have the chance to pick your team.<br><br>\n";
		}
	}
}

function TeamSchedule($team, $seasonID, $weekID) {
	global $testing, $poolname, $leagueID, $team, $prefix, $dbi, $module_name, $db, $home_wins, $home_losses, $away_wins, $away_losses, $boxTitle, $boxContent, $debug;
	$sql = "SELECT team_id, team_name, ncaa_div";
	$sql .= " FROM ".$prefix."_pool_teams";
	$sql .= " WHERE league = '$leagueID'";
	$sql .= " ORDER BY team_name";
	if ( $debug > 1 ) { $boxContent .= "\n<!-- HBT \$sql = '$sql' -->\n"; }
	$results = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($results)) {
		$team_id = $row['team_id'];
		$division[$team_id] = strval($row[ncaa_div]);
		$teamname[$team_id] = $row['team_name'];
		$team_num[$row['team_name']] = $team_id;
		if ( $debug > 1 ) { $boxContent .= "\n<!-- HBT team_id = '".$row['team_id']."', div = '".$row[ncaa_div]."', teamname = '".$row[team_name]."' -->"; }
	}
	# throw up a form to select the team:
	$boxContent .= "<form action=\"modules.php?name=$module_name&amp;op=TeamSchedule\" method=\"post\">";
	$boxContent .= "<input type=\"hidden\" name=\"seasonID\" value=\"".$seasonID."\">";
	$boxContent .= "<select name=\"team\">\n\t<option value=\"".$team."\">".$teamname[$team]."</option>\n";
	foreach ($teamname as $team_id => $t_name) {
		if ($team !== $team_id) {
			$boxContent .= "\t<option value=\"".$team_id."\">".$t_name."</option>\n";
		}
	}
	$boxContent .= "</select>\n";
	$boxContent .= "<input type=\"submit\" VALUE=\"Submit\">\n";
	$boxContent .= "</form>\n";
	$sql = "SELECT home, home_score, visitor, visitor_score, date, week, game";
	$sql .= " FROM ".$prefix."_pool_games";
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID' AND season = '$seasonID'";
	$sql .= " AND (home = '$team' OR visitor = '$team')";
	$sql .= " ORDER BY week";
	if ( $debug > 0 ) { $boxContent .= "<!-- HBT \$sql = '$sql' -->\n"; }
	$results = sql_query($sql, $dbi);
	$boxContent .= "<table cellpadding=\"2\" border=\"1\"><tr><th>date</th><th>opponent</th><th>result</th></tr>\n";
	while ($row = $db->sql_fetchrow($results)) {
		$boxContent .= "<tr><td>".$row[date]."</td>";
		if (($team == $row[home]) && (intval($row[home_score]) > intval($row[visitor_score]))) {
			$boxContent .= "<td>";
			$boxContent .= "<a href=\"modules.php?name=$module_name&amp;op=TeamSchedule&amp;seasonID=$seasonID&amp;team=";
			$boxContent .= $team_num[$teamname[$row[visitor]]]."\">".$teamname[$row[visitor]]."</a>";
			$boxContent .= "</td><td><b>W ".$row[home_score]." - ".$row[visitor_score]."</b></td></tr>\n";
		} elseif (($team == $row[home]) && (intval($row[home_score]) < intval($row[visitor_score]))) {
			$boxContent .= "<td>";
			$boxContent .= "<a href=\"modules.php?name=$module_name&amp;op=TeamSchedule&amp;seasonID=$seasonID&amp;team=";
			$boxContent .= $team_num[$teamname[$row[visitor]]]."\">".$teamname[$row[visitor]]."</a>";
			$boxContent .= "</td><td>L ".$row[home_score]." - ".$row[visitor_score]."</td></tr>\n";
		} elseif (($team == $row[visitor]) && (intval($row[home_score]) < intval($row[visitor_score]))) {
			$boxContent .= "<td>@ ";
			$boxContent .= "<a href=\"modules.php?name=$module_name&amp;op=TeamSchedule&amp;seasonID=$seasonID&amp;team=";
			$boxContent .= $team_num[$teamname[$row[home]]]."\">".$teamname[$row[home]]."</a>";
			$boxContent .= "</td><td><b>W ".$row[visitor_score]." - ".$row[home_score]."</b></td></tr>\n";
		} elseif (($team == $row[visitor]) && (intval($row[home_score]) > intval($row[visitor_score]))) {
			$boxContent .= "<td>@ ";
			$boxContent .= "<a href=\"modules.php?name=$module_name&amp;op=TeamSchedule&amp;seasonID=$seasonID&amp;team=";
			$boxContent .= $team_num[$teamname[$row[home]]]."\">".$teamname[$row[home]]."</a>";
			$boxContent .= "</td><td>L ".$row[visitor_score]." - ".$row[home_score]."</td></tr>\n";
		} elseif (($team == $row[home]) && (intval($row[home_score]) == intval($row[visitor_score]))) { 
			$boxContent .= "<td>";
			$boxContent .= "<a href=\"modules.php?name=$module_name&amp;op=TeamSchedule&amp;seasonID=$seasonID&amp;team=";
			$boxContent .= $team_num[$teamname[$row[visitor]]]."\">".$teamname[$row[visitor]]."</a>";
			$boxContent .= "</td><td>&nbsp</td></tr>\n";
		} elseif (($team == $row[visitor]) && (intval($row[home_score]) == intval($row[visitor_score]))) { 
			$boxContent .= "<td>@ ";
			$boxContent .= "<a href=\"modules.php?name=$module_name&amp;op=TeamSchedule&amp;seasonID=$seasonID&amp;team=";
			$boxContent .= $team_num[$teamname[$row[home]]]."\">".$teamname[$row[home]]."</a>";
			$boxContent .= "</td><td>&nbsp</td></tr>\n";
		} 
	}
	$boxContent .= "</table>\n<br>\n";
}

function TeamRecords($seasonID, $leagueID, $weekID) {
	global $poolname, $testing, $top25, $prefix, $dbi, $module_name, $db, $home_wins, $home_losses, $away_wins, $away_losses;
	$sql = "SELECT home, home_score, visitor, visitor_score, week, game";
	$sql .= " FROM ".$prefix."_pool_games";
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$leagueID' AND season = '$seasonID' AND week <= '$weekID'";
	$sql .= " AND (home_score IS NOT NULL and visitor_score IS NOT NULL) ORDER BY home, week";
	$results = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($results)) {
		$ht = $row['home'];
		$vt = $row['visitor'];
		$hs = intval($row['home_score']);
		$vs = intval($row['visitor_score']);
		$w = $row['week'];
		$g = $row['game'];
		if ($hs > $vs) {
			$home_wins[$ht]++;
			$away_losses[$vt]++;
		} else {
			$home_losses[$ht]++;
			$away_wins[$vt]++;
		}
	}
}
