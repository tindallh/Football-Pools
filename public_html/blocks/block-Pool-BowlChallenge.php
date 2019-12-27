<?php
/*****************************************************************************/
/* Bowl Challenge Block                                                      */
/*   Version 1.2 - 29 Dec 2011 - Changed the code to reflect the new         */
/*                 separate Conference affiliation table                     */
/*           1.1 - 24 Dec 2009 - Added the 'sortval'routine to make the team */
/*                 Most losses come out on the bottom.                       */
/*           1.0 - 11 Dec 2009 - Initial release                             */
/*****************************************************************************/

if (eregi("block-NCAA.php", $_SERVER['SCRIPT_NAME'])) {
    Header("Location: index.php");
    die();
}
global $user_prefix, $cookie, $prefix, $dbi, $module_name, $db, $today_date, $now_time;
$today_date = date("Y-m-d");
$disp_date = date("l, j F, Y");
$now_time = date("Hm");
$week = 15;            # Bowl week !! change every year !
$debug = 0;

$this_year = date("Y");
$julian_date = date("z");
if ($julian_date < 90) {
	$season = $this_year-1;
} else {
	$season = $this_year;
}

$valid['ACC']=$valid['AAC']=$valid['Big Ten']=$valid['SEC']=$valid['Big 12']=1;
$valid['C-USA']=$valid['MAC']=$valid['MWC']=$valid['Pac-12']=$valid['Sun Belt']=$valid['WAC']=1;

# Let's get the conference affiliations first, remembering season is relevant:
$sql = "SELECT c.team_id, c.team_name, c.conference, c.division, c.ncaa_div";
$sql .= " FROM ( select team_id, MAX(season) as season";
$sql .= " FROM ".$prefix."_pool_conferences WHERE season <= '$season' AND league = 'NCAA' AND ncaa_div = 'I' AND team_id < 900";
$sql .= " GROUP BY team_id ) AS x INNER JOIN ".$prefix."_pool_conferences as c on c.team_id = x.team_id AND c.season = x.season";
if ($debug > 0) { $content .= "\n<!-- HBT \$sql='$sql' -->\n"; }
$results = sql_query($sql, $dbi);
while ($row = $db->sql_fetchrow($results)) {
	# if ($debug > 0) { $content .= "\n<!. -->\n"; }
	$team_id = $row['team_id'];
	$ncaa_div[$team_id] = strval($row['ncaa_div']);
	$conference[$team_id] = strval($row['conference']);
	if ($debug > 0) { $content .= "\n<!-- team_id = '".$row['team_id']."', ncaa_div = '".$row['ncaa_div']."', conference = '".$row['conference']."', division = '".$row['division']."' -->"; }
}

$sql = "SELECT team_id, team_name, ncaa_div from ".$prefix."_pool_teams";
$sql .= " WHERE league = 'NCAA' ORDER BY team_name";
$result = $db->sql_query($sql, $dbi);
while ($row = $db->sql_fetchrow($result)) {
	$team_id = $row['team_id'];
}

$sql = "SELECT home, visitor FROM ".$prefix."_pool_games";
$sql .= " WHERE season = '$season' and week = '$week' AND league = 'NCAA'";
$results = sql_query($sql, $dbi);
while ($row = $db->sql_fetchrow($results)) {
	$home = $row['home'];
	$visitor = $row['visitor'];
	$hconf=$conference[$home];
	$vconf=$conference[$visitor];
	if ($valid[$hconf]) { $gresults[$hconf]++; }
	if ($valid[$vconf]) { $gresults[$vconf]++; }
}

$sql = "SELECT home, home_score, visitor, visitor_score, week, game";
$sql .= " FROM ".$prefix."_pool_games";
if ($testing == '1') { $sql .= "_test";
} $sql .= " WHERE season = '$season' and week = '$week' AND league = 'NCAA'";
$sql .= " AND (home_score IS NOT NULL and visitor_score IS NOT NULL) ORDER BY home";
$results = sql_query($sql, $dbi);
while ($row = $db->sql_fetchrow($results)) {
	$home = $row['home'];
	$visitor = $row['visitor'];
	$hs = intval($row['home_score']);
	$vs = intval($row['visitor_score']);
	$hconf=$conference[$home];
	$vconf=$conference[$visitor];
	if ($hs > $vs) {
		if ($valid[$hconf]) { $wins[$hconf]++; }
		if ($valid[$vconf]) { $losses[$vconf]++; }
	} else {
		if ($valid[$hconf]) { $losses[$hconf]++; }
		if ($valid[$vconf]) { $wins[$vconf]++; }
	}
}
foreach ($valid as $conf => $static) {
	if ($gresults[$conf] >= 3) {
		(($wins[$conf]) ? $wins[$conf] = $wins[$conf] : $wins[$conf] = 0 );
		(($losses[$conf]) ? $losses[$conf] = $losses[$conf] : $losses[$conf] = 0 );
		if (($wins[$conf] + $losses[$conf]) > 0) {
			$pct[$conf] = $wins[$conf] / ($wins[$conf] + $losses[$conf]);
		} else {
		  $pct[$conf] = 0;
		}
		# $sorter[$conf]= $pct[$conf] . (9900 + $wins[$conf]) - $losses[$conf];
		$sorter[$conf]= strval(number_format($pct[$conf],3)) . strval((9900 + $wins[$conf]) - $losses[$conf]);
	}
}

arsort($sorter);
arsort($pct);
$i = 1;
$rank = 1;
$t_rank = 1;
foreach ($sorter as $conf => $sortval ) {
	$conf_pct=$pct[$conf];
	if ($i == 1) {
		$ranking[$conf]=$rank;
		$bigpct = ltrim(strval(number_format($conf_pct,3)),0);
	} else {
		if ($conf_pct == $prev_pct) {
			$ranking[$conf] = $rank;
			$t_rank++;
		} else {
			$rank += $t_rank;
			$t_rank = 1;
			$ranking[$conf] = $rank;
		}
	}
	$i++;
	$prev_pct=$conf_pct;
}

$content .= "\n\n<center><table cellpadding=\"2\" cellspacing=\"2\">\n";
foreach ($sorter as $conf => $sortval ) {
	$conf_pct=$pct[$conf];
	$d_rank = intval($ranking[$conf]);
	(($d_rank == 1) ? $emph = "<b>" : $emph = "" ); 
	(($d_rank == 1) ? $demph = "</b>" : $demph = "" );
	(($wins[$conf]) ? $wins[$conf] = $wins[$conf] : $wins[$conf] = "0" );  
	(($losses[$conf]) ? $losses[$conf] = $losses[$conf] : $losses[$conf] = "0" );  
	$content .= "<tr><td align=\"right\"><font class=\"challenge\">".$emph.$ranking[$conf].$demph."&nbsp;&nbsp;</font></td>";
	$content .= "<td nowrap><font class=\"challenge\">".$emph.$conf.$demph."&nbsp;&nbsp;</font></td>";
	$content .= "<td nowrap><font class=\"challenge\">".$emph.$wins[$conf]."-".$losses[$conf].$demph."&nbsp;&nbsp;</font></td>";
	$content .= "<td align=\"right\"><font class=\"challenge\">".$emph.ltrim(number_format($conf_pct,3),0).$demph."</font>";
	if ($debug > 0) {$content .= "<!-- \$sortval='$sortval' -->"; }
	$content .= "</td>";
	$content .= "</tr>\n";
}

$content .= "</table>\n<br>\n";
$content .= "</center><br>\n";
$content .= "Number of appearances by conference:<br>\n";

arsort($gresults);
$k=1;
$nonqual=0;
foreach ($gresults as $conf => $numbowls) {
	if (($gresults[$conf] >= 3) && ($valid[$conf])) {
		if ($k > 1) { $content .= ", "; }
		$content .= "$conf: $numbowls";
		$k++;
	} elseif (($gresults[$conf] < 3) && ($valid[$conf])) {
		if ($k > 1) { $content .= ", "; }
		$content .= "$conf: $numbowls*";
		$nonqual++;
		$k++;
	}
}

$content .= "<br>\n";
if ($nonqual > 0) {
	$content .= "<br>\n<i>* Does not qualify for the cup, minimum appearances is 3.</i><br>\n"; }

?>


