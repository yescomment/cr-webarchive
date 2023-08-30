#!/usr/bin/perl
## make sure the path to pearl is correct above. ##

##########################################################################################
##  FILE: bizmail.cgi                                                                   ##
##                                                                                      ##
##  BizMailForm                                   Version 2.0                           ##
##  ©Copyright 2000-2004+ Seth Michael Knorr      mail@sethknorr.com                    ##
##                                                                                      ##
##                       http://www.bizmailform.com/                                    ##
##         Please contact me with any bugs found, or any bug fixes.                     ##
##                                                                                      ##
##########################################################################################
##                                                                                      ##
##  There is no email support provided for this script, the only support can be         ##
##  found at our web site: http://www.bizmailform.com/                                  ##
##                                                                                      ##
##                                                                                      ##
##  ANY PERSON(S) MAY USE AND MODIFY THESE SCRIPT(S) FREE OF CHARGE FOR EITHER BUSINESS ##
##  OR PERSONAL, HOWEVER AT ALL TIMES HEADERS AND COPYRIGHT MUST ALWAYS REMAIN INTACT.  ##
##                                                                                      ##
##  REDISTRIBUTION FOR PROFIT IS PROHIBITED WITH OUT THE CONSENT OF SETH KNORR.         ##
##                                                                                      ##
##  By using this code you agree to indemnify Seth M. Knorr from any liability that     ##
##  might arise from its use.                                                           ##
##                                                                                      ##
##########################################################################################
##                                                                                      ##
##                       MIME Lite.pm copyright information below:                      ##
##                                                                                      ##
## Eryq (F<eryq@zeegee.com>).President, ZeeGee Software Inc. (F<http://www.zeegee.com>).##
##                                                                                      ##
##########################################################################################
##                                                                                      ##
##               T H A N K   Y O U   I N   A D V A N C E   F O R                        ##
##                 S U P P O R T I N G   M Y   S P O N S O R S                          ##
##                                                                                      ##
##########################################################################################



#----- S E N D M A I L   &   S M T P   S E T T I N G S  -----#

## $useLib defines the method of sending the email auto response and email form info.   ##       
## Set $useLib = "sendmail"; TO USE THE SENDMAIL METHOD
## Set $useLib = "smtp"; TO USE THE SMTP METHOD

$useLib = "sendmail";

$mailprog = "/usr/sbin/sendmail –t"; ## $mailprog = path to Sendmail on your server  ##

#$smtp_server = "mail.dhlabsnyc.com";  ## Optional $smtp_server is your smtp server address (ONLY WHEN USING SMTP)   ##



#----- F R O M   N A M E   O N   R E P L Y   E M A I L  -----#

##  $reply_from_name =  from name that will apear on the auto response email.  ##

        $reply_from_name = "DH Labs";



#----- S E N D   T O    E M A I L   S E T U P  -----#

## $sendto{"1"} is Where form submissions will be sent,                                 ##
## (REMEMBER THE \ BEFORE THE @ SIGN)                                                   ##
## This is also the reply address used in the auto response to person filling out the   ## 
## form.                                                                                ## 


        $sendto{"1"} = "cityreliquary\@dhlabsnyc.com";  ## (REMEMBER THE \ BEFORE THE @ SIGN)##
        $sendto{"2"} = "mail3\@yourdomain.com";
        $sendto{"3"} = "mail3\@yourdomain.com";
        $sendto{"4"} = "mail4\@yourdomain.com";
        $sendto{"5"} = "mail5\@yourdomain.com";



#----- C A R B O N   C O P I E D    E M A I L   S E T U P  -----#

## $cc_to  Is an optional field. If you want to carbon copy form information to a       ##
## second email address, enter a value above. If you do not want to carbon copy the     ##
## form submissions, leave the value blank.                                             ##

        $cc_to{"1"} = "ccmail\@yourdomain.com";
        $cc_to{"2"} = "ccmail2\@yourdomain.com";
        $cc_to{"3"} = "";
        $cc_to{"4"} = "";
        $cc_to{"5"} = "";



#########################################################################
###                                                                   ###
###         O P T I O N A L    V A R I A B L E S    B E L O W         ###
###                                                                   ###
#########################################################################


#----- U S E   P E R S O N A L I Z E D   E R R O R   F O R M   P A G E -----#

## set $use_html_error = "2"; to not use the personalized html form error page template ##
## (DEFAULT) set $use_html_error = "1"; to use the personalized html form error page template ##

$use_html_error = "1";

$HTML_error_page{"1"} = "formerror.html";
$HTML_error_page{"2"} = "";
$HTML_error_page{"3"} = "";
$HTML_error_page{"4"} = "";
$HTML_error_page{"5"} = "";


#----- D E F A U L T   H T M L   E R O R   P A G E   T E M P L A T E  -----#

$MY_error_page = "error.html";   # HTML error page you configured  ##



#----- P R I N T   B L A N K   F I E L D S -----#

## set $PRNT_blankfields = "2"; to not send & leave out blank form field results to your email ##
## (DEFAULT) set $PRNT_blankfields = "1"; to send blank form field results to your email ##

$PRNT_blankfields = "1";


#----- D A T A   F I L E   L O C A T I O N  -----#

## $datafile{"1"} & $datafile{"2"} is the file name of the data file that form data will get sent to.  ##

        $datafile{"1"} = "bizmail.dat";
        $datafile{"2"} = "";
        $datafile{"3"} = "";
        $datafile{"4"} = "";
        $datafile{"5"} = "";

#----- S E N D    R E S U L T S   T O   D A T A   F I L E  -----#

## set $send_data_results = "2"; to not send form results to the data file ##
## (DEFAULT) set $send_data_results = "1"; to send form results to the data file ##

$send_data_results = "1";



#----- S E N D   E M A I L   R E S U L T S   T O   Y O U R   E M A I L  -----#

## set $send_email_results = "2"; to not send form results to your email ##
## (DEFAULT) set $send_email_results = "1"; to send form results to your email ##

$send_email_results = "1";



#----- D E L E T E   D U P S -----#

## $delete_dups = "1";  will delete all dups.    ##
## To leave duplicate posts set $delete_dups = "2"; (DEFAULT - MOST COMMON CHOICE)  ## 
##  (A duplicate is determined by the email field.)  ##

$delete_dups = "2";  # USHUALY, IT IS ONLY SET TO 1 FOR PEOPLE GENERATING LEADS ##



#-----  F I L E   A T T A C H M E N T -----#

## $send_attachement specifys if you want to send an attachment with the auto response. ##
## Set $send_attachement = "1"; to send attachment with autoresponse                    ##
## Set $send_attachement = "2"; to not send attachment with autoresponse                ##

      $send_attachement = "2";

## IF USING A FILE ATTACHMENT YOU MUST CONFIGURE THE BELOW THREE VARIABLES              ##

$attachment_nm = "page.pdf";  ## file name of attachement ##

$att_path = "/path/to/attachment/";  ## Path to file attachement WITH TRAILING / ##

$att_format = ".pdf";  ## File attachement extension, or file type (basicaly .exstension ) ##


# $att_content_type IS THE CONTENT-TYPE OF THE ATTACHMENT #

	$att_content_type = "pdf"; # FORMAT FOR A "PDF" #

	# $att_content_type = "text/html";
	# $att_content_type = "image/gif"; # FORMAT FOR A "GIF IMAGE" #
	# $att_content_type = "image/jpeg"; # FORMAT FOR A "JPEG IMAGE" #
	# $att_content_type = "image/png"; # FORMAT FOR A "PNG IMAGE" #
	# $att_content_type = "zip"; # FORMAT FOR A "ZIP" #


#-----  S E T   O K   U R L -----#

        $setokurl = "0";

## to use @okurls to verify the url the form is submited by set $setokurl to 1 and      ##
## set $setokurl to 0 if you do not want to use @okurls to verfiy form submission URL   ##


        @okurls = ("http://www.yourdomain.com", "http://yourdomain.com", "34.344.344.344");



###########################################################################
####                                                                    ###
####   N O   N E E D   T O   E D I T  V A R I A B L E S   B E L O W     ###
####                                                                    ###
###########################################################################


$versionnumber = "Version 2.0";
$footer = "<br><br><br><br><br><center><font face='Arial'><a href='http://www.bizmailform.com/' target='_blank'><font color='#ff0000'>Form processing script provided by Biz Mail Form</font></a> </center></font>";

   $offset = @_;
    $offset=$offset*86400;

    ($sec, $min, $hour, $dayofmonth, $mon, $year, $weekday, $dayofyear, $IsDST) = localtime(time + $offset);
    $year = $year + 1900;
    @months = ("JA", "FB", "MR", "AP", "MY", "JN", "JL", "AG", "SP", "Oc", "NV", "DE");
    $monthname = $months[$mon];
    @monthsnum = ("1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12");
    $monthnamenum = $monthsnum[$mon];

    @monthsactual = ("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
    $monthnameactual = $monthsactual[$mon];


    @days = ("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
    $dayname = $days[$weekday-1];

${'monthname'} = "$monthname";
${'monthnamenum'} = "$monthnamenum";
${'dayofmonth'} = "$dayofmonth";


if ($hour > 12){
$hourplus=$hour;
$newhour = ($hourplus-12);
$nightday = "PM";
}else{
$newhour=$hour;
$nightday = "AM";
}


if ($min <= 9){
$newmin = "0$min";
}else{
$newmin = "$min";
}

${'hour'}=$newhour;


if($ENV{'REQUEST_METHOD'} eq "GET") {
require 'error.cgi';
&nopost;
}else{
&get_form_data;
&main;
}


sub get_form_data {

	read(STDIN, $buffer, $ENV{'CONTENT_LENGTH'});
	@pairs=split(/&/,$buffer);
	foreach $pair (@pairs)
	{
		@a = split(/=/,$pair);
		$name=$a[0];
		$value=$a[1];
		$value2=$a[1],;
		$name =~ s/\+/ /g;
		$value =~ s/%([a-fA-F0-9][a-fA-F0-9])/pack("C", hex($1))/eg;
		$value =~ s/~!/ ~!/g;
		$value =~ s/\+/ /g;
		$value =~ s/(\r)+/\-\-/g;
		$value =~ s/\n+//g;
		$value =~ s/(\-\-)+/\n/g;
##  $value =~ s|\&|\&amp\;|g;	# convert all '&' to "&amp;" (must be first)
## The above value was removed for means of new success page ##
                $value =~ s|<|\&lt\;|g; 	# convert all '<' to "&lt;"
                $value =~ s|>|\&gt\;|g;	        # convert all '>' to "&gt;"

## $value2 is used in variables of data sent to the data file##
		$value2 =~ s/(\")+/``/g; #V1.5 Bug Fix - Converts " to `` in data file #
		$value2 =~ s/(')+/`/g; #V1.8 Bug Fix - Converts ' to ` in data file #
		$value2 =~ s/%([a-fA-F0-9][a-fA-F0-9])/pack("C", hex($1))/eg;
                $value2 =~ s|\n|<br>\n|g;	# convert all line feeds to "<br>" and line feed
		#$value2 =~ s/~!/ ~!/g;
		#$value2 =~ s/\+/ /g;
		#$value2 =~ s/(\r)+/\-\-/g;
		#$value2 =~ s/\n+//g;



		push (@formdata,$name);
		push (@formdata,$value);
		push (@formdata2,$name);
		push (@formdata2,$value2);
		push (@form,$nameform);
		push (@form,$valueform);
		push (@formname,$name);
		push (@formvalue,$value);
		push (@formvalue2,$value2);


	}
	%formname=@formname;
	%formname;
	%formdvalue2=@formvalue2;
	%formvalue2;
	%formvalue=@formvalue;
	%formvalue;
	%formdata=@formdata;
	%formdata;
	%formdata2=@formdata2;
	%formdata2;


	}


sub main {

$att_path = "$att_path$attachment_nm";


if (! $formdata{'error_page'}) {
$USE_error_page=$HTML_error_page{"1"};
}else{
$USE_error_page=$HTML_error_page{"$formdata{'error_page'}"};
}


    @sortfields = split(/,/,$formdata{'sort'});

    @required = split(/,/,$formdata{'required'});

    @required_desc = split(/,/,$formdata{'required_desc'});


for ($indexreq = 0; $indexreq < @required; $indexreq++) {
$myrequired = $required[$indexreq];
$myformreq = $formdata{"$myrequired"};


  if ($myrequired eq "email"){
      if (!$myformreq || $myformreq =~ /(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/ || $myformreq !~ /^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z0-9]+)(\]?)$/){

		if ($use_html_error eq "1") {
		      require 'required.cgi';
		      &REQ_format_error;
		}else{
 		     require 'error.cgi';
		      &format_error;
		}
      }
  } ## END IF REQUIRED IS EMAIL ##
  elsif (!$myformreq){

if ($use_html_error eq "1") {
      require 'required.cgi';
      &REQ_format_error;
}else{
      require 'error.cgi';
      &format_error;
}

  }

}


if ($formdata{'sendreply'} eq "1"){
      if (!$formdata{'email'} || $formdata{'email'} =~ /(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/ || $formdata{'email'} !~ /^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z0-9]+)(\]?)$/){

if ($use_html_error eq "1") {
      require 'required.cgi';
      &REQ_missing_email;
}else{
      require 'error.cgi';
      &missing_email;
}

      } ## END IF ##
}


                     if ($setokurl =~ /1/){

  $url = "0";
  $referer = "\L$ENV{'HTTP_REFERER'}\E";


  foreach $myokurls (@okurls) {
$myokurls = "\L$myokurls\E";
     if ($referer =~ /$myokurls/)
      { 
      $url = "1"; 
      }

  }
  if ( $url == 0) {
  require 'error.cgi';
  &bad_okurl;
  }

                     }


## CHECK TO SEE IF WRITE TO DATA FILE. ##

if ($send_data_results eq "1"){

if ($formdata{'datafile'}) {
$FORM_datafile = "$formdata{'datafile'}";
        $ctrfile=$datafile{"$FORM_datafile"};

}else{
        $ctrfile=$datafile{"1"};

}



     if ($formdata{'data_format'}){
     ${'seperatedby'} = "$formdata{'data_format'}";
     }else{
     ${'seperatedby'} = ",";
     }

     if ($formdata{'text_qualifier'} =~ 1){
     ${'spqualifier'} = '"';
     }



## START CHECK DUPLICATE ##

  if ($delete_dups eq "1"){

	open ( DUPFILE, "$ctrfile");
	@filelines=<DUPFILE>;
	close( DUPFILE );

        $linectr = "@filelines";

           if ($linectr =~ $formdata{'email'}){
                           &duplicate_email;
           }

  }



                        if ($formdata{'outputfile'}) { ##start if output##


    @outputfile = split(/,/,$formdata{'outputfile'});

   	open(OUT,">> $ctrfile");
		eval "flock OUT,2";


for ($indexoutput = 0; $indexoutput < @outputfile; $indexoutput++) { ##start for each select output##
$myoutput = $outputfile[$indexoutput];
$myformoutput = $formdata2{"$myoutput"};

    if ($myoutput eq "ipaddress"){ ## if ipaddress  ##
       print OUT "${'spqualifier'}$ENV{'REMOTE_ADDR'}${'spqualifier'}${'seperatedby'}";
    }
    elsif ($myoutput eq "date"){ ## if date  ##
       print OUT "${'spqualifier'}$monthnamenum/$dayofmonth/$year at $newhour:$newmin${'spqualifier'}${'seperatedby'}";
    }else{
    print OUT "${'spqualifier'}$myformoutput${'spqualifier'}${'seperatedby'}";
    } ## end else  ##




} ##end for each ##

       print OUT "\n";
	close (OUT);




                          }else{ ## end if output / start else ouput##

   	open(OUT,">> $ctrfile");
		eval "flock OUT,2";


for ($indexval = 0; $indexval < @formvalue; $indexval++) { ##start for ouput##
${'myname'} = $formname[$indexval];
${'myvalue'} = $formvalue2[$indexval];

## Check if config field ##
if (${'myname'} eq "reply_subject"|| ${'myname'} eq "subject" || ${'myname'} eq "sendreply" || ${'myname'} eq "required" || ${'myname'} eq "success_page" || ${'myname'} eq "message_format" || ${'myname'} eq "text_qualifier" || ${'myname'} eq "datafile" || ${'myname'} eq "outputfile" || ${'myname'} eq "data_format" || ${'myname'} eq "cc_to" || ${'myname'} eq "send_to" || ${'myname'} eq "plain_mesfile" || ${'myname'} eq "html_mesfile" || ${'myname'} eq "error_page" || ${'myname'} eq "required_desc"){ 
$dontrun = "1";
}else{
$dontrun = "";
}

                        if (! $dontrun){
			print OUT "${'spqualifier'}${'myvalue'}${'spqualifier'}${'seperatedby'}";
                        }


	} ##end for ouput##

			print OUT "${'spqualifier'}$ENV{'REMOTE_ADDR'}${'spqualifier'}${'seperatedby'}${'spqualifier'}$monthnamenum/$dayofmonth/$year at $newhour:$newmin${'spqualifier'}\n";

	close (OUT);

                                  
                           } ##end else output##



} ## END IF SEND RESULTS TO DATA FILE ##



for ($indexval = 0; $indexval < @formvalue; $indexval++) {
${'myname'} = $formname[$indexval];
$myname = $formname[$indexval];
${'myvalue'} = $formvalue[$indexval];

    $formdata{'reply_subject'} =~s/{$myname}/${'myvalue'}/g;

}


for ($indexval = 0; $indexval < @formvalue; $indexval++) {
${'myname'} = $formname[$indexval];
$myname = $formname[$indexval];
${'myvalue'} = $formvalue[$indexval];

    $formdata{'subject'} =~s/{$myname}/${'myvalue'}/g;

}


for ($indexval = 0; $indexval < @formvalue; $indexval++) {
${'myname'} = $formname[$indexval];
$myname = $formname[$indexval];
${'myvalue'} = $formvalue[$indexval];

    $formdata{'success_page'} =~s/{$myname}/${'myvalue'}/g;

}


     if ($formdata{'email'}) {
     &notify;
     }

         if ($formdata{'success_page'}) {
         print "Location: $formdata{'success_page'}\n\n";
         exit;
         }else{
         require 'error.cgi';
         &success; 
         }



}


sub notify{

$FORM_sendto = "$formdata{'send_to'}";
$FORM_ccto = "$formdata{'cc_to'}";
$FINAL_sendto = $sendto{"$FORM_sendto"};
$FINAL_ccto = $cc_to{"$FORM_ccto"};


                 if ($formdata{'send_to'}){
                 $mail_sendto = "\"$reply_from_name\" <$FINAL_sendto>";
                 $MAIN_mail_send = "$FINAL_sendto";
                 }else{
                 $mail_sendto = "\"$reply_from_name\" <$FINAL_sendto>";
                 $MAIN_mail_send = "$FINAL_sendto";
                 }

                 if ($formdata{'cc_to'}){
                 $mail_ccto = "$FINAL_ccto";

                 }else{
                 $mail_ccto = "$FINAL_ccto";
                 }


        $HTML_format = "text/html";
        $PLAIN_format = "text/plain";

if ($formdata{'html_mesfile'}) {
$mime_html_body = &LoadFile("$formdata{'html_mesfile'}");
}else{
$mime_html_body = &LoadFile("html.mes");
}

if ($formdata{'plain_mesfile'}) {
$mime_plain_body = &LoadFile("$formdata{'plain_mesfile'}");
}else{
$mime_plain_body = &LoadFile("plain.mes");
}

        $reply_subject = $formdata{'reply_subject'}; 


if ($formdata{'message_format'} eq "html") {

$email_format = "$HTML_format";
$mime_body = "$mime_html_body";


}else{

$email_format = "$PLAIN_format";
$mime_body = "$mime_plain_body";

}



### CHECKS SEND TYPE ###

	if ($useLib eq "sendmail") {
        require 'sendmail.cgi';
        &sdsmail;
        }else{
        require 'smtp.cgi';
        &sdsmtp;
        }

}


sub duplicate_email {

      if ($formdata{'success_page'}){
      print "Location: $formdata{'success_page'}\n\n";
      exit;
      }else{
      require 'error.cgi';
      &success;
      }

}


sub LoadFile {
    my($filename) = @_;
    my($result);

    if (! -e $filename) {
        return $result;
    } # if

    open(FILE, "<$filename");
    my(@lines) = <FILE>;
    close FILE;

    my($line);
    foreach $line (@lines) {
        $result = $result . $line;
    } # foreach




for ($indexval = 0; $indexval < @formvalue; $indexval++) {
${'myname'} = $formname[$indexval];
$myname = $formname[$indexval];
${'myvalue'} = $formvalue[$indexval];

    $result =~s/{$myname}/${'myvalue'}/g;

}

    $PERSONAL_time = "$newhour:$newmin";
    $PERSONAL_date = "$monthnameactual/$dayofmonth/$year";

    $result =~s/{%DATE%}/$PERSONAL_date/g;
    $result =~s/{%TIME%}/$PERSONAL_time/g;
    $result =~s/{%TOD%}/$nightday/g;
    $result =~s/{%IP%}/$ENV{'REMOTE_ADDR'}/g;



    return $result;
}



