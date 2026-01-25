<?php
/**
 * Archive template.
 */
get_header();
?>
<header class="archive-header card">
	<h1 class="archive-title"><?php the_archive_title(); ?></h1>
	<?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
</header>
<section class="content-grid">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'card' ); ?>>
				<header class="entry-header">
					<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<div class="entry-meta"><?php echo get_the_date(); ?></div>
				</header>
				<div class="entry-content">
					<?php the_excerpt(); ?>
				</div>
			</article>
		<?php endwhile; ?>

		<div class="pagination">
			<?php the_posts_pagination(); ?>
		</div>
	<?php else : ?>
		<article class="card">
			<h2><?php esc_html_e( 'Nothing found', 'wpshadow-theme' ); ?></h2>
		</article>
	<?php endif; ?>
</section>
<?php get_footer(); ?>
