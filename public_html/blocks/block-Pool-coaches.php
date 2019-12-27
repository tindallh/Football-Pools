<?php
global $user_prefix, $cookie, $prefix, $dbi, $module_name, $db, $today_date, $now_time;

if (eregi("block-NCAA.php", $_SERVER['SCRIPT_NAME'])) {
    Header("Location: index.php");
    die();
}

$content .= "<center><table cellpadding=1 cellspacing=4><tr><td valign=\"top\">\n";

$content .= "<table cellpadding=1 cellspacing=4>\n";
$content .= "<tr><th colspan=2>NCAA</th></tr>\n";

$sql = "select latestteam.team_name team, latestcoach.Name Name, latestcoach.end end from";
$sql .= " ( select t.team_id team_id, t.team_name team_name, t.league league, t.season season";
$sql .= " from `nuke_pool_teams` t";
$sql .= " inner join";
$sql .= "   (SELECT team_id, MAX(season) as season";
$sql .= "   from `nuke_pool_teams`";
$sql .= "   group by team_id) maxseason";
$sql .= " on ( t.team_id = maxseason.team_id and t.season = maxseason.season )";
$sql .= " ) latestteam,";
$sql .= " (select c.Name Name, c.team_id team_id, c.end end";
$sql .= " from `nuke_pool_coaches` c";
$sql .= " inner join";
$sql .= "   ( SELECT team_id, MAX(end) as end";
$sql .= "   from `nuke_pool_coaches`";
$sql .= "   Group by team_id ) maxend";
$sql .= " on ( c.team_id = maxend.team_id and c.end = maxend.end )";
$sql .= " ) latestcoach";
$sql .= " Where latestteam.team_id = latestcoach.team_id";
$sql .= " and latestteam.league = 'NCAA'";
$sql .= " order by latestcoach.end, latestteam.team_name ASC";

$result = $db->sql_query($sql, $dbi);
while ($row = $db->sql_fetchrow($result)) {
	$team_name = $row['team'];
	$coach = $row['Name'];
	$end = $row['end'];
	if ( $end == '9999-12-01' ) { $coach .= " (interim)"; }
	if ( $end < '9999-12-01' ) { $coach = "<b>Vacant</b>"; }
	$content .= "<tr><td>$team_name</td><td>$coach</td></tr>";
}
$content .= "</table>\n";

$content .= "</td><td valign=\"top\">\n";

$content .= "<table cellpadding=1 cellspacing=4>";
$content .= "<tr><th colspan=2>NFL</th></tr>";

$sql = "select latestteam.team_name team, latestcoach.Name Name, latestcoach.end end from";
$sql .= " ( select t.team_id team_id, t.team_name team_name, t.league league, t.season season";
$sql .= " from `nuke_pool_teams` t";
$sql .= " inner join";
$sql .= "   (SELECT team_id, MAX(season) as season";
$sql .= "   from `nuke_pool_teams`";
$sql .= "   group by team_id) maxseason";
$sql .= " on ( t.team_id = maxseason.team_id and t.season = maxseason.season )";
$sql .= " ) latestteam,";
$sql .= " (select c.Name Name, c.team_id team_id, c.end end";
$sql .= " from `nuke_pool_coaches` c";
$sql .= " inner join";
$sql .= "   ( SELECT team_id, MAX(end) as end";
$sql .= "   from `nuke_pool_coaches`";
$sql .= "   Group by team_id ) maxend";
$sql .= " on ( c.team_id = maxend.team_id and c.end = maxend.end )";
$sql .= " ) latestcoach";
$sql .= " Where latestteam.team_id = latestcoach.team_id";
$sql .= " and latestteam.league = 'NFL'";
$sql .= " order by latestcoach.end, latestteam.team_name ASC";

$result = $db->sql_query($sql, $dbi);
while ($row = $db->sql_fetchrow($result)) {
	$team_name = $row['team'];
	$coach = $row['Name'];
	$end = $row['end'];
	if ( $end == '9999-12-01' ) { $coach .= " (interim)"; }
	if ( $end < '9999-12-01' ) { $coach = "<b>Vacant</b>"; }
	$content .= "<tr><td>$team_name</td><td>$coach</td></tr>";
}
$content .= "</table>\n</td>\n";

$content .= "</tr></table></center>\n";

?>
