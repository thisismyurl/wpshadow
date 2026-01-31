<?php
/**
 * Plugin Activation Order Issues Diagnostic
 *
 * Detects plugin loading order issues and dependencies.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Activation Order Issues Diagnostic
 *
 * Identifies plugin load order problems and unmet dependencies.
 *
 * @since 1.2601.2240
 */
class Diagnostic_Plugin_Activation_Order_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-activation-order-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Activation Order Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects plugin loading order issues and dependencies';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_filter;

		$issues      = array();
		$order_issues = array();

		// Plugin dependencies map
		$dependencies = array(
			'woocommerce/woocommerce.php' => array(
				'woocommerce-gateway-stripe/woocommerce-gateway-stripe.php',
				'woo-gutenberg-products-block/woo-gutenberg-products-block.php',
				'woocommerce-services/woocommerce-services.php',
			),
			'jetpack/jetpack.php' => array(
				'jetpack-search/jetpack-search.php',
				'jetpack-protect/jetpack-protect.php',
			),
			'buddypress/bp-loader.php' => array(
				'bp-activity-stream/bp-activity-stream.php',
				'bp-profile-search/bp-profile-search.php',
			),
		);

		$active_plugins = get_option( 'active_plugins', array() );

		// Check if dependencies are activated before dependents
		foreach ( $dependencies as $parent => $children ) {
			$parent_index = array_search( $parent, $active_plugins, true );

			if ( $parent_index === false ) {
				continue; // Parent not active
			}

			foreach ( $children as $child ) {
				$child_index = array_search( $child, $active_plugins, true );

				if ( $child_index !== false && $child_index < $parent_index ) {
					$order_issues[] = sprintf(
						/* translators: 1: child plugin, 2: parent plugin */
						__( '%1$s loaded before its dependency %2$s', 'wpshadow' ),
						basename( dirname( $child ) ),
						basename( dirname( $parent ) )
					);
				}
			}
		}

		// Check for common plugin load order issues
		$common_order = array( 'wpforms-lite/wpforms.php', 'contact-form-7/wp-contact-form-7.php', 'woocommerce/woocommerce.php' );
		$active_order = array();

		foreach ( $common_order as $plugin ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$active_order[] = $plugin;
			}
		}

		// Multiple plugin loaders
		$loaders = array(
			'mu-plugins-loader/mu-plugins-loader.php' => 'MU Plugins Loader',
			'plugin-organizer/plugin-organizer.php'   => 'Plugin Organizer',
		);

		$loader_count = 0;
		foreach ( $loaders as $plugin => $name ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				++$loader_count;
			}
		}

		if ( $loader_count > 1 ) {
			$issues[] = __( 'Multiple plugin loading managers detected - may conflict', 'wpshadow' );
		}

		// Check for init hook overrides
		$init_hooks = 0;
		if ( isset( $wp_filter['init'] ) && ! empty( $wp_filter['init']->callbacks ) ) {
			$init_hooks = count( $wp_filter['init']->callbacks );
		}

		if ( $init_hooks > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number of init hooks */
				__( '%d init hooks registered - potential load order issues', 'wpshadow' ),
				$init_hooks
			);
		}

		// Check for plugins missing from active list but loaded
		if ( ! function_exists( 'get_mu_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$mu_plugins = get_mu_plugins();
		if ( ! empty( $mu_plugins ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of MU plugins */
				__( '%d must-use plugins are active - ensure load order is correct', 'wpshadow' ),
				count( $mu_plugins )
			);
		}

		// Report findings
		if ( ! empty( $order_issues ) || ! empty( $issues ) ) {
			$severity     = 'low';
			$threat_level = 35;

			if ( ! empty( $order_issues ) || $init_hooks > 50 ) {
				$severity     = 'medium';
				$threat_level = 60;
			}

			$description = __( 'Plugin activation order issues detected', 'wpshadow' );

			$details = array(
				'active_plugin_count' => count( $active_plugins ),
			);

			if ( ! empty( $order_issues ) ) {
				$details['order_issues'] = $order_issues;
			}
			if ( ! empty( $issues ) ) {
				$details['issues'] = $issues;
			}
			$details['init_hook_count'] = $init_hooks;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugin-activation-order-issues',
				'details'      => $details,
			);
		}

		return null;
	}
}
