<?php 

/* MusicTicker - XML version 1.4.1                           */ 
/* MAD props to Tom Pepper and Tag Loomis for all their help */ 
/* --------------------------------------------------------- */ 
/* SCXML configuration version 0.4.1                         */ 
/* June 30 2003 11:19 EST                                    */ 

//manditory config items 
$host = "kara.fast-serv.com"; // host or ip of shoutcast server 
$port = "9326"; // port of shoutcast server 
$password = "fidelity"; // password for shoutcast server 
$maxsongs = "20"; // max number of songs to display (max is 20) 
$rfrshrate = "30"; // reload rate of page 
$timeat = "0"; // display starttime (0) or endtime (1) 

//gui config items 
$bodybgcolor = "#FFFFFF"; 
$bodytext = "#000000"; 
$bodylink = "#663300"; 
$bodyvlink = "#663300"; 
$bordercolor = "#FFFFFF"; 
$csscolor = "#708fbe"; 
$font = "Arial, Helvetica"; 
$align = "center"; 

//master table color scheme 
$mstrtext = "#000000"; 
$mstrbgcolor = "#FFFFFF"; 

//lead table color scheme 
$tbl1bgcolor1 = "#ddddaa"; 
$tbl1bgcolor2 = "#ffffff"; 
$tbl1text = "#663300"; 

//content table color scheme 
$tbl2bgcolor1 = "#ddddaa"; 
$tbl2bgcolor2 = "#ffffff"; 
$tbl2bgcolor3 = "#EEEEEE"; 
$tbl2text1 = "#663300"; 
$tbl2text2 = "#000000"; 

//error screen color scheme 
$errorbgcolor = "#f0f6fb"; 
$errortext = "#708fbe"; 

//On screen messages 
$pgtitle = "WCRM: Citizens' Radio Music"; 
$header = "WCRM: Citizens' Radio Music: Now Playing"; 
$timezone = "EST (-0500 GMT)"; 
$errormsg1 = "Sorry, The Server Is Currently Down"; 
$errormsg2 = "It Will Be Back Up ASAP !!!"; 
$dsperror1 = "Sorry, The Server Is Currently Unable To Retreive It's Source"; 
$dsperror2 = "It Will Be Back Up ASAP !!!"; 

$error1 = "<html>\n<head>\n</head>\n<body bgcolor=\"$errorbgcolor\">\n<h2>\n<p align=\"center\"><font color=\"$errortext\">$errormsg1</p>\n<br>\n<p align=\"center\">$errormsg2</p>\n</font>\n</h2>\n</body>\n</html>"; 
$error2 = "<html>\n<head>\n</head>\n<body bgcolor=\"$errorbgcolor\">\n<h2>\n<p align=\"center\"><font color=\"$errortext\">$dsperror1</p>\n<br>\n<p align=\"center\">$dsperror2</p>\n</font>\n</h2>\n</body>\n</html>"; 

?> 