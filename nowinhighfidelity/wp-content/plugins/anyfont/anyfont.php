<?php
/*
Plugin Name: AnyFont
Plugin URI: http://2amlife.com/projects/anyfont
Description: AnyFont allows you to use any truetype or opentype font for post titles, menu items or anywhere else you want to use a custom font in your theme design.
Author: Ryan Peel
Version: 0.7.3
Author URI: http://2amlife.com/
Text Domain: anyfont

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

!defined('WP_ADMIN_URL') ? define('WP_ADMIN_URL', get_option('siteurl') . '/wp-admin') :0;
!defined('WP_CONTENT_URL') ? define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content') :0;
!defined('WP_CONTENT_DIR') ? define('WP_CONTENT_DIR', ABSPATH . 'wp-content') : 0;
!defined('WP_PLUGIN_URL') ? define('WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins') : 0;
!defined('WP_PLUGIN_DIR') ? define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins') : 0;
define('ANYFONT_ROOT', WP_PLUGIN_DIR."/".basename(dirname( __FILE__ )));
define('ANYFONT_URL', WP_PLUGIN_URL."/".basename(dirname( __FILE__ )));
define('ANYFONT_FONTDIR', WP_CONTENT_DIR."/fonts");
define('ANYFONT_CACHE', WP_CONTENT_DIR."/font-cache");

if(get_option('anyfont_image_module') && get_option('anyfont_image_module') != "auto"){
	$libver = get_option('anyfont_image_module');
} else {
	if(!function_exists('version_compare') || version_compare( phpversion(), '5', '<' )){
		$libver = "php4";
	}else if(!extension_loaded("imagick")) {
		$libver = "php4";
	}else{
		$libver = "php5";
	}
}

define('ANYFONT_LIBDIR', ANYFONT_ROOT."/lib/$libver");

$dont_replace_title = false;

function anyfont_admin_menu(){
	$settings = __("Settings", 'anyfont');
	$fontmanager = __("Manage Fonts", 'anyfont');
	$stylemanager = __("Manage Styles", 'anyfont');
	add_menu_page('AnyFont', 'AnyFont', 8, 'anyfont-settings', 'anyfont_settings_page', ANYFONT_URL."/img/anyfont-icon.png");
	add_submenu_page('anyfont-settings', $settings, $settings, 8, 'anyfont-settings', 'anyfont_settings_page');
	add_submenu_page('anyfont-settings', $fontmanager, $fontmanager, 8, 'anyfont-fonts', 'anyfont_fonts_page');
	add_submenu_page('anyfont-settings', $stylemanager, $stylemanager, 8, 'anyfont-styles', 'anyfont_styles_page');
}

function anyfont_settings_page(){
	require_once(ANYFONT_LIBDIR."/class.admin.php");
	$config = array("page" => "settings", "title" => __("Settings", 'anyfont'));
	$page = new anyfontAdmin($config);
	$page->printPage();
}

function anyfont_fonts_page() {
	require_once(ANYFONT_LIBDIR."/class.admin.php");
	$config = array("page" => "fonts", "title" => __("Font Manager", 'anyfont'));
	$page = new anyfontAdmin($config);
	$page->printPage();
}

function anyfont_styles_page() {
	require_once(ANYFONT_LIBDIR."/class.admin.php");
	$config = array("page" => "styles", "title" => __("Style Manager", 'anyfont'));
	$page = new anyfontAdmin($config);
	$page->printPage();
}

function anyfont_insert_scripts() {
	wp_enqueue_script("anyfont", ANYFONT_URL."/anyfont.js", array('prototype', 'scriptaculous'));
}

function anyfont_install(){

	add_option('anyfont_image_module', 'auto');
	add_option('anyfont_disable_hotlinking', true);

	wp_mkdir_p(ANYFONT_CACHE);
	wp_mkdir_p(ANYFONT_FONTDIR);

	$htaccess_file = ABSPATH.".htaccess";
	$styles_file = ANYFONT_FONTDIR."/styles.ini";

	$home_root = parse_url(get_option('home'));
	$home_root = isset($home_root['path']) ? trailingslashit($home_root['path']) : '/';

	$rules = "# BEGIN AnyFont\n";
	$rules .= "<IfModule mod_rewrite.c>\n";
	$rules .= "RewriteEngine On\n";
	$rules .= "RewriteBase $home_root\n";
	$rules .= "RewriteRule ^images/(.*)/(.*)\.png$ {$home_root}wp-content/plugins/anyfont/img.php [L]\n";
	$rules .= "</IfModule>\n\n";
	$rules .= "# END AnyFont\n\n";

	if(!file_exists($htaccess_file)){
		if(is_writeable(ABSPATH)){
			touch($htaccess_file);
			anyfont_file_put_contents($htaccess_file, $rules);
		}
	} else {
		$orig = file_get_contents($htaccess_file);
		if(!strstr($orig, $rules)){
			anyfont_file_put_contents($htaccess_file, $rules.$orig);
		}
	}

	if(!file_exists($styles_file)){
		touch($styles_file);
		anyfont_write_default_style();
	} /*else {
		//Causes Fatal Error on plugin activation...
		$styles - parse_ini_file($styles_file, true);
		if(!is_array($styles)){
			anyfont_write_default_style();
		} else if(is_array($styles) && !isset($styles['admin'])){
			anyfont_write_default_style($styles);
		}
	}*/
	return true;
}

function anyfont_uninstall(){

	$htaccess_file = ABSPATH.".htaccess";

	if(file_exists($htaccess_file)){
		if(is_writeable($htaccess_file)){
			$contents = file_get_contents($htaccess_file);
			$start = mb_strpos($contents, "# BEGIN AnyFont");
			$end = mb_strpos($contents, "# END AnyFont") + mb_strlen("# END AnyFont");
			if(is_numeric($start)){
				$new_contents = substr_replace($contents, "", $start, $end);
				anyfont_file_put_contents($htaccess_file, $new_contents);
			}
		}
	}
}

function anyfont_write_default_style($styles=false){
	!$styles ? $styles = array("admin" => array()) : 0;
	$styles['admin']['font-name'] = false;
	$styles['admin']['font-size'] = 28;
	$styles['admin']['color'] = "333333";
	$styles['admin']['shadow'] = true;
	$styles['admin']['shadow-color'] = "FFFFFF";
	anyfont_write_styles($styles);
	return true;
}

function anyfont_file_put_contents($file, $data){
	if(is_writeable($file)){
		if(!function_exists('version_compare') || version_compare( phpversion(), '5', '<' )){
			$fw = fopen($file, 'w');
			fwrite($fw, $data);
			fclose($fw);
		} else {
			file_put_contents($file, $data);
		}
		return true;
	}
	return false;
}

function anyfont_serialize_array($array, $prefix = ''){
	$ini = array();
	if( is_array($array)){
		ksort($array);
		foreach ($array as $key => $value){
			// parse data types
			if ($value === true || $value == '1')
				$value = 'true';
			else if ($value === false || $value === '')
				$value = 'false';
			else if (is_string($value) && $key != 'font-size')
				$value = '"' . addslashes($value) . '"';

			// serialize value
			if (!is_array($value) && !is_numeric($key))
				$ini[] = ($prefix ? $prefix . '.' : '') . $key . ' = ' . $value;
			else if (!is_array($value))
				$ini[] = $prefix . '[] = ' . $value;
			else
				$ini = array_merge($ini, anyfont_serialize_array($value, ($prefix ? $prefix . '.' : '') . $key));
		}
		return $ini;
	} else {
		return false;
	}
}

function anyfont_edit_styles(){
	if(isset($_REQUEST['update_style']) && $_REQUEST['update_style'] != ''){
		if(!file_exists(ANYFONT_FONTDIR."/styles.ini")){
			touch(ANYFONT_FONTDIR."/styles.ini");
			anyfont_write_default_style();
		}
		$style = parse_ini_file(ANYFONT_FONTDIR."/styles.ini", true);
		$style[$_REQUEST['update_style']]['font-name'] = $_REQUEST['font-name'];
		$style[$_REQUEST['update_style']]['font-size'] = intval($_REQUEST['font-size']);
		$style[$_REQUEST['update_style']]['color'] = str_replace("#", "", $_REQUEST['color']);
		$style[$_REQUEST['update_style']]['shadow'] = $_REQUEST['shadow'] == 'on' ? true : false;
		$style[$_REQUEST['update_style']]['shadow-color'] =  str_replace("#", "", $_REQUEST['shadow-color']);
		// RW : New attributes
		$style[$_REQUEST['update_style']]['limit-width'] = $_REQUEST['limit-width'] == 'on' ? true : false;
		$style[$_REQUEST['update_style']]['max-width'] = $_REQUEST['max-width'];
		anyfont_write_styles($style);
		if(!isset($_REQUEST['new-style'])){
			$result = array("savestatus" => "saved",
						"stylename" => $_REQUEST['update_style'],
						// RW : Added an @ to the base64_encode to prevent AJAX errors on my prod server while saving an edited style.
						"img" => @base64_encode(file_get_contents(get_option('siteurl')."/images/".urlencode($_REQUEST['update_style'])."/".urlencode($_REQUEST['update_style']).".png"))
					);
		} else {
			require_once(ANYFONT_LIBDIR."/class.admin.php");
			$admn = new anyfontAdmin();
			$style_block = $admn->getStyleBlock($_REQUEST['update_style'], $style[$_REQUEST['update_style']]);
			$result = array("savestatus" => "savedNew", "stylename" => $_REQUEST['update_style'], "styleblock" => $style_block, "msg" => $_REQUEST['update_style']." has been saved.");
		}
	} else {
		$result = array("savestatus" => "failed", "error" => _("Please enter the style name."));
	}
	header("Content-type: application/json");
	if (function_exists('json_encode')) {
		echo json_encode($result);
		exit(0);
	} else {
		require_once(ANYFONT_ROOT.'/lib/class.json.php');
		$JSON = new serviceJSON();
		echo $JSON->encode($result);
		exit(0);
	}
}

function anyfont_write_styles($style){
	ksort($style);
	$sections = array_unique(array_merge(array(''), array_keys($style)));
	$ini = array();
	foreach ($sections as $section) {
		// write sections
		if ($section != '')
			$ini[] = '[' . $section . ']';
			// serialize value array
		if(is_array($style[$section])){
			$ini = array_merge($ini, anyfont_serialize_array($style[$section]));
		}
		$ini[] = '';
	}
	anyfont_file_put_contents(ANYFONT_FONTDIR."/styles.ini", implode("\r\n", $ini));
}

function anyfont_delete_font(){
	if(isset($_REQUEST['font-name'])){
		$font = $_REQUEST['font-name'];
		$file = ANYFONT_FONTDIR."/$font".(!file_exists(ANYFONT_FONTDIR."/$font.ttf") ? ".otf" : ".ttf");
		unlink($file);
		printf(__("%s has been deleted.", 'anyfont'), $font);
		exit(0);
	}else if(isset($_REQUEST['fonts'])){
		$fonts = explode(",", $_REQUEST['fonts']);
		foreach($fonts as $font){
			$file = ANYFONT_FONTDIR."/$font".(!file_exists(ANYFONT_FONTDIR."/$font.ttf") ? ".otf" : ".ttf");
			unlink($file);
		}
		_e("The selected fonts have been deleted.", 'anyfont');
		exit(0);
	}
}

function anyfont_delete_style(){
	$style = parse_ini_file(ANYFONT_FONTDIR."/styles.ini", true);
	if(isset($_REQUEST['styles'])){
		$styles = explode(",", $_REQUEST['styles']);
		foreach($styles as $stylename){
			unset($style[$stylename]);
			$msg = __("The selected styles have been deleted.", 'anyfont');
		}
	}else if(isset($_REQUEST['style-name'])){
		unset($style[$_REQUEST['style-name']]);
		$msg = sprintf(__("%s has been deleted.", 'anyfont'), $_REQUEST['style-name']);
	}
	anyfont_write_styles($style);
	echo $msg;
	exit(0);
}

function anyfont_clear_cache(){
	$cachedir = ANYFONT_CACHE;
	$dir = opendir($cachedir);
	while ($file = readdir($dir)) {
		if(!is_dir($file)){
			unlink($cachedir."/".$file);
		}
	}
	rmdir($cachedir);
	wp_mkdir_p(ANYFONT_CACHE);
	_e("Cache Cleared", 'anyfont');
	exit(0);
}

function anyfont_replace_title($title){
	global $dont_replace_title;
	if(!in_the_loop() || is_feed()){
		return $title;
	}else{
		if(is_page() && $dont_replace_title){
			return $title;
		}
		$uri = explode("/", $_SERVER['REQUEST_URI']);
		$title_replace = !is_page() ? get_option('anyfont_post_title') : get_option('anyfont_page_title');
		if($title_replace && !in_array("wp-admin", $uri)){
			 $style = !is_page() ? get_option('anyfont_post_title_style') : get_option('anyfont_page_title_style');
			 $url = get_option('siteurl');
			 $urltitle = urlencode($title);
			 $style =  urlencode($style);
			 return "<img src=\"$url/images/$style/$urltitle.png\" title=\"$title\" alt=\"$title\" style=\"border: 0pt none ;\"/>";
		}else{
			return $title;
		}
	}
}

function anyfont_replace_tag_title($title){
	$uri = explode("/", $_SERVER['REQUEST_URI']);
	if(get_option('anyfont_tag_title') && !in_array("wp-admin", $uri)){
		remove_filter("single_tag_title", "anyfont_replace_tag_title", 10, 2);
		 $style = get_option('anyfont_tag_title_style');
		 $url = get_option('siteurl');
		 $urltitle = urlencode($title);
		 $style =  urlencode($style);
		 return "<img src=\"$url/images/$style/$urltitle.png\" title=\"$title\" alt=\"$title\" style=\"border: 0pt none ;\"/>";
	}else{
		return $title;
	}
}

function anyfont_replace_cat_title($title){
	$uri = explode("/", $_SERVER['REQUEST_URI']);
	if((is_category() || get_option('anyfont_cat_title')) && !in_array("wp-admin", $uri)){
		remove_filter("single_cat_title", "anyfont_replace_cat_title", 10, 2);
		 $style = get_option('anyfont_cat_title_style');
		 $url = get_option('siteurl');
		 $urltitle = urlencode($title);
		 $style =  urlencode($style);
		 echo "<img src=\"$url/images/$style/$urltitle.png\" title=\"$title\" alt=\"$title\" style=\"border: 0pt none ;\"/>";
	}else{
		return $title;
	}
}

function anyfont_replace_widget_title($params){
	if(get_option('anyfont_widget_title')){
		$style = urlencode(get_option('anyfont_widget_title_style'));
		$url = get_option('siteurl');
		foreach($params as $key => $param){
			$params[$key]['before_title'] = $params[$key]['before_title']."<img src=\"$url/images/$style/";
			$params[$key]['after_title'] = ".png\" />".$params[$key]['after_title'];
		}
	}
	return $params;
}

function anyfont_update_option(){
	if(is_array($_REQUEST['option'])){
		foreach($_REQUEST['option'] as $option){
			if($option == "anyfont_image_module"){
				update_option($option , $_REQUEST[$option]);
			}else {
				$result = $_REQUEST['result'][$option] == "on" ? true : false;
				if($_REQUEST['style'][$option] !== "false"){
					 update_option("{$option}_style", $_REQUEST['style'][$option]);
					 update_option($option , $result);
				 } else {
					 _e("Error: No Style Selected! If you have not created any styles yet, <a href='admin.php?page=anyfont-styles'>click here</a> to go to the style manager now.", 'anyfont');
					 exit(0);
				 }
			 }
		}
		_e("Your settings have been saved!", 'anyfont');
		exit(0);
	}
	_e("Unknown Error Saving Data", 'anyfont');
	exit(0);
}

function anyfont_bloginfo_replace($output, $show){
	switch($show){
		case 'name':
			if(get_option('anyfont_blog_title')){
				$style = get_option('anyfont_blog_title_style');
				$url = get_option('siteurl');
				$urltitle = urlencode($output);
				$style =  urlencode($style);
				!get_option('anyfont_blog_desc') ? remove_filter("bloginfo", "anyfont_bloginfo_replace", 10, 2) : 0;
				return "<img src=\"$url/images/$style/$urltitle.png\" title=\"$output\" alt=\"$output\" style=\"border: 0pt none ;\"/>";
			}else{
				return $output;
			}

		case 'description':
			if(get_option('anyfont_blog_desc')){
				$style = get_option('anyfont_blog_desc_style');
				$url = get_option('siteurl');
				$urltitle = urlencode($output);
				$style =  urlencode($style);
				remove_filter("bloginfo", "anyfont_bloginfo_replace", 10, 2);
				return "<img src=\"$url/images/$style/$urltitle.png\" title=\"$output\" alt=\"$output\" style=\"border: 0pt none ;\"/>";
			}else{
				return $output;
			}

		default:
			return $output;

	}
}

function anyfont_add_header_filter(){
	add_filter("bloginfo", "anyfont_bloginfo_replace", 10, 2);
	add_filter("single_cat_title", "anyfont_replace_cat_title", 10, 2);
	add_filter("single_tag_title", "anyfont_replace_tag_title", 10, 2);
}

register_activation_hook(__FILE__, 'anyfont_install');
register_deactivation_hook(__FILE__, 'anyfont_uninstall');
add_action('wp_ajax_anyfont_edit_styles', 'anyfont_edit_styles');
add_action('wp_ajax_anyfont_delete_font', 'anyfont_delete_font');
add_action('wp_ajax_anyfont_delete_style', 'anyfont_delete_style');
add_action('wp_ajax_anyfont_clear_cache', 'anyfont_clear_cache');
add_action('wp_ajax_anyfont_update_option', 'anyfont_update_option');
add_action('admin_menu', 'anyfont_admin_menu');
add_action("admin_print_scripts", 'anyfont_insert_scripts');
add_filter("the_title", "anyfont_replace_title");
add_action("wp_head", "anyfont_add_header_filter", 10, 0);
add_filter("dynamic_sidebar_params", "anyfont_replace_widget_title");
?>