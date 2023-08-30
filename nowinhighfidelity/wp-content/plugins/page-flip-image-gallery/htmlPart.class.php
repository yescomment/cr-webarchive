<?php 
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

class HTMLPart
{
   var $count = 0, $main;

   function HTMLPart( )
   {

   }

   
   function viewBook( $book, $bookWidth, $bookHeight, $bookBackground )
   {
		if( $this->count > 0 )
			return '<p style="text-align: center;"><strong>' . __('You can add only one book on page', 'pageFlip') . '</strong></p>';

		if ($this->main->isPopup)
			$bookWidth = '100%';

		
		$tNoPlayer = __('Download Adobe Flash Player.', 'pageFlip');

		$pages = ''; $pagesZoom = ''; $pagesLink = '';
		foreach( $book->pages as $id => $page )
		{
			if( $pages !== '' )
			{
				$pages .= ", \n";
				$pagesZoom .= ", \n";
				$pagesLink .= ", \n";
			}

			if( $page->number !== $book->countPages - 1 ) $split = '|';
			else $split = '';

			$pages .= '"' . $page->image . $split . '"';
			$pagesZoom .= '"' . $page->zoomURL . $split . '"';
			$pagesLink .= '"' . $split . '"';
		}

		$flipSound = $book->flipSound;
		
		

		$navigation = ( $book->navigation == 'true' ) ? $this->main->navigation : '';

		$jsFiles = "
		  <script type=\"text/javascript\" src=\"{$this->main->jqueryJS}\"></script>
	      <script type=\"text/javascript\" src=\"{$this->main->swfObjectJS}\"></script>
		  <script type=\"text/javascript\" src=\"{$this->main->componentJS}\"></script>
";

		$text = "
		<!-- WP PageFlip component -->
	    <div id=\"book{$book->id}\">
			{$jsFiles}
		    <div id=\"fbContainer\">
				<a id=\"altmsg\" class=\"altlink\" href=\"http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash\" target=\"_blank\">{$tNoPlayer}</a>
			</div>
			<script language=\"JavaScript\" type=\"text/javascript\">
			//<![CDATA[
			flippingBook{$book->id} = new FlippingBook();
			flippingBook{$book->id}.pages = [
				{$pages}
			];

			flippingBook{$book->id}.enlargedImages = [
				{$pagesZoom}
			];

			flippingBook{$book->id}.pageLinks = [
				{$pagesLink}
			];

			flippingBook{$book->id}.stageWidth = \"{$bookWidth}\";
			flippingBook{$book->id}.stageHeight = \"{$bookHeight}\";
			flippingBook{$book->id}.settings.bookWidth = \"{$book->width}\";
			flippingBook{$book->id}.settings.bookHeight = \"{$book->height}\";
			flippingBook{$book->id}.settings.firstPageNumber = \"{$book->firstPage}\";
			flippingBook{$book->id}.settings.navigationBar = \"{$navigation}\";
			flippingBook{$book->id}.settings.navigationBarPlacement = \"{$book->navigationBarPlacement}\";
			flippingBook{$book->id}.settings.pageBackgroundColor = {$book->pageBack};
			flippingBook{$book->id}.settings.backgroundColor = {$book->backgroundColor};
			flippingBook{$book->id}.settings.backgroundImage = \"{$bookBackground}\";
			flippingBook{$book->id}.settings.backgroundImagePlacement = \"{$book->backgroundImagePlacement}\";
			flippingBook{$book->id}.settings.staticShadowsType = \"{$book->staticShadowsType}\";
			flippingBook{$book->id}.settings.staticShadowsDepth = \"{$book->staticShadowsDepth}\";
			flippingBook{$book->id}.settings.autoFlipSize = \"{$book->autoFlip}\";
			flippingBook{$book->id}.settings.centerBook = {$book->centerBook};
			flippingBook{$book->id}.settings.scaleContent = {$book->scaleContent};
			flippingBook{$book->id}.settings.alwaysOpened = {$book->alwaysOpened};
			flippingBook{$book->id}.settings.flipCornerStyle = \"{$book->flipCornerStyle}\";
			flippingBook{$book->id}.settings.hardcover = {$book->hardcover};
			flippingBook{$book->id}.settings.downloadURL = \"{$book->downloadURL}\";
			flippingBook{$book->id}.settings.downloadTitle = \"{$book->downloadTitle}\";
			flippingBook{$book->id}.settings.downloadSize = \"{$book->downloadSize}\";
			flippingBook{$book->id}.settings.allowPagesUnload = {$book->allowPagesUnload};
			flippingBook{$book->id}.settings.fullscreenEnabled = {$book->fullscreenEnabled};
			flippingBook{$book->id}.settings.zoomEnabled = {$book->zoomEnabled};
			flippingBook{$book->id}.settings.zoomImageWidth = \"{$book->zoomImageWidth}\";
			flippingBook{$book->id}.settings.zoomImageHeight = \"{$book->zoomImageHeight}\";
			flippingBook{$book->id}.settings.zoomUIColor = {$book->zoomUIColor};
			flippingBook{$book->id}.settings.slideshowButton = {$book->slideshowButton};
			flippingBook{$book->id}.settings.slideshowAutoPlay = {$book->slideshowAutoPlay};
			flippingBook{$book->id}.settings.slideshowDisplayDuration = \"{$book->slideshowDisplayDuration}\";
			flippingBook{$book->id}.settings.goToPageField = {$book->goToPageField};
			flippingBook{$book->id}.settings.firstLastButtons = {$book->firstLastButtons};
			flippingBook{$book->id}.settings.printEnabled = {$book->printEnabled};
			flippingBook{$book->id}.settings.zoomOnClick = {$book->zoomOnClick};
			flippingBook{$book->id}.settings.moveSpeed = \"{$book->moveSpeed}\";
			flippingBook{$book->id}.settings.closeSpeed = \"{$book->closeSpeed}\";
			flippingBook{$book->id}.settings.gotoSpeed = \"{$book->gotoSpeed}\";
			flippingBook{$book->id}.settings.rigidPageSpeed = \"{$book->rigidPageSpeed}\";
			flippingBook{$book->id}.settings.zoomHint = \"{$book->zoomHint}\";
			flippingBook{$book->id}.settings.printTitle = \"{$book->printTitle}\";
			flippingBook{$book->id}.settings.downloadComplete = \"{$book->downloadComplete}\";
			flippingBook{$book->id}.settings.dropShadowEnabled = {$book->dropShadowEnabled};
			flippingBook{$book->id}.settings.flipSound = \"{$flipSound}\";
			flippingBook{$book->id}.settings.hardcoverSound = \"{$book->hardcoverSound}\";
			flippingBook{$book->id}.settings.preloaderType = \"{$book->preloaderType}\";
			flippingBook{$book->id}.settings.preserveProportions = {$book->preserveProportions};
			flippingBook{$book->id}.settings.centerContent = {$book->centerContent};
			flippingBook{$book->id}.settings.hardcoverThickness = \"{$book->hardcoverThickness}\";
			flippingBook{$book->id}.settings.hardcoverEdgeColor = {$book->hardcoverEdgeColor};
			flippingBook{$book->id}.settings.highlightHardcover = {$book->highlightHardcover};
			flippingBook{$book->id}.settings.frameWidth = \"{$book->frameWidth}\";
			flippingBook{$book->id}.settings.frameColor = {$book->frameColor};
			flippingBook{$book->id}.settings.frameAlpha = \"{$book->frameAlpha}\";
			flippingBook{$book->id}.settings.navigationFlipOffset = \"{$book->navigationFlipOffset}\";
			flippingBook{$book->id}.settings.flipOnClick = {$book->flipOnClick};
			flippingBook{$book->id}.settings.handOverCorner = {$book->handOverCorner};
			flippingBook{$book->id}.settings.handOverPage = {$book->handOverPage};
			flippingBook{$book->id}.settings.staticShadowsLightColor = {$book->staticShadowsLightColor};
			flippingBook{$book->id}.settings.staticShadowsDarkColor = {$book->staticShadowsDarkColor};
			flippingBook{$book->id}.settings.dynamicShadowsDepth = \"{$book->dynamicShadowsDepth}\";
			flippingBook{$book->id}.settings.dynamicShadowsLightColor = {$book->dynamicShadowsLightColor};
			flippingBook{$book->id}.settings.dynamicShadowsDarkColor = {$book->dynamicShadowsDarkColor};
			flippingBook{$book->id}.settings.loadOnDemand = {$book->loadOnDemand};
			flippingBook{$book->id}.settings.showUnderlyingPages = {$book->showUnderlyingPages};
			flippingBook{$book->id}.settings.playOnDemand = {$book->playOnDemand};
			flippingBook{$book->id}.settings.freezeOnFlip = {$book->freezeOnFlip};
			flippingBook{$book->id}.settings.darkPages = {$book->darkPages};
			flippingBook{$book->id}.settings.smoothPages = {$book->smoothPages};
			flippingBook{$book->id}.settings.rigidPages = {$book->rigidPages};
			flippingBook{$book->id}.settings.flipCornerPosition = \"{$book->flipCornerPosition}\";
			flippingBook{$book->id}.settings.flipCornerAmount = \"{$book->flipCornerAmount}\";
			flippingBook{$book->id}.settings.flipCornerAngle = \"{$book->flipCornerAngle}\";
			flippingBook{$book->id}.settings.flipCornerRelease = {$book->flipCornerRelease};
			flippingBook{$book->id}.settings.flipCornerVibrate = {$book->flipCornerVibrate};
			flippingBook{$book->id}.settings.flipCornerPlaySound = {$book->flipCornerPlaySound};
			flippingBook{$book->id}.settings.useCustomCursors = {$book->useCustomCursors};
			flippingBook{$book->id}.settings.dropShadowHideWhenFlipping = {$book->dropShadowHideWhenFlipping};
			flippingBook{$book->id}.settings.loader = true;
			flippingBook{$book->id}.create(\"{$this->main->component}\");
			jQuery.noConflict();
			//]]>
			</script>
		<!-- ----trial version---- -->
		  <div style=\"text-align: center; font-size:9px; width:{$bookWidth};\">
		  	<a href=\"http://pageflipgallery.com\" title=\"FlippingBook Wordpress Gallery\">FlippingBook Wordpress Gallery</a>
		  </div>
        <!-- ----trial version---- -->
		</div>
	    <!-- WP PageFlip component end -->
";

	  $this->count++; 
	  return $text;
   }

	function popupLink($book, $bookWidth, $bookHeight, $bookBackground)
	{
		global $wpdb;

		$bookName = $wpdb->get_var("SELECT `name` FROM `{$this->main->table_name}` WHERE `id`='{$book->id}'");

		$popup_php = WP_PLUGIN_URL.'/'.$this->main->plugin_dir.'/popup.php';

		if ( preg_match('/.*%$/', $bookWidth) )
		{
			$bookWidth = $book->width;
		}

		if ($book->preview)
			$text = '<img src="'.$book->preview.'" alt="'.$bookName.'" title="" />';
		else
			$text = $bookName;

		$link = "
			<p><a href=\"{$popup_php}?book_id={$book->id}&amp;title={$bookName}\" onclick=\"window.open('{$popup_php}?book_id={$book->id}&amp;title={$bookName}', '', 'location=no,menubar=no,resizable=no,scrollbars=no,status=no,toolbar=no,left='+(screen.availWidth-{$bookWidth})/2+',top='+(screen.availHeight-{$bookHeight})/2+',width={$bookWidth},height={$bookHeight}'); return false;\">{$text}</a></p>
";
		return $link;
	}

   
   function ajaxPreviewBook()
   {
    	$siteUrl = get_option( 'siteurl' );
    	$rootUrl = WP_PLUGIN_URL . '/' . $this->main->plugin_dir . '/';
		

    	$tPlusAlt = __('Press to open pages list', 'pageFlip');
		$tPlusLabel = __('Press &quot;+&quot; to open pages list', 'pageFlip');
		$tMinusAlt = __('Press to close pages list', 'pageFlip');
		$tMinusLabel = __('Press &quot;-&quot; to close pages list', 'pageFlip');

    	$text = "
        		<script src=\"{$this->main->jsUrl}jquery-1.2.3.min.js\" type=\"text/javascript\"> </script>
        		<script type=\"text/javascript\" src=\"{$this->main->jsUrl}jquery.tablednd_0_5.js\"></script>
        		<script type=\"text/javascript\" src=\"{$this->main->jsUrl}swfobject1.5.js\"></script>
        		<script type=\"text/javascript\">
				//<![CDATA[
				    is = new Array();
				    function pageList(bookId)
					{
						switch(is[bookId])
						{
							case 1 :
							{
								document.getElementById('pages' + bookId).innerHTML = '';
								document.getElementById('plus' + bookId).innerHTML = '<a href=\"#\" onclick=\"pageList(' + bookId + '); return false;\"><img src=\"{$this->main->imgUrl}plus.gif\" width=\"16\" height=\"16\" alt=\"+\" title=\"{$tPlusAlt}\" border=\"0\" \/><\/a>';
								document.getElementById('tip' + bookId).innerHTML = '{$tPlusLabel}';
								is[bookId] = 0;
							}; break;
							default :
							{
								$.post(
								'{$siteUrl}/wp-admin/admin-ajax.php',
								{action:'pages_list', 'cookie': encodeURIComponent(document.cookie), bookId: bookId},
								function(str)
								 {
									document.getElementById('pages' + bookId).innerHTML = str;
									initDnD(bookId);
							 	 }
								);
								document.getElementById('plus' + bookId).innerHTML = '<a href=\"#\" onclick=\"pageList(' + bookId + '); return false;\"><img src=\"{$this->main->imgUrl}minus.gif\" width=\"16\" height=\"16\" alt=\"-\" title=\"{$tMinusAlt}\" border=\"0\" \/><\/a>';
								document.getElementById('tip' + bookId).innerHTML = '{$tMinusLabel}';
								is[bookId] = 1;
							}
						}
					}

					function initDnD( bookId ) {
					    // Initialise the table
					    $(\"#pagesList_\" + bookId).tableDnD({
						    onDragClass: \"myDragClass\",
						    onDrop: function(table, row) {
							  disableButtons(table);
							  var rows = table.tBodies[0].rows;
					          var w = \"\";
					          for (var i = 0; i < rows.length; i++) {
					            w += rows[i].id + \";\";
					          }

					          $.post(
									'{$siteUrl}/wp-admin/admin-ajax.php',
									{action:'drop_pages_list', 'cookie': encodeURIComponent(document.cookie), pages: w, bookId: bookId},
									function(str)
									 {
										is[bookId] = 0;
										pageList(bookId);

										refreshBookPreview(bookId);
								 	 }
									);
					        }
					  	});
					}

					function disableButtons( obj )
					{
						var buttons = obj.getElementsByTagName(\"input\");
						for(el in buttons){
	    					buttons[el].disabled=\"disabled\";
	   					}
					}

					function pageMove( bookId, pageId, op )
					{
						$.post(
							'{$siteUrl}/wp-admin/admin-ajax.php',
							{action:'drop_pages_list', 'cookie': encodeURIComponent(document.cookie), bookId: bookId, pageId: pageId, op: op},
							function(str)
							 {
								is[bookId] = 0;
								pageList(bookId);
								refreshBookPreview(bookId);
						 	 }
							);
					}

					function deletePage( bookId, pageId )
					{
						if( confirm('Delete this page?') )
						{
							$.post(
								'{$siteUrl}/wp-admin/admin-ajax.php',
								{action:'delete_page', 'cookie': encodeURIComponent(document.cookie), bookId: bookId, pageId: pageId},
								function(str)
								 {
									is[bookId] = 0;
									pageList(bookId);
									refreshBookPreview(bookId);
							 	 }
								);
						}
					}

					function splitImage( bookId, pageId )
					{
						$.post(
								'{$siteUrl}/wp-admin/admin-ajax.php',
								{action:'split_image', 'cookie': encodeURIComponent(document.cookie), bookId: bookId, pageId: pageId},
								function(str)
								 {
									is[bookId] = 0;
									pageList(bookId);
									refreshBookPreview(bookId);
							 	 }
								);
					}

					function mergeImage( bookId, pageId )
					{
						$.post(
								'{$siteUrl}/wp-admin/admin-ajax.php',
								{action:'merge_image', 'cookie': encodeURIComponent(document.cookie), bookId: bookId, pageId: pageId},
								function(str)
								 {
									is[bookId] = 0;
									pageList(bookId);
									refreshBookPreview(bookId);
							 	 }
								);
					}

					function refreshBookPreview(bookId)
					{
						$.post(
							'{$siteUrl}/wp-admin/admin-ajax.php',
							{action:'refresh_book_preview', 'cookie': encodeURIComponent(document.cookie), bookId: bookId},
							function(str)
							 {
								images = str.split(\"<split>\");
								document.getElementById('first_image_' + bookId).innerHTML = images[0];
								document.getElementById('second_image_' + bookId).innerHTML = images[1];
						 	 }
							);
					}

					function viewEditor( bookId )
					{
						var so = new SWFObject('{$this->main->editor}','player',\"100%\",\"500\",'9');

						so.addVariable(\"album_id\", bookId);
						so.addVariable(\"lang\", 'en_US');
						so.addVariable(\"root_path\", '{$rootUrl}');
						so.write('bookList');
					}

					function onFinishEdit()
					{
						location.reload();
					}

					function sortBook( bookId, sortBy, sortOrder )
					{
						$.post(
							'{$siteUrl}/wp-admin/admin-ajax.php',
							{
								action:'sort_book', 'cookie': encodeURIComponent(document.cookie),
								bookId: bookId,
								sortBy: sortBy,
								sortOrder: sortOrder
   							},
							function(str)
							{
								is[bookId] = 0;
								pageList(bookId);
								refreshBookPreview(bookId);
							}
						);
					}

				//]]>
				</script>
";

		return $text;
   }

   
   function ajaxPreviewImage( $bookId )
   {
    	$siteUrl = get_option( 'siteurl' );

    	$tDeleteImage = __('Delete this Image?', 'pageFlip');
    	$tDeleteImages = __('Delete this Images?', 'pageFlip');
    	$tGalleryName = __('Gallery Name', 'pageFlip');
    	$tAddGallery = __('Add Gallery', 'pageFlip');
    	$tAlert = __('You must enter gallery name', 'pageFlip');
		$tDeleteGallery = __('Delete this Gallery with all images?', 'pageFlip');

    	$text = "
        		<script src=\"{$this->main->jsUrl}jquery-1.2.3.min.js\" type=\"text/javascript\"> </script>
        		<script type=\"text/javascript\">
				//<![CDATA[
					var curPage = 0;
					var type = 'pageFlip';
					var gallery = 0;

					function checkAll( form, name, checked )
					{
						for (var i=0; i < form[name].length; i++) form[name][i].checked = checked;
						form[name].checked = checked
					}

					function viewAddGalleryForm()
					{
						document.getElementById('addNewGallery').innerHTML = '<label for=\"galleryName\">{$tGalleryName}<\/label> ' +
																			 '<input name=\"galleryName\" id=\"galleryName\" size=\"40\" type=\"text\" \/> ' +
																			 '<input class=\"button\" name=\"button\" value=\"{$tAddGallery}\" type=\"button\" onclick=\"addGallery( this.form );\" \/>';
					}

					function addGallery( form )
					{
						if( form['galleryName'].value === '' )
						{
							alert('{$tAlert}');
							return false;
						}

						$.post(
							'{$siteUrl}/wp-admin/admin-ajax.php',
							{action:'add_gallery', 'cookie': encodeURIComponent(document.cookie), name: form['galleryName'].value },
							function(str)
							 {
								//goToPage( 0, {$bookId} );
								viewGalleries();
						 	 }
							);
						return true;
					}

					function viewGalleries()
					{
						$.post(
							'{$siteUrl}/wp-admin/admin-ajax.php',
							{action:'view_galleries', 'cookie': encodeURIComponent(document.cookie), bookId: {$bookId}, type: type },
							function(str)
							 {
								document.getElementById('addPage').innerHTML = str;
						 	 }
							);
						return true;
					}

					function viewGallery( galleryId )
					{
						gallery = galleryId;
						goToPage( 0, {$bookId} );
						return true;
					}

					function moveTo( selObj, imageId )
					{
						var galleryId = selObj.options[selObj.selectedIndex].value;

						$.post(
							'{$siteUrl}/wp-admin/admin-ajax.php',
							{action:'move_image_to', 'cookie': encodeURIComponent(document.cookie), gallery: galleryId, imageId: imageId},
							function(str)
							 {
								goToPage( 0, {$bookId} );
						 	 }
							);
					}

					function moveImagesTo( selObj, form, name )
					{
						var galleryId = selObj.options[selObj.selectedIndex].value;
						var imgList = '';

						for (var i=0; i < form[name].length; i++)
							if( form[name][i].checked == true )
							{
								if( imgList != '' ) imgList += ';';
								imgList += form[name][i].value;
							}

						if( imgList != '' )
							$.post(
								'{$siteUrl}/wp-admin/admin-ajax.php',
								{action:'move_images_to', 'cookie': encodeURIComponent(document.cookie), gallery: galleryId, imageList: imgList},
								function(str)
								 {
									goToPage( 0, {$bookId} );
							 	 }
								);
					}

					function goToPage( page, bookId )
					{
						$.post(
							'{$siteUrl}/wp-admin/admin-ajax.php',
							{action:'images_list', 'cookie': encodeURIComponent(document.cookie), page: page, bookId: bookId, type: type, gallery: gallery},
							function(str)
							 {
								var paging = str.split(\"<split>\");
								document.getElementById('addPage').innerHTML = paging[0];
								curPage = paging[1];
						 	 }
							);

						return true;
					}

					function assignToPage( bookId, imageId )
					{
						$.post(
							'{$siteUrl}/wp-admin/admin-ajax.php',
							{action:'add_page_form', 'cookie': encodeURIComponent(document.cookie), imageId: imageId, bookId: bookId, type: type},
							function(str)
							 {
								document.getElementById('addPage').innerHTML = str;
						 	 }
							);

						return true;
					}

					function goTo( newType )
					{
						type = newType;
						switch( type )
						{
							case 'NGGallery' :
							case 'pageFlip' : viewGalleries(); break;
							default : goToPage( 0, {$bookId} );
						}

						$.post(
							'{$siteUrl}/wp-admin/admin-ajax.php',
							{action:'add_page_menu', 'cookie': encodeURIComponent(document.cookie), bookId: {$bookId}, type: type},
							function(str)
							 {
								var blocks = str.split(\"<split>\");
								document.getElementById('addPageMenu').innerHTML = blocks[0];
								document.getElementById('buttons_top').innerHTML = blocks[1];
								document.getElementById('buttons_bottom').innerHTML = blocks[1];
						 	 }
							);
					}

					function itemsPerPage( selObj )
					{
						var count = selObj.options[selObj.selectedIndex].value;

						$.post(
							'{$siteUrl}/wp-admin/admin-ajax.php',
							{action:'img_per_page', 'cookie': encodeURIComponent(document.cookie), count: count},
							function(str)
							 {
								goToPage( 0, {$bookId} );
						 	 }
							);
					}

					function deleteImage( imageId )
					{
						if( confirm('{$tDeleteImage}') )
						{
							$.post(
								'{$siteUrl}/wp-admin/admin-ajax.php',
								{action:'delete_image', 'cookie': encodeURIComponent(document.cookie), imageId: imageId},
								function(str)
								 {
									goToPage( curPage, {$bookId} );
							 	 }
								);
						}
					}

					function deleteImages( form, name )
					{
						if( confirm('{$tDeleteImages}') )
						{
							var imgList = '';

							for (var i=0; i < form[name].length; i++)
								if( form[name][i].checked == true )
								{
									if( imgList != '' ) imgList += ';';
									imgList += form[name][i].value;
								}

							if( imgList != '' )
								$.post(
									'{$siteUrl}/wp-admin/admin-ajax.php',
									{action:'delete_images', 'cookie': encodeURIComponent(document.cookie), imageList: imgList},
									function(str)
									 {
										goToPage( curPage, {$bookId} );
										document.getElementById('totalCheck').checked = false;
								 	 }
									);
						}
					}

					function deleteGallery( id )
					{
						if( confirm('{$tDeleteGallery}') )
						{
							$.post(
							'{$siteUrl}/wp-admin/admin-ajax.php',
							{action:'delete_gallery', 'cookie': encodeURIComponent(document.cookie), gallery: id },
							function(str)
							 {
								viewGalleries();
						 	 }
							);
							//return true;
						}
						//else
						return false;
					}
				//]]>
				</script>
";

		return $text;
   }

   
   function headerPreviewBook()
   {
    	$tID = __('ID', 'pageFlip');
		$tPreview = __('Preview', 'pageFlip');
		$tBookName = __('Book Name', 'pageFlip');
		$tCreationDate = __('Creation Date', 'pageFlip');
		$tOperation = __('Operation', 'pageFlip');

		$text = "
	    	<table class=\"widefat\">
					<thead>
					<tr>

					<th scope=\"col\" style=\"text-align: center;\">{$tID}</th>
					<th scope=\"col\" style=\"text-align: left;\">{$tPreview}</th>
					<th scope=\"col\" style=\"text-align: left;\">{$tBookName}</th>
					<th scope=\"col\" style=\"text-align: center;\">{$tCreationDate}</th>
					<th scope=\"col\" style=\"text-align: center;\">{$tOperation}</th>

					</tr>
					</thead>
					<tbody>
";
		return $text;
   }

   
   function operationBookPreview( $side = 'top' )
   {
   	    $tButtonName = __('Add New Book', 'pageFlip');

		$text = "
			     <br />
        		  <form name=\"operations_{$side}\" method=\"post\" action=\"\">
        		   <input name=\"do\" value=\"Add New Book\" type=\"hidden\" />
		     	   <input class=\"button\" name=\"button\" value=\"{$tButtonName}\" type=\"submit\" />
		     	  </form>
		     	  <br />
";

		return $text;
   }

    
   function noBooksPreviewBook()
   {
    	$tNoBooks = __('No books', 'pageFlip');

		$text = "
	    				<tr class=\"alternate author-self status-publish\" valign=\"top\">
	                 	    <td colspan=\"5\" style=\"text-align: center;\"><strong>{$tNoBooks}</strong></td>
					  	</tr>
";
		return $text;
   }

   
   function previewBook( $book, $bookName, $creationDate, $firstPageImg, $secondPageImg )
   {
		$tBookError = __('Bookfile is not found', 'pageFlip');
		$tDeleteBook = __('Delete this book?', 'pageFlip');
		$tDelete = __('Delete Book', 'pageFlip');
		$tPlusAlt = __('Press to open pages list', 'pageFlip');
		$tPagesList = __('Pages List', 'pageFlip');
		$tPlusLabel = __('Press &quot;+&quot; to open pages lists', 'pageFlip');
		$tNoPages = __('No Pages', 'pageFlip');

		$previewWidth = $this->main->thumbWidth + 4;
    	$previewHeight = $this->main->thumbHeight + 4;
		$text = "
    				<tr class=\"alternate author-self status-publish\" valign=\"top\">
		                <td width=\"5%\" style=\"text-align: center;\">
		                  	<strong>{$book->id}</strong>
		                </td>
		                <td width=\"25%\" style=\"text-align: left;\">
		                	<table cellpadding=\"0\" cellspacing=\"0\">
								<tbody>
								 <tr>
									<td id=\"first_image_{$book->id}\" style=\"border:1px; border-color:#000000; border-style:dashed; width:{$previewWidth}px; height:{$previewHeight}px; padding: 2px; margin: 0px; text-align:center;\" valign=\"middle\">
		 								{$firstPageImg}
		     	 		    		</td>
									<td id=\"second_image_{$book->id}\" style=\"border:1px; border-color:#000000; border-style:dashed; width:{$previewWidth}px; height:{$previewHeight}px; padding: 2px; margin: 0px; text-align:center;\" valign=\"middle\">
	     								{$secondPageImg}
		 			     			</td>
							     </tr>
							    </tbody>
							  </table>
				       </td>
					   <td width=\"20%\" style=\"text-align: left; font-weight: bold; text-transform: uppercase;\">
";
		 if( $book->state == 0 )
		   $text .= "
		   				<div style=\"font-weight: bold; text-transform: uppercase; text-align: left; float: right; width: 88%; padding-left: 2%;\">
						  	{$bookName}
					 	</div>
					 	<div>{$tBookError}</div>
";
	     else
	      	$text .= "
	      				{$bookName}
";

		 $text .= "
					  </td>
					  <td width=\"15%\" style=\"text-align: center;\">
	                  	{$creationDate}
					  </td>
					  <td width=\"35%\" style=\"text-align: center;\">
					    <form name=\"operations_{$book->id}\" method=\"post\" action=\"\">
					     <input name=\"id\" value=\"{$book->id}\" type=\"hidden\"/>
";

		if( $book->state != 0 )
		{
	      	$text .= "
	      				 <input class=\"button\" name=\"do\" value=\"Add Page\" type=\"submit\"/>
";
		  $text .= "
		  		    	 <input class=\"button\" name=\"do\" value=\"Book Properties\" type=\"submit\"/>
		  		    	 <input class=\"button\" name=\"button\" value=\"Flash Editor\" type=\"button\" onclick=\"viewEditor({$book->id})\" />
";
		}
		$text .= "
				     	  <input class=\"button\" name=\"actionButton\" value=\"{$tDelete}\" type=\"submit\" onclick=\"return confirm('{$tDeleteBook}')\"/>
						  <input name=\"action\" value=\"Delete Book\" type=\"hidden\"/>
					    </form>
					  </td>
				   </tr>
				   <tr>
				   	  <td colspan=\"5\">
";
		if( $book->countPages > 0 )
			$text .= "
				     	<div>
					  		<div id=\"plus{$book->id}\" style=\"float: left; width: 2%;\">
						  		<a href=\"#\" onclick=\"pageList({$book->id}); return false;\"><img src=\"{$this->main->imgUrl}plus.gif\" width=\"16\" height=\"16\" alt=\"+\" title=\"{$tPlusAlt}\" border=\"0\" /></a>
						  	</div>
						  	<div style=\"text-align: left; float: right; width: 97%; padding-left: 1%;\">
						  		<a href=\"#\" onclick=\"pageList({$book->id}); return false;\">{$tPagesList}</a>
						  	</div>
						</div><br />
					  	<div id=\"tip{$book->id}\" style=\"width: 100%; float: none; margin-bottom:5px;\">$tPlusLabel</div>
					  	<div id=\"pages{$book->id}\"></div>
";
  		else
  			$text .= "
				     	<div>
					  	 {$tNoPages}
						</div>
";
		$text .= "
			     	 </td>
				   </tr>
";

	  return $text;
   }

   
   function footerPreviewBook()
   {
   		$text = "
   			</tbody></table>
";
		return $text;
   }

   
   function headerPreviewGallery()
   {
   		$tPreview = __('Preview', 'pageFlip');
   		$tGalleryName = __('Gallery Name', 'pageFlip');
   		$tCreationDate = __('Creation Date', 'pageFlip');
   		$tOperation = __('Operations', 'pageFlip');

		$text = "
		   <table class=\"widefat\">
					<thead>
					<tr>
					<th scope=\"col\" style=\"text-align: center;\">{$tPreview}</th>
					<th scope=\"col\" style=\"text-align: left;\">{$tGalleryName}</th>
					<th scope=\"col\" style=\"text-align: center;\">{$tCreationDate}</th>
					<th scope=\"col\" style=\"text-align: center;\">{$tOperation}</th>
					</tr>
					</thead>
					<tbody id=\"galleries_list\">
";

		return $text;
   }

   
   function operationPreviewGallery( $bookId = 0 )
   {
   	    $tButtonName = __('Create New Gallery', 'pageFlip');
   	    $tUploadButton = __('Upload New Images', 'pageFlip');

   	    $text = '<form method="post" name="addGalleryForm" id="addGalleryForm" action="">';

		
		$text .= "
				  <input class=\"button\" name=\"button\" value=\"{$tButtonName}\" type=\"button\" onclick=\"viewAddGalleryForm();\" />
				  <br />
				  <div id=\"addNewGallery\">&nbsp;</div>
				  </form>
";

		return $text;
   }

   
   function previewGallery( $bookId, $id, $name, $count, $creationDate, $preview, $type = 'pageFlip' )
   {
	   

       $buttons = '<form name="operations_' . $id . '" method="post" action="">' .
				   	 '<input name="galleryId" value="' . $id . '" type="hidden"/>' .
					 '<input name="id" value="' . $bookId . '" type="hidden"/>' .
					 '<input name="type" value="'.$type.'" type="hidden"/>';
	   if( (int)$bookId === 0 )
	   {
	     $buttons .= '<input class="button" name="buttonUpload" value="' . __('Upload Images', 'pageFlip') . '" type="submit"/>' .
	 				 '<input name="do" value="Upload New Images" type="hidden" />';

	     $buttons .= '<input type="submit" class="button" name="do" value="'. __('Create Book', 'pageFlip') .'" />';

	     if( $id > 0 ) $buttons .= '<input class="button" name="do" value="' . __('Delete Gallery', 'pageFlip') . '" type="submit" onclick="javascript:return deleteGallery(' . $id . ')" />';
	   }
       else
	   {
       	 if( $type === 'pageFlip' )
			 $buttons .= '<input class="button" name="buttonUpload" value="' . __('Upload New Images', 'pageFlip') . '" type="submit"/>' .
       	 			 	 '<input name="thisdo" value="Upload New Images" type="hidden" />';

       	 $buttons .= '<input name="action" value="Assign Images from Gallery" type="hidden" />' .
				 	 '<input class="button" name="actionButton" value="' . __('Assign to Book', 'pageFlip') . '" type="submit" />';
	   }

       $buttons .= '</form>';

	   $text = "
       					<tr class=\"alternate author-self status-publish\" valign=\"top\">
		                    <td width=\"20%\" style=\"text-align: center;\">{$preview}</td>
						    <td width=\"25%\" style=\"text-align: left;\">
								<a href=\"#\" onclick=\"return viewGallery({$id});\"><strong>{$name}</strong></a> ({$count})
							</td>
						    <td width=\"20%\" style=\"text-align: center;\">{$creationDate}</td>
						    <td width=\"35%\" style=\"text-align: center;\">
						    	{$buttons}
						    </td>
				          </tr>
";

	   return $text;
   }

   
   function footerPreviewGallery()
   {
   		$text = "
   				 </tbody>
				</table>
";

		return $text;
   }

   
   function headerPreviewImage()
   {
   		$tImage = __('Image', 'pageFlip');
   		$tName = __('Name', 'pageFlip');
   		$tUploadDate = __('Upload Date', 'pageFlip');
   		$tOperation = __('Operation', 'pageFlip');

		$text = "
		   <table class=\"widefat\">
					<thead>
					<tr>

                    <th scope=\"col\" style=\"text-align: center;\">
					<input type=\"checkbox\" name=\"total\" id=\"totalCheck\" value=\"checkbox\" onclick=\"checkAll(this.form,'images[]',this.checked)\"/>
					</th>
					<th scope=\"col\" style=\"text-align: center;\">{$tImage}</th>
					<th scope=\"col\" style=\"text-align: left;\">{$tName}</th>
					<th scope=\"col\" style=\"text-align: center;\">{$tUploadDate}</th>
					<th scope=\"col\" style=\"text-align: left;\">{$tOperation}</th>

					</tr>
					</thead>
					<tbody id=\"images_list\">
";

		return $text;
   }

   
   function operationPreviewImage( $bookId, $where, $navigation, $type = 'pageFlip', $gallery = 0 )
   {
		

		if( $where == 'top' ) $formStart = "
				<form name=\"operations\" action=\"\" method=\"post\">
					<input type=\"hidden\" value=\"{$gallery}\" name=\"galleryId\" />
";
		else $formStart = '';

		if( $where == 'bottom' ) $formClose = "
				</form>
";
		else $formClose = '';

		$text = "
				{$formStart}
					<div style=\"float:left; margin: 5px 0px 5px 0px;\" id=\"buttons_{$where}\">
";
		$text .= $this->buttonsOpImages( $bookId, $type, $gallery );

		$text .= "
					</div>
					<div style=\"float:right; margin: 5px 0px;\" id=\"navigation_{$where}\">
						{$navigation['bar']}
					</div>
				{$formClose}
";

		return $text;
   }

   
   function buttonsOpImages( $bookId, $type, $galleryId = 0 )
   {
		$tDeleteSelected = __('Delete Selected', 'pageFlip');
		$tAssignImages = __('Assign Selected Images to Page', 'pageFlip');
   	    $tUploadButton = __('Upload New Images', 'pageFlip');
   	    $tMove = __('Move selected to', 'pageFlip');

		$text = '<input name="type" value="' . $type . '" type="hidden" />';

		if( $type === 'pageFlip' )
		{
			if( (int)$bookId !== 0 ) $text .= "
									<input name=\"thisdo\" value=\"Upload New Images\" type=\"hidden\" />
";

			else $text .= "
									<input name=\"do\" value=\"Upload New Images\" type=\"hidden\" />
";

			$text .= "
									<input class=\"button\" name=\"button\" value=\"{$tUploadButton}\" type=\"submit\" />
";
		}

		if( (int)$bookId !== 0 ) $text .= "
								<input name=\"id\" value=\"{$bookId}\" type=\"hidden\" />
";

		if( (int)$bookId == 0 )
		{
			$galleryList = $this->main->functions->galleryJumpList( $galleryId );
			$text .= "
						<input class=\"button\" value=\"{$tDeleteSelected}\" type=\"button\" onclick=\"deleteImages(this.form,'images[]');\" />
						<span style=\"height: 19px; padding-top: 5px;\">
								  {$tMove}
								  <select size=\"1\" name=\"gallery\" style=\"font-size: 9px; height: 19px; margin-top: 2px;\" onchange=\"moveImagesTo(this, this.form,'images[]');\">
									$galleryList
								  </select>
				  		</span>
";
		}
		else $text .= "
						<input name=\"action\" value=\"Assign Selected Images to Page\" type=\"hidden\" />
						<input class=\"button\" name=\"actionButton\" value=\"{$tAssignImages}\" type=\"submit\" />
";

		return $text;
   }


   
   function previewImage( $bookId, $id, $name, $uploadDate, $image, $galleryId = 0, $type = 'pageFlip' )
   {
	   $tDelete = __('Delete', 'pageFlip');
       $tAssignImage = __('Assign Image to Page', 'pageFlip');
       $tMove = __('Move to', 'pageFlip');

	   if( (int)$bookId === 0 )
	   {
	   		$galleryList = $this->main->functions->galleryJumpList( $galleryId );

			$buttons = "
		   						  <input class=\"button\" name=\"action\" value=\"{$tDelete}\" type=\"button\" onclick=\"deleteImage({$id});\" /><br />
		   						  <div style=\"height: 19px; padding-top: 5px;\">
								  {$tMove}
								  <select size=\"1\" name=\"gallery\" style=\"font-size: 9px; height: 19px; margin-top: -3px;\" onchange=\"moveTo(this, {$id});\">
									$galleryList
								  </select>
								  </div>
";
	   }
	   else
		$buttons = "
								  <input class=\"button\" name=\"button\" value=\"{$tAssignImage}\" type=\"button\" onclick=\"assignToPage({$bookId}, {$id})\" />
";

       $text = "
       					<tr class=\"alternate author-self status-publish\" valign=\"top\">
		                    <td width=\"5%\" style=\"text-align: center;\">
		                    	<input name=\"images[]\" type=\"checkbox\" value=\"{$id}\" />
		                    </td>
		                    <td width=\"20%\" style=\"text-align: center;\">{$image}</td>
						    <td width=\"30%\" style=\"text-align: left;\">{$name}</td>
						    <td width=\"20%\" style=\"text-align: center;\">{$uploadDate}</td>
						    <td width=\"25%\" style=\"text-align: left;\">
                                  {$buttons}
						    </td>
				          </tr>
";

	   return $text;
   }

   
   function footerPreviewImage()
   {
   		$text = "
   				 </tbody>
				</table>
";

		return $text;
   }

   
   function headerPreviewPage( $bookId )
   {
		$tPage = __('Page', 'pageFlip');
		$tPageName = __('Page Name', 'pageFlip');
		$tOperation = __('Operation', 'pageFlip');
		$tSortPageNameA = __('Sort by Page Name (A-Z)');
		$tSortPageNameD = __('Sort by Page Name (Z-A)');

		$text = "
				<table class=\"widefat\" id=\"pagesList_{$bookId}\">
					<thead>
					<tr>

					<th scope=\"col\" style=\"text-align:center; cursor:default;\">#</th>
					<th scope=\"col\" style=\"text-align:left; cursor:default;\">{$tPage}</th>
					<th scope=\"col\" style=\"text-align:left; cursor:default;\">{$tPageName} <a href=\"#\" title=\"{$tSortPageNameA}\" onclick=\"sortBook({$bookId}, 'name', 'asc'); return false;\">&#9660;</a> <a href=\"#\" title=\"{$tSortPageNameD}\" onclick=\"sortBook({$bookId}, 'name', 'desc'); return false;\">&#9650;</a></th>
					<th scope=\"col\" style=\"text-align:center; cursor:default;\">{$tOperation}</th>

					</tr>
					</thead>
					<tbody>
";
		return $text;
   }

   
   function noPagesPreviewPage()
   {
   		$tNoPages = __('No pages', 'pageFlip');
		$text = "
   			<tr class=\"alternate author-self status-publish\" valign=\"top\">
              <td colspan=\"4\" style=\"text-align: center;\"><strong>{$tNoPages}</strong></td>
	  		</tr>
";
		return $text;
   }

   
   function previewPage( $bookId, $page, $side, $image, $countPages )
   {
        $tDelete = __('Delete', 'pageFlip');

		$thumbWidth = $this->main->thumbWidth + 4;
        $thumbHeight = $this->main->thumbHeight + 4;

		$leftImage = ($side == "left") ? $image : "";
        $rightImage = ($side == "right") ? $image : "";

        if( $page->number !== 0 ) $buttons = '<input class="button" name="action" value="' . __('Up', 'pageFlip'). '" type="button" onclick="return pageMove( ' . $bookId . ', ' . $page->number . ', \'up\' );">';
		if( $page->number !== $countPages - 1 ) $buttons .= '<input class="button" name="action" value="' . __('Down', 'pageFlip') . '" type="button" onclick="return pageMove( ' . $bookId . ', ' . $page->number . ', \'down\' );">';

		$picType = $this->main->functions->checkPic($page->image);
		if( $picType == 'pageFlip' && substr( $page->image, -3 ) != 'swf' )
				$buttons .= '<input class="button" name="split" value="' . __('Split Image', 'pageFlip') . '" type="button" onclick="return splitImage( ' . $bookId . ', ' . $page->number .' );" />';
		elseif( $this->main->functions->checkPic( $page->image ) == 'splitImage' )
				$buttons .= '<input class="button" name="merge" value="' . __('Merge Image', 'pageFlip') . '" type="button" onclick="return mergeImage( ' . $bookId . ', ' . $page->number .' );" />';

   		$text = "
   						 <tr id=\"{$page->number}\" class=\"alternate author-self status-publish\" style=\"border-top:0px;\" valign=\"top\">
		                    <td width=\"5%\" style=\"text-align: center;\"><strong>{$page->number}</strong></td>
						    <td width=\"30%\" style=\"text-align: left;\">
						      <table cellpadding=\"0\" cellspacing=\"0\" >
								<tbody>
								 <tr>
									<td style=\"border:1px; border-style:dashed; width:{$thumbWidth}px; height:{$thumbHeight}px; padding: 2px; margin: 0px; text-align:center; cursor:default;\" valign=\"middle\">
				                      {$leftImage}
	            					</td>
									<td style=\"border:1px; border-style:dashed; width:{$thumbWidth}px; height:{$thumbHeight}px; padding: 2px; margin: 0px; text-align:center; cursor:default;\" valign=\"middle\">
                                      {$rightImage}
						     		</td>
							     </tr>
							    </tbody>
							  </table>
						    </td>
						    <td width=\"30%\" style=\"text-align: left;\">{$page->name}</td>
						    <td width=\"25%\" style=\"text-align: center;\">
						         <form name=\"operations\" id=\"operations\" method=\"post\" action=\"\">
						          <input name=\"pageId\" value=\"{$page->number}\" type=\"hidden\">
						          <input name=\"id\" value=\"{$bookId}\" type=\"hidden\">
                                  {$buttons}
								  <input class=\"button\" name=\"action\" value=\"{$tDelete}\" type=\"button\" onclick=\"return deletePage( {$bookId}, {$page->number} );\" />
						     	 </form>
						    </td>
				          </tr>
";

		return $text;
   }

   
   function footerPreviewPage()
   {
   		$text = "
   			</tbody></table>
";
		return $text;
   }


   
   function addPageForm( $id, $imageId, $image, $imageName, $type = 'pageFlip' )
   {
   		$tImage = __('Image', 'pageFlip');
        $tPageName = __('Page Name', 'pageFlip');
        $tPageDesc = __('Enter page name (You can leave that page empty)', 'pageFlip');
        $tAddPage = __('Add Page', 'pageFlip');

		$text = "
   				<div id=\"ajax-response\"></div>
				<form name=\"addpage\" id=\"addpage\" method=\"post\" action=\"\" enctype=\"multipart/form-data\" class=\"add:the-list: validate\">
					<input name=\"action\" value=\"addpage\" type=\"hidden\">
					<input name=\"type\" value=\"{$type}\" type=\"hidden\">
					<input name=\"id\" value=\"{$id}\" type=\"hidden\">
					<input name=\"imageId\" value=\"{$imageId}\" type=\"hidden\">
					<table class=\"form-table\">
						<tbody>
						    <tr class=\"form-field\">
								<th scope=\"row\" valign=\"top\"><label for=\"page\">{$tImage}</label></th>
								<td>
									{$image}
					            	<p>{$imageName}</p>
					            </td>
							</tr>
							<tr class=\"form-field\">
								<th scope=\"row\" valign=\"top\"><label for=\"page\">{$tPageName}</label></th>
								<td><input name=\"name\" id=\"name\" value=\"{$imageName}\" size=\"40\" aria-required=\"true\" type=\"text\">
					            <p>{$tPageDesc}</p></td>
							</tr>
						</tbody>
					</table>
					<p class=\"submit\">
					   <input type=\"submit\" class=\"button\" name=\"actionButton\" value=\"{$tAddPage}\">
					</p>
				</form>
";
		return $text;
   }

   
    function uploadImageForm( $bookId='' )
	{
        $tImage = __('Name', 'pageFlip');
        $tImageName = __('Image name', 'pageFlip');
        $tImage = __('Image', 'pageFlip');
        $tImageDesc = __('Image must be jpg, png, gif or swf file', 'pageFlip');
        $tUpload = __('Upload', 'pageFlip');
        $tUploadImage = __('Upload Image', 'pageFlip');

		if( $bookId !== '' )
			$buttons = '<input name="id" value="'.$bookId.'" type="hidden"/>' .
					   '<input name="do" value="New Page" type="hidden"/>';
		else $buttons = '';

		$siteUrl = get_option( 'siteurl' );

		$menu = $this->uploadImageMenu();
		$form = $this->uploadImgForm();

        $text = "
        	<script src=\"{$this->main->jsUrl}jquery-1.2.3.min.js\" type=\"text/javascript\"> </script>
            <script type=\"text/javascript\">
			//<![CDATA[
				function addUploadForm()
				{
				    var tbody = document.getElementById('upload_table').getElementsByTagName('tbody')[0];

				    var row = document.createElement(\"tr\");
				    	row.class = 'form-field';
				    var row2 = document.createElement(\"tr\");
				    	row2.class = 'form-field';

				    tbody.appendChild(row);
				    tbody.appendChild(row2);

				    var th1 = document.createElement(\"th\");
				    	th1.scope = 'row';
				    var td1 = document.createElement(\"td\");
				    	td1.style.paddingBottom = '0.5em';

				    var label1 = document.createElement(\"label\");
				    var input1 = document.createElement(\"input\");
				    	input1.id = 'name';
				    	input1.name = 'name[]';
				    	input1.value = '';
				    	input1.size = '40';
				    	input1.type = 'text';
				    var p1 = document.createElement(\"div\");

					var th2 = document.createElement(\"th\");
						th2.scope = 'row';
				    var td2 = document.createElement(\"td\");
				    	td2.style.paddingBottom = '3em';

				    var label2 = document.createElement(\"label\");
				    var input2 = document.createElement(\"input\");
				    	input2.id = 'image';
				    	input2.name = 'image[]';
				    	input2.value = '';
				    	input2.size = '40';
				    	input2.type = 'file';
				    var p2 = document.createElement(\"div\");

				    th1.appendChild(label1);
				    td1.appendChild(input1);
				    td1.appendChild(p1);
				    th2.appendChild(label2);
				    td2.appendChild(input2);
				    td2.appendChild(p2);
				    row.appendChild(th1);
				    row.appendChild(td1);
				    row2.appendChild(th2);
				    row2.appendChild(td2);


				    label1.innerHTML = '{$tImage}';
				    p1.innerHTML = '{$tImageName}';
				    label2.innerHTML = '{$tImage}';
				    p2.innerHTML = '{$tImageDesc}';
				}

				function uploadForm( type )
				{
					$.post(
						'{$siteUrl}/wp-admin/admin-ajax.php',
						{action: 'upload_form' , 'cookie': encodeURIComponent(document.cookie), type: type, galleryId: {$_POST['galleryId']}},
						function(str)
						 {
							upload = str.split(\"<split>\");
							document.getElementById('pageflip-navigation').innerHTML = upload[0];
							document.getElementById('pageflip-ajax').innerHTML = upload[1];
					 	 }
						);
				}
			//]]>
			</script>

            <br />
			<div class=\"wrap\">
				<h2>{$tUploadImage}</h2>
				<div id=\"pageflip-navigation\">
					{$menu}
				</div>
				<br/>
				<form name=\"uploadimage\" id=\"uploadimage\" method=\"post\" action=\"\" enctype=\"multipart/form-data\" class=\"add:the-list: validate\">
					<input name=\"action\" value=\"uploadimage\" type=\"hidden\"/>
					<input name=\"galleryId\" value=\"{$_POST['galleryId']}\" type=\"hidden\"/>
					{$buttons}
					<div id=\"pageflip-ajax\">
						{$form}
					</div>
					<p class=\"submit\">
					   <input type=\"submit\" class=\"button-primary\" name=\"actionButton\" value=\"{$tUpload}\" />
					</p>
				</form>
			</div>
";

       return $text;
	}

	
	function uploadImageMenu()
	{
		if( empty( $_POST['type'] ) || $_POST['type'] === 'pageFlip' ) $_POST['type'] = 'image';

		$tUploadImg = __('Upload Image From Computer', 'pageFlip');
		$tSwfUpload = __('swfUpload', 'pageFlip');
		$tUploadArc = __('Upload Archive', 'pageFlip');
		$tAddUrl = __('Add From URL', 'pageFlip');
		$tFromFolder = __('Import from FTP folder', 'pageFlip');

		$text = ( $_POST['type'] !== 'image' ) ? '<a href="#" onclick="uploadForm( \'image\' ); return false;">' . $tUploadImg . '</a>' : '<strong>' . $tUploadImg . '</strong>' ;
		$text .= '&nbsp;|&nbsp;';
		
		$text .= ( $_POST['type'] !== 'zip' ) ? '<a href="#" onclick="uploadForm( \'zip\' ); return false;">' . $tUploadArc . '</a>' : '<strong>' . $tUploadArc . '</strong>' ;
		$text .= '&nbsp;|&nbsp;';
		$text .= ( $_POST['type'] !== 'fromUrl' ) ? '<a href="#" onclick="uploadForm( \'fromUrl\' ); return false;">' . $tAddUrl . '</a>' : '<strong>' . $tAddUrl . '</strong>' ;
		$text .= '&nbsp;|&nbsp;';
		$text .= ( $_POST['type'] !== 'fromFolder' ) ? '<a href="#" onclick="uploadForm( \'fromFolder\' ); return false;">'. $tFromFolder .'</a>' : '<strong>'. $tFromFolder .'</strong>' ;

		return $text;
	}

	
	function uploadImgForm()
	{
        $tName = __('Name', 'pageFlip');
        $tNameDesc = __('Image name', 'pageFlip');
        $tImage = __('Image', 'pageFlip');
        $tImageDesc = __('Image must be jpg, gif or swf file', 'pageFlip');
        $tUploadDesc = __('Upload one more image', 'pageFlip');

		$text = "
					<table class=\"form-table\" id=\"upload_table\">
						<tbody>
							<tr>
								<th scope=\"row\" valign=\"top\"><label for=\"name\">{$tName}</label></th>
								<td style=\"padding-bottom:0.5em;\"><input name=\"name[]\" id=\"name\" value=\"\" size=\"40\" type=\"text\"/>
					            <div>{$tNameDesc}</div></td>
							</tr>
							<tr>
								<th scope=\"row\" valign=\"top\"><label for=\"image\">{$tImage}</label></th>
								<td style=\"padding-bottom:3em;\"><input name=\"image[]\" id=\"image\" value=\"\" size=\"40\" type=\"file\"/>
					            <div>{$tImageDesc}</div></td>
							</tr>
						</tbody>
						<tfoot>
							<td></td>
							<td><button class=\"button\" onclick=\"addUploadForm(); return false;\">+ {$tUploadDesc}</button></td>
						</tfoot>
					</table>
";
        return $text;
	}

	
	function uploadSwfForm()
	{
		$text = "

		swfUpload

";
		return $text;
	}

	
    function uploadZipForm()
	{
          $tArchive = __('Archive', 'pageFlip');
          $tArchiveDesc = __('Archive must be zip file', 'pageFlip');

		  $text = "
					<table class=\"form-table\" id=\"upload_table\">
						<tbody>
							<tr>
								<th scope=\"row\" valign=\"top\"><label for=\"zip\">{$tArchive}</label></th>
								<td><input name=\"zip\" id=\"zip\" value=\"\" size=\"40\" type=\"file\"/>
					            <div>{$tArchiveDesc}</div></td>
							</tr>
						</tbody>
					</table>
";

       return $text;
	}

	
    function uploadFromUrlForm()
	{
        $tName = __('Name', 'pageFlip');
        $tImageDesc = __('Image name', 'pageFlip');
        $tUrl = __('URL', 'pageFlip');
        $tUrlDesc = __('Image URL', 'pageFlip');

		$text = "
					<table class=\"form-table\" id=\"upload_table\">
						<tbody>
							<tr>
								<th scope=\"row\" valign=\"top\"><label for=\"name\">{$tName}</label></th>
								<td><input name=\"name\" id=\"name\" value=\"\" size=\"40\" type=\"text\"/>
					            <div>{$tImageDesc}</div></td>
							</tr>
							<tr>
								<th scope=\"row\" valign=\"top\"><label for=\"url\">{$tUrl}</label></th>
								<td><input name=\"url\" id=\"url\" value=\"\" size=\"40\" type=\"text\"/>
					            <div>{$tUrlDesc}</div></td>
							</tr>
						</tbody>
					</table>
";

		return $text;
	}

	
    function uploadFromFolder()
	{
        $tName = __('Folder', 'pageFlip');
        $tImageDesc = __('Folder with images', 'pageFlip');

        $folder = substr( $this->main->uploadPath, strlen( ABSPATH ) );

		$text = "
					<table class=\"form-table\" id=\"upload_table\">
						<tbody>
							<tr>
								<th scope=\"row\" valign=\"top\"><label for=\"folder\">{$tName}</label></th>
								<td><input name=\"folder\" id=\"folder\" value=\"{$folder}\" size=\"40\" type=\"text\"/>
					            <div>{$tImageDesc}</div></td>
							</tr>
						</tbody>
					</table>
";

       return $text;
	}


   
   function bookForm( $title, $name, $book, $bgImage, $bgImagesAr, $flipSound, $bgImageList, $action, $button, $galleryId = '' )
   {
   	 $rp_0 = ($book->rigidPages == 'true') ? ' checked="checked"' : '';
   	 $rp_1 = ($book->rigidPages == 'false') ? ' checked="checked"' : '';
	 $sc_0 = ($book->scaleContent == 'true') ? ' checked="checked"' : '';
   	 $sc_1 = ($book->scaleContent == 'false') ? ' checked="checked"' : '';
	 $cc_0 = ($book->centerContent == 'true') ? ' checked="checked"' : '';
   	 $cc_1 = ($book->centerContent == 'false') ? ' checked="checked"' : '';
   	 $ao_0 = ($book->alwaysOpened == 'true') ? ' checked="checked"' : '';
   	 $ao_1 = ($book->alwaysOpened == 'false') ? ' checked="checked"' : '';
   	 $pp_0 = ($book->preserveProportions == 'true') ? ' checked="checked"' : '';
   	 $pp_1 = ($book->preserveProportions == 'false') ? ' checked="checked"' : '';
   	 $h_0 = ($book->hardcover == 'true') ? ' checked="checked"' : '';
   	 $h_1 = ($book->hardcover == 'false') ? ' checked="checked"' : '';
   	 $foc_0 = ($book->flipOnClick == 'true') ? ' checked="checked"' : '';
   	 $foc_1 = ($book->flipOnClick == 'false') ? ' checked="checked"' : '';
   	 $hoc_0 = ($book->handOverCorner == 'true') ? ' checked="checked"' : '';
   	 $hoc_1 = ($book->handOverCorner == 'false') ? ' checked="checked"' : '';
   	 $hop_0 = ($book->handOverPage == 'true') ? ' checked="checked"' : '';
   	 $hop_1 = ($book->handOverPage == 'false') ? ' checked="checked"' : '';
   	 $sst_0 = ($book->staticShadowsType == 'Asymmetric') ? ' selected="selected"' : '';
     $sst_1 = ($book->staticShadowsType == 'Symmetric') ? ' selected="selected"' : '';
     $sst_2 = ($book->staticShadowsType == 'Default') ? ' selected="selected"' : '';
	 $pt_0 = ($book->preloaderType == 'Progress Bar') ? ' selected="selected"' : '';
     $pt_1 = ($book->preloaderType == 'Round') ? ' selected="selected"' : '';
     $pt_2 = ($book->preloaderType == 'None') ? ' selected="selected"' : '';
     $ze_0 = ($book->zoomEnabled == 'true') ? ' checked="checked"' : '';
	 $ze_1 = ($book->zoomEnabled == 'false') ? ' checked="checked"' : '';
	 $zoc_0 = ($book->zoomOnClick == 'true') ? ' checked="checked"' : '';
	 $zoc_1 = ($book->zoomOnClick == 'false') ? ' checked="checked"' : '';
	 $cb_0 = ($book->centerBook == 'true') ? ' checked="checked"' : '';
	 $cb_1 = ($book->centerBook == 'false') ? ' checked="checked"' : '';
	 $ucc_0 = ($book->useCustomCursors == 'true') ? ' checked="checked"' : '';
	 $ucc_1 = ($book->useCustomCursors == 'false') ? ' checked="checked"' : '';
	 $dse_0 = ($book->dropShadowEnabled == 'true') ? ' checked="checked"' : '';
	 $dse_1 = ($book->dropShadowEnabled == 'false') ? ' checked="checked"' : '';
	 $dshwf_0 = ($book->dropShadowHideWhenFlipping == 'true') ? ' checked="checked"' : '';
	 $dshwf_1 = ($book->dropShadowHideWhenFlipping == 'false') ? ' checked="checked"' : '';
	 $pe_0 = ($book->printEnabled == 'true') ? ' checked="checked"' : '';
	 $pe_1 = ($book->printEnabled == 'false') ? ' checked="checked"' : '';
	 $bip_0 = ($book->backgroundImagePlacement == 'top left') ? ' selected="selected"' : '';
	 $bip_1 = ($book->backgroundImagePlacement == 'center') ? ' selected="selected"' : '';
	 $bip_2 = ($book->backgroundImagePlacement == 'fit') ? ' selected="selected"' : '';
	 $nb_0 = ($book->navigation == 'true') ? ' checked="checked"' : '';
	 $nb_1 = ($book->navigation == 'false') ? ' checked="checked"' : '';
	 $nbp_0 = ($book->navigationBarPlacement == 'top') ? ' checked="checked"' : '';
	 $nbp_1 = ($book->navigationBarPlacement == 'bottom') ? ' checked="checked"' : '';
	 $downl_0 = ($book->downloadURL != '') ? ' checked="checked"' : '';
	 $downl_1 = ($book->downloadURL == '') ? ' checked="checked"' : '';
	 $pop_0 = ($book->popup == 'true') ? ' checked="checked"' : '';
	 $pop_1 = ($book->popup == 'false') ? ' checked="checked"' : '';

     
     	$tBookName = __('Book name', 'pageFlip');
		$tBookNameDesc = __('The name is how the book appears on admin panel.', 'pageFlip');
		$tStageWidth = __('Stage Width', 'pageFlip');
		$tStageWidthDesc = __('Defines the width of the book background area in pixels or percents (%).', 'pageFlip');
		$tStageHeight = __('Stage Height', 'pageFlip');
		$tStageHeightDesc = __('Defines the height of the book background area in pixels.', 'pageFlip');
		$tWidth = __('Width', 'pageFlip');
		$tWidthDesc = __('Defines the book width in pixels.', 'pageFlip');
		$tHeight = __('Height', 'pageFlip');
		$tHeightDesc = __('Defines the book height in pixels.', 'pageFlip');
		$tScaleContent = __('Scale Content', 'pageFlip');
		$tScaleContentDesc = __('Defines the page content scaling method. If the parameter is set to Yes the loaded files will be automatically scaled to the page size. If the parameter is set to No the page content will be clipped to the page borders.', 'pageFlip');
		$tYes = __('yes', 'pageFlip');
		$tNo = __('no', 'pageFlip');
		$tCenterContent = __('Center Content', 'pageFlip');
		$tCenterContentDesc = __('Defines the page centering method. If the parameter is set to Yes the loaded files will be automatically centered within the page boundaries. If the parameter is set to No then the page content will be placed at the top left page corner.', 'pageFlip');
		$tPreserveProportions = __('Preserve Proportions', 'pageFlip');
		$tPreserveProportionsDesc = __('Defines the page content scaling rules. If the parameter is set to Yes the loaded files will be scaled this keeping original content proportions. If the parameter is set to No then the page content scaled without respect to the original proportions.', 'pageFlip');
		$tHardcover = __('Hardcover', 'pageFlip');
		$tHardcoverDesc = __('Turn the book hardcover on / off.', 'pageFlip');
		$tHardcoverThickness = __('Hardcover Thickness', 'pageFlip');
		$tHardcoverThicknessDesc = __('Defines the thickness of a rigid book page in pixels. Pages look more realistic for users.', 'pageFlip');
		$tFrameWidth = __('Frame Width', 'pageFlip');
		$tFrameWidthDesc = __('Defines the width of the book frame in pixels.', 'pageFlip');
		$tFrameColor = __('Frame Color', 'pageFlip');
		$tFrameColorDesc = __('Defines the color of the book frame. The color should be set in RGB in the following format: 0xNNNNNN, where N is hexadecimal number (0-F).', 'pageFlip');
		$tFrameAlpha = __('Frame Alpha', 'pageFlip');
		$tFrameAlphaDesc = __('Defines the transparency of the book frame.', 'pageFlip');
		$tFirstPage = __('First Page', 'pageFlip');
		$tFirstPageDesc = __('Number of the page from which the book will be opened upon the start of playing the movie. Page numbering starts from #0.', 'pageFlip');
		$tFlipOnClick = __('Flip on Click', 'pageFlip');
		$tFlipOnClickDesc = __('This parameter determines whether flipping will be started by mouse clicking a page. If set No, flipping will not start.', 'pageFlip');
		$tHandOverCorner = __('Hand Over Corner', 'pageFlip');
		$tHandOverCornerDesc = __('Defines the style of the mouse pointer over page corners. Setting this parameter to Yes turns the pointer to the finger when dragging mouse over page corner.', 'pageFlip');
		$tHandOverPage = __('Hand Over Page', 'pageFlip');
		$tHandOverPageDesc = __('Defines the style of the mouse pointer over page area (excepting corners). Setting this parameter to Yes turns the pointer to the finger when dragging mouse over page.', 'pageFlip');
		$tAlwaysOpened = __('Always Opened', 'pageFlip');
		$tAlwaysOpenedDesc = __('This parameter determines the appearance of the book. If its value is Yes, the book is always opened, if No, you may add the front and rear cover pages and make your book open and close.', 'pageFlip');
		$tStaticShadowType = __('Static Shadow Type', 'pageFlip');
		$tStaticShadowTypeDesc = __('Defines the look of the book center shadow.', 'pageFlip');
		$tAsymmetric = __('Asymmetric', 'pageFlip');
		$tSymmetric = __('Symmetric', 'pageFlip');
		$tDefault = __('Default', 'pageFlip');
		$tStaticShadowsDepth = __('Static Shadows Depth', 'pageFlip');
		$tStaticShadowsDepthDesc = __('Shadow intensity in the middle of the book. These shadow imitate fixed page curvature.', 'pageFlip');
		$tRigidPageSpeed = __('Rigid Page Speed', 'pageFlip');
		$tRigidPageSpeedDesc = __('The speed of a rigid page movement.', 'pageFlip');
		$tFlipSound = __('Flip Sound', 'pageFlip');
		$tFlipSoundDesc = __('This parameter is the flip sound source file', 'pageFlip');
		$tUploadSound = __('Upload Sound', 'pageFlip');
		$tUploadSoundDesc = __('You can upload your own sound. Sound must be mp3 file (&lt; 100 Kb).', 'pageFlip');
		$tPreloaderType = __('Preloader Type', 'pageFlip');
		$tPreloaderTypeDesc = __('The type of preloader in use.', 'pageFlip');
		$tProgressBar = __('Progress Bar', 'pageFlip');
		$tRound = __('Round', 'pageFlip');
		$tNone = __('None', 'pageFlip');
		$tPageBack = __('Page Back', 'pageFlip');
		$tPageBackDesc = __('This parameter determines the page background color. The color will be displayed when loading pages and used as color for empty pages. The color should be set in RGB in the following format: 0xNNNNNN, where N is hexadecimal number (0-F).', 'pageFlip');
		$tRigidPages = __('Rigid Pages', 'pageFlip');
		$tRigidPagesDesc = __('Setting this parameter to Yes makes all book pages rigid.', 'pageFlip');
		$tZoomEnabled = __('Zoom Enabled', 'pageFlip');
		$tZoomEnabledDesc = __('Controls the zooming function. When set to Yes, zooming is enabled (by double clicking and zoom button). When set to No, zooming is disabled.', 'pageFlip');
		$tZoomImageWidth = __('Zoom Image Width', 'pageFlip');
		$tZoomImageWidthDesc = __('Defines the width of large page in pixels.', 'pageFlip');
		$tZoomImageHeight = __('Zoom Image Height', 'pageFlip');
		$tZoomImageHeightDesc = __('Defines the height of large page in pixels.', 'pageFlip');
		$tZoomOnClick = __('Zoom On Click', 'pageFlip');
		$tZoomOnClickDesc = __('Turns zooming on double click on / off.', 'pageFlip');
		$tZoomHint = __('Zoom Hint', 'pageFlip');
		$tZoomHintDesc = __('Defines the caption text of the &quot;Double click for zooming&quot; hint window.', 'pageFlip');
		$tCenterBook = __('Center Book', 'pageFlip');
		$tCenterBookDesc = __('Setting this parameter to Yes centers the book within book area.', 'pageFlip');
		$tUseCustomCursors = __('Use Custom Cursors', 'pageFlip');
		$tUseCustomCursorsDesc = __('Setting this parameter to Yes enables custom mouse pointer for dragging and zooming.', 'pageFlip');
		$tDropShadowEnabled = __('Drop Shadow Enabled', 'pageFlip');
		$tDropShadowEnabledDesc = __('Setting this parameter to Yes enables drop shadow around the book.', 'pageFlip');
		$tDropShadowHideWhenFlipping = __('Drop Shadow Hide When Flipping', 'pageFlip');
		$tDropShadowHideWhenFlippingDesc = __('Setting this parameter to Yes forces the book drop shadow to hide when flipping pages. This allows to save CPU resources and speed up page flipping animation.', 'pageFlip');
		$tBackgroundColor = __('Background Color', 'pageFlip');
		$tBackgroundColorDesc = __('Defines the color of application background. Users see this color while the background image is loading. The color should be set in RGB in the following format: 0xNNNNNN, where N is hexadecimal number (0-F).', 'pageFlip');
		$tBackgroundImage = __('Background Image', 'pageFlip');
		$tBackgroundImageDesc = __('This parameter is the background of flipping book.', 'pageFlip');
		$tUploadBackgroundImage = __('Upload Background Image', 'pageFlip');
		$tUploadBackgroundImageDesc = __('You can upload your own background image. Image must be jpg or png file', 'pageFlip');
		$tBackgroundImagePlacement = __('Background Image Placement', 'pageFlip');
		$tBackgroundImagePlacementDesc = __('Defines the position of the application background image.', 'pageFlip');
		$tTopLeft = __('top left', 'pageFlip');
		$tCenter = __('center', 'pageFlip');
		$tFit = __('fit', 'pageFlip');
		$tPrintEnabled = __('Print Enabled', 'pageFlip');
		$tPrintEnabledDesc = __('Controls the printing function. When set to Yes, printing is enabled. When set to No, printing is disabled.', 'pageFlip');
		$tPrintTitle = __('Print Title', 'pageFlip');
		$tPrintTitleDesc = __('Defines the caption text of the print window.', 'pageFlip');
		$tNavigationBar = __('Navigation Bar', 'pageFlip');
		$tNavigationBarDesc = __('Controls the navigation bar. When set to Yes, navigation bar is enabled. When set to No, navigation bar is disabled.', 'pageFlip');
		$tNavigationBarPlacement = __('Navigation Bar Placement', 'pageFlip');
		$tNavigationBarPlacementDesc = __('', 'pageFlip');
		$tTop = __('Top', 'pageFlip');
		$tBottom = __('Bottom', 'pageFlip');

		$tDownload = __('Download', 'pageFlip');
		$tDownloadDesc = __('', 'pageFlip');
		$tDownloadURL = __('URL', 'pageFlip');
		$tDownloadURLDesc = __('', 'pageFlip');
		$tDownloadTitle = __('Title', 'pageFlip');
		$tDownloadTitleDesc = __('', 'pageFlip');
		$tDownloadSize = __('Size', 'pageFlip');
		$tDownloadSizeDesc = __('', 'pageFlip');
		$tDownloadComplete = __('Complete Text', 'pageFlip');
		$tDownloadCompleteDesc = __('', 'pageFlip');

		$tPopup = __('Popup');
		$tPopupDescription = __('Open book in popup window');

	 $secondButton = ($book->id == 0) ? '<input class="button" name="do" value="' .  __('Delete', 'pageFlip') . '" type="submit"/>' : '';

	 $text = "
  			<script  type=\"text/javascript\">
            //<![CDATA[
				function viewBackground(selObj)
            	{
            		var id = selObj.options[selObj.selectedIndex].value;
					var preview = '';
					switch(id)
					{
						 {$bgImagesAr}
					}
					document.getElementById('bgImagePreview').innerHTML = preview;
            	}
            //]]>
            </script>
            <div class=\"wrap\">
				<h2>{$title}</h2>
				<div id=\"ajax-response\"></div>
				<form name=\"addbook\" id=\"addbook\" method=\"post\" action=\"\" enctype=\"multipart/form-data\" class=\"add:the-list: validate\">
					<input name=\"action\" value=\"{$action}\" type=\"hidden\"/>
					<input name=\"bookId\" value=\"{$book->id}\" type=\"hidden\"/>
					<input name=\"galleryId\" value=\"{$galleryId}\" type=\"hidden\" />
					<table class=\"form-table\">
						<tbody>
						    <tr class=\"form-field form-required\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"name\">{$tBookName}</label></th>
								<td><input name=\"bookName\" id=\"name\" value=\"{$name}\" size=\"40\" type=\"text\"/>
					            <p>{$tBookNameDesc}</p></td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666; background-color: #e0e0e0\">
								<th scope=\"row\" valign=\"top\"><label for=\"stageWidth\">{$tStageWidth}</label></th>
								<td><input name=\"stageWidth\" id=\"stageWidth\" value=\"{$book->stageWidth}\" size=\"40\" type=\"text\"/>
					            <p>{$tStageWidthDesc}</p></td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"stageHeight\">{$tStageHeight}</label></th>
								<td><input name=\"stageHeight\" id=\"stageHeight\" value=\"{$book->stageHeight}\" size=\"40\" type=\"text\"/>
					            <p>{$tStageHeightDesc}</p></td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666; background-color: #e0e0e0\">
								<th scope=\"row\" valign=\"top\"><label for=\"width\">{$tWidth}</label></th>
								<td><input name=\"width\" id=\"width\" value=\"{$book->width}\" size=\"40\" type=\"text\"/>
					            <p>{$tWidthDesc}</p></td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"height\">{$tHeight}</label></th>
								<td><input name=\"height\" id=\"height\" value=\"{$book->height}\" size=\"40\" type=\"text\"/>
					            <p>{$tHeightDesc}</p></td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666; background-color: #e0e0e0\">
								<th scope=\"row\" valign=\"top\"><label for=\"scaleContent\">{$tScaleContent}</label></th>
								<td id=\"scaleContent\">
								  <label><input type=\"radio\" name=\"scaleContent\" value=\"true\" id=\"scaleContent_0\" style=\"width:10px; height:10px;\"{$sc_0}/> {$tYes}</label>&nbsp;
								  <label><input type=\"radio\" name=\"scaleContent\" value=\"false\" id=\"scaleContent_1\" style=\"width:10px; height:10px;\"{$sc_1}/> {$tNo}</label>
					              <p>{$tScaleContentDesc}</p>
					            </td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"centerContent\">{$tCenterContent}</label></th>
								<td id=\"centerContent\">
								  <label><input type=\"radio\" name=\"centerContent\" value=\"true\" id=\"centerContent_0\" style=\"width:10px; height:10px;\"{$cc_0}/> {$tYes}</label>&nbsp;
								  <label><input type=\"radio\" name=\"centerContent\" value=\"false\" id=\"centerContent_1\" style=\"width:10px; height:10px;\"{$cc_1}/> {$tNo}</label>
					              <p>{$tCenterContentDesc}</p>
					            </td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666; background-color: #e0e0e0\">
								<th scope=\"row\" valign=\"top\"><label for=\"preserveProportions\">{$tPreserveProportions}</label></th>
								<td id=\"preserveProportions\">
								  <label><input type=\"radio\" name=\"preserveProportions\" value=\"true\" id=\"preserveProportions_0\" style=\"width:10px; height:10px;\"{$pp_0}/> {$tYes}</label>&nbsp;
								  <label><input type=\"radio\" name=\"preserveProportions\" value=\"false\" id=\"preserveProportions_1\" style=\"width:10px; height:10px;\"{$pp_1}/> {$tNo}</label>
					              <p>{$tPreserveProportionsDesc}</p>
					            </td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"hardcover\">{$tHardcover}</label></th>
								<td id=\"hardcover\">
								  <label><input type=\"radio\" name=\"hardcover\" value=\"true\" id=\"hardcover_0\" style=\"width:10px; height:10px;\"{$h_0}/> {$tYes}</label>&nbsp;
								  <label><input type=\"radio\" name=\"hardcover\" value=\"false\" id=\"hardcover_1\" style=\"width:10px; height:10px;\"{$h_1}/> {$tNo}</label>
					              <p>{$tHardcoverDesc}</p>
					            </td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666; background-color: #e0e0e0\">
								<th scope=\"row\" valign=\"top\"><label for=\"hardcoverThickness\">{$tHardcoverThickness}</label></th>
								<td><input name=\"hardcoverThickness\" id=\"hardcoverThickness\" value=\"{$book->hardcoverThickness}\" size=\"40\" type=\"text\"/>
					            <p>{$tHardcoverThicknessDesc}</p></td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"frameWidth\">{$tFrameWidth}</label></th>
								<td><input name=\"frameWidth\" id=\"frameWidth\" value=\"{$book->frameWidth}\" size=\"40\" type=\"text\"/>
					            <p>{$tFrameWidthDesc}</p></td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666; background-color: #e0e0e0\">
								<th scope=\"row\" valign=\"top\"><label for=\"frameColor\">{$tFrameColor}</label></th>
								<td><input name=\"frameColor\" id=\"frameColor\" value=\"{$book->frameColor}\" size=\"40\" type=\"text\"/>
					            <p>{$tFrameColorDesc}</p></td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"frameAlpha\">{$tFrameAlpha}</label></th>
								<td><input name=\"frameAlpha\" id=\"frameAlpha\" value=\"{$book->frameAlpha}\" size=\"40\" type=\"text\"/>
					            <p>{$tFrameAlphaDesc}</p></td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666; background-color: #e0e0e0\">
								<th scope=\"row\" valign=\"top\"><label for=\"firstPage\">{$tFirstPage}</label></th>
								<td><input name=\"firstPage\" id=\"firstPage\" value=\"{$book->firstPage}\" size=\"40\" type=\"text\"/>
					            <p>{$tFirstPageDesc}</p></td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"flipOnClick\">{$tFlipOnClick}</label></th>
								<td id=\"flipOnClick\">
								  <label><input type=\"radio\" name=\"flipOnClick\" value=\"true\" id=\"flipOnClick_0\"{$foc_0} style=\"width:10px; height:10px;\" /> {$tYes}</label>&nbsp;
								  <label><input type=\"radio\" name=\"flipOnClick\" value=\"false\" id=\"flipOnClick_1\"{$foc_1} style=\"width:10px; height:10px;\" /> {$tNo}</label>
					              <p>{$tFlipOnClickDesc}</p>
					            </td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666; background-color: #e0e0e0\">
								<th scope=\"row\" valign=\"top\"><label for=\"handOverCorner\">{$tHandOverCorner}</label></th>
								<td id=\"handOverCorner\">
								  <label><input type=\"radio\" name=\"handOverCorner\" value=\"true\" id=\"handOverCorner_0\"{$hoc_0} style=\"width:10px; height:10px;\" /> {$tYes}</label>&nbsp;
								  <label><input type=\"radio\" name=\"handOverCorner\" value=\"false\" id=\"handOverCorner_1\"{$hoc_1} style=\"width:10px; height:10px;\" /> {$tNo}</label>
					              <p>{$tHandOverCornerDesc}</p>
					            </td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"handOverPage\">{$tHandOverPage}</label></th>
								<td id=\"handOverPage\">
								  <label><input type=\"radio\" name=\"handOverPage\" value=\"true\" id=\"handOverPage_0\"{$hop_0} style=\"width:10px; height:10px;\" /> {$tYes}</label>&nbsp;
								  <label><input type=\"radio\" name=\"handOverPage\" value=\"false\" id=\"handOverPage_1\"{$hop_1} style=\"width:10px; height:10px;\" /> {$tNo}</label>
					              <p>{$tHandOverPageDesc}</p>
					            </td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666; background-color: #e0e0e0\">
								<th scope=\"row\" valign=\"top\"><label for=\"alwaysOpened\">{$tAlwaysOpened}</label></th>
								<td id=\"alwaysOpened\">
								  <label><input type=\"radio\" name=\"alwaysOpened\" value=\"true\" id=\"alwaysOpened_0\" style=\"width:10px; height:10px;\"{$ao_0} /> {$tYes}</label>&nbsp;
								  <label><input type=\"radio\" name=\"alwaysOpened\" value=\"false\" id=\"alwaysOpened_1\" style=\"width:10px; height:10px;\"{$ao_1} /> {$tNo}</label>
					              <p>{$tAlwaysOpenedDesc}</p>
					            </td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"staticShadowType\">{$tStaticShadowType}</label></th>
								<td>
								    <select size=\"1\" name=\"staticShadowType\" id=\"staticShadowType\">
									  <option value=\"Asymmetric\"{$sst_0}>{$tAsymmetric}</option>
									  <option value=\"Symmetric\"{$sst_1}>{$tSymmetric}</option>
									  <option value=\"Default\"{$sst_2}>{$tDefault}</option>
									</select>
					                <p>{$tStaticShadowTypeDesc}</p>
					            </td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666; background-color: #e0e0e0\">
								<th scope=\"row\" valign=\"top\"><label for=\"staticShadowsDepth\">{$tStaticShadowsDepth}</label></th>
								<td><input name=\"staticShadowsDepth\" id=\"staticShadowsDepth\" value=\"{$book->staticShadowsDepth}\" size=\"40\" type=\"text\"/>
					            <p>{$tStaticShadowsDepthDesc}</p></td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"rigidPageSpeed\">{$tRigidPageSpeed}</label></th>
								<td><input name=\"rigidPageSpeed\" id=\"rigidPageSpeed\" value=\"{$book->rigidPageSpeed}\" size=\"40\" type=\"text\"/>
					            <p>{$tRigidPageSpeedDesc}</p></td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666; background-color: #e0e0e0\">
								<th scope=\"row\" valign=\"top\"><label for=\"flipSound\">{$tFlipSound}</label></th>
								<td>
								 {$flipSound}
					             <p>{$tFlipSoundDesc}</p>
					            </td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"sound\">{$tUploadSound}</label></th>
								<td><input name=\"sound\" id=\"sound\" value=\"\" size=\"40\" type=\"file\"/>
					            <p>{$tUploadSoundDesc}</p></td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666; background-color: #e0e0e0\">
								<th scope=\"row\" valign=\"top\"><label for=\"preloaderType\">{$tPreloaderType}</label></th>
								<td>
								    <select size=\"1\" name=\"preloaderType\" id=\"preloaderType\">
									  <option value=\"Progress Bar\"{$pt_0}>{$tProgressBar}</option>
									  <option value=\"Round\"{$pt_1}>{$tRound}</option>
									  <option value=\"None\"{$pt_2}>{$tNone}</option>
									</select>
					                <p>{$tPreloaderTypeDesc}</p>
					            </td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"pageBack\">{$tPageBack}</label></th>
								<td><input name=\"pageBack\" id=\"pageBack\" value=\"{$book->pageBack}\" size=\"40\" type=\"text\"/>
					            <p>{$tPageBackDesc}</p></td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666; background-color: #e0e0e0\">
								<th scope=\"row\" valign=\"top\"><label for=\"rigidPages\">{$tRigidPages}</label></th>
								<td id=\"rigidPages\">
								  <label><input type=\"radio\" name=\"rigidPages\" value=\"true\" id=\"rigidPages_0\"{$rp_0} style=\"width:10px; height:10px;\" /> {$tYes}</label>&nbsp;
								  <label><input type=\"radio\" name=\"rigidPages\" value=\"false\" id=\"rigidPages_1\"{$rp_1} style=\"width:10px; height:10px;\" /> {$tNo}</label>
					              <p>{$tRigidPagesDesc}</p>
					            </td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"zoomEnabled\">{$tZoomEnabled}</label></th>
								<td id=\"zoomEnabled\">
								  <label><input type=\"radio\" name=\"zoomEnabled\" value=\"true\" id=\"zoomEnabled_0\"{$ze_0} style=\"width:10px; height:10px;\" /> {$tYes}</label>&nbsp;
								  <label><input type=\"radio\" name=\"zoomEnabled\" value=\"false\" id=\"zoomEnabled_1\"{$ze_1} style=\"width:10px; height:10px;\" /> {$tNo}</label>
					              <p>{$tZoomEnabledDesc}</p>
					            </td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666; background-color: #e0e0e0\">
								<th scope=\"row\" valign=\"top\"><label for=\"zoomImageWidth\">{$tZoomImageWidth}</label></th>
								<td><input name=\"zoomImageWidth\" id=\"zoomImageWidth\" value=\"{$book->zoomImageWidth}\" size=\"40\" type=\"text\"/>
					            <p>{$tZoomImageWidthDesc}</p></td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"zoomImageHeight\">{$tZoomImageHeight}</label></th>
								<td><input name=\"zoomImageHeight\" id=\"zoomImageHeight\" value=\"{$book->zoomImageHeight}\" size=\"40\" type=\"text\"/>
					            <p>{$tZoomImageHeightDesc}</p></td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666; background-color: #e0e0e0\">
								<th scope=\"row\" valign=\"top\"><label for=\"zoomOnClick\">{$tZoomOnClick}</label></th>
								<td id=\"zoomOnClick\">
								  <label><input type=\"radio\" name=\"zoomOnClick\" value=\"true\" id=\"zoomOnClick_0\"{$zoc_0} style=\"width:10px; height:10px;\" /> {$tYes}</label>&nbsp;
								  <label><input type=\"radio\" name=\"zoomOnClick\" value=\"false\" id=\"zoomOnClick_1\"{$zoc_1} style=\"width:10px; height:10px;\" /> {$tNo}</label>
					              <p>{$tZoomOnClickDesc}</p>
					            </td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"zoomHint\">{$tZoomHint}</label></th>
								<td><input name=\"zoomHint\" id=\"zoomHint\" value=\"{$book->zoomHint}\" size=\"40\" type=\"text\"/>
					            <p>{$tZoomHintDesc}</p></td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666; background-color: #e0e0e0\">
								<th scope=\"row\" valign=\"top\"><label for=\"centerBook\">{$tCenterBook}</label></th>
								<td id=\"centerBook\">
								  <label><input type=\"radio\" name=\"centerBook\" value=\"true\" id=\"centerBook_0\"{$cb_0} style=\"width:10px; height:10px;\" /> {$tYes}</label>&nbsp;
								  <label><input type=\"radio\" name=\"centerBook\" value=\"false\" id=\"centerBook_1\"{$cb_1} style=\"width:10px; height:10px;\" /> {$tNo}</label>
					              <p>{$tCenterBookDesc}</p>
					            </td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"useCustomCursors\">{$tUseCustomCursors}</label></th>
								<td id=\"useCustomCursors\">
								  <label><input type=\"radio\" name=\"useCustomCursors\" value=\"true\" id=\"useCustomCursors_0\"{$ucc_0} style=\"width:10px; height:10px;\" /> {$tYes}</label>&nbsp;
								  <label><input type=\"radio\" name=\"useCustomCursors\" value=\"false\" id=\"useCustomCursors_1\"{$ucc_1} style=\"width:10px; height:10px;\" /> {$tNo}</label>
					              <p>{$tUseCustomCursorsDesc}</p>
					            </td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666; background-color: #e0e0e0\">
								<th scope=\"row\" valign=\"top\"><label for=\"dropShadowEnabled\">{$tDropShadowEnabled}</label></th>
								<td id=\"dropShadowEnabled\">
								  <label><input type=\"radio\" name=\"dropShadowEnabled\" value=\"true\" id=\"dropShadowEnabled_0\"{$dse_0} style=\"width:10px; height:10px;\" /> {$tYes}</label>&nbsp;
								  <label><input type=\"radio\" name=\"dropShadowEnabled\" value=\"false\" id=\"dropShadowEnabled_1\"{$dse_1} style=\"width:10px; height:10px;\" /> {$tNo}</label>
					              <p>{$tDropShadowEnabledDesc}</p>
					            </td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"dropShadowHideWhenFlipping\">{$tDropShadowHideWhenFlipping}</label></th>
								<td id=\"dropShadowHideWhenFlipping\">
								  <label><input type=\"radio\" name=\"dropShadowHideWhenFlipping\" value=\"true\" id=\"dropShadowHideWhenFlipping_0\"{$dshwf_0} style=\"width:10px; height:10px;\" /> {$tYes}</label>&nbsp;
								  <label><input type=\"radio\" name=\"dropShadowHideWhenFlipping\" value=\"false\" id=\"dropShadowHideWhenFlipping_1\"{$dshwf_1} style=\"width:10px; height:10px;\" /> {$tNo}</label>
					              <p>{$tDropShadowHideWhenFlippingDesc}</p>
					            </td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666; background-color: #e0e0e0\">
								<th scope=\"row\" valign=\"top\"><label for=\"backgroundColor\">{$tBackgroundColor}</label></th>
								<td><input name=\"backgroundColor\" id=\"backgroundColor\" value=\"{$book->backgroundColor}\" size=\"40\" type=\"text\"/>
					            <p>{$tBackgroundColorDesc}</p></td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"bgImage\">{$tBackgroundImage}</label></th>
								<td>
								<div id=\"bgImagePreview\" style=\"width:{$this->main->thumbWidth}px; height:{$this->main->thumbHeight}px;\">{$bgImage}</div>
								{$bgImageList}
					            <p>{$tBackgroundImageDesc}</p></td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666; background-color: #e0e0e0\">
								<th scope=\"row\" valign=\"top\"><label for=\"image\">{$tUploadBackgroundImage}</label></th>
								<td><input name=\"image[]\" id=\"image\" value=\"\" size=\"40\" type=\"file\"/>
					            <p>{$tUploadBackgroundImageDesc}</p></td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"backgroundImagePlacement\">{$tBackgroundImagePlacement}</label></th>
								<td>
								    <select size=\"1\" name=\"backgroundImagePlacement\" id=\"backgroundImagePlacement\">
									  <option value=\"top left\"{$bip_0}>{$tTopLeft}</option>
									  <option value=\"center\"{$bip_1}>{$tCenter}</option>
									  <option value=\"fit\"{$bip_2}>{$tFit}</option>
									</select>
					                <p>{$tBackgroundImagePlacementDesc}</p>
					            </td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666; background-color: #e0e0e0\">
								<th scope=\"row\" valign=\"top\"><label for=\"printEnabled\">{$tPrintEnabled}</label></th>
								<td id=\"printEnabled\">
								  <label><input type=\"radio\" name=\"printEnabled\" value=\"true\" id=\"printEnabled_0\"{$pe_0} style=\"width:10px; height:10px;\" /> {$tYes}</label>&nbsp;
								  <label><input type=\"radio\" name=\"printEnabled\" value=\"false\" id=\"printEnabled_1\"{$pe_1} style=\"width:10px; height:10px;\" /> {$tNo}</label>
					              <p>{$tPrintEnabledDesc}</p>
					            </td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"printTitle\">{$tPrintTitle}</label></th>
								<td><input name=\"printTitle\" id=\"printTitle\" value=\"{$book->printTitle}\" size=\"40\" type=\"text\"/>
					            <p>{$tPrintTitleDesc}</p></td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666; background-color: #e0e0e0;\">
								<th scope=\"row\" valign=\"top\"><label for=\"navigation\">{$tNavigationBar}</label></th>
								<td id=\"navigation\">
								  <label><input type=\"radio\" name=\"navigation\" value=\"true\" id=\"navigation_0\"{$nb_0} style=\"width:10px; height:10px;\" /> {$tYes}</label>&nbsp;
								  <label><input type=\"radio\" name=\"navigation\" value=\"false\" id=\"navigation_1\"{$nb_1} style=\"width:10px; height:10px;\" /> {$tNo}</label>
					              <p>{$tNavigationBarDesc}</p>
					            </td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"navigation_place\">{$tNavigationBarPlacement}</label></th>
								<td id=\"navigation_place\">
								  <label><input type=\"radio\" name=\"navigationBarPlacement\" value=\"top\" id=\"navigation_place_0\"{$nbp_0} style=\"width:10px; height:10px;\" /> {$tTop}</label>&nbsp;
								  <label><input type=\"radio\" name=\"navigationBarPlacement\" value=\"bottom\" id=\"navigation_place_1\"{$nbp_1} style=\"width:10px; height:10px;\" /> {$tBottom}</label>
					              <p>{$tNavigationBarPlacementDesc}</p>
					            </td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666; background-color: #e0e0e0;\">
								<th scope=\"row\" valign=\"top\"><label for=\"download\">{$tDownload}</label></th>
								<td id=\"download\">
									<label><input type=\"radio\" id=\"download_yes\" name=\"download\"{$downl_0} value=\"yes\" style=\"width:10px; height:10px;\" /> {$tYes}</label>&nbsp;
									<label><input type=\"radio\" id=\"download_no\" name=\"download\"{$downl_1} value=\"no\" style=\"width:10px; height:10px;\" /> {$tNo}</label>
									<p>{$tDownloadDesc}</p>

									<div id=\"download_options\">
										<p><label for=\"downloadURL\">{$tDownloadURL}</label><br />
										<input type=\"text\" id=\"downloadURL\" name=\"downloadURL\" value=\"$book->downloadURL\" /><br />
										<small>{$tDownloadURLDesc}</small></p>

										<p><label for=\"downloadTitle\">{$tDownloadTitle}</label><br />
										<input type=\"text\" id=\"downloadTitle\" name=\"downloadTitle\" value=\"$book->downloadTitle\" /><br />
										<small>{$tDownloadTitleDesc}</small></p>

										<p><label for=\"downloadSize\">{$tDownloadSize}</label><br />
										<input type=\"text\" id=\"downloadSize\" name=\"downloadSize\" value=\"$book->downloadSize\" /><br />
										<small>{$tDownloadSizeDesc}</small></p>

										<p><label for=\"downloadComplete\">{$tDownloadComplete}</label><br />
										<input type=\"text\" id=\"downloadComplete\" name=\"downloadComplete\" value=\"$book->downloadComplete\" /><br />
										<small>{$tDownloadCompleteDesc}</small></p>
									</div>
									<script type=\"text/javascript\">
										var
											download_yes = document.getElementById('download_yes'),
											download_no = document.getElementById('download_no'),
											prev_url = ' ';

										download_yes.onchange =
										download_no.onchange =
										download_options =
										function()
										{
											if (download_yes.checked)
											{
												document.getElementById('download_options').style.display = 'block';
												if (prev_url != ' ')
													document.getElementById('downloadURL').value = prev_url;
											}
											else
											{
												document.getElementById('download_options').style.display = 'none';
												prev_url = document.getElementById('downloadURL').value;
												document.getElementById('downloadURL').value = ' ';
   											}
										}

										download_options();
									</script>
								</td>
							</tr>
							<tr class=\"form-field\" style=\"border-bottom: 1px solid #666666;\">
								<th scope=\"row\" valign=\"top\"><label for=\"navigation_place\">{$tPopup}</label></th>
								<td id=\"navigation_place\">
								  <label><input type=\"radio\" name=\"popup\" value=\"true\" id=\"popup_0\"{$pop_0} style=\"width:10px; height:10px;\" /> {$tYes}</label>&nbsp;
								  <label><input type=\"radio\" name=\"popup\" value=\"false\" id=\"popup_1\"{$pop_1} style=\"width:10px; height:10px;\" /> {$tNo}</label>
								  <p>{$tPopupDescription}</p>
					            </td>
							</tr>
						</tbody>
					</table>
					<p class=\"submit\">
					   <input class=\"button\" name=\"actionButton\" value=\"{$button}\" type=\"submit\"/>
					   {$secondButton}
					</p>
				</form>
			</div>
";

		return $text;
   }

   
   function mainPage()
   {
		global $wpdb;

		$text = '
				<p><strong>' . __('Form Plugin Installation', 'pageFlip') . '</strong></p>
				<p>' . __('This flipping book plugin is simple to install and can be done in a matter of minutes. The following steps will guide you through the process:', 'pageFlip') . '</p>
				<p>' . __('1. Unpack the *.zip file and extract the /page-flip-image-gallery/ folder.', 'pageFlip') . '<br/>
				' . __('2. Using an FTP program, upload the /page-flip-image-gallery/ folder to your WordPress plugins directory (Example: /wp-content/plugins/).', 'pageFlip') . '<br/>
				' . __('3. Open your WordPress Admin panel and go to the &quot;Plugins&quot; page. Locate the &quot;FlippingBook gallery&quot; plugin and click on the &quot;Activate&quot; link.', 'pageFlip') . '<br/>
				' . __('4. Once activated, go to the &quot;FlippingBook&quot; page and select the &quot;Configuration&quot; link on the sub-menu. If you need help, review the &quot;FAQ&quot;.', 'pageFlip') . '</p>
				<p>' . __('To add the FlippingBook add this code on the text area:', 'pageFlip') . '</p>
				<p><code><strong>[book id=\'1\' /]</strong></code></p>

				<p>' . __("Where the number 1 refers to the ID number of your book. To display a different book, simply replace the number 1 with the appropriate ID number of the book you wish to use. &quot;Save&quot; the page.", 'pageFlip') . '<br />
				' . __("That's it, you're about done. The FlippingBook will be on the new page you created.", 'pageFlip') . '</p>
				<p>' . __('If you encounter problems, review the &quot;<a href="http://pageflipgallery.com/faq-and-tutorials/" title="FlippingBook Wordpress Gallery FAQ">FAQs</a>&quot; or contact via <a href="mailto:pageflip@pageflipgallery.com">pageflip@pageflipgallery.com</a>.', 'pageFlip') . '</p>';

	
	$sqlVersion = $wpdb->get_var( "SELECT VERSION() AS version" );

	
	if( ini_get( 'safe_mode' ) ) $safeMode = __( 'On', 'pageFlip' );
	else $safeMode = __( 'Off', 'pageFlip' );
	
	if( ini_get( 'upload_max_filesize' ) ) $uploadMaxFilesize = ini_get( 'upload_max_filesize' );
	else $uploadMaxFilesize = __( 'N/A', 'pageFlip' );
	
	if( ini_get( 'post_max_size' ) ) $postMaxSize = ini_get( 'post_max_size' );
	else $postMaxSize = __( 'N/A', 'pageFlip' );
	
	if( ini_get( 'max_execution_time' ) ) $maxExecutionTime = ini_get( 'max_execution_time' );
	else $maxExecutionTime = __( 'N/A', 'pageFlip' );
	
	if( ini_get( 'memory_limit' ) ) $memoryLimit = ini_get( 'memory_limit' );
	else $memoryLimit = __( 'N/A', 'pageFlip' );
	
	if ( function_exists( 'memory_get_usage' ) ) $memoryUsage = round( memory_get_usage() / 1024 / 1024, 2 ) . __(' MByte', 'pageFlip');
	else $memoryUsage = __('N/A', 'pageFlip');
	
	if( ini_get( 'allow_url_fopen' ) ) $allowUrlOpen = __( 'On', 'pageFlip' );
	else $allowUrlOpen = '<strong style="color:#c00;">'.__( 'Off', 'pageFlip' ).'</strong>';

		$text .= '
				<table class="widefat fixed" cellspacing="0">
					<thead>
						<tr class="thead">
							<th scope="col" class="" style="">Server Settings</th>
						</tr>
					</thead>
					<tbody id="users" class="">
						<tr id="user-1" class="alternate">
							<td class="">
								<ul class="settings">
				      			  <li>' . __('Operating System', 'pageFlip') . ' : <span>' . PHP_OS . '</span></li>
								  <li>' . __('Server', 'pageFlip') . ' : <span>' . $_SERVER["SERVER_SOFTWARE"] . '</span></li>
								  <li>' . __('Memory usage', 'pageFlip') . ' : <span>' . $memoryUsage . '</span></li>
								  <li>' . __('MySQL Version', 'pageFlip') . ' : <span>' . $sqlVersion . '</span></li>
								  <li>' . __('PHP Version', 'pageFlip') . ' : <span>' . PHP_VERSION . '</span></li>
								  <li>' . __('PHP Safe Mode', 'pageFlip') . ' : <span>' . $safeMode . '</span></li>
								  <li>' . __('PHP Memory Limit', 'pageFlip') . ' : <span>' . $memoryLimit . '</span></li>
								  <li>' . __('PHP Max Upload Size', 'pageFlip') . ' : <span>' . $uploadMaxFilesize . '</span></li>
								  <li>' . __('PHP Max Post Size', 'pageFlip') . ' : <span>' . $postMaxSize . '</span></li>
								  <li>' . __('PHP Max Script Execute Time', 'pageFlip') . ' : <span>' . $maxExecutionTime . 's</span></li>
								  <li>' . __('PHP Allow URL Open', 'pageFlip') . ' : <span>' . $allowUrlOpen . '</span></li>
						   		</ul>
							</td>
						</tr>
					</tbody>
				</table>

				';

		   return $text;
   }

   
   function navigationLink( $text, $page, $adParam = '' )
   {
   		return '<a href="#" onclick="goToPage(' . $page . $adParam . ');">' . $text . '</a>';
   }

   
   function itemsPerPageLink( $text, $count, $adParam = '' )
   {
   		return '<a href="#" onclick="itemsPerPage(' . $count . $adParam . ');">' . $text . '</a>';
   }

   
	function addPageMenu( $type = 'pageFlip' )
	{
		$tPF = __('PageFlip Images', 'pageFlip');
		$tWPMedia = __('Assign from WP Media', 'pageFlip');
		$tNGG = __('Assign from NextGEN Gallery', 'pageFlip');

		$active_plugins = get_option( 'active_plugins' );

		$text = ( $type !== 'pageFlip' ) ? '<a href="#" onclick="goTo( \'pageFlip\' ); return false;">'. $tPF .'</a>' : '<strong>'. $tPF .'</strong>' ;
		$text .= '&nbsp;|&nbsp;';
		$text .= ( $type !== 'WPMedia' ) ? '<a href="#" onclick="goTo( \'WPMedia\' ); return false;">'. $tWPMedia .'</a>' : '<strong>'. $tWPMedia .'</strong>' ;
		if ( in_array( 'nextgen-gallery/nggallery.php', $active_plugins ) )
		{
			$text .= '&nbsp;|&nbsp;';
			$text .= ( $type !== 'NGGallery' ) ? '<a href="#" onclick="goTo( \'NGGallery\' ); return false;">'. $tNGG .'</a>' : '<strong>'. $tNGG .'</strong>' ;
		}

		return $text;
	}
}

?>