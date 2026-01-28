<?php
/**
 * wp-includes Directory Protection Status Diagnostic
 *
 * Validates wp-includes directory isn't directly web-accessible for PHP execution.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * wp-includes Directory Protection Status Class
 *
 * Tests wp-includes directory protection.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Wp_Includes_Directory_Protection_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wp-includes-directory-protection-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'wp-includes Directory Protection Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates wp-includes directory isn\'t directly web-accessible for PHP execution';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$protection_status = self::check_wp_includes_protection();
		
		if ( ! $protection_status['protected'] ) {
			$issues = array();

			if ( ! $protection_status['htaccess_exists'] ) {
				$issues[] = __( 'No .htaccess file in wp-includes directory', 'wpshadow' );
			}

			if ( $protection_status['direct_access_possible'] ) {
				$issues[] = __( 'PHP files in wp-includes can be executed directly (file inclusion attack risk)', 'wpshadow' );
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wp-includes-directory-protection-status',
				'meta'         => array(
					'htaccess_exists'        => $protection_status['htaccess_exists'],
					'direct_access_possible' => $protection_status['direct_access_possible'],
					'web_server'             => $protection_status['web_server'],
				),
			);
		}

		return null;
	}

	/**
	 * Check wp-includes directory protection.
	 *
	 * @since  1.26028.1905
	 * @return array Protection status.
	 */
	private static function check_wp_includes_protection() {
		$status = array(
			'protected'             => true,
			'htaccess_exists'       => false,
			'direct_access_possible' => false,
			'web_server'            => '',
		);

		// Detect web server.
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';
		$status['web_server'] = $server_software;

		// Check for .htaccess in wp-includes (Apache).
		$htaccess_path = ABSPATH . 'wp-includes/.htaccess';
		$status['htaccess_exists'] = file_exists( $htaccess_path );

		// For Apache servers, .htaccess should exist.
		if ( false !== stripos( $server_software, 'apache' ) ) {
			if ( ! $status['htaccess_exists'] ) {
				$status['protected'] = false;
			}
		}

		// Test direct access to a core file (non-destructive test).
		$test_url = includes_url( 'version.php' );
		$response = wp_remote_get( $test_url, array( 'timeout' => 5 ) );
		
		if ( ! is_wp_error( $response ) ) {
			$status_code = wp_remote_retrieve_response_code( $response );
			$body = wp_remote_retrieve_body( $response );

			// If we can access and execute the file (contains PHP variables), it's not protected.
			if ( 200 === $status_code && ! empty( $body ) && false !== strpos( $body, '$wp_version' ) ) {
				$status['direct_access_possible'] = true;
				$status['protected'] = false;
			}
		}

		return $status;
	}
}
