<?php 
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

class Page
{
	var $image,
		$number,
		$name,
		$zoomURL,
		$zoomType = 'type4',
		$target = 'type1',
		$zoomHeight = 600,
		$zoomWidth = 800;		
	
	function Page( $image, $number, $name )
	{
		$this->image = (string)$image;
		$this->number = (int)$number;
		$this->name = (string)$name;
		$this->zoomURL = (string)$image;
	}	
}

?>