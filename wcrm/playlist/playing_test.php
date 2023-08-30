<?php 

/* MusicTicker - XML version 1.4.1                           */ 
/* MAD props to Tom Pepper and Tag Loomis for all their help */ 
/* --------------------------------------------------------- */ 
/* SCXML reference version 0.4.1                             */ 
/* June 30 2003 11:19 EST                                    */ 

error_reporting (E_ALL ^ E_NOTICE); 

require "scxml-obj.php"; 
require "config.php"; 

$serv1 = new SCXML; 

$serv1->set_host("$host"); 
$serv1->set_port("$port"); 
$serv1->set_password("$password"); 

if (!$serv1->retrieveXML()) DIE ("$error1"); 

$con_dsp=$serv1->fetchMatchingTag("STREAMSTATUS"); 
if (!$con_dsp == "1") DIE ("$error2"); 

$cur_listen=$serv1->fetchMatchingTag("CURRENTLISTENERS"); 
if ($cur_listen == "") { 
    $cur_listen = 0; 
    } 
$peak_listen=$serv1->fetchMatchingTag("PEAKLISTENERS"); 
$max_listen=$serv1->fetchMatchingTag("MAXLISTENERS"); 
$title=$serv1->fetchMatchingTag("SERVERTITLE"); 
$song_title=$serv1->fetchMatchingTag("SONGTITLE"); 
$con_hostname=$serv1->fetchMatchingArray("HOSTNAME"); 
$con_listen=$serv1->fetchMatchingArray("CONNECTTIME"); 
$con_song=$serv1->fetchMatchingArray("TITLE"); 
$con_song_print=array_slice($con_song, 1, $maxsongs); 
$con_time=$serv1->fetchMatchingArray("PLAYEDAT"); 
if (preg_match ("/^[0-9]{10}$/", $con_time[0])) { 
   for ($i=0; $i<count($con_time); $i++) { 
    $con_time[$i] = date('H:i:s', $con_time[$i]); 
       } 
   $playtime = $con_time; 
  } 
else { 
   $playtime = $con_time; 
  } 

if ($timeat == "0") { 
      $playat = array_shift ($playtime); 
   } else { 
      $playtime = $playtime; 
   } 

  echo "<html>\n"; 
  echo "<META HTTP-EQUIV=\"REFRESH\" CONTENT=\"$rfrshrate\">\n"; 
  echo "<head>\n"; 
  echo " <title>$pgtitle</title>\n"; 
  echo "     <style type=\"text/css\">"; 
  echo "        .SongTitle    { color: $csscolor;}    A.SongTitle    { color: $csscolor; }    A:hover.SongTitle    { color: $csscolor; }";
  echo "       .odd{background-color: white;}";
  echo "       .even{background-color: gray;}";
  echo "    </style>";
  echo " <script type=\"text/javascript\">";
  echo " function alternate(id){ ";
  echo " if(document.getElementsByTagName){  ";
  echo " var table = document.getElementById(id); ";  
  echo " var rows = table.getElementsByTagName(\"tr\");  "; 
  echo " for(i = 0; i < rows.length; i++){   ";        
  echo " //manipulate rows ";
  echo "  if(i % 2 == 0){ ";
  echo "   rows[i].className = \"even\"; ";
  echo "  }else{ ";
  echo "   rows[i].className = \"odd\"; ";
  echo "  }     ";  
  echo " } ";
  echo " } ";
  echo " }";
  echo "  </script>";
  echo "</head>\n"; 
  echo "\n"; 
  echo "<body onload=\"alternate('thetable')\" bgcolor=\"$bodybgcolor\" text=\"$bodytext\" link=\"$bodylink\" vlink=\"$bodyvlink\">\n"; 
  echo "<p align=\"$align\">\n"; 
//Start of Master Table 
  echo " <table bgcolor=\"$mstrbgcolor\" width=\"570\" border=\"0\" cellspacing=\"0\" cellpadding=\"5\" bordercolor=\"$bordercolor\">\n"; 
  echo "  <tr>\n"; 
  echo "  <td>\n";   
  echo "    <p>\n"; 
//Start of Lead Table 
  echo "    <table id=\"thetable\" width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\" bordercolor=\"$bordercolor\">\n"; 
  echo "      <tr>\n"; 
  echo "        <td bgcolor=\"$tbl1bgcolor1\">\n"; 
  echo "         <p align=center>\n"; 
  echo "          <b><font face=\"$font\" size=\"2\" color=\"$tbl1text\">Currently Playing Track:</font></b>\n"; 
  echo "         </p>\n"; 
  echo "        </td>\n"; 
  echo "      </tr>\n"; 
  echo "      <tr>\n"; 
  echo "        <td bgcolor=\"$tbl1bgcolor2\">\n"; 
  echo "         <p align=center>\n"; 
  echo "          <font face=\"$font\" size=\"2\"><a href=\"http://$host:$port/listen.pls\" class=\"SongTitle\">$song_title</a></font>\n"; 
  echo "         </p>\n"; 
  echo "        </td>\n"; 
  echo "      </tr>\n"; 
  echo "    </table>\n"; 
//End of Lead Table 
  echo "    <p>\n"; 
//Start of Content Table 
  echo "    <table id=\"thetable\" width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\" bordercolor=\"$bordercolor\">\n"; 
  echo "      <tr>\n"; 
  echo "        <td width=\"15%\" bgcolor=\"$tbl2bgcolor1\">\n"; 
  echo "          <b><font face=\"$font\" size=\"2\" color=\"$tbl2text1\">Time:</font></b>\n"; 
  echo "        </td>\n"; 
  echo "        <td width=\"85%\" colspan=\"2\" bgcolor=\"$tbl2bgcolor1\">\n"; 
  echo "          <b><font face=\"$font\" size=\"2\" color=\"$tbl2text1\">Last $maxsongs Tracks Played:</font></b>\n"; 
  echo "        </td>\n"; 
  echo "      </tr>\n"; 
while(list($key,$val) = each($con_song_print)) { 
  echo "      <tr>\n"; 
  echo "        <td width=\"15%\" bgcolor=\"$tbl2bgcolor3\">\n"; 
  echo "          <font face=\"$font\" size=\"2\" color=\"$tbl2text2\">$playtime[$key]</font>\n"; 
  echo "        </td>\n"; 
  echo "        <td width=\"85%\" bgcolor=\"$tbl2bgcolor2\">\n"; 
  echo "          <font face=\"$font\" size=\"2\" color=\"$tbl2text2\">$con_song_print[$key]</font>\n"; 
  echo "        </td>\n"; 
  echo "      </tr>\n"; 
} 
  echo "    </table>\n"; 
//End of Content Table 
  echo "  </td>\n"; 
  echo "  </tr>\n"; 
  echo " </table>\n"; 
//End of Master Table 
//Start of Shoutcast Logo 
//End of Shoutcast Logo 
  echo "</body>\n"; 
  echo "</html>"; 

?> 
