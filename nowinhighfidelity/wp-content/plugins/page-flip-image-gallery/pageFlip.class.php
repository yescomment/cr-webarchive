<?php 
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

class pageFlip_plugin_base
{
	var $page_title,
		$menu_title,
		$access_level = 5,
		$add_page_to = 1,
		$table_name, 
		$table_img_name, 
		$table_gal_name, 
		$plugin_dir = 'page-flip-image-gallery', 
		$pluginFilesDir = 'pageflip', 
		$plugin_path, 
		$plugin_url, 
		$component, 
		$editor, 
		$navigation, 
		$componentJS, 
		$jqueryJS, 
		$swfObjectJS, 
		$width = 800, 
		$height = 600, 
		$maxPageSize, 
		$bgFile, 
		$maxSoundSize, 
		$parent, 
		$booksDir = 'books', 
		$soundsDir = 'sounds', 
		$imagesDir = 'images', 
		$imagesPath,
		$uploadDir = 'upload', 
		$imgUrl, 
 		$jsDir = 'js', 
		$langDir = 'lang', 
		$imagesUrl, 
		$uploadPath, 
		$jsUrl, 
		$thumbWidth = 70, 
		$thumbHeight = 90, 
		$trial, 
		$functions, 
		$html, 
		$itemsPerPage,
		$layouts = array(), 
		$isPopup = false;	

	function pageFlip_plugin_base()
	{
		global $wpdb;

		$this->get_options();

		$this->page_title = __('Wordpress PageFlip', 'pageFlip');

		$this->menu_title = __('FlippingBook', 'pageFlip');

		$this->table_name = $wpdb->prefix . 'pageflip';
		$this->table_img_name = $wpdb->prefix . 'pageflip_img';
		$this->table_gal_name = $wpdb->prefix . 'pageflip_gallery';

		$this->maxPageSize = 5 * 1024 * 1024;
		$this->maxSoundSize = 100 * 1024;

		$this->plugin_path = WP_CONTENT_DIR . '/' .  $this->pluginFilesDir . '/';
		$this->imagesPath = $this->plugin_path . $this->imagesDir . '/';
		$this->plugin_url = WP_CONTENT_URL . '/' .  $this->pluginFilesDir . '/';
		$this->imagesUrl = $this->plugin_url . $this->imagesDir . '/';
		$this->jsUrl = WP_PLUGIN_URL . '/' . $this->plugin_dir . '/' . $this->jsDir . '/';
		$this->imgUrl = WP_PLUGIN_URL . '/' . $this->plugin_dir . '/img/';
		$this->bgFile = $this->imgUrl . 'bg.jpg';
		$this->component = WP_PLUGIN_URL . '/' .  $this->plugin_dir . '/flippingBook.swf';
		$this->editor = WP_PLUGIN_URL . '/' .  $this->plugin_dir . '/albumEditor.swf';

		$this->navigation  = WP_PLUGIN_URL . '/' .  $this->plugin_dir . '/navigation.swf';
		$this->uploadPath = $this->plugin_path . $this->uploadDir . '/';
		$this->componentJS = $this->jsUrl . 'flippingbook.js';
		$this->swfObjectJS = $this->jsUrl . 'swfobject.js';
		$this->jqueryJS = $this->jsUrl . 'jquery-1.2.6.pack.js';
		$this->trial = 10;
	}

	function init()
	{
		include_once ( 'functions.class.php' ); 
		include_once ( 'htmlPart.class.php' ); 
		include_once ( 'book.class.php' ); 
		include_once ( 'album.class.php' ); 

		$this->functions = new Functions( );
		$this->html = new HTMLPart( );

		
		$this->layouts[1] = new Layout( 1 );
		$this->layouts[1]->addArea( 0, 0, 0, 1, 1 );
		$this->layouts[2] = new Layout( 2 );
		$this->layouts[2]->addArea( 0, 0, 0, 1, 0.5 );
		$this->layouts[2]->addArea( 1, 0, 0.5, 1, 0.5 );
		$this->layouts[3] = new Layout( 3 );
		$this->layouts[3]->addArea( 0, 0, 0, 0.5, 0.5 );
		$this->layouts[3]->addArea( 1, 0.5, 0, 0.5, 0.25 );
		$this->layouts[3]->addArea( 2, 0.5, 0.25, 0.5, 0.25 );
		$this->layouts[3]->addArea( 3, 0, 0.5, 1, 0.5 );
		$this->layouts[4] = new Layout( 4 );
		$this->layouts[4]->addArea( 0, 0, 0, 1, 0.25 );
		$this->layouts[4]->addArea( 1, 0, 0.25, 0.33, 0.25 );
		$this->layouts[4]->addArea( 2, 0.33, 0.25, 0.67, 0.5 );
		$this->layouts[4]->addArea( 3, 0, 0.5, 0.33, 0.25 );
		$this->layouts[4]->addArea( 4, 0, 0.75, 0.33, 0.25 );
		$this->layouts[4]->addArea( 5, 0.33, 0.75, 0.33, 0.25 );
		$this->layouts[4]->addArea( 6, 0.66, 0.75, 0.323, 0.25 );

		
		$this->check_db();
		$this->check_dir();

		
		$this->itemsPerPage = array(
									 0 => array ( 'value' => 25, 'label' => __('25 per page', 'pageFlip') ),
									 1 => array ( 'value' => 50, 'label' => __('50 per page', 'pageFlip') ),
									 2 => array ( 'value' => 200, 'label' => __('200 per page', 'pageFlip') ),
									 3 => array ( 'value' => 0, 'label' => __('all', 'pageFlip') )
							  		);
	}

    function get_options()
	{
		if ( !defined( 'WP_PLUGIN_DIR' ) )
		{
			if ( !defined( 'WP_CONTENT_DIR ') )
		  	  define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
		  	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' ); 
		}

		if ( !defined( 'WP_PLUGIN_URL' ) )
		{
			if ( !defined( 'WP_CONTENT_URL ') )
			  define( 'WP_CONTENT_URL', get_option( 'siteurl ') . '/wp-content'); 
			define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' ); 
		}

		if ( !defined('PLUGINDIR') )
  			define( 'PLUGINDIR', 'wp-content/plugins' ); 
	}


	function add_admin_menu()
	{
		if ( $this->add_page_to == 1 )
			add_menu_page( $this->page_title,
				$this->menu_title, $this->access_level,
				$this->plugin_dir, array ( $this , 'main_page') );

		elseif ( $this->add_page_to == 2 )
			add_options_page( $this->page_title,
				$this->menu_title, $this->access_level,
				$this->plugin_dir, array ( $this , 'main_page')  );

		elseif ( $this->add_page_to == 3 )
			add_management_page( $this->page_title,
				$this->menu_title, $this->access_level,
				$this->plugin_dir, array ( $this , 'main_page')  );

		elseif ( $this->add_page_to == 4 )
			add_theme_page( $this->page_title,
				$this->menu_title, $this->access_level,
				$this->plugin_dir, array ( $this , 'main_page')  );


        add_submenu_page( $this->plugin_dir, __('Main', 'pageFlip'),
								__('Main', 'pageFlip'), $this->access_level,
								$this->plugin_dir );
		add_submenu_page( $this->plugin_dir, __('Manage books and pages', 'pageFlip'),
								__('Manage books and pages', 'pageFlip'), $this->access_level,
								$this->plugin_dir . '/books', array( $this, 'manage_books' ) );
		add_submenu_page( $this->plugin_dir, __('Images', 'pageFlip'),
								__('Images', 'pageFlip'), $this->access_level,
								$this->plugin_dir . '/images', array( $this, 'images' ) );
		
	}

	function activate()
	{
	}

	function deactivate()
	{
	}

	
	function replaceBooks( $att, $content = null )
	{
		global $wpdb;

		
		if( $att['id'] == '' )
			return '';

		
        $sql = "select `bgImage` from `".$this->table_name."` where `id` = '".$att['id']."'";
        $bgImage = $wpdb->get_var( $sql );

        if( empty( $bgImage ) ) $bgImage = $this->bgFile;
        elseif( $bgImage == "-1" ) $bgImage = '';
        else
        {
        	$sql = "select `filename` from `" . $this->table_img_name . "` where `id` = '" . $bgImage . "' and `type` = 'bg'";
         	$bgImage = $this->plugin_url . $this->imagesDir . '/' . $wpdb->get_var( $sql );
        }

		$book = new Book( $att['id'] ); 

		if( $book->state !== 1 ) return false; 

		if( $book->countPages == 0 ) return false; 

		
		if( empty( $att['width'] ) || empty( $att['height'] ) )
		{
			 if( empty( $att['width'] ) ) $att['width'] = $book->stageWidth;
			 if( empty( $att['height'] ) ) $att['height'] = $book->stageHeight;
		}

		if ( $book->popup == 'true' && !$this->isPopup )
		{
			
			if ( $att['preview'] )
				$book->preview = $att['preview'];

			return $this->html->popupLink( $book, $att['width'], $att['height'], $bgImage );
		}
		else
		{
			
			return $this->html->viewBook( $book, $att['width'], $att['height'], $bgImage );
		}
	}

	
	function main_page()
	{
        echo '<div class="wrap">';
		echo $this->functions->printHeader( '<a href="http://pageflipgallery.com/">' . $this->page_title . '</a>' );

		$this->functions->splitImage( WP_CONTENT_DIR . '/photo.jpg' );
		
		if( defined( 'PAGEFLIP_ERROR' ) ) echo PAGEFLIP_ERROR;
		echo $this->functions->check();


		echo $this->html->mainPage();
		echo '</div>';
	}

	
    function manage_books()
    {
    	echo '<div class="wrap">';

    	if( defined( 'PAGEFLIP_ERROR' ) )
		{
			echo PAGEFLIP_ERROR . '</div>';
			return false;
		}

		echo '<noscript>'.$this->functions->errorMessage( 'JavaScript is disabled. Please, enable JavaScript for correctly work.' ).'</noscript>';

		
		if( !empty( $_POST['thisdo'] ) ) $_POST['do'] = $_POST['thisdo'];

    	if( isset( $_POST['actionButton'] ) )
			switch( $_POST['action'] )
	        {
	         	case 'addbook' : $this->add_book(); break;
	         	case 'editbook' : $this->edit_book(); break;
	         	case 'addpage' : $this->add_page( $_POST['imageId'], $_POST['type'] ); break;
	         	case 'Assign Selected Images to Page' :
	         	{
	         		if( count( $_POST['images'] ) > 0 )
					 foreach( $_POST['images'] as $imageId )
	         			if( !$this->add_page( $imageId, $_POST['type'] ) ) break;
	         		unset( $_POST['do'] );
	         	} break;
	         	case 'Assign Images from Gallery' :
	         	{
	         		$this->addPageFromGallery( $_POST['galleryId'], $_POST['type'] );
	         		unset( $_POST['do'] );
	         	} break;
	         	case 'uploadimage' :
	         		if( ( $_POST['do'] == 'New Page' ) )
	         		{
	         			$imagesId = $this->upload_image( 'New page' );
	         			if( count( $imagesId ) > 1 )
	         			{
	         				foreach( $imagesId as $imageId )
	         					if( !$this->add_page( $imageId, $_POST['type']  ) ) break;
	         				unset( $_POST['do'] );
	         			}
	         		}
	         	 	break;
         	 	case 'Delete Book' : $this->delete_book( $_POST['id'] ); break;
	        }

        if( isset( $_POST['do'] ) )
         switch( $_POST['do'] )
         {
         	case 'Book Properties' : $this->book_form( $_POST['id'] ); break;
         	case 'Add Page' :
         	case 'New Page' :
         	{
         		echo $this->functions->printHeader( 'New Page to book #' . $_POST['id'] );
         		if( isset( $_POST['imageId'] ) && $_POST['action'] == 'Assign Image to Page' && isset( $_POST['actionButton'] ) )
         			$this->add_page_form( $_POST['id'], $_POST['imageId'], $_POST['type'] );
         		elseif( ( $_POST['action'] == 'uploadimage' ) && ( count( $imagesId ) == 1 ) )
         			$this->add_page_form( $_POST['id'], $imagesId[0] );
         		else
				{
				 echo '<div id="addPageMenu">' . $this->html->addPageMenu() . '</div>';
				 $this->galleriesList( $_POST['id'] );
				 
				}
         	} break;
         	case 'Upload New Images' :
         		echo $this->html->uploadImageForm( $_POST['id'] );
         		break;
         	case 'Add New Book' :
         		$this->book_form();
         		break;
         }
        else
        {
        	echo $this->functions->printHeader( __( 'Manage books and pages', 'pageFlip' ) );
        	echo $this->html->operationBookPreview();

			$this->books_list();

			echo $this->html->operationBookPreview( 'bottom' );
        }
        echo "</div>";
    }

    
    function images()
    {
        echo '<div class="wrap">';

		if( defined( 'PAGEFLIP_ERROR' ) )
		{
			echo PAGEFLIP_ERROR . '</div>';
			return false;
		}

		echo '<noscript>'.$this->functions->errorMessage( 'JavaScript is disabled. Please, enable JavaScript for correctly work.' ).'</noscript>';

		if( isset( $_POST['actionButton'] ) )
			switch( $_POST['action'] )
	        {
	         	case 'addbook' : $this->add_book(); break;
	        	case 'uploadimage' :
				 	{
				 		$this->upload_image();
				 		unset( $_POST['do'] );
				 	} break;
	        }

        switch( $_POST['do'] )
        {
         	case 'Upload New Images' : echo $this->html->uploadImageForm(); break;
         	case 'Upload Image' : echo $this->html->uploadImageForm( $_POST['bookId'] ); break;
         	case __('Create Book', 'pageFlip') :
         		global $wpdb;
         		$gallery = $wpdb->get_row("SELECT * FROM {$this->table_gal_name} WHERE id='{$_POST['galleryId']}' ");
         		$this->book_form('', $gallery->id);
         		break;
         	default :
         	    {
	         		
		     		
		     		$this->galleriesList();
	     		}
        }
        echo '</div>';

    }

	
	function books_list()
	{
        global $wpdb;

        $list = $this->html->ajaxPreviewBook();

        $list .= '<div id="bookList">';

        $list .= $this->html->headerPreviewBook();

	    $sql = "select `id`, `name`, `date` from `".$this->table_name."` order by `id`";
	    $books = $wpdb->get_results( $sql, ARRAY_A );

	    if( count($books) == "0" ) $list .= $this->html->noBooksPreviewBook();
        else foreach( $books as $curBook )
        {
        	 $creationDate = date( "m/d/Y", $curBook['date'] );

        	 $book = new Book( $curBook['id'] );

        	 $bookPreview = $this->bookPreview( $book );

             $list .= $this->html->previewBook ( $book, $curBook['name'], $creationDate, $bookPreview['first'], $bookPreview['second'] );
		}

        $list .= $this->html->footerPreviewBook();

        if( isset( $_POST['id'] ) )
			$list .= '<script type="text/javascript">
						//<![CDATA[
						pageList(' . $_POST['id'] . ');
						//]]>
				 	  </script>';

		$list .= '</div>';

        echo $list;
	}

	function bookPreview( $book = '' )
	{
		if ( empty( $book ))
		{
			$book = new Book( $_POST['bookId'] );
			$ajax = true;
		}
		else $ajax = false;

		
         
         if( ( ( $book->alwaysOpened == 'false' ) && ( (int)$book->firstPage % 2 == 1 ) ) ||
             ( ( $book->alwaysOpened == 'true' ) && ( (int)$book->firstPage % 2 == 0 ) ) )
	     {

	     		$firstPage = (int)$book->firstPage;
	     		$secondPage = (int)$book->firstPage + 1;
	     }
         else
         {
          	 $firstPage = (int)$book->firstPage - 1;
          	 $secondPage = (int)$book->firstPage;
         }

         $result['first'] = $this->functions->printImg( $book->pages[$firstPage]->image, $curBook['name'] );
         $result['second'] = $this->functions->printImg( $book->pages[$secondPage]->image, $curBook['name'] );

		 if ( $ajax )
		 {
			echo $result['first'] . '<split>' . $result['second'];
			exit;
		 }
		 else return $result;
	}


	function replacePages()
	{
		$book = new Book( $_POST['bookId'] );

		switch( $_POST['op'] )
		{
			case 'up' : {
				if( (int)$_POST['pageId'] > 0 )
				{
					$book->pages[((int)$_POST['pageId'] - 1)]->number++;
					$book->pages[(int)$_POST['pageId']]->number--;
				}
			} break;
			case 'down' : {
				if( (int)$_POST['pageId'] < $book->countPages )
				{
					$book->pages[(int)$_POST['pageId']]->number++;
					$book->pages[((int)$_POST['pageId'] + 1)]->number--;
				}
			} break;
			default : {
				$pages = split( ';', $_POST['pages'] );

				for( $i=0; $i < $book->countPages; $i++ )
					$book->pages[(int)$pages[$i]]->number = $i;
			}
		}

		$book->refreshPages(); 

		$book->save();

		exit;
	}

	function pagesList()
	{
        global $wpdb;

        $list  = $this->html->headerPreviewPage( $_POST['bookId'] );

        $book = new Book( $_POST['bookId'] );

        if( (int)$book->countPages === 0 ) $list .= $this->html->noPagesPreviewPage();
     	else
	        foreach( $book->pages as $id=>$page )
	        {
	        	if( trim( $book->alwaysOpened ) == 'false' )
	        	{
	        		if( ( $id % 2 == 0 ) ) $side = 'right';
	        		else $side = 'left';
	        	}
	        	else
	        	{
	        		if( ( $id % 2 == 0 ) ) $side = 'left';
	        		else $side = 'right';
	        	}

	        	$list .= $this->html->previewPage( $_POST['bookId'], $page, $side,
	        										 $this->functions->printImg( $page->image, $page->number, $this->thumbWidth, $this->thumbHeight, true ),
													   $book->countPages );
	        }

		$list .= $this->html->footerPreviewPage();

        echo $list;
        exit;
	}

	
	function add_book()
	{
        global $wpdb;

        foreach( $_POST as $key=>$value )
        {
        	$_POST["$key"] = trim( $value ); 
        	$_POST["$key"] = stripslashes( $value );
			$_POST["$key"] = htmlspecialchars( $value );
			$_POST["$key"] = $wpdb->escape( $value ); 
        	
        }

        if( empty( $_POST['bookName'] ) ) $_POST['bookName'] = 'unnamed';

        
        if( !empty( $_FILES['image']['name'][0] ) ) $imageId = $this->upload_image( 'bgImage' );
        else $imageId[0] = $_POST['bgImage'];

        
        $sql = "insert into `".$this->table_name."` (`name`, `date`, `bgImage`) values ('".$_POST['bookName']."', '".date("U")."', '".$imageId[0]."')";
        $wpdb->query( $sql );

        $id = $wpdb->get_var( "select LAST_INSERT_ID();", 0, 0 );

        
        $_POST['flipSound'] = $this->add_sound();

        
        $newBook = new Book();

		$newBook->id = $id;
		foreach( $newBook->properties as $property )
			if( !empty( $_POST[$property] ) || $property == 'flipSound' )
				$newBook->$property = $_POST[$property];

		
        if( !$newBook->save() )
        {
        	
        	$sql = "delete from `" . $this->table_name . "` where `id` = '" . $id . "'";
        	$wpdb->query( $sql );

        	echo __('Adding book error! Please setup permission to the books/ , images/ , sounds/ folders and include files to &quot;777&quot;', 'pageFlip');
		    return 0;
        }

		if ($galleryId = $_POST['galleryId'])
		{
			$images = $wpdb->get_results("SELECT * FROM `{$this->table_img_name}` WHERE `gallery`='{$galleryId}'");
			foreach ($images as $image)
			{
				$_POST['id'] = $id;
				$this->add_page($image->id, $image->type);
			}
			echo '<script type="text/javascript">location.href="?page=page-flip-image-gallery/books";</script>';
		}
	}

	
	function add_page( $imageId, $type )
	{
        global $wpdb;

        $book = new Book( $_POST['id'] );

        $imageName = isset($_POST['name']) ? htmlspecialchars( stripslashes( $_POST['name'] ) ) : NULL;

        switch( $type )
        {
        	case 'WPMedia' : {
        		$uploads = wp_upload_dir();
				$location = get_post_meta( $imageId, '_wp_attached_file', true );

				
				$image_path = $uploads['basedir'].'/'.$location;
				$new_url = $this->imagesUrl.basename($location);
				$new_path = $this->plugin_path.$this->imagesDir.'/'.basename($location);

				$_POST['galleryId'] = 0;
				$this->copyImage($new_path, $image_path, filesize($image_path), 'img', basename($location), 'copy');
        		$sql = "select `filename` from `" . $this->table_img_name . "` where `id` = '" . $wpdb->insert_id . "'";
	    		$img = $wpdb->get_row( $sql, ARRAY_A, 0 );
				$image = $this->functions->getImageUrl( $img['filename'] );
    			$filename = $img['filename'];
        	} break;
        	case 'NGGallery' : {
				$sql = "SELECT `filename`, `galleryid`, `alttext` FROM `".$wpdb->prefix."ngg_pictures` where `pid` = '".$imageId."'";
				$img = $wpdb->get_row($sql, ARRAY_A);
    			$filename = $img['filename'];
				$sql = "select `path` from `".$wpdb->prefix."ngg_gallery` where `gid` = '".$img['galleryid']."'";
				$path = $wpdb->get_var( $sql );

    			
    			$image_path = ABSPATH.$path.'/'.$img['filename'];
    			$new_url = $this->imagesUrl.$img['filename'];
    			$new_path = $this->plugin_path.$this->imagesDir.'/'.$img['filename'];

				$_POST['galleryId'] = 0;
				$this->copyImage($new_path, $image_path, filesize($image_path), 'img', $img['filename'], 'copy');
        		$sql = "select `filename` from `" . $this->table_img_name . "` where `id` = '" . $wpdb->insert_id . "'";
	    		$img = $wpdb->get_row( $sql, ARRAY_A, 0 );
				$image = $this->functions->getImageUrl( $img['filename'] );
        	} break;
        	default : {
        		$sql = "select `filename`, `name` from `" . $this->table_img_name . "` where `id` = '" . $imageId . "'";
	    		$img = $wpdb->get_row( $sql, ARRAY_A, 0 );
	    		$image = $this->functions->getImageUrl( $img['filename'] );
    			$filename = $img['name'];
	    	}
        }

    	if (!$imageName)
    	{
    		preg_match('|(.*)\..*?|', $filename, $m);
    		$imageName = $m[1];
    	}

	    $book->pages[$book->countPages] = new Page( $image, $book->countPages, $imageName );

        if( !$book->save() )
        {
        	echo __('Save file error! Please setup permission to the books/ , images/ , sounds/ folders and include files to &quot;777&quot;', 'pageFlip');
        	return false;
        }

        return true;
	}

	
	
	function addPageFromGallery( $galleryId, $type )
	{
        global $wpdb;

        switch( $type )
		{
			case 'NGGallery' :
				$sql = "SELECT `pid` as id FROM `".$wpdb->prefix."ngg_pictures` where `galleryId` = '".$galleryId."'";
			 break;
			default :
				$sql = "select `id` from `".$this->table_img_name."` where `type` = 'img' and `gallery` = '".$galleryId."'";
		}

		$images = $wpdb->get_results( $sql, ARRAY_A );
		if( count( $images ) > 0 )
			foreach( $images as $img )
				$this->add_page( $img['id'], $type );
	}

	
	function addGallery()
	{
		global $wpdb;

		$name = $wpdb->escape( $_POST['name'] );

		$sql = "insert into `".$this->table_gal_name."` (`name`, `date`, `preview`) values ('".$name."', '".date("U")."', 0)";
		$wpdb->query( $sql );

		exit;
	}

	
	function upload_image( $action='' )
	{
        global $wpdb;

        $imagesId = array();

	    
	    if( !empty( $_POST['url'] ) )
        {
	       	
	       	if( !$this->functions->isUrl( $_POST['url'] ) )
	       	{
	       		$txt = '<strong>' . $_POST['url'] . '</strong> - <strong>' . __('Error', 'pageFlip') . '</strong>: ' . __('Incorrect url', 'pageFlip') . '<br />';
				echo $this->functions->errorMessage( $txt );
				return false;
	       	}

	       	if( !$this->functions->checkImage( $_POST['url'] ) ) return false;

	       	$type = 'image';

			if( empty( $_POST['name'] ) ) $_POST['name'] = basename( $_POST['url'] );

			$_POST['name'] = stripslashes( $_POST['name'] );
			$_POST['name'] = htmlspecialchars( $_POST['name'] );
			$_POST['name'] = $wpdb->escape( $_POST['name'] ); 

			
		    $sql = "insert into `".$this->table_img_name."` (`name`, `filename`, `date`, `type`, `gallery`) values ('".$_POST['name']."', '".$_POST['url']."', '".date("U")."', '".$type."', '".$_POST['galleryId']."')";
		    $wpdb->query($sql);

	        if( ( $action == 'New page' ) || ( $action == 'bgImage' ) )
	        {
	          	$sql = "select LAST_INSERT_ID();";
	          	$imagesId[] = $wpdb->get_var( $sql, 0, 0 );
	        }

	        return true;
        }

        if( !empty( $_POST['folder'] ) )
        {
        	$curDir = ABSPATH . $_POST['folder'];
			if( is_dir( $curDir ) )
          	{
	  			$dir = opendir( $curDir ); 

				while ( $file = readdir( $dir ) )
				  if ( is_file( $curDir . $file ) )
				  {
		            	$size = filesize( $curDir . $file );
						$id = $this->copyImage( $file, $curDir . $file, $size, $action, '', 'rename' );
	            		if( $id ) $imagesId[] = $id;
				  }

		        closedir ( $dir ); 
          	}
        }

		
		if( !empty( $_FILES['zip']['name'] ) )
        {
        	@ini_set('memory_limit', '256M');

			
			if( $_FILES['zip']['type'] != 'application/zip'
				&& $_FILES['zip']['type'] != 'application/x-zip-compressed' )
				 {
				 	$txt = '<strong>' . $_FILES['zip']['name'] . '</strong> - <strong> ' . __('Error', 'pageFlip') . '</strong>: ' . __('This is not a zip file', 'pageFlip') . '<br />';
				    echo $this->functions->errorMessage( $txt );
					return false;
				 }

			if( ! class_exists( 'PclZip' ) )
			   require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );

			
			$dir =  $this->plugin_path . $this->imagesDir . '/';
			$archiveName = $dir . basename( $_FILES['zip']['tmp_name'] );
			$folderName = $archiveName . '_folder/';
			copy( $_FILES['zip']['tmp_name'], $archiveName );

			
			$zip = new PclZip( $archiveName );

			
			$extractFiles = $zip->extract( PCLZIP_OPT_PATH, $folderName );

			
			if( $extractFiles == 0 )
			{
			 	$txt = '<strong>' . $_FILES['zip']['name'] . '</strong> - <strong>' . __('Extracting Error', 'pageFlip') . '</strong><br />';
			    echo $this->functions->errorMessage( $txt );
				return false;
			}

			
			foreach ( $extractFiles as $image )
			{
				$id = $this->copyImage( $image['stored_filename'], $image['filename'], $image['size'], $action, '', 'rename' );
	            if( $id ) $imagesId[] = $id;
			}

			
			@unlink( $archiveName );
			
			$this->functions->removeDir( $folderName );
        }

        
		if( !empty( $_FILES['image']['name'] ) )
			foreach( $_FILES['image']['name'] as $id=>$imageName )
	        {
	            $id = $this->copyImage( $imageName, $_FILES['image']['tmp_name'][$id], $_FILES['image']['size'][$id], $action, $_POST['name'][$id] );
	            if( $id ) $imagesId[] = $id;
	        }

        unset( $_POST['name'] ); 

	    return $imagesId;
	}

	
	function copyImage( $imageName, $tmpName, $size, $action = 'img', $name = '', $functionName = 'move_uploaded_file' )
	{
	   global $wpdb;

	   @ini_set( 'memory_limit', '256M' );


	   
	   if( $size == 0 )
	   {
	   	$txt = '<strong>' . $imageName . '</strong> - <strong>' . __('Error', 'pageFlip') . '</strong>: '. __('This file is too big', 'pageFlip') . '<br />';
		echo $this->functions->errorMessage( $txt );
		return false;
	   } 



	   if( !$this->functions->checkImage( $imageName ) ) return false;

	   
	   switch( $action )
	   {
	   		case 'bgImage' : $type = 'bg'; break;
	   		default : $type = 'img';
	   }

	   $fileExt = split( "\.", $imageName );

	   
       $dir =  $this->plugin_path . $this->imagesDir . '/';
       

	   
       
       

	   
		do
		{
			$filename = $this->functions->fileName( $type, $imageName );
			$new_filename = $dir . $filename;
		}
		while( file_exists( $new_filename ) );

		
	    $thumbName = $dir . 't_' . basename( $new_filename );
	    $imgSize = GetImageSize( $tmpName );
	    $newSize = $this->functions->imgSize( $imgSize[0], $imgSize[1], $this->thumbWidth, $this->thumbHeight );

		switch( strtolower( $fileExt['1'] ) )
        {
        	case 'swf' :
        		if( !$functionName( $tmpName, $new_filename ) )
        		{
	            	unlink( $new_filename );
	            	$txt = '<strong>' . $imageName . '</strong> - <strong>' . __('Error', 'pageFlip') . ' [001]</strong>: ' . __('Write file error! Please setup permission to the books/ , images/ , sounds/ folders and include files to &quot;777&quot;', 'pageFlip') . '<br/>';
	            	echo $this->functions->errorMessage( $txt );
					return false;
	            } break;
        	default :
        		if( !$this->functions->img_resize( $tmpName, $thumbName, $newSize['width'], $newSize['height'] )
	                || !$functionName( $tmpName, $new_filename ) )
	            {
	            	unlink( $new_filename ); unlink( $thumbName );
	            	$txt = '<strong>' . $imageName . '</strong> - <strong>' . __('Error', 'pageFlip') . ' [002]</strong>: ' . __('Write file error! Please setup permission to the books/ , images/ , sounds/ folders and include files to &quot;777&quot;', 'pageFlip') . '<br/>';
	            	echo $this->functions->errorMessage( $txt );
					return false;
	            }
        }

        
		
		if( empty( $name ) ) $name = $imageName;
	    else $name = $wpdb->escape( $name ); 

	    
	    $sql = "insert into `".$this->table_img_name."` (`name`, `filename`, `date`, `type`, `gallery`) values ('".$name."', '".basename($new_filename)."', '".date("U")."', '".$type."', '".$_POST['galleryId']."')";
	    $wpdb->query( $sql );

        if( ($action == 'New page') || ($action == 'bgImage') )
        {
          	$sql = "select LAST_INSERT_ID();";
          	return $wpdb->get_var( $sql, 0, 0 );
        }

		return false;
	}

	
	function uploadForm()
	{
		echo $this->html->uploadImageMenu();
		echo '<split>';

		switch( $_POST['type'] )
		{
			case 'swfUpload' : echo $this->html->uploadSwfForm(); break;
			case 'zip' : echo $this->html->uploadZipForm(); break;
			case 'fromUrl' : echo $this->html->uploadFromUrlForm(); break;
			case 'fromFolder' : echo $this->html->uploadFromFolder(); break;
			default : echo $this->html->uploadImgForm();
		}

		exit;
	}

	
	function edit_book()
	{
        global $wpdb;

        foreach($_POST as $key=>$value)
        {
        	$_POST[$key] = trim( $value );
        	$_POST["$key"] = stripslashes( $value );
			$_POST["$key"] = htmlspecialchars( $value );
			$_POST["$key"] = $wpdb->escape($value); 
        	
        }

        if( empty( $_POST['bookName'] ) ) $_POST['bookName'] = 'unnamed';

        
        if( !empty($_FILES['image']['name'][0]) ) $imageId = $this->upload_image( "bgImage" );
        else $imageId[0] = $_POST['bgImage'];

        
        $sql = "update `".$this->table_name."` set `name` = '".$_POST['bookName']."', `bgImage` = '".$imageId[0]."' where `id` = '".$_POST['bookId']."'";
        $wpdb->query( $sql );

        
        $_POST['flipSound'] = $this->add_sound();;

        $book = new Book( $_POST['bookId'] );

        
        foreach( $book->properties as $property )
			if( (string)$_POST[$property] !== '' || $property == 'flipSound' )
				$book->$property = $_POST[$property];

		
        if( !$book->save() )
        {
        	$txt = __('Save file error! Please setup permission to the books/ , images/ , sounds/ folders and include files to &quot;777&quot;', 'pageFlip');
        	echo $this->functions->errorMessage( $txt );
			return false;
        }
	}

	
	function delete_book($bookId)
	{
        global $wpdb;

        @unlink($this->plugin_path . $this->booksDir . '/' . $bookId . '.xml');

        
        $sql = "delete from `".$this->table_name."` where `id` = '".$bookId."'";
        
        $wpdb->query($sql);

        unset($_POST['do']);

        
        
	}

	
    function images_list( $bookId = 0, $gallery = 0 )
    {
    	global $wpdb;

    	if( isset( $_POST['bookId'] ) )
    	{
    		$bookId = $_POST['bookId'];
    		$gallery = $_POST['gallery'];
    	}

    	if( (int)$_POST['page'] < 1 ) $_POST['page'] = 1;

		$navigation = $this->functions->navigationBar( $_POST['page'], get_option( 'pageFlip_imgPerPage' ), $_POST['type'], '', $gallery );

    	$start = ( $navigation['page'] - 1 ) * get_option( 'pageFlip_imgPerPage' );

    	switch( $_POST['type'] )
    	{
    		case 'NGGallery' : {
    			$sql = "select `title` from `".$wpdb->prefix."ngg_gallery` where `gid` = '".$gallery."'";
		    	$galleryName = $wpdb->get_var( $sql );
    		} break;
    		case 'pageFlip' : {
    			if( (int)$gallery === 0 ) $galleryName = __('Unsorted', 'pageFlip');
		    	else
		    	{
		    		$sql = "select `name` from `".$this->table_gal_name."` where `id` = '".$gallery."'";
		    		$galleryName = $wpdb->get_var( $sql );
		    	}
    		} break;
    	}

		if( $_POST['type'] === 'pageFlip' || $_POST['type'] === 'NGGallery' )
    	{
			$header = '<a href="#" onclick="return viewGalleries();">' . __('Galleries', 'pageFlip') . '</a> -> ' . __('Images from gallery', 'pageFlip') . ' &quot;'. $galleryName . '&quot;';

			if( (int)$bookId === 0 )
				$list .= $this->functions->printHeader( $header );
			else
				$list .= '<p style="font-size: medium;">' . $header . '</p>';
		}
    	
    	

 		

    	

		$list .= $this->html->operationPreviewImage( $bookId, 'top', $navigation, $_POST['type'], $gallery );

		$list .= $this->html->headerPreviewImage();

        $list .= $this->viewImagesList( $bookId, $start, get_option( 'pageFlip_imgPerPage' ), $_POST['type'], $gallery );

		$list .= $this->html->footerPreviewImage();

		$list .= $this->html->operationPreviewImage( $bookId, 'bottom', $navigation, $_POST['type'], $gallery );

		

        echo $list;

        if( isset( $_POST['bookId'] ) )
		{
			echo '<split>' . $navigation['page'];
			exit;
		}
    }

    
    function galleriesList( $bookId = 0 )
    {
    	global $wpdb;

    	$list = '';

		if( isset( $_POST['bookId'] ) )
		{
			$bookId = $_POST['bookId'];
			$type = $_POST['type'];
		}
		else
		{
    	  	$list = $this->html->ajaxPreviewImage( $bookId );
    	  	$type = 'pageFlip';

    		$list .= '<div id="addPage">';
    	}

    	if( (int)$bookId === 0 )
			$list .= $this->functions->printHeader( __('Galleries', 'pageFlip') );

    	$list .= '<div id="pageFlipTop">';

		if( (int)$bookId === 0 )
			$list .= $this->html->operationPreviewGallery( $bookId );
		else $list .= '&nbsp;';

		$list .= '</div>';

    	$list .= '<div id="pageFlipList">';

 		
 		$list .= $this->html->headerPreviewGallery();

        $list .= $this->viewGalleriesList( $bookId, $type );

		$list .= $this->html->footerPreviewGallery();

		$list .= '</div>';

		if( isset( $_POST['bookId'] ) )
		{
			echo $list;
			exit;
		}
		else
		{
			$list .= '</div>';
        	echo $list;
  		}
    }

    function pagingImages()
    {
    	if( (int)$_POST['page'] < 1 ) $_POST['page'] = 1;

		$navigation = $this->functions->navigationBar( $_POST['page'], get_option( 'pageFlip_imgPerPage' ), $_POST['type'] );

		echo $navigation['bar'];

    	echo '<split>';

    	$start = ( $navigation['page'] - 1 ) * get_option( 'pageFlip_imgPerPage' );

    	echo $this->viewImagesList( $_POST['bookId'], $start, get_option( 'pageFlip_imgPerPage' ), $_POST['type'], $_POST['gallery'] );

		
		

    	

		

		

        

		

		

		
		

		echo '<split>';

		echo $navigation['page'];

		exit;
    }

	
	function viewImagesList( $bookId = 0, $start = 0, $count = 0, $type = 'pageFlip', $gallery = 0 )
	{
		if ( $count > 0 ) $limit = "limit ".$start.", ".$count;
		else $limit = '';

		switch( $type )
		{
			case 'WPMedia' : return $this->viewWPMediaImgList( $bookId, $limit ); break;
			
			case 'NGGallery' : return $this->viewNGGalleryImgList( $bookId, $limit, $gallery ); break;
			default : return $this->viewPageFlipImgList( $bookId, $gallery, $limit );
		}
	}

	
	function viewGalleriesList( $bookId = 0, $type = 'pageFlip' )
	{
		global $wpdb;

		$result = '';

		switch( $type )
		{
			case 'NGGallery' : {
				$sql = "select `gid` as id, `path`, `title` as name, `previewpic` from `".$wpdb->prefix."ngg_gallery`";
				$galleries = $wpdb->get_results($sql, ARRAY_A);
			} break;
			default : {
				$sql = "SELECT `id`, `name`, `date`, `preview` FROM `".$this->table_gal_name."` ORDER BY `name` ASC ";
				$galleries = $wpdb->get_results( $sql, ARRAY_A );
			}
		}

        
		if( (int)count( $galleries ) > 0 )
		    foreach( $galleries as $gallery )
		    {
		       	$sql = $this->functions->sqlImgList( 'count', $type, $gallery['id'] );
		    	$countImg = $wpdb->get_var( $sql );

		    	if( $countImg > 0 )
		    		$imageUrl = $this->functions->getGalleryPreview( $gallery['id'], $type );
		    	else $imageUrl = '';

				if( $type === 'pageFlip' ) $creationDate = date( "d/m/Y", $gallery['date'] );
				else $creationDate = '';

		        $result .= $this->html->previewGallery( $bookId, $gallery['id'], $gallery['name'], $countImg, $creationDate,
		        										 $this->functions->printImg( $imageUrl, '', '', '', true ), $type );
		    }

	    if( $type === 'pageFlip' )
	    {
			$sql = $this->functions->sqlImgList( 'count', $type, 0 );
			$count = $wpdb->get_var( $sql );

		    if( (int)$count > 0 )
		    {
				$sql = "select `filename` from `".$this->table_img_name."` where `type` = 'img' and `gallery` = '0' order by RAND() limit 1";
		    	$imageUrl = $this->functions->getImageUrl( $wpdb->get_var( $sql ) );

				$result .= $this->html->previewGallery( $bookId, 0, __('Unsorted', 'pageFlip'), $count, '',
		        										 $this->functions->printImg( $imageUrl, '', '', '', true ) );
		    }
	    }
	    elseif( count( $galleries )  == 0 )
			$result = "<tr class=\"alternate author-self status-publish\" valign=\"top\">
				          <td colspan=\"5\" style=\"text-align: center;\"><strong>" . __('No galleries', 'pageFlip') ."</strong></td>
		        	   </tr>";

		return $result;
	}

	
	function viewAlbumsList( $bookId = 0, $start = 0, $count = 0, $type = 'pageFlip' )
	{
		if ( $count > 0 ) $limit = "limit ".$start.", ".$count;
		else $limit = '';

		switch( $type )
		{
			case 'WPMedia' : return $this->viewWPMediaImgList( $bookId, $limit ); break;
			
			case 'NGGallery' : return $this->viewNGGalleryImgList( $bookId, $limit ); break;
			default : return $this->viewPageFlipImgList( $bookId, $limit );
		}
	}

	
	function viewPageFlipImgList( $bookId, $gallery, $limit )
	{
		global $wpdb;

		$result = '';

		$sql = $this->functions->sqlImgList( 'list', 'pageFlip', $gallery ).$limit;

		$images = $wpdb->get_results($sql, ARRAY_A);
        if( count($images) == "0" ) $result = "<tr class=\"alternate author-self status-publish\" valign=\"top\">
	                 					          <td colspan=\"5\" style=\"text-align: center;\"><strong>" . __('No images', 'pageFlip') ."</strong></td>
					  				            </tr>";
	    else foreach($images as $img)
	    {
	       	$imageUrl = $this->functions->getImageUrl( $img['filename'] );

			$uploadDate = date( "d/m/Y", $img['date'] );
	        $result .= $this->html->previewImage( $bookId, $img['id'], $img['name'], $uploadDate,
	        										 $this->functions->printImg( $imageUrl, $img['name'], '', '', true ), $gallery );
	    }

		return $result;
	}

	
	function viewWPMediaImgList( $bookId, $limit )
	{
		global $wpdb;

		$result = '';

    	$uploads = wp_upload_dir();

    	$sql = $this->functions->sqlImgList( 'list', 'WPMedia' ).$limit;
		$WPImages = $wpdb->get_results($sql, ARRAY_A);
		if( count( $WPImages )  == 0 ) $result = "<tr class=\"alternate author-self status-publish\" valign=\"top\">
	                 					          <td colspan=\"5\" style=\"text-align: center;\"><strong>" . __('No images', 'pageFlip') ."</strong></td>
					  				            </tr>";
		else
		 foreach( $WPImages as $img )
		 {
			$location = get_post_meta( $img['post_id'], '_wp_attached_file', true );
    		$filetype = wp_check_filetype( $location );

			if( ( substr( $filetype['type'], 0, 5 ) == 'image' ) && ( $thumb = wp_get_attachment_image( $img['post_id'], array(80, 60), true ) ) )
    		{
				$att_title = wp_specialchars( _draft_or_post_title( $img['post_id'] ) );
    			$result .= $this->html->previewImage( $bookId, $img['post_id'], $att_title, '', $thumb, 'WPMedia' );
    		}
		 }

		return $result;
	}

	
	function viewNGGalleriesList( $bookId )
	{
		global $wpdb;

		$result = '';

    	$sql = "select `gid`, `path`, `title`, `previewpic` from `".$wpdb->prefix."ngg_gallery`";
		$NGGalleries = $wpdb->get_results($sql, ARRAY_A);
		if( count( $NGGalleries )  == 0 ) $result = "<tr class=\"alternate author-self status-publish\" valign=\"top\">
	                 					          <td colspan=\"5\" style=\"text-align: center;\"><strong>" . __('No galleries', 'pageFlip') ."</strong></td>
					  				            </tr>";
		else
		 foreach( $NGGalleries as $gallery )
		 {
			$sql = "SELECT `filename` FROM `".$wpdb->prefix."ngg_pictures` where `pid` = '".$gallery['previewpic']."'";
			$imageUrl = get_option( 'siteurl' ) . '/' . $gallery['path'] . '/thumbs/thumbs_' . $wpdb->get_var( $sql );
			$result .= $this->html->previewImage( $bookId, $gallery['pid'], $gallery['title'], '',
												 	$this->functions->printImg( $imageUrl, $gallery['title'], '', '', true ) , 'NGGallery' );
		 }

		return $result;
	}

	
	function viewNGGalleryImgList( $bookId, $limit, $gallery )
	{
		global $wpdb;

		$result = '';

    	$sql = $this->functions->sqlImgList( 'list', 'NGGallery', $gallery ).$limit;
		$NGGImages = $wpdb->get_results($sql, ARRAY_A);
		if( count( $NGGImages )  == 0 ) $result = "<tr class=\"alternate author-self status-publish\" valign=\"top\">
	                 					          <td colspan=\"5\" style=\"text-align: center;\"><strong>" . __('No images', 'pageFlip') ."</strong></td>
					  				            </tr>";
		else
		 foreach( $NGGImages as $img )
		 {
			$sql = "select `path` from `".$wpdb->prefix."ngg_gallery` where `gid` = '".$img['galleryid']."'";
			$imageUrl = get_option( 'siteurl' ) . '/' . $wpdb->get_var( $sql ) . '/thumbs/thumbs_' . $img['filename'];

			$result .= $this->html->previewImage( $bookId, $img['pid'], $img['alttext'], $img['imagedate'],
												 	$this->functions->printImg( $imageUrl, $img['alttext'], '', '', true ) , 'NGGallery' );
		 }

		return $result;
	}

	
	function addPageMenu()
	{
		echo $this->html->addPageMenu( $_POST['type'] );

		echo '<split>';

		echo $this->html->buttonsOpImages( $_POST['bookId'], $_POST['type'] );

		exit;
	}

	
	function setImgPerPage()
	{
		update_option( 'pageFlip_imgPerPage', (int)$_POST['count'] );
		exit;
	}

    
    function delete_page()
    {
		$book = new Book( $_POST['bookId'] );

        $book->deletePage( $_POST['pageId'] ); 

		
        $book->save();
    }

    
    function splitImage()
    {
		$book = new Book( $_POST['bookId'] );

		if( $this->functions->checkPic( $book->pages[(int)$_POST['pageId']]->image ) != 'pageFlip' ) exit;

        $image = $this->imagesPath . basename( $book->pages[(int)$_POST['pageId']]->image );

		$newImages = $this->functions->splitImage( $image );
        if( !$newImages ) return false;

        $firstImage = $this->imagesUrl . basename( $newImages[0] );
        $secondImage = $this->imagesUrl . basename( $newImages[1] );

        

        $book->pages[(int)$_POST['pageId']] = new Page( $firstImage, $_POST['pageId'], '' );

        for( $i = $book->countPages; $i > $_POST['pageId'] + 1; $i-- )
        {
        	$book->pages[$i] = $book->pages[($i - 1)];
        	$book->pages[$i]->number = $i;
        }

        $book->pages[($_POST['pageId'] + 1)] = new Page( $secondImage, ($_POST['pageId'] + 1), '' );

		$book->refreshPages(); 

		
        $book->save();

        exit;
    }

    
    function mergeImage()
    {
		$book = new Book( $_POST['bookId'] );

		$secondImage = substr( basename( $book->pages[(int)$_POST['pageId']]->image ), 2 );
		$mergeImage = $this->imagesUrl . $this->functions->getSplitImageName( $book->pages[(int)$_POST['pageId']]->image );

		@unlink( $this->imagesPath . basename( $book->pages[(int)$_POST['pageId']]->image ) ); 
		@unlink( $this->imagesPath . 't_' . basename( $book->pages[(int)$_POST['pageId']]->image ) ); 

		
		$book->pages[(int)$_POST['pageId']] = new Page( $mergeImage, $_POST['pageId'], '' );

        
        foreach( $book->pages as $page )
         	if( substr( basename( $page->image ), 2) == $secondImage )
        	{
				@unlink( $this->imagesPath . basename( $page->image ) ); 
				@unlink( $this->imagesPath . 't_' . basename( $page->image ) ); 

				$book->deletePage( $page->number );
				break;
        	}

		
        $book->save();

        exit;
    }

    
    function delete_image( $imageId = '' )
    {
        global $wpdb;

        if( $imageId === '' )
		{
			$imageId = $_POST['imageId'];
			$ajax = true;
		}

        $sql = "select `filename` from `".$this->table_img_name."` where `id` = '".$imageId."'";
	    $img = $wpdb->get_row($sql, ARRAY_A, 0);

        
        $sql = "delete from `".$this->table_img_name."` where `id` = '".$imageId."'";
        
        $wpdb->query($sql);

        if( !$this->functions->isUrl( $img['filename'] ) )
        {
			$page =  $this->plugin_path . $this->imagesDir . '/' . $img['filename'];

	        @unlink( $page );

	        
	        $fileExt = split( "\.", $img['filename'] );
	        if( $fileExt[1] != "swf" )
	        {
	        	 $thumb = $this->plugin_path . $this->imagesDir . '/t_' . $img['filename'];
	        	 @unlink( $thumb );
	        }
        }

        if( $ajax ) exit;

        
    }

    
    function deleteImages()
    {
    	if( empty( $_POST['imageList'] ) ) return false;

		$images = split( ';', $_POST['imageList'] );

		foreach( $images as $imageId )
			$this->delete_image( $imageId );

		exit;
    }

	function deleteGallery()
	{
		global $wpdb;

		
		$sql = "select `id` from `".$this->table_img_name."` where `type` = 'img' and `gallery` = '".$_POST['gallery']."'";
		$images = $wpdb->get_results($sql, ARRAY_A);
		if( count( $images ) > 0 )
			foreach( $images as $img ) $this->delete_image( $img['id'] );

		
        $sql = "delete from `".$this->table_gal_name."` where `id` = '".$_POST['gallery']."'";
        
        $wpdb->query($sql);

		exit;
	}

    
    function moveImgTo( $galleryId = '', $imageId = '' )
    {
    	global $wpdb;

    	if( ( $galleryId === '' ) || ( $imageId === '' ) )
    	{
    		$galleryId = (int)$_POST['gallery'];
    		$imageId = (int)$_POST['imageId'];
    	}

		$sql = "update `".$this->table_img_name."` set `gallery` = '".$galleryId."' where `id` = '".$imageId."'";
    	$wpdb->query( $sql );

		if( ( $galleryId === '' ) || ( $imageId === '' ) ) exit;
    }

    
    function moveImgsTo()
    {
    	if( empty( $_POST['imageList'] ) ) return false;

		$images = split( ';', $_POST['imageList'] );

		foreach( $images as $imageId )
			$this->moveImgTo( $_POST['gallery'], $imageId );

		exit;
	}

    
	function add_sound()
	{
        if ($_FILES['sound']['name'])
        {
           if($_FILES["sound"]["size"] > $this->maxSoundSize) {echo __("This file is too big", 'pageFlip'); return 0;} 
           
	       $fileExt = split("\.", $_FILES['sound']['name']);
	       if(strtolower($fileExt['1']) != "mp3"){echo __("Wrong file type", 'pageFlip'); return 0;} 
	       
           $dirName = $this->plugin_path.$this->soundsDir."/";

           
           $maxNum = 0;
           $dir = opendir($dirName); 

	       while ($sound = readdir($dir))
	       {
	          if ($sound != '.' && $sound != '..')
	          {
	            $name = split("\.", $sound);
	            if((int)$name["0"] > (int)$maxNum) $maxNum = $name["0"];
	          }
	       }

	       closedir ($dir); 

	       
           $filename =  ( $maxNum + 1 ) . '.' . $fileExt['1'];

	       $new_filename = $dirName . $filename;

	       $_POST['flipSound'] = basename($new_filename);

	       if(!copy( $_FILES['sound']['tmp_name'], $new_filename ) ) {echo __("Write file error!", 'pageFlip'); return '';}
	    }

	    if( $_POST['flipSound'] !== '' ) $flipSound = $this->plugin_url . $this->soundsDir . '/' . $_POST['flipSound']; 
	    else $flipSound = '';

	    return $flipSound;
	}
	
    function check_db()
    {
         global $wpdb;

         $fieldsPageFlip = array( 'id' => 'BIGINT( 20 ) NOT NULL AUTO_INCREMENT PRIMARY KEY',
		 						  'name' => 'TEXT NOT NULL',
								  'date' => 'BIGINT( 11 ) NOT NULL DEFAULT \''.date("U").'\'',
								  'bgImage' => 'BIGINT( 11 ) NOT NULL'
								 );

		 $fieldsPageFlipImg = array( 'id' => 'BIGINT( 20 ) NOT NULL AUTO_INCREMENT PRIMARY KEY',
		 							 'name' => 'TEXT NOT NULL',
		 							 'filename' => 'TEXT NOT NULL',
		 							 'date' => 'BIGINT( 11 ) NOT NULL DEFAULT \''.date("U").'\'',
		 							 'type' => 'VARCHAR( 10 ) NOT NULL DEFAULT \'img\'',
		 							 'gallery' => 'BIGINT( 20 ) NOT NULL'
								   );

		$fieldsPageFlipGallery = array( 'id' => 'BIGINT( 20 ) NOT NULL AUTO_INCREMENT PRIMARY KEY',
		 							 	'name' => 'TEXT NOT NULL',
		 							 	'date' => 'BIGINT( 11 ) NOT NULL DEFAULT \''.date("U").'\'',
		 							 	'preview' => 'BIGINT( 20 ) NOT NULL'
								  	  );


		 
		 $this->functions->createTable( $this->table_name, $fieldsPageFlip );

		 
		 $this->functions->checkTable( $this->table_name, $fieldsPageFlip );

		 
		 $this->functions->createTable( $this->table_img_name, $fieldsPageFlipImg );

		 
		 $this->functions->checkTable( $this->table_img_name, $fieldsPageFlipImg );

		 
		 $this->functions->createTable( $this->table_gal_name, $fieldsPageFlipGallery );

		 
		 $this->functions->checkTable( $this->table_gal_name, $fieldsPageFlipGallery );
    }

	
    function check_dir()
    {
          global $pageFlipError;

		  

          $pageFlipError = '';

          if( $this->functions->createDir( $this->plugin_path ) )
          {
	          $this->functions->createDir( $this->plugin_path . $this->booksDir );
	          $this->functions->createDir( $this->plugin_path . $this->soundsDir );
	          $this->functions->createDir( $this->plugin_path . $this->imagesDir );
	          $this->functions->createDir( $this->plugin_path . $this->uploadDir );
          }

          if( $pageFlipError !== '' ) define( 'PAGEFLIP_ERROR', $pageFlipError );

		  
		  $oldFolders = array( $this->booksDir, $this->imagesDir, $this->soundsDir );
		  foreach( $oldFolders as $folder)
		  {
          	$curDir = WP_PLUGIN_DIR . '/' . $this->plugin_dir . '/' . $folder . '/';
			if( is_dir( $curDir ) )
          	{
	  			$dir = opendir( $curDir ); 

				while ( $file = readdir( $dir ) )
				  if ( is_file( $curDir . $file ) )
				  {
		            if( $folder === $this->booksDir ) 
		            {
		            	$book = join( '', file( $curDir . $file ) );
		            	$book = str_replace( WP_PLUGIN_URL . '/' . $this->plugin_dir . '/', $this->plugin_url, $book );
						$bookFile = fopen( $this->plugin_path . $folder . '/' . $file, 'w+' );
						if( fwrite( $bookFile, $book ) ) @unlink( $curDir . $file );
						fclose( $bookFile );
		            }
		            else
						@rename( $curDir . $file, $this->plugin_path . $folder . '/' . $file );
				  }

		        closedir ( $dir ); 

		        @rmdir( $curDir );
          	}
          }
    }

	
	function removeDir($dirName)
	{
	      
	      if(!is_dir($dirName)) return true;
	      
	      $delete_dir = opendir($dirName);
	      chdir($dirName);
	      while ($delete = readdir($delete_dir))
	      {
	             if(is_dir($delete) && ($delete !== ".") && ($delete !== "..")) $del_dir_names[] = $delete;
	             if(is_file($delete)) $del_file_names[] = $delete;
	      }
	      
	      if( is_dir("0/") ) $del_dir_names[] = "0/";

	      if(isset($del_file_names))
	       foreach($del_file_names as $delete_this_file) unlink($dirName.$delete_this_file);

	      if(isset($del_dir_names))
	       foreach($del_dir_names as $delete_this_dir) $this->removeDir($dirName.$delete_this_dir."/");

	      closedir($delete_dir);
	      if(rmdir($dirName)) return true;
	      else return false;
	}

	
	function addPageForm()
	{
		echo $this->add_page_form( $_POST['bookId'], $_POST['imageId'], $_POST['type'] );
		exit;
	}


	
	function add_page_form($id, $imageId, $type='pageFlip' )
	{
        global $wpdb;

        switch( $type )
        {
        	case 'WPMedia' : {
    			$image = wp_get_attachment_image( $imageId, array(80, 60), true );
    			$name = wp_specialchars( _draft_or_post_title( $imageId ) );
        	} break;
        	case 'NGGallery' : {
				$sql = "SELECT `filename`, `galleryid`, `alttext` FROM `".$wpdb->prefix."ngg_pictures` where `pid` = '".$imageId."'";
				$img = $wpdb->get_row($sql, ARRAY_A);
				$sql = "select `path` from `".$wpdb->prefix."ngg_gallery` where `gid` = '".$img['galleryid']."'";

    			$image = get_option( 'siteurl' ) . '/' . $wpdb->get_var( $sql ) . '/thumbs/thumbs_' . $img['filename'];
				$image = $this->functions->printImg( $image, $img['alttext'] );
				$name = $img['alttext'];
        	} break;
        	default : {
        		$sql = "select `name`, `filename` from `".$this->table_img_name."` where `id` = '".$imageId."'";
			    $img = $wpdb->get_row($sql, ARRAY_A, 0);

			    $imageUrl = $this->functions->getImageUrl( $img['filename'] );
			    $image = $this->functions->printImg( $imageUrl, $img['name'] );
    			$name = $img['name'];
        	}
        }

        echo $this->html->addPageForm( $id, $imageId, $image, $name, $type );
	}

	
	function flashEditor( $do )
	{
		switch( $do )
		{
			case 'loadalbumxml' : echo $this->functions->loadAlbumXml( (int)$_POST['bookId'] ); break;
			case 'savealbumxml' : $this->functions->saveAlbumXml( (int)$_POST['bookId'] ); break;
			case 'loadlayouts' : echo $this->functions->loadLayouts( ); break;
		}
		exit;
	}

    
	function book_form( $bookId = '', $galleryId = '' )
	{
		global $wpdb;
		

		$thisBook = new Book( $bookId );

		if( $bookId == '' )
        {
            $book['name'] = '';
            $book['button'] = __('Add Book', 'pageFlip');
            $book['title'] = __('Add Book', 'pageFlip');
            $book['action'] = 'addbook';
            $book['bgImage'] = '0';
        	if ($galleryId)
        	{
        		$gallery = $wpdb->get_row("SELECT * FROM `{$this->table_gal_name}` WHERE `id`='{$galleryId}'");
        		$book['name'] = $gallery->name;
        	}
        }
        else 
        {
            global $wpdb;
            $sql = "select `name`, `bgImage` from `".$this->table_name."` where `id` = '".$bookId."'";

            
            $book['name'] = $wpdb->get_var($sql, 0, 0);
            $book['button'] = __('Save Changes', 'pageFlip');
            $book['title'] = __('Book properties', 'pageFlip');
            $book['action'] = 'editbook';
            $book['bgImage'] = $wpdb->get_var($sql, 1, 0);
        }

        
        $dir_name = $this->plugin_path . $this->soundsDir . '/';
        $dir = opendir( $dir_name ); 

        $flipSound = '<select size="1" name="flipSound" id="flipSound">';
        $flipSound .= '<option value="">' . __('No sound', 'pageFlip') . '</option>';
        while ( $sound = readdir( $dir ) )
        {
          if ( $sound != '.' && $sound != '..' )
          {
            $flipSound .= '<option value="' . $sound . '"';
            if( basename( $thisBook->flipSound ) == $sound )   $flipSound .= ' selected="selected"';
            $flipSound .= '>' . $sound . '</option>';
          }
        }
        $flipSound .= '</select>';

        closedir ( $dir ); 

        
        $sql = "select `id`, `name`, `filename` from `" . $this->table_img_name . "` where `type` = 'bg' order by `id`";
	    $bgrounds = $wpdb->get_results( $sql, ARRAY_A );

	    $bgImageUrl = '';

        $bgImageList = '<select size="1" name="bgImage" id="bgImage" onchange="viewBackground(this);">';
        $bgImageList .= '<option value="-1"';
        if($book['bgImage'] == "-1")   $bgImageList .= ' selected="selected"';
        $bgImageList .= '>' . __('No Background', 'pageFlip') . '</option>' .
						'<option value="0"';

		if( $book['bgImage'] == "0" )
        {
        	$bgImageList .= ' selected="selected"';
        	$bgImageUrl = $this->bgFile;
        }

        $bgImageList .= '>' . __('default', 'pageFlip') . '</option>';

        $bgImagesAr .= 'case \'0\' : preview = \'' . str_replace( "/", "\\/", $this->functions->printImg( $this->bgFile, 'default' ) ) . '\'; break; ' . "\n";

        if( count( $bgrounds ) > 0 )
         foreach ( $bgrounds as $bground )
         {
             $bgImageList .= '<option value="' . $bground['id'] . '"';
             if( $book['bgImage'] == $bground['id'] )
             {
             	$bgImageList .= ' selected="selected"';
             	$bgImageUrl = $this->plugin_url . $this->imagesDir . '/' . $bground['filename'];
             	$bgImageName = $bground['name'];
             }
             $bgImageList .= '>' . $bground['name'] . '</option>';

             $bgImagesAr .= 'case \'' . $bground['id'] . '\' : preview = \'' . str_replace( "/", "\\/", $this->functions->printImg( $this->plugin_url.$this->imagesDir . '/' . $bground['filename'], $bground['name'] ) ) . '\'; break; \n';
         }
        $bgImageList .= '</select>';

        
        echo $this->html->bookForm( $book['title'], $book['name'], $thisBook,
									  $this->functions->printImg( $bgImageUrl, $bgImageName ),
        						  	   $bgImagesAr, $flipSound, $bgImageList,
        						         $book['action'], $book['button'], $galleryId );
	}

	
	function pageFlipWidget($args)
	{
	    extract($args);

	    echo $before_widget;
	    echo $before_title;
	    echo 'Title';
	    echo $after_title;
	    echo '<center>My first widget1</center>';
	    echo $after_widget;
	}

    
	function mce_external_plugins( $plugin_array )
	{
		$plugin_array['pageFlip'] = $this->jsUrl . 'editor_plugin.js';
	    return $plugin_array;
	}

	
	function mce_buttons( $buttons )
	{
	    array_push( $buttons, "pageFlip" );
	    return $buttons;
	}

	
	function init_textdomain()
	{
    	if ( function_exists( 'load_plugin_textdomain' ) )
        	load_plugin_textdomain( 'pageFlip', PLUGINDIR . '/' . $this->plugin_dir . '/' . $this->langDir . '/' );
	}

	
	function registerWidget()
	{
		register_sidebar_widget('pageflip widget', array( $this, 'pageFlipWidget' ) );
	}

	function my_first_widget($args)
	{
	    extract($args);

	    echo $before_widget;
	    echo $before_title;
	    echo 'Title1';
	    echo $after_title;
	    echo '<center>My first widget1</center>';
	    echo $after_widget;
	}

	function sortBook()
	{
		$book = new Book($_POST['bookId']);
		$sortBy = $_POST['sortBy'];
		$sortOrder = $_POST['sortOrder'] == 'desc' ? SORT_DESC : SORT_ASC;

		foreach ($book->pages as $id => $page)
		{
			$sort[$id] = $page->$sortBy;
		}
		array_multisort($sort, $sortOrder, $book->pages);

        if( !$book->save() )
        {
        	echo __('Save file error! Please setup permission to the books/ , images/ , sounds/ folders and include files to &quot;777&quot;', 'pageFlip');
        	return false;
        }
	}

	
}

?>