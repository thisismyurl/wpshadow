<?php
/**
 * Security Incidents Documented Diagnostic
 *
 * Tests if security incidents are logged and tracked.
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
 * Security Incidents Documented Diagnostic Class
 *
 * Verifies that incident logs or records exist.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Documents_Security_Incidents extends Diagnostic_Base {

	protected static $slug = 'documents-security-incidents';
	protected static $title = 'Security Incidents Documented';
	protected static $description = 'Tests if security incidents are logged and tracked';
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$log_plugins = array(
			'simple-history/index.php',
			'activity-log/activity-log.php',
			'wp-security-audit-log/wp-security-audit-log.php',
		);

		foreach ( $log_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return null;
			}
		}

		$manual_flag = get_option( 'wpshadow_security_incident_log' );
		if ( $manual_flag ) {
			return null;
		}

		$keywords = array(
			'security incident',
			'incident log',
			'incident report',
			'breach report',
		);

		if ( self::has_documented_item( $keywords ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No security incident documentation found. Keep an incident log to learn from events and meet compliance needs.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/security-incidents-documented?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'persona'      => 'enterprise-corp',
		);
	}

	/**
	 * Check for documentation evidence in posts.
	 *
	 * @since 0.6093.1200
	 * @param  array $keywords Search terms.
	 * @return bool True if found.
	 */
	private static function has_documented_item( array $keywords ) {
		if ( ! function_exists( 'get_posts' ) ) {
			return false;
		}

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'post', 'documentation', 'kb' ),
					'post_status'    => array( 'publish', 'private' ),
					'posts_per_page' => 1,
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				return true;
			}
		}

		return false;
	}
}
