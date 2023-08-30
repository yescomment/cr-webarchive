<?php 



 $actions = array( 'loadalbumxml', 'savealbumxml', 'loadlayouts' );
 if( !in_array( $_GET['action'], $actions ) || empty( $_GET['id'] ) ) 
	{ die('You are not allowed to call this page directly.'); }


$post = Array('feAction' => $_GET['action'], 'cookie' => $cookie, 'bookId' => (int)$_GET['id']);
 
if( $_GET['action'] == 'savealbumxml' )
{
	$xml = file_get_contents("php://input");
	$post['xml'] = UrlEncode( $HTTP_RAW_POST_DATA ); 
}

$cookie = 'wp-settings-time-1%3D1238660065%3B%20wp-settings-1%3Deditor%253Dtinymce%2526mfold%253Do%2526m0%253Do%2526m1%253Dc%2526m2%253Dc%2526m3%253Dc%2526m4%253Do%2526m5%253Dc%2526m6%253Dc%2526m7%253Dc%2526m8%253Dc%2526m9%253Dc%2526m10%253Dc%3B%20wordpress_test_cookie%3DWP%2BCookie%2Bcheck%3B%20_csuid%3DXf14584cda9762a%3B%20SESScfc90a62c81b7bfc6f292320b1d0b8ca%3De909f2e05fbd93fac8e6f5314e2c8d25%3B%20PHPSESSID%3D1209e7f231cd9cbec19087e2294aa902';
		  






  function make_http_post_request($server, $uri, $post, $uagent) {  
       $_post = Array();  
     
       if (is_array($post)) {  
           foreach ($post as $name => $value) {  
               $_post[] = $name.'='.urlencode($value);  
           }  
       }  
    
       $post = implode('&', $_post);  
     
       $fp = fsockopen($server, 80);  
     
       if ($fp) {  
           fputs($fp, "POST $uri HTTP/1.1\r\nHost: $server \r\n".  
                   "User-Agent: $uagent \r\nContent-Type:".  
                   " application/x-www-form-urlencoded\r\n".  
                   "Content-Length: ".strlen($post)."\r\n".  
                   "Connection: close\r\n\r\n$post");  
           $content = '';  
           while (!feof($fp)) {  
               $content  .= fgets($fp);  
           }  
           fclose($fp);  
     
           return $content;  
       }  
     
       return false;  
   }
   
   $cookie =urlencode($_SERVER['HTTP_COOKIE']);
   
   $uri = substr( $_SERVER['SCRIPT_NAME'], 0, strpos( $_SERVER['SCRIPT_NAME'], '/wp-content/' ) );
   $uri .= '/wp-admin/admin-ajax.php';
 
 
 
 
   
 $content = make_http_post_request($_SERVER['HTTP_HOST'], $uri, $post, false, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');  
   
 if ($content !== false) {  
     
	 
	 
	 $content = substr( $content, strpos( $content, '<?xml' ) );
	 $content = trim( $content );
	 header( 'Content-type: text/xml' ); 

	 if( substr( $content, -1 ) == '0' ) $content = substr( $content, 0, strlen( $content ) - 1 );
	 echo $content;
}

?>