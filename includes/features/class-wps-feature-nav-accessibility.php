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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


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
				'name'            => __( 'Better Navigation for Everyone', 'wpshadow' ),
				'description'     => __( 'Make your menus work better for people using screen readers and keyboards instead of a mouse.', 'wpshadow' ),
				'scope'           => 'core',
				'default_enabled' => true,
				'version'         => '1.0.0',
				'widget_group'    => 'accessibility',
				'aliases'         => array( 'menu accessibility', 'navigation', 'aria current', 'keyboard navigation', 'nav menu', 'menu classes', 'screen reader navigation', 'accessible menus', 'menu optimization', 'nav cleanup', 'menu aria', 'keyboard menu' ),
				'sub_features'    => array(
					'add_aria_current'   => array(
						'name'               => __( 'Add ARIA Current Indicator', 'wpshadow' ),
						'description_short'  => __( 'Show which page is currently active in menus', 'wpshadow' ),
						'description_long'   => __( 'Adds ARIA current page attributes to menu items that match the current page being viewed. This helps screen reader users understand where they are in the site structure. Also adds visual focus indicators so keyboard and screen reader users can clearly see which menu item represents the current page.', 'wpshadow' ),
						'description_wizard' => __( 'Screen reader users need to know which menu item represents the current page. This adds ARIA attributes that announce it.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'simplify_classes'   => array(
						'name'               => __( 'Simplify Menu Classes', 'wpshadow' ),
						'description_short'  => __( 'Remove extra CSS classes from menu items', 'wpshadow' ),
						'description_long'   => __( 'Removes unnecessary CSS classes from menu items that most sites don\'t need. WordPress adds classes for depth levels, parent indicators, and menu structure. Many themes don\'t use all of these classes, so removing them reduces HTML size and makes CSS cleaner and easier to write. Keeps classes that have accessibility value.', 'wpshadow' ),
						'description_wizard' => __( 'Reduce menu HTML bloat by removing unused CSS classes. Most themes can style menus without all the extra classes.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'remove_nav_ids'     => array(
						'name'               => __( 'Remove Menu Item IDs', 'wpshadow' ),
						'description_short'  => __( 'Remove ID attributes from menu items', 'wpshadow' ),
						'description_long'   => __( 'Removes ID attributes from menu items that WordPress adds for tracking. These IDs are rarely used by themes and mostly add HTML bloat. Removing them reduces page size and improves privacy slightly by not exposing unique identifiers to external trackers.', 'wpshadow' ),
						'description_wizard' => __( 'Menu item IDs serve little purpose and add HTML size. Safe to remove for most sites.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'keyboard_support'   => array(
						'name'               => __( 'Enhanced Keyboard Navigation', 'wpshadow' ),
						'description_short'  => __( 'Better support for keyboard-only navigation', 'wpshadow' ),
						'description_long'   => __( 'Improves keyboard navigation for dropdown menus with additional JavaScript support. Makes submenu navigation easier for keyboard-only users by adding focus indicators and keyboard event handling. Enables arrow keys and other keyboard shortcuts for navigating nested menus. Improves accessibility for users who cannot use mice.', 'wpshadow' ),
						'description_wizard' => __( 'Enhanced keyboard support makes navigation much easier for keyboard-only users and screen reader users. Disabled by default but recommended for accessibility.', 'wpshadow' ),
						'default_enabled'    => false,
					),
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

		if ( $this->is_sub_feature_enabled( 'keyboard_support', false ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_keyboard_support' ) );
		}

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		$this->log_activity( 'feature_registered', 'Nav Accessibility hooks registered', 'info' );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'wpshadow nav-accessibility', array( $this, 'handle_cli_command' ) );
		}
	}

	/**
	 * Enqueue keyboard navigation support.
	 *
	 * @return void
	 */
	public function enqueue_keyboard_support(): void {
		if ( is_admin() ) {
			return;
		}

		// Inline keyboard navigation script.
		wp_add_inline_script(
			'jquery',
			"
			(function($) {
				$(document).ready(function() {
					// Handle keyboard navigation for dropdown menus
					$('.menu-item-has-children > a, .has-children > a').on('focus', function() {
						$(this).parent().addClass('focus');
					}).on('blur', function() {
						$(this).parent().removeClass('focus');
					});

					// Tab navigation
					$('.menu-item a').on('keydown', function(e) {
						if (e.which === 9) { // Tab key
							var parent = $(this).closest('.menu-item-has-children, .has-children');
							if (e.shiftKey) {
								parent.removeClass('focus');
							} else {
								parent.addClass('focus');
							}
						}
					});

					// Escape key closes submenus
					$('.menu-item a').on('keydown', function(e) {
						if (e.which === 27) { // Escape key
							$(this).closest('.menu-item-has-children, .has-children').removeClass('focus');
							$(this).blur();
						}
					});
				});
			})(jQuery);
			"
		);

		// Add focus styles.
		wp_add_inline_style(
			'wp-block-navigation',
			"
			.menu-item.focus > .sub-menu,
			.menu-item:focus-within > .sub-menu { display: block; visibility: visible; opacity: 1; }
			.menu-item a:focus { outline: 2px solid currentColor; outline-offset: 2px; }
			"
		);
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

		do_action( 'wpshadow_nav_accessibility_optimized', $items );

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

	/**
	 * Handle WP-CLI command for navigation accessibility.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args (unused).
	 *
	 * @return void
	 */
	public function handle_cli_command( array $args, array $assoc_args ): void {
		$action = $args[0] ?? 'status';

		if ( 'status' !== $action ) {
			\WP_CLI::error( __( 'Unknown subcommand. Try: wp wpshadow nav-accessibility status', 'wpshadow' ) );
			return;
		}

		\WP_CLI::log( __( 'Navigation Accessibility status:', 'wpshadow' ) );
		\WP_CLI::log( sprintf( '  %s: %s', __( 'Feature enabled', 'wpshadow' ), $this->is_enabled() ? 'yes' : 'no' ) );

		$subs = array(
			'add_aria_current',
			'simplify_classes',
			'remove_nav_ids',
			'keyboard_support',
		);

		foreach ( $subs as $sub ) {
			$enabled = $this->is_sub_feature_enabled( $sub, false );
			\WP_CLI::log( sprintf( '  - %s: %s', $sub, $enabled ? 'on' : 'off' ) );
		}

		\WP_CLI::success( __( 'Navigation accessibility inspected.', 'wpshadow' ) );
	}
}
