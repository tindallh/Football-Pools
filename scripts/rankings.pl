#!/usr/bin/perl
#############################################################################
# rankings.pl                                                               #
#    written by:  Henry B. Tindall, Jr. for football-pools.org              #
# original release:  6 Nov 2011                                             #
# version 2019.1.0 - 05 Sep 2019 - minor changes to regexps.                #
#         1.0 - 06 Nov 2011 - original release, using usatoday as source.   #
#############################################################################

use DBI;

$version=2019.1.0;
$debug = 1;

$spacing = "[\s]+";

$email_to = "henry\@football-pools.org";
$email_from = "NCAA Rankings Checker <henry\@football-pools.org>";
my $dbname = 'footban4_nuke-pool';
my $dbuser = 'footban4_poolweb';
my $dbpass = 'F00tba11!';
my $seasonID = '2019';
my $updatecounts = 0;
my $rankingsurl = "https://www.usatoday.com/sports/ncaaf/polls/amway-coaches-poll/";
my $dbh = DBI->connect("dbi:mysql:$dbname", $dbuser, $dbpass)|| die "Cannot connect to db server $DBI::errstr,\n";

@pad=("00","01","02","03","04","05","06","07","08","09");

# okey-doke, now lets grab this week's games.
# Since we run the script on Sunday evening, it's a safe bet that
# today + 2 days will be in the week we're updating.
# two days out is Tuesday, if there's not one Tues. through Fri., We will catch Saturday.
$twodays=60*60*24*(2);
$now=time();
$weektime=$now+$twodays;
($j1,$j1,$j1,$day,$mon,$year,$j1)=localtime($weektime);
$mon +=1;
$year +=1900;
if ($mon <= 9) {$mon=$pad[$mon];}
if ($day <= 9) {$day=$pad[$day];}
$adate=$mon."/".$day."/".$year;
$bdate=$year."-".$mon."-".$day;
my $dbh = DBI->connect("dbi:mysql:$dbname", $dbuser, $dbpass)|| die "Cannot connect to db server $DBI::errstr,\n";
$sql = "SELECT week FROM nuke_pool_games";
$sql .= " WHERE date >= '$bdate' AND league = 'NCAA' AND season = '$seasonID'";
$sql .= " ORDER BY date, time limit 1";
print ("\$sql=\"$sql\"\n");
$cursor = $dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$week = $fields[0];
}
if ($debug > 0) { print ("\$week=\"$week\"\n"); }
#$week=6; # Just for Bowls !!

$sql="SELECT team_id, team_name FROM nuke_pool_teams_rev WHERE league='NCAA'";
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$teamid{$fields[1]} = $fields[0];
	$fullteamname_id{$fields[1]} = $fields[0];
	if ($debug > 1) { print ("--- \$teamid{$fields[1]}='$teamid{$fields[1]}'\n"); }
	if ($debug > 1) { print ("--- \$fullteamname_id{$fields[1]}='$fields[0]'\n"); }
}
$sql="SELECT team_id,team_name FROM nuke_pool_teams WHERE league='NCAA'";
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$teamname{$fields[0]} = $fields[1];
	if ($debug > 2) { print ("\$teamname{$fields[0]}='$teamname{$fields[0]}'\n"); }
}


print ("\$rankingsurl=\"$rankingsurl\"\n");
#$rcmd = "/usr/bin/lynx -lss=~/lynx.lss -force_html -source $rankingsurl";
$rcmd = "/usr/bin/curl -q -o- $rankingsurl";
#$rcmd = 'cat ~/scripts/coachespoll.txt';
$rcmd .= ' | sed -re \'s/<\/td>/<\/td>\n/g\'';
$rcmd .= ' | sed -re \'s/<tr/\n<tr/g\'';
# This next row won't match anything, we just stuck it in as an example straight out of the 
# TVbroadcast script
# $rcmd .= ' | sed -re \'s/<a href=\"http:\/\/btn.com\/gamefinder\/\" Title=\"GameFinder\">//g\'';

@srankings = `$rcmd`;
#$regexp_date = '<tr class="dateRow"><td colspan=9>Updated ([\d]{2})/([\d]{2})/([\d]{4})</td></tr>';
$regexp_date = 'content:\'Week (\d+) results: Published ([^ ]+) (\d+), (\d+)\';disp';
$newrankings = 0;
for ($y=0; $y < $#srankings+1; $y++) {
	chomp $srankings[$y];
	if ($srankings[$y] =~ /$regexp_date/ ) {
		$htmldate=$2." ".$3.", ".$4;
		$startrow=$y;
		($j1,$j1,$j1,$day,$mon,$year,$wday,$j1,$j1) = localtime();
		$rankingsweek=$1;
		# 
		# Some kind of SNAFU, adding one to make a match as of 21 October 2019 !!
		$rankingsweek++;
		if ($debug > 0) { print ("Found \$regexp_date, rankingsweek is \"$rankingsweek\"\n"); }
		if ( $rankingsweek == $week ) { 
			$newrankings =1; 
			if ($debug > 0) { print ("Matches; week is \"$week\"\n"); }
		} else {
			if ($debug > 0) { print ("NO Match; week is \"$week\"\n"); }
		}
	}
}
## $newrankings = 1;   # Just for testing !!
my $teamrowregexp = '<td class="gnt_sp_td gnt_sp_td__tm" style=background-image:url\(https://www.gannett-cdn.com/.*\.png\) aria-label=["]*([^>"]+)["]*><span class=gnt_sp_tm_mblo>';
my $rankingrowregexp = 'class=gnt_sp_td>(\d+)</td>';
if ($newrankings == 1 ) {
	for ($y=$startrow; $y < $#srankings+1; $y++) {
		if ($debug > 2) { print ("$y\t-\"$srankings[$y]\"\n"); }
		if ($srankings[$y]=~ /$teamrowregexp/ ) {
			$fullteamname=$1;
			if ($srankings[$y-1]=~ /$rankingrowregexp/ ) { 
				$trank=$1;
			} else {
				if ($debug > 1) { print ("No rank match on line $srankings[$y-1].\n"); }
			}
			$idnum=$fullteamname_id{$fullteamname};
			if ($debug > 0) { print ("+++ \$idnum=\"$idnum\"; \$fullteamname='$fullteamname',\$trank='$trank'\n"); }
			$ranks{$idnum} = $trank; 
			if ($debug > 0) { print ("+++ \$ranks{$idnum}=$ranks{$idnum}}, \$fullteamname='$fullteamname',\$trank='$trank'\n"); }
		}
	}
	open(SENDMAIL, "|/usr/sbin/sendmail -t") or die "Sendmail Offline: $!\n";
	print SENDMAIL "From: $email_from\n";
	print SENDMAIL "To: $email_to\n" or die "E-Mail wrong.\n";
	print SENDMAIL "Content-Type: multipart/alternative;\n";
	print SENDMAIL "        boundary=\"BEGIN_HTML\"\n";
	print SENDMAIL "X-Priority: 1\n";
	print SENDMAIL "Subject: Football Pools (NCAA) - Rankings update\n\n";
	print SENDMAIL "X-MSMail-Priority: High\n\n\n";
	print SENDMAIL "--BEGIN_HTML\n";
	print SENDMAIL "Content-Type: text/html; charset=utf-8\n";
	print SENDMAIL "Content-Transfer-Encoding: 8bit\n\n\n";
	foreach $teamid (keys %ranks) {
		$sql="SELECT game, home, visitor FROM nuke_pool_games";
		$sql.=" WHERE (league='NCAA' AND season = '$seasonID' AND week = '$week'";
		$sql.=" AND (home  = '$teamid' OR visitor = '$teamid'))";
		$cursor = $dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
		if ($debug > 0) {  print ("\$sql='$sql'\n"); }
		$cursor->execute;
		undef $game;
		undef $home;
		undef $visitor;
		while(@fields = $cursor->fetchrow) {
			$game = $fields[0];
			$home = $fields[1];
			$visitor = $fields[2];
		}
		if ($debug > 0) { print ("\$home='$home', \$visitor='$visitor', \$game='$game', \$teamid='$teamid'\n"); }
		if ($home == $teamid) {
		$sql2 = "UPDATE nuke_pool_games SET `home_rank` = '$ranks{$teamid}'";
			$sql2 .= " WHERE `league` = 'NCAA' AND `season` = '$seasonID' AND `week` = '$week' AND `game` = '$game' LIMIT 1";
			$cursor2 = $dbh->prepare($sql2) || die "Cannot prepare statement: $DBI::errstr\n";
			if ($debug < 2) { $cursor2->execute; }
			print ("Updating week $week, game #$game, $teamname{$visitor} @ $teamname{$home}, $teamname{$home} is ranked #$ranks{$teamid}.\n");
			print SENDMAIL ("Updating week $week, game #$game, $teamname{$visitor} @ $teamname{$home}, $teamname{$home} is ranked #$ranks{$teamid}.<br>\n");
			$updatecounts++;
		} elsif ($visitor == $teamid) {
			$sql2 = "UPDATE nuke_pool_games SET `visitor_rank` = '$ranks{$teamid}'";
			$sql2 .= " WHERE `league` = 'NCAA' AND `season` = '$seasonID' AND `week` = '$week' AND `game` = '$game' LIMIT 1";
			$cursor2 = $dbh->prepare($sql2) || die "Cannot prepare statement: $DBI::errstr\n";
			if ($debug < 2) { $cursor2->execute; }
			print ("Updating week $week, game #$game, $teamname{$visitor} @ $teamname{$home}, $teamname{$visitor} is ranked #$ranks{$teamid}.\n");
			print SENDMAIL ("Updating week $week, game #$game, $teamname{$visitor} @ $teamname{$home}, $teamname{$visitor} is ranked #$ranks{$teamid}.<br>\n");
			$updatecounts++;
		} else {
			print ("It would appear that #$ranks{$teamid}  $teamname{$teamid} is idle week $week.\n");
			print SENDMAIL ("It would appear that #$ranks{$teamid} $teamname{$teamid} is idle week $week.<br>\n");
			$updatecounts++;
		}
	}
	if ( $updatecounts == 0) { print SENDMAIL "No updates made!\n"; }
	print SENDMAIL "\n\n";
	close(SENDMAIL) or warn "sendmail didn't close nicely";
}
print ("Done!\n");
exit(0);

