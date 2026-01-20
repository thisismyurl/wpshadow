<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_CSS_Class_Cleanup extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'              => 'css-class-cleanup',
				'name'            => __( 'Remove Extra Code Labels', 'wpshadow' ),
				'description'     => __( 'Remove extra labels in your site code that you don\'t need. Makes pages smaller and load faster.', 'wpshadow' ),
				'scope'           => 'core',
				'default_enabled' => false,
				'version'         => '1.0.0',
				'widget_group'    => 'cleanup',
				'aliases'         => array( 'css classes', 'clean classes', 'body classes', 'post classes', 'nav classes', 'html cleanup', 'class bloat', 'css optimization', 'class removal', 'html bloat', 'remove classes', 'simplify markup' ),
				'sub_features'    => array(
					'clean_post_classes'   => array(
						'name'               => __( 'Clean Post Classes', 'wpshadow' ),
						'description_short'  => __( 'Remove extra classes from post elements', 'wpshadow' ),
						'description_long'   => __( 'Removes unnecessary CSS classes from post elements that WordPress adds automatically. WordPress adds dozens of classes to posts for functionality that often isn\'t needed, including post IDs, format information, and status classes. This reduction can save 5-10% of HTML size on post-heavy pages. Keeps only essential classes that themes actually use.', 'wpshadow' ),
						'description_wizard' => __( 'WordPress adds many unnecessary classes to posts. Removing them makes HTML cleaner and slightly smaller. Most themes don\'t need all of them.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'clean_nav_classes'    => array(
						'name'               => __( 'Clean Menu Item Classes', 'wpshadow' ),
						'description_short'  => __( 'Remove extra classes from menu items', 'wpshadow' ),
						'description_long'   => __( 'Removes unnecessary CSS classes from menu items that WordPress adds automatically. Menu items get classes for depth levels, activity states, and parent-child relationships. Many of these can be simplified since modern CSS can handle styling without all these classes. Reduces HTML size and makes menus easier to style with CSS.', 'wpshadow' ),
						'description_wizard' => __( 'Simplify navigation menu HTML by removing extra classes. Makes CSS easier to write and HTML smaller.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'remove_nav_ids'       => array(
						'name'               => __( 'Remove Menu Item IDs', 'wpshadow' ),
						'description_short'  => __( 'Remove ID attributes from menu items', 'wpshadow' ),
						'description_long'   => __( 'Removes ID attributes from menu items that WordPress adds automatically for tracking purposes. These IDs are rarely used and mostly add HTML bloat. Removing them has no impact on functionality in modern setups. Reduces page size and removes unnecessary attributes that might be exploited to fingerprint or track the site.', 'wpshadow' ),
						'description_wizard' => __( 'Menu item IDs serve little purpose in modern WordPress. Remove them to reduce HTML size and improve privacy slightly.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'clean_body_classes'   => array(
						'name'               => __( 'Clean Body Tag Classes', 'wpshadow' ),
						'description_short'  => __( 'Remove extra classes from body element', 'wpshadow' ),
						'description_long'   => __( 'Removes unnecessary CSS classes from the body tag that WordPress adds automatically. WordPress adds many classes for page type detection, browser detection, and site state. Modern sites often don\'t need all of these since media queries and feature detection have replaced many use cases. Reduces the body tag size while keeping commonly-used classes.', 'wpshadow' ),
						'description_wizard' => __( 'The body tag gets many auto-generated classes most sites don\'t need. This keeps only the useful ones while removing bloat.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'remove_block_classes' => array(
						'name'               => __( 'Remove Block Editor Classes', 'wpshadow' ),
						'description_short'  => __( 'Remove Gutenberg-specific body classes', 'wpshadow' ),
						'description_long'   => __( 'Removes CSS classes that WordPress adds to the body tag specifically for the block editor (Gutenberg) presence. These classes are used for JavaScript selectors and editor-specific styling in WordPress 5.0+. If you don\'t need to style differently when the block editor is available, these classes are unnecessary bloat. Removes classes like wp-has-current-submenu.', 'wpshadow' ),
						'description_wizard' => __( 'Remove block editor-specific classes from the body tag if you don\'t need them for styling or JavaScript.', 'wpshadow' ),
						'default_enabled'    => true,
					),
				),
			)
		);

		$this->register_default_settings(
			array(
				'clean_post_classes'   => true,
				'clean_nav_classes'    => true,
				'remove_nav_ids'       => true,
				'clean_body_classes'   => true,
				'remove_block_classes' => true,
			)
		);

		$this->log_activity( 'feature_initialized', 'CSS Class Cleanup feature initialized', 'info' );
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( $this->is_sub_feature_enabled( 'clean_post_classes', true ) ) {
			add_filter( 'post_class', array( $this, 'simplify_post_classes' ), 10, 3 );
		}

		if ( $this->is_sub_feature_enabled( 'clean_nav_classes', true ) ) {
			add_filter( 'nav_menu_css_class', array( $this, 'simplify_nav_classes' ), 10, 4 );
		}

		if ( $this->is_sub_feature_enabled( 'remove_nav_ids', true ) ) {
			add_filter( 'nav_menu_item_id', '__return_false' );
		}

		if ( $this->is_sub_feature_enabled( 'clean_body_classes', true ) ) {
			add_filter( 'body_class', array( $this, 'simplify_body_classes' ) );
		}

		if ( $this->is_sub_feature_enabled( 'remove_block_classes', true ) ) {
			add_filter( 'body_class', array( $this, 'remove_block_body_classes' ) );
		}

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'wpshadow css-class-cleanup', array( $this, 'handle_cli_command' ) );
		}
	}

	public function simplify_post_classes( array $classes, $class, int $post_id ): array {

		$essential = array( 'post', 'entry', 'hentry' );
		$keep      = array();

		foreach ( $classes as $css_class ) {

			if ( in_array( $css_class, $essential, true ) ) {
				$keep[] = $css_class;
				continue;
			}

			if ( str_starts_with( $css_class, 'type-' ) || str_starts_with( $css_class, 'format-' ) ) {
				$keep[] = $css_class;
				continue;
			}

			if ( in_array( $css_class, array( 'sticky', 'has-post-thumbnail' ), true ) ) {
				$keep[] = $css_class;
			}
		}

		$filtered = array_unique( $keep );
		do_action( 'wpshadow_css_class_cleanup_post', $post_id, $filtered );
		return $filtered;
	}

	public function simplify_nav_classes( array $classes, $item, $args, int $depth ): array {
		$keep = array();

		if ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current_page_item', $classes, true ) ) {
			$keep[] = 'current';
		}

		if ( in_array( 'menu-item-has-children', $classes, true ) ) {
			$keep[] = 'has-children';
		}

		if ( in_array( 'current-menu-ancestor', $classes, true ) ) {
			$keep[] = 'ancestor';
		}

		$keep[] = 'menu-item';

		$filtered = array_unique( $keep );
		do_action( 'wpshadow_css_class_cleanup_nav', $item->ID ?? 0, $filtered );
		return $filtered;
	}

	public function simplify_body_classes( array $classes ): array {
		$keep = array();

		foreach ( $classes as $css_class ) {

			if ( in_array(
				$css_class,
				array(
					'home',
					'blog',
					'archive',
					'single',
					'page',
					'search',
					'error404',
					'logged-in',
					'admin-bar',
				),
				true
			) ) {
				$keep[] = $css_class;
				continue;
			}

			if ( str_starts_with( $css_class, 'post-type-' ) || str_starts_with( $css_class, 'page-template-' ) ) {
				$keep[] = $css_class;
			}
		}

		$filtered = array_unique( $keep );
		do_action( 'wpshadow_css_class_cleanup_body', $filtered );
		return $filtered;
	}

	public function remove_block_body_classes( array $classes ): array {
		return array_filter(
			$classes,
			static function ( $class ) {
				return ! str_starts_with( $class, 'wp-' ) && ! str_starts_with( $class, 'block-' );
			}
		);
	}

	public function handle_cli_command( array $args, array $assoc_args ): void {
		$action = $args[0] ?? 'status';

		if ( 'status' !== $action ) {
			\WP_CLI::error( __( 'Unknown subcommand. Try: wp wpshadow css-class-cleanup status', 'wpshadow' ) );
			return;
		}

		\WP_CLI::log( __( 'CSS Class Cleanup status:', 'wpshadow' ) );
		\WP_CLI::log( sprintf( '  %s: %s', __( 'Feature enabled', 'wpshadow' ), $this->is_enabled() ? 'yes' : 'no' ) );

		$subs = array(
			'clean_post_classes',
			'clean_nav_classes',
			'remove_nav_ids',
			'clean_body_classes',
			'remove_block_classes',
		);

		foreach ( $subs as $sub ) {
			$enabled = $this->is_sub_feature_enabled( $sub, false );
			\WP_CLI::log( sprintf( '  - %s: %s', $sub, $enabled ? 'on' : 'off' ) );
		}

		\WP_CLI::success( __( 'CSS class cleanup inspected.', 'wpshadow' ) );
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['css_class_cleanup'] = array(
			'label' => __( 'CSS Class Cleanup', 'wpshadow' ),
			'test'  => array( $this, 'test_css_class_cleanup' ),
		);

		return $tests;
	}

	public function test_css_class_cleanup(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'CSS Class Cleanup', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'CSS class cleanup is disabled.', 'wpshadow' ),
				'test'        => 'css_class_cleanup',
			);
		}

		$enabled_features = 0;

		if ( $this->is_sub_feature_enabled( 'clean_post_classes', true ) ) {
			$enabled_features++;
		}
		if ( $this->is_sub_feature_enabled( 'clean_nav_classes', true ) ) {
			$enabled_features++;
		}
		if ( $this->is_sub_feature_enabled( 'remove_nav_ids', true ) ) {
			$enabled_features++;
		}
		if ( $this->is_sub_feature_enabled( 'clean_body_classes', true ) ) {
			$enabled_features++;
		}
		if ( $this->is_sub_feature_enabled( 'remove_block_classes', true ) ) {
			$enabled_features++;
		}

		$status = $enabled_features >= 3 ? 'good' : 'recommended';

		return array(
			'label'       => __( 'CSS class cleanup is active', 'wpshadow' ),
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				__( '%d CSS class cleanup features are enabled, reducing HTML bloat.', 'wpshadow' ),
				(int) $enabled_features
			),
			'test'        => 'css_class_cleanup',
		);
	}
}
