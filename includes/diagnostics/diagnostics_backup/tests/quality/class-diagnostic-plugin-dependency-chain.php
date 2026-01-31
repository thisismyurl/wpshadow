<?php
/**
 * Plugin Dependency Chain Diagnostic
 *
 * Identifies plugins with dependencies on other plugins
 * and verifies dependencies are installed.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_Dependency_Chain Class
 *
 * Verifies plugin dependencies.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Plugin_Dependency_Chain extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-dependency-chain';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Dependency Chain';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies plugin dependencies';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if dependencies missing, null otherwise.
	 */
	public static function check() {
		$dependency_status = self::check_plugin_dependencies();

		if ( ! $dependency_status['has_issue'] ) {
			return null; // All dependencies satisfied
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: dependent plugin */
				__( '%s requires another plugin that\'s not active. Missing dependency = feature broken = customer frustration. Activate required plugins or disable dependent plugin.', 'wpshadow' ),
				$dependency_status['plugin_name']
			),
			'severity'     => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/plugin-dependencies',
			'family'       => self::$family,
			'meta'         => array(
				'unmet_dependencies' => true,
			),
			'details'      => array(
				'common_plugin_dependencies'      => array(
					'WooCommerce Extensions' => array(
						'Example: WooCommerce Subscriptions',
						'Requires: WooCommerce active',
						'If missing: Plugin may disable',
					),
					'Premium Add-Ons' => array(
						'Example: ACF Pro',
						'Requires: ACF (Free) sometimes',
						'If missing: Features don\'t work',
					),
					'Builder Plugins' => array(
						'Example: Elementor Pro',
						'Requires: Elementor Free',
						'If missing: Pro features fail',
					),
				),
				'finding_dependencies'            => array(
					'Plugin Header' => array(
						'Location: Plugin file top',
						'Look for: "Requires Plugins"',
						'Format: Comma-separated slugs',
					),
					'Plugin Documentation' => array(
						'Check: Plugin website',
						'Section: Requirements',
						'Or: README.txt file',
					),
					'Plugin Admin Page' => array(
						'wp-admin: Plugins list',
						'Notice: "Requires..."',
						'Shows: Dependencies when missing',
					),
				),
				'handling_dependencies'           => array(
					'Install Required Plugin' => array(
						'Check: wp.org plugins page',
						'Or: From plugin developer',
						'Install: Upload & activate',
					),
					'Verify' => array(
						'Check: Dependent plugin works',
						'Test: Features enabled',
					),
					'If Unavailable' => array(
						'Disable: Dependent plugin',
						'Find: Alternative plugin',
						'Or: Update dependent plugin (may have fix)',
					),
				),
				'plugin_chain_risk'               => array(
					'Critical Chains' => array(
						'Example: A requires B, B requires C',
						'If C disabled: Both A and B fail',
						'Risk: One plugin breaks many',
					),
					'Update Risk' => array(
						'Dependency updated: May break',
						'Example: B updated, breaks A',
						'Plan: Test dependencies together',
					),
				),
				'managing_dependencies'           => array(
					__( '1. Map all plugin dependencies' ),
					__( '2. Verify all required plugins active' ),
					__( '3. Test plugins together' ),
					__( '4. Keep dependencies updated' ),
					__( '5. Plan removal: Check dependencies first' ),
				),
			),
		);
	}

	/**
	 * Check plugin dependencies.
	 *
	 * @since  1.2601.2148
	 * @return array Dependency status.
	 */
	private static function check_plugin_dependencies() {
		$has_issue = false;
		$plugin_name = '';

		// Known plugin dependencies
		$dependencies = array(
			'woocommerce' => array(
				'woocommerce-subscriptions/woocommerce-subscriptions.php',
				'woocommerce-bookings/woocommerce-bookings.php',
			),
			'elementor/elementor.php' => array(
				'elementor-pro/elementor-pro.php',
			),
		);

		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $dependencies as $required => $dependents ) {
			foreach ( $dependents as $dependent ) {
				if ( in_array( $dependent, $active_plugins, true ) && ! in_array( $required, $active_plugins, true ) ) {
					$has_issue = true;
					$plugin_name = basename( $dependent, '.php' );
					break;
				}
			}

			if ( $has_issue ) {
				break;
			}
		}

		return array(
			'has_issue'   => $has_issue,
			'plugin_name' => $plugin_name,
		);
	}
}
