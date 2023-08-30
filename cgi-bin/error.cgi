###################################################################
#  FILE: error.cgi                                                #
#  © Copyright 2000-2003 Seth Michael Knorr - mail@sethknorr.com  #
###################################################################


sub nopost {

print "Content-type: text/html\n\n";

print <<"MYMAINtext";

<html>

<head>
<title>Biz Mail Form &nbsp; &nbsp; &nbsp; &nbsp;  $versionnumber </title> 
</head><body bgcolor="#cfcfcf">
<center>


<table width=500 border=1><tr><td bgcolor="#000080">
<br><br>
<font face="Arial" color="#ffffff">
<center>
Biz Mail Form &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  $versionnumber <br><br>

 &copy; Copyright 2000-2003 Seth Knorr<br><br>
A free download of this script can be found at: <a href="http://www.bizmailform.com/" target="_blank"> <font color="#ff0000"><b> http://www.bizmailform.com/ </b></font></a>
</font></center>
<br><br>
</td></tr></table>
</center>

</body></html>

MYMAINtext

exit;

}

sub success {


print "Content-type: text/html\n\n";

	print <<"Myformatsuccess";

<html>
<head>
<title>Thank You.</title>
</head>
<body>
<BR><BR><CENTER>
<h2>Your form has been submitted successfully!</h2>


</CENTER>


$footer

</body>
</html>

Myformatsuccess


exit;

}


sub missing_email {

$body = &LoadFile("$MY_error_page");


$title = "<title>Missing or invalid format of email!</title>";
$errormessage = "<h2>Missing or invalid format of email.</h2><b>The email Field must be filled in and in the proper format!</b>";
$backbutton = "<br><br><b>Hit your browsers back button and resubmit the form.</b>";
               $body =~ s|<%TITLE%>|$title|g;
               $body =~ s|<%ERROR%>|$errormessage|g;
               $body =~ s|<%HIT_BROWSER_BACK_BUTTON%>|$backbutton|g;

print "Content-type: text/html\n\n";

	print <<"missingemailERROR";

$body


missingemailERROR

exit;

}


sub bad_okurl {


$body = &LoadFile("$MY_error_page");

$title = "<title>Invalid Reffering URL.</title>";
$errormessage = "<h2>Invalid Referring URL.</h2><b>If you are the administrator, edit the \@okurls in the bizmail.cgi script or turn this feature off.</b>";

               $body =~ s|<%TITLE%>|$title|g;
               $body =~ s|<%ERROR%>|$errormessage|g;

print "Content-type: text/html\n\n";

	print <<"badurlERROR";


$body


badurlERROR

exit;

}



sub format_error {

$body = &LoadFile("$MY_error_page");

for ($indexreq = 0; $indexreq < @required; $indexreq++) {
$myrequired = $required[$indexreq];
$myformreq = $formdata{"$myrequired"};

	if ($required_desc[$indexreq]) {
	$missing_desc = "$required_desc[$indexreq]";
	}else{
	$missing_desc = "$myrequired";
	}



if (!$myformreq){

$REQUIRED{"$indexreq"} = "${'LISTITEMS'}<li> $missing_desc";
${'LISTITEMS'} = $REQUIRED{"$indexreq"};

}
}


$title = "<title>Missing form fields!</title>";
$errormessage = "<h2>Missing form fields!</h2><b>The Below Required Fields Where Left Blank:</b><br><br>${'LISTITEMS'}<br><br><b>Hit your browsers back button and resubmit the form.</b>";
$backbutton = "<br><br><b>Hit your browsers back button and resubmit the form.</b>";
               $body =~ s|<%TITLE%>|$title|g;
               $body =~ s|<%ERROR%>|$errormessage|g;
               $body =~ s|<%HIT_BROWSER_BACK_BUTTON%>|$backbutton|g;

print "Content-type: text/html\n\n";

	print <<"MyformatERROR";

$body


MyformatERROR

exit;

}

return 1;
