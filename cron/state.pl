#!/usr/bin/perl -w

use strict;
use DBI;
use POSIX;

my $pageURL = 'http://web.engr.oregonstate.edu/~albertd/eecsabet/index.php';

my $headers  = "Subject: Your courses are ready to be finalized\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
$headers .= 'From: eecsabet@eecs.oregonstate.edu' . "\r\n";
$headers .= 'Reply-To: eecsabet@eecs.oregonstate.edu' . "\r\n";

my $body = "Your courses are ready to be finalized of their ABET accredidation information. Please provide this information soon. To do so, visit the following pages:";

my $beginSummer = POSIX::strftime("%V", 0, 0, 0, 20, 5, 2011);
my $beginFall = POSIX::strftime("%V", 0, 0, 0, 26, 8, 2011);
my $beginWinter = POSIX::strftime("%V", 0, 0, 0, 2, 0, 2011);
my $beginSpring = POSIX::strftime("%V", 0, 0, 0, 2, 3, 2011);
my $weekNumber = POSIX::strftime("%V", gmtime time);
my $year = POSIX::strftime("%Y", gmtime time);
my $weekOfTerm;

print "Begin Summer: $beginSummer\n";
print "Begin Fall: $beginFall\n";
print "Begin Winter: $beginWinter\n";
print "Begin Spring: $beginSpring\n";
print "Begin Week Numebr: $weekNumber\n";

my $termID;
if ($weekNumber > $beginFall)
{
	$termID = ($year + 1) . '01';
	$weekOfTerm = $weekNumber - $beginFall;
}
elsif ($weekNumber > $beginSummer)
{
	$termID = ($year + 1) . '00';
	$weekOfTerm = $weekNumber - $beginSummer;
}
elsif ($weekNumber > $beginSpring)
{
	$termID = $year . '03';
	$weekOfTerm = $weekNumber - $beginSpring;
}
else
{
	$termID = $year . '02';
	$weekOfTerm = $weekNumber - $beginWinter;
}

my $newState;
if ($weekOfTerm >= 8)
{
	$newState = 'Finalized';
}
else
{
	$newState = 'Approved';
}

print "$weekOfTerm, $weekNumber, $beginSummer, $newState\n";

my $hostname = "engr-db.engr.oregonstate.edu:3307";
my $database = "eecsabet";
my $username = "eecsabet";
my $password = "OPtbHauT";

my $con = DBI->connect("dbi:mysql:$database:$hostname", $username, $password)
	or die "Could not connect to MySQL database: $DBI::errstr\n";

my $query = "SELECT * FROM CurrentTermStateInformation";
my $sth = $con->prepare($query);
$sth->execute();
my @termInfoRow = $sth->fetchrow_array();
$sth->finish(); # Prevent warnings on exit

my $currentTerm = $termInfoRow[0];
my $state = $termInfoRow[1];

# FOR TESTING
$termID = '201203';
$newState = 'Finalized';
if ($currentTerm > $termID)
{
	# Advance to next term
	# Send mail
	print "Advancing term...\n";
}
elsif (($state eq 'Approved') and ($newState eq 'Finalized'))
{
	# Advance to next state
	# Send mail
	print "Advancing state...\n";
	
	$query = "CALL AllowFinalize('$termID')";
	$sth = $con->prepare($query);
	$sth->execute();
	my @resultRow = $sth->fetchrow_array();
	$sth->finish(); # Prevent warnings on exit
	
	my $result = $resultRow[0];
	if ($result != 1)
	{
		print "An error occured while calling AllowFinalize()\n";
	}
	
	$query = "CALL GetInstructorsByTerm('$termID')";
	$sth = $con->prepare($query);
	
	$query = "CALL GetUnfinishedCourses(?, '$termID')";
	my $h = $con->prepare_cached($query);
	
	$sth->execute();
	while (my @row = $sth->fetchrow_array())
	{
		my $name = $row[0];
		my $email = $row[1];
		
		open(MAIL, "|/usr/sbin/sendmail -t");
	
		print MAIL "To: $email\r\n";
		print MAIL "$headers\r\n";
		
		print MAIL '<html><head><title>Ready to be Finalized</title><head><body>' . $name . ',<br /><br />' . $body . '<br />';
		$h->execute($email);
		while (my @courseRow = $h->fetchrow_array())
		{
			print MAIL '<a href="' . $pageURL . '?courseInstanceID=' . $courseRow[1] . '">' . $courseRow[0] . '</a><br />';
		}
		print MAIL '<br />EECS ABET Mailer';
		
		close(MAIL);
	}
	$sth->finish(); # Prevent warnings on exit
	$h->finish(); # Prevent warnings on exit
}
else
{
	print "No change ($termID, $state)\n";
}

$con->disconnect() or warn "$con->errstr\n";
