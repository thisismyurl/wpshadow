<?php
/**
 * Plugin CSRF Protection Diagnostic
 *
 * Detects plugins missing CSRF nonce verification.
 *
 * @since   1.4031.1939
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_CSRF_Protection Class
 *
 * Identifies plugins missing CSRF nonce protection.
 */
class Diagnostic_Plugin_CSRF_Protection extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-csrf-protection';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin CSRF Protection';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins missing CSRF nonce verification';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.4031.1939
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$csrf_risks = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );
		$plugins_dir    = WP_PLUGIN_DIR;

		foreach ( $active_plugins as $plugin ) {
			$plugin_file = $plugins_dir . '/' . $plugin;

			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			$content = file_get_contents( $plugin_file );

			// Check for form submissions without nonce
			if ( preg_match( '/<form[^>]*method\s*=\s*["\']post["\']/', $content ) ) {
				// Check if it has nonce fields
				if ( ! preg_match( '/wp_nonce_field|wp_create_nonce/', $content ) ) {
					$csrf_risks[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Has POST forms without nonce verification.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for $_POST processing without nonce check
			if ( preg_match( '/if\s*\(\s*isset\s*\(\s*\$_POST/', $content ) ) {
				// Check if it verifies nonce
				if ( ! preg_match( '/wp_verify_nonce|check_admin_referer/', $content ) ) {
					$csrf_risks[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Processes $_POST without nonce verification.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for AJAX handlers without nonce
			if ( preg_match( '/add_action\s*\(\s*["\']wp_ajax/', $content ) ) {
				// Check if it checks nonce
				if ( ! preg_match( '/check_ajax_referer|wp_verify_nonce/', $content ) ) {
					$csrf_risks[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Has AJAX handlers without nonce verification.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for links that modify data
			if ( preg_match( '/\?.*=delete|trash|restore/', $content ) ) {
				// Check if they use WordPress nonce
				if ( ! preg_match( '/wp_nonce_url|wp_create_nonce/', $content ) ) {
					$csrf_risks[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Has data-modifying links without nonce protection.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}
		}

		if ( ! empty( $csrf_risks ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: risk count, %s: details */
					__( '%d CSRF protection vulnerabilities detected: %s', 'wpshadow' ),
					count( $csrf_risks ),
					implode( ' | ', array_slice( $csrf_risks, 0, 3 ) )
				),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'details'      => array(
					'csrf_risks' => $csrf_risks,
				),
				'kb_link'      => 'https://wpshadow.com/kb/csrf-protection',
			);
		}

		return null;
	}
}
