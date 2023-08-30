

## CONFIGURE WHICH VARIABLES ARE WHICH ##


$IS_selected = " selected";
$IS_checked = " checked";
$IS_required = " style=\"background: #ffff00\"";
$bold = "<b>";
$endbold = "</b>";

## IF REQUIRED MAKE <%REQ%FIELD%> ##
## IF REQUIRED MAKE BOLD <%B%FIELD%> ##
## IF REQUIRED MAKE END BOLD <%EB%FIELD%> ##


## START ERROR PAGE SUB ROUTINES ##


sub REQ_format_error {

$body = &LoadFORMFile("$USE_error_page");

for ($indexreq = 0; $indexreq < @required; $indexreq++) {
$myrequired = $required[$indexreq];
$myformreq = $formdata{"$myrequired"};

if ($required_desc[$indexreq]) {
$missing_desc = "$required_desc[$indexreq]";
}else{
$missing_desc = "$myrequired";
}


if ($myrequired eq "email") {

	if (!$myformreq || $myformreq =~ /(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/ || $myformreq !~ /^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z0-9]+)(\]?)$/){

$REQUIRED{"$indexreq"} = "${'LISTITEMS'}<li> $missing_desc Incorrectly Formatted";
${'LISTITEMS'} = $REQUIRED{"$indexreq"};
	
	}

} ## END IF $myrequired eq "email" ##

elsif (!$myformreq){

$REQUIRED{"$indexreq"} = "${'LISTITEMS'}<li> $missing_desc";
${'LISTITEMS'} = $REQUIRED{"$indexreq"};

}
}


$title = "<title>Missing Form Fields!</title>";
$errormessage = "<h2>Missing Form Fields!</h2><b>The Below Required Fields Where Left Blank:</b><br><br>${'LISTITEMS'}<br><br><b>Fill in all missing fields below and resubmit the form:</b><br>";
               $body =~ s|<%TITLE%>|$title|g;
               $body =~ s|<%ERROR%>|$errormessage|g;


print "Content-type: text/html\n\n";

	print <<"MyformatERROR";

$body


MyformatERROR

exit;

}




sub REQ_missing_email {

$body = &LoadFORMFile("$USE_error_page");


$title = "<title>Missing or invalid format of email!</title>";
$errormessage = "<h2>Missing or invalid format of email!</h2><b>The email Field must be filled in and in the proper format!</b><br><br><b>Fill in all missing fields below and resubmit the form:</b><br>";
               $body =~ s|<%TITLE%>|$title|g;
               $body =~ s|<%ERROR%>|$errormessage|g;

print "Content-type: text/html\n\n";

	print <<"missingemailERROR";

$body


missingemailERROR

exit;

}

## END ERROR PAGE SUB ROUTINES ##



sub LoadFORMFile {
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
$r_myname = $formname[$indexval];
${'r_myname'} = $formname[$indexval];
$r_myvalue = $formvalue[$indexval];
${'r_myvalue'} = $formvalue[$indexval];


## START ALL CHECKED AND SELECTED VALUES  CHECK ##


## CHECK IF SELECTED ##

#    if ($find =~ /<%SEL%$r_myname {(\w+)}%>/) {
#        $sub[$r_myname] = $1;
#
#		if ($sub[$r_myname] eq "$r_myvalue") {
#		$result =~s/<%SEL%$r_myname {$r_myvalue}%>/$IS_selected/g;
#                $pths = "$pths $sub[$r_myname]";
#		}
#     }

$result =~s/<%SEL%$r_myname {$r_myvalue}%>/$IS_selected/g;


$result =~s/<%SEL%$r_myname {(.*)}%>//g;  ## REPLACE ALL NON MATCHING SELECTED ##

## END CHECK IF SELECTED ##


## CHECK IF CHECKED ##

if (! $r_myvalue) {

$result =~s/<%CK%$r_myname%>//g;

}else{

$result =~s/<%CK%$r_myname%>/$IS_checked/g;

}

## END ALL CHECKED AND SELECTED VALUES  CHECK ##


## CHECK REQUIRED  REPLACE ##

	for ($indexreq = 0; $indexreq < @required; $indexreq++) {
	$REQ_name = $required[$indexreq];
	$REQ_value = $formdata{"$REQ_name"};



   ## CK IF REQUIRED IS EMAIL ##
   if ($REQ_name eq "$r_myname" && $REQ_name eq "email") {
	if (!$r_myvalue || $r_myvalue =~ /(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/ || $r_myvalue !~ /^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z0-9]+)(\]?)$/){
	$result =~s/<%REQ%$r_myname%>/$IS_required/g;
	$result =~s/<%B%$r_myname%>/$bold/g;
	$result =~s/<%EB%$r_myname%>/$endbold/g;
	$result =~s/<%$r_myname%>/$r_myvalue/g;
	}
   }

		if ($REQ_name eq "$r_myname" && ! $r_myvalue){

		$result =~s/<%REQ%$r_myname%>/$IS_required/g;

		$result =~s/<%B%$r_myname%>/$bold/g;

		$result =~s/<%EB%$r_myname%>/$endbold/g;

		}

	} ## END FOR LOOP ##

## END CHECK REQUIRED  REPLACE ##



## REPLACE BELOW WITH NOTHING IN CASE VALUE NOT FOUND ##

  $result =~s/<%REQ%$r_myname%>//g;
  $result =~s/<%B%$r_myname%>//g;
  $result =~s/<%EB%$r_myname%>//g;


  $result =~s/<%$r_myname%>/$r_myvalue/g;


} ## END THE MAIN FOR LOOP FOR EACH POSTED VARIABLE ##


$result =~s/<%CK%(.*)%>//g;  ## MAKE SURE REPLACE ALL NULL CHECK BOXES ##
$result =~s/<%SEL%(.*){(.*)}%>//g;  ## REPLACE ALL NON MATCHING SELECTED ##

    return $result;
} ## end sub LoadFORMFile ##




return 1;

