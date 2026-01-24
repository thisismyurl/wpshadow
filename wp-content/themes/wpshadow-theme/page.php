<?php
/**
 * Page template.
 */
get_header();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'card' ); ?>>
	<?php while ( have_posts() ) : the_post(); ?>
		<header class="entry-header">
			<h1 class="entry-title"><?php the_title(); ?></h1>
		</header>
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
		<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		?>
	<?php endwhile; ?>
</article>
<?php get_footer(); ?>
