<?php 

/* MusicTicker - XML version 1.4.1                           */ 
/* MAD props to Tom Pepper and Tag Loomis for all their help */ 
/* --------------------------------------------------------- */ 
/* SCXML object version 0.4.1                                */ 
/* June 30 2003 11:19 EST                                    */ 

error_reporting (E_ALL ^ E_NOTICE); 

class SCXML { 

  var $host="kara.fast-serv.com"; // host or ip of shoutcast server 
  var $port="9326"; // port of shoutcast server 
  var $password="fidelity"; // password for shoutcast server 

/* DO NOT CHANGE ANYTHING FROM THIS POINT ON - THIS MEANS YOU !!! */ 

  var $depth = 0; 
  var $lastelem= array(); 
  var $xmlelem = array(); 
  var $xmldata = array(); 
  var $stackloc = 0; 

  var $parser; 

  function set_host($host) { 
    $this->host=$host; 
  } 

  function set_port($port) { 
    $this->port=$port; 
  } 

  function set_password($password) { 
    $this->password=$password; 
  } 

  function startElement($parser, $name, $attrs) { 
    $this->stackloc++; 
    $this->lastelem[$this->stackloc]=$name; 
    $this->depth++; 
  } 

  function endElement($parser, $name) { 
    unset($this->lastelem[$this->stackloc]); 
    $this->stackloc--; 
  } 

  function characterData($parser, $data) { 
    $data=trim($data); 
    if ($data) { 
      $this->xmlelem[$this->depth]=$this->lastelem[$this->stackloc]; 
      $this->xmldata[$this->depth].=$data; 
    } 
  } 

  function retrieveXML() { 
    $rval=1; 

    $sp=@fsockopen($this->host,$this->port,&$errno,&$errstr,10); 
    if (!$sp) $rval=0; 
    else { 

      set_socket_blocking($sp,false); 

      // request xml data from sc server 

      fputs($sp,"GET /admin.cgi?pass=$this->password&mode=viewxml HTTP/1.1\nUser-Agent:Mozilla\n\n"); 

      // if request takes > 15s then exit 

      for($i=0; $i<30; $i++) { 
    if(feof($sp)) break; // exit if connection broken 
    $sp_data.=fread($sp,31337); 
    usleep(500000); 
      } 

      // strip useless data so all we have is raw sc server XML data 

      $sp_data=ereg_replace("^.*<!DOCTYPE","<!DOCTYPE",$sp_data); 

      // plain xml parser 

      $this->parser = xml_parser_create(); 
      xml_set_object($this->parser,&$this); 
      xml_set_element_handler($this->parser, "startElement", "endElement"); 
      xml_set_character_data_handler($this->parser, "characterData"); 

      if (!xml_parse($this->parser, $sp_data, 1)) { 
    $rval=-1; 
      } 

      xml_parser_free($this->parser); 

    } 
    return $rval; 
  } 

  function debugDump(){ 
    reset($this->xmlelem); 
    while (list($key,$val) = each($this->xmlelem)) { 
      echo "$key. $val -> ".$this->xmldata[$key]."\n"; 
    } 

  } 

  function fetchMatchingArray($tag){ 
    reset($this->xmlelem); 
    $rval = array(); 
    while (list($key,$val) = each($this->xmlelem)) { 
      if ($val==$tag) $rval[]=$this->xmldata[$key]; 
    } 
    return $rval; 
  } 

  function fetchMatchingTag($tag){ 
    reset($this->xmlelem); 
    $rval = ""; 
    while (list($key,$val) = each($this->xmlelem)) { 
      if ($val==$tag) $rval=$this->xmldata[$key]; 
    } 
    return $rval; 
  } 

} 

?> 
