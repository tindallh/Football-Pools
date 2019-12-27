#!/usr/bin/perl

#############################################################################
# gametimecheck.pl                                                          #
#    written by:  Henry B. Tindall, Jr. for football-pools.org              #
# Last update: 07 Sep 2009                                                  #
# version 2016.1.0 - 17 Sep 2016 - moved mail logic to the end to put more  #
#                    info in the subject line.                              #
#         2.1 - 07 Sep 2009 - converted the oddscheck script to also check  #
#               for start times, since the old one still used the excite    #
#               URL until now.                                              #
#         2.0 - 21 Oct 2008 - complete (almost) rewrite, due to yahoo       #
#               changing the format of the spreads.                         #
#         1.1 - 04 Sep 2009 - changed the source URL from excite.com to     #
#               yahoo.com because the yahoos at excite weren't updating     #
#               the scores for days at a time...                            #
#         1.0 - 04 Aug 2008 - original release                              #
#############################################################################

use DBI;

$version='2016.1.0';
$spacing = "[ ]+";
$email_to = "henry\@football-pools.org";
$email_from="Football-Pools";
my $dbname='footban4_nuke-pool';
my $dbuser='footban4_poolweb';
my $dbpass='F00tba11!';
my $seasonID='2017';
my $debug=1;

@pad=("00","01","02","03","04","05","06","07","08","09");

$oddsurl = "http://sports.yahoo.com/ncaaf/odds";
# Since we run the script on Monday-Thursday, it's a safe bet that
# today + 4 days will be in the week we're updating.
$fourdays=60*60*24*5;
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
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$week = $fields[0];
}

$sql="SELECT team_name,team_id FROM nuke_pool_teams_rev WHERE league='NCAA' AND team_id < 900";
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$teamid{$fields[0]} = $fields[1];
	if ($debug > 1) {  print ("\$teamid{$fields[0]}='$teamid{$fields[0]}'\n"); }
}

$sql="SELECT team_id,team_name FROM nuke_pool_teams WHERE league='NCAA' AND team_id < 900";
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$teamname{$fields[0]} = $fields[1];
	if ($debug > 1) {  print ("\$teamname{$fields[0]}='$teamname{$fields[0]}'\n"); }
}

print ("\$oddsurl=\"$oddsurl\"\n");
@odds = `/usr/bin/lynx -lss=~/lynx.lss --source $oddsurl`;

# First find a starting point.
$start = '<caption>ncaaf Odds</caption>';
$begin_row = '<td class="teams ncaaf">';
# in the array, the x+1 is visitor, x+2 is home. x+4 is the game time.
$team_regexp = '<span class="team"><a href="http://sports.yahoo.com/ncaaf/teams/[^>]+>([^<]+)</a>';
$time_regexp = '<span>([^:]+):([^ ]+) ([ap])m (.[DS]T)</span>';
$g=1;

for ($x=0; $x < $#odds+1; $x++) {
	chomp $odds[$x];
  if ($debug > 1) { print ("$x\t--->$odds[$x]\n"); }
	$start_time=0;
	if ($ready == 1) {
		if ($odds[$x] =~ m/$begin_row/ ) {
			undef $id; 
			if ($odds[$x+2] =~ m/$team_regexp/ ) {
				$id=$1;
				print ("Home: \$id='$id', \$teamid='$teamid{$id}'\n"); 
				$s_home[$g] = $teamid{$id};
			}
			undef $id; 
			if ($odds[$x+1] =~ m/$team_regexp/ ) {
				$id=$1;
				print ("Visitor: \$id='$id', \$teamid='$teamid{$id}'\n"); 
				$s_visitor[$g] = $teamid{$id}; 
			}
			# First, split up the time stuff into bits.
			if ($odds[$x+4] =~ m/$time_regexp/ ) {
				$st_hour = $1;
				$st_min = $2;
				$st_ap = $3;
				$st_tz = $4;
				if ($st_ap == 'p') {
					if ($st_hour < 12) {
						$st_hour +=12; 
					}
				}
				if ($st_tz =~ "E") { $st_hour -=1; }
				$start_time[$g] = ($st_hour.$st_min);
				if ($debug > 2) { print ("\$st_hour = '$st_hour', \$st_min = '$st_min',\$start_time = '$start_time[$g]'\n"); }
			} else {
				print ("No regexp match on time!\n");
			}
			# If everything has gone according to plan, Now I have a Home, Visitor, and start time.
			$g++;                #increment game number;
		}
	} elsif ($odds[$x] =~ m/$start/ ) {
		print ("Found the Start !!!!!!!!!!!!!!!!!!!!!!!!!!!\n\n");
		$ready=1;
	}
}

#for ($g=1; $g < $#s_spread; $g++) {
#	print ("$teamname{$s_visitor[$g]} @ $teamname{$s_home[$g]}, $s_spread[$g]\n");
#}

undef %home;
undef %visitor;
$sql="SELECT home,visitor,game FROM nuke_pool_games";
$sql.=" WHERE (league='NCAA' AND season = '$seasonID' AND week = '$week')";
$sql.=" AND time = '0800'";
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$home{$fields[2]} = $teamname{$fields[0]};
	$visitor{$fields[2]} = $teamname{$fields[1]};
	print("'$teamname{$fields[1]}' @ '$teamname{$fields[0]}', game #$fields[2] needs a start time.\n");
}

$updates=0;
foreach $game (keys %home) {	
	$hometeam=$teamid{$home{$game}};
	$visitingteam=$teamid{$visitor{$game}};
	# Now match up against what we pulled from the spreads page...  At least one should match!
	for ($x=0; $x <= $#start_time; $x++) {
		if ((($s_home[$x] eq $hometeam) && ($s_visitor[$x] eq $visitingteam)) || ($s_home[$x] eq $visitingteam) && ($s_visitor[$x] eq $hometeam)) {
			# Match !!
			if (!($start_time[$x] == 0)) {
				$updates++;
				print ("($s_home[$x] == $hometeam) || ($s_visitor[$x] == $visitingteam)\n");
				$start_time = $start_time[$x];
				$sql = "UPDATE nuke_pool_games SET time = '$start_time'";
				$sql .= " WHERE league = 'NCAA' AND season = '$seasonID' AND week = '$week' AND game = '$game' LIMIT 1";
				print ("\"$sql\"\n");
				$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
				print ("Updating game # $game, $teamname{$visitingteam} @ $teamname{$hometeam} start @ $start_time\n");
				$cursor->execute;
				$msgbody .= "Updating Week $week game # $game, $teamname{$visitingteam} at $teamname{$hometeam} - start @ $start_time<br>\n";
			}
		}
	}
}

if ($debug > 0 || $updates > 0) {
	$verb='update';
	if ($updates > 1 || $updates == 0 ) { $verb = 'updates'; } 
	if ($updates == 0) { $updates = 'No'; }
	open(SENDMAIL, "|/usr/sbin/sendmail -t") or die "Sendmail Offline: $!\n";
	print SENDMAIL "From: $email_from\n";
	print SENDMAIL "To: $email_to\n" or die "E-Mail wrong.\n";
	print SENDMAIL "Content-Type: multipart/alternative;\n";
	print SENDMAIL "        boundary=\"BEGIN_HTML\"\n";
	print SENDMAIL "X-Priority: 3\n";
	print SENDMAIL "Subject: Football Pools (NCAA) - $updates Start Time $verb\n\n";
	print SENDMAIL "X-MSMail-Priority: Low\n\n\n";
	print SENDMAIL "--BEGIN_HTML\n";
	print SENDMAIL "Content-Type: text/html;\n";
	print SENDMAIL "Content-Transfer-Encoding: quoted-printable\n\n\n";
	print SENDMAIL "<!-- created by $0 version $version -->\n";
	print SENDMAIL "<br>";
	print SENDMAIL "$msgbody";
	print SENDMAIL "<br>\n";
	print SENDMAIL "$updates updates made.<br>\n";
	print SENDMAIL "\n\n";
	close(SENDMAIL) or warn "sendmail didn't close nicely";
}

print ("Done!\n");
exit(0);
