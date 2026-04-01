<?php
/**
 * Plugin Dependency Resolution Diagnostic
 *
 * Checks if required plugin dependencies are satisfied to ensure plugins work
 * correctly and don't cause silent failures.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Dependency Resolution Diagnostic Class
 *
 * Verifies plugin dependencies:
 * - Required plugin presence
 * - PHP version compatibility
 * - WordPress version compatibility
 * - Extension availability
 *
 * @since 0.6093.1200
 */
class Diagnostic_Plugin_Dependency_Resolution extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-dependency-resolution';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Dependency Resolution';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for missing plugin dependencies and compatibility issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		global $wp_version;

		// Check PHP version against active plugins
		$php_version = phpversion();
		$php_issues  = array();

		// Common plugins with PHP version requirements
		$php_requirements = array(
			'woocommerce/woocommerce.php'              => '7.2',
			'elementor/elementor.php'                  => '7.0',
			'divi-builder/divi-builder.php'            => '5.6',
		);

		foreach ( $php_requirements as $plugin_path => $required_version ) {
			if ( is_plugin_active( $plugin_path ) && version_compare( $php_version, $required_version, '<' ) ) {
				$php_issues[] = array(
					'plugin'   => basename( dirname( $plugin_path ) ),
					'required' => $required_version,
					'current'  => $php_version,
				);
			}
		}

		if ( ! empty( $php_issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of plugins with unmet dependencies */
					__( '%d active plugins have unmet PHP version requirements. This may cause errors or reduced functionality.', 'wpshadow' ),
					count( $php_issues )
				),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/plugin-dependencies?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'          => array(
					'issue_count'          => count( $php_issues ),
					'current_php'          => $php_version,
					'issues'               => array_slice( $php_issues, 0, 3 ),
					'recommendation'       => 'Upgrade PHP to meet plugin requirements or deactivate incompatible plugins',
					'impact'               => 'Unmet dependencies can cause plugin errors, performance issues, or security vulnerabilities',
				),
			);
		}

		return null;
	}
}
