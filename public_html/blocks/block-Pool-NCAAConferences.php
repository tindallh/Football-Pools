<?php
/*****************************************************************************/
/* Conference Alignment Block                                                */
/*   Version 1.0 - 21 Sep 2013 - Initial release                             */
/*****************************************************************************/

if (eregi("block-Pool-NCAAConferences.php", $_SERVER['SCRIPT_NAME'])) {
    Header("Location: index.php");
    die();
}
global $user_prefix, $cookie, $prefix, $dbi, $module_name, $db, $today_date, $now_time;
$debug = 0;

$this_year = date("Y");
$julian_date = date("z");
if ($julian_date < 30) {
	$season = $this_year-1;
} else {
	$season = $this_year;
}

# Let's get the conference affiliations first, remembering season is relevant:
$sql = "SELECT c.team_id team_id, c.team_name team_name, c.conference conference, c.division division ";
$sql .= " from `nuke_pool_conferences` c";
$sql .= " INNER JOIN";
$sql .= "   ( SELECT team_id, MAX(season) as season";
$sql .= "   FROM `nuke_pool_conferences` ";
$sql .= "   GROUP BY team_id ) maxseason";
$sql .= " on ( c.team_id = maxseason.team_id and c.season = maxseason.season )";
$sql .= " WHERE c.season <= ".$season." AND c.league = 'NCAA' AND c.ncaa_div = 'I' AND c.team_id < 900";
$sql .= " ORDER BY c.conference, c.division";

if ($debug > 0) { $content .= "\n<!-- HBT \$sql='$sql' -->\n"; }
$results = sql_query($sql, $dbi);
while ($row = $db->sql_fetchrow($results)) {
	# if ($debug > 0) { $content .= "\n<!. -->\n"; }
	$team_id = $row['team_id'];
	$teamname[$team_id] = strval($row['team_name']);
	$conference[$team_id] = strval($row['conference']);
	$c = strval($row['conference']);
	$division[$team_id] = strval($row['division']);
	$d = strval($row['division']);
	$cdivision[$conference[$team_id]] = strval($row['division']);
	$cr[$c][$d][$team_id] = strval($row['team_name']);
	if ($debug > 0) { $content .= "\n<!-- team_id = '".$row['team_id']."', conference = '".$row['conference']."', division = '".$row['division']."', cr= '".$cr[$c][$d][$team_id]."' -->"; }
}

# asort ($cr);
$content .= "\n<table";
if ($debug > 0) { $content .= " border=1"; }
$content .= " cellpadding=2 cellspacing=2>\n";
foreach ($cr as $c => $conf) {
	$content .= "\n\t<tr>";
	if ($debug > 0) { $content .= "\n\t<!-- HBT \$c='$c', \$conf='$conf' -->"; }
	foreach ($cr[$c] as $d => $div) {
		$content .= "\n\t\t<td><center><b>$c<br>$d</b></center>";
		if ($debug > 0) { $content .= "\n\t\t\t<!-- HBT \$d='$d', \$div='$div' -->"; }
		asort ($cr[$c][$d]);
		foreach ($cr[$c][$d] as $t => $team) {
			$content .= "\n\t\t\t<a href=\"modules.php?name=TeamSchedule-NCAA&seasonID=$season&leagueID=NCAA&team=$t\" target=\"_new\">$team</a><br>";
			# $content .= "\n\t\t\t$team<br>";
		}
		$content .= "\n\t\t</td>";
	}
	$content .= "\n\t</tr>";
}
$content .= "\n</table>\n\n";		
?>
