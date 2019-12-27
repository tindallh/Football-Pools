#!/usr/bin/perl
#############################################################################
# MailingListHTML.pl                                                        #
#    written by:  Henry B. Tindall, Jr. for football-pools.org              #
# original release:  19 Sep 2016                                            #
# version 2016.1.0 - 19 Sep 2016 - initial release                          #
#############################################################################

#$spacing = "[ ]+";
$email_to = "henry\@Linux-Meister.com";
#$email_to = "talk\@football-pools.org";
$email_from="Henry B. Tindall Jr. <henry\@football-pools.org>";

@input=`cat ~/scripts/input.html`;
$subject=@input[0];

open(SENDMAIL, "|/usr/sbin/sendmail -t") or die "Sendmail Offline: $!\n";
print SENDMAIL "From: $email_from\n";
print SENDMAIL "To: $email_to\n" or die "E-Mail wrong.\n";
print SENDMAIL "Content-Type: multipart/alternative;\n";
print SENDMAIL "        boundary=\"BEGIN_HTML\"\n";
print SENDMAIL "X-Priority: 5\n";
print SENDMAIL "Subject: $subject\n\n";
print SENDMAIL "X-MSMail-Priority: Low\n\n\n";
print SENDMAIL "--BEGIN_HTML\n";
print SENDMAIL "Content-Type: text/html;\n";
print SENDMAIL "Content-Transfer-Encoding: quoted-printable\n\n\n";
print SENDMAIL "<!-- created by $0 version $version -->\n";
print SENDMAIL "<br>";
for ($y=1; $y < $#input+1; $y++) {
	print SENDMAIL $input[$y];
}
print SENDMAIL "<br><!-- All content (unless otherwise noted) is copyright 2016 Henry B. Tindall, Jr., and/or Football-Pools.org.\n";
print SENDMAIL "  All team logos are copyright/trademark their respective team and/or league -->\n";
print SENDMAIL "\n\n";
close(SENDMAIL) or warn "sendmail didn't close nicely";
