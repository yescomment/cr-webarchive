<?php
/*
Template Name: cr-museum
*/
?>
<?php get_header(); ?>
<div id="container" class="clearfix">
	<div id="leftbar">
			  <div id="local">
			  <ul id="local-nav"> 
					<li><a href="<?php bloginfo('template_directory'); ?>/permanentcollection/">Permanent Collection</a></li>
					<li><a href="<?php bloginfo('template_directory'); ?>/communitycollections/">Community Collections</a></li>
					<li><a href="<?php bloginfo('template_directory'); ?>/exhibitionhall/">Rotating Exhibition Hall</a></li>
					<li><a href="<?php bloginfo('template_directory'); ?>/galleries/">Image Galleries</a></li>
				</ul>
				</div>
				
	 <div class="shadow">
	 <div class="drop">
	 <div id="contact-tube" class="cast">
	 <div class="line1">
	 <div class="line2">
	  <span id="s-location" class="s-ir"></span>
	  <p>370 Metropolitan Ave <br />Brooklyn, NY 11211</p>
	  <span id="s-phone" class="s-ir"></span>
	  <p>718-78-24842<br /> 718-RU-CIVIC</p>  
	  <span id="s-email" class="s-ir"></span>
	  <p>info@cityreliquary.com</p>
	 </div>
	 </div>
	 </div>
	 </div>
	 </div>
	 
	 <div class="shadow">
		<div class="drop">
		<div class="rd-tube cast">
		<h3 id="h-press" class="ir"></h3>
			<ul>
			  <li><p>New York Times, City Section "The Night of Know-It-Alls"</p></li>
			  <li><p>New York Times, Metro Section "The Collector's New York"</p></li>
			  <li><p>National Public Radio, 10/01/06 episode of Weekend Edition Sunday</p></li>
			</ul>
		<div class="f-tube"></div>
		</div>
		</div>
		</div>	

	</div>
	<div id="content" class="widecolumn" role="main">

		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<div class="post" id="post-<?php the_ID(); ?>">
		<h2><?php the_title(); ?></h2>
			<div class="entry">
				<?php the_content('<p class="serif">Read the rest of this page &raquo;</p>'); ?>

				<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>

			</div>
		</div>
		<?php endwhile; endif; ?>
	<?php edit_post_link('Edit this entry.', '<p>', '</p>'); ?>
	</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
