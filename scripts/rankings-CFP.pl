#!/usr/bin/perl
#############################################################################
# bcsrankings.pl                                                            #
#    written by:  Henry B. Tindall, Jr. for football-pools.org              #
# original release:  6 Nov 2011                                             #
# version 1.0 - 06 Nov 2011 - original release, using usatoday as source.   #
#############################################################################

use DBI;

$version=1.0;
$debug = 2;

$spacing = "[\s]+";

$email_to = "henry\@football-pools.org";
$email_from = "NCAA Rankings Checker <henry\@football-pools.org>";
my $dbname = 'footban4_nuke-pool';
my $dbuser = 'footban4_poolweb';
my $dbpass = 'F00tba11!';
my $seasonID = '2019';
my $updatecounts = 0;
my @pad=("00","01","02","03","04","05","06","07","08","09");
my %months = (
    "Jan"  => "01",
    "Feb" => "02",
    "Oct" => "10",
    "Nov" => "11",
    "Dec" => "12",
 );

($j1,$j1,$j1,$day,$mon,$year,$wday,$j1,$j1) = localtime();
$mon +=1;
$year += 1900;
if ($day <= 9) {$day=$pad[$day];}
if ($mon <= 9) {$mon=$pad[$mon];}
$nowdate = $year."-".$mon."-".$day;

my $dbh = DBI->connect("dbi:mysql:$dbname", $dbuser, $dbpass)|| die "Cannot connect to db server $DBI::errstr,\n";

# get the mascots.
$sql = "SELECT team_id, mascot";
$sql .= " FROM nuke_pool_teams_mascots";
if ($testing == '1') { $sql .= "_test"; }
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$mascot{$fields[0]} = $fields[1];
	if ($debug > 2) { print ("\$mascot{$fields[0]} = \'$fields[1]\'\n"); }
}
$sql="SELECT team_name,team_id FROM nuke_pool_teams_rev WHERE league='NCAA'";
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$teamid{$fields[0]} = $fields[1];
	$teamname{$fields[1]} = $fields[0];
	$fullteamname = $fields[0]." ".$mascot{$fields[1]};
	$fullteamname_id{$fullteamname} = $fields[1];
	if ($debug > 1) { print ("\$teamid{$fields[0]}='$teamid{$fields[0]}'\n"); }
	if ($debug > 1) { print ("\$fullteamname_id{$fullteamname}='$fields[1]'\n"); }
}
$sql="SELECT team_id,team_name FROM nuke_pool_teams WHERE league='NCAA'";
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$teamname{$fields[0]} = $fields[1];
	if ($debug > 2) { print ("\$teamname{$fields[0]}='$teamname{$fields[0]}'\n"); }
}

#$rankingsurl = "https://www.cbssports.com/collegefootball/rankings/playoff";
$rankingsurl = "view-source:https://collegefootballplayoff.com/rankings.aspx?year=$year"
print ("\$rankingsurl=\"$rankingsurl\"\n");
#$rcmd = "/usr/bin/lynx -lss=~/lynx.lss --source $rankingsurl";
$rcmd = "/usr/bin/curl -q -o- $rankingsurl";
$rcmd .= ' | sed \':a;N;$!ba;s/>\n[ ]*<td/><td/g\' | sed \':a;N;$!ba;s/>\n<\/tr/><\/tr/g\'';
$rcmd .= ' | sed \':a;N;$!ba;s/>\n[ ]*<div/><div/g\'';
$rcmd .= ' | sed \':a;N;$!ba;s/>\n[ ]*<\/td/><\/td/g\''; 
$rcmd .= ' | sed \':a;N;$!ba;s/>\n[ ]*-[ ]*/>/g\''; 
$rcmd .= ' | sed \':a;N;$!ba;s/>\n[ ]*<\/div/><\/div/g\'';
$rcmd .= ' | sed \':a;N;$!ba;s/>\n[ ]*<span/><span/g\'';
$rcmd .= ' | sed \':a;N;$!ba;s/>\n[ ]*<img/><img/g\'';
$rcmd .= ' | sed \':a;N;$!ba;s/)\n[ ]*<\/div/)<\/div/g\'';
$rcmd .= ' | sed \':a;N;$!ba;s/\>\n[ ]+<center/\><center/g;:a;N;$!ba;s/\>\n[ ]*<\//\><\//g\'';
# This next row won't match anything, we just stuck it in as an example straight out of the 
# TVbroadcast script
# $rcmd .= ' | sed -re \'s/<a href=\"http:\/\/btn.com\/gamefinder\/\" Title=\"GameFinder\">//g\'';

@srankings = `$rcmd`;
#$regexp_date = '<tr class="dateRow"><td colspan=9>Updated ([\d]{2})/([\d]{2})/([\d]{4})</td></tr>';
#$regexp_date = '   (Nov|Dec) ([\d]{2}), ([\d]{4})';
$regexp_date = '';
$newrankings = 0;
for ($y=0; $y < $#srankings+1; $y++) {
	chomp $srankings[$y];
	if ($srankings[$y] =~ /$regexp_date/ ) {
		$gmonth=$months{$1};
		$htmldate=$3."-".$gmonth."-".$2;
		print ("Found Date! - \"$srankings[$y]\", \$htmldate=\"$htmldate\"\n"); 
		if ($htmldate == $nowdate) {
			print ("Match! - \$htmldate = '$htmldate', \$nowdate = '$nowdate'\n");
			$newrankings = 1;
		} else {
			print ("NO Match! - \$htmldate = '$htmldate', \$nowdate = '$nowdate'\n");
		}
	}
}
## $newrankings = 1;   # Just for testing !!
if ($newrankings == 1 ) {
	$rankingrowregexp = '<div class="rankTableCol">([\d]+)</div>';
	# Gets fun here.  The link for the team name looks like:
	# <span class="analysisTeamLabel"></span><a href="/collegefootball/teams/page/FSU">Florida State Seminoles</a>
	$myregexp = '<tr class="row[0-9]*"><td><div class="rankTableCol">([0-9]+)</div>.*<span class="analysisTeamLabel"></span><a href="/collegefootball/teams/page/[A-Za-z0-9]+">([A-Za-z0-9& ]+)</a>';
	for ($y=0; $y < $#srankings+1; $y++) {
		if ($debug > 1) { print ("$y\t-\"$srankings[$y]\"\n"); }
		if ($srankings[$y]=~ /$myregexp/ ) {
			$trank = $1;
			chomp $trank;
			$fullteamname = $2;
			$ranks{$fullteamname_id{$fullteamname}} = $trank; 
			if ($debug > 0) { print ("+++ \$ranks{$fullteamname_id{$fullteamname}}=$ranks{$fullteamname_id{$fullteamname}}, \$fullteamname='$fullteamname',\$trank='$trank'\n"); }
		}
	}
	# okey-doke, now lets grab next week's games, match up the teamids, and do an update;
	# Since we run the script on Sunday evening, it's a safe bet that
	# today + 4 days will be in the week we're updating.
	# four days out is Thursday, if there's not one Thurs. or Fri., We will catch Saturday.
	$fourdays=60*60*24*(4);
	$now=time();
	$weektime=$now+$fourdays;
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
	#$week=6; # Just for Bowls !!
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
			$cursor2->execute;
			print ("Updating week $week, game #$game, $teamname{$visitor} @ $teamname{$home}, $teamname{$home} is ranked #$ranks{$teamid}.\n");
			print SENDMAIL ("Updating week $week, game #$game, $teamname{$visitor} @ $teamname{$home}, $teamname{$home} is ranked #$ranks{$teamid}.<br>\n");
			$updatecounts++;
		} elsif ($visitor == $teamid) {
			$sql2 = "UPDATE nuke_pool_games SET `visitor_rank` = '$ranks{$teamid}'";
			$sql2 .= " WHERE `league` = 'NCAA' AND `season` = '$seasonID' AND `week` = '$week' AND `game` = '$game' LIMIT 1";
			$cursor2 = $dbh->prepare($sql2) || die "Cannot prepare statement: $DBI::errstr\n";
			$cursor2->execute;
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

