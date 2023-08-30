<?php 
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

class Functions
{
   var $main;

   function Functions( )
   {
   }

   
	function createDir( $dirName, $permit = 0777 )
	{
		global $wpdb, $pageFlipError;

		$dir = substr( $dirName, strlen( ABSPATH ) );

		
		if ( !is_dir( $dirName ) )
		{
			if ( !wp_mkdir_p( $dirName ) )
			{
				$txt  = __('Directory', 'pageFlip').' <strong>'.$dir.'</strong> '.__("didn't exist. Please create directory with permission 777 manually!", 'pageFlip').'<br />';
    			$pageFlipError = $pageFlipError . $this->errorMessage( $txt );
				return false;
			}
		}

		
		if ( !is_writeable( $dirName ) || !is_readable( $dirName ) )
		{
			$txt  = __('Directory', 'pageFlip').' <strong>'.$dir.'</strong> '.__('is not writeable! Please set 777 permission.', 'pageFlip').'<br />';
			$pageFlipError = $pageFlipError . $this->errorMessage( $txt );
			return false;
		}

		if ( $this->checkSafeMode() && fileowner ( $dirName ) !== getmyuid () )
		{
			
			rmdir( $dirName );
			$txt  = __('Please create ', 'pageFlip').' <strong>'.$dir.'</strong> '.__('directory with permission 777 manually.', 'pageFlip').'<br />';
			$pageFlipError = $pageFlipError . $this->errorMessage( $txt );
			return false;
		}

	     return true;
	}

	
	function imgSize( $width, $height, $thumbWidth = '', $thumbHeight = '' )
	{
        if( $thumbWidth === '' ) $thumbWidth = $this->main->thumbWidth;
        if( $thumbHeight === '' ) $thumbHeight = $this->main->thumbHeight;

        
        if( $width > $thumbWidth )
		{
		   $photoSize['width'] = $thumbWidth;
		   $photoSize['height'] = round( $height * $thumbWidth / $width );

		   if( $photoSize['height'] > $thumbHeight )
		   {
		       $photoSize['width'] = round( $photoSize['width'] * $thumbHeight / $photoSize['height'] );
		       $photoSize['height'] = $thumbHeight;
		   }
		}
		elseif( $height > $thumbHeight )
		{
		   $photoSize['height'] = $thumbHeight;
		   $photoSize['width'] = round( $width * $photoSize['height'] / $height );
		}
		else
		{
		    $photoSize['width'] = $width;
		    $photoSize['height'] = $height;
		}

        return $photoSize;
	}

	
	function printImg( $img, $alt = '', $width = '', $height = '', $pageList = false )
	{
        if( $width === '' ) $width = $this->main->thumbWidth;
        if( $height === '' ) $height = $this->main->thumbHeight;

        if( $img == '' ) return '&nbsp;';

        $pathParts = PathInfo( $img );

        
	    
        preg_match('/.*\.(.*)$/', $pathParts['basename'], $fileExt);

	    $result = '';

	    switch( strtolower( $fileExt[1] ) )
	    {
	    	case "swf":
	    	{
				if( $pageList == false )
					$result = '<script src="'.$this->main->jsUrl.'AC_RunActiveContent.js" type="text/javascript"></script>' . "\n" .
							  '<script type="text/javascript">' . "\n" .
							  ' //<![CDATA[' . "\n" .
							  ' AC_FL_RunContent( ' .
							       '\'codebase\',' .
								   '\'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0\',' .
								   '\'width\',' .
								   '\'' . $width . '\',' .
								   '\'height\',' .
								   '\'' . $height . '\',' .
								   '\'src\',' .
								   '\'' . $pathParts['dirname'] . '/' . $fileExt[0] . '\',' .
								   '\'quality\',' .
								   '\'high\',' .
								   '\'pluginspage\',' .
								   '\'http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash\',' .
								   '\'movie\',' .
								   '\'' . $pathParts['dirname'] . '/' . $fileExt[0] . '\'' .
								  '); //end AC code' . "\n" .
					          ' //]]>' . "\n" .
							  '</script>' . "\n" .
							  '<noscript>';

				$result .= '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0" width="'.$width.'" height="'.$height.'">' . "\n" .
  						   ' <param name="movie" value="' . $img . '" />' . "\n" .
				           ' <param name="quality" value="high" />' . "\n" .
						   ' <embed src="' . $img . '" quality="high" pluginspage="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" width="'.$width.'" height="'.$height.'"></embed>' . "\n" .
						   '</object>';
				if( $pageList == false ) $result .= '</noscript>';
	    	} break;
	    	default :
	    	{
				$thumb = $this->getThumb( $img );

				
		        
		        
		        
		        
		        

		        if( !ini_get( 'allow_url_fopen' ) || ini_get( 'allow_url_fopen' ) == 'Off' )
					$photoSize = array( 'width' => $width,
										'height' => $height );
		        else 
		        {
					$imageSize = @getimagesize( $thumb );
					if( !$imageSize ) return __('No image', 'pageFlip');  
					$photoSize = $this->imgSize( $imageSize[0], $imageSize[1], $width, $height );
				}

		        $result = '<img src="' . $thumb . '" width="' . $photoSize['width'] . '" height="' . $photoSize['height'] . '" alt="' . $alt . '" />';
		        
	    	}
	    }

        return $result;
	}

	
	function img_resize( $src, $dest, $width, $height, $rgb=0xFFFFFF, $quality=100 )
	{
		  if ( !file_exists( $src ) ) return false;

		  $size = getimagesize( $src );

		  if ( $size === false ) return false;

		  
		  
		  
		  $format = strtolower( substr( $size['mime'], strpos( $size['mime'], '/' ) + 1 ) );
		  $icfunc = "imagecreatefrom" . $format;
		  if ( !function_exists( $icfunc ) ) return false;

		  $x_ratio = $width / $size[0];
		  $y_ratio = $height / $size[1];

		  $ratio       = min( $x_ratio, $y_ratio );
		  $use_x_ratio = ( $x_ratio == $ratio );

		  $new_width   = $use_x_ratio  ? $width  : floor( $size[0] * $ratio );
		  $new_height  = !$use_x_ratio ? $height : floor( $size[1] * $ratio );
		  $new_left    = $use_x_ratio  ? 0 : floor( ( $width - $new_width ) / 2 );
		  $new_top     = !$use_x_ratio ? 0 : floor( ( $height - $new_height ) / 2 );

		  $isrc = $icfunc( $src );
		  if( !$isrc ) return false;
		  
		  $idest = imagecreatetruecolor( $width, $height );

		  if( $idest === false ) return false;

		  imagefill( $idest, 0, 0, $rgb );
		  imagecopyresampled( $idest, $isrc, $new_left, $new_top, 0, 0,
		   					  $new_width, $new_height, $size[0], $size[1] );

		  if( !imagejpeg( $idest, $dest, $quality ) ) return false;

		  imagedestroy( $isrc );
		  imagedestroy( $idest );

		  return true;
	}

	
	function splitImage( $src, $quality=75 )
	{
		  if ( !file_exists( $src ) ) return false;

		  $prefix = $this->randStr( 5 );

		  do
		  {
				$str = $this->randStr( 5 );
				$destFiles = array( 0 => $this->main->imagesPath . '/p1_' . $str . '_' . basename( $src ),
		  					  		1 => $this->main->imagesPath . '/p2_' . $str . '_' . basename( $src ) );
		  }
		  while( file_exists( $destFiles[0] ) || file_exists( $destFiles[1] ) );

		  $size = getimagesize( $src );
		  if ( $size === false ) return false;

		  
		  
		  
		  $format = strtolower( substr( $size['mime'], strpos( $size['mime'], '/' ) + 1 ) );
		  $icfunc = "imagecreatefrom" . $format;
		  if ( !function_exists( $icfunc ) ) return false;

		  $width[0] = ceil( $size[0] / 2 );
		  $width[1] = $size[0] - $width[0];
		  $left     = array( 0 => 0, 1 => $width[0] );

		  $isrc = $icfunc( $src );
		  if( !$isrc ) return false;

		  
		  foreach( $destFiles as $id=>$dest )
		  {
		  	$idest = imagecreatetruecolor( $width[$id], $size[1] );

		  	if( $idest === false ) return false;

		  	
		  	imagecopyresampled( $idest, $isrc, 0, 0, $left[$id], 0,
		   					  	$width[$id], $size[1], $width[$id], $size[1] );

		  	if( !imagejpeg( $idest, $dest, $quality ) ) return false;

			imagedestroy( $idest );

			
			$thumbName = $this->main->imagesPath . '/t_' . basename( $dest );
			$newSize = $this->imgSize( $width[$id], $size[1], $this->main->thumbWidth, $this->main->thumbHeight );
			$this->img_resize( $dest, $thumbName, $newSize['width'], $newSize['height'] );
		  }

		  imagedestroy( $isrc );

		  return $destFiles;
	}

	
	function buildImage( $images, $page, $width, $height, $layoutId, $quality=75 )
	{
		  do
		  {
				$destFile = $this->main->imagesPath . '/fe_' . $this->randStr() . '.jpg';
		  }
		  while( file_exists( $destFile ) );

		  $idest = imagecreatetruecolor( $width, $height );
		  if( $idest === false ) return false;
		  imagefill( $idest, 0, 0, $page->frameColor );

		  $padding = ceil( $page->frameSize / 2 );

		  $areaX = $areaY = $padding;
		  
		  $areaW = $width - $page->frameSize;
		  $areaH = $height - $page->frameSize;

		  
		  foreach( $this->main->layouts[(int)$layoutId]->areas as $area )
		  {
				
				if( empty( $images[(int)$area->id] ) ) continue;

				$size = getimagesize( $images[(int)$area->id] );
				if ( $size === false ) continue;

			  	
			  	
			  	
			  	$format = strtolower( substr( $size['mime'], strpos( $size['mime'], '/' ) + 1 ) );
			  	$icfunc = "imagecreatefrom" . $format;

			  	if ( !function_exists( $icfunc ) ) continue;

				$isrc = $icfunc( $images[(int)$area->id] );

		  		if( !$isrc ) continue;

				
				$curAreaX = ceil( $area->X * $areaW ) + $areaX + $padding;
				$curAreaY = ceil( $area->Y * $areaH ) + $areaY + $padding;
				$curAreaW = ceil( $area->W * $areaW ) - 2 * $padding;
				$curAreaH = ceil( $area->H * $areaH ) - 2 * $padding;

				$srcX = 0;
				$srcY = 0;
				$srcW = $size[0];
				$srcH = $size[1];
				$dstX = $curAreaX;
				$dstY = $curAreaY;
				$dstW = $curAreaW;
				$dstH = $curAreaH;

				$coefArea = $curAreaW / $curAreaH;
				$coefImg = $size[0] / $size[1];

				
				switch( $page->imgs[(int)$area->id]->scaling )
				{
					case 'scaleToFit' :
					{
						if( $coefArea < $coefImg )
						{
							$dstH = ceil( $dstW / $coefImg );
							$dstY += ceil( ( $curAreaH - $dstH ) / 2 );
						}
						else
						{
							$dstW = ceil( $dstH * $coefImg );
							$dstX += ceil( ( $curAreaW - $dstW ) / 2 );
						}
					} break;
					case 'scaleToFill' :
					{
						if( $coefArea > $coefImg )
						{
							$srcH = ceil( $srcW / $coefArea );
							$srcY = ceil( ( $size[1] - $srcH ) / 2 );
						}
						else
						{
							$srcW = ceil( $srcH * $coefArea );
							$srcX = ceil( ( $size[0] - $srcW ) / 2 );
						}
					} break;
					case 'doNotScale' :
					{
						$srcX = (int)$page->imgs[(int)$area->id]->x;
						$srcY = (int)$page->imgs[(int)$area->id]->y;
						$srcW = $dstW;
						$srcH = $dstH;
					} break;
				}

				imagecopyresampled( $idest, $isrc, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH );

				imagedestroy( $isrc );
			}

		  if( !imagejpeg( $idest, $destFile, $quality ) ) return false;
		  imagedestroy( $idest );

		  $destFile = basename( $destFile );

		  
		  $thumbName = $this->main->imagesPath . '/t_' . $destFile;
		  $newSize = $this->imgSize( $width, $height, $this->main->thumbWidth, $this->main->thumbHeight );
		  $this->img_resize( $this->main->imagesPath . '/' . $destFile, $thumbName, $newSize['width'], $newSize['height'] );

		  return $destFile;
	}

	
	function getSplitImageName( $imageName )
	{
		$imageName = basename( $imageName );
		preg_match( "/^p(1|2)_[a-zA-Z0-9]{5}_([a-zA-Z0-9\._]*)$/", $imageName, $srcImageName );

		return $srcImageName[2];
	}

	
	function getImageSize( $filename )
	{
		
		if (ini_get('allow_url_fopen'))
			return @getimagesize( $filename );
	}

	
	function getThumb( $image )
	{
		$pathParts = PathInfo( $image );
		if( $pathParts['dirname'] == $this->main->plugin_url . $this->main->imagesDir && $this->getExt( $image ) !== 'swf' )
			$thumb = $pathParts['dirname'] . '/t_' . $pathParts['basename'];
		else $thumb = $image;

		return $thumb;
	}

	
	function getFullImage( $thumb, $type = 'url' )
	{
		$pathParts = PathInfo( $thumb );
		if( $pathParts['dirname'] == $this->main->plugin_url . $this->main->imagesDir && $this->getExt( $thumb ) !== 'swf' )
			$image = $pathParts['dirname'] . '/' . str_replace( 't_', '', $pathParts['basename'] );
		else $image = $thumb;

		if( $type == 'path' ) $image = $this->main->imagesPath . basename( $image );

		return $image;
	}

	
	function getExt( $filename )
	{
		
	    
		preg_match('/.*\.(.*)$/', $filename, $fileExt);
	    return $fileExt[1];
	}

    
    function printHeader( $text )
    {
    	return "<h2>".$text."</h2>";
    }

    
	function randStr( $length = 'rand', $max = 6, $min = 12 )
	{
	    if( $length == 'rand' ) $length = rand( $max, $min );

		$symb = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
		for( $i=0; $i < $length; $i++ ) $str .= substr( $symb, rand( 0, strlen( $symb ) ), 1 );
		return $str;
	}

	
	function fileName( $type, $oldName )
	{
	    
	    preg_match('/.*\.(.*)$/', basename($oldName), $fileExt);

	    return substr( $type, 0, 1 ) . '_' . $this->randStr() . '.' . $fileExt[1];
	}

	
	function checkPic( $imageName )
	{
		if( dirname( $imageName ) . '/' == $this->main->imagesUrl )
	 		if( substr( basename( $imageName ), 0, 2 ) == 'i_' ) return 'pageFlip';
	 		elseif( substr( basename( $imageName ), 0, 3 ) == 'fe_' ) return 'flashEditor';
	 		elseif( substr( basename( $imageName ), 0, 2 ) == 'p_' ) return 'splitImage';
	}

	
	function check()
	{
		if( !$this->checkMemoryLimit() )
			$memoryLimit = $this->warningMessage( __("Attention! It's required to have Memory Limit 16 Mb or higher for roper work of PageFlip plugin.", "pageFlip") );
		else $memoryLimit = '';

		return $memoryLimit;
	}

	function checkMemoryLimit()
	{
	    $memory_limit = (int)substr( ini_get('memory_limit'), 0, -1 );

		
	    if ( ( $memory_limit != 0 ) && ( $memory_limit < 16 ) )
	      return false;
		else return true;
    }

    function checkSafeMode()
    {
		if ( ( gettype( ini_get( 'safe_mode' ) ) == 'string' ) )
		{
		  if ( strtolower( ini_get( 'safe_mode' ) ) == 'off' || (int)ini_get( 'safe_mode' ) === 0 ) return false;
	      else return true;
	    }
		else
  			return (bool)ini_get( 'safe_mode' );
    }

    function checkPermit( $dir )
	{
		if( empty( $dir ) ) $dir = $this->main->plugin_path;

		$permitions = @fileperms( $dir );
		$permitions = decoct( $permitions );

		return substr( $permitions, strlen( $permitions )-3, strlen( $permitions ) );
	}

  
  function removeDir( $dirName )
  {
        if( !is_dir( $dirName ) ) return true;
        $delete_dir = opendir( $dirName );
        chdir( $dirName );
        while ( $delete = readdir( $delete_dir ) )
        {
               if( is_dir( $delete ) && ( $delete !== "." ) && ( $delete !== ".." ) ) $del_dir_names[] = $delete;
               if( is_file( $delete ) ) $del_file_names[] = $delete;
        }
        if( is_dir( "0/" ) ) $del_dir_names[] = "0/";

        if( isset( $del_file_names ) )
         foreach( $del_file_names as $delete_this_file ) unlink( $dirName . $delete_this_file );

        if( isset( $del_dir_names ) )
         foreach( $del_dir_names as $delete_this_dir ) $this->removeDir( $dirName . $delete_this_dir . '/' );

        closedir( $delete_dir );
        if( rmdir( $dirName ) ) return true;
        else return false;
  }

  
  function getImageUrl( $filename )
  {
  	if( $this->isUrl( $filename ) ) return $filename;
  	else return  $this->main->plugin_url . $this->main->imagesDir . '/' . $filename;
  }

  function isUrl( $url )
  {
  	return eregi("^http:\/\/[-0-9a-z\._]+.*$", trim( $url ));
  }

  
  function checkImage( $imageName )
  {
  	$imageName = basename( $imageName );
  	if (!preg_match('/.*\.(jpg)|(jpeg)|(gif)|(swf)$/i', $imageName, $m))
  	{
		echo '<strong>'.$imageName.'</strong> - <strong>' . __('Error', 'pageFlip') . '</strong>: ' . __('Wrong file type', 'pageFlip') . '<br />';
		return false;
  	}
	
    return true;
  }

      
	function checkTable( $tableName, $fields )
    {
    	global $wpdb;

		$existsFields = Array();
        $sql = "SHOW COLUMNS FROM `".$tableName."`";
	    $columns = $wpdb->get_results( $sql, ARRAY_A );
		foreach( $columns as $column ) $existsFields[] = $column['Field'];

		foreach( $fields as $field=>$prop )
		 if( !in_array( $field, $existsFields ) ) 
			$wpdb->query( "ALTER TABLE `" . $tableName . "` ADD `".$field."` ".$prop.";" );
    }

    
	function createTable( $tableName, $fields )
	{
		global $wpdb;

		
		if( $wpdb->get_var( "SHOW TABLES LIKE '".$tableName."'" ) != $tableName )
		{
              $columns = '';

			  foreach( $fields as $field=>$prop )
			  {
              	  if( $columns != '' ) $columns .= ', ';
				  $columns .= '`'.$field.'` '.$prop.'';
              }

		      $sql = "CREATE TABLE " . $tableName . " (".$columns.");";
		      $wpdb->query( $sql );
		}
	}

	
   function navigationBar( $page = '', $imgPerPage = '', $type = 'pageFlip', $adParam = '', $gallery = 0 )
   {
   		global $wpdb;

		if( (int)$page < 1 ) $page = 1;

		$countNumbers = 7; 

		$sql = $this->sqlImgList( 'count', $type, $gallery );
		$countImg = $wpdb->get_var( $sql );

		settype( $countImg, 'integer');
		settype( $imgPerPage, 'integer');

		if( $imgPerPage < 1 )
			$imgPerPage = ( $countImg > 0 ) ? $countImg : 1;

		$countPages = ceil( $countImg / $imgPerPage ); 

		if( $page > $countPages ) $page = $countPages;

		if( $page > 1 ) $left = $this->main->html->navigationLink( '&lt;&lt;', '1' , $adParam ) . '&nbsp;' . $this->main->html->navigationLink( '&lt;', ( $page - 1 ) , $adParam ) . '&nbsp;';
		else $left = '';

		if( $page < $countPages ) $right = '&nbsp;' . $this->main->html->navigationLink( '&gt;', ( $page + 1 ) , $adParam ) . '&nbsp;' . $this->main->html->navigationLink( '&gt;&gt;', $countPages , $adParam );
		else $right = '';

		if( $page + floor( $countNumbers / 2 ) > $countPages ) $minus = $page + 2 * floor( $countNumbers / 2 ) - $countPages;
		else $minus = floor( $countNumbers / 2 );

		$start = $page - $minus;

		if( $start < 1 ) $start = 1;

		$bar = '';
		if( $countPages > 1 )
			for( $i = $start; $i < $start + $countNumbers; $i++ )
			{
				if( $i > $countPages ) break;
				if( $bar !== '' ) $bar .= '&nbsp;';
				if( (int)$i === (int)$page ) $bar .= $i;
				else $bar .= $this->main->html->navigationLink( $i, $i , $adParam );
			}

		$bar = $left . $bar . $right . '&nbsp;' . $this->imagesOnPage();

		$result = array(
						 'bar' => $bar,
						 'page' => $page
						);

		return $result;
   }

   
   function galleryJumpList( $galleryId )
   {
   		global $wpdb;

		$galleryList = '';

		$sql = "select `id`, `name` from `".$this->main->table_gal_name."` order by `name` asc";
		$galleries = $wpdb->get_results( $sql, ARRAY_A );
		if( count( $galleries ) > 0 )
		foreach( $galleries as $gallery )
		{
			$galleryList .= '<option value="'.$gallery['id'].'"';
			if( (int)$galleryId === (int)$gallery['id'] ) $galleryList .= ' selected="selected"';
			$galleryList .= '>'.$gallery['name'].' </option>' . "\n";
		}

		$galleryList .= '<option value="0"';
		if( (int)$galleryId === 0 ) $galleryList .= ' selected="selected"';
		$galleryList .= '>'.__('Unsorted', 'pageFlip').' </option>' . "\n";

		return $galleryList;
   }

   
   function imagesOnPage( )
   {
		$imgOnPage = get_option( 'pageFlip_imgPerPage' );

   		if( (string)$imgOnPage == '' )
   		{
		   $imgOnPage = $this->main->itemsPerPage[0]['value'];
		   add_option( 'pageFlip_imgPerPage', $imgOnPage, '', 'no' );
   		}

   		$id = rand();

   		$result = '<select size="1" name="itemsPerPage_' . $id . '" id="itemsPerPage_' . $id . '" style="font-size:9px; height: 19px;" onchange="itemsPerPage(this);">';

   		foreach( $this->main->itemsPerPage as $itemsPerPage )
		{
			$result .= '<option value="' . $itemsPerPage['value'] . '"';
			$result .= ( (int)$itemsPerPage['value'] === (int)$imgOnPage ) ? ' selected="selected"' : '';
			$result .= '>' . $itemsPerPage['label'] . '</option>';
		}

		$result .= '</select>';

   		return $result;
   }

   
   function sqlImgList( $typeQuery, $typeList, $gallery = 0 )
   {
   	   global $wpdb;

	   switch( $typeList )
   	   {
   	   		case 'WPMedia' : {
   	   			$result['count'] = "select count(*) from `".$wpdb->postmeta."` where `meta_key` = '_wp_attached_file' AND (`meta_value` LIKE '%.jpg' OR `meta_value` LIKE '%.jpeg' OR `meta_value` LIKE '%.gif' OR `meta_value` LIKE '%.swf')";
				$result['list'] = "SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_key` = '_wp_attached_file' AND (`meta_value` LIKE '%.jpg' OR `meta_value` LIKE '%.jpeg' OR `meta_value` LIKE '%.gif' OR `meta_value` LIKE '%.swf') ORDER BY `meta_id` DESC ";
   	   		} break;
			case 'NGGallery' : {
				$result['count'] = "select count(*) from `".$wpdb->prefix."ngg_pictures` where `galleryId` = '".$gallery."'";
				$result['list'] = "SELECT `pid`, `filename`, `description`, `imagedate`, `alttext`, `galleryid` FROM `".$wpdb->prefix."ngg_pictures` where `galleryId` = '".$gallery."' ORDER BY `imagedate` DESC ";
			} break;
			default : {
				$result['count'] = "select count(*) from `".$this->main->table_img_name."` where `type` = 'img' and `gallery` = '".$gallery."'";
				$result['list'] = "select `id`, `name`, `filename`, `date` from `".$this->main->table_img_name."` where `type` = 'img' and `gallery` = '".$gallery."' order by `date` desc ";
			}
   	   }

	   return $result[$typeQuery];
   }

   
   function getGalleryPreview( $galleryId, $type = 'pageFlip' )
   {
   	   global $wpdb;

	   switch( $type )
   	   {
			case 'NGGallery' : {
				$sql = "select `path`, `previewpic` from `".$wpdb->prefix."ngg_gallery` where `gid` = '".$galleryId."'";
				$gallery = $wpdb->get_row( $sql, ARRAY_A );
				$sql = "SELECT `filename` FROM `".$wpdb->prefix."ngg_pictures` where `pid` = '".$gallery['previewpic']."'";
				$imgName = $wpdb->get_var( $sql );
				if( !$imgName )
				{
				 	$sql = "SELECT `filename` FROM `".$wpdb->prefix."ngg_pictures` where `galleryid` = '".$galleryId."' order by RAND() limit 1";
				 	$imgName = $wpdb->get_var( $sql );
				}
				$result = get_option( 'siteurl' ) . '/' . $gallery['path'] . '/thumbs/thumbs_' . $imgName;
			} break;
			default : {
				$sql = "select `filename` from `".$this->main->table_img_name."` where `type` = 'img' and `gallery` = '".$galleryId."' order by RAND() limit 1";
				$result = $this->main->functions->getImageUrl( $wpdb->get_var( $sql ) );
			}
   	   }

	   return $result;
   }

   function loadLayouts()
   {
   		$xml = '<?xml version="1.0" ?>' .
				'<Layouts>';
		foreach( $this->main->layouts as $layout )
			$xml .= $layout->asXML();
		$xml .= '</Layouts>';

		return $xml;
   }

   
   function loadAlbumXml( $bookId )
   {
		$book = new Book( $bookId );

   		return '<?xml version="1.0" encoding="UTF-8"?>' . $book->album->asXML();


   }

   
   function saveAlbumXML( $bookId )
   {
   	 $f = fopen( 'C:\AppServ\www\wp7\wp-content\plugins\page-flip-image-gallery\1.txt', 'w+');

	 $book = new Book( $bookId );

   	 $book->album->parseFromXML( urldecode( $_POST['xml'] ) );

   	 foreach( $book->album->pages as $page )
		if( $page->modified == 'true' )
	 	{
			unset( $newPage );
			unset( $images );

			foreach ( $page->imgs as $img )
	   	 		$images[] = $this->getFullImage( $book->album->images[$img->id]->thumb );

			$newPage = $this->buildImage( $images, $page, $book->album->pageWidth, $book->album->pageHeight, $page->template );

			if( $newPage )
			{
				$addPage = new Page( $this->main->imagesUrl . $newPage, $page->id, '' );

				

				
				
				if( $this->checkPic( $book->pages[(int)$page->id]->image ) == 'flashEditor' )
				{
					$oldPage =  $this->main->plugin_path . $this->main->imagesDir . '/' . basename( $book->pages[(int)$page->id]->image );

	        		@unlink( $oldPage );

			        
			        if( $this->getExt( $oldPage ) != "swf" )
			        {
			        	 $thumb = $this->main->plugin_path . $this->main->imagesDir . '/t_' . basename( $book->pages[(int)$page->id]->image );
			        	 @unlink( $thumb );
			        }
				}

				
				$book->pages[(int)$page->id] = $addPage;
			}
	 	}

		 fwrite($f, urldecode( $_POST['xml'] ));
	  $book->save();
   	 }

   
	function myErrorHandler( $errNo, $errStr, $errFile, $errLine )
	{
	    if( str_replace( '\\', '/', dirname( $errFile ) ) !== str_replace( '\\', '/', WP_PLUGIN_DIR . '/' . $this->main->plugin_dir ) ) return true;

		switch ($errNo)
		{
		    case E_ERROR:
		    case E_USER_ERROR:
		    {
		    	$txt = __( '<b>Fatal error</b>: '.$errStr . ' on line ' . $errLine . ' in file ' . $errFile );
		    	echo $this->errorMessage( $txt );
		    }
		        break;
		    case E_WARNING:
		    case E_USER_WARNING:
		    {
		    	$txt = __( '<b>Warning</b>: '.$errStr . ' on line ' . $errLine . ' in file ' . $errFile );
		    	echo $this->errorMessage( $txt );
		    }
		        break;
		    default:
		    {
		    	$txt = __( '<b>Unknown error type</b>: '.$errStr . ' on line ' . $errLine . ' in file ' . $errFile );
		    	echo $this->errorMessage( $txt );
		    }
		        break;
	    }

	    
	    return true;
	}

   
   function errorMessage( $message )
   {
   		return '<div class="wrap"><div class="error" id="error"><p>' . $message. '</p></div></div>' . "\n";
   }

   
   function warningMessage( $message )
   {
   		return '<div class="wrap"><div class="updated fade" id="message"><p>' . $message . '</p></div></div>' . "\n";
   }
}

?>