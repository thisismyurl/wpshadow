<?php
/**
 * Feature: Navigation Menu Accessibility & Class Simplification
 *
 * Enhance WordPress navigation menus by adding proper ARIA attributes
 * while simultaneously cleaning up excessive classes for leaner HTML.
 *
 * @package WPShadow\CoreSupport
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
				'id'              => 'nav-accessibility',
				'name'            => __( 'Navigation Menu Accessibility & Class Simplification', 'wpshadow' ),
				'description'     => __( 'Make your menus accessible - add screen reader support and keyboard navigation.', 'wpshadow' ),
				'scope'           => 'core',
				'default_enabled' => true,
				'version'         => '1.0.0',
				'widget_group'    => 'accessibility',
				'sub_features'    => array(
					'add_aria_current'   => __( 'Add ARIA Current Attribute', 'wpshadow' ),
					'simplify_classes'   => __( 'Simplify Menu Classes', 'wpshadow' ),
					'remove_nav_ids'     => __( 'Remove Navigation IDs', 'wpshadow' ),
					'keyboard_support'   => __( 'Enhanced Keyboard Support', 'wpshadow' ),
				),
			)
		);

		$this->register_default_settings(
			array(
				'add_aria_current'   => true,
				'simplify_classes'   => true,
				'remove_nav_ids'     => true,
				'keyboard_support'   => false,
			)
		);

		$this->log_activity( 'feature_initialized', 'Nav Accessibility feature initialized', 'info' );
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

		if ( $this->is_sub_feature_enabled( 'add_aria_current', true ) || $this->is_sub_feature_enabled( 'simplify_classes', true ) ) {
			add_filter( 'wp_nav_menu_objects', array( $this, 'optimize_menu_output' ), 10, 2 );
		}

		if ( $this->is_sub_feature_enabled( 'remove_nav_ids', true ) ) {
			add_filter( 'nav_menu_item_id', '__return_false' );
		}

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		$this->log_activity( 'feature_registered', 'Nav Accessibility hooks registered', 'info' );
	}

	/**
	 * Optimize menu output with ARIA attributes and simplified classes.
	 *
	 * @param array    $items Array of menu items.
	 * @param stdClass $args  Menu arguments.
	 * @return array Modified menu items.
	 */
	public function optimize_menu_output( array $items, $args ): array {
		foreach ( $items as $item ) {
			// Add ARIA attributes.
			if ( $this->is_sub_feature_enabled( 'add_aria_current', true ) ) {
				if ( $item->current ) {
					$item->attributes['aria-current'] = 'page';
				}
			}

			// Simplify classes.
			if ( $this->is_sub_feature_enabled( 'simplify_classes', true ) ) {
				$active_class  = $item->current ? 'is-active' : '';
				$has_children  = in_array( 'menu-item-has-children', $item->classes, true ) ? 'has-children' : '';
				$item->classes = array_filter( array( 'nav-item', $active_class, $has_children ) );
			}
		}

		return $items;
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array $tests Array of Site Health tests.
	 * @return array Modified tests array.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['nav_accessibility'] = array(
			'label' => __( 'Navigation Accessibility', 'wpshadow' ),
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
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Navigation Accessibility', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Accessibility', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'Navigation accessibility feature is disabled.', 'wpshadow' ),
				'test'        => 'nav_accessibility',
			);
		}

		$enabled_features = 0;

		if ( $this->is_sub_feature_enabled( 'add_aria_current', true ) ) {
			$enabled_features++;
		}
		if ( $this->is_sub_feature_enabled( 'simplify_classes', true ) ) {
			$enabled_features++;
		}
		if ( $this->is_sub_feature_enabled( 'remove_nav_ids', true ) ) {
			$enabled_features++;
		}

		$status = $enabled_features >= 2 ? 'good' : 'recommended';
		$label  = $enabled_features >= 2 ?
			__( 'Navigation accessibility is enhanced', 'wpshadow' ) :
			__( 'Navigation accessibility could be improved', 'wpshadow' );

		return array(
			'label'       => $label,
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Accessibility', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %d: number of enabled accessibility features */
					__( '%d navigation accessibility features are enabled, improving menu usability for all users.', 'wpshadow' ),
					(int) $enabled_features
				)
			),
			'test'        => 'nav_accessibility',
		);
	}
}
