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

		add_filter( 'post_class', array( $this, 'clean_post_classes' ), 999 );
		add_filter( 'nav_menu_css_class', array( $this, 'cleanup_nav_classes' ), 10 );
		add_filter( 'nav_menu_item_id', '__return_false' );
		add_filter( 'body_class', array( $this, 'remove_block_body_classes' ) );
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
}
