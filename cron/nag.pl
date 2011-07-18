#!/usr/bin/perl -w

use strict;
use DBI;

my $hostname = "engr-db.engr.oregonstate.edu:3307";
my $database = "eecsabet";
my $username = "eecsabet";
my $password = "OPtbHauT";

my $pageURL = 'http://web.engr.oregonstate.edu/~albertd/eecsabet/index.php';

my $headers  = "Subject: You've been neglecting your ABET forms\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
$headers .= 'From: eecsabet@eecs.oregonstate.edu' . "\r\n";
$headers .= 'Reply-To: eecsabet@eecs.oregonstate.edu' . "\r\n";

my $body = "You still have courses which you have not provided ABET " .
	"accredidation information for. Please provide this information soon. To " .
	"do so, visit the following pages:";

my $con = DBI->connect("dbi:mysql:$database:$hostname", $username, $password)
	or die "Could not connect to MySQL database: $DBI::errstr\n";

my $query = "SELECT * FROM NaggingInformation GROUP BY Email";
my $sth = $con->prepare($query);

$query = "SELECT InstanceID, Course " .
	"FROM NaggingInformation " .
	"WHERE NaggingInformation.Email=?";

my $h = $con->prepare_cached($query);
	
$sth->execute();
while (my @row = $sth->fetchrow_array())
{
	my $instructor = "$row[0] $row[1]";
	
	open(MAIL, "|/usr/sbin/sendmail -t");
	
	print MAIL "To: $row[2]\r\n";
	print MAIL "$headers\r\n";
	
	print MAIL '<html><head><title>Nagging</title><head><body>' . $instructor .
		',<br /><br />' . $body . '<br />';
	$h->execute($row[2]);
	while (my @courseRow = $h->fetchrow_array())
	{
		print MAIL '<a href="' . $pageURL . '?courseInstanceID=' .
			$courseRow[0] . '">' . $courseRow[1] . '</a><br />';
	}
	print MAIL '<br />EECS ABET Mailer';
	
	close(MAIL);
}

$con->disconnect() or warn "$con->errstr\n";
