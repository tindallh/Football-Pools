#!/usr/bin/perl

#############################################################################
# scorecheck-ncaa.pl                                                        #
#    written by:  Henry B. Tindall, Jr. for football-pools.org              #
# original release:  4 Aug 2005                                             #
# version 2018.1.0 - 27 Aug 2018 - NCAA scoreboard changed up a LOT         #
#         2016.1.0 - 06 Sep 2016 - Added number of updates to subject line. #
#         1.0 - 04 Aug 2008 - original release                              #
#############################################################################

use DBI;

$version='2018.1.0';
$debug = 1;
$spacing = "[ ]+";
$email_to = "henry\@football-pools.org";
$email_from="NCAA Score Checker <henry\@football-pools.org>";
my $dbname='footban4_nuke-pool';
my $dbuser='footban4_poolweb';
my $dbpass='F00tba11!';
$scoreurl = "https://www.ncaa.com/scoreboard/football/fbs/";

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
#$urand=int(rand(1000000));
#$scoreurl=$scoreurl."/&rand=".$urand;
print ("\$scoreurl=\"$scoreurl\"\n");
$curltime=localtime();
#$rcmd="curl -z \"$curltime\" -f -k -o- $scoreurl | grep -vE \"^\s*\$\"";
#$rcmd="curl -k -o- $scoreurl | grep -vE \"^\s*\$\"";
$rcmd="wget --no-check-certificate -O- $scoreurl | grep -vE \"^\s*\$\"";


if ($debug > 0) { print ("\$rcmd=\"$rcmd\"\n"); }
@sscores = `$rcmd`;
for ($y=0; $y < $#sscores+1; $y++) {
	chomp $sscores[$y];
	if ($debug > 1) { print ("\$sscores[$y]=\"$sscores[$y]\"\n"); }
	# New for 2018:
	# href="/game/football/fbs/2018/08/25/duquesne-massachusetts">
	# should make finding games easier.
	if ($sscores[$y] =~ /gamePod gamePod-type-game status-final/ ) {
		# Matching to make sure it's a valid game.
		if ($sscores[$y+3] =~ /href="\/game\/football\/fbs\/([0-9]{4}\/[0-9]{2}\/[0-9]{2})\/[^-]+-[^"]+">/) {
			$g2date = $1;
			$g2date =~ s/\//-/g;
			if ($debug > 1) { print ("\$g2date=\"$g2date\"\n"); }
			if ($debug > 1) { print ("\$sscores[$y]=\"$sscores[$y]\"\n"); }
			if ($sscores[$y+17] =~ /span class="gamePod-game-team-name">([^<]+)<\/span/ ) { # visiting team
				$v_name=$1;
				if ($debug > 1) { print ("\$v_name=\"$v_name\"\n"); }
			}
			if ($sscores[$y+18] =~ /span class="gamePod-game-team-score">([^<]+)<\/span/ ) { # visiting score
				$score{$v_name}=$1;
				if ($debug > 1) { print ("\$score{$v_name}=\"$score{$v_name}\"\n"); }
			}
			if ($sscores[$y+25] =~ /span class="gamePod-game-team-name">([^<]+)<\/span/ ) { # home team
				$h_name=$1;
				if ($debug > 1) { print ("\$h_name=\"$h_name\"\n"); }
			}
			if ($sscores[$y+26] =~ /span class="gamePod-game-team-score">([^<]+)<\/span/ ) { # home score
				$score{$h_name}=$1;
				if ($debug > 1) { print ("\$score{$h_name}=\"$score{$h_name}\"\n"); }
			}
			$id_score{$g2date}{$teamid{$h_name}} = $score{$h_name};
			$id_score{$g2date}{$teamid{$v_name}} = $score{$v_name};
			if ($debug > 0) { 
				print ("\$v_name=\"$v_name\", \$score{$v_name}=\"$score{$v_name}\", "); 
				print ("\$h_name=\"$h_name\", \$score{$h_name}=\"$score{$h_name}\"\n"); 
				print ("\$id_score{$g2date}{$teamid{$v_name}}=\"$id_score{$g2date}{$teamid{$v_name}}\"\n"); 
				print ("\$id_score{$g2date}{$teamid{$h_name}}=\"$id_score{$g2date}{$teamid{$h_name}}\"\n"); 
			}
		} else {
			if ($debug > 1) { print ("=-=-=-=> No match !!!\n"); }
		}
	}
}

for ($d=0;$d < 4; $d++ ) {
	&DoDate($d);
	print("**** $dbdate *****\n");
	undef %home;
	undef %visitor;
	$sql="SELECT home,visitor,game,home_score,visitor_score FROM nuke_pool_games";
	$sql.=" WHERE (league='NCAA' AND date = '$dbdate')";
	$sql.=" AND (home_score IS NULL && visitor_score IS NULL)";
	$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
	if ($debug > 1) {  print ("\$sql='$sql'\n"); }
	$cursor->execute;
	while(@fields = $cursor->fetchrow) {
		$home{$fields[2]} = $teamname{$fields[0]};
		$visitor{$fields[2]} = $teamname{$fields[1]};
		print("'$teamname{$fields[1]}' @ '$teamname{$fields[0]}', game #$fields[2] needs a score.\n");
	}
	foreach $game (keys %home) {
		$hometeam=$home{$game};
		$visitingteam=$visitor{$game};
		if ($debug > 0) { print ("\$id_score{$hometeam}='$id_score{$hometeam}' \$id_score{$visitingteam}='$id_score{$visitingteam}'\n"); }
		#if (($id_score{$teamid{$hometeam}} || ($id_score{$teamid{$hometeam}} == 0)) && ($id_score{$teamid{$visitingteam}} || ($id_score{$teamid{$visitingteam}} == 0))) { 
		if ($id_score{$dbdate}{$teamid{$hometeam}} && $id_score{$dbdate}{$teamid{$visitingteam}}) {
			$home_score = $id_score{$dbdate}{$teamid{$hometeam}};
			$visitor_score = $id_score{$dbdate}{$teamid{$visitingteam}};
			$sql = "UPDATE nuke_pool_games SET home_score =  '$home_score', visitor_score =  '$visitor_score'";
			$sql .= " WHERE league =  'NCAA' and date =  '$dbdate' and game =  '$game' AND `home_score` IS NULL AND `visitor_score` IS NULL LIMIT 1";
			print ("Updating game # $game, $hometeam $home_score -- $visitingteam $visitor_score\n");
			print ("\"$sql\"\n");
			$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
			$rows = $dbh->do( $sql );
			print "database updates: $rows.\n";
			if ($rows != '0E0' ) { 
				$updates += $rows; 
				$msgbody .= "Updating game # $game, $hometeam $home_score -- $visitingteam $visitor_score<br>\n";
				$msgbody .= "\t<br><i>$sql</i><br><br>\n";
			}
		}
	}
}
if ($debug <2 && $updates > 0 ) {
	if ( $updates < 1) {
		$msgbody .= "<b>No games updated!</b><br>\n";
	}
	open(SENDMAIL, "|/usr/sbin/sendmail -t") or die "Sendmail Offline: $!\n";
	print SENDMAIL "From: $email_from\n";
	print SENDMAIL "To: $email_to\n" or die "E-Mail wrong.\n";
	print SENDMAIL "Content-Type: multipart/alternative;\n";
	print SENDMAIL "        boundary=\"BEGIN_HTML\"\n";
	print SENDMAIL "X-Priority: 5\n";
	$verb='update';
	if ($updates > 1 || $updates == 0 ) { $verb = 'updates'; } 
	if ($updates == 0 ) { $updates = 'No'; }
	print SENDMAIL "Subject: Football Pools (NCAA) - $updates Final Score $verb\n\n";
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
