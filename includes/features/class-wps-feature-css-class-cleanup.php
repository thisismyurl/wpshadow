<?php
/**
 * Feature: CSS Class Cleanup (Post/Nav/Body)
 *
 * Strip excessive WordPress-generated CSS classes from posts, navigation
 * menus, and body tags to reduce HTML bloat.
 *
 * @package WPShadow\Features
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
				'id'                 => 'css-class-cleanup',
				'name'               => __( 'CSS Class Cleanup (Post/Nav/Body)', 'plugin-wpshadow' ),
				'description'        => __( 'Removes redundant or noisy CSS classes from post, navigation, and body markup to produce cleaner HTML that is easier to style and slightly lighter to deliver. Reduces unexpected styling conflicts from excessive class names, improves readability for audits, and can trim a few bytes from every page. Works automatically with sensible defaults and keeps important classes required by themes and accessibility features.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'cleanup',
				'widget_label'       => __( 'Code Cleanup', 'plugin-wpshadow' ),
				'widget_description' => __( 'Remove unnecessary code artifacts and optimize output', 'plugin-wpshadow' ),
			)
		);
		
		if ( method_exists( $this, 'register_sub_features' ) ) {
			$this->register_sub_features(
				array(
					'clean_post_classes'   => __( 'Clean Post Classes', 'plugin-wpshadow' ),
					'clean_nav_classes'    => __( 'Clean Navigation Classes', 'plugin-wpshadow' ),
					'remove_nav_ids'       => __( 'Remove Navigation IDs', 'plugin-wpshadow' ),
					'clean_body_classes'   => __( 'Clean Body Classes', 'plugin-wpshadow' ),
					'remove_block_classes' => __( 'Remove Block-Related Classes', 'plugin-wpshadow' ),
				)
			);
			if ( method_exists( $this, 'set_default_sub_features' ) ) {
				$this->set_default_sub_features(
					array(
						'clean_post_classes'   => true,
						'clean_nav_classes'    => true,
						'remove_nav_ids'       => true,
						'clean_body_classes'   => true,
						'remove_block_classes' => true,
					)
				);
			}
		}
		
		$this->log_activity( 'feature_initialized', 'CSS Class Cleanup feature initialized', 'info' );
	}

	/**
	 * Indicate this feature has a details page.
	 *
	 * @return bool
	 */
	public function has_details_page(): bool {
		return true;
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

		if ( get_option( 'wpshadow_css-class-cleanup_clean_post_classes', true ) ) {
			add_filter( 'post_class', array( $this, 'clean_post_classes' ), 999 );
		}
		
		if ( get_option( 'wpshadow_css-class-cleanup_clean_nav_classes', true ) ) {
			add_filter( 'nav_menu_css_class', array( $this, 'cleanup_nav_classes' ), 10 );
		}
		
		if ( get_option( 'wpshadow_css-class-cleanup_remove_nav_ids', true ) ) {
			add_filter( 'nav_menu_item_id', '__return_false' );
		}
		
		if ( get_option( 'wpshadow_css-class-cleanup_clean_body_classes', true ) ) {
			add_filter( 'body_class', array( $this, 'remove_block_body_classes' ) );
		}
		
		// Add Site Health tests.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Clean up post classes.
	 *
	 * @param array $classes Array of post classes.
	 * @return array Filtered classes.
	 */
	public function clean_post_classes( array $classes ): array {
		$keep_classes = (array) $this->get_setting( 'wpshadow_post_class_whitelist', array( 'has-post-thumbnail', 'post', 'hentry'  ) );
		return array_intersect( $classes, $keep_classes );
	}

	/**
	 * Clean up navigation menu classes.
	 *
	 * @param array $classes Array of nav classes.
	 * @return array Filtered classes.
	 */
	public function cleanup_nav_classes( array $classes ): array {
		$keep = array( 'current-menu-item', 'menu-item-has-children', 'current-menu-ancestor' );
		return array_intersect( $classes, $keep );
	}

	/**
	 * Remove block-related classes from body tag.
	 *
	 * @param array $classes Array of body classes.
	 * @return array Filtered classes.
	 */
	public function remove_block_body_classes( array $classes ): array {
		return array_filter(
			$classes,
			static function ( $class ) {
				return strpos( $class, 'wp-block-' ) === false;
			}
		);
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array $tests Array of Site Health tests.
	 * @return array Modified tests array.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['css_class_cleanup'] = array(
			'label' => __( 'CSS Class Cleanup', 'plugin-wpshadow' ),
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
		$enabled_features = 0;
		
		if ( get_option( 'wpshadow_css-class-cleanup_clean_post_classes', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_css-class-cleanup_clean_nav_classes', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_css-class-cleanup_remove_nav_ids', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_css-class-cleanup_clean_body_classes', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_css-class-cleanup_remove_block_classes', true ) ) {
			$enabled_features++;
		}

		$status = $enabled_features >= 3 ? 'good' : 'recommended';
		$label  = $enabled_features >= 3 ?
			__( 'CSS class cleanup is active', 'plugin-wpshadow' ) :
			__( 'CSS class cleanup could be improved', 'plugin-wpshadow' );

		return array(
			'label'       => $label,
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Performance', 'plugin-wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %d: number of enabled cleanup features */
					__( '%d CSS class cleanup features are enabled, reducing HTML bloat.', 'plugin-wpshadow' ),
					$enabled_features
				)
			),
			'actions'     => '',
			'test'        => 'css_class_cleanup',
		);
	}
}
