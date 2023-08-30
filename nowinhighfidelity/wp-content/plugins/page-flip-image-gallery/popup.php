<?php 

include '../../../wp-config.php';

$book_id = $wpdb->escape(htmlspecialchars($_GET['book_id']));
$title = htmlspecialchars($_GET['title']);

;echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>'; echo $title; ;echo '</title>

'; wp_head(); ;echo '
</head>

<body style="margin:0; font-family:sans-serif;">
<div>
';

$pageFlip->isPopup = true;
echo $pageFlip->replaceBooks( array('id'=>$book_id) );

;echo '</div>
</body>
</html>';
?>