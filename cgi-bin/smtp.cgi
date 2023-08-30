###################################################################
#  FILE: smtp.cgi                                                 #
#  © Copyright 2000-2003 Seth Michael Knorr - mail@sethknorr.com  #
###################################################################

sub sdsmtp {


	$msgSubject = "BizMail Contact Form - " . "$formdata{'subject'}";

	if ($formdata{'message_format'} eq "html") {
		$nl = "<br>";
		$msgMessage = '<FONT face=Arial size=3>';
	} else {
		$nl = "\n";
		$msgMessage = "";
	}
	$msgMessage=$msgMessage.$formdata{'message'}.$nl.$nl.$nl;
	$msgMessage=$msgMessage."The form below was submitted by ";
	$msgMessage=$msgMessage.$formdata{'email'}."    Ip address:  ".$ENV{'REMOTE_ADDR'};
	$msgMessage=$msgMessage."   ".$monthnameactual." ".$dayofmonth.", ".$year;
	$msgMessage=$msgMessage." at ".$newhour.":".$newmin.$nightday.$nl;
	$msgMessage=$msgMessage."-------------------------------------------------------------------------".$nl.$nl;


if (! $sort) {

	for ($indexval = 0; $indexval < @formvalue; $indexval++) {
		${'myname'} = $formname[$indexval];
		${'myvalue'} = $formvalue[$indexval];
                if (${'myname'} eq "reply_subject"|| ${'myname'} eq "subject" || ${'myname'} eq "sendreply" || ${'myname'} eq "required" || ${'myname'} eq "success_page" || ${'myname'} eq "message_format" || ${'myname'} eq "text_qualifier" || ${'myname'} eq "datafile" || ${'myname'} eq "sort" || ${'myname'} eq "outputfile" || ${'myname'} eq "data_format" || ${'myname'} eq "cc_to" || ${'myname'} eq "send_to" || ${'myname'} eq "plain_mesfile" || ${'myname'} eq "html_mesfile" || ${'myname'} eq "error_page" || ${'myname'} eq "required_desc"){ 
			$dontrun = "1";
		}else{
			$dontrun = "";
		}
			## CHECK BLANK FIELDS ##
			if ($PRNT_blankfields eq "2" && ! ${'myvalue'}) {
			$dontrun = "1";
			} ## END CHECK BLANK FIELDS ##

		if (! $dontrun) {
		$msgMessage= $msgMessage.${'myname'}.": ".${'myvalue'}.$nl;
		}
	}

}else{  ##ELSE IF NO SORT FIELD ##

	for ($numsort = 0; $numsort < @sortfields; $numsort++) {
	$sfield = $sortfields[$numsort];
	${'sfieldvalue'} = $formdata{"$sfield"};
	${'sfield'} = "$sfield";

	## CHECK BLANK FIELDS ##
	if ($PRNT_blankfields eq "2" && ! ${'sfieldvalue'}) {
	}else{
	$msgMessage .= "${'sfield'}:   ${'sfieldvalue'} \n\n";
        } ## END CHECK BLANK FIELDS ##

	}

} ## END ELSE IF SORT FIELD ##

if ($formdata{'message_format'} eq "html") {

  $msgMessage=$msgMessage."</font>";
  $msgformat = "text/html";
  $mime_body = "$mime_html_body";

}else{

  $msgformat = "text/plain";
  $mime_body = "$mime_plain_body";

}

$mail_return = "$mail_sendto";

eval { require(Net::SMTP)};
if ($@) {
print "Failed to load";
exit;
}

##check to see if send email results##
    if ($send_email_results eq "1") {
$smtp = Net::SMTP->new($smtp_server); # connect to an SMTP server #
$smtp->mail($formdata{'email'}); # use the sender's address here #
$smtp->to ($mail_sendto); # recipient's address #
$smtp->data(); # Start the mail #

$smtp->datasend("To: ".$mail_sendto."\n");
$smtp->datasend("From: $formdata{'email'}\n"); 
$smtp->datasend("Subject: $msgSubject\n"); 
$smtp->datasend("Content-Type: ".$msgformat);
$smtp->datasend("\n\n");
$smtp->datasend($msgMessage."\n"); 
$smtp->datasend("\n"); 
$smtp->datasend("\n"); 
$smtp->datasend("\n"); 
$smtp->datasend("\n");

$smtp->dataend(); # Finish sending the mail #
$smtp->quit; # Close the SMTP connection #
    } ## END IF SEND EMAIL RESULTS ##

##check to see if cc results##
    if ($mail_ccto && $send_email_results eq "1") {
$smtp = Net::SMTP->new($smtp_server); # connect to an SMTP server #
$smtp->mail($formdata{'email'}); # use the sender's address here #
$smtp->to ($mail_ccto); # cc to address #
$smtp->data(); # Start the mail #
$smtp->datasend("To: ".$mail_ccto."\n"); 
$smtp->datasend("From: $formdata{'email'}\n"); 
$smtp->datasend("Subject: $msgSubject\n"); 
$smtp->datasend("Content-Type: ".$msgformat);
$smtp->datasend("\n\n");
$smtp->datasend($msgMessage."\n"); 
$smtp->datasend("\n"); 
$smtp->datasend("\n"); 
$smtp->datasend("\n"); 
$smtp->datasend("\n");

$smtp->dataend(); # Finish sending the mail #
$smtp->quit; # Close the SMTP connection #
    } ## END IF cc to RESULTS ##

       if ($formdata{'sendreply'} eq "1") { 
 $smtp = Net::SMTP->new($smtp_server); # connect to an SMTP server #
 $smtp->mail($mail_sendto); # use the sender's address here #
 $smtp->to ($formdata{'email'}); # recipient's address #
 $smtp->data(); # Start the mail #

 $smtp->datasend("To: ".$formdata{'email'}."\n"); 
 $smtp->datasend("From: $mail_sendto\n"); 
 $smtp->datasend("Subject: $reply_subject\n"); 
 $smtp->datasend("Content-Type: ".$msgformat);
 $smtp->datasend("\n\n");
 $smtp->datasend($mime_body."\n"); 
 $smtp->datasend("\n"); 
 $smtp->datasend("\n"); 
 $smtp->datasend("\n"); 
 $smtp->datasend("\n"); 




########################################
## USE ATTACHEMENT                    ##
########################################

if ($send_attachement eq "1"){


                    $smtp->attach(Type => $att_content_type, 
                                 Encoding => $att_format->[1],
                                 Path => $att_path,
                                 Filename => $attachment_nm
                    );

}

	
 $smtp->dataend(); # Finish sending the mail #
 $smtp->quit; # Close the SMTP connection #
	 }

} ## END sdsmtp #

return 1;
