<?php
global $user_prefix, $cookie, $prefix, $dbi, $module_name, $db;
#, $poolname, $testing, $top25, $seasonID, $league, $usespreads, $user_prefix, 
# $lastweek, $user, $user_id, $uname, $cookie, $prefix, $ppercent, $today_date, $now_time;

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
} else {
	#$select_time = $now_time-300;
	$select_time = $now_time;
}

if ($debug > 0) {
	$content .= "<!-- HBT \$today_date = '$today_date' -->\n";
	$content .= "<!-- HBT \$now_time = '$now_time' -->\n";
	$content .= "<!-- HBT \$this_year = '$this_year' -->\n";
	$content .= "<!-- HBT \$julian_date = '$julian_date' -->\n";
	$content .= "<!-- HBT \$seasonID = '$seasonID' -->\n";
	$content .= "<!-- HBT \$select_time = '$select_time' -->\n";
}

$sql = "SELECT user_id, username FROM ".$user_prefix."_users";
$sql .= " WHERE user_id > 1 ORDER BY user_id";
if ($debug > 0) { $content .= "<!-- HBT1 \$sql='$sql' -->\n"; }
$result = sql_query($sql, $dbi);
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
	if ($debug > 0) { $content .= "\n<!-- HBT2 ($poolname,$league,$ppercent,$usespreads) -->\n"; }
	$total_pool_games=0;
	$content .= "<td valign=\"top\"><center><font color=\"blue\" size=+2>".$league." ".$poolname." pool";
	if ($usespreads == 0) {
		$content .= "<br>(no spreads)";
	} else {
		$content .= "<br><br>";
	}
	$content .= "</font></center>\n";
	$total_pool_games=0;
	$numweeks = 0;
	$sql = "SELECT week FROM ".$user_prefix."_pool_games";
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$league' AND season = '$seasonID'";
	$sql .= " AND ((`date` < '$today_date') OR (`date` <= '$today_date' AND `time` < '$select_time')) ORDER BY week DESC limit 1";
	if ($debug > 0) {	$content .= "<!-- HBT4 \$sql='$sql' -->\n"; }
	$counting = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($counting)) {
		$weekID = intval($row['week']);
	}
	$sql = "SELECT user_id, game, pick, week";
	$sql .= " FROM ".$prefix."_pool_picks_".$poolname;
	if ($testing == '1') { $sql .= "_test"; }
	$sql .= " WHERE league = '$league' AND usespreads = '$usespreads'";
	$sql .= " AND season = '$seasonID' AND week <= $weekID";
	$sql .= " ORDER BY week";
	if ($debug > 0) { $content .= "<!-- HBT5 \$sql='$sql' -->\n"; }
	$result_b = sql_query($sql, $dbi);
	while ($row = $db->sql_fetchrow($result_b)) {
		$db_week = intval($row['week']);
		$uid = intval($row['user_id']);
		$db_game = intval($row['game']);
		$db_pick[$db_week][$uid][$db_game] = $row['pick'];
		$weekID = intval($row['week']);
		$numweeks++;
	}
	if ($numweeks > 0) {
		$sql = "SELECT home_score, visitor_score, week";
		$sql .= " FROM ".$prefix."_pool_games";
		if ($testing == '1') { $sql .= "_test"; }
		$sql .= " WHERE league = '$league' AND season = '$seasonID' AND week <= '$weekID'";
		# Serious cludge here!
		if (eregi("top25",$poolname)) { $top25 = 1; }
		if ($league == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
		if ($usespreads == '1') { $sql .= " AND (home_spread IS NOT NULL)"; }
		if ($debug > 0) { $content .= "<!-- HBT6 \$sql='$sql' -->\n"; }
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
		$sql = "SELECT count(*) count";
		$sql .= " FROM ".$prefix."_pool_games";
		if ($testing == '1') { $sql .= "_test"; }
		$sql .= " WHERE league = '$league' AND season = '$seasonID'";
		$sql .= " AND ((date < '$today_date') or (date = '$today_date' and time < '$select_time'))";
		$sql .= " AND home_score IS NOT NULL AND visitor_score IS NOT NULL";
		if ($league == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
		if ($debug > 0) {$content .= "<!-- HBT7 \$sql='$sql' -->\n"; }
		$fini = sql_query($sql, $dbi);
		while ($row = $db->sql_fetchrow($fini)) {
			$fini_games=$row['count'];
		}
		$total_pool_games = 0;
		$sql = "SELECT home_score, visitor_score, home_spread, week, game";
		$sql .= " FROM ".$prefix."_pool_games";
		if ($testing == '1') { $sql .= "_test"; }
		$sql .= " WHERE league = '$league' AND season = '$seasonID' AND week <= '$lrweek'";
		if ($league == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
		if ($usespreads == '1') { $sql .= " AND (home_spread IS NOT NULL)"; }
		$sql .= " ORDER by week, date, game";
		if ($debug > 0) { $content .= "<!-- HBT8 \$sql='$sql' -->\n"; }
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
			if ($debug > 1) { $content .= "<!-- HBT9 \$g_week='$g_week,\$g_game='$g_game',\$g_home_score='$g_home_score',\$g_visitor_score='$g_visitor_score',\$g_home_spread='$g_home_spread' -->\n"; }
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
			} elseif ($g_result == 0)  {
				$g_winner[$g_week][$g_game] = "push";
			}
		}
		unset ($t_right);
		unset ($t_wrong);
		unset ($push);
		unset ($games_picked);
#		unset ($ranking);
		if (count($g_winner) > 0) {
			foreach ($db_uid as $usernum) {
				$games_picked[$usernum] = 0;
				$t_right[$usernum] = 0;
				$t_wrong[$usernum] = 0;
				arsort ($r_week);
				foreach ($r_week as $rweek) {
					$push[$rweek][$usernum] = 0;
					$right[$rweek][$usernum] = 0;
					$wrong[$rweek][$usernum] = 0;
					foreach ($w_game as $g_num) {
						# next line not very clean....  Fix this.
						if (( $db_pick[$rweek][$usernum][$g_num] == "home" ) 
							|| ( $db_pick[$rweek][$usernum][$g_num] == "visitor" )) {
							if ($g_winner[$rweek][$g_num] == $db_pick[$rweek][$usernum][$g_num]) {
								$right[$rweek][$usernum]++;
								$t_right[$usernum]++;
								$games_picked[$usernum]++;
							# next line not very clean....  Fix this.
#							} elseif (($g_winner[$rweek][$g_num] == "push") && ($usespreads == 1)) {
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
				}
				if (($t_right[$usernum] + $t_wrong[$usernum]) > 0) {
					$pct[$usernum] = $t_right[$usernum] / ($t_right[$usernum] + $t_wrong[$usernum]);
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
				$twpc = ($gp/$total_pool_games)*100;
				if ($twpc >= $ppercent) {
					if ($i == 1) {
						$ranking[$picker]=$rank;
						$bigpct = ltrim(number_format($pick_pct,3),0);
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
			$x=0;
			foreach ($pct as $picker => $pick_pct ) {
				$x++;
				if ($x <= 10) {
					$real_user = $db_user[$picker];
					$real_uid = $db_uid[$real_user];
					$d_rank = intval($ranking[$picker]);
					if ($debug > 0) {	$content .= "<!-- \$d_rank = '$d_rank' -->"; }
					(($d_rank == 1) ? $emph = "<b>" : $emph = "" ); 
					(($d_rank == 1) ? $demph = "</b>" : $demph = "" ); 
					$content .= "<tr><td align=\"right\"><font class=\"winners\">".$emph.$ranking[$picker].$demph."</font></td>";
					$content .= "<td><font class=\"winners\">".$emph.$db_user[$picker].$demph."</font></td>";
					$content .= "<td align=\"right\"><font class=\"winners\">".$emph.ltrim(number_format($pick_pct,3),0).$demph."</font></td>";
					$content .= "</tr>\n";
				}
			}
			$content .= "</table>\n";
		}
	}
	$content .= "</td>\n";
	if ($blockcounter >= 4) {
		$content .= "</tr><tr>\n";
		$blockcounter = 0;
	}
}
$content .= "</tr></table>\n</center>\n\n";

?>
