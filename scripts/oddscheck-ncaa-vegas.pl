#!/usr/bin/perl
#############################################################################
# oddscheck-ncaa.pl                                                         #
#    written by:  Henry B. Tindall, Jr. for football-pools.org              #
# original release:  4 Aug 2005                                             #
# version 2019.1.0 - 26 Aug 2019 - changed regexp to match teams in html    #
#                    and added logic to update for neutral site matches     #
#         2017.1.0 - 16 Aug 2017 - ported from NFL script                   #
#         2016.2.0 - 07 Sep 2016 - changed URL because Yahoo sucks.         #
#         2016.1.0 - 02 Sep 2016 - added # of updates in mail subject line. #
#         2.0 - 21 Oct 2008 - complete (almost) rewrite, due to yahoo       #
#               changing the format of the spreads.                         #
#         1.1 - 04 Sep 2008 - changed the source URL from excite.com to     #
#               yahoo.com because the yahoos at excite weren't updating     #
#               the scores for days at a time...                            #
#         1.0 - 04 Aug 2008 - original release                              #
#############################################################################

use DBI;

$spacing = "[ ]+";
$email_to = "henry\@football-pools.org";
$email_from="NCAA Spreads Checker <Henry\@Football-Pools.org>";
my $dbname='footban4_nuke-pool';
my $dbuser='footban4_poolweb';
#my $dbname='nuke-pool';
#my $dbuser='poolweb';
my $dbpass='F00tba11!';
my $seasonID='2019';
my $debug=1;

@pad=("00","01","02","03","04","05","06","07","08","09");

$oddsurl = "http://www.vegasinsider.com/college-football/odds/las-vegas/";
# Since we run the script on Monday-Thursday, it's a safe bet that
# today + 2 days will be in the week we're updating.
$twodays=60*60*24*2;
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
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$week = $fields[0];
}
if ($debug > 0) { print ("\$week}='$week'\n"); }

# Only for Bowl week !!!
#$week = '15';

$sql = "SELECT team_id, team_name FROM nuke_pool_teams NATURAL JOIN";
$sql .= " ( SELECT team_id, MAX(season) as season FROM nuke_pool_teams";
$sql .= " WHERE season <= '$seasonID' AND league = 'NCAA'"; 
$sql .= " GROUP BY team_id ) latestteam";
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$dispteamname{$fields[0]} = $fields[1];
	$dispteamname_id{$fields[1]} = $fields[0];
	if ($debug > 1 ) { print ("\$fields[1]=\"$fields[1]\"\n"); }
	if ($debug > 1 ) { print ("\$dispteamname_id{$fields[1]}=\"$dispteamname_id{$fields[1]}\", \$dispteamname{$fields[0]}=\"$dispteamname{$fields[0]}\"\n"); }
}
$sql = "SELECT team_id, team_name FROM nuke_pool_teams_rev";
$sql .= " WHERE league = 'NCAA'"; 
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$dispteamname_id{$fields[1]} = $fields[0];
	if ($debug > 1 ) { print ("\$fields[1]=\"$fields[1]\"\n"); }
	if ($debug > 1 ) { print ("\$dispteamname_id{$fields[1]}=\"$dispteamname_id{$fields[1]}\"\n"); }
}

$needspreads=0;
undef %home;
undef %visitor;
$sql="SELECT count(home) FROM nuke_pool_games";
$sql.=" WHERE (league='NCAA' AND season = '$seasonID' AND week = '$week')";
$sql.=" AND home_spread IS NULL";
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$needspreads = $fields[0];
}

if ($needspreads == 0) {
	print ("All games have spreads, exiting.\n");
	exit 1;
}

print ("\$oddsurl=\"$oddsurl\"\n");
@odds = `/usr/bin/lynx -lss=~/lynx.lss --source $oddsurl`;

if ($debug >2) {
	for ($u=1; $u < $#odds; $u++) {
		print ("$u\t-->$odds[$u]");
	}
}	
	
# First find a starting point.
$start = "<tr><td class='viHeaderNorm'>NCAA FOOTBALL SPREAD</td></tr>";
$begin_row = '<span class="cellTextHot">';
# in the array, the x+1 is visitor, x+2 is home.
$team_regexp = '\d+&nbsp;<b><a href="/college-football/teams/team-page.cfm/team/([^"]+)" title=".*class="tabletext">([^<]+)</a></b>';
# subsequent games:   x+6, x+11, x+16, and x+21 are the spreads, samples follow,top is visitor favored, bottom is home:
# &nbsp;<br>-1&nbsp;-10<br>44u-10
# &nbsp;<br>47&frac12;u-07<br>-3&nbsp;-12
# we'll use an if to see if it matches the regexp with the fraction...
# 
$spread_regexp_v = '&nbsp;<br>-(.*)&nbsp;-?(.*)<br>(.*)u(.*)';
$spread_regexp_h = '&nbsp;<br>(.*)u(.*)<br>-(.*)&nbsp;-?(.*)';
#$spread_regexp_v = '(?:&nbsp;<br>-(.*)&nbsp;-?(.*)<br>(.*)u(.*)|&nbsp;<br>-(.*)&nbsp;-(.*)<br>&nbsp;$)';
#$spread_regexp_h = '(?:&nbsp;<br>(.*)u(.*)<br>-(.*)&nbsp;-?(.*)|&nbsp;<br>&nbsp;<br>-(.*)&nbsp;-(.*)$)';
# attempting to interpret the spreads that don't have over/under
$spread_regexp_alt_v = '&nbsp;<br>-(.*)&nbsp;-(.*)<br>&nbsp;';
$spread_regexp_alt_h = '&nbsp;<br>&nbsp;<br>-(.*)&nbsp;-(.*)';
$g=1;

for ($x=0; $x < $#odds+1; $x++) {
	chomp $odds[$x];
if ($debug > 2) { print ("$x --->\"$odds[$x]\"\n"); }
	$spread=0;
	if ($ready == 1) {
		if ($odds[$x] =~ m/$begin_row/ ) {
			if ($debug > 1 ) { print ("!!!!!!!!!!!  Found begin row at $x !!!!!!!!!!!!! \n"); }
			undef $dispname; 
			if ($odds[$x+2] =~ m/$team_regexp/ ) {
				$dispname=$2;
				print ("Home: \$dispname='$dispname', \$dispteamname_id='$dispteamname_id{$dispname}'\n"); 
				$s_home[$g] = $dispteamname_id{$dispname};
			}
			undef $dispname; 
			if ($odds[$x+1] =~ m/$team_regexp/ ) {
				$dispname=$2;
				print ("Visitor: \$dispname='$dispname', \$dispteamname_id='$dispteamname_id{$dispname}'\n"); 
				$s_visitor[$g] = $dispteamname_id{$dispname}; 
			}
			if ($odds[$x+6] =~ m/$spread_regexp_h/ ) {
				$s_spread[$g] = $3;
				$s_spread[$g] =~ s/&frac12;/\.5/;
				if ($debug > 0) { print ("\$s_spread[$g]='$s_spread[$g]' found on line x+6\n"); }
			} elsif ($odds[$x+6] =~ m/$spread_regexp_alt_h/ ) {
				$s_spread[$g] = $1;
				$s_spread[$g] =~ s/&frac12;/\.5/;
				if ($debug > 0) { print ("\$s_spread[$g]='$s_spread[$g]' (alt) found on line x+6\n"); }
			} elsif ($odds[$x+6] =~ m/$spread_regexp_v/ ) {
				$s_spread[$g] = '-'.$1;
				$s_spread[$g] =~ s/&frac12;/\.5/;
				if ($debug > 0) { print ("\$s_spread[$g]='$s_spread[$g]' found on line x+6\n"); }
			} elsif ($odds[$x+6] =~ m/$spread_regexp_alt_v/ ) {
				$s_spread[$g] = '-'.$1;
				$s_spread[$g] =~ s/&frac12;/\.5/;
				if ($debug > 0) { print ("\$s_spread[$g]='$s_spread[$g]' (alt) found on line x+6\n"); }
			} elsif ($odds[$x+11] =~ m/$spread_regexp_h/ ) {
				$s_spread[$g] = $3;
				$s_spread[$g] =~ s/&frac12;/\.5/;
				if ($debug > 0) { print ("\$s_spread[$g]='$s_spread[$g]' found on line x+11\n"); }
			} elsif ($odds[$x+11] =~ m/$spread_regexp_v/ ) {
				$s_spread[$g] = '-'.$1;
				$s_spread[$g] =~ s/&frac12;/\.5/;
				if ($debug > 0) { print ("\$s_spread[$g]='$s_spread[$g]' found on line x+11\n"); }
			} elsif ($odds[$x+16] =~ m/$spread_regexp_h/ ) {
				$s_spread[$g] = $3;
				$s_spread[$g] =~ s/&frac12;/\.5/;
				if ($debug > 0) { print ("\$s_spread[$g]='$s_spread[$g]' found on line x+16\n"); }
			} elsif ($odds[$x+16] =~ m/$spread_regexp_v/ ) {
				$s_spread[$g] = '-'.$1;
				$s_spread[$g] =~ s/&frac12;/\.5/;
				if ($debug > 0) { print ("\$s_spread[$g]='$s_spread[$g]' found on line x+16\n"); }
			} elsif ($odds[$x+21] =~ m/$spread_regexp_h/ ) {
				$s_spread[$g] = $3;
				$s_spread[$g] =~ s/&frac12;/\.5/;
				if ($debug > 0) { print ("\$s_spread[$g]='$s_spread[$g]' found on line x+21\n"); }
			} elsif ($odds[$x+21] =~ m/$spread_regexp_v/ ) {
				$s_spread[$g] = '-'.$1;
				$s_spread[$g] =~ s/&frac12;/\.5/;
				if ($debug > 0) { print ("\$s_spread[$g]='$s_spread[$g]' found on line x+21\n"); }
			} elsif ($odds[$x+26] =~ m/$spread_regexp_h/ ) {
				$s_spread[$g] = $3;
				$s_spread[$g] =~ s/&frac12;/\.5/;
				if ($debug > 0) { print ("\$s_spread[$g]='$s_spread[$g]' found on line x+26\n"); }
			} elsif ($odds[$x+26] =~ m/$spread_regexp_v/ ) {
				$s_spread[$g] = '-'.$1;
				$s_spread[$g] =~ s/&frac12;/\.5/;
				if ($debug > 0) { print ("\$s_spread[$g]='$s_spread[$g]' found on line x+26\n"); }
			} elsif ($odds[$x+31] =~ m/$spread_regexp_h/ ) {
				$s_spread[$g] = $3;
				$s_spread[$g] =~ s/&frac12;/\.5/;
				if ($debug > 0) { print ("\$s_spread[$g]='$s_spread[$g]' found on line x+31\n"); }
			} elsif ($odds[$x+31] =~ m/$spread_regexp_v/ ) {
				$s_spread[$g] = '-'.$1;
				$s_spread[$g] =~ s/&frac12;/\.5/;
				if ($debug > 0) { print ("\$s_spread[$g]='$s_spread[$g]' found on line x+31\n"); }
			} elsif ($odds[$x+36] =~ m/$spread_regexp_h/ ) {
				$s_spread[$g] = $3;
				$s_spread[$g] =~ s/&frac12;/\.5/;
				if ($debug > 0) { print ("\$s_spread[$g]='$s_spread[$g]' found on line x+36\n"); }
			} elsif ($odds[$x+36] =~ m/$spread_regexp_v/ ) {
				$s_spread[$g] = '-'.$1;
				$s_spread[$g] =~ s/&frac12;/\.5/;
				if ($debug > 0) { print ("\$s_spread[$g]='$s_spread[$g]' found on line x+36\n"); }
			} elsif ($odds[$x+41] =~ m/$spread_regexp_h/ ) {
				$s_spread[$g] = $3;
				$s_spread[$g] =~ s/&frac12;/\.5/;
				if ($debug > 0) { print ("\$s_spread[$g]='$s_spread[$g]' found on line x+41\n"); }
			} elsif ($odds[$x+41] =~ m/$spread_regexp_v/ ) {
				$s_spread[$g] = '-'.$1;
				$s_spread[$g] =~ s/&frac12;/\.5/;
				if ($debug > 0) { print ("\$s_spread[$g]='$s_spread[$g]' found on line x+41\n"); }
			} elsif ($odds[$x+46] =~ m/$spread_regexp_h/ ) {
				$s_spread[$g] = $3;
				$s_spread[$g] =~ s/&frac12;/\.5/;
				if ($debug > 0) { print ("\$s_spread[$g]='$s_spread[$g]' found on line x+46\n"); }
			} elsif ($odds[$x+46] =~ m/$spread_regexp_v/ ) {
				$s_spread[$g] = '-'.$1;
				$s_spread[$g] =~ s/&frac12;/\.5/;
				if ($debug > 0) { print ("\$s_spread[$g]='$s_spread[$g]' found on line x+46\n"); }
			} else {
				print ("Bad mojo.  Didn't find any spreads for game #$g.\n");					
				$msgbody .= "Bad mojo.  Didn't find any spreads for game #$g.<br>\n";					
			}
			# If everything has gone according to plan, Now I have a Home, Visitor, and spread.
			$g++;                #increment game number;
		}
	} elsif ($odds[$x] =~ m/$start/ ) {
		print ("!!!!!!!!!!!!!!!!!!!!!!!!!!! Found the Start !!!!!!!!!!!!!!!!!!!!!!!!!!!\n\n");
		$ready=1;
	}
}

#for ($g=1; $g < $#s_spread; $g++) {
	print ("$dispteamname{$s_visitor[$g]} @ $dispteamname{$s_home[$g]}, $s_spread[$g]\n");
#}

undef %home;
undef %visitor;
$sql="SELECT home,visitor,game,neutral FROM nuke_pool_games";
$sql.=" WHERE (league='NCAA' AND season = '$seasonID' AND week = '$week')";
$sql.=" AND home_spread IS NULL";
if ($debug > 1) { print ("\"$sql\"\n"); }
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$home{$fields[2]} = $dispteamname{$fields[0]};
	$visitor{$fields[2]} = $dispteamname{$fields[1]};
	$neutral{$fields[2]} = $fields[3];
	if ( $neutral{$fields[2]} > 0 ) { $site = 'vs.'; } else { $site = '@'; }
	print("'$dispteamname{$fields[1]}' $site '$dispteamname{$fields[0]}', game #$fields[2] needs a spread.\n");
}

$updates=0;
foreach $game (keys %home) {	
	$hometeam=$dispteamname_id{$home{$game}};
	$visitingteam=$dispteamname_id{$visitor{$game}};
	$location=$neutral{$game};
	# Now match up against what we pulled from the spreads...  At least one should match!
	if ($debug > 1) { print ("\$hometeam=\"$hometeam\", \$visitingteam=\"$visitingteam\", \$location=\"$neutral{$game}\""); }
	for ($x=0; $x <= $#s_spread; $x++) {
		if (($s_home[$x] eq $hometeam) && ($s_visitor[$x] eq $visitingteam)) {
			# Match !!
			if (!($s_spread[$x] == 0)) {
				print ("($s_home[$x] == $hometeam) && ($s_visitor[$x] == $visitingteam)\n");
				$home_spread = $s_spread[$x];
				$sql = "UPDATE nuke_pool_games SET home_spread =  '$home_spread'";
				$sql .= " WHERE league = 'NCAA' AND season = '$seasonID' AND week = '$week' AND game = '$game'";
				$sql .= " AND `home` = '$hometeam' AND `visitor` = '$visitingteam'";
				$sql .= " AND `home_spread` is NULL LIMIT 1";
				print ("\"$sql\"\n");
				print ("Updating game # $game, $dispteamname{$visitingteam} ($visitingteam) @ $dispteamname{$hometeam} ($hometeam) - ($home_spread)\n");
				$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
				if (!($debug > 1)) { 
					$rows = $dbh->do( $sql );
					print "database updates: $rows.\n";
					if ($rows != '0E0' ) { 
						$updates += $rows; 
						$msgbody .= "Updating game # $game, $dispteamname{$visitingteam} ($visitingteam) @ $dispteamname{$hometeam} ($hometeam) - ($home_spread)<br>\n";
						$msgbody .= "<i>$sql</i><br><br>\n";
					}
				} else {
					print "NOT updating because of debug level $debug.\n";
				}
			} else {
				$msgbody .= "Game # $game, $dispteamname{$visitingteam} ($visitingteam) @ $dispteamname{$hometeam} ($hometeam), spread = 0!<br>\n";
				print ("Game # $game, $dispteamname{$visitingteam} ($visitingteam) @ $dispteamname{$hometeam} ($hometeam), spread = 0!<br>\n");
			}
		} elsif (($s_home[$x] eq $visitingteam ) && ($s_visitor[$x] eq $hometeam) && (!( $location == 0))) {
			# Match !!
			if (!($s_spread[$x] == 0)) {
				print ("($s_home[$x] == $visitingteam) && ($s_visitor[$x] == $hometeam) && ($location > 0)\n");
				# Reverse the spread !
				if ($debug > 0) { print ( "Reversed, because of Neutral site..."); }
				$home_spread = 0-$s_spread[$x];
				$sql = "UPDATE nuke_pool_games SET home_spread =  '$home_spread'";
				$sql .= " WHERE league = 'NCAA' AND season = '$seasonID' AND week = '$week' AND game = '$game'";
				$sql .= " AND `home` = '$hometeam' AND `visitor` = '$visitingteam'";
				$sql .= " AND `home_spread` is NULL LIMIT 1";
				print ("\"$sql\"\n");
				print ("Updating game # $game, $dispteamname{$visitingteam} ($visitingteam) @ $dispteamname{$hometeam} ($hometeam) - ($home_spread)\n");
				$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
				if (!($debug > 1)) { 
					$rows = $dbh->do( $sql );
					print "database updates: $rows.\n";
					if ($rows != '0E0' ) { 
						$updates += $rows; 
						$msgbody .= "Updating game # $game, $dispteamname{$visitingteam} ($visitingteam) @ $dispteamname{$hometeam} ($hometeam) - ($home_spread)<br>\n";
						$msgbody .= "<i>$sql</i><br><br>\n";
					}
				} else {
					print "NOT updating because of debug level $debug.\n";
				}
			} else {
				$msgbody .= "Game # $game, $dispteamname{$visitingteam} ($visitingteam) @ $dispteamname{$hometeam} ($hometeam), spread = 0!<br>\n";
				print ("Game # $game, $dispteamname{$visitingteam} ($visitingteam) @ $dispteamname{$hometeam} ($hometeam), spread = 0!<br>\n");
			}
		}
	}
}

if ($debug > 0 || $updates > 0) {
	$verb='update';
	if ($updates == 0 || $updates > 1 ) { $verb = 'updates'; } 
	if ($updates == 0 ) { $updates = 'No'; $msgbody .= "<br><b>No updates made.</b><br>\n"; } 
	open(SENDMAIL, "|/usr/sbin/sendmail -t") or die "Sendmail Offline: $!\n";
	print SENDMAIL "From: $email_from\n";
	print SENDMAIL "To: $email_to\n" or die "E-Mail wrong.\n";
	print SENDMAIL "Content-Type: multipart/alternative;\n";
	print SENDMAIL "        boundary=\"BEGIN_HTML\"\n";
	print SENDMAIL "X-Priority: 3\n";
	print SENDMAIL "Subject: Football Pools (NCAA) - $updates spreads $verb made\n\n";
	print SENDMAIL "X-MSMail-Priority: High\n\n\n";
	print SENDMAIL "--BEGIN_HTML\n";
	print SENDMAIL "Content-Type: text/html; charset=utf-8\n";
	print SENDMAIL "Content-Transfer-Encoding: 8bit\n\n\n";
	print SENDMAIL ("$msgbody\n");
	print SENDMAIL "<br>$updates updates made.\n\n";
	print SENDMAIL "\n\n";
	close(SENDMAIL) or warn "sendmail didn't close nicely";
}

print ("Done!\n");

exit(0);
