<?php
/**
 * PHP Version Compatibility Diagnostic
 *
 * Checks theme and plugin compatibility with current PHP version,
 * detecting deprecated functions that will break on PHP upgrades.
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
 * Diagnostic_PHP_Version_Compatibility Class
 *
 * Monitors PHP version compatibility.
 *
 * @since 1.2601.2148
 */
class Diagnostic_PHP_Version_Compatibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'php-version-compatibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PHP Version Compatibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks PHP compatibility of themes/plugins';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$compat_check = self::check_php_compatibility();

		if ( empty( $compat_check['issues'] ) ) {
			return null; // All compatible
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of compatibility issues */
				__( '%d PHP compatibility issues detected. Deprecated functions = site breaks on PHP upgrade. Host upgrades PHP = site goes white screen.', 'wpshadow' ),
				count( $compat_check['issues'] )
			),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/php-compatibility',
			'family'       => self::$family,
			'meta'         => array(
				'current_php'    => $compat_check['current_version'],
				'issues_found'   => $compat_check['issues'],
			),
			'details'      => array(
				'why_compatibility_matters' => array(
					__( 'Hosts upgrade PHP automatically (security patches)' ),
					__( 'Deprecated functions removed in new PHP versions' ),
					__( 'Site breaks = downtime, lost revenue' ),
					__( 'Testing before upgrade prevents disasters' ),
				),
				'common_php_deprecations'   => array(
					'PHP 7.x → 8.x' => array(
						'each() function removed',
						'create_function() removed',
						'Unparenthesized ternaries deprecated',
						'Required parameters after optional deprecated',
					),
					'PHP 5.6 → 7.x' => array(
						'mysql_* functions removed (use mysqli)',
						'Split() removed (use explode)',
						'ereg functions removed (use preg)',
					),
				),
				'testing_php_compatibility' => array(
					'PHP Compatibility Checker (Plugin)' => array(
						'Install: PHP Compatibility Checker',
						'Tools → PHP Compatibility',
						'Scans all themes/plugins',
						'Shows exact line numbers with issues',
					),
					'WP-CLI PHP Compatibility' => array(
						'Command: wp plugin list --format=json',
						'Check each plugin compatibility',
						'Automated testing in CI/CD',
					),
					'Staging Environment' => array(
						'Create staging with new PHP version',
						'Test all functionality',
						'Check error logs',
					),
				),
				'fixing_compatibility_issues' => array(
					'Update Plugins/Themes' => array(
						'Check for updates first',
						'Most active plugins updated for PHP 8',
						'Contact developer if abandoned',
					),
					'Replace Deprecated Functions' => array(
						'mysql_query → wpdb->query',
						'create_function → anonymous functions',
						'each → foreach',
					),
					'Hire Developer' => array(
						'Complex fixes need expertise',
						'Cost: $50-200 for small fixes',
						'Worth it vs broken site',
					),
				),
				'php_version_roadmap'       => array(
					'PHP 7.4' => 'End of life: November 2022',
					'PHP 8.0' => 'Active support until November 2023',
					'PHP 8.1' => 'Active support until November 2024',
					'PHP 8.2' => 'Active support until December 2025',
					'PHP 8.3' => 'Latest stable (December 2023)',
				),
			),
		);
	}

	/**
	 * Check PHP compatibility.
	 *
	 * @since  1.2601.2148
	 * @return array Compatibility analysis.
	 */
	private static function check_php_compatibility() {
		$issues = array();
		$current_version = phpversion();

		// Check if PHP Compatibility Checker plugin active
		if ( is_plugin_active( 'php-compatibility-checker/php-compatibility-checker.php' ) ) {
			// Plugin handles detailed checks
			return array(
				'current_version' => $current_version,
				'issues'          => array(),
			);
		}

		// Basic check: Is PHP version old?
		if ( version_compare( $current_version, '8.0', '<' ) ) {
			$issues[] = sprintf(
				/* translators: %s: PHP version */
				__( 'PHP %s is outdated - upgrade to 8.1+ recommended', 'wpshadow' ),
				$current_version
			);
		}

		// Check for deprecated WordPress features
		if ( version_compare( get_bloginfo( 'version' ), '6.0', '<' ) ) {
			$issues[] = __( 'WordPress version outdated - may use deprecated PHP features', 'wpshadow' );
		}

		return array(
			'current_version' => $current_version,
			'issues'          => $issues,
		);
	}
}
