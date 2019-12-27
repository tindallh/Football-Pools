<?php
/*****************************************************************************/
/* Football Pool module for PHP-Nuke - SchedPop module                       */
/*   written by: Henry B. Tindall, Jr.                                       */
/*   version 4.07                                                            */
/*   first written: 13 Nov 2013                                              */
/*   last modified: 23 Aug 2016                                              */
/*                                                                           */
/* Version 19.01 - 23 Sep 2019 - fixed pop-up schedule error for Neutral     */
/*                 site.                                                     */
/*         18.01 - 12 Sep 2018 - new versioning, added logic for NFL ties.   */
/*          2.01 - 23 Aug 2016 - Added logic to account for NFL teams        */
/*                 moving cities, seperate League modules.                   */
/*          1.00 - 13 Nov 2013 - Initial Release - ported from TeamSchedule  */
/*                 To create a tiny schedule for the popup over the helmets  */ 
/*****************************************************************************/
$version = '18.01.24';
$boxContent .= "<!-- Version = $version -->\n";

global $poolname, $DST_start, $DST_end, $home_wins, $home_losses, $away_wins, $away_losses, $home_ties, $away_ties, $network;

if (!eregi("modules.php", $_SERVER['SCRIPT_NAME'])) {
    die ("You can't access this file directly...");
}

$debug = 0;

require_once("mainfile.php");
$module_name = basename(dirname(__FILE__));
include("themes/$ThemeSel/theme.php");
$sql = "SELECT week, date, time, tvnetwork FROM ".$prefix."_pool_games";
$sql .= " WHERE ((date > '$today_date') OR (date = '$today_date' AND time > '$now_time'))";
$sql .= " AND league = '$leagueID' AND season = ".$seasonID."";
$sql .= " ORDER BY date, time limit 1";
$result = $db->sql_query($sql);
$object = sql_fetch_object($result, $dbi);
$sql = "SELECT home_score, visitor_score, week FROM ".$prefix."_pool_games";
if ($testing == '1') { $sql .= "_test"; }
$sql .= " WHERE league = '$leagueID' AND season = '$seasonID' AND week = '$weekID'";
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

$prev_season = $seasonID-1;
$next_season = $seasonID+1;
if (!$sweekID) { $sweekID = $weekID; }
$prev_week = $sweekID - 1;
$next_week = $sweekID + 1;
$current_week = $lrweek;

global $boxContent, $poolname, $testing, $top25, $team, $prefix, $dbi, $module_name, $db, $home_wins, $home_losses, $away_wins, $away_losses, $boxTitle, $boxContent;
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
}
$sql = "SELECT home, home_score, visitor, visitor_score, home_rank, visitor_rank, date, week, game, neutral";
$sql .= " FROM ".$prefix."_pool_games";
if ($testing == '1') { $sql .= "_test"; }
$sql .= " WHERE league = '$leagueID' AND season = '$seasonID'";
$sql .= " AND (home = '$team' OR visitor = '$team')";
$sql .= " ORDER BY week";
$results = sql_query($sql, $dbi);
# Let's put in the Helmet and a Team name before the schedule to make it look pretty....
# $boxContent .= "\n<center>";
if (!$team) { $team = 0; }
$boxContent .= "<center><table cellpadding=\"0\" border=\"1\">";
$boxContent .= "\n<tr><td colspan=3 valign=\"middle\" align=\"center\" bgcolor=\"white\">";
$boxContent .= "<img src=\"images/poollogos/helmets/".$team.".gif\" width=\"80\" height=\"50\"><br>\n";
$boxContent .= "<b>$teamname[$team] $mascot[$team]</b><br>\n";
$boxContent .= "<tr><th><font size=-2>date</font></th><th><font size=-2>opponent</font></th><th><font size=-2>result</font></th></tr>\n";
$wins=$losses=$ties=0;
while ($row = $db->sql_fetchrow($results)) {
	$boxContent .= "<tr><td><font size=-2>".$row[date]."</font></td>";
	$week = $row['week']-1;
	$home_wins=$away_wins=$home_losses=$away_losses=$home_ties=$away_ties='';
	TeamRecords($seasonID, $leagueID, $week);
	$visitor = $row['visitor'];
	$home = $row['home'];
	$home_score = intval($row[home_score]);
	$visitor_score = intval($row[visitor_score]);
	$neutral = intval($row[neutral]);
	if ($neutral == 0) {
		if (($team == $home) && ($home_score > $visitor_score)) {
			$boxContent .= "<td><font size=-2>";
			if ($row[visitor_rank]) { $boxContent .= "#".$row[visitor_rank]." "; }
			$record = " (".intval($home_wins[$visitor]+$away_wins[$visitor])."-".intval($home_losses[$visitor]+$away_losses[$visitor]);
			if ( intval($home_ties[$visitor]+$away_ties[$visitor]) > 0 ) { $record .= "-".intval($home_ties[$visitor]+$away_ties[$visitor]); }
			$record .= ")";
			$boxContent .= $teamname[$visitor]." ".$record;
			$boxContent .= "</font></td><td><font size=-2><b>W ".$home_score." - ".$visitor_score."</b></font></td></tr>\n";
			$wins++;
		} elseif (($team == $visitor) && ($home_score < $visitor_score)) {
			$boxContent .= "<td><font size=-2>@ ";
			if ($row[home_rank]) { $boxContent .= "#".$row[home_rank]." "; }
			$record = " (".intval($home_wins[$home]+$away_wins[$home])."-".intval($home_losses[$home]+$away_losses[$home]);
			if ( intval($home_ties[$home]+$away_ties[$home]) > 0 ) { $record .= "-".intval($home_ties[$home]+$away_ties[$home]); }
			$record .= ")";
			$boxContent .= $teamname[$home]." ".$record;
			$boxContent .= "</font></td><td><font size=-2><b>W ".$visitor_score." - ".$home_score."</b></font></td></tr>\n";
			$wins++;
		} elseif (($team == $home) && ($home_score < $visitor_score)) {
			$boxContent .= "<td><font size=-2>";
			if ($row[visitor_rank]) { $boxContent .= "#".$row[visitor_rank]." "; }
			$record = " (".intval($home_wins[$visitor]+$away_wins[$visitor])."-".intval($home_losses[$visitor]+$away_losses[$visitor]);
			if ( intval($home_ties[$visitor]+$away_ties[$visitor]) > 0 ) { $record .= "-".intval($home_ties[$visitor]+$away_ties[$visitor]); }
			$record .= ")";
			$boxContent .= $teamname[$visitor]." ".$record;
			$boxContent .= "</font></td><td><font size=-2>L ".$home_score." - ".$visitor_score."</font></td></tr>\n";
			$losses++;
		} elseif (($team == $visitor) && ($home_score > $visitor_score)) {
			$boxContent .= "<td><font size=-2>@ ";
			if ($row[home_rank]) { $boxContent .= "#".$row[home_rank]." "; }
			$record = " (".intval($home_wins[$home]+$away_wins[$home])."-".intval($home_losses[$home]+$away_losses[$home]);
			if ( intval($home_ties[$home]+$away_ties[$home]) > 0 ) { $record .= "-".intval($home_ties[$home]+$away_ties[$home]); }
			$record .= ")";
			$boxContent .= $teamname[$home]." ".$record;
			$boxContent .= "</font></td><td><font size=-2>L ".$visitor_score." - ".$home_score."</font></td></tr>\n";
			$losses++;
		} elseif (($team == $home) && ($visitor_score > 0 && ( $home_score == $visitor_score))) {
			$boxContent .= "<td><font size=-2>";
			if ($row[visitor_rank]) { $boxContent .= "#".$row[visitor_rank]." "; }
			$record = " (".intval($home_wins[$visitor]+$away_wins[$visitor])."-".intval($home_losses[$visitor]+$away_losses[$visitor]);
			if ( intval($home_ties[$visitor]+$away_ties[$visitor]) > 0 ) { $record .= "-".intval($home_ties[$visitor]+$away_ties[$visitor]); }
			$record .= ")";
			$boxContent .= $teamname[$visitor]." ".$record;
			$boxContent .= "</td><td><font size=-2>T ".$visitor_score." - ".$home_score."</font></td></tr>\n";
			$ties++;
		} elseif (($team == $visitor) && ($home_score > 0  && ( $home_score == $visitor_score))) {
			$boxContent .= "<td><font size=-2>@ ";
			if ($row[home_rank]) { $boxContent .= "#".$row[home_rank]." "; }
			$record = " (".intval($home_wins[$home]+$away_wins[$home])."-".intval($home_losses[$home]+$away_losses[$home]);
			if ( intval($home_ties[$home]+$away_ties[$home]) > 0 ) { $record .= "-".intval($home_ties[$home]+$away_ties[$home]); }
			$record .= ")";
			$boxContent .= $teamname[$home]." ".$record;
			$boxContent .= "</td><td><font size=-2>T ".$visitor_score." - ".$home_score."</font></td></tr>\n";
			$ties++;
		} elseif (($team == $home) && ( $home_score == $visitor_score )) {
			$boxContent .= "<td><font size=-2>";
			if ($row[visitor_rank]) { $boxContent .= "#".$row[visitor_rank]." "; }
			$record = " (".intval($home_wins[$visitor]+$away_wins[$visitor])."-".intval($home_losses[$visitor]+$away_losses[$visitor]);
			if ( intval($home_ties[$visitor]+$away_ties[$visitor]) > 0 ) { $record .= "-".intval($home_ties[$visitor]+$away_ties[$visitor]); }
			$record .= ")";
			$boxContent .= $teamname[$visitor]." ".$record;
			$boxContent .= "</td><td><font size=-2>&nbsp</font></td></tr>\n";
		} elseif (($team == $visitor) && ( $home_score == $visitor_score )) {
			$boxContent .= "<td><font size=-2>@ ";
			if ($row[home_rank]) { $boxContent .= "#".$row[home_rank]." "; }
			$record = " (".intval($home_wins[$home]+$away_wins[$home])."-".intval($home_losses[$home]+$away_losses[$home]);
			if ( intval($home_ties[$home]+$away_ties[$home]) > 0 ) { $record .= "-".intval($home_ties[$home]+$away_ties[$home]); }
			$record .= ")";
			$boxContent .= $teamname[$home]." ".$record;
			if ($debug > 0) {$boxContent .= "<!-- \$home_ties[$home]=\"$home_ties[$home]\", \$away_ties[$home]=\"$away_ties[$home]\" -->"; }
			$boxContent .= "</td><td><font size=-2>&nbsp</font></td></tr>\n";
		}
	} else {
		if (($team == $home) && ($home_score > $visitor_score)) {
			$boxContent .= "<td><font size=-2>vs. ";
			if ($row[visitor_rank]) { $boxContent .= "#".$row[visitor_rank]." "; }
			$record = " (".intval($home_wins[$visitor]+$away_wins[$visitor])."-".intval($home_losses[$visitor]+$away_losses[$visitor]);
			if ( intval($home_ties[$visitor]+$away_ties[$visitor]) > 0 ) { $record .= "-".intval($home_ties[$visitor]+$away_ties[$visitor]); }
			$record .= ")";
			$boxContent .= $teamname[$visitor]." ".$record;
			$boxContent .= "</font></td><td><font size=-2><b>W ".$home_score." - ".$visitor_score."</b></font></td></tr>\n";
			$wins++;
		} elseif (($team == $visitor) && ($home_score < $visitor_score)) {
			$boxContent .= "<td><font size=-2>vs. ";
			if ($row[home_rank]) { $boxContent .= "#".$row[home_rank]." "; }
			$record = " (".intval($home_wins[$home]+$away_wins[$home])."-".intval($home_losses[$home]+$away_losses[$home]);
			if ( intval($home_ties[$home]+$away_ties[$home]) > 0 ) { $record .= "-".intval($home_ties[$home]+$away_ties[$home]); }
			$record .= ")";
			$boxContent .= $teamname[$home]." ".$record;
			$boxContent .= "</font></td><td><font size=-2><b>W ".$visitor_score." - ".$home_score."</b></font></td></tr>\n";
			$wins++;
		} elseif (($team == $home) && ($home_score < $visitor_score)) {
			$boxContent .= "<td><font size=-2>vs. ";
			if ($row[visitor_rank]) { $boxContent .= "#".$row[visitor_rank]." "; }
			$record = " (".intval($home_wins[$visitor]+$away_wins[$visitor])."-".intval($home_losses[$visitor]+$away_losses[$visitor]);
			if ( intval($home_ties[$visitor]+$away_ties[$visitor]) > 0 ) { $record .= "-".intval($home_ties[$visitor]+$away_ties[$visitor]); }
			$record .= ")";
			$boxContent .= $teamname[$visitor]." ".$record;
			$boxContent .= "</font></td><td><font size=-2>L ".$home_score." - ".$visitor_score."</font></td></tr>\n";
			$losses++;
		} elseif (($team == $visitor) && ($home_score > $visitor_score)) {
			$boxContent .= "<td><font size=-2>vs. ";
			if ($row[home_rank]) { $boxContent .= "#".$row[home_rank]." "; }
			$record = " (".intval($home_wins[$home]+$away_wins[$home])."-".intval($home_losses[$home]+$away_losses[$home]);
			if ( intval($home_ties[$home]+$away_ties[$home]) > 0 ) { $record .= "-".intval($home_ties[$home]+$away_ties[$home]); }
			$record .= ")";
			$boxContent .= $teamname[$home]." ".$record;
			$boxContent .= "</font></td><td><font size=-2>L ".$visitor_score." - ".$home_score."</font></td></tr>\n";
			$losses++;
		} elseif (($team == $home) && ($visitor_score > 0 && ( $home_score == $visitor_score))) {
			$boxContent .= "<td><font size=-2>vs. ";
			if ($row[visitor_rank]) { $boxContent .= "#".$row[visitor_rank]." "; }
			$record = " (".intval($home_wins[$visitor]+$away_wins[$visitor])."-".intval($home_losses[$visitor]+$away_losses[$visitor]);
			if ( intval($home_ties[$visitor]+$away_ties[$visitor]) > 0 ) { $record .= "-".intval($home_ties[$visitor]+$away_ties[$visitor]); }
			$record .= ")";
			$boxContent .= $teamname[$visitor]." ".$record;
			$boxContent .= "</td><td><font size=-2>T ".$visitor_score." - ".$home_score."</font></td></tr>\n";
			$ties++;
		} elseif (($team == $visitor) && ($home_score > 0  && ( $home_score == $visitor_score))) {
			$boxContent .= "<td><font size=-2>vs. ";
			if ($row[home_rank]) { $boxContent .= "#".$row[home_rank]." "; }
			$record = " (".intval($home_wins[$home]+$away_wins[$home])."-".intval($home_losses[$home]+$away_losses[$home]);
			if ( intval($home_ties[$home]+$away_ties[$home]) > 0 ) { $record .= "-".intval($home_ties[$home]+$away_ties[$home]); }
			$record .= ")";
			$boxContent .= $teamname[$home]." ".$record;
			$boxContent .= "</td><td><font size=-2>T ".$visitor_score." - ".$home_score."</font></td></tr>\n";
			$ties++;
		} elseif (($team == $home) && ( $home_score == $visitor_score )) {
			$boxContent .= "<td><font size=-2>vs. ";
			if ($row[visitor_rank]) { $boxContent .= "#".$row[visitor_rank]." "; }
			$record = " (".intval($home_wins[$visitor]+$away_wins[$visitor])."-".intval($home_losses[$visitor]+$away_losses[$visitor]);
			if ( intval($home_ties[$visitor]+$away_ties[$visitor]) > 0 ) { $record .= "-".intval($home_ties[$visitor]+$away_ties[$visitor]); }
			$record .= ")";
			$boxContent .= $teamname[$visitor]." ".$record;
			$boxContent .= "</td><td><font size=-2>&nbsp;</font></td></tr>\n";
		} elseif (($team == $visitor) && ( $home_score == $visitor_score )) {
			$boxContent .= "<td><font size=-2>vs. ";
			if ($row[home_rank]) { $boxContent .= "#".$row[home_rank]." "; }
			$record = " (".intval($home_wins[$home]+$away_wins[$home])."-".intval($home_losses[$home]+$away_losses[$home]);
			if ( intval($home_ties[$home]+$away_ties[$home]) > 0 ) { $record .= "-".intval($home_ties[$home]+$away_ties[$home]); }
			$record .= ")";
			$boxContent .= $teamname[$home]." ".$record;
			$boxContent .= "</td><td><font size=-2>&nbsp;test</font></td></tr>\n";
		}
	}
}
$boxContent .= "<tr><td colspan=3><center><font size=-2>Record: $wins-$losses";
if ( $ties > 0 ) { $boxContent .= "-$ties"; }
$boxContent .= "</font></td></tr>\n";
$boxContent .= "</table></center><br>\n";

themecenterbox($boxTitle, $boxContent);

/*********************************************************/
/* Functions                                             */
/*********************************************************/


function TeamRecords($seasonID, $leagueID, $weekID) {
	global $boxContent, $poolname, $testing, $top25, $prefix, $dbi, $module_name, $db, $home_wins, $home_losses, $away_wins, $away_losses, $home_ties, $away_ties;
	$sql2 = "SELECT home, home_score, visitor, visitor_score, week, game";
	#$sql2 = "SELECT home, home_score, visitor, visitor_score";
	$sql2 .= " FROM ".$prefix."_pool_games";
	if ($testing == '1') { $sql2 .= "_test"; }
	$sql2 .= " WHERE league = '$leagueID' AND season = '$seasonID' AND week <= '$weekID'";
	$sql2 .= " AND (home_score IS NOT NULL and visitor_score IS NOT NULL) ORDER BY home, week";
	$results2 = sql_query($sql2, $dbi);
	while ($row = $db->sql_fetchrow($results2)) {
		$ht = $row['home'];
		$vt = $row['visitor'];
		$hs = $row['home_score'];
		$vs = $row['visitor_score'];
		$w = $row['week'];
		$g = $row['game'];
		if ($hs > $vs) {
			$home_wins[$ht]++;
			$away_losses[$vt]++;
		} elseif ($hs < $vs) {
			$home_losses[$ht]++;
			$away_wins[$vt]++;
		#} elseif (array_key_exists('hs') && $hs == $vs) {
		} elseif ($hs > 0 && $hs == $vs) {
			if ($debug > 0) {$boxContent .= "<!-- \$home_ties[$ht]=\"$home_ties[$ht]\", \$away_ties[$ht]=\"$away_ties[$ht]\" -->"; }
			$home_ties[$ht]++;
			$away_ties[$vt]++;
		}
	}
}