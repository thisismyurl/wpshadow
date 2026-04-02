<?php
/**
 * Single post template.
 */
get_header();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'card' ); ?>>
	<?php while ( have_posts() ) : the_post(); ?>
		<header class="entry-header">
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<div class="entry-meta"><?php echo get_the_date(); ?></div>
		</header>
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
		<?php
			wp_link_pages( array(
				'before' => '<div class="page-links">' . __( 'Pages:', 'wpshadow-theme' ),
				'after'  => '</div>',
			) );
		?>
	<?php endwhile; ?>
</article>
<?php get_footer(); ?>
