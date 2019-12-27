#!/usr/bin/perl
############################################################################################
#   TV time/broadcast network checker
#   Version 2019.1.0 - 30 Aug 2019 - minor regexp changes.
#           2018.1.0 - 08 Aug 2019 - changed date matching stuff 
#           2016.1.0 - 17 Sep 2016 - moved mail stuff to the bottom to add more info to subject. 
#           1.4 - 02 Sep 2016 - beginning of season checks/fixes
#           1.3 - 26 Oct 2014 - changed the regular esxpressions to match pm vice p.m.
#           1.2 - 01 Aug 2014 - Added some sed to pull more networks.
############################################################################################

use DBI;

$version='2019.1.0';
$daysahead=14;
$spacing = "[ ]+";
@padded=("00","01","02","03","04","05","06","07","08","09");
$email_to = "henry\@football-pools.org";
$email_from="NCAA TV schedule checker <henry\@football-pools.org>";
my $dbname='footban4_nuke-pool';
my $dbuser='footban4_poolweb';
#my $dbname='nuke-pool';
#my $dbuser='poolweb';
my $dbpass='F00tba11!';
$debug = 1;

my $dbh = DBI->connect("dbi:mysql:$dbname", $dbuser, $dbpass)|| die "Cannot connect to db server $DBI::errstr,\n";

$sql="SELECT team_id,team_name FROM nuke_pool_teams WHERE league='NCAA'";
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

$sql="SELECT id,name FROM nuke_pool_tvnetworks";
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$network_name{$fields[0]} = $fields[1];
}

$sql="SELECT id,name FROM nuke_pool_tvnetworks_rev";
$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
$cursor->execute;
while(@fields = $cursor->fetchrow) {
	$network_id{$fields[1]} = $fields[0];
	if ($debug > 1) {  print "\$network_id{$fields[1]} = \"$fields[0]\"\n"; }
}

for ($d=0;$d <= $daysahead; $d++ ) {
	&DoDate($d);
	print("***** $dbdate *****\n");
	undef %home;
	undef %visitor;
	$sql="SELECT home,visitor,game,time FROM nuke_pool_games";
	$sql.=" WHERE (league='NCAA' AND date = '$dbdate')";
	$sql.=" AND tvnetwork IS NULL";      # We don't care about the second network, it should be blank if the first one is.
	$cursor =$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
	if ($debug > 1) {  print ("\$sql='$sql'\n"); }
	$cursor->execute;
	while(@fields = $cursor->fetchrow) {
		$gid{$fields[0]} = $fields[2];
		$gid{$fields[1]} = $fields[2];
		$hometeam{$fields[2]} = $teamname{$fields[0]};
		$visitorteam{$fields[2]} = $teamname{$fields[1]};
		$stime{$fields[2]} = $fields[3];
		$sdate{$fields[2]} = $dbdate;
		if ($debug > 0) { print ("++ \$gid{$fields[0]}='$gid{$fields[0]}', \$gid{$fields[1]}='$gid{$fields[1]}', \$hometeam{$fields[2]}='$hometeam{$fields[2]}', \$visitorteam{$fields[2]}='$visitorteam{$fields[2]}'\n"); }
		print("'$teamname{$fields[1]}' against '$teamname{$fields[0]}', game #$fields[2] at $fields[3] needs a Network!\n");
	}
}

$tvurl = "http://www.lsufootball.net/tvschedule.htm";
print ("\$tvurl=\"$tvurl\"\n");
$rcmd = "/usr/bin/lynx -lss=~/lynx.lss --source $tvurl ";
$rcmd .= ' | sed \':a;N;$!ba;s/>\n[ ]*<td/><td/g\' | sed \':a;N;$!ba;s/>\n<\/tr/><\/tr/g\'';
$rcmd .= ' | sed \':a;N;$!ba;s/\>\n[ ]+<center/\><center/g;:a;N;$!ba;s/\>\n[ ]*<\//\><\//g\'';
$rcmd .= ' | sed -re \'s/<a href=\"http:\/\/btn.com\/gamefinder\/\" Title=\"GameFinder\">//g\'';
$rcmd .= ' | sed -re \'s/<a href=\"http:\/\/btn.com\/gamefinder\/">//\'';
$rcmd .= ' | sed -re \'s/<a href=\"http:\/\/espn.go.com\/watchespn\/index\/_\/sport\/football\/\">//g\'';
$rcmd .= ' | sed -re \'s/<a href=\"http:\/\/www.btn2go.com\/btn2go\/btnlive.jsp\">BTN2GO Video/BTN2GO/g\'';
$rcmd .= ' | sed -re \'s/<a href=\"http:\/\/www.btn2go.com\/btn2go\/btnlive.jsp\">//g\'';
$rcmd .= ' | sed -re \'s/<a href=\"http:\/\/btn.com\/about\/btn-gamefinder\/\">//g\'';
$rcmd .= ' | sed -re \'s/<a href=\"http:\/\/watchabc.go.com\/live\">//g\'';
$rcmd .= ' | sed -re \'s/<a href=\"http:\/\/pac-12.com\/channel-finder\">//g\'';
$rcmd .= ' | sed -re \'s/<a href=\"http:\/\/support.pac-12.com\/hc\/en-us\">//g\'';
$rcmd .= ' | sed -re \'s/<a href=\"http:\/\/www.cbssports.com\/seclive\">//g\'';
$rcmd .= ' | sed -re \'s/<a href=\"http:\/\/www.theacc.com[^>]*>//g\'';
$rcmd .= ' | sed -re \'s/<a href=\"misc\/sectv.htm\">//g\'';
$rcmd .= ' | sed -re \'s/<a href=\"http:\/\/www.espn3.com\/\">//g;s/SEC TV/SEC-TV/g\'';
$rcmd .= ' | sed -re \'s/<a href=\"http:\/\/espn.go.com\/watchespn\/\?channel=espn3\">//g\'';
$rcmd .= ' | sed -re \'s/<a href=\"http:\/\/espn.go.com\/watchespn\/\">//g\'';
$rcmd .= ' | sed -re \'s/<a href=\"http:\/\/espn.go.com\/watchespn\/index\/_\/sport\/football\?channel=espn3\">//g\'';
$rcmd .= ' | sed -re \'s/<a href=\"http:\/\/espn.go.com\/watchespn\/index\?sport=football\">//g\'';
$rcmd .= ' | sed -re \'s/<a href=\"http:\/\/espn.go.com\/watchespn\/\?type=livenow&sport=football&channel=espn3\">//g\'';
$rcmd .= ' | sed -re \'s/<a href=\"misc\/fsnaffiliates.htm\">FSN Affiliates/FSN/g\'';
$rcmd .= ' | sed -re \'s/BTN2Go Video/BTN2Go/g\'';
$rcmd .= ' | sed -re \'s/Big South Video/BigSouth/g\'';
$rcmd .= ' | sed -re \'s/FSN Affiliates/FSN/g\'';
$rcmd .= ' | sed -re \'s/espn3 Video/ESPN3/g\'';
$rcmd .= ' | sed -re \'s/WatchESPN Video/WatchESPN/g\'';
$rcmd .= ' | sed -re \'s/\$espn+ Video/espnplus/g\'';
$rcmd .= ' | sed -re \'s/SEC Network alternate/SECN/g\'';
$rcmd .= ' | sed -re \'s/SEC Network/SECN/g\'';
$rcmd .= ' | sed -re \'s/SEC Video/SECV/g\'';
$rcmd .= ' | sed -re \'s/<a href=\"http:\/\/msn.foxsports.com\/foxsportsgo\/\">//g\'';
$rcmd .= ' | sed -re \'s/<a href=\"https:\/\/www.foxsportsgo.com\/\">//g\'';
$rcmd .= ' | sed -re \'s/FOX Sports GO/FOXSportsGO/g\'';
$rcmd .= ' | sed -re \'s/\(<a href=\"http:\/\/espngameplan.espn.com\/images\/maps\/[0-9]+.jpg\">//g\'';
$rcmd .= ' | sed -re \'s/\(<a href=\"http:\/\/a.espncdn.com\/photo\/20[0-9]+\/[0-9]+\/[a-zA-Z0-9_]+.jpg\">//g\'';
$rcmd .= ' | sed -re \'s/<a href=\"http:\/\/espn.go.com\/longhornnetwork\/\">//g\'';
$rcmd .= ' | sed -re \'s/<a href=\"http:\/\/espn.go.com\/watchespn\/\?channel=sec\">SECN Video/SECV/g\'';
$rcmd .= ' | sed -re \'s/\(ESPN-GP/gameplan/g;s/ESPN2\)/ESPN2/g;s/\(ABC \/ ESPN/ABC-ESPN/g;s/ \(alternate channel\)//;s/ESPN News/ESPNNews/g\'';
$rcmd .= ' | sed -re \'s/<\/a>//g;s/ \(HD\)//g;s/ESPN-GP/gameplan/g\'';
# Here's the "catch-all" to remove any extra hyperlinks...
$rcmd .= ' | sed -re \'s/<a\b[^>]+>//\'';
if ($debug > 0) { print ("\$rcmd=\"$rcmd\"\n"); }
@broadcasts = `$rcmd`;
for ($y=0; $y < $#broadcasts+1; $y++) {
	chomp $broadcasts[$y];
	if ($debug > 1) { print ("\$broadcasts[$y]=\"$broadcasts[$y]\"\n"); }
	# Do the Date thing !!!
	# samples:
	# 1 -  <p align="right"><b>Saturday, August, 26th&nbsp;
	# 2 -  <center><b>Sunday, August, 27th</b></center>
	# Nice.  Homey figured out he had an extra comma... sometimes.
	#if ($broadcasts[$y] =~ /<p align="right"><b>[\w]+day, ([^ ]+), ([\d]+)[\w]{2}&nbsp;/ ) {
	if ($broadcasts[$y] =~ /<p align="right"><b>[\w]+day, ([^ ,]+)[,]* ([\d]+)[\w]{2}&nbsp;/ ) {
		$gdate=`date +%Y-%m-%d -d "$1 $2"`;
		chomp $gdate;
		if ($debug > 0) { print ("Found a date!  $1 $2; \$gdate=\"$gdate\"\n"); }
        }
	#if ($broadcasts[$y] =~ /<center><b>[\w]+day, ([^ ]+), ([\d]+)[\w]{2}<\/b><\/center>/ ) {
	if ($broadcasts[$y] =~ /<center><b>[\w]+day, ([^ ,]+)[,]* ([\d]+)[\w]{2}<\/b><\/center>/ ) {
		$gdate=`date +%Y-%m-%d -d "$1 $2"`;
		chomp $gdate;
		if ($debug > 0) { print ("Found a date!  $1 $2; \$gdate=\"$gdate\"\n"); }
		# I don't think this'll be necessary...
		# if ($gdate gt $dbdate ) { print (" LAST !!!, '$gdate'>='$dbdate'\n"); }
		# last;
	}
	#if ($broadcasts[$y] =~ /td>([A-Za-z& ]{3,}) [at|vs].+ ([A-Za-z& ]{3,})<\/td><td>([\d]+:[\d]+.* [ap])\.m\.<\/td><td>[ ]*([a-zA-Z0-9-]+)[ ]*(\/ ([a-zA-Z0-9]+))*/ ) {
	#if ($broadcasts[$y] =~ /td>([A-Za-z& ]{3,}) [at|vs.]+ ([A-Za-z& ]{3,})<\/td><td>([\d]+:[\d]+.* [ap])m<\/td><td>[ ]*([a-zA-Z0-9-]+)[ ]*(\/ ([a-zA-Z0-9]+))*/ ) {
	# added the ";" in team name, for stuff like 'North Carolina A&amp;T', and the possible '$' in the network
	if ($broadcasts[$y] =~ /td>([A-Za-z& ;]{3,}) [at|vs.]+ ([A-Za-z& ;]{3,})<\/td><td>([\d]+:[\d]+.* [ap])m<\/td><td>[ ]*([\$]*[a-zA-Z0-9-]+)[ ]*(\/ ([\$]*[a-zA-Z0-9]+))*/ ) {
		$v_name = $1;
		$h_name = $2;
		$gtime = $3;
		$network1 = $4;
		$network2 = $6;
		if ($gtime =~ /p/) {
			$gtime =~ /([0-9]{1,2}):([0-9]{2})/;
			$h = $1;
			$m = $2;
			if ($h < 12) { $h += 12; }
			$gametime = $h.$m;
			if ($debug > 1) { print ("+++ "); }
		} else {
			$gtime =~ m/([0-9]{1,2}):([0-9]{2})/;
			$h=$1;
			$m=$2;
			if ($h < 10) { $h = @padded[$h]; }
			$gametime = $h.$m;
			if ($debug > 1) { print ("AM: "); }
		}
		if ($debug > 1) { print ("\$v_name=\"$v_name\", \$h_name=\"$h_name\", \$gametime=\"$gametime\", \$network1=\"$network1\", \$network2=\"$network2\" \n"); }
		$h_id=$teamid{$h_name};
		$v_id=$teamid{$v_name};
		$tgame = $gid{$h_id};
		if ($debug > 1) { print ("*** \$v_id='$v_id', \$h_id='$h_id', \$tgame='$tgame'\n"); }
		# Check to see if the home team and visitor teams we just found are in the same game on this date:
		# However; since there is "vs." it could be a neutral site, so either team could be home !!
		# Check it normally first, if it works, great, we'll set a flag and skip the other checks.
		$updated=0;
		if ($debug > 1 ) { print ("checking to see if the home team and visitor teams we just found are in the same game on this date...\n"); }
		if ( $debug > 1) { print ("\$gid{$h_id}=\'$gid{$h_id}\', \$gid{$v_id}=\'$gid{$v_id}\', \$sdate{\$gid{$h_id}}=\'$sdate{$gid{$h_id}}\', \$gdate=\'$gdate\'\n"); }
		if ( ( $gid{$h_id} == $gid{$v_id} ) && ( $sdate{$gid{$h_id}} == $gdate ) ) {
			# that's some logic, huh?
			# Now we can set the tvnetworks, and game start time if necessary.
			# see if the network is one we really care about !
			if ($network_id{$network1}) {
				$up_network1{$tgame} = $network_id{$network1};
				$up_network2{$tgame} = $network_id{$network2};
				if ($stime{$tgame} = '0800') { $up_stime{$tgame} = $gametime; }
				if ($debug > 0) { print ("******  \$up_network1{$tgame}='$up_network1{$tgame}', \$up_network2{$tgame}='$up_network2{$tgame}'\n"); }
				$updated=1;
			} else {
				if ($debug > 0) { print ("******  Don't care about the network \"$network1\".\n"); }
			}
		} else {
			if ($debug > 1) { print ("****** No Game updated, could not match \$gid{$h_id}=\'$gid{$h_id}\', \$gid{$v_id}=\'$gid{$v_id}\', \$sdate{\$gid{$h_id}}=\'$sdate{$gid{$h_id}}\', \$gdate=\'$gdate\' \n"); }
		}
		if ($updated=0) {
			if ($debug > 1) { print ("checking reverse: \$v_name=\"$h_name\", \$h_name=\"$v_name\", \$gametime=\"$gametime\", \$network1=\"$network1\", \$network2=\"$network2\" \n"); }
			$h_id=$teamid{$v_name};
			$v_id=$teamid{$h_name};
			$tgame = $gid{$h_id};
			if ( $debug > 1) { print ("\$gid{$h_id}=\'$gid{$h_id}\', \$gid{$v_id}=\'$gid{$v_id}\', \$sdate{\$gid{$h_id}}=\'$sdate{$gid{$h_id}}\', \$gdate=\'$gdate\'\n"); }
			if ( ( $gid{$h_id} == $gid{$v_id} ) && ( $sdate{$gid{$h_id}} == $gdate ) ) {
				if ($network_id{$network1}) {
					$up_network1{$tgame} = $network_id{$network1};
					$up_network2{$tgame} = $network_id{$network2};
					if ($stime{$tgame} = '0800') { $up_stime{$tgame} = $gametime; }
					if ($debug > 0) { print ("******  \$up_network1{$tgame}='$up_network1{$tgame}', \$up_network2{$tgame}='$up_network2{$tgame}'\n"); }
				} else {
					if ($debug > 0) { print ("******  Don't care about the network \"$network1\".\n"); }
				}
			} else {
				if ($debug > 1) { print ("****** No Game updated, could not match \$gid{$h_id}=\'$gid{$h_id}\', \$gid{$v_id}=\'$gid{$v_id}\', \$sdate{\$gid{$h_id}}=\'$sdate{$gid{$h_id}}\', \$gdate=\'$gdate\' \n"); }
			}
		}
	#} elsif ($broadcasts[$y] =~ /td>([A-Za-z& ]{3,}) [at|vs.]+ ([A-Za-z& ]{3,})<\/td><td>([\d]+:[\d]+.* [ap])\.m\.<\/td><td>[ ]*([a-zA-Z0-9-]+)[ ]*/ ) {
	} elsif ($broadcasts[$y] =~ /td>([A-Za-z& ]{3,}) [at|vs.]+ ([A-Za-z& ]{3,})<\/td><td>([\d]+:[\d]+.* [ap])m<\/td><td>[ ]*([a-zA-Z0-9-]+)[ ]*/ ) {
		$v_name = $1;
		$h_name = $2;
		$gtime = $3;
		$network1 = $4;
		if ($gtime =~ /p/) {
			$gtime =~ /([0-9]{1,2}):([0-9]{2})/;
			$h = $1;
			$m = $2;
			if ($h < 12) { $h += 12; }
			$gametime = $h.$m;
			if ($debug > 1) { print ("PM: "); }
		} else {
			$gtime =~ m/([0-9]{1,2}):([0-9]{2})/;
			$h=$1;
			$m=$2;
			if ($h < 10) { $h = @padded[$h]; }
			$gametime = $h.$m;
			if ($debug > 1) { print ("AM: "); }
		}
		if ($debug > 1) { print ("\$v_name=\"$v_name\", \$h_name=\"$h_name\", \$gametime=\"$gametime\", \$network1=\"$network1\"\n"); }
		$h_id=$teamid{$h_name};
		$v_id=$teamid{$v_name};
		$tgame = $gid{$h_id};
		if ($debug > 1) { print ("*** \$v_id='$v_id', \$h_id='$h_id', \$tgame='$tgame'\n"); }
		# Check to see if the home team and visitor teams we just found are in the same game on this date:
		if ( ( $gid{$h_id} == $gid{$v_id} ) && ( $sdate{$gid{$h_id}} == $gdate ) ) {
			# that's some logic, huh?
			# Now we can set the tvnetworks, and game start time if necessary.
			# see if the network is one we really care about !
			if ($network_id{$network1}) {
				$up_network1{$tgame} = $network_id{$network1};
				if ($stime{$tgame} = '0800') { $up_stime{$tgame} = $gametime; }
				if ($debug > 1) { print ("******** \$up_network1{$tgame}='$up_network1{$tgame}'\n"); }
				$updated=1;
			} else {
				if ($debug > 0) { print ("******  Don't care about the network \"$network1\".\n"); }
			}
		} else {
			if ($debug > 1) { print ("****** No Game updated, could not match \$gid{$h_id}=\'$gid{$h_id}\', \$gid{$v_id}=\'$gid{$v_id}\', \$sdate{\$gid{$h_id}}=\'$sdate{$gid{$h_id}}\', \$gdate=\'$gdate\' \n"); }
		}
		if ($updated=0) {
			if ($debug > 1) { print ("checking reverse: \$v_name=\"$h_name\", \$h_name=\"$v_name\", \$gametime=\"$gametime\", \$network1=\"$network1\", \$network2=\"$network2\" \n"); }
			$h_id=$teamid{$v_name};
			$v_id=$teamid{$h_name};
			$tgame = $gid{$h_id};
			if ( $debug > 1) { print ("\$gid{$h_id}=\'$gid{$h_id}\', \$gid{$v_id}=\'$gid{$v_id}\', \$sdate{\$gid{$h_id}}=\'$sdate{$gid{$h_id}}\', \$gdate=\'$gdate\'\n"); }
			if ( ( $gid{$h_id} == $gid{$v_id} ) && ( $sdate{$gid{$h_id}} == $gdate ) ) {
				if ($network_id{$network1}) {
					$up_network1{$tgame} = $network_id{$network1};
					$up_network2{$tgame} = $network_id{$network2};
					if ($stime{$tgame} = '0800') { $up_stime{$tgame} = $gametime; }
					if ($debug > 0) { print ("******  \$up_network1{$tgame}='$up_network1{$tgame}', \$up_network2{$tgame}='$up_network2{$tgame}'\n"); }
				} else {
					if ($debug > 0) { print ("******  Don't care about the network \"$network1\".\n"); }
				}
			} else {
				if ($debug > 1) { print ("****** No Game updated, could not match \$gid{$h_id}=\'$gid{$h_id}\', \$gid{$v_id}=\'$gid{$v_id}\', \$sdate{\$gid{$h_id}}=\'$sdate{$gid{$h_id}}\', \$gdate=\'$gdate\' \n"); }
			}
		}
	}
}

foreach $game (keys %up_network1) {
	# No need for duplicate networks!
	if ( $up_network2{$game} == $up_network1{$game} ) { 
		if ($debug > 0) { print ("\$up_network1{$game} = \$up_network2{$game} ($up_network1{$game}=$up_network2{$game}) , clearing \$up_network1{$game}...\n"); }
		$up_network2{$game}='';
	}
	# if the network is in our list, update, othewise, who cares.
	if ($debug > 0) { print ("\$up_network1{$game}='$up_network1{$game}' \$up_network2{$game}='$up_network2{$game}', \$up_stime{$game}='$up_stime{$game}'\n"); }
	print ("Updating game # $game, $visitorteam{$game} at $hometeam{$game} -- $network_name{$up_network1{$game}}");
	if ($up_stime{$game}) { print (", @ $up_stime{$game}"); }
	print ("\n"); 
	$msgbody .= "Updating game # $game, $visitorteam{$game} at $hometeam{$game} -- $network_name{$up_network1{$game}}";
	if ($up_stime{$game} ) { $msgbody .= ", @ $up_stime{$game}"; }
	$msgbody .= "<br>\n"; 
	$sql = "UPDATE nuke_pool_games SET tvnetwork = '$up_network1{$game}', tvnetwork2 = '$up_network2{$game}'";
	if ($up_stime{$game} ) { $sql .= ", time = '$up_stime{$game}'"; }
	$sql .= " WHERE league = 'NCAA' and date = '$sdate{$game}' and game = '$game' LIMIT 1";
	$cursor=$dbh->prepare($sql) || die "Cannot prepare statement: $DBI::errstr\n";
	$cursor->execute;
	print ("\"$sql\"\n");
	$msgbody .= "<i>$sql</i><br>\n";\
	$updates++;
}

if ($debug > 0 || $updates > 0) {
	$verb='update';
	if ($updates > 1 || $updates == 0 ) { $verb = 'updates'; } 
	if ($updates == 0 ) { $updates = 'No'; $msgbody .= "<br><b>No updates made.</b><br>\n"; } 
	open(SENDMAIL, "|/usr/sbin/sendmail -t") or die "Sendmail Offline: $!\n";
	print SENDMAIL "From: $email_from\n";
	print SENDMAIL "To: $email_to\n" or die "E-Mail wrong.\n";
	print SENDMAIL "Content-Type: multipart/alternative;\n";
	print SENDMAIL "        boundary=\"BEGIN_HTML\"\n";
	print SENDMAIL "X-Priority: 5\n";
	print SENDMAIL "Subject: Football Pools (NCAA) - $updates TVNetwork $verb\n\n";
	print SENDMAIL "X-MSMail-Priority: Low\n\n\n";
	print SENDMAIL "--BEGIN_HTML\n";
	print SENDMAIL "Content-Type: text/html;\n";
	print SENDMAIL "Content-Transfer-Encoding: quoted-printable\n\n\n";
	print SENDMAIL "<!-- created by $0 version $version -->\n";
	print SENDMAIL "<br>\n";
	print SENDMAIL "$msgbody";
	print SENDMAIL "<br>\n";
	print SENDMAIL "\n\n";
	close(SENDMAIL) or warn "sendmail didn't close nicely";
}

print ("Done!\n");
exit(0);

sub DoDate($d) {
	@pad=("00","01","02","03","04","05","06","07","08","09");
	@dow=("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
	@moy=("January","February","March","April","May","June","July","August","September","October","November","December");
	$t=time();
	$start = $t+(86400*$d);
	($j1,$j1,$j1,$day,$mon,$year,$wday,$j1,$j1) = localtime($start);
	$year += 1900;
	$big_date = $dow[$wday]." ".$moy[$mon]." ".$day.", ".$year;
	if ($debug > 0) { print ("$big_date\n"); }                           ## testing
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
	if ($debug > 3) { print ("\$big_date='$big_date', now=\"$now\", yesterday=\"$yesterday\", dbdate=\"$dbdate\"\n"); }
	return $dbdate, $big_date;
}
