<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

<title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>

<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<style type="text/css" media="screen">

<?php
// Checks to see whether it needs a sidebar or not
if ( empty($withcomments) && !is_single() ) {
?>
	#page { background: transparent; }
<?php } else { // No sidebar ?>
	#page { background: transparent; }
<?php } ?>

</style>

<?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>

<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<div id="wrapper">

<div id="page" class="clearfix">

<div id="header" class="clearfix" role="banner">
  <div id="home-link"><a href="http://www.cityreliquary.org/nowinhighfidelity/"></a></div>
	<div id="nav">
	<ul>
	<li class="nav-visit"><a href="/plan-your-visit/">Visit</a></li>
	<li class="nav-museum"><a href="/museum/">Museum</a></li>
	<li class="nav-calendar"><a href="../category/calendar/">Calendar</a></li>
	<li class="nav-shop"><a href="/shop/">Shop</a></li>
	<li class="nav-volunteer"><a href="/volunteer/">Volunteer</a></li>
	<li class="nav-about"><a class="last" href="/about/">About</a></li>
	</ul>
	</div>
</div>
