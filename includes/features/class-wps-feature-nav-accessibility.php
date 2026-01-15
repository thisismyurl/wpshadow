<?php
/**
 * Feature: Navigation Menu Accessibility & Class Simplification
 *
 * Enhance WordPress navigation menus by adding proper ARIA attributes
 * while simultaneously cleaning up excessive classes for leaner HTML.
 *
 * @package WPS\CoreSupport\Features
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

/**
 * WPSHADOW_Feature_Nav_Accessibility
 *
 * Improves navigation accessibility and reduces HTML bloat.
 */
final class WPSHADOW_Feature_Nav_Accessibility extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'nav-accessibility',
				'name'               => __( 'Navigation Menu Accessibility & Class Simplification', 'plugin-wpshadow' ),
				'description'        => __( 'Make your navigation easier for everyone to use, including people with disabilities', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'accessibility',
				'widget_label'       => __( 'Accessibility', 'plugin-wpshadow' ),
				'widget_description' => __( 'Improve site accessibility for all users', 'plugin-wpshadow' ),
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

		add_filter( 'wp_nav_menu_objects', array( $this, 'optimize_menu_output' ), 10, 2 );
		add_filter( 'nav_menu_item_id', '__return_false' );
	}

	/**
	 * Optimize menu output with ARIA attributes and simplified classes.
	 *
	 * @param array    $items Array of menu items.
	 * @param stdClass $args  Menu arguments.
	 * @return array Modified menu items.
	 */
	public function optimize_menu_output( array $items, $args ): array {
		$options = (array) $this->get_setting( 'wpshadow_nav_accessibility_options', $this->get_default_options( ) );

		foreach ( $items as $item ) {
			// Add ARIA attributes.
			if ( $options['add_aria_current'] ?? false ) {
				if ( $item->current ) {
					$item->attributes['aria-current'] = 'page';
				}
			}

			// Simplify classes.
			if ( $options['simplify_classes'] ?? false ) {
				$active_class  = $item->current ? 'is-active' : '';
				$has_children  = in_array( 'menu-item-has-children', $item->classes, true ) ? 'has-children' : '';
				$item->classes = array_filter( array( 'nav-item', $active_class, $has_children ) );
			}
		}

		return $items;
	}

	/**
	 * Get default options.
	 *
	 * @return array Default options.
	 */
	protected function get_default_options(): array {
		return array(
			'add_aria_current' => true,
			'simplify_classes' => true,
		);
	}
}
