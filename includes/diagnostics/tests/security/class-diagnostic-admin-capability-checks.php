<?php
/**
 * Admin Capability Checks Diagnostic
 *
 * Checks if admin actions properly verify user capabilities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Capability Checks Diagnostic Class
 *
 * Actions without capability checks allow privilege escalation—like having admin
 * buttons visible but not checking if user should actually use them.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Admin_Capability_Checks extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-capability-checks';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin Actions Missing Capability Checks';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if admin actions verify user capabilities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$missing_checks = array();

		// Check theme functions.php.
		$theme_checks = self::check_theme_capability_checks();
		if ( ! empty( $theme_checks ) ) {
			$missing_checks = array_merge( $missing_checks, $theme_checks );
		}

		// Check active plugins.
		$plugin_checks = self::check_plugin_capability_checks();
		if ( ! empty( $plugin_checks ) ) {
			$missing_checks = array_merge( $missing_checks, $plugin_checks );
		}

		if ( empty( $missing_checks ) ) {
			return null; // All capability checks in place.
		}

		$severity     = count( $missing_checks ) > 5 ? 'critical' : 'high';
		$threat_level = count( $missing_checks ) > 5 ? 90 : 70;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of missing checks */
				__( 'Found %d admin action(s) without capability checks. Subscriber-role users could perform admin actions. Add current_user_can() checks.', 'wpshadow' ),
				count( $missing_checks )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/capability-checks?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'meta'         => array(
				'missing_checks' => $missing_checks,
			),
		);
	}

	/**
	 * Check theme for capability checks.
	 *
	 * @since 0.6093.1200
	 * @return array List of missing checks.
	 */
	private static function check_theme_capability_checks(): array {
		$missing = array();

		$functions_file = get_stylesheet_directory() . '/functions.php';
		if ( ! file_exists( $functions_file ) ) {
			return $missing;
		}

		$content = file_get_contents( $functions_file );
		if ( empty( $content ) ) {
			return $missing;
		}

		// Check for admin_init hooks without capability checks.
		if ( preg_match_all( '/add_action\s*\(\s*[\'"]admin_init[\'"].*?\)/', $content, $matches ) ) {
			foreach ( $matches[0] as $match ) {
				if ( strpos( $content, 'current_user_can' ) === false ) {
					$missing[] = array(
						'location' => 'functions.php',
						'issue'    => 'admin_init hook without capability check',
					);
				}
			}
		}

		return $missing;
	}

	/**
	 * Check plugins for capability checks.
	 *
	 * @since 0.6093.1200
	 * @return array List of missing checks.
	 */
	private static function check_plugin_capability_checks(): array {
		$missing = array();

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins    = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $active_plugins as $plugin_file ) {
			$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
			if ( ! file_exists( $plugin_path ) ) {
				continue;
			}

			$content = file_get_contents( $plugin_path );
			if ( empty( $content ) ) {
				continue;
			}

			// Check for AJAX handlers without capability checks.
			if ( preg_match_all( '/add_action\s*\(\s*[\'"]wp_ajax_(\w+)[\'"]/', $content, $matches ) ) {
				foreach ( $matches[1] as $action ) {
					// Look for capability check near the ajax handler.
					$pattern = '/function.*?' . preg_quote( $action, '/' ) . '.*?\{(.*?)\}/s';
					if ( preg_match( $pattern, $content, $func_match ) ) {
						if ( strpos( $func_match[1], 'current_user_can' ) === false ) {
							$missing[] = array(
								'location' => basename( $plugin_file ),
								'issue'    => 'AJAX handler ' . $action . ' without capability check',
							);
						}
					}
				}
			}
		}

		// Limit to first 10 issues.
		return array_slice( $missing, 0, 10 );
	}
}
