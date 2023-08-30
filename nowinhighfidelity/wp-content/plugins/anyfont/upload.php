<?php

define('DOING_AJAX', true);
define('WP_ADMIN', true);

$root = dirname( __FILE__ );

define('ABSPATH', realpath('./../../../')."/");

if ( file_exists( ABSPATH . 'wp-config.php') ) {
	/** The config file resides in ABSPATH */
	require_once( ABSPATH . 'wp-config.php' );
} elseif ( file_exists( dirname(ABSPATH) . '/wp-config.php' ) ) {
	/** The config file resides one level below ABSPATH */
	require_once( dirname(ABSPATH) . '/wp-config.php' );
} else {
	header("HTTP/1.0 500 Server Error");
	_e("We could not find your <code>wp-config.php</code> file.");
	exit(0);
}

require_once(ABSPATH.'wp-admin/includes/admin.php');

!defined('WP_CONTENT_URL') ? define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content') :0;
!defined('WP_CONTENT_DIR') ? define('WP_CONTENT_DIR', ABSPATH . 'wp-content') : 0;
!defined('WP_PLUGIN_URL') ? define('WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins') : 0;
!defined('WP_PLUGIN_DIR') ? define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins') : 0;
define('ANYFONT_ROOT', WP_PLUGIN_DIR."/".basename(dirname( __FILE__ )));
define('ANYFONT_URL', WP_PLUGIN_URL."/".basename(dirname( __FILE__ )));
define('ANYFONT_FONTDIR', WP_CONTENT_DIR."/fonts");
define('ANYFONT_CACHE', WP_CONTENT_DIR."/font-cache");

if(!is_user_logged_in()){
	die('-1');
}

if(!current_user_can('upload_files')){
	send_response(array(
		"success"	=>	false,
		"failure"	=>	__("You are not allowed to upload files. Please contact your Administrator for assistance.", 'anyfont')
	));
}elseif($_FILES){
	list($name,$result) = upload('font', ANYFONT_FONTDIR, 'ttf,otf');
	if($name){
		$details = stat(ANYFONT_FONTDIR."/$name");
		$name_array = explode(".tt", $name);
		$name_array[1] != "f" ? $name_array = explode(".otf", $name) : 0;
		send_response(array(
			"success"	=>	true,
			"failure"	=>	false,
			"file_name"	=>	$name_array[0],
			"img_url"	=>	get_option( 'siteurl' )."/images/admin/".$name_array[0].".png",
			"img_del"	=>	ANYFONT_URL."/img/icon-delete.png"
		));
	} else {
		send_response(array(
			"success"	=>	false,
			"failure"	=>	$result
		));
	}
}else{
	send_response(array(
		"success"	=>	false,
		"failure"	=>	__("File Upload Error", 'anyfont')
	));
}

function upload($file_id, $folder=false, $types=false) {
    if(!$_FILES[$file_id]['name']) return array('','No file specified');

    $file_name = str_replace("&", "and", $_FILES[$file_id]['name']);

	$ext_arr = split("\.",basename($file_name));
    $ext = strtolower($ext_arr[count($ext_arr)-1]);
	$file_name = $ext_arr[0];

    if($types) {
		$all_types = explode(",",strtolower($types));
        if(in_array($ext,$all_types));
        else {
            $result = "'".$_FILES[$file_id]['name']."' is not a valid file.";
            return array('',$result);
        }
    }

	if($folder){
		$uploadfile = $folder."/$file_name.$ext";
	} else {
		$result = "Server Error.";
		return array('',$result);
	}

    $result = '';

	if (!move_uploaded_file($_FILES[$file_id]['tmp_name'], $uploadfile)) {
        $result = "Cannot upload the file '".$_FILES[$file_id]['name']."'"; //Show error if any.
        if(!file_exists($folder)) {
            $result .= " Folder does not exist.";
        } elseif(!is_writable($folder)) {
            $result .= " Folder is not writable.";
        } elseif(!is_writable($uploadfile)) {
            $result .= " File is not writable.";
        }
        $file_name = '';
    } else {
        if(!$_FILES[$file_id]['size']) {
            @unlink($uploadfile);
            $file_name = '';
            $result = "Empty file found - please use a valid file.";
        } else {
            chmod($uploadfile,0777);
        }
    }

    return array("$file_name.$ext",$result);
}

function send_response($result){
	if (function_exists('json_encode')) {
		echo json_encode($result);
	} else {
		require_once(ANYFONT_ROOT.'/lib/class.json.php');
		$JSON = new serviceJSON();
		echo $JSON->encode($result);
	}
}