<?php
/*
    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor,
    Boston, MA  02110-1301, USA.
    ---
    Copyright (C) 2009, Ryan Peel ryan@2amlife.com
 */


define('DOC_ROOT', getcwd());
define('FONT_DIR', realpath('./../../fonts'));
define('CACHE_DIR', realpath('./../../font-cache'));

$path = realpath('./../../../')."/";

if ( file_exists( $path.'wp-config.php') ) {
	/** The config file resides in ABSPATH */
	require_once( $path.'wp-config.php' );
} elseif ( file_exists( dirname($path) . '/wp-config.php' ) ) {
	/** The config file resides one level below ABSPATH */
	require_once( dirname($path) . '/wp-config.php' );
} else {
	header("HTTP/1.0 500 Server Error");
	exit(0);
}
if($_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR']){
	$site_url = parse_url(get_option('siteurl'));
	$referer = parse_url($_SERVER['HTTP_REFERER']);
	if(!strstr($site_url['host'], $referer['host']) && get_option('anyfont_disable_hotlinking')){
		wp_redirect(get_option('siteurl'));
		exit(0);
	}
}

if(get_option('anyfont_image_module') && get_option('anyfont_image_module') != "auto"){
	$libver = get_option('anyfont_image_module');
} else {
	if(!function_exists('version_compare') || version_compare( phpversion(), '5', '<' )){
		$libver = "php4";
	}else if(!extension_loaded("imagick")) {
		if(!extension_loaded("gd")){
			echo "This script requires either one of the GD or Imagick image modules installed to run correctly.";
			exit(0);
		}else{
			$libver = "php4";
		}
	}else{
		$libver = "php5";
	}
}

define('ANYFONT_LIBDIR', DOC_ROOT."/lib/$libver");

$req_vars = explode( "/", $_SERVER['REQUEST_URI']);

foreach($req_vars as $n => $v){
	if($v == "images"){
		$sn = $n;
		continue;
	}
}

$style = urldecode($req_vars[($sn+1)]);
$text = str_replace(".png", "", html_entity_decode(urldecode($req_vars[($sn+2)])));

if(!isset($text) || !isset($style)){
	wp_redirect(get_option('siteurl'));
	exit(0);
}else{
	$gzip = isset($HTTP_SERVER_VARS['HTTP_ACCEPT_ENCODING']) ? strstr($HTTP_SERVER_VARS['HTTP_ACCEPT_ENCODING'], 'gzip') ? 1 : 0 : 0;
	require_once(ANYFONT_LIBDIR."/class.image.php");
	new ttfImage($text, $style, $gzip);
}

?>
