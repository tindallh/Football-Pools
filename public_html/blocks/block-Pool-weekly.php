<?php
global $user_prefix, $cookie, $prefix, $dbi, $module_name, $db;
#, $poolname, $testing, $top25, $seasonID, $league, $usespreads, $user_prefix, $lastweek, $user, $user_id, $uname, $cookie, $prefix, $dbi, $module_name, $db, $ppercent, $today_date, $now_time;

if (eregi("block-Forums.php", $_SERVER['SCRIPT_NAME'])) {
    Header("Location: index.php");
    die();
}

require_once("mainfile.php");

require("pool-config.inc.php");

# If they weren't read in from the config file, do dates and time.
if (!$today_date) { $today_date = date("Y-m-d"); }
if (!$now_time) { $now_time = date("Hi"); }
if (!$this_year) { $this_year = date("Y"); }
if (!$julian_date) { $julian_date = date("z"); }
if ($julian_date < 90) {
	$seasonID = $this_year-1;
} else {
	$seasonID = $this_year;
}
if ($now_time < 300) {
	$select_time = $now_time;
} elseif ($now_time < 1300) {
	$select_time = '0'.$now_time-300;
} else {
	$select_time = $now_time-300;
}
if ($debug > 0) {
	$content .= "<!-- HBT \$today_date = '$today_date' -->\n";
	$content .= "<!-- HBT \$now_time = '$now_time' -->\n";
	$content .= "<!-- HBT \$this_year = '$this_year' -->\n";
	$content .= "<!-- HBT \$julian_date = '$julian_date' -->\n";
	$content .= "<!-- HBT \$seasonID = '$seasonID' -->\n";
	$content .= "<!-- HBT \$select_time = '$select_time' -->\n";
}

$sql="SELECT user_id, username FROM ".$user_prefix."_users";
$sql .= " WHERE user_id > 1 ORDER BY user_id";
$result = sql_query($sql, $dbi);
if ($debug > 0) { $content .= "<!-- HBT \$sql='$sql' --><br>\n"; }
while ($row = $db->sql_fetchrow($result)) {
	$db_id = intval($row['user_id']);
	$db_user[$db_id] = $row['username'];
	$db_uid[$db_user[$db_id]] = $db_id;
}

$poolinfo = explode(";",$pools);

$content .= "\n\n";
$content .= "<center>\n<table border=\"1\" cellpadding=\"2\" cellspacing=\"2\">\n";
$content .= "<tr>\n";
$blockcounter=0;
foreach ($poolinfo as $pi) {
	$blockcounter++;
	unset($right);
	unset($wrong);
	unset($push);
	unset($pct);
	unset($w_game);
	unset($g_winner);
	unset($db_pick);
	list($poolname,$league,$ppercent,$usespreads)=explode(",",$pi);
	$sql = "SELECT week FROM ".$user_prefix."_pool_games";
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$league' AND season = '$seasonID'";
	$sql .= " AND ((`date` < '$today_date') OR (`date` <= '$today_date' AND `time` < $select_time)) ORDER BY week DESC limit 1";
	if ($debug > 0) { $content .= "<!-- HBT \$sql='$sql' -->\n";  }
	$counting = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($counting)) {
		$weekID = intval($row['week']);
	}
	if ($debug > 0) {  $content .= "<!-- HBT '$poolname,$league,$ppercent,$usespreads' -->\n"; }
	$total_pool_games=0;
	$content .= "<td valign=\"top\"><center><font color=\"blue\" size=+2>";
	# here's where the link to the pool goes, if we ever get the names constant.
	# $content .= "<a href=/modules.php?name=";
	$content .= $league." ".$poolname;
	$content .= " pool week $weekID";
	if ($usespreads == 0) {
		$content .= "<br>(no spreads)";
	} else {
		$content .= "<br><br>";
	}
	$content .= "</font></center>\n";
	$total_pool_games=0;
	$numweeks = 0;
	$numpicks = 0;
	unset($db_pickers);
	$sql = "SELECT user_id, game, pick";
	$sql .= " FROM ".$user_prefix."_pool_picks_".$poolname;
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$league' AND usespreads = '$usespreads'";
	$sql .= " AND season = '$seasonID' AND week='$weekID'";
	$sql .= " ORDER BY user_id";
	if ($debug > 0) {  $content .= "<!-- HBT \$sql='$sql' -->\n"; }
	$result_b = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result_b)) {
		$uid = intval($row['user_id']);
		$db_game = intval($row['game']);
		$db_pick[$uid][$db_game] = $row['pick'];
		$db_pickers[$uid] = 1;
		$numpicks++;
	}
	if ($numpicks > 0) {
		if ($debug > 0) {	$content .= "<!-- numpicks = $numpicks -->\n"; }
		$ftw = 0;
		$sql = "SELECT home_score, visitor_score, home_spread, game";
		$sql .= " FROM ".$user_prefix."_pool_games";
		if ($testing == '1') { $sql .= "_test"; }
		$sql .= " WHERE league = '$league' AND season = '$seasonID' AND week = '$weekID'";
		$sql .= " AND (home_score IS NOT NULL AND visitor_score IS NOT NULL)";
		# Serious cludge here!
		if (eregi("top25",$poolname)) { $top25 = 1; }
		if ($league == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
		if ($usespreads == '1') { $sql .= " AND (home_spread IS NOT NULL)"; }
		$sql .= " ORDER by date,game";
		if ($debug > 0) {  $content .= "<!-- HBT \$sql='$sql' -->\n"; }
		$gameresults = sql_query($sql, $dbi);
		while ($row = $db->sql_fetchrow($gameresults)) {
			$ftw++;
			$boxContent .= "<!-- \$ftw=$ftw -->\n";
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
		foreach ($db_uid as $username => $usernum) {
			if ($debug > 1) { $content .= "<!-- $username => $usernum -->\n"; }
			$games_picked[$usernum] = 0;
			$push[$usernum] = 0;
			$right[$usernum] = 0;
			$wrong[$usernum] = 0;
			foreach ($w_game as $g_num) {
				if (( $db_pick[$usernum][$g_num] == "home" ) || ( $db_pick[$usernum][$g_num] == "visitor" )) {
					$uname = $db_user[$usernum];             #testing
					$victor = $g_winner[$g_num];             #testing
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
			if (($right[$usernum] + $wrong[$usernum]) > 0) {
				$pct[$usernum] = $right[$usernum] / ($right[$usernum] + $wrong[$usernum]);
			} else {
			  $pct[$usernum] = 0;
			}		
		}
		arsort($pct);
		$i = 1;
		$rank = 1;
		$t_rank = 1;
		foreach ($pct as $picker => $pick_pct ) {
			$gp = $games_picked[$picker];
			$twpc = ($gp/$ftw)*100;
			if ($debug > 1) { $content .= "<!-- $picker => $pick_pct -- \$games_picked[$picker] = '$games_picked[$picker]' -- \$twpc = '$twpc' -->\n"; }
			if ($twpc >= $ppercent) {
				if ($i == 1) {
					$ranking[$picker]=$rank;
					$bigpct = ltrim(strval(number_format($pick_pct,3)),0);
				} else {
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
		$content .= "<table cellpadding=\"2\" cellspacing=\"2\">\n";
		if ($debug > 0) { $content .= "<!-- winner table -->\n"; }
		$k = 1;
		foreach ($pct as $picker => $pick_pct ) {
			if ($debug > 1) { $content .= "<!-- $picker => $pick_pct (\$k=$k) -->\n"; }
			if ($k <= 10) {
				$real_user = $db_user[$picker];
				$real_uid = $db_uid[$real_user];
				$d_rank = intval($ranking[$picker]);
				if ($debug > 1) { $content .= "<!-- \$d_rank = '$d_rank' -->"; }
				(($d_rank == 1) ? $emph = "<b>" : $emph = "" ); 
				(($d_rank == 1) ? $demph = "</b>" : $demph = "" ); 
				$content .= "<tr><td align=\"right\"><font class=\"winners\">".$emph.$ranking[$picker].$demph."</font></td>";
				$content .= "<td><font class=\"winners\">".$emph.$db_user[$picker].$demph."</font></td>";
				$content .= "<td align=\"right\"><font class=\"winners\">".$emph.ltrim(number_format($pick_pct,3),0).$demph."</font></td>";
				$content .= "</tr>\n";
			}
			$k++;
		}
		$content .= "</table>\n";
	}
	$content .= "</td>\n";
	if ($blockcounter >= 4) {
		$content .= "</tr><tr>\n";
		$blockcounter = 0;
	}
}
$content .= "</tr></table>\n</center>\n\n";

?>


