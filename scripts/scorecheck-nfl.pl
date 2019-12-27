#!/usr/bin/perl
#############################################################################
# scorecheck-nfl.pl                                                         #
#    written by:  Henry B. Tindall, Jr. for football-pools.org              #
# original release:  4 Aug 2005                                             #
# version 2019.1.0 - 05 Sep 2019 - minor changes to regexps.                #
#         2017.1.1 - 12 Sep 2017 - fixed sql to select week for updates     #
#         2017.1.0 - 11 Sep 2017 - Changed URL again, to vegasinsider       #
#                  and changed logic to use weekly scoreboard.              #
#         2016.2.0 - 16 Sep 2016 - changed URL for scores since CBS sucks   #
#                  and had to rewrite most of the sed scripts and logic.    #
#                  Hell, basically an entire re-write !                     #
#         2016.1.0 - 02 Sep 2016 - added # of updates in mail subject line. #
#                    changed SQL to reflect team moves...                   #
#         2.0 - 21 Oct 2008 - complete (almost) rewrite, due to yahoo       #
#               changing the format of the spreads.                         #
#         1.1 - 04 Sep 2008 - changed the source URL from excite.com to     #
#               yahoo.com because the yahoos at excite weren't updating     #
#               the scores for days at a time...                            #
#         1.0 - 04 Aug 2008 - original release                              #
#############################################################################

use DBI;
use LWP::Simple;

$version='2019.1.0';
$spacing = "[ ]+";
$email_to = "henry\@football-pools.org";
$email_from="Score Checker (NFL) <henry\@football-pools.org>";
my $dbname='footban4_nuke-pool';
my $dbuser='footban4_poolweb';
my $dbpass='F00tba11!';
$season='2019';
$debug = 1;

@pad=("00","01","02","03","04","05","06","07","08","09");
my %months = (
    "January"  => "01",
    "February" => "02",
    "August" => "08",
    "September" => "09",
    "October" => "10",
    "November" => "11",
    "December" => "12",
 );

$updates=0;
($j1,$j1,$j1,$day,$mon,$year,$j1)=localtime();
$mon +=1;
$year +=1900;
if ($mon <= 9) {$mon=$pad[$mon];}
if ($day <= 9) {$day=$pad[$day];}
$bdate=$year."-".$mon."-".$day;
if ($debug > 0) { print ("\$bdate=\"$bdate\"\n") }

my $dbh = DBI->connect("dbi:mysql:$dbname", $dbuser, $dbpass)|| die "Cannot connect to db server $DBI::errstr,\n";

$sql = "SELECT week FROM nuke_pool_games";
$sql .= " WHERE date <= '$bdate' AND league = 'NFL' AND season = '$season'";
$sql .= " ORDER BY date DESC, time DESC limit 1";
if ($debug > 0) { print ("\$sql=\"$sql\"\n") }
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$week = $fields[0];
}
if ($debug > 1) { print ("\$week='$week'\n"); }

$scoreurl="http://www.vegasinsider.com/nfl/scoreboard/scores.cfm/week/$week/season/$season";
if ($debug > 0) { print ("\$scoreurl='$scoreurl'\n"); }

$sql = "SELECT team_id, team_name FROM nuke_pool_teams NATURAL JOIN";
$sql .= " ( SELECT team_id, MAX(season) as season FROM nuke_pool_teams";
$sql .= " WHERE season <= '$season' and league = 'NFL'"; 
$sql .= " GROUP BY team_id ) latestteam";
if ($debug > 0) { print ("\$sql=\"$sql\"\n") }
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$teamid{$fields[1]} = $fields[0];
	$teamname{$fields[0]} = $fields[1];
	if ($debug > 2) { 
		print ("------> \$teamid{$fields[1]} = \"$teamid{$fields[1]}\", "); 
		print ("\$teamname{$fields[0]} = \"$teamname{$fields[0]}\"\n");
	}
}

# get the mascots.  120 - 151 is NFL.
$sql = "SELECT team_id, mascot";
$sql .= " FROM nuke_pool_teams_mascots";
$sql .= " WHERE team_id >= '120' AND team_id <= '151'";
if ($debug > 2) { print ("\$sql=\"$sql\"\n") }
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$lcmascot=lc($fields[1]);
	$mascot_id{$lcmascot} = $fields[0];
	$mascot_name{$fields[0]} = $lcmascot;
	if ($debug > 2) { 
		print ("-----> \$mascot_id{$lcmascot} = \'$fields[0]\', "); 
		print ("\$mascot_name{$fields[0]} = \'$lcmascot\'\n"); 
	}
}

my $content = get($scoreurl);

# Regular expressions for matching:
# gameregexp:
# <td class="yeallowBg2 sportPicksBorderR2 fourleft" colspan="10"><a class='black' href='/nfl/teams/team-page.cfm/team/jets'>NEW YORK JETS</a> @ <a class='black' href='/nfl/teams/team-page.cfm/team/bills'>BUFFALO BILLS</a></td>
# <td class="yeallowBg2 sportPicksBorderR2 fourleft" colspan="10"><a class='black' href='/nfl/teams/team-page.cfm/team/jaguars'>JACKSONVILLE JAGUARS</a> @ <a class='black' href='/nfl/teams/team-page.cfm/team/texans'>HOUSTON TEXANS</a></td>
# <td class="yeallowBg2 sportPicksBorderR2 fourleft" colspan="10"><a class='black' href='/nfl/teams/team-page.cfm/team/patriots'>NEW ENGLAND PATRIOTS</a> @ <a class='black' href='/nfl/teams/team-page.cfm/team/saints'>NEW ORLEANS SAINTS</a></td>
$gameregexp="<td class=\"yeallowBg2 sportPicksBorderR2 fourleft\" colspan=\"10\"><a class='black' href='/nfl/teams/team-page.cfm/team/([^']+)'>([^<]+)</a> @ <a class='black' href='/nfl/teams/team-page.cfm/team/([^']+)'>([^<]+)</a></td>";
# 
# OTcheckregexp:
# <td class="sportPicksBorderL zerocenter">&nbsp;T&nbsp;</td>
$OTcheckregexp='<td class="sportPicksBorderL zerocenter">&nbsp;T&nbsp;</td>';
#
# Date:
# <strong>Week 1 - Thursday September 7, 2017</strong>
$gamedate='<strong>Week (\d+) - \w+\s+([^\s]+)\s+(\d+), (\d{4})</strong>';
# Final team score:
# <font color="#b20000"><B> &nbsp;7&nbsp; </B></font>
$finalregexp='<font color="#b20000"><B> &nbsp;(\d+)&nbsp; </B></font>';

$content =~ s/^[[:space:]]+$/\n/mg;
$content =~ s/^\s*$//mg;
$content =~ s/^[ ]*$//mg;
$content =~ s/\n\n/\n/sg;
#$print $content;
@scores = split("\n",$content);
for ($y=0; $y < $#scores+1; $y++) {
	chomp $scores[$y];
	# zero !
	$vteam=$hteam=$vteamscore=$hteamscore='';
	if ($debug > 2) { print ("\$scores[$y]=\"$scores[$y]\"\n"); }
	# grab a date, if present.
	if ( $scores[$y] =~ /$gamedate/ ) {
		$gweek=$1;
		$gmonth=$months{$2};
		$day=$3;
		if ($day <= 9) { 		
			$gday=@pad[$day];
		} else {
			$gday=$day;
		}
		$gyear=$4;
		$dbdate=$gyear."-".$gmonth."-".$gday;
		if ($debug > 2) { print ("\$dbdate = \"$dbdate\", \$gweek = \"$gweek\"\n"); }
	}
	# Match the "gameregexp" line.... 
	elsif (( $scores[$y+4] =~ "Final Score" ) && ( $scores[$y] =~ /$gameregexp/ )) {
		$vmascot=lc($1);
		$visitor=lc($2);
		$vteamid=$mascot_id{$vmascot};
		$hmascot=lc($3);
		$home=lc($4);
		$hteamid=$mascot_id{$hmascot};
		# convert all the interesting info to lower case, for matching.
		if ($debug > 1) { print ("game regexp & game-status match\n") }
		if ($debug > 2) { print ("\$vmascot = \"$vmascot\", \$visitor = \"$visitor\"\n"); }
		if ($debug > 2) { print ("\$hmascot = \"$hmascot\", \$home = \"$home\"\n"); } 
		# make sure there was no overtime:
		if ( $scores[$y+25] =~ $OTcheckregexp ) {
		# only 4 periods. set offset to zero.
			$offset=0;
			if ($debug > 0) { print ("No offset for OT...\n"); }
		} elsif ( $scores[$y+45] =~ $OTcheckregexp ) {
		# must be some OT ! (we'll use this to calculate how much farther down the scores must be...)
			$offset=1;
			if ($debug > 0) { print ("setting offset for OT...\n"); }
		}
		# Y+46 is visitor score (no offset), +68 is home.
		if ( $offset == 0 ) {
			if ( $scores[$y+46] =~ /$finalregexp/ ) {
				$vteamscore = $1; 
				if ($debug > 0) { print ("found \$vteamscore at \$y ($y)+46...\n"); }
			}
			if ( $scores[$y+68] =~ /$finalregexp/ ) {
				$hteamscore = $1; 
				if ($debug > 0) { print ("found \$hteamscore at \$y ($y)+68...\n"); }
			}
		} elsif ( $offset == 1 ) {
			if ( $scores[$y+69] =~ /$finalregexp/ ) {
				$vteamscore = $1; 
				if ($debug > 0) { print ("found \$vteamscore at \$y ($y)+46...\n"); }
			}
			if ( $scores[$y+94] =~ /$finalregexp/ ) {
				$hteamscore = $1; 
				if ($debug > 0) { print ("found \$hteamscore at \$y ($y)+68...\n"); }
			}
		}
		# Taking some liberties here, since the NFL mascots are all unique.
		print ("Updating $teamname{$vteamid} $mascot_name{$vteamid} (id $vteamid) $vteamscore vs. $teamname{$hteamid} $mascot_name{$hteamid} (id $hteamid) $hteamscore\n");
		if ($debug > 0) { print ("\$vteamid=\"$vteamid\"; \$vteamscore=\"$vteamscore\"; \$hteamid=\"$hteamid\"; \$hteamscore=\"$hteamscore\"\n") }
		$sql = "UPDATE nuke_pool_games SET home_score =  '$hteamscore', visitor_score =  '$vteamscore'";
		$sql .= " WHERE `league` =  'NFL' AND `home` = '$hteamid' AND `visitor` = '$vteamid' AND `week` = '$gweek' AND `date` = '$dbdate' AND ( `home_score` is NULL AND `visitor_score` is NULL ) LIMIT 1";
		if ($debug > 0) { print ("\"$sql\"\n"); }
		$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
		if ((!($debug > 2)) && ( $hteamscore != '' && $vteamscore != '' )) { 
			$rows = $dbh->do( $sql );
			print "database updates: $rows.\n";
			if ($rows != '0E0' ) { 
				$updates += $rows; 
				$msgbody .= "Updating $teamname{$vteamid} $mascot_name{$vteamid} $vteamscore vs. $teamname{$hteamid} $mascot_name{$hteamid} $hteamscore<br>\n";
				$msgbody .= "<i>$sql</i><br><br>\n";
			}
		}
	}
}
if ($debug < 2 && $updates > 0) {
	open(SENDMAIL, "|/usr/sbin/sendmail -t") or die "Sendmail Offline: $!\n";
	print SENDMAIL "From: $email_from\n";
	print SENDMAIL "To: $email_to\n" or die "E-Mail wrong.\n";
	print SENDMAIL "Content-Type: multipart/alternative;\n";
	print SENDMAIL "        boundary=\"BEGIN_HTML\"\n";
	print SENDMAIL "X-Priority: 5\n";
	$verb='update';
	if ($updates > 1 || $updates == 0 ) { $verb = 'updates'; } 
	print SENDMAIL "Subject: $updates Final Score $verb, week $week\n\n";
	print SENDMAIL "X-MSMail-Priority: Low\n\n\n";
	print SENDMAIL "--BEGIN_HTML\n";
	print SENDMAIL "Content-Type: text/html;\n";
	print SENDMAIL "Content-Transfer-Encoding: quoted-printable\n\n\n";
	print SENDMAIL "<!-- created by $0 version $version -->\n";
	print SENDMAIL "<br>";
	print SENDMAIL "$msgbody";
	print SENDMAIL "<br>\n";
	print SENDMAIL "\n\n";
	close(SENDMAIL) or warn "sendmail didn't close nicely";
}
print ("Done!\n");
exit(0);
