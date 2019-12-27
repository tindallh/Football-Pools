<?php
/*****************************************************************************/
/* Football Pool TV Schedule BLock for PHP-Nuke                              */
/*   written by: Henry B. Tindall, Jr.                                       */
/*   version 9.01                                                            */
/*   first written: 15 Aug 2004                                              */
/*   last modified: 07 Sep 2016                                              */
/*****************************************************************************/

global $user_prefix, $cookie, $prefix, $dbi, $module_name, $db, $today_date, $now_time;

if (eregi("block-NCAA.php", $_SERVER['SCRIPT_NAME'])) {
    Header("Location: index.php");
    die();
}

require("pool-config.inc.php");

# If they weren't read in from the config file, do dates and time.
if (!$today_date) { $today_date = date("Y-m-d"); }
if (!$now_time) { $now_time = date("Hi"); }
if (!$this_year) { $this_year = date("Y"); }
$julian_date = date("z");
if ($julian_date < 90) {
	$season = $this_year-1;
} else {
	$season = $this_year;
}

$disp_date = date("l, j F, Y");
$gstart = $now_time-300;   #  This will give us three hours ago; that way we get games in progress.
if ($gstart < 1000) { $gstart = "0".$gstart; }
$howmany = 60;   # how many games (for each league) in the block.
$which = 3;      # 1 is NCAA only, 2 is NFL only, 3 is both.

$content .= "<!-- HBT - \$which = $which; \$howmany = $howmany --->\n";
#$sql = "SELECT team_id, team_name from ".$prefix."_pool_teams ORDER BY team_name";
$sql = "SELECT team_id, team_name FROM nuke_pool_teams NATURAL JOIN";
$sql .= " ( SELECT team_id, MAX(season) as season FROM ".$prefix."_pool_teams";
$sql .= " WHERE season <= '$season'"; 
# AND league = '$leagueID'";
$sql .= " GROUP BY team_id ) latestteam";
if ($debug > 0) { $content .= "\n<!-- HBT - \$sql='$sql' -->\n"; }

$result = $db->sql_query($sql, $dbi);
while ($row = $db->sql_fetchrow($result)) {
	$team_id = $row['team_id'];
	$team_name[$team_id] = $row['team_name'];
	if ($debug > 0) { $content .= "<!-- HBT - \$team_id = \"$team_id\"; \$team_name[$team_id] = \"$team_name[$team_id]\" --->\n"; }
	
}

$sql = "SELECT id, name FROM ".$prefix."_pool_tvnetworks";
$result = $db->sql_query($sql, $dbi);
while ($row = $db->sql_fetchrow($result)) {
	$network[$row['id']] = $row['name'];
}

$sql = "SELECT home, home_score, visitor, visitor_score, week, game";
$sql .= " FROM ".$prefix."_pool_games";
if ($testing == '1') { $sql .= "_test"; }
$sql .= " WHERE season = '$season'";
$sql .= " AND (home_score IS NOT NULL and visitor_score IS NOT NULL) ORDER BY home, week";
if ($debug > 0) { $content .= "<!-- HBT \$sql='$sql' -->\n"; }
$results = sql_query($sql, $dbi);
while ($row = $db->sql_fetchrow($results)) {
	$home = $row['home'];
	$visitor = $row['visitor'];
	$hs = intval($row['home_score']);
	$vs = intval($row['visitor_score']);
	$w = $row['week'];
	$g = $row['game'];
  # $content .= "<!-- \$g='$g', \$w='$w', \$ht='$ht', \$vt='$vt', \$hs='$hs', \$vs='$vs' -->\n";  
	if ($hs > $vs) {
		$home_wins[$home]++;
		$away_losses[$visitor]++;
	} else {
		$home_losses[$home]++;
		$away_wins[$visitor]++;
	}
}

$content .= "\n<center>\n<table cellpadding=1 cellspacing=4><tr>";

if ($which == 1 || $which == 3) {
	$content .= "<td background=\"http://football-pools.org/images/white.png\" valign=\"top\">\n";
	$content .= "<table><tr><th colspan=\"3\" align=\"middle\" width=\"320\"><h2>NCAA</h2><hr></th></tr>\n";
	$sql =  "SELECT week, date, time, home, visitor, home_rank, visitor_rank, tvnetwork, tvnetwork2, Title, neutral FROM ".$prefix."_pool_games";
	$sql .= " WHERE ((date > '".$today_date."') OR (date = '".$today_date."' AND time > '".$gstart."'))";
	$sql .= " AND season = '".$season."' AND league = 'NCAA' AND (tvnetwork IS NOT NULL ";
	# no need for WatchESPN, everybody knows that...
	$sql .= " AND (tvnetwork > 0 AND tvnetwork != 19 And tvnetwork != 39 ))";
	$sql .= " ORDER BY date, time, home LIMIT ".$howmany;
	if ($debug > 0) { $content .= "\n<!-- HBT2 - \$sql='$sql' -->\n"; }
	$result = sql_query($sql, $dbi);
	$c=0;
	while ($row = $db->sql_fetchrow($result)) {
		$game = $row['game'];
		$date = $row['date'];
		$day = date("l", strtotime($date));
		$time = $row['time'];
		$home = $row['home'];
		$home_rank = $row['home_rank'];
		$visitor = $row['visitor'];
		$visitor_rank = $row['visitor_rank'];
		$tvnetwork = $row['tvnetwork'];
		$tvnetwork2 = $row['tvnetwork2'];
		$title = $row['Title'];
		$neutral = $row['neutral'];
		list($y,$mo,$d) = explode("-",$date);
		$h = substr($time,0,2);
		$mi = substr($time,2);
		$DST = localtime(mktime($h,$mi,0,$mo,$d,$y));
		$game_day = $DST[7]+1;
		((($game_day > $DST_start) && ($game_day < $DST_end)) ? $timestring = "CDT" : $timestring = "CST" );
		(($visitor_rank > 0) ? $d_v_rank = "#".$visitor_rank." " : $d_v_rank = "");
		(($home_rank > 0) ? $d_h_rank = "#".$home_rank." " : $d_h_rank = "");
		if ($c>0) { $content .= "\t<tr><td colspan=\"3\"><hr height=\"2\"></td></tr>\n"; }
		$c++;
		#$content .= "\t<tr>\n\t\t<td colspan=\"3\" nowrap><img src=\"images/poollogos/tv/".$tvnetwork.".png\" align=\"left\"";
		$content .= "\t<tr>\n\t\t<td colspan=\"3\"><img src=\"images/poollogos/tv/".$tvnetwork.".png\" align=\"left\"";
		$content .= " alt=\"On ".$network{$tvnetwork}."\" title=\"On ".$network{$tvnetwork}."\">";
		if (($tvnetwork2) && ($tvnetwork2 > 0)) {
			$content .= "<img src=\"images/poollogos/tv/".$tvnetwork2.".png\"";
			$content .= " alt=\"Also on ".$network{$tvnetwork2}."\" title=\"Also on ".$network{$tvnetwork2}."\">";
		}
		if (!( $title == '' )) {
			$content .= "<img src=\"images/spacer16.png\"><font class=\"btitle\"><b>".$title."</b></font>";
		}
		$content .= "</td>\n\t</tr>\n\t<tr>\n\t\t<td colspan=\"3\">";
		$content .= "<font class=\"btitle\">".$day.", ".$date." - ".$time." ".$timestring."</font><br>";
		$content .= "</td>\n\t</tr>\n";
		if ($visitor < 900) {
			$record = " (".intval($home_wins[$visitor]+$away_wins[$visitor])."-".intval($home_losses[$visitor]+$away_losses[$visitor]);
			if ($neutral == '0') { $record .= ", road: ".intval($away_wins[$visitor])."-".intval($away_losses[$visitor]); }
			$record .= ")";
		} else {
			$record = "";
		}
		$altstring = "alt=\"".$d_v_rank.$team_name[$visitor]." ".$record."\" title=\"".$team_name[$visitor]." ".$record."\"";
		$content .= "\t<tr>\n\t\t<td>";
		if ($visitor < 900) {
			$content .= "<a href=\"modules.php?name=Pool-NCAA&amp;op=TeamSchedule&amp;team=$visitor\">";
		}
		$content .= "<img src=\"images/poollogos/helmets/".$visitor.".gif\" ".$altstring." width=\"160\" height=\"100\" border=0>";
		$content .= "</a>";
		$content .= "</td>\n";
		if ($neutral == '0') {
			$content .= "\t\t<td><img src=\"images/poollogos/at.png\" width=\"40\" height=\"40\"></td>\n";
		} elseif ($neutral == '1') {
			$content .= "\t\t<td><img src=\"images/poollogos/vs.png\" width=\"40\" height=\"40\"></td>\n";
		}
		if ($home < 900) {
			$record = " (".intval($home_wins[$home]+$away_wins[$home])."-".intval($home_losses[$home]+$away_losses[$home]);
			if ($neutral == '0') { $record .= ", home: ".intval($home_wins[$home])."-".intval($home_losses[$home]); }
			$record .= ")";
		} else {
			$record = "";
		}
		$altstring = "alt=\"".$d_h_rank.$team_name[$home]." ".$record."\" title=\"".$team_name[$home]." ".$record."\"";
		$content .= "\t\t<td>";
		if ($home < 900) {
			$content .= "<a href=\"modules.php?name=Pool-NCAA&amp;op=TeamSchedule&amp;team=$home\">";
		}
		$content .= "<img src=\"images/poollogos/helmets/".$home.".gif\" ".$altstring." width=\"160\" height=\"100\" border=0>";
		$content .= "</a>";
		$content .= "</td>\n\t</tr>";
	}
	$content .= "</table>\n</td>";
}
if ($which == 2 || $which == 3) {
	$content .= "<td background=\"http://football-pools.org/images/white.png\" valign=\"top\">\n\n";
	$content .= "<table><tr><th colspan=\"3\" align=\"middle\" width=\"320\"><h2>NFL</h2><hr></th></tr>\n";
	$sql =  "SELECT week, date, time, home, visitor, home_rank, visitor_rank, tvnetwork, Title, neutral FROM ".$prefix."_pool_games";
	$sql .= " WHERE ((date > '".$today_date."') OR (date = '".$today_date."' AND time > '".$gstart."'))";
	$sql .= " AND season = '".$season."' AND league = 'NFL' AND  (tvnetwork IS NOT NULL AND (tvnetwork > 0))";
	$sql .= " ORDER BY date, time, home LIMIT ".$howmany;
	if ($debug > 0) { $content .= "<!-- HBT3 - \$sql='$sql' -->\n"; }
	$result = sql_query($sql, $dbi);
	$c=0;
	while ($row = $db->sql_fetchrow($result)) {
		$game = $row['game'];
		$date = $row['date'];
		$day = date("l", strtotime($date));
		$time = $row['time'];
		$home = $row['home'];
		$home_rank = $row['home_rank'];
		$visitor = $row['visitor'];
		$visitor_rank = $row['visitor_rank'];
		$tvnetwork = $row['tvnetwork'];
		$title = $row['Title'];
		$neutral = $row['neutral'];
		list($y,$mo,$d) = explode("-",$date);
		$h = substr($time,0,2);
		$mi = substr($time,2);
		$DST = localtime(mktime($h,$mi,0,$mo,$d,$y));
		$game_day = $DST[7]+1;
		((($game_day > $DST_start) && ($game_day < $DST_end)) ? $timestring = "CDT" : $timestring = "CST" );
		(($visitor_rank > 0) ? $d_v_rank = "#".$visitor_rank." " : $d_v_rank = "");
		(($home_rank > 0) ? $d_h_rank = "#".$home_rank." " : $d_h_rank = "");
		if ($c > 0) { $content .= "\t<tr><td colspan=\"3\"><hr height=\"2\"></td></tr>\n"; }
		$c++;
		#$content .= "\t<tr>\n\t\t<td colspan=\"3\" nowrap><img src=\"images/poollogos/tv/".$tvnetwork.".png\" align=\"left\"";
		$content .= "\t<tr>\n\t\t<td colspan=\"3\"><img src=\"images/poollogos/tv/".$tvnetwork.".png\" align=\"left\"";
		$content .= " alt=\"On ".$network{$tvnetwork}."\" title=\"On ".$network{$tvnetwork}."\">";
		if (($tvnetwork2) && ($tvnetwork2 > 0)) {
			$content .= "<img src=\"images/poollogos/tv/".$tvnetwork2.".png\"";
			$content .= " alt=\"Also on ".$network{$tvnetwork2}."\" title=\"Also on ".$network{$tvnetwork2}."\">";
		}
		if (!( $title == '' )) {
			$content .= "<img src=\"images/spacer16.png\"><font class=\"btitle\"><b>".$title."</b></font>";
		}
		$content .= "</td>\n\t</tr>\n\t<tr>\n\t\t<td colspan=\"3\">";
		$content .= "<font class=\"btitle\">".$day.", ".$date." - ".$time." ".$timestring."</font><br>";
		$content .= "</td>\n\t</tr>\n";
		if ($visitor < 900) {
			$record = " (".intval($home_wins[$visitor]+$away_wins[$visitor])."-".intval($home_losses[$visitor]+$away_losses[$visitor]);
			if ($neutral =='0') {$record .= ", road: ".intval($away_wins[$visitor])."-".intval($away_losses[$visitor]); }
			$record .= ")";
		} else {
			$record = "";
		}
		$altstring = "alt=\"".$d_v_rank.$team_name[$visitor]." ".$record."\" title=\"".$team_name[$visitor]." ".$record."\"";
		$content .= "\t<tr>\n\t\t<td>";
		if ($visitor < 900) {
			$content .= "<a href=\"modules.php?name=Pool-NFL&amp;op=TeamSchedule&amp;team=$visitor\">";
		}
		$content .= "<img src=\"images/poollogos/helmets/".$visitor.".gif\" ".$altstring." width=\"160\" height=\"100\" border=0>";
		$content .= "</a>";
		$content .= "</td>\n";
		$content .= "\t\t<td><img src=\"images/poollogos/at.png\" width=\"40\" height=\"40\"></td>\n";
		if ($home < 900) {
			$record = " (".intval($home_wins[$home]+$away_wins[$home])."-".intval($home_losses[$home]+$away_losses[$home]);
			if ($neutral =='0') {$record .= ", home: ".intval($home_wins[$home])."-".intval($home_losses[$home]);}
			$record .= ")";
		} else {
			$record = "";
		}
		$altstring = "alt=\"".$d_h_rank.$team_name[$home]." ".$record."\" title=\"".$team_name[$home]." ".$record."\"";
		# Here we want to put in a link to the TeamSchedule page..
		$content .= "\t\t<td>";
		if ($home < 900) {
			$content .= "<a href=\"modules.php?name=Pool-NFL&amp;op=TeamSchedule&amp;team=$home\">";
		}
		$content .= "<img src=\"images/poollogos/helmets/".$home.".gif\" ".$altstring." width=\"160\" height=\"100\" border=0>";
		$content .= "</a>";
		$content .= "</td>\n\t</tr>";
	}
	$content .= "</table>\n</td>";
}
$content .= "</tr></table></center>\n";

?>


