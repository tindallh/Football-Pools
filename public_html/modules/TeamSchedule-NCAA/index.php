<?php
/*****************************************************************************/
/* Football Pool module for PHP-Nuke - TeamSchedule module                   */
/*   written by: Henry B. Tindall, Jr.                                       */
/*   version 18.01                                                            */
/*   first written: 29 Nov 2009                                              */
/*   last modified: 12 Sep 2018                                              */
/*         18.01 - 12 Sep 2018 - Added logic to account for NFL ties.        */
/*          2.01 - 23 Aug 2016 - Added logic to account for NFL teams        */
/*                 moving cities, seperate League modules.                   */
/*          1.00 - 29 Nov 2009 - Initial Release                             */
/*****************************************************************************/
$version='18.01.11';
$boxContent .= "\n<!-- Version $version -->\n"; 

global $poolname, $DST_start, $DST_end, $home_wins, $home_losses, $away_wins, $away_losses, $network;

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
include("themes/$ThemeSel/theme.php");
include("includes/meta.php");
include("includes/javascript.php");
include("includes/my_header.php");
include("includes/counter.php");

if (file_exists("includes/custom_files/custom_mainfile.php")) {
        include_once("includes/custom_files/custom_mainfile.php");
}

$debug=0;

/*  A few variables we use later on....       */
require_once("config.inc.php");

# If they weren't read in from the config file, do dates and time.
if (!$leagueID) { $leagueID = "NFL"; }
if (!$today_date) { $today_date = date("Y-m-d"); }
if (!$now_time) { $now_time = date("Hi"); }
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
        $sql .= " AND league = '$leagueID' AND season = ".$seasonID."";
        if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
        $sql .= " ORDER BY date, time limit 1";
#       $boxContent .= "<!-- HBT \'$sql\' -->\n";
        $result = $db->sql_query($sql);
        $object = sql_fetch_object($result, $dbi);
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

$prev_season = $seasonID-1;
$next_season = $seasonID+1;
if (!$sweekID) { $sweekID = $weekID; }
$prev_week = $sweekID - 1;
$next_week = $sweekID + 1;
$current_week = $lrweek;

global $poolname, $testing, $top25, $team, $prefix, $dbi, $module_name, $db, $home_wins, $home_losses, $away_wins, $away_losses, $boxTitle, $boxContent;
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
		$team_id = $row['team_id'];
		if ($leagueID == 'NCAA') { $ncaa_div[$team_id] = strval($row['ncaa_div']); }
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
	if ($debug > 0 ) { $boxContent .= "\n<!-- team_id = '".$row['team_id']."', mascot = '".$row['mascot']."' -->"; }
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
	if ($debug > 0) {	$boxContent .= "\n<!-- team_id = '".$row['team_id']."', mascot = '".$row['mascot']."' -->"; }
}
# throw up a form to select the team:
$boxContent .= "<form action=\"modules.php?name=$module_name\" method=\"post\">";
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
	$home_wins=$away_wins=$home_losses=$away_losses=$home_ties=$away_ties='';
	TeamRecords($seasonID, $leagueID, $week);
	$visitor = $row['visitor'];
	$home = $row['home'];
	$home_score = intval($row[home_score]);
	$visitor_score = intval($row[visitor_score]);
	$neutral = intval($row[neutral]);
	if ($neutral == 0) {
		if (($team == $home) && ($home_score > $visitor_score)) {
			$boxContent .= "<td><a href=\"modules.php?name=$module_name&amp;seasonID=$seasonID&amp&amp;team=".$visitor."\">";
			if ($row[visitor_rank]) { $boxContent .= "#".$row[visitor_rank]." "; }
			$record = " (".intval($home_wins[$visitor]+$away_wins[$visitor])."-".intval($home_losses[$visitor]+$away_losses[$visitor]);
			if ( intval($home_ties[$visitor]+$away_ties[$visitor]) > 0 ) { $record .= "-".intval($home_ties[$visitor]+$away_ties[$visitor]); }
			$record .= ")";
			$boxContent .= $teamname[$visitor]."</a> ".$record;
			$boxContent .= "</td><td><b>W ".$home_score." - ".$visitor_score."</b></td></tr>\n";
			$wins++;
		} elseif (($team == $visitor) && ($home_score < $visitor_score)) {
			$boxContent .= "<td>@ <a href=\"modules.php?name=$module_name&amp;seasonID=$seasonID&amp&amp;team=".$home."\">";
			if ($row[home_rank]) { $boxContent .= "#".$row[home_rank]." "; }
			$record = " (".intval($home_wins[$home]+$away_wins[$home])."-".intval($home_losses[$home]+$away_losses[$home]);
			if ( $home_ties[$home] > 0 || $away_ties[$home] > 0 ) { $record .= "-".intval($home_ties[$home]+$away_ties[$home]); }
			$record .= ")";
			$boxContent .= $teamname[$home]."</a> ".$record;
			$boxContent .= "</td><td><b>W ".$visitor_score." - ".$home_score."</b></td></tr>\n";
			$wins++;
		} elseif (($team == $home) && ($home_score < $visitor_score)) {
			$boxContent .= "<td><a href=\"modules.php?name=$module_name&amp;seasonID=$seasonID&amp&amp;team=".$visitor."\">";
			if ($row[visitor_rank]) { $boxContent .= "#".$row[visitor_rank]." "; }
			$record = " (".intval($home_wins[$visitor]+$away_wins[$visitor])."-".intval($home_losses[$visitor]+$away_losses[$visitor]);
			if ( intval($home_ties[$visitor]+$away_ties[$visitor]) > 0 ) { $record .= "-".intval($home_ties[$visitor]+$away_ties[$visitor]); }
			$record .= ")";
			$boxContent .= $teamname[$visitor]."</a> ".$record;
			$boxContent .= "</td><td>L ".$home_score." - ".$visitor_score."</td></tr>\n";
			$losses++;
		} elseif (($team == $visitor) && ($home_score > $visitor_score)) {
			$boxContent .= "<td>@ <a href=\"modules.php?name=$module_name&amp;seasonID=$seasonID&amp&amp;team=".$home."\">";
			if ($row[home_rank]) { $boxContent .= "#".$row[home_rank]." "; }
			$record = " (".intval($home_wins[$home]+$away_wins[$home])."-".intval($home_losses[$home]+$away_losses[$home]);
			if ( $home_ties[$home] > 0 || $away_ties[$home] > 0 ) { $record .= "-".intval($home_ties[$home]+$away_ties[$home]); }
			$record .= ")";
			$boxContent .= $teamname[$home]."</a> ".$record;
			$boxContent .= "</td><td>L ".$visitor_score." - ".$home_score."</td></tr>\n";
			$losses++;
		#} elseif (($team == $home) && (array_key_exists('home_score') && ( $home_score == $visitor_score))) {
		} elseif (($team == $home) && ($visitor_score > 0 && ( $home_score == $visitor_score))) {
			$boxContent .= "<td><a href=\"modules.php?name=$module_name&amp;seasonID=$seasonID&amp&amp;team=".$visitor."\">";
			if ($row[visitor_rank]) { $boxContent .= "#".$row[visitor_rank]." "; }
			$record = " (".intval($home_wins[$visitor]+$away_wins[$visitor])."-".intval($home_losses[$visitor]+$away_losses[$visitor]);
			if ( intval($home_ties[$visitor]+$away_ties[$visitor]) > 0 ) { $record .= "-".intval($home_ties[$visitor]+$away_ties[$visitor]); }
			$record .= ")";
			$boxContent .= $teamname[$visitor]."</a> ".$record;
			$boxContent .= "</td><td>T ".$visitor_score." - ".$home_score."</td></tr>\n";
			$ties++;
		#} elseif (($team == $visitor) && (array_key_exists('visitor_score') && ( $home_score == $visitor_score))) {
		} elseif (($team == $visitor) && ($home_score > 0  && ( $home_score == $visitor_score))) {
			$boxContent .= "<td>@ <a href=\"modules.php?name=$module_name&amp;seasonID=$seasonID&amp&amp;team=".$home."\">";
			if ($row[home_rank]) { $boxContent .= "#".$row[home_rank]." "; }
			$record = " (".intval($home_wins[$home]+$away_wins[$home])."-".intval($home_losses[$home]+$away_losses[$home]);
			if ( intval($home_ties[$home]+$away_ties[$home]) > 0 ) { $record .= "-".intval($home_ties[$home]+$away_ties[$home]); }
			$record .= ")";
			$boxContent .= $teamname[$home]."</a> ".$record;
			$boxContent .= "</td><td>T ".$visitor_score." - ".$home_score."</td></tr>\n";
			$ties++;
		#} elseif (($team == $home) && !(array_key_exists('home_score'))) {
		} elseif (($team == $home) && ($home_score == $visitor_score)) {
			$boxContent .= "<td><a href=\"modules.php?name=$module_name&amp;seasonID=$seasonID&amp&amp;team=".$visitor."\">";
			if ($row[visitor_rank]) { $boxContent .= "#".$row[visitor_rank]." "; }
			$record = " (".intval($home_wins[$visitor]+$away_wins[$visitor])."-".intval($home_losses[$visitor]+$away_losses[$visitor]);
			if ( intval($home_ties[$visitor]+$away_ties[$visitor]) > 0 ) { $record .= "-".intval($home_ties[$visitor]+$away_ties[$visitor]); }
			$record .= ")";
			$boxContent .= $teamname[$visitor]."</a> ".$record;
			$boxContent .= "</td><td>&nbsp</td></tr>\n";
		#} elseif (($team == $visitor) && !(array_key_exists('visitor_score'))) {
		} elseif (($team == $visitor) && ($home_score == $visitor_score)) {
			$boxContent .= "<td>@ <a href=\"modules.php?name=$module_name&amp;seasonID=$seasonID&amp&amp;team=".$home."\">";
			if ($row[home_rank]) { $boxContent .= "#".$row[home_rank]." "; }
			$record = " (".intval($home_wins[$home]+$away_wins[$home])."-".intval($home_losses[$home]+$away_losses[$home]);
			if ( intval($home_ties[$home]+$away_ties[$home]) > 0 ) { $record .= "-".intval($home_ties[$home]+$away_ties[$home]); }
			$record .= ")";
			$boxContent .= $teamname[$home]."</a> ".$record;
			$boxContent .= "</td><td>&nbsp</td></tr>\n";
		}
	} else {
		if (($team == $home) && ($home_score > $visitor_score)) {
			$boxContent .= "<td>vs. <a href=\"modules.php?name=$module_name&amp;seasonID=$seasonID&amp&amp;team=".$visitor."\">";
			if ($row[visitor_rank]) { $boxContent .= "#".$row[visitor_rank]." "; }
			$record = " (".intval($home_wins[$visitor]+$away_wins[$visitor])."-".intval($home_losses[$visitor]+$away_losses[$visitor]);
			if ( intval($home_ties[$visitor]+$away_ties[$visitor]) > 0 ) { $record .= "-".intval($home_ties[$visitor]+$away_ties[$visitor]); }
			$record .= ")";
			$boxContent .= $teamname[$visitor]."</a> ".$record;
			$boxContent .= "</td><td><b>W ".$home_score." - ".$visitor_score."</b></td></tr>\n";
			$wins++;
		} elseif (($team == $visitor) && ($home_score < $visitor_score)) {
			$boxContent .= "<td>vs. <a href=\"modules.php?name=$module_name&amp;seasonID=$seasonID&amp&amp;team=".$home."\">";
			if ($row[home_rank]) { $boxContent .= "#".$row[home_rank]." "; }
			$record = " (".intval($home_wins[$home]+$away_wins[$home])."-".intval($home_losses[$home]+$away_losses[$home]);
			if ( intval($home_ties[$home]+$away_ties[$home]) > 0 ) { $record .= "-".intval($home_ties[$home]+$away_ties[$home]); }
			$record .= ")";
			$boxContent .= $teamname[$home]."</a> ".$record;
			$boxContent .= "</td><td><b>W ".$visitor_score." - ".$home_score."</b></td></tr>\n";
			$wins++;
		} elseif (($team == $home) && ($home_score < $visitor_score)) {
			$boxContent .= "<td>vs. <a href=\"modules.php?name=$module_name&amp;seasonID=$seasonID&amp&amp;team=".$visitor."\">";
			if ($row[visitor_rank]) { $boxContent .= "#".$row[visitor_rank]." "; }
			$record = " (".intval($home_wins[$visitor]+$away_wins[$visitor])."-".intval($home_losses[$visitor]+$away_losses[$visitor]);
			if ( intval($home_ties[$visitor]+$away_ties[$visitor]) > 0 ) { $record .= "-".intval($home_ties[$visitor]+$away_ties[$visitor]); }
			$record .= ")";
			$boxContent .= $teamname[$visitor]."</a> ".$record;
			$boxContent .= "</td><td>L ".$home_score." - ".$visitor_score."</td></tr>\n";
			$losses++;
		} elseif (($team == $visitor) && ($home_score > $visitor_score)) {
			$boxContent .= "<td>vs. <a href=\"modules.php?name=$module_name&amp;seasonID=$seasonID&amp&amp;team=".$home."\">";
			if ($row[home_rank]) { $boxContent .= "#".$row[home_rank]." "; }
			$record = " (".intval($home_wins[$home]+$away_wins[$home])."-".intval($home_losses[$home]+$away_losses[$home]);
			if ( intval($home_ties[$home]+$away_ties[$home]) > 0 ) { $record .= "-".intval($home_ties[$home]+$away_ties[$home]); }
			$record .= ")";
			$boxContent .= $teamname[$home]."</a> ".$record;
			$boxContent .= "</td><td>L ".$visitor_score." - ".$home_score."</td></tr>\n";
			$losses++;
		#} elseif (($team == $home) && (($home_score > 0) && ( $home_score == $visitor_score))) {
		} elseif (($team == $home) && ($visitor_score > 0 && ( $home_score == $visitor_score))) {
			$boxContent .= "<td>vs. <a href=\"modules.php?name=$module_name&amp;seasonID=$seasonID&amp&amp;team=".$visitor."\">";
			if ($row[visitor_rank]) { $boxContent .= "#".$row[visitor_rank]." "; }
			$record = " (".intval($home_wins[$visitor]+$away_wins[$visitor])."-".intval($home_losses[$visitor]+$away_losses[$visitor]);
			if ( intval($home_ties[$visitor]+$away_ties[$visitor]) > 0 ) { $record .= "-".intval($home_ties[$visitor]+$away_ties[$visitor]); }
			$record .= ")";
			$boxContent .= $teamname[$visitor]."</a> ".$record;
			$boxContent .= "</td><td>T ".$visitor_score." - ".$home_score."</td></tr>\n";
			$ties++;
		#} elseif (($team == $visitor) && (($visitor_score > 0) && ( $home_score == $visitor_score))) {
		} elseif (($team == $visitor) && ($home_score > 0  && ( $home_score == $visitor_score))) {
			$boxContent .= "<td>vs. <a href=\"modules.php?name=$module_name&amp;seasonID=$seasonID&amp&amp;team=".$home."\">";
			if ($row[home_rank]) { $boxContent .= "#".$row[home_rank]." "; }
			$record = " (".intval($home_wins[$home]+$away_wins[$home])."-".intval($home_losses[$home]+$away_losses[$home]);
			if ( intval($home_ties[$home]+$away_ties[$home]) > 0 ) { $record .= "-".intval($home_ties[$home]+$away_ties[$home]); }
			$record .= ")";
			$boxContent .= $teamname[$home]."</a> ".$record;
			$boxContent .= "</td><td>T ".$visitor_score." - ".$home_score."</td></tr>\n";
			$ties++;
		} elseif (($team == $home) && ($home_score == $visitor_score)) {
			$boxContent .= "<td>vs. <a href=\"modules.php?name=$module_name&amp;seasonID=$seasonID&amp&amp;team=".$visitor."\">";
			if ($row[visitor_rank]) { $boxContent .= "#".$row[visitor_rank]." "; }
			$record = " (".intval($home_wins[$visitor]+$away_wins[$visitor])."-".intval($home_losses[$visitor]+$away_losses[$visitor]);
			if ( intval($home_ties[$visitor]+$away_ties[$visitor]) > 0 ) { $record .= "-".intval($home_ties[$visitor]+$away_ties[$visitor]); }
			$record .= ")";
			$boxContent .= $teamname[$visitor]."</a> ".$record;
			$boxContent .= "</td><td>&nbsp</td></tr>\n";
		} elseif (($team == $visitor) && ($home_score == $visitor_score)) {
			$boxContent .= "<td>vs. <a href=\"modules.php?name=$module_name&amp;seasonID=$seasonID&amp&amp;team=".$home."\">";
			if ($row[home_rank]) { $boxContent .= "#".$row[home_rank]." "; }
			$record = " (".intval($home_wins[$home]+$away_wins[$home])."-".intval($home_losses[$home]+$away_losses[$home]);
			if ( intval($home_ties[$home]+$away_ties[$home]) > 0 ) { $record .= "-".intval($home_ties[$home]+$away_ties[$home]); }
			$record .= ")";
			$boxContent .= $teamname[$home]."</a> ".$record;
			$boxContent .= "</td><td>&nbsp</td></tr>\n";
		}
	}
}
$boxContent .= "<tr><td colspan=3><center><h3>Record: $wins-$losses";
if ($ties > 0) { $boxContent .= "-".$ties; };
$boxContent .= "</h3></td></tr>\n";
$boxContent .= "</table>\n<br>\n";

themecenterbox($boxTitle, $boxContent);

# include ('footer.php');

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
                #} elseif (array_key_exists('hs') && ($hs == $vs)) {
                } elseif ($hs > 0 && $hs == $vs) {
                	$home_ties[$ht]++;
                	$away_ties[$vt]++;
                }
        }
}