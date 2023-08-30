#!/usr/bin/perl
## make sure the path to pearl is correct above. ##


###################################################################
#  FILE: success.cgi                                              #
#  © Copyright 2000-2003 Seth Michael Knorr - mail@sethknorr.com  #
#  THIS IS THE SUCCESS MODULE OF THE BIZ MAIL FORM - PROCESSOR    #
###################################################################


$success_file{"1"} = "success.html";
$success_file{"2"} = "success2.html";

## $success_file{"1"} refers to HTML TEMPLATE success page that the CGI script success.cgi should access.   ##
## You can use multiple HTML TEMPLATE success pages for multiple forms.                                     ##
## To add more HTML TEMPLATE success pages simply add the line:                                             ##
## $success_file{"2"} = "success2.html";                                                                    ##
##  -OR-                                                                                                    ##
## $success_file{"3"} = "success3.html";                                                                    ##
##  -OR-                                                                                                    ##
## $success_file{"4"} = "success4.html";                                                                    ##
## AND SO ON.....    5, 6, 7, ETC... Corresponding proportionally                                           ##
##                                                                                                          ##
## To access this page simply change the variable used to call the Corresponding PAGE NUMBER SELECTED       ##
## EXAMPLE: p=1 success.cgi?p=1                                                                             ##
## WOULD USE THE SUCCESS HTML TEMPLATE PAGE COFIGURED ON                                                    ##
## $success_file{"1"} = "success.html";                                                                     ##
##  -OR-                                                                                                    ##
## ANOTHER EXAMPLE: p=2 success.cgi?p=2                                                                     ##
## WOULD USE THE SUCCESS HTML TEMPLATE PAGE COFIGURED ON                                                    ##
## $success_file{"2"} = "success2.html";                                                                    ##
##  -OR-                                                                                                    ##
## ANOTHER EXAMPLE: p=3 success.cgi?p=3                                                                     ##
## WOULD USE THE SUCCESS HTML TEMPLATE PAGE COFIGURED ON                                                    ##
## $success_file{"3"} = "success2.html";                                                                    ##
## ETC......                                                                                                ##
##                                                                                                          ##
##############################################################################################################




#############################################################################################
####                                                                                      ###
####             N O   N E E D   T O   E D I T  V A R I A B L E S   B E L O W             ###
####                                                                                      ###
#############################################################################################


my(@gets) = split(/&/, $ENV{QUERY_STRING});
my($get, $name, $value);
foreach $get (@gets) {
    ($name,$value) = split(/=/, $get);
    $name =~ tr/+/ /;
    $name =~ s/%([a-fA-F0-9][a-fA-F0-9])/pack("C", hex($1))/eg;
    $value =~ tr/+/ /;
    $value =~ s/%([a-fA-F0-9][a-fA-F0-9])/pack("C", hex($1))/eg;
    $value =~ s/<!--(.|\n)*-->//g;
    $GET{$name} = $value;

    push (@getvalue,$value);
    push (@getname,$name);

	%getvalue=@getvalue;
	%getvalue;
	%getname=@getname;
	%getname;
} # foreach


&main;

sub main {

$myPAGE = "$GET{'p'}";
$MYsuccess_file = $success_file{"$myPAGE"};


$body = &LoadFile("$MYsuccess_file");

print "Content-type: text/html\n\n";

print <<"(eot)";

$body

(eot)

exit;

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


for ($indexval = 0; $indexval < @getvalue; $indexval++) {
${'myname'} = $getname[$indexval];
${'myvalue'} = $getvalue[$indexval];

    $result =~s/<%$myname%>/${'myvalue'}/g;

}


    return $result;
}
