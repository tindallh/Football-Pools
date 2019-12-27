#!/usr/bin/perl

use DBI;

$version=1.0;
$debug = 1;
$spacing = "[ ]+";
$scoretable = '<table width="100%" border="0" cellspacing="0" cellpadding="1">';
$scoretd = 'class="ysptblbdr2">';
$scorefinal = '<span class="yspscores">Final</span>';
$regexp_team = '<a href="/ncaaf/teams/[^"]+">([^<]+)</a>';
$regexp_score = '<span class="yspscores">[<b>]*([\d]+)[</b>]*</span>$';
$regexp_date = '<td colspan="3" height="18" class="yspdetailttl">&nbsp;';
$email_to = "henry\@football-pools.org";
$email_from="Football-Pools";
my $dbname='footban4_nuke-pool';
my $dbuser='footban4_poolweb';
my $dbpass='F00tba11!';

my $dbh = DBI->connect("dbi:mysql:$dbname", $dbuser, $dbpass)|| die "Cannot connect to db server $DBI::errstr,\n";
$sql="SELECT team_id,team_name FROM nuke_pool_teams WHERE league='NCAA'";
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$teamname{$fields[0]} = $fields[1];
}
$sql="SELECT team_name,team_id FROM nuke_pool_teams_rev WHERE league='NCAA'";
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$teamid{$fields[0]} = $fields[1];
}

$scoreurl = "http://rivals.yahoo.com/ncaa/football/scoreboard";
print ("\$scoreurl=\"$scoreurl\"\n");
@sscores = `/usr/bin/lynx -lss=~/lynx.lss --source $scoreurl`;
$x=0;
for ($y=0; $y < $#sscores+1; $y++) {
	if (!($sscores[$y] =~ /^[\s]+$/)) {
		$scores[$x]=$sscores[$y];
		chomp $scores[$x];
		#	print ("\$x='$x',\$scores[$x]='$scores[$x]'\n"); 
		$x++;
	}
}

for ($d=0;$d < 4; $d++ ) {
	$go = $stop = 0;
	&DoDate($d);
	print("**** $dbdate *****\n");
	for ($x=0; $x < $#scores+1; $x++) {
		if ($debug > 1) { print ("$x: -->$scores[$x]\n"); }                                       # for testing
		$hscore=$vscore=0;
		if ($go == 1 && $stop == 0) {
			# This won't be found unless there's more than one date on the page, 
			# and it won't be found before the first date 'cuz we're not here
			# until the date is found.
		  if ($scores[$x] =~ /$regexp_date/) { 
		  	$stop=1; 
		  # Standard 4-quarter game
		  } elsif ($scores[$x] =~ /^$scoretable/ && $scores[$x+2] =~ /$scoretd/ && $scores[$x+41] =~ /$scorefinal/ ) {
				if ($scores[$x+32] =~ /$regexp_team/) {
					$v_name=$1;
					if ($debug > 1) { print ("\$v_name='$v_name'\n"); }                             ## testing
				}
				if ($scores[$x+39] =~ /$regexp_score/) {
					$score{$v_name}=$1;
					if ($debug > 1) { print ("\$score{$v_name}='$score{$v_name}'\n");  }                          ## testing
				}
				if ($scores[$x+51] =~ /$regexp_team/) {
					$h_name=$1;
					if ($debug > 1) { print ("\$h_name='$h_name'\n"); }                                  ## testing
				}
				if ($scores[$x+58] =~ /$regexp_score/) {
					$score{$h_name}=$1;
					if ($debug > 1) { print ("\$score{$h_name}='$score{$h_name}'\n"); }                           ## testing
				}
				print ("Got a Final !  home: '$h_name' $score{$h_name} -- visitor: '$v_name' $score{$v_name}\n");
				$id_score{$teamid{$h_name}} = $score{$h_name};
				$id_score{$teamid{$v_name}} = $score{$v_name};
			# Single overtime
		  } elsif ($scores[$x] =~ /^$scoretable/ && $scores[$x+2] =~ /$scoretd/ && $scores[$x+43] =~ /$scorefinal/ ) {
				if ($scores[$x+34] =~ /$regexp_team/) {
					$v_name=$1;
					if ($debug > 1) { print ("\$v_name='$v_name'\n"); }                           ## testing
				}
				if ($scores[$x+42] =~ /$regexp_score/) {
					$score{$v_name}=$1;
					if ($debug > 1) {  print ("\$score{$v_name}='$score{$v_name}'\n"); }                            ## testing
				}
				if ($scores[$x+54] =~ /$regexp_team/) {
					$h_name=$1;
					if ($debug > 1) { print ("\$h_name='$h_name'\n"); }                           ## testing
				}
				if ($scores[$x+62] =~ /$regexp_score/) {
					$score{$h_name}=$1;
					if ($debug > 1) {  print ("\$score{$h_name}='$score{$h_name}'\n"); }                           ## testing
				}
				print ("Got a Final !  home: '$h_name' $score{$h_name} -- visitor: '$v_name' $score{$v_name}\n");
				$id_score{$teamid{$h_name}} = $score{$h_name};
				$id_score{$teamid{$v_name}} = $score{$v_name};
			# Double overtime
		  } elsif ($scores[$x] =~ /^$scoretable/ && $scores[$x+2] =~ /$scoretd/ && $scores[$x+47] =~ /$scorefinal/ ) {
				if ($scores[$x+36] =~ /$regexp_team/) {
					$v_name=$1;
					if ($debug > 1) {  print ("\$v_name='$v_name'\n"); }                           ## testing
				}
				if ($scores[$x+45] =~ /$regexp_score/) {
					$score{$v_name}=$1;
					if ($debug > 1) { print ("\$score{$v_name}='$score{$v_name}'\n"); }                           ## testing
				}
				if ($scores[$x+57] =~ /$regexp_team/) {
					$h_name=$1;
					if ($debug > 1) { print ("\$h_name='$h_name'\n"); }                            ## testing
				}
				if ($scores[$x+66] =~ /$regexp_score/) {
					$score{$h_name}=$1;
					if ($debug > 1) { print ("\$score{$h_name}='$score{$h_name}'\n"); }                           ## testing
				}
				print ("Got a Final !  home: '$h_name' $score{$h_name} -- visitor: '$v_name' $score{$v_name}\n");
				$id_score{$teamid{$h_name}} = $score{$h_name};
				$id_score{$teamid{$v_name}} = $score{$v_name};
			}
		} else {
			# Look for the beginning of the scores for this date
			if ($scores[$x] =~ $big_date) {
				$go=1;
				$x++;
				print ("Found $big_date on line $x !!! \n");
			}
		}
	}
	undef %home;
	undef %visitor;
	$sql="SELECT home,visitor,game,home_score,visitor_score FROM nuke_pool_games";
	$sql.=" WHERE (league='NCAA' AND date = '$dbdate')";
	$sql.=" AND (home_score IS NULL && visitor_score IS NULL)";
	$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
	if ($debug > 0) {  print ("\$sql='$sql'\n"); }
	$cursor->execute;
	while(@fields = $cursor->fetchrow) {
		$home{$fields[2]} = $teamname{$fields[0]};
		$visitor{$fields[2]} = $teamname{$fields[1]};
		print("'$teamname{$fields[1]}' @ '$teamname{$fields[0]}', game #$fields[2] needs a score.\n");
	}
	foreach $game (keys %home) {
		$hometeam=$home{$game};
		$visitingteam=$visitor{$game};
		if ($debug > 0) { print ("\$id_score{$teamid{$hometeam}}='$id_score{$teamid{$hometeam}}' \$id_score{$teamid{$visitingteam}}='$id_score{$teamid{$visitingteam}}'\n"); }
		if ($id_score{$teamid{$hometeam}} || $id_score{$teamid{$visitingteam}}) { 
			$home_score = $id_score{$teamid{$hometeam}};
			$visitor_score = $id_score{$teamid{$visitingteam}};
			open(SENDMAIL, "|/usr/sbin/sendmail -t") or die "Sendmail Offline: $!\n";
			print SENDMAIL "From: $email_from\n";
			print SENDMAIL "To: $email_to\n" or die "E-Mail wrong.\n";
			print SENDMAIL "Content-Type: multipart/alternative;\n";
			print SENDMAIL "        boundary=\"BEGIN_HTML\"\n";
			print SENDMAIL "X-Priority: 3\n";
			print SENDMAIL "Subject: Football Pools (NCAA) - Final: $hometeam $home_score, $visitingteam $visitor_score\n\n";
			print SENDMAIL "X-MSMail-Priority: High\n\n\n";
			print SENDMAIL "--BEGIN_HTML\n";
			print SENDMAIL "Content-Type: text/html;\n";
			print SENDMAIL "Content-Transfer-Encoding: quoted-printable\n\n\n";
			print SENDMAIL "<!-- created by $0 version $version -->\n";
			print ("Updating game # $game, $hometeam $home_score -- $visitingteam $visitor_score\n");
			print SENDMAIL ("Updating game # $game, $hometeam $home_score -- $visitingteam $visitor_score<br>\n");
			$sql = "UPDATE nuke_pool_games SET home_score =  '$home_score', visitor_score =  '$visitor_score'";
			$sql .= " WHERE league =  'NCAA' and date =  '$dbdate' and game =  '$game' LIMIT 1";
			print ("\"$sql\"\n");
			$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
			print ("Updating database....\n");
			$cursor->execute;
			print SENDMAIL "<br><br><i>";
			print SENDMAIL $sql;
			print SENDMAIL "</i><br>\n";
			print SENDMAIL "\n\n";
			close(SENDMAIL) or warn "sendmail didn't close nicely";
		}
	}
}

print ("Done!\n");
exit(0);

sub DoDate($d) {
	# $now=time();
	@pad=("00","01","02","03","04","05","06","07","08","09");
	@dow=("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
	@moy=("January","February","March","April","May","June","July","August","September","October","November","December");
	$t=time();
	$start = $t-(86400*$d);
	($j1,$j1,$j1,$day,$mon,$year,$wday,$j1,$j1) = localtime($start);
	$year += 1900;
	$big_date = $dow[$wday]." ".$moy[$mon]." ".$day.", ".$year;
	print ("$big_date\n");                           ## testing
	$mon +=1;
	if ($mon <= 9) {$mon=$pad[$mon];}
	if ($day <= 9) {$day=$pad[$day];}
	$adate[1]=$mon.$day.$year;
	$bdate[1]=$year."-".$mon."-".$day;
	($j1,$j1,$j1,$yday,$ymon,$yyear,$j1)=localtime($start);
	$ymon = $mon+1;
	if ($mon <= 9) {$mon=$pad[$mon];}
	if ($day <= 9) {$day=$pad[$day];}
	$dbdate=$year."-".$mon."-".$day;
  if ($debug > 1) { print ("\$big_date='$big_date', now=\"$now\", yesterday=\"$yesterday\", dbdate=\"$dbdate\"\n"); }
	return $dbdate, $big_date;
}
