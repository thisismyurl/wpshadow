<?php
/**
 * Plugin Nonce Validation Diagnostic
 *
 * Detects plugins missing nonce verification on forms.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5030.1045
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Nonce Validation Class
 *
 * Scans plugins for CSRF vulnerabilities (missing nonces).
 *
 * @since 1.5030.1045
 */
class Diagnostic_Plugin_Nonce_Validation extends Diagnostic_Base {

	protected static $slug        = 'plugin-nonce-validation';
	protected static $title       = 'Plugin Nonce Validation';
	protected static $description = 'Detects missing CSRF protection';
	protected static $family      = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5030.1045
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_nonce_validation';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins    = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );
		$vulnerabilities = array();

		foreach ( $all_plugins as $plugin_path => $plugin_data ) {
			if ( ! in_array( $plugin_path, $active_plugins, true ) ) {
				continue;
			}

			$plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin_path );
			$issues     = $this->scan_for_nonce_issues( $plugin_dir );

			if ( ! empty( $issues ) ) {
				$vulnerabilities[] = array(
					'name'   => $plugin_data['Name'],
					'slug'   => dirname( $plugin_path ),
					'issues' => $issues,
					'risk'   => count( $issues ) > 5 ? 'high' : 'medium',
				);
			}

			// Limit scans.
			if ( count( $vulnerabilities ) >= 8 ) {
				break;
			}
		}

		if ( ! empty( $vulnerabilities ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of plugins */
					__( '%d plugins have potential CSRF vulnerabilities. Update or replace immediately.', 'wpshadow' ),
					count( $vulnerabilities )
				),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-nonce-validation',
				'data'         => array(
					'vulnerable_plugins' => $vulnerabilities,
					'total_vulnerable'   => count( $vulnerabilities ),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}

	/**
	 * Scan plugin files for missing nonce validation.
	 *
	 * @since  1.5030.1045
	 * @param  string $plugin_dir Plugin directory.
	 * @return array  Array of nonce issues.
	 */
	private static function scan_for_nonce_issues( $plugin_dir ) {
		$issues    = array();
		$php_files = glob( $plugin_dir . '/*.php' );
		
		if ( ! empty( $php_files ) ) {
			$php_files = array_merge( $php_files, glob( $plugin_dir . '/**/*.php' ) );
		}

		// Limit to 20 files.
		$php_files = array_slice( $php_files, 0, 20 );

		foreach ( $php_files as $file ) {
			if ( ! is_readable( $file ) ) {
				continue;
			}

			$content  = file_get_contents( $file );
			$filename = basename( $file );

			// Check for $_POST usage without nonce verification.
			if ( preg_match( '/\$_(?:POST|REQUEST)\s*\[/i', $content ) ) {
				// Check if file has nonce verification.
				$has_nonce_check = preg_match( '/(?:wp_verify_nonce|check_ajax_referer|check_admin_referer)/i', $content );
				
				if ( ! $has_nonce_check ) {
					$issues[] = array(
						'file'  => $filename,
						'issue' => 'Uses $_POST/$_REQUEST without nonce verification',
					);
				}
			}

			// Check for form submission handlers without nonce.
			if ( preg_match( '/isset\s*\(\s*\$_POST\s*\[/i', $content ) ) {
				$has_nonce_field = preg_match( '/wp_nonce_field/i', $content );
				
				if ( ! $has_nonce_field ) {
					$issues[] = array(
						'file'  => $filename,
						'issue' => 'Form handler missing wp_nonce_field()',
					);
				}
			}

			// Limit issues per plugin.
			if ( count( $issues ) >= 10 ) {
				break;
			}
		}

		return $issues;
	}
}
