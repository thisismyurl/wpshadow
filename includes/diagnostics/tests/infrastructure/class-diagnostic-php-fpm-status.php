<?php
/**
 * Diagnostic: PHP-FPM Status Check
 *
 * Checks if PHP-FPM status page is accessible and provides pool information.
 * Status page helps monitor PHP-FPM performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Infrastructure
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Php_Fpm_Status
 *
 * Tests PHP-FPM status page availability.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Php_Fpm_Status extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-fpm-status';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP-FPM Status Check';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP-FPM status page is accessible';

	/**
	 * Check PHP-FPM status page.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if running PHP-FPM.
		$is_php_fpm = false;

		if ( function_exists( 'php_sapi_name' ) ) {
			$sapi = php_sapi_name();
			if ( strpos( $sapi, 'fpm' ) !== false ) {
				$is_php_fpm = true;
			}
		}

		if ( ! $is_php_fpm ) {
			return null; // Not applicable if not using PHP-FPM.
		}

		// Common PHP-FPM status page URLs.
		$status_urls = array(
			home_url( '/fpm-status' ),
			home_url( '/php-fpm-status' ),
			home_url( '/status' ),
		);

		$status_available = false;

		foreach ( $status_urls as $url ) {
			$response = wp_remote_get(
				$url,
				array(
					'timeout'   => 5,
					'sslverify' => false,
				)
			);

			if ( ! is_wp_error( $response ) ) {
				$status_code = wp_remote_retrieve_response_code( $response );
				$body        = wp_remote_retrieve_body( $response );

				// Check if response looks like PHP-FPM status.
				if ( 200 === $status_code && ( strpos( $body, 'pool:' ) !== false || strpos( $body, 'accepted conn:' ) !== false ) ) {
					$status_available = true;
					break;
				}
			}
		}

		if ( ! $status_available ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP-FPM status page is not accessible. Enabling it can help monitor pool status, active processes, and performance metrics. This is a server-level configuration.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_fpm_status',
				'meta'        => array(
					'is_php_fpm'       => true,
					'status_available' => false,
					'tested_urls'      => $status_urls,
				),
			);
		}

		// PHP-FPM status page is accessible.
		return null;
	}
}
