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

class ttfImage {

	var $text;
	var $style;
	var $file;
	var $etag;

	function ttfImage($text, $style, $gzip){
		isset($text) ? $this->text = $text : 0;
		isset($style) ? $this->style = $style : 0;
		isset($gzip) ? $this->gzip = $gzip : 0;
		$stylesheet =  FONT_DIR."/styles.ini";
		if(file_exists($stylesheet)){
			$styles = parse_ini_file($stylesheet, true);
			if(isset($styles[$this->style])){
				$this->fontsettings = $styles[$this->style];
				if($this->style == "admin"){
					$this->fontsettings['font-name'] = $this->text;
				}
				$this->fonttype = !file_exists(FONT_DIR."/".$this->fontsettings['font-name'].".ttf") ? ".otf" : ".ttf";
				$hash = md5(basename($this->fontsettings['font-name']).$this->fontsettings['color'].$this->fontsettings['font-size'].$this->fontsettings['shadow-color'].$this->fontsettings['limit-width'].$this->fontsettings['max-width'].$this->text);
				$this->etag = $hash;
				$this->cache_file = CACHE_DIR."/$hash.png";
				if(file_exists($this->cache_file)){
					if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == gmdate('D, d M Y H:i:s', filemtime($this->file)).' GMT'){
						header("HTTP/1.0 304 Not Modified");
					} else{
						$this->fetchImage();
					}
				} else {
					$this->text2Image();
				}
			}else{
				header("HTTP/1.0 404 Not Found");
				echo "<h2>Error 404 - Not Found</h2>";
			}
		} else {
			header("HTTP/1.0 404 Not Found");
			echo "<h2>Error 404 - Not Found</h2>";
		}
	}

	function text2Image(){

		$style = $this->style;

		$font = FONT_DIR."/".$this->fontsettings['font-name'].$this->fonttype;

		$text = get_magic_quotes_gpc() ? $this->clean_text() : $this->text;

		// RW : Added this multiline block
	    $linecount = 1;
		if ($this->fontsettings["limit-width"]){
	      // The basic approach is to add back in some \n's to the cleaned text,
	      // as GD will correctly interpret them as new lines - I work out
	      // the number of lines by counting the number of them that are added,
	      // plus one for the first line
	      $text = wordwrap($text, (int)$this->fontsettings["max-width"], "\n", true);
	      $linecount = substr_count($text, "\n") + 1;
	    }

		$size = $this->fontsettings['font-size'];

		$box = ImageTTFBBox($size, 0, $font, $text);
		$width = abs($box[0]) + abs($box[2]);

		$test_chars = 	'abcdefghijklmnopqrstuvwxyz' .
				'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .
				'1234567890' .
				'!@#$%^&*()\'"\\/;.,`~<>[]{}-+_-=' ;

		$box = @ImageTTFBBox($size, 0, $font, $test_chars);
		$dip = abs($box[3]);
		$box = ImageTTFBBox($size, 0, $font, $text);
		$lowheight = abs($box[5]-$dip);
		$height = abs($box[5]);
		!$leading ? $leading = round($lowheight*.2) : 0;

		// RW : Added linecount multiple here
		$img = imagecreate($width, ($lowheight + $leading) * $linecount);

		$color = $this->hex_to_rgb($this->fontsettings['color']);
		$background = $this->hex_to_rgb($this->fontsettings['shadow-color']);
		$color1 = imagecolorallocate($img, $background[0], $background[1], $background[2]);
		$color2 = imagecolorallocate($img, $color[0], $color[1], $color[2]);
		imagecolortransparent($img, $color1);
		imagettftext($img, $size, 0, 0, $height + $leading, $color2, $font, $text);
		@imagepng($img, $this->cache_file);
		imagedestroy($img);
		$this->fetchImage();
	}

	function fetchImage(){
		$image = !$this->gzip ? file_get_contents($this->cache_file) : gzencode(file_get_contents($this->cache_file));
		header('X-Generated-By: AnyFont for WordPress');
		$this->gzip ? header('Content-encoding: gzip') : 0;
		header('Content-type: image/png');
		header('Content-length: '.strlen($image));
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($this->cache_file)).' GMT');
		header('Etag: '.$this->etag);
		print($image);
	}


	/** Description: Strips slashes and other "bad" characters from a text string for image creation
	* @param string
	* @return string of plain text
	*/
	function clean_text(){

		$this->text = stripslashes($this->text);
		$matches = null;
		preg_match_all('/%u([0-9A-F]{4})/i',$this->text,$matches);
		if(!empty($matches)) for($i=0;$i<sizeof($matches[0]);$i++)
				$this->text = str_replace($matches[0][$i],'&#'.hexdec($matches[1][$i]).';',$this->text);
		return $this->text;
	}

	/** Description: Method to convert colours from the hex format to RGB
	* @param var hex colour definition
	* @return var RBG Colour Definition
	*/
	function hex_to_rgb($hex){
    		substr($hex,0,1) == '#' ? $hex = substr($hex,1) : 0;

		if(strlen($hex) == 3){
			$hex = substr($hex,0,1) . substr($hex,0,1).
			substr($hex,1,1) . substr($hex,1,1).
			substr($hex,2,1) . substr($hex,2,1);
		}

		strlen($hex) != 6 ? $error = 'Error: Invalid color "'.$hex.'"' : 0 ;

		$rgb[] = hexdec(substr($hex,0,2));
		$rgb[] = hexdec(substr($hex,2,2));
		$rgb[] = hexdec(substr($hex,4,2));

		return $rgb;
	}
}
?>
