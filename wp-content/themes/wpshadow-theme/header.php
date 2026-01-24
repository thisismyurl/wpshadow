<?php
/**
 * Header template.
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<header class="site-header">
	<div class="container header-inner">
		<div class="branding">
			<?php if ( has_custom_logo() ) : ?>
				<div class="logo"><?php the_custom_logo(); ?></div>
			<?php else : ?>
				<a class="site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
			<?php endif; ?>
			<p class="site-tagline"><?php bloginfo( 'description' ); ?></p>
		</div>
		<nav class="primary-nav" aria-label="<?php esc_attr_e( 'Primary Menu', 'wpshadow-theme' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'menu_class'     => 'menu',
				'container'      => false,
				'fallback_cb'    => false,
			) );
			?>
		</nav>
	</div>
</header>
<main class="site-main container">
