#!/usr/bin/perl
############################################################################################
#   StartTime checker
#   Version 2016.1.0 - 02 Sep 2016 - ported from TVcheck-ncaa.pl
############################################################################################

use DBI;

$version='2016.1.1';
$debug = 0;
$dbupdates = 0;
$spacing = "[ ]+";
@padded=("00","01","02","03","04","05","06","07","08","09");
$email_to = "henry\@football-pools.org";
$email_from="NCAA Start Time checker <henry\@football-pools.org>";
my $dbname='footban4_nuke-pool';
my $dbuser='footban4_poolweb';
#my $dbname='nuke-pool';
#my $dbuser='poolweb';
my $dbpass='F00tba11!';

my $dbh = DBI->connect("dbi:mysql:$dbname", $dbuser, $dbpass)|| die "Cannot connect to db server $DBI::errstr,\n";

$sql="SELECT team_id,team_name FROM nuke_pool_teams WHERE league='NCAA' AND team_id < '900'";
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$teamname{$fields[0]} = $fields[1];
}

$sql="SELECT team_id,team_name FROM nuke_pool_teams_rev WHERE league='NCAA'";
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$teamid{$fields[1]} = $fields[0];
}

$tvurl = "http://www.lsufootball.net/tvschedule.htm";
print ("\$tvurl=\"$tvurl\"\n");
$rcmd = "/usr/bin/lynx -lss=~/lynx.lss --source $tvurl ";
$rcmd .= ' | sed \':a;N;$!ba;s/>\n[ ]*<td/><td/g\' | sed \':a;N;$!ba;s/>\n<\/tr/><\/tr/g\'';
$rcmd .= ' | sed \':a;N;$!ba;s/\>\n[ ]+<center/\><center/g;:a;N;$!ba;s/\>\n[ ]*<\//\><\//g\'';
$rcmd .= ' | sed -re \'s/<a href=\"http:\/\/btn.com\/gamefinder\/\" Title=\"GameFinder\">//g\'';
$rcmd .= ' | sed -re \'s/ <td><a href=\"http:\/\/.*\">(.*)<\/td>/\1/g\'';
$rcmd .= ' | sed -re \'s/\(ESPN-GP/gameplan/g;s/ESPN2\)/ESPN2/g;s/\(ABC \/ ESPN/ABC-ESPN/g;s/ \(alternate channel\)//;s/ESPN News/ESPNNews/g\'';
$rcmd .= ' | sed -re \'s/<\/a>//g;s/ \(HD\)//g;s/ESPN-GP/gameplan/g\'';

if ($debug > 0) { print ("\$rcmd=\"$rcmd\"\n"); }
@gametimes = `$rcmd`;
for ($y=0; $y < $#gametimes+1; $y++) {
	chomp $gametimes[$y];
	if ($debug > 1) { print ("\$gametimes[$y]=\"$gametimes[$y]\"\n"); }
	# Do the Date thing !!!
	# Curveball for 2017, dude added a different type of date line... see example 2. Dude also add a superfluous comma after the month, 
	# Don't be amazed if it gets removed an breaks the script.
	# example 1: <center><b>Sunday, August, 27th</b></center>
	# example 2: <p align="right"><b>Saturday, August, 26th&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </b>&nbsp;(<b><a href="tvschedule-directv.htm">Printable schedule with DirecTV channels</a></b>)</td>
	if ($gametimes[$y] =~ /<p align="right"><b>[\w]+day, ([^ ]+), ([\d]+)[\w]{2}&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <\/b>&nbsp;/ ) {
		$gdate=`date +%Y-%m-%d -d "$1 $2"`;
		chomp $gdate;
		if ($debug > 1) { print ("Found a date!  $1 $2; \$gdate=\"$gdate\"\n"); }
        }
	if ($gametimes[$y] =~ /<center><b>[\w]+day, ([^ ]+), ([\d]+)[\w]{2}<\/b><\/center>/ ) {
		$gdate=`date +%Y-%m-%d -d "$1 $2"`;
		chomp $gdate;
		if ($debug > 1) { print ("Found a date!  $1 $2; \$gdate=\"$gdate\"\n"); }
	}
	if ($gametimes[$y] =~ /td>([A-Za-z& ]{3,}) [at|vs.]+ ([A-Za-z& ]{3,})<\/td><td>([\d]+:[\d]+.* [ap])m<\/td><td>[ ]*([a-zA-Z0-9-]+)[ ]*/ ) {
		$v_name = $1;
		$h_name = $2;
		$gtime = $3;
		if ($gtime =~ /p/) {
			$gtime =~ /([0-9]{1,2}):([0-9]{2})/;
			$h = $1;
			$m = $2;
			if ($h < 12) { $h += 12; }
			$gametime = $h.$m;
			if ($debug > 1) { print ("+++ PM: "); }
		} else {
			$gtime =~ m/([0-9]{1,2}):([0-9]{2})/;
			$h=$1;
			$m=$2;
			if ($h < 10) { $h = @padded[$h]; }
			$gametime = $h.$m;
			if ($debug > 1) { print ("+++ AM: "); }
		}
		if ($debug > 0) { print ("\$v_name=\"$v_name\", \$h_name=\"$h_name\", \$gametime=\"$gametime\"\n"); }
		$h_id=$teamid{$h_name};
		$v_id=$teamid{$v_name};
		$newstart{$v_id}{$h_id}=$gametime;
		if ($h_id && $v_id) {
			# Make a SQL statement to update the game based on date, home team, visiting team, and ONLY if the current time is "0800" !!
			$sql = "UPDATE nuke_pool_games set `time` = '$gametime'";
			$sql .= " WHERE `league` = 'NCAA' AND ( `date` = '$gdate' AND `time` = '0800' )";
			$sql .= " AND ( `home` = '$h_id' AND `visitor` = '$v_id' ) LIMIT 1";
			$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
			if ($debug > 1) {  print ("\$sql='$sql'\n"); }
			$rows = $dbh->do( $sql );
			print "database updates: $rows.\n";
			if ($rows != '0E0' ) { 
				$updates += $rows; 
				$msgbody .= "$gdate, $v_name at $h_name, starts at $gametime.<br>\n";
				# $msgbody .= "database updates: $rows.<br>\n";
			}
		}
	} else {
		# didn't match
		if ($debug > 2 ) { print ("---> didn't match: \"$broadcast[$y]\"\n"); } 
	}
}

if ($debug > 0 || $updates > 0) {
	$verb='update';
	if ($updates > 1 || $updates == 0 ) { $verb = 'updates'; }
	if ($updates == 0 ) { $updates = 'No'; }
	open(SENDMAIL, "|/usr/sbin/sendmail -t") or die "Sendmail Offline: $!\n";
	print SENDMAIL "From: $email_from\n";
	print SENDMAIL "To: $email_to\n" or die "E-Mail wrong.\n";
	print SENDMAIL "Content-Type: multipart/alternative;\n";
	print SENDMAIL "        boundary=\"BEGIN_HTML\"\n";
	print SENDMAIL "X-Priority: 5\n";
	print SENDMAIL "Subject: Football Pools (NCAA) - Start Times: $updates $verb\n\n";
	print SENDMAIL "X-MSMail-Priority: Low\n\n\n";
	print SENDMAIL "--BEGIN_HTML\n";
	print SENDMAIL "Content-Type: text/html;\n";
	print SENDMAIL "Content-Transfer-Encoding: quoted-printable\n\n\n";
	print SENDMAIL "<!-- created by $0 version $version -->\n";
	print SENDMAIL "<br>";
	print SENDMAIL "$msgbody\n";
	print SENDMAIL "<br>\n";
	print SENDMAIL "\n\n";
	close(SENDMAIL) or warn "sendmail didn't close nicely";
}

print ("Done!\n");
exit(0);
