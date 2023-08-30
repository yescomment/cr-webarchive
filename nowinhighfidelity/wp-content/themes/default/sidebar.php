<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */
?>

	<div id="sidebar" role="complementary">
	
	<div class="shadow">
		<div class="drop">
		<ul class="cast">
			<?php 	/* Widgetized sidebar, if you have the plugin installed. */
					if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : ?>
			<li>
				<?php get_search_form(); ?>
			</li>

		</ul>
	</div>
	</div>
		
	<div class="shadow">
	<div class="drop">
	<div id="sub-events" class="gr-tube cast">
	<h3 id="h-upcoming" class="ir"></h3>
		<div role="navigation">
			
			<!--<div class="sidelist">
			
			<h2>Archives</h2>
				<ul class="li-lines">
				<?php wp_get_archives('type=monthly'); ?>
				</ul>
			</div>-->
			
			<div class="sidelist">
				<ul>
				      <!--<?php wp_list_categories('show_count=1&title_li=<h2>Categories</h2>'); ?>-->
				  		<?php $recent = new WP_Query("cat=1&showposts=10"); while($recent->have_posts()) : $recent->the_post();?>
							<li><a href="<?php the_permalink() ?>" rel="bookmark">
							<?php the_title(); ?></a></li>
							<?php endwhile; ?>
				</ul>
				

							
			</div>
			
			<div class="sidelist">
				<?php wp_loginout(); ?>
			</div>
			
		</div>
		
	<div class="f-tube"></div>
	</div>
	</div>
	</div>	
	
		
	<div class="shadow">
	<div class="drop">
	<div class="bl-tube cast">
	<h3 id="h-signup" class="ir"></h3>	
		<ul>
		<!--<li>
<form id="joinform" method="get" action="http://www.cityreliquary.org/nowinhighfidelity/">
<input id="join" type="text" value="" class="texter" name="s"/>
<input id="joinsubmit" type="image" src="<?php bloginfo('template_directory'); ?>/images/btn-join.png"/>
</form></li>-->
      <li><?php wpsb_opt_in(); ?></li>
			<?php endif; ?>
		</ul>
	<div class="n-tube"></div>	
	</div>	
	</div>
	</div>
	
	<!--<div class="shadow">
	<div class="drop">
	<div id="sub-feed" class="gr-tube cast">
	<h3 id="h-feed" class="ir"></h3>
		<ul>
		  <li><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras eu </p></li>
		</ul>
	<div class="n-tube"></div>
	</div>
	</div>
	</div>-->	
		
	
	</div>

