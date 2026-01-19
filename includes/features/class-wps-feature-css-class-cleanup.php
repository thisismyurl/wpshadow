<?php
/**
 * Feature: CSS Class Cleanup (Post/Nav/Body)
 *
 * Strip excessive WordPress-generated CSS classes from posts, navigation
 * menus, and body tags to reduce HTML bloat.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_CSS_Class_Cleanup
 *
 * Filters CSS classes for cleaner HTML.
 */
final class WPSHADOW_Feature_CSS_Class_Cleanup extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
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
					'clean_post_classes'   => __( 'Remove extra post labels', 'wpshadow' ),
					'clean_nav_classes'    => __( 'Remove extra menu labels', 'wpshadow' ),
					'remove_nav_ids'       => __( 'Remove menu tracking codes', 'wpshadow' ),
					'clean_body_classes'   => __( 'Remove extra page labels', 'wpshadow' ),
					'remove_block_classes' => __( 'Remove block editor labels', 'wpshadow' ),
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

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
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

	/**
	 * Simplify post classes.
	 *
	 * @param array  $classes CSS classes.
	 * @param string $class Additional class.
	 * @param int    $post_id Post ID.
	 * @return array Modified classes.
	 */
	public function simplify_post_classes( array $classes, $class, int $post_id ): array {
		// Keep only essential classes.
		$essential = array( 'post', 'entry', 'hentry' );
		$keep      = array();

		foreach ( $classes as $css_class ) {
			// Keep essential classes.
			if ( in_array( $css_class, $essential, true ) ) {
				$keep[] = $css_class;
				continue;
			}

			// Keep post type and format.
			if ( str_starts_with( $css_class, 'type-' ) || str_starts_with( $css_class, 'format-' ) ) {
				$keep[] = $css_class;
				continue;
			}

			// Keep status classes.
			if ( in_array( $css_class, array( 'sticky', 'has-post-thumbnail' ), true ) ) {
				$keep[] = $css_class;
			}
		}

		$filtered = array_unique( $keep );
		do_action( 'wpshadow_css_class_cleanup_post', $post_id, $filtered );
		return $filtered;
	}

	/**
	 * Simplify navigation menu classes.
	 *
	 * @param array    $classes CSS classes.
	 * @param WP_Post  $item Menu item.
	 * @param stdClass $args Menu args.
	 * @param int      $depth Menu depth.
	 * @return array Modified classes.
	 */
	public function simplify_nav_classes( array $classes, $item, $args, int $depth ): array {
		$keep = array();

		// Keep current item indicator.
		if ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current_page_item', $classes, true ) ) {
			$keep[] = 'current';
		}

		// Keep parent indicator.
		if ( in_array( 'menu-item-has-children', $classes, true ) ) {
			$keep[] = 'has-children';
		}

		// Keep ancestor indicator.
		if ( in_array( 'current-menu-ancestor', $classes, true ) ) {
			$keep[] = 'ancestor';
		}

		// Add basic menu-item class.
		$keep[] = 'menu-item';

		$filtered = array_unique( $keep );
		do_action( 'wpshadow_css_class_cleanup_nav', $item->ID ?? 0, $filtered );
		return $filtered;
	}

	/**
	 * Simplify body classes.
	 *
	 * @param array $classes CSS classes.
	 * @return array Modified classes.
	 */
	public function simplify_body_classes( array $classes ): array {
		$keep = array();

		foreach ( $classes as $css_class ) {
			// Keep essential page type classes.
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

			// Keep post type.
			if ( str_starts_with( $css_class, 'post-type-' ) || str_starts_with( $css_class, 'page-template-' ) ) {
				$keep[] = $css_class;
			}
		}

		$filtered = array_unique( $keep );
		do_action( 'wpshadow_css_class_cleanup_body', $filtered );
		return $filtered;
	}

	/**
	 * Remove block-related body classes.
	 *
	 * @param array $classes CSS classes.
	 * @return array Modified classes.
	 */
	public function remove_block_body_classes( array $classes ): array {
		return array_filter(
			$classes,
			static function ( $class ) {
				return ! str_starts_with( $class, 'wp-' ) && ! str_starts_with( $class, 'block-' );
			}
		);
	}

	/**
	 * Handle WP-CLI command for CSS class cleanup.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args (unused).
	 *
	 * @return void
	 */
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

	/**
	 * Register Site Health test.
	 *
	 * @param array $tests Array of Site Health tests.
	 * @return array Modified tests array.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['css_class_cleanup'] = array(
			'label' => __( 'CSS Class Cleanup', 'wpshadow' ),
			'test'  => array( $this, 'test_css_class_cleanup' ),
		);

		return $tests;
	}

	/**
	 * Site Health test for CSS class cleanup.
	 *
	 * @return array Test result.
	 */
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
