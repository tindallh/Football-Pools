<?php

if (eregi("block-NCAA.php", $_SERVER['SCRIPT_NAME'])) {
    Header("Location: index.php");
    die();
}
global $user, $user_prefix, $cookie, $prefix, $dbi, $module_name, $db, $today_date, $now_time;

$debug=0;

if (is_user($user)) {
 	cookiedecode($user);
 	$uname = $cookie[1];
 	$result = $db->sql_query("SELECT user_id FROM ".$user_prefix."_users WHERE username='$uname'");
 	$row = $db->sql_fetchrow($result);
 	$user_id = intval($row[user_id]);
}
# If they weren't read in from the config file, do dates and time.
if (!$today_date) { $today_date = date("Y-m-d"); }
if (!$now_time) { 
	$now_time = date("Hi");
	list($ty,$tmo,$td) = explode("-",$today_date);
	# Compensating for the Server being on Mountain Time.
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
if (!$this_year) { $this_year = date("Y"); }
if (!$julian_date) { $julian_date = date("z"); }
if ($julian_date < 90) {
	$this_season = $this_year-1;
} else {
	$this_season = $this_year;
}
if (!isset($seasonID)) {
	$seasonID = $this_season;
}

$sql = "SELECT * FROM ".$user_prefix."_users WHERE username='$uname'";
$result = $db->sql_query($sql);
$num = $db->sql_numrows($result);
$userinfo = $db->sql_fetchrow($result);
if(!$bypass) cookiedecode($user);

# figure out which pools they're members of.
require_once("participation.inc.php");
$membership = explode(",",$userinfo[member_of]);
$poollist = explode(",",$allpools);
$content .= "<center>\n<table border=1 cellpadding=2 cellspacing=2><tr>\n";
$content .= "<th>Pool name (% required)</th><th>% Picked This week</th><th>% Picked Prior</th></tr>\n";
foreach ($poollist as $pool) {
	#$content .= "<!-- \$pool=\"$pool\" -->\n";
	# check membership;
	# read in the variables for the individual Pool name, for stuff like the percentages, after zeroing variables !!
	$leagueID=$poolname='';
	$ppercent=$top25=0;
	if ($debug > 1) { $content .= "\n<!-- HBT Reading in \"modules/$pool/config.inc.php\" -->\n"; }
	require("modules/$pool/config.inc.php");

	foreach ($membership as $sub) {
		if ($debug > 1) { $content .= "<!-- \$sub=\"$sub\", \$poolname=\"$poolname\", \$leagueID=\"$leagueID\",\$today_date=\"$today_date\", \$now_time=\"$now_time\", -->\n"; } 
		if (eregi( $sub, $poolname )) {
			# $content .= "<!-- MATCH!  \$sub=\"$sub\", \$poolname=\"$poolname\" -->\n";
			$content .= "<tr><td><a href=\"/modules.php?name=$pool&op=MakePicks&seasonID=$seasonID\"><font class=\"big\">$pool ($ppercent)</font></a></td>";
			# Do some nifty SQL queries to pull the weekly pick percentage.
			#####
			# gotta figure out the weekid first !!
			$sql = "SELECT week, date, time FROM ".$prefix."_pool_games";
			$sql .= " WHERE ((date > '$today_date') OR (date = '$today_date' AND time > '$now_time'))";
			$sql .= " AND league = '$leagueID' AND season = ".$seasonID."";
			if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
			$sql .= " ORDER BY date, time limit 1";
			if ($debug > 1) { $content .= "\n<!-- HBT1 \$sql=\"$sql\" -->\n"; }
			$result = $db->sql_query($sql);
			$object = sql_fetch_object($result, $dbi);
			if(is_object($object)) {
				$weekID = $object->week;
				$weekID = intval($weekID);
				$date = $object->date;
				$time = $object->time;
				$lastweek = $weekID-1;
				list($y,$m,$d) = explode("-",$date);
				$date = date("l j F, Y", mktime(0,0,0,$m,$d,$y));
				### $content .= "The next game is $date at $time<br>\n";
			} else {
				$sql = "SELECT week,date FROM ".$prefix."_pool_games WHERE season = '$seasonID' AND league = '$leagueID'";
				if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
				$sql .= " ORDER by week DESC limit 1";
				$result = $db->sql_query($sql);
				$object = sql_fetch_object($result, $dbi);
				if(is_object($object)) {
					$weekID = $object->week;
					$weekID = intval($weekID);
					$lastweek = $weekID;
				}
			}
			if ($debug > 0) { $content .= "<!-- HBT4 \$seasonID=\"$seasonID\", \$leagueID=\"$leagueID\", \$weekID=\"$weekID\" -->\n"; }
			# get All the games we care about, after zeroing the variables!
			$pastgames=$thisweekgames=$futuregames=0;
			$sql = "SELECT week, game, home_spread FROM ".$prefix."_pool_games WHERE league = '$leagueID' AND season = '$seasonID'";
			if ($leagueID == 'NCAA' && $top25 == '1') { $sql .= " AND (home_rank IS NOT NULL or visitor_rank IS NOT NULL)"; }
			$sql .= " ORDER BY week, game";
			$result = $db->sql_query($sql, $dbi);
			if ($debug > 0) { $content .= "<!-- HBT-PickPct \$sql='$sql' -->\n"; }	
			while ($row = $db->sql_fetchrow($result)) {
				if ($row['week'] < $weekID) {
					if ($debug > 2) { $content .= "<!-- HBT-PickPct2 pastgames row !! -->\n"; }
					if ($usespreads == 1) {
						if ($row['home_spread'] != '') { $pastgames++; }
					} else {
						$pastgames++;
					}
				} elseif ($row['week'] == $weekID) {
					if ($debug > 2) { $content .= "<!-- HBT-PickPct2 thisweekgames row !! -->\n"; }
					if ($usespreads == 1) {
						if ($row['home_spread'] != '') { $thisweekgames++; }
					} else {
						$thisweekgames++;
					}
				} else {
					if ($debug > 1) { $content .= "<!-- HBT-PickPct2 futuregames row !! -->\n"; }
					$futuregames++;
				}
			}
			# now get the users's picked games, after zeroing the variables!
			$pastpicked=$thisweekpicked=0;
			$sql = "SELECT week, game FROM ".$prefix."_pool_picks_".$poolname." WHERE league = '$leagueID'";
			$sql .= " AND season = '$seasonID' AND user_id = '$user_id' AND usespreads = '$usespreads'";
			#$sql .= " AND (pick = 'home' or pick = 'visitor') ORDER BY week, game";
			$sql .= " AND (pick IS NOT NULL) ORDER BY week, game";
			if ($debug > 0) { $content .= "<!-- HBT6 user's picked games \$sql='$sql' -->\n"; }
			$result = $db->sql_query($sql, $dbi);
			while ($row = $db->sql_fetchrow($result)) {
				if ($debug > 3) { $content .= "<!-- HBT6 row of picks  -->\n"; }
				if ($row['week'] < $weekID) {
					$pastpicked++;
					if ($debug > 1) { $week= $row['week']; $game= $row['game']; $content .= "<!-- HBT6 \$week=\"$week\", \$game=\"$game\", \$pastpicked='$pastpicked' -->\n"; }
				} elseif ($row['week'] == $weekID) {
					$thisweekpicked++;
					if ($debug > 1) { $week= $row['week']; $game= $row['game']; $content .= "<!-- HBT6 \$week=\"$week\", \$game=\"$game\", \$thisweekpicked='$thisweekpicked' -->\n"; }
				}
			}
			# figure past, present, and future (needed) percentages.
			if ($debug > 0) { $content .= "<!-- HBT \$weekID=\"$weekID\",\$pastgames=\"$pastgames\",\$pastpicked=\"$pastpicked\",\$thisweekgames=\"$thisweekgames\",\$thisweekpicked=\"$thisweekpicked\",\$futuregames=\"$futuregames\" -->\n"; }
			$past_pct=(($pastpicked/$pastgames)*100);
			if ($debug > 1) { $content .= "<!-- \$past_pct=((\$pastpicked/\$pastgames)*100) -->\n"; }
			if ($debug > 1) { $content .= "<!-- $past_pct=(($pastpicked/$pastgames)*100) -->\n"; }
			$thisweek_pct=number_format(($thisweekpicked/$thisweekgames)*100,1);
			if ($debug > 1) { $content .= "<!-- \$thisweek_pct=number_format((\$thisweekpicked/\$thisweekgames)*100,1) -->\n"; }
			if ($debug > 1) { $content .= "<!-- $thisweek_pct=number_format(($thisweekpicked/$thisweekgames)*100,1) -->\n"; }
			$allpicked=$thisweekpicked+$pastpicked;
			if ($debug > 1) { $content .= "<!-- \$allpicked=\$thisweekpicked+\$pastpicked -->\n"; }
			if ($debug > 1) { $content .= "<!-- $allpicked=$thisweekpicked+$pastpicked -->\n"; }
			$allgames=$pastgames+$thisweekgames+$futuregames;
			if ($debug > 1) { $content .= "<!-- \$allgames=\$pastgames+\$thisweekgames+\$futuregames -->\n"; }
			if ($debug > 1) { $content .= "<!-- $allgames=$pastgames+$thisweekgames+$futuregames -->\n"; }
			if ($weekID > 1) { $past_pct=number_format(($pastpicked/$pastgames)*100,1); }
			if ($debug > 1) { $content .= "<!-- \$past_pct=number_format((\$pastpicked/\$pastgames)*100,1) -->\n"; }
			if ($debug > 1) { $content .= "<!-- $past_pct=number_format(($pastpicked/$pastgames)*100,1) -->\n"; }
			$futurepicks=number_format((($ppercent/100)*$allgames)-$allpicked,0);
			if ($debug > 1) { $content .= "<!-- \$futurepicks=number_format(((\$ppercent/100)*\$allgames)-\$allpicked,0) -->\n"; }
			if ($debug > 1) { $content .= "<!-- $futurepicks=number_format((($ppercent/100)*$allgames)-$allpicked,0) -->\n"; }
			$future_pct=number_format(($futurepicks/$futuregames)*100,1);
			if ($debug > 1) { $content .= "<!-- \$future_pct=number_format((\$futurepicks/\$futuregames)*100,1) -->\n"; }
			if ($debug > 1) { $content .= "<!-- $future_pct=number_format(($futurepicks/$futuregames)*100,1) -->\n"; }
			if ($ppercent - $thisweek_pct > 0) {
				$hl = "red";
			} else {
				$hl = "";
			}
			# This table is just for the Percentages.
			###$content .= "<b>You've picked $hl_on$thisweek_pct%$hl_off of the games this week";
			$content .= "<td valign=\"center\" align=\"middle\"><font class=\"big$hl\">$thisweek_pct %</font></td>";
			if ($weekID > 1) { 
				$content .= "<td valign=\"center\" align=\"middle\"><font class=\"big\">$past_pct%"; 
			} else { 
				$content .= "<td valign=\"center\" align=\"middle\"><font class=\"big\">";
			}
			# $content .= ".</b><br>\n";
			if ($leagueID == 'NCAA' && $top25 == '1') {
				$sql = "SELECT week FROM ".$prefix."_pool_games WHERE league = '$leagueID' AND season = '$seasonID' ORDER BY week desc limit 1";
				if ($debug > 0) { $content .= "<!-- HBT5 \$sql=\"$sql\" -->\n"; } 
				$thelastwk = $db->sql_query($sql);
				$row = $db->sql_fetchrow($thelastwk);
				$wk = intval($row['week']);
				if ($debug > 0) { $content .= "<!-- HBT5 \$wk=\"$wk\" -->\n"; }
				# we're going to re-work this during the off-season;
				$low_gr = 20 * ($wk - $weekID);
				$high_gr = 25 * ($wk - $weekID);
				$low_allgames = $pastgames+$thisweekgames+$low_gr;
				if ($debug > 1) { 	$content .= "<!-- \$low_allgames = \$pastgames+\$thisweekgames+\$low_gr; -->\n"; }
				if ($debug > 1) { 	$content .= "<!-- $low_allgames = $pastgames+$thisweekgames+$low_gr; -->\n"; }
				$high_allgames = $pastgames+$thisweekgames+$high_gr;
				if ($debug > 1) { 	$content .= "<!-- \$high_allgames = \$pastgames+\$thisweekgames+\$high_gr; -->\n"; }
				if ($debug > 1) { 	$content .= "<!-- $high_allgames = $pastgames+$thisweekgames+$high_gr; -->\n"; }
				$low_pgr = number_format((($ppercent/100)*$low_allgames)-$allpicked,0);
				if ($debug > 1) { 	$content .= "<!-- \$low_pgr = number_format((($ppercent/100)*\$low_allgames)-\$allpicked,0); -->\n"; }
				if ($debug > 1) { 	$content .= "<!-- $low_pgr = number_format((($ppercent/100)*$low_allgames)-$allpicked,0); -->\n"; }
				$high_pgr = number_format((($ppercent/100)*$high_allgames)-$allpicked,0);
				if ($debug > 1) { 	$content .= "<!-- \$high_pgr = number_format((($ppercent/100)*\$high_allgames)-\$allpicked,0); -->\n"; }
				if ($debug > 1) { 	$content .= "<!-- $high_pgr = number_format((($ppercent/100)*$high_allgames)-$allpicked,0); -->\n"; }
				$low_f_pct = number_format(($low_pgr/$low_gr)*100,1);
				$high_f_pct = number_format(($high_pgr/$high_gr)*100,1);
				# $content .= "You can count on between $low_gr and $high_gr games to pick for the rest of the season;<br>";
				if ((($low_f_pct >= 100)&&($high_f_pct <= 100)) && (($past_pct < $ppercent) && ($thisweek_pct < $ppercent))) {
					if ($debug > 0) { $content .= "<!-- ($low_pgr, $high_pgr)) -->\n"; }
					##$content .= " so you'll have to pick at least $high_pgr ($high_f_pct%) more games;";
					##$content .= " You might not be able to pick enough games ";
					$content .= " *";
					if (!( $caveat == 2 || $caveat ==3 || $caveat == 6 || $caveat == 7 || $caveat == 10 || $caveat == 11 || $caveat == 14 || $caveat == 15 )) { $caveat +=2; }
				} elseif (($low_pgr <= 0) && (($past_pct < $ppercent) && ($thisweek_pct < $ppercent))) {
					#$content .= " so you'll have to pick between $low_pgr ($low_f_pct%) and $high_pgr ($high_f_pct%) more games";
					$content .= " &nbsp;";
				} else {
					#$content .= " so you'll have to pick between $low_pgr ($low_f_pct%) and $high_pgr ($high_f_pct%) more games";
					$content .= " &nbsp;";
				}
				#$content .= " to be in the end-of-season stats.";
			} else {
				if ($future_pct < 0) {
					##$content .= "You don't have to pick any more games";
					$content .= " +";
					if (!( $caveat == 1 || $caveat ==3 || $caveat == 5 || $caveat == 7 || $caveat == 9 || $caveat == 11 || $caveat == 13 || $caveat == 15 )) { $caveat += 1; }
				} elseif ($future_pct > 100) {
					##$content .= "Sorry, you won't be able to pick enough games for the rest of the year";
					$content .= " -";
					if ($caveat < 8) { $caveat += 8; }
				} elseif ($future_pct >= 95) {
					##$content .= "<font color=darkred>BE CAREFULL ! &nbsp;You have very little 'wiggle room'! You must pick $future_pct% of all remaining games";
					$content .= " !";
					if (!($caveat == 4 || $caveat >= 12 )) {  $caveat += 4; }
				} else {
					##$content .= "You have to make at least $futurepicks more picks this year, or $future_pct% of the remaining games ";
					$content .= " &nbsp;";
				}
				## $content .= " to be in the End-of-season stats.<br>\n";
				if ($usespreads == 1) {
					## $content .= "Keep in mind that some of the remaining games might NOT have spreads, so the percentage you have to pick might actually be higher or lower.<br>";
				}
				#if ($future_pct >= 95) { $content .= "</font>"; }
			}
			$content .= "</font></td></tr>\n";
		}
	}
}	
	$content .= "</tr>\n";
	if ($caveat >= 8) {
		$content .= "<tr><td colspan=3>(-) Sorry, you won't be able to pick enough games for the rest of the year for the marked pool(s).</td></tr>\n";
		$caveat -= 8;
	} 
	if ($caveat >= 4) {
		$content .= "<tr><td colspan=3>(!) <font color=darkred>BE CAREFULL ! &nbsp;You have very little 'wiggle room' in the marked pool(s)!</font></td></tr>\n";
		$caveat -= 4;
	} 
	if ($caveat >= 2) {
		$content .= "<tr><td colspan=3>(*) You might not be able to pick enough games to be in the end-of-season stats in the marked pool(s).</td></tr>\n";
		$caveat -= 2;
	} 
	if ($caveat == 1) {
		$content .= "<tr><td colspan=3>(+) You don't have to pick any more games to be in the end-of-season stats in the marked pool(s).</td></tr>\n";	
	} 
	$content .= "</table>\n</center>\n";
	
?>
