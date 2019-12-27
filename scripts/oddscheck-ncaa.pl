#!/usr/bin/perl

#############################################################################
# oddscheck-ncaa.pl                                                         #
#    written by:  Henry B. Tindall, Jr. for football-pools.org              #
# original release:  4 Aug 2005                                             #
# version 2016.2.0 - 13 Oct 2016 - changed URL because Yahoo sucks for NCAA #
#                    as well as NFL.
#         2016.1.0 - 06 Sep 2016 - Added number of updates to subject line. #
#         3.0 - 04 Nov 2014 - changed to hash for matching games            #
#         2.1 - 27 Aug 2011 - changes in the matching, using fullname_id    #
# version 2.0 - 21 Oct 2008 - complete (almost) rewrite, due to yahoo       #
#               changing the format of the spreads.                         #
# version 1.1 - 04 Sep 2008 - changed the source URL from excite.com to     #
#               yahoo.com because the yahoos at excite weren't updating     #
#               the scores for days at a time...                            #
#         1.0 - 04 Aug 2008 - original release                              #
#############################################################################

use DBI;

$version = '2016.2.0';
$spacing = "[ ]+";
$email_to = "henry\@football-pools.org";
$email_from="Football-Pools";
my $dbname='footban4_nuke-pool';
my $dbuser='footban4_poolweb';
my $dbpass='F00tba11!';
my $seasonID='2016';
my $debug=1;

@pad=("00","01","02","03","04","05","06","07","08","09");
$monthID{January}='01';
$monthID{February}='02';
$monthID{March}='03';
$monthID{April}='04';
$monthID{May}='05';
$monthID{June}='06';
$monthID{July}='07';
$monthID{August}='08';
$monthID{September}='09';
$monthID{October}='10';
$monthID{November}='11';
$monthID{December}='12';

#$oddsurl = "http://sports.yahoo.com/ncaaf/odds";
$oddsurl = "http://www.vegasinsider.com/college-football/scoreboard/";
# Since we run the script on Monday-Thursday, it's a safe bet that
# today + 3 days will be in the week we're updating.
# Normally, I say three days out.  However, here's the fudge for testing...
$fudge=0;
$threedays=60*60*24*(3+$fudge);
$now=time();
$weektime=$now+$threedays;
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
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$week = $fields[0];
}
#$week=16; # Just for Bowl week !!
if ($debug > -1) { print "\$week = '$week'\n"; }

$sql="SELECT team_id, team_name FROM nuke_pool_teams WHERE league='NCAA' and `team_id` < 900";
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$teamname{$fields[0]} = $fields[1];
	if ($debug > 3) { print ("\$teamname{$fields[0]}='$teamname{$fields[0]}'\n"); }
}

$sql="SELECT team_id, team_name FROM nuke_pool_teams_rev WHERE league='NCAA' and `team_id` < 900";
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	# Converting the name to All upper to match against our HTML.
	$TN=uc($fields[1]);
	$teamid{$TN} = $fields[0];
	if ($debug > 3) { print ("\$teamid{$TN}='$teamid{$TN}'\n"); }
}

print ("\$oddsurl=\"$oddsurl\"\n");
# And we *Might* be able to snip out some garbage using sed...
$rcmd="lynx -lss=~/lynx.lss --source $oddsurl";
$rcmd .= ' | grep -vE "^[[:space:]]*$"';
$rcmd .= ' | sed \':a;N;$!ba;s/^[ ]*\n//g\'';   # get rid of blank lines.
@odds = `$rcmd`;

$gameheaderline="<TD ALIGN=\"[A-Z]+\" class=\"sportPicksBorderR2\" colspan=\"9\"><b><A HREF='/college-football/teams/team-page.cfm/team/[^']+' CLASS='black'>([^<]+)</a> @ <A HREF='/college-football/teams/team-page.cfm/team/[^']+' CLASS='black'>([^<]+)</a></b></TD>";
# in the HTML, after finding the gameheader line, 
# x+5 is the game time: "7:00 PM Game Time"
# x+23 is the over/under OR the spread if it has a "-" sign for vistior: "<TD CLASS="sportPicksBorderL2" align="middle">&nbsp;71.5&nbsp;</TD>"
$vspreadregexp='<TD CLASS="sportPicksBorderL2" align="middle">&nbsp;([^&]+)&nbsp;</TD>';
# x+35 is the over/under OR the spread if it has a "-" sign for home: "<TD align="middle" CLASS="sportPicksBorderL">&nbsp;-35&nbsp;</TD>"
$hspreadregexp='<TD align="middle" CLASS="sportPicksBorderL">&nbsp;([^&]+)&nbsp;</TD>';
# We also grab the week and the date, haha ! "<strong>Week 7 - Friday October 14, 2016</strong>"
$WDregexp='<strong>Week (\d+) - \w+ (\w+) (\d+), (\d+)';

for ($x=0; $x < $#odds+1; $x++) {
	chomp $odds[$x];
	if ($debug > 2) { print ("$x\t$odds[$x]\n"); }
	undef $h_spread;
	if ($odds[$x] =~ m/$WDregexp/ ) {
		$html_week=$1;
		$month=$monthID{$2};
		$day=$3;
		if ($day < 10) { $day = @padded[$day]; }
		$year=$4;
		$mydate=$year."-".$month."-".$day;
		if ($debug > 1) { print ("\$html_week=\"$html_week\"; \$month=\"$month\"; \$day=\"$day\"; \$year=\"$year\"; \$mydate=\"$mydate\"\n"); }
		if ($html_week != $week) { 
			print ("\$html_week <> \$week; ( $html_week <> $week)\n");
			#exit 1;
		}
	}
	if ($odds[$x] =~ m/$gameheaderline/ ) {
		$vteam=$1;
		$hteam=$2;
		if ($debug > 1) { print ("==========> Found gameheaderline <==========\n"); }
		if ( $teamid{$vteam} > 0 && $teamid{$hteam} > 0 ) {
			if ($odds[$x+23] =~ m/$vspreadregexp/ ) {
				$v_ouspread=$1;
				if ($debug > 1) { print ("\$v_ouspread=\'$v_ouspread\'\n"); }
				if ($v_ouspread < 0) {  # It's a spread, not an over/under.
					$h_spread = 0-$v_ouspread;
					# Maybe add in some over/under stuff here later !
				}
			}
			if ($odds[$x+35] =~ m/$hspreadregexp/ ) {
				$h_ouspread=$1;
				if ($debug > 1) { print ("\$h_ouspread=\'$h_ouspread\'\n"); }
				if ($h_ouspread < 0) {  # It's a spread, not an over/under.
					#reverse it so we get a postive number, until we fix all the displays.
					$h_spread = 0-$h_ouspread;
				}
			}
			if ($h_spread) {
				# Make the update.
				if ($debug > 1) { print ("\$h_spread=\"$h_spread\"\n"); }
				$hID=$teamid{$hteam};
				$vID=$teamid{$vteam};
				$sql = "UPDATE nuke_pool_games SET home_spread =  '$h_spread'";
				$sql .= " WHERE league = 'NCAA' AND season = '$seasonID'";
				$sql .= " AND `home` = '$hID' and `visitor` = '$vID'";
				$sql .= " AND week = '$week' AND `date` = '$mydate' and `home_spread` is NULL LIMIT 1";
				if (!($debug > 1)) { 
					$rows = $dbh->do( $sql );
					print "database updates: $rows.\n";
					if ($rows != '0E0' ) { 
						$updates += $rows; 
						print "Updating $vteam @ $hteam - ($h_spread)\n";
						$msgbody .= "Updating $vteam @ $hteam - ($h_spread)<br>\n";
						$msgbody .= "<i>$sql</i><br><br>\n";
					}
				} else {
					print ("NOT executing \$sql=\"$sql\"; debug=$debug\n");
				}
			} else {
				$msgbody .= "Still no spread for $vteam @ $hteam.<br>\n";
				print ("Still no spread for $vteam @ $hteam.\n");
			}
		} else {
			print ("One (or both) of the teams was not found!  \$vteam=\"$vteam\"; \$hteam=\"$hteam\"\n");
		}
			
	}
}

if ( $debug > 0 || $updates > 1) {
	$verb='update';
	if ($updates > 1 || $updates == 0 ) { $verb = 'updates'; } 
	if ($updates == 0 ) { $msgbody = "No updates made."; }
	open(SENDMAIL, "|/usr/sbin/sendmail -t") or die "Sendmail Offline: $!\n";
	print SENDMAIL "From: $email_from\n";
	print SENDMAIL "To: $email_to\n" or die "E-Mail wrong.\n";
	print SENDMAIL "Content-Type: multipart/alternative;\n";
	print SENDMAIL "        boundary=\"BEGIN_HTML\"\n";
	print SENDMAIL "X-Priority: 3\n";
	print SENDMAIL "Subject: Football Pools (NCAA) - Spreads - $updates $verb made\n\n";
	print SENDMAIL "X-MSMail-Priority: High\n\n\n";
	print SENDMAIL "--BEGIN_HTML\n";
	print SENDMAIL "Content-Type: text/html; charset=utf-8\n";
	print SENDMAIL "Content-Transfer-Encoding: 8bit\n\n\n";
	print SENDMAIL ("<!-- created by $0 version $version -->\n");
	print SENDMAIL ("$msgbody\n");
	print SENDMAIL "\n\n";
	close(SENDMAIL) or warn "sendmail didn't close nicely";
}

print ("Done!\n");

exit(0);
