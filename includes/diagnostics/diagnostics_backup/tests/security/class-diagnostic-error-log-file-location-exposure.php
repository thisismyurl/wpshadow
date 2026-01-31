<?php
/**
 * Error Log File Location Exposure Diagnostic
 *
 * Checks if error_log, debug.log files are web-accessible.
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
 * Error Log File Location Exposure Class
 *
 * Tests error log accessibility.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Error_Log_File_Location_Exposure extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'error-log-file-location-exposure';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Error Log File Location Exposure';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if error_log, debug.log files are web-accessible';

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
		$exposure_check = self::check_log_exposure();
		
		if ( $exposure_check['accessible_logs_found'] > 0 ) {
			$issues = array();
			
			$issues[] = sprintf(
				/* translators: %d: number of accessible log files */
				__( '%d log files publicly accessible', 'wpshadow' ),
				$exposure_check['accessible_logs_found']
			);

			if ( ! empty( $exposure_check['accessible_paths'] ) ) {
				$issues[] = sprintf(
					/* translators: %s: comma-separated list of accessible paths */
					__( 'Accessible: %s', 'wpshadow' ),
					implode( ', ', array_slice( $exposure_check['accessible_paths'], 0, 3 ) )
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/error-log-file-location-exposure',
				'meta'         => array(
					'accessible_logs_found' => $exposure_check['accessible_logs_found'],
					'accessible_paths'      => $exposure_check['accessible_paths'],
					'contains_sensitive'    => $exposure_check['contains_sensitive'],
				),
			);
		}

		return null;
	}

	/**
	 * Check log file exposure.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_log_exposure() {
		$check = array(
			'accessible_logs_found' => 0,
			'accessible_paths'      => array(),
			'contains_sensitive'    => false,
		);

		$site_url = get_site_url();
		
		// Common log file locations.
		$log_paths = array(
			'/error_log',
			'/error.log',
			'/errors.log',
			'/wp-content/debug.log',
			'/wp-content/error_log',
			'/wp-content/uploads/error_log',
			'/debug.log',
			'/logs/error.log',
		);

		foreach ( $log_paths as $path ) {
			$test_url = $site_url . $path;
			
			$response = wp_remote_head( $test_url, array( 'timeout' => 5 ) );
			
			if ( ! is_wp_error( $response ) ) {
				$status_code = wp_remote_retrieve_response_code( $response );
				
				if ( 200 === $status_code ) {
					++$check['accessible_logs_found'];
					$check['accessible_paths'][] = $path;

					// Try to get a snippet to check for sensitive data.
					$get_response = wp_remote_get( $test_url, array( 'timeout' => 5 ) );
					if ( ! is_wp_error( $get_response ) ) {
						$body = wp_remote_retrieve_body( $get_response );
						
						// Check for sensitive patterns.
						$sensitive_patterns = array( 'password', 'api_key', 'database', 'mysql', 'credentials' );
						foreach ( $sensitive_patterns as $pattern ) {
							if ( false !== stripos( $body, $pattern ) ) {
								$check['contains_sensitive'] = true;
								break;
							}
						}
					}
				}
			}
		}

		return $check;
	}
}
