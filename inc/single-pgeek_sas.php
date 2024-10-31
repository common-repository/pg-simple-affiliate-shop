<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">
			<h1>This is the SAS Single Product Page sample</h1>
			<p>PG Simple Affiliate Shop is a custom post type and the default post type is 'pgeek_sas'</p>
			<p>Wordpress allows you to create a template in your theme for a single page of a custom post type - <a href="http://codex.wordpress.org/Template_Hierarchy#Single_Post_display" target="_blank">WordPress Template Hierarchy</a></p>
			<p>Remove these lines and customise your template page and add it to your theme or child theme - this one is based on <a href="http://wordpress.org/extend/themes/twentytwelve" target="_blank">twentytwelve</a>. Basically a copy of single.php from whatever theme you are using and then you add your own customisations.</p>
					
			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'content', get_post_format() ); ?>

				<nav class="nav-single">
					<h3 class="assistive-text"><?php _e( 'Post navigation', 'twentytwelve' ); ?></h3>
					<span class="nav-previous"><?php previous_post_link( '%link', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'twentytwelve' ) . '</span> %title' ); ?></span>
					<span class="nav-next"><?php next_post_link( '%link', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'twentytwelve' ) . '</span>' ); ?></span>
				</nav><!-- .nav-single -->

				<?php comments_template( '', true ); ?>

			<?php endwhile; // end of the loop. ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>