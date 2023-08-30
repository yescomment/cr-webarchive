###################################################################
#  FILE: sendmail.cgi                                             #
#  © Copyright 2000-2003 Seth Michael Knorr - mail@sethknorr.com  #
###################################################################

sub sdsmail {


                if ($send_email_results eq "1") {

 	open(MAIL, "|$mailprog -t") || die "Can't open $mailprog!\n";
	print MAIL "To: $MAIN_mail_send\n";
if ($mail_ccto){
      print MAIL "Cc: $mail_ccto\n";
}
	print MAIL "From: $formdata{'email'}\n";
	print MAIL "Subject: $formdata{'subject'}\n";
	print MAIL "The form below was submited by $formdata{'email'} form Ip address: $ENV{'REMOTE_ADDR'} on $monthnameactual $dayofmonth, $year at $newhour:$newmin $nightday \n";
	print MAIL "-------------------------------------------------------------------------\n\n";


if (! $formdata{'sort'}) {


  for ($indexval = 0; $indexval < @formvalue; $indexval++) {
  ${'myname'} = $formname[$indexval];
  ${'myvalue'} = $formvalue[$indexval];


	if (${'myname'} eq "reply_subject"|| ${'myname'} eq "subject" || ${'myname'} eq "sendreply" || ${'myname'} eq "required" || ${'myname'} eq "success_page" || ${'myname'} eq "message_format" || ${'myname'} eq "text_qualifier" || ${'myname'} eq "datafile" || ${'myname'} eq "outputfile" || ${'myname'} eq "sort" || ${'myname'} eq "data_format" || ${'myname'} eq "cc_to" || ${'myname'} eq "send_to" || ${'myname'} eq "plain_mesfile" || ${'myname'} eq "html_mesfile" || ${'myname'} eq "error_page" || ${'myname'} eq "required_desc"){ 
	$dontrun = "1";
	}else{
	$dontrun = "";
	}

		## CHECK PRINT BLANK FIELDS ##
		if ($PRNT_blankfields eq "2" && ! ${'myvalue'}) {
		$dontrun = "1";
		}

	if (! $dontrun){
	print MAIL "${'myname'}:   ${'myvalue'} \n\n";
	}
  }

}else{  ##ELSE IF NO SORT FIELD ##

	for ($numsort = 0; $numsort < @sortfields; $numsort++) {
	$sfield = $sortfields[$numsort];
	${'sfieldvalue'} = $formdata{"$sfield"};
	${'sfield'} = "$sfield";

	## CHECK PRINT BLANK FIELDS ##
	if ($PRNT_blankfields eq "2" && ! ${'myvalue'}) {
	}else{

	print MAIL "${'sfield'}:   ${'sfieldvalue'} \n\n";

	} ## END CHECK PRINT BLANK FIELDS ##

	}

} ## END ELSE IF SORT FIELD ##

	close MAIL;

                 }  ## END IF SEND_EMAIL_RESULTS##
 
if ($formdata{'sendreply'} eq "1"){



use MIME::Lite;
    MIME::Lite->send("sendmail", "$mailprog -t -oi -oem");



    $msg = MIME::Lite->new(
           From    => $mail_sendto,
           To      => $formdata{'email'},
           Subject => $reply_subject,
           Type    => $email_format,
           Data    => $mime_body,
           "X-Loop:" => "BIZMAIL",
           "X-Arpidentifier:" => 12345

    ); 

########################################
## USE ATTACHEMENT                    ##
########################################

if ($send_attachement eq "1"){

                    $msg->attach(Type => $att_content_type,
                                 Encoding => $att_format->[1],
                                 Path => $att_path,
                                 Filename => $attachment_nm
                    );

}


######################################
$msg->send
}

}

return 1;
