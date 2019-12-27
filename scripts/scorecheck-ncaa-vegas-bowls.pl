#!/usr/bin/perl
#############################################################################
# scorecheck-ncaa-vegas.pl                                                  #
#    written by:  Henry B. Tindall, Jr. for football-pools.org              #
# original release:  4 Aug 2005                                             #
# version 2018.01 -  26 Sep 2018 - ported from the scorecheck-nfl.pl        #
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

$version='2018.01.2';
$spacing = "[ ]+";
$email_to = "henry\@football-pools.org";
$email_from="Score Checker (NCAA) <henry\@football-pools.org>";
my $dbname='footban4_nuke-pool';
my $dbuser='footban4_poolweb';
my $dbpass='F00tba11!';
$season='2018';
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
$sql .= " WHERE date <= '$bdate' AND league = 'NCAA' AND season = '$season'";
$sql .= " ORDER BY date DESC, time DESC limit 1";
if ($debug > 1) { print ("\$sql=\"$sql\"\n") }
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$week = $fields[0];
}
if ($debug > 0) { print ("\$week='$week'\n"); }

# Bowl season looks weird:
# http://www.vegasinsider.com/college-football/scoreboard/scores.cfm/week/17/season/2018
# so we'll just use the straight "http://www.vegasinsider.com/college-football/scoreboard/" URL for week 15.
$scoreurl='http://www.vegasinsider.com/college-football/scoreboard/'; 

if ($debug > 0) { print ("\$scoreurl='$scoreurl'\n"); }

$sql = "SELECT team_id, team_name FROM nuke_pool_teams NATURAL JOIN";
$sql .= " ( SELECT team_id, MAX(season) as season FROM nuke_pool_teams";
$sql .= " WHERE season <= '$season' and league = 'NCAA'"; 
$sql .= " GROUP BY team_id ) latestteam";
if ($debug > 0) { print ("\$sql=\"$sql\"\n") }
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$teamid{$fields[1]} = $fields[0];
	$teamname{$fields[0]} = $fields[1];
	if ($debug > 1) { 
		print ("------> \$teamid{$fields[1]} = \"$teamid{$fields[1]}\", "); 
		print ("\$teamname{$fields[0]} = \"$teamname{$fields[0]}\"\n");
	}
}

$sql = "SELECT team_id, team_name FROM nuke_pool_teams_rev";
$sql .= " WHERE league = 'NCAA'"; 
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$lcname=lc($fields[1]);
	$dispteamname_id{$lcname} = $fields[0];
	if ($debug > 2 ) { print ("\$fields[1]=\"$fields[1]\"\n"); }
	if ($debug > 2 ) { print ("\$lcname=\"$lcname\"\n"); }
	if ($debug > 1 ) { print ("\$dispteamname_id{$lcname}=\"$dispteamname_id{$lcname}\"\n"); }
}

# Regular expressions for matching:
# gameregexp:
# <td class="sportPicksBorderR2" colspan="9"><b><A HREF='/college-football/teams/team-page.cfm/team/kennesaw-state' CLASS='black'>KENNESAW STATE</a> @ <A HREF='/college-football/teams/team-page.cfm/team/tennessee-tech' CLASS='black'>TENNESSEE TECH</a></b></td>
$gameregexp="<td class=\"sportPicksBorderR2\" colspan=\"9\"><b><A HREF='/college-football/teams/team-page.cfm/team/[^']+' CLASS='black'>([^<]+)</a> @ <A HREF='/college-football/teams/team-page.cfm/team/[^']+' CLASS='black'>([^<]+)</a></b></td>";
# 
# OTcheckregexp:   (trusting this one from the NFL carryover... ;) )
# <td class="sportPicksBorderL zerocenter">&nbsp;T&nbsp;</td>
#$OTcheckregexp='<td class="sportPicksBorderL zerocenter">&nbsp;T&nbsp;</td>';
$OTcheckregexp='TD class="sportPicksBorderL center_text" nowrap>&nbsp;<B>T</B>&nbsp';
#
# Date:
# <strong>Week 2 - Thursday September 6, 2018</strong>
# <strong>Bowl Week 1 - Friday December 14, 2018</strong>
#
$gamedate='<strong>Bowl Week (\d+) - \w+\s+([^\s]+)\s+(\d+), (\d{4})</strong>';
# Final team score:
# <font color="#b20000"><B> &nbsp;7&nbsp; </B></font>
$finalregexp='<font color="#b20000"><B> &nbsp;(\d+)&nbsp; </B></font>';

#Temp override
#$scoreurl='http://www.vegasinsider.com/college-football/scoreboard/scores.cfm/week/17/season/2018';
my $content = get($scoreurl);
#my $content = `/usr/bin/curl -k -o- $scoreurl`;
#my $content = `/usr/bin/wget --no-check-certificate -O - $scoreurl`;
if ($debug > 2) { print ("\$content=\"$content\"\n"); }
if ($debug > 2) { print ("\$#content=\"$#content\"\n"); }

$content =~ s/^[[:space:]]+$/\n/mg;
$content =~ s/^\s*$//mg;
$content =~ s/^[ ]*$//mg;
$content =~ s/\n\n/\n/sg;

# After substitutions:
if ($debug > 2) { print ("After substitution: \$#content=\"$#content\"\n"); }

@scores = split("\n",$content);
for ($y=0; $y < $#scores+1; $y++) {
	chomp $scores[$y];
	# zero !
	$vteam=$hteam=$vteamscore=$hteamscore=$overtime='';
	if ($debug > 2) { print ("\$scores[$y]=\"$scores[$y]\"\n"); }
	# grab a date, if present.
	if ( $scores[$y] =~ /$gamedate/ ) {
		$gmonth=$months{$2};
		$day=$3;
		if ($day <= 9) { 		
			$gday=@pad[$day];
		} else {
			$gday=$day;
		}
		$gyear=$4;
		$dbdate=$gyear."-".$gmonth."-".$gday;
		if ($debug > 1) { print ("\$dbdate = \"$dbdate\", \$gweek = \"$gweek\"\n"); }
	}
	# Match the "gameregexp" line....   regexp has to be the LAST check, for capture.
	elsif (( $scores[$y+5] =~ "Final Score" ) && ( $scores[$y] =~ /$gameregexp/ )) {
		$visitor=lc($1);
		$vteamid=$dispteamname_id{$visitor};
		$home=lc($2);
		$hteamid=$dispteamname_id{$home};
		# convert all the interesting info to lower case, for matching.
		if ($debug > 1) { print ("game regexp & game-status match\n") }
		if ($debug > 2) { print ("\$visitor = \"$visitor\", \$vteamid = \"$vteamid\"\n"); }
		if ($debug > 2) { print ("\$home = \"$home\", \$hteamid = \"$hteamid\"\n"); } 
		# make sure there was no overtime:
		if ( $scores[$y+35] =~ $OTcheckregexp ) {
		# must be some OT ! (we'll use this to calculate how much farther down the scores must be...)
 			if ($debug > 1) { print ("We have OT...\n"); }
			# Y+60 is visitor score (overtime), +88 is home.
			if ( $scores[$y+60] =~ /$finalregexp/ ) {
				$vteamscore = $1; 
			}
			if ( $scores[$y+88] =~ /$finalregexp/ ) {
				$hteamscore = $1; 
			}
		# probably should elseif the next, for no OT, in case we have to modify later to  add double OT.
        # } elseif ( $scores[$y+31] =~ $OTcheckregexp ) {
		} else {
			if ($debug > 2) { print ("No OT...\n"); }
			# Y+53 is visitor score (no overtime), +78 is home.
			if ( $scores[$y+53] =~ /$finalregexp/ ) {
				$vteamscore = $1; 
			}
			if ( $scores[$y+78] =~ /$finalregexp/ ) {
				$hteamscore = $1; 
			}
		}			
		print ("Updating $teamname{$vteamid} $vteamscore vs. $teamname{$hteamid} $hteamscore\n");
		if ($debug > 0) { print ("\$vteamid=\"$vteamid\"; \$vteamscore=\"$vteamscore\"; \$hteamid=\"$hteamid\"; \$hteamscore=\"$hteamscore\"\n") }
		$sql = "UPDATE nuke_pool_games SET home_score =  '$hteamscore', visitor_score =  '$vteamscore'";
		if ($week < '15') {
			$sql .= " WHERE `league` =  'NCAA' AND `home` = '$hteamid' AND `visitor` = '$vteamid' AND `week` = '$gweek' AND `date` = '$dbdate' AND ( `home_score` is NULL AND `visitor_score` is NULL ) LIMIT 1";
		} else {
			$sql .= " WHERE `league` =  'NCAA' AND `home` = '$hteamid' AND `visitor` = '$vteamid' AND `week` = '$week' AND `date` = '$dbdate' AND ( `home_score` is NULL AND `visitor_score` is NULL ) LIMIT 1";
		}
		if ($debug > 0) { print ("\"$sql\"\n"); }
		$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
		if ((!($debug > 1)) && ( $hteamscore != '' || $vteamscore != '' )) { 
			$rows = $dbh->do( $sql );
			print "database updates: $rows.\n";
			if ($rows != '0E0' ) { 
				$updates += $rows; 
				$msgbody .= "Updating $teamname{$vteamid} $vteamscore vs. $teamname{$hteamid} $hteamscore<br>\n";
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
