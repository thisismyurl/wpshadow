<?php
/**
 * Diagnostic: Debug Mode in Production
 *
 * Checks if WP_DEBUG, WP_DEBUG_DISPLAY, or WP_DEBUG_LOG are enabled on production sites.
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Debug_Mode Class
 *
 * Detects if WordPress debug mode is enabled in production, which exposes
 * sensitive information like file paths, database queries, plugin versions,
 * and PHP configuration to potential attackers.
 *
 * Debug mode should only be used in development environments when actively
 * troubleshooting issues. On production sites, it's a serious security risk
 * and performance drain.
 *
 * Checks for three conditions:
 * - Critical (80): WP_DEBUG true AND WP_DEBUG_DISPLAY true (publicly showing errors)
 * - High (50): WP_DEBUG true AND WP_DEBUG_LOG true (logging debug info)
 * - Good: WP_DEBUG false or undefined (no debug mode enabled)
 *
 * @since 1.2601.2200
 */
class Diagnostic_Debug_Mode extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @var string
	 */
	protected static $slug = 'debug-mode';

	/**
	 * Diagnostic title (user-facing)
	 *
	 * @var string
	 */
	protected static $title = 'Debug Mode in Production';

	/**
	 * Diagnostic description (plain language)
	 *
	 * @var string
	 */
	protected static $description = 'Detects if debug mode is enabled, exposing sensitive site information';

	/**
	 * Family grouping for batch operations
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Family label (human-readable)
	 *
	 * @var string
	 */
	protected static $family_label = 'Security';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks the WP_DEBUG, WP_DEBUG_DISPLAY, and WP_DEBUG_LOG constants
	 * to determine the severity of debug mode exposure. On production sites,
	 * any debug mode enabled is a security concern.
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if debug mode detected, null if disabled.
	 */
	public static function check() {
		$wp_debug         = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$wp_debug_display = defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY;
		$wp_debug_log     = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
		$environment_type = function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production';

		// If debug mode is not enabled at all, we're good
		if ( ! $wp_debug ) {
			return null;
		}

		// Critical: Debug mode enabled with public display
		// This directly exposes errors to visitors
		if ( $wp_debug_display ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => __(
					'Debug mode is publicly displaying errors. This exposes file paths, database queries, plugin versions, and other sensitive information to attackers. Disable debug mode immediately on production sites.',
					'wpshadow'
				),
				'severity'           => 'critical',
				'threat_level'       => 80,
				'site_health_status' => 'critical',
				'auto_fixable'       => true,
				'kb_link'            => 'https://wpshadow.com/kb/security-debug-mode',
				'family'             => self::$family,
				'details'            => array(
					'wp_debug'         => $wp_debug,
					'wp_debug_display' => $wp_debug_display,
					'wp_debug_log'     => $wp_debug_log,
					'environment'      => $environment_type,
					'risk'             => 'critical',
				),
			);
		}

		// High: Debug mode enabled with logging
		// Less immediately dangerous than display, but still a concern
		if ( $wp_debug_log ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => __(
					'Debug logging is enabled in production. While errors aren\'t displayed publicly, the debug.log file may accumulate sensitive information. Consider disabling debug mode unless actively troubleshooting.',
					'wpshadow'
				),
				'severity'           => 'high',
				'threat_level'       => 50,
				'site_health_status' => 'recommended',
				'auto_fixable'       => true,
				'kb_link'            => 'https://wpshadow.com/kb/security-debug-mode',
				'family'             => self::$family,
				'details'            => array(
					'wp_debug'         => $wp_debug,
					'wp_debug_display' => $wp_debug_display,
					'wp_debug_log'     => $wp_debug_log,
					'environment'      => $environment_type,
					'risk'             => 'high',
				),
			);
		}

		// Debug mode is true but no logging/display is enabled
		// Still worth monitoring but lower priority
		return array(
			'id'                 => self::$slug,
			'title'              => self::$title,
			'description'        => __(
				'Debug mode is enabled but not logging or displaying errors. While safer than other configurations, consider disabling this on production sites unless actively troubleshooting.',
				'wpshadow'
			),
			'severity'           => 'medium',
			'threat_level'       => 35,
			'site_health_status' => 'recommended',
			'auto_fixable'       => true,
			'kb_link'            => 'https://wpshadow.com/kb/security-debug-mode',
			'family'             => self::$family,
			'details'            => array(
				'wp_debug'         => $wp_debug,
				'wp_debug_display' => $wp_debug_display,
				'wp_debug_log'     => $wp_debug_log,
				'environment'      => $environment_type,
				'risk'             => 'medium',
			),
		);
	}
}
