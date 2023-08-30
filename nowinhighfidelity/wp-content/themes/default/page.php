<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */

get_header(); ?>

<div id="container" class="clearfix">
	<div id="leftbar">
			 				
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
	 
	 <!--<div class="shadow">
		<div class="drop">
		<div class="rd-tube cast">
		<h3 id="h-press" class="ir"></h3>
			<ul>
			  <li><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras eu </p></li>
			  <li><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras eu </p></li>
			  <li><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras eu </p></li>
			</ul>
		<div class="f-tube"></div>
		</div>
		</div>
		</div>-->

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
