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

	private $text;
	private $style;
	private $file;
	private $gzip;
	private $fontsettings;
	private $etag;

	/**
	* @param $style style to be used (must be defined in styles.ini)
	* @param $text string to be converted to an image
	* @param $gzip boolean to control if the response should be gzipped or not.
	*/
	public function __construct($text, $style, $gzip){
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
				$this->linecount = 1;
				if ($this->fontsettings["limit-width"]){
					$this->text = wordwrap($this->text, (int)$this->fontsettings["max-width"], "\n", true);
					$this->linecount = substr_count($this->text, "\n") + 1;
				}
				$this->fonttype = !file_exists(FONT_DIR."/".$this->fontsettings['font-name'].".ttf") ? ".otf" : ".ttf";
				$hash = md5(basename($this->fontsettings['font-name']).$this->fontsettings['color'].$this->fontsettings['font-size'].$this->fontsettings['shadow'].$this->fontsettings['shadow-color'].$this->fontsettings['limit-width'].$this->fontsettings['max-width'].$this->text);
				$this->etag = $hash;
				$this->file = CACHE_DIR."/$hash.png";
				if(file_exists($this->file)){
					if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == gmdate('D, d M Y H:i:s', filemtime($this->file)).' GMT'){
						header("HTTP/1.0 304 Not Modified");
					} else{
						self::fetchImage();
					}
				} else {
					self::text2Image();
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

	/** Description
	* Creates a png image from a text string using truetype fonts and writes the file to cache
	*/
	private function text2Image(){
		$image = new Imagick();
		$draw = new ImagickDraw();
		$draw->setGravity(Imagick::GRAVITY_WEST);
		$draw->setFillColor(new ImagickPixel("#".$this->fontsettings['color']));
		$draw->setFontSize($this->fontsettings['font-size']);
		$draw->setFont(FONT_DIR."/".$this->fontsettings['font-name'].$this->fonttype);

		if($this->fontsettings['shadow']){
			$shadow = new ImagickDraw();
			$shadow->setGravity(Imagick::GRAVITY_WEST);
			$shadow->setFillColor(new ImagickPixel("#".$this->fontsettings['shadow-color']));
			$shadow->setFontSize($this->fontsettings['font-size']);
			$shadow->setFont(FONT_DIR."/".$this->fontsettings['font-name'].$this->fonttype);
			$fm = $image->queryFontMetrics($shadow, $this->text, false);
			$width = $fm["textWidth"]+4;
			$height = ($fm["textHeight"]+3)*$this->linecount;

		} else {
			$fm = $image->queryFontMetrics($draw, $this->text, false);
			$width = $fm["textWidth"]+3;
			$height = ($fm["textHeight"]+2)*$this->linecount;
		}
		$image->newImage($width, $height, "transparent", "png");
		$this->fontsettings['shadow'] ? $image->annotateImage($shadow, 2, 2, 0, $this->text) : 0;
		$image->annotateImage($draw, 1, 1, 0, $this->text);
		$image->writeImage($this->file);
		if(file_exists($this->file)){
			self::fetchImage();
		}
	}

	/** Description
	* Fetches the image from cache and delivers it to the browser along with the required headers.
	* @return PNG image
	*/
	private function fetchImage(){
		$image = !$this->gzip ? file_get_contents($this->file) : gzencode(file_get_contents($this->file));
		header('X-Generated-By: AnyFont for WordPress');
		$this->gzip ? header('Content-encoding: gzip') : 0;
		header('Content-type: image/png');
		header('Content-length: '.strlen($image));
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($this->file)).' GMT');
		header('Etag: '.$this->etag);
		print($image);
	}
}
?>