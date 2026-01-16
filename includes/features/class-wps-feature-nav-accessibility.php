<?php
/**
 * Feature: Navigation Menu Accessibility & Class Simplification
 *
 * Enhance WordPress navigation menus by adding proper ARIA attributes
 * while simultaneously cleaning up excessive classes for leaner HTML.
 *
 * @package WPShadow\Features
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

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
				'description'        => __( 'Makes your navigation easier for everyone to use by adding proper ARIA labels, keyboard support, focus indicators, and screen-reader hints to menus and dropdowns. Ensures people using assistive technology can understand your site structure, navigate submenus, and know where they are, meeting accessibility standards while working with existing menu code and popular themes.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'accessibility',
			)
		);
		
		if ( method_exists( $this, 'register_sub_features' ) ) {
			$this->register_sub_features(
				array(
					'add_aria_current'   => __( 'Add ARIA Current Attribute', 'plugin-wpshadow' ),
					'simplify_classes'   => __( 'Simplify Menu Classes', 'plugin-wpshadow' ),
					'remove_nav_ids'     => __( 'Remove Navigation IDs', 'plugin-wpshadow' ),
					'keyboard_support'   => __( 'Enhanced Keyboard Support', 'plugin-wpshadow' ),
				)
			);
			if ( method_exists( $this, 'set_default_sub_features' ) ) {
				$this->set_default_sub_features(
					array(
						'add_aria_current'   => true,
						'simplify_classes'   => true,
						'remove_nav_ids'     => true,
						'keyboard_support'   => false,
					)
				);
			}
		}
		
		$this->log_activity( 'feature_initialized', 'Nav Accessibility feature initialized', 'info' );
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

		add_filter( 'wp_nav_menu_objects', array( $this, 'optimize_menu_output' ), 10, 2 );
		
		if ( get_option( 'wpshadow_nav-accessibility_remove_nav_ids', true ) ) {
			add_filter( 'nav_menu_item_id', '__return_false' );
		}
		
		// Add Site Health tests.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
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

	/**
	 * Register Site Health test.
	 *
	 * @param array $tests Array of Site Health tests.
	 * @return array Modified tests array.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['nav_accessibility'] = array(
			'label' => __( 'Navigation Accessibility', 'plugin-wpshadow' ),
			'test'  => array( $this, 'test_nav_accessibility' ),
		);

		return $tests;
	}

	/**
	 * Site Health test for navigation accessibility.
	 *
	 * @return array Test result.
	 */
	public function test_nav_accessibility(): array {
		$enabled_features = 0;

		if ( get_option( 'wpshadow_nav-accessibility_add_aria_current', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_nav-accessibility_simplify_classes', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_nav-accessibility_remove_nav_ids', true ) ) {
			$enabled_features++;
		}

		$status = $enabled_features >= 2 ? 'good' : 'recommended';
		$label  = $enabled_features >= 2 ?
			__( 'Navigation accessibility is enhanced', 'plugin-wpshadow' ) :
			__( 'Navigation accessibility could be improved', 'plugin-wpshadow' );

		return array(
			'label'       => $label,
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Accessibility', 'plugin-wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %d: number of enabled accessibility features */
					__( '%d navigation accessibility features are enabled, improving menu usability for all users.', 'plugin-wpshadow' ),
					$enabled_features
				)
			),
			'actions'     => '',
			'test'        => 'nav_accessibility',
		);
	}
}
