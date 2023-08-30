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

class anyfontAdmin {

	var $page;
	var $page_html;
	var $tplPath;
	var $fontlist = array();

	function anyfontAdmin($config=false){
		require_once(ANYFONT_LIBDIR."/class.tpl.php");
		$this->tplPath = ANYFONT_ROOT."/tpl";
		$this->readFontDir();
		if(is_array($config) && isset($config['page'])){
			$this->page = $config['page'];
			$this->page_title = $config['title'];
			$this->get_page();
		}
	}

	function printPage(){
		print($this->page_html);
	}

	function get_page(){
		$url = get_bloginfo('wpurl');
		$jslang_array = array(
			"del_style_note" => __("Please note that once a style is deleted, any generated images that are assosiated with the style will no longer load.", 'anyfont'),
			"chk_del_style" => __("Are you sure you want to delete this style?", 'anyfont'),
			"chk_del_styles" => __("Are you sure you want to delete the selected styles?", 'anyfont'),
			"msg_del_fonts" => __("Deleting Selected Fonts...", 'anyfont'),
			"msg_del_styles" => __("Deleting Selected Styles...", 'anyfont'),
			"msg_del" => __("Deleting", 'anyfont'),
			"err_select_font" => __("No Fonts Selected!", 'anyfont'),
			"err_select_style" => __("No Styles Selected!", 'anyfont'),
			"chk_clear_cache" => __("Are you sure you want to clear the cache?", 'anyfont'),
			"msg_clear_cache" => __("Clearing the cache...", 'anyfont'),
			"msg_no_images" => __("no images", 'anyfont'),
			"msg_upload_success" => __("was uploaded successfully", 'anyfont'),
			"err_upload_failed" => __("Upload Failed:", 'anyfont'),
			"err_saving_style" => __("Save Failed! Please ensure that the font folder and all files inside are writable by the webserver.", 'anyfont'),
			"msg_saved_style" => __("Style has been saved.", 'anyfont'),
			"msg_saving_style" => __("Saving Style...", 'anyfont'),
			"msg_upload_start" => __("Uploading Font...", 'anyfont'),
			"msg_saving_settings" => __("Saving Settings...", 'anyfont')

		);
		if (function_exists('json_encode')) {
			$jslang = json_encode($jslang_array);
		} else {
			require_once(ANYFONT_ROOT.'/lib/class.json.php');
			$JSON = new serviceJSON();
			$jslang = $JSON->encode($jslang_array);
		}
		$this->tpl = new fastTPL($this->tplPath);
		$this->tpl->define(array($this->page => "{$this->page}.html",
							"header" => "header.html",
							"footer" => "footer.html",
						));
		$this->tpl->assign("STYLE", file_get_contents(ANYFONT_ROOT."/styles.css"));
		$this->tpl->assign("URL", $url."/");
		$this->tpl->assign("JSLANG", $jslang);
		$this->tpl->assign("ICON_CLASS", $this->page);
		$this->tpl->assign("PAGE_TITLE", "AnyFont - ".$this->page_title);
		$this->tpl->assign("HEADER", $this->tpl->fetchParsed("header"));
		switch($this->page){
			case 'settings':
				$this->getSettings();
				break;

			case 'fonts':
				$this->getFonts();
				break;

			case 'styles':
				$this->getStyles();
				break;
		}
		$this->tpl->assign("FOOTER", $this->tpl->fetchParsed("footer"));
		$this->page_html =  $this->tpl->fetchParsed($this->page);
	}

	function getSettings(){
		$cachedir = ANYFONT_CACHE;
		$totalsize = 0;
		$imagecount = 0;
		if(!is_dir($cachedir)){
			return false;
		} else {
			$dir = opendir($cachedir);
			while ($file = readdir($dir)) {
				$fileinfo = explode(".", $file);
				if ($fileinfo[1] == "png") {
					$imagecount++;
					$totalsize += filesize($cachedir."/".$file);
				}
			}
		}
		$imageplural = $imagecount == 1 ? " image" : " images";
		$isare = $imagecount == 1 ? "is" : "are";
		$imagecount == 0 ? $imagecount = "no" : 0;
		$opt = array("auto(Default)" => "auto", "gd" => "php4", "imagick" => "php5");
		$imageopt = "";
		foreach($opt as $mod => $val){
			$selected = ($val == get_option('anyfont_image_module')) ? "selected=\"selected\"" : "";
			if($val == "auto"){
				$imageopt .= "<option value=\"$val\" $selected>$mod</option>";
			}else if(extension_loaded($mod)){
				$imageopt .= "<option value=\"$val\" $selected>$mod module</option>";
			}
		}

		$this->isReplaceEnabled(get_option('anyfont_page_title'), "PAGE");
		$this->isReplaceEnabled(get_option('anyfont_post_title'), "POST");
		$this->isReplaceEnabled(get_option('anyfont_cat_title'), "CAT");
		$this->isReplaceEnabled(get_option('anyfont_tag_title'), "TAG");
		$this->isReplaceEnabled(get_option('anyfont_widget_title'), "WIDGET");
		$this->isReplaceEnabled(get_option('anyfont_blog_title'), "BLOG_TITLE");
		$this->isReplaceEnabled(get_option('anyfont_blog_desc'), "BLOG_DESC");
		$this->isReplaceEnabled(get_option('anyfont_disable_hotlinking'), "DISABLE_HOTLINKING");
		$this->tpl->assign("REPLACE_H2", __("Replace Text Titles", 'anyfont'));
		$this->tpl->assign("IMAGE_H2", __("Image Cache", 'anyfont'));
		$this->tpl->assign("ADVANCED_H2", __("Advanced Settings", 'anyfont'));
		$this->tpl->assign("ADVANCED_MSG", __("It is recommended that the following options are left on their default settings.", 'anyfont'));
		$this->tpl->assign("HELP_MSG", __("To replace certain plain text titles with images in your wordpress blog, enable the appropriate section below and then assign it one of your <a href=\"{URL}\">styles</a>.", 'anyfont'));
		$this->tpl->assign("SEO_NOTE", __("The post title, page title, blog name and blog description image replacements are SEO compatible.", 'anyfont'));
		$this->tpl->assign("DISCLAIMER", __("PLEASE NOTE:<br /> These options may not be compatible with ALL themes and/or custom widgets.<br />If your theme already uses a custom image for the title, it is NOT advisable to enable the blog name option.", 'anyfont'));
		$this->tpl->assign("DISCLAIMER_CONT", __("If you encounter any problems, Please check the FAQ for known issues with certain themes, check with the developer of your theme, or <a href=\"http://2amlife.com/contact\" target=\"_blank\">contact me</a> for assistance.", 'anyfont'));
		$this->tpl->assign("POST_TITLE_STYLES", $this->titleStyleConfig( get_option('anyfont_post_title_style')));
		$this->tpl->assign("PAGE_TITLE_STYLES", $this->titleStyleConfig(get_option('anyfont_page_title_style')));
		$this->tpl->assign("TAG_TITLE_STYLES", $this->titleStyleConfig(get_option('anyfont_tag_title_style')));
		$this->tpl->assign("CAT_TITLE_STYLES", $this->titleStyleConfig(get_option('anyfont_cat_title_style')));
		$this->tpl->assign("WIDGET_TITLE_STYLES", $this->titleStyleConfig(get_option('anyfont_widget_title_style')));
		$this->tpl->assign("BLOG_TITLE_STYLES", $this->titleStyleConfig(get_option('anyfont_blog_title_style')));
		$this->tpl->assign("BLOG_DESC_STYLES", $this->titleStyleConfig(get_option('anyfont_blog_desc_style')));
		$this->tpl->assign("IMAGE_MODULE_OPTIONS", $imageopt);
		$this->tpl->assign("URL", WP_ADMIN_URL."/admin.php?page=anyfont-styles");
		$this->tpl->assign("IMAGE_COUNT", $imagecount.$imageplural);
		$this->tpl->assign("ISARE", $isare);
		$this->tpl->assign("TOTAL_SIZE", $this->bytecalc($totalsize));
	}

	function isReplaceEnabled($section, $assign){
		if($section){
			$this->tpl->assign("{$assign}_CHECKED", "checked=\"checked\"");
			$this->tpl->assign("{$assign}_ON_CLASS", " anyfont_checkbox_on");
		} else {
			$this->tpl->assign("{$assign}_DISABLED", "disabled=\"disabled\"");
		}
	}

	function titleStyleConfig($section){
		$styles = parse_ini_file(ANYFONT_FONTDIR."/styles.ini", true);
		$styleoptions = '';
		foreach($styles as $style => $option){
			if($style != "admin"){
				$selected = $style == $section ? "selected=\"selected\"" : '';
				$styleoptions .= "<option value=\"$style\" $selected>$style</option>";
			}
		}
		return $styleoptions;
	}

	function getFonts(){
		$list = "";
		$tpl = new fastTPL($this->tplPath);
		$tpl->define(array("font_block" => "fonts-block.html"));
		foreach($this->fontlist as $font){
			$urlname = urlencode($font['name']);
			$tpl->assign('FONT_NAME', $font['name']);
			$tpl->assign('FONT_URL', get_bloginfo('wpurl')."/images/admin/$urlname.png");
			$tpl->assign('DELETE_ICON', ANYFONT_URL."/img/icon-delete.png");
			$list .= $tpl->fetchParsed("font_block");
		}
		$this->tpl->assign("FONTS", $list);
		$this->tpl->assign("UPLOAD_URL", ANYFONT_URL."/upload.php");
	}

	function readFontDir() {
		$fontdir = ANYFONT_FONTDIR;

		$this->fontlist = array();
		if(!is_dir($fontdir)){
			return false;
		} else {
			$dir = opendir($fontdir);
			while ($file = readdir($dir)) {
				$fileinfo = explode(".", $file);
				if ($fileinfo[1] == "ttf" || $fileinfo[1] == "otf") {
					array_push($this->fontlist, array("name" => $fileinfo[0], "type" => $fileinfo[1]));
				}
			}
		}
	}

	function getStyles(){
		$styles = "";
		$settings = parse_ini_file(ANYFONT_FONTDIR."/styles.ini", true);
		foreach($settings as $style => $option){
			if($style != "admin"){
				$styles.=$this->getStyleBlock($style, $option);
			}
		}
		$new_style = "<label for=\"color\">color</label><input type='text' class=\"colorinput\" name='color' id='color' value='#000000' /><br /><br />";
		$new_style .= "<label for=\"font-name\">font-name</label><select name=\"font-name\">";
		foreach($this->fontlist as $font){
			$new_style .= "<option value=\"{$font['name']}\">{$font['name']}</option>";
		}
		$new_style .= "</select><br /><br />";
		$sizes = array("7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "18", "22", "24", "28", "36", "40", "44", "48", "54", "60", "72");
		$new_style .= "<label for=\"font-size\">font-size</label><select name=\"font-size\">";
		foreach($sizes as $size){
			$new_style .= "<option value=\"$size\">{$size}pt</option>";
		}
		$new_style .= "</select><br /><br />";
		// Contribution from RW
		// Added new checkbox styles - RP
		$new_style .= "<div class=\"anyfont_checkbox\" title=\"Click to enable/disable this option.\"><label for=\"#\">limit-width</label><input id=\"limit-width\" type=\"checkbox\" name=\"limit-width\" onclick=\"AnyFont.toggleHidden(this)\" /><br/><br /></div>";
		$new_style .= "<div class='hidden_option' style='display:none;'><label for=\"max-width\">max-width</label><input type='text' name='max-width' id='max-width' value='50' /><em>(character limit)</em><br /><br /></div>";
		// End
		$new_style .= "<input id=\"shadow\" type=\"hidden\" name=\"shadow\" value=\"true\">";
		$new_style .= "<label for=\"shadow-color\">background-color</label><input type=\"text\" class=\"colorinput\" name=\"shadow-color\" id=\"shadow-color\" value=\"#FFFFFF\" /><br /><br />";
		$new_style .= "<input id=\"submit_style\" class=\"button-primary button-save\" type=\"button\" value=\"save\" onclick=\"AnyFont.updateStyle('anyfont-style-new-form');\"><input type=\"button\" name=\"cancel\" value=\"Cancel\" class=\"button-secondary\" style=\"float:right;margin-right:5px;\" onclick=\"AnyFont.toggleNew('anyfont-style-new');\"/></form>";
		$this->tpl->assign("NEW_STYLE_FORM", $new_style);
		$this->tpl->assign("STYLES", $styles);
	}

	function getStyleBlock($style, $option){
		$url = get_bloginfo('wpurl');
		count($this->fontlist) == 0 ? $this->readFontDir() : 0;
		$tpl = new fastTPL($this->tplPath);
		$tpl->define(array("styles_block" => "styles_block.html"));
		$tpl->assign('STYLE_NAME', $style);
		$tpl->assign('STYLE_NAME_ID', str_replace(" ", "_", $style));
		$tpl->assign('PREVIEW_URL', $url."/images/$style/$style.png");
		$options = "<form id=\"$style\" class=\"anyfont_style_settings\"><input type=\"hidden\" value=\"$style\" name=\"update_style\" />";
		!isset($option['limit-width']) ? $option['limit-width'] = false : 0;
		!isset($option['max-width']) ? $option['max-width'] = 50 : 0;
		foreach($option as $name => $value){
			$for = $style."_$name";
			if($name == 'max-width'){
				$styling = !$option['limit-width'] ? "display:none;" : "";
				$options .= "<div class=\"hidden_option\" style=\"$styling\">";
			}
			if($name == 'limit-width'){
				$option_on = !$option['limit-width'] ? "" : " anyfont_checkbox_on";
				$options .= "<div class=\"anyfont_checkbox$option_on\">";
				$for = "#";
			}
			if($name == "shadow-color"){
				$options .= "<label for='$for'>background-color</label>";
			} else if($name == "shadow"){ } else {
				$options .= "<label for='$for'>$name</label>";
			}

			switch($name){
				case "color":
					$options .= "<input type='text' class=\"colorinput\" name='$name' id='{$style}_$name' value='#$value' /><br /><br />";
					break;

				case "font-name":
					$options .= "<select id='{$style}_$name' name=\"$name\">";
					foreach($this->fontlist as $font){
						$selected = ($value == $font['name']) ? "selected=\"selected\"" : "";
						$options .= "<option value=\"{$font['name']}\" $selected >{$font['name']}</option>";
					}
					$options .= "</select><br /><br />";
					break;

				case "font-size":
					$sizes = array("7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "18", "22", "24", "28", "36", "40", "44", "48", "54", "60", "72");
					$options .= "<select id='{$style}_$name' name=\"$name\">";
					foreach($sizes as $size){
						$selected = $value == $size ? "selected=\"selected\"" : "";
						$options .= "<option value='$size' $selected >{$size}pt</option>";
					}
					$options .= "</select><br /><br />";
					break;

				case "shadow-color":
					$options .= "<input  type='text' class=\"colorinput\" name='$name' id='{$style}_$name' value='#$value' /><br /><br />";
					break;

				// Contribution from RW
				case "limit-width":
					$checked = !$value ? "" : "checked=\"checked\"";
					$options .= "<input id='{$style}_$name' type=\"checkbox\" name=\"$name\" $checked><br /><br /></div>";
					break;

				case "max-width":
					$options .= "<input type='text' name='$name' id='{$style}_$name' value='$value' /><em>(character limit)</em><br /><br /></div>";
				// End
			}
		}
		$options .= "<input id=\"submit_style\" class=\"button-primary button-save\" type=\"button\" value=\"save\" onclick=\"AnyFont.updateStyle('$style')\"></form>";
		$tpl->assign('DELETE_ICON', ANYFONT_URL."/img/icon-delete.png");
		$tpl->assign('EDIT_ICON', ANYFONT_URL."/img/icon-edit.png");
		$tpl->assign('OPTIONS', $options);
		return $tpl->fetchParsed("styles_block");
	}

	function bytecalc($bytes, $base10=false, $round=0){
        $labels=array(' bytes', ' kB', ' MB', ' GB');

        if (($bytes <= 0) || (! is_array($labels)) || (count($labels) <= 0))
            return null;

        $step = $base10 ? 3 : 10 ;
        $base = $base10 ? 10 : 2;

        $log = (int)(log10($bytes)/log10($base));

        krsort($labels);

        foreach ($labels as $p=>$lab) {
            $pow = $p * $step;
            if ($log < $pow) continue;
            $text = $bytes." bytes (".round($bytes/pow($base,$pow),$round) . $lab.")";
            break;
        }
        return $text;
    }
}