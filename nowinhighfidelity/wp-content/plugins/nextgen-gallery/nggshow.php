<?php
// Load wp-config
require_once( dirname(__FILE__) . '/ngg-config.php');

// reference thumbnail class
include_once( nggGallery::graphic_library() );
include_once('lib/core.php');

// get the plugin options
$ngg_options = get_option('ngg_options');	

// Some parameters from the URL
$pictureID = (int) $_GET['pid'];
$mode = isset($_GET['mode']) ? attribute_escape($_GET['mode']) : '';

// let's get the image data
$picture  = nggdb::find_image( $pictureID );
$thumb = new ngg_Thumbnail( $picture->imagePath );

// Resize if necessary
if ( !empty($_GET['width']) || !empty($_GET['height']) ) {
 	// Sanitize
 	$w = ( !empty($_GET['width'])) ? intval($_GET['width']) : 0;
 	$h = ( !empty($_GET['height'])) ? intval($_GET['height']) : 0;
	// limit the maxium size, prevent server memory overload
	if ($w > 1280) $w = 1280;
	if ($h > 1280) $h = 1280;
	$thumb->resize( $w, $h );
}

// Apply effects according to the mode parameter
if ($mode == 'watermark') {
	if ($ngg_options['wmType'] == 'image') {
		$thumb->watermarkImgPath = $ngg_options['wmPath'];
		$thumb->watermarkImage($ngg_options['wmPos'], $ngg_options['wmXpos'], $ngg_options['wmYpos']); 
	} else if ($ngg_options['wmType'] == 'text') {
		$thumb->watermarkText = $ngg_options['wmText'];
		$thumb->watermarkCreateText($ngg_options['wmColor'], $ngg_options['wmFont'], $ngg_options['wmSize'], $ngg_options['wmOpaque']);
		$thumb->watermarkImage($ngg_options['wmPos'], $ngg_options['wmXpos'], $ngg_options['wmYpos']);  
	}
} else if ($mode == 'web20') {
	$thumb->createReflection(40,40,50,false,'#a4a4a4');
}

// Show thumbnail
$thumb->show();
$thumb->destruct();

exit;
?>