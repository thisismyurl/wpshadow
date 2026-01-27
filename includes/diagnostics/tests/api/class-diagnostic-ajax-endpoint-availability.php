<?php
/**
 * Diagnostic: AJAX Endpoint Availability (Duplicate Check)
 *
 * Note: This appears to be a duplicate of issue #1251. This diagnostic checks
 * for AJAX endpoint accessibility issues that may not be caught by the primary check.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\API
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Ajax_Endpoint_Availability
 *
 * Additional AJAX endpoint availability checks.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Ajax_Endpoint_Availability extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'ajax-endpoint-availability';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'AJAX Endpoint Availability (Extended)';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Extended AJAX endpoint accessibility check';

	/**
	 * Check AJAX endpoint availability (extended checks).
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$ajax_url = admin_url( 'admin-ajax.php' );

		// Check if admin-ajax.php file exists.
		$ajax_file = ABSPATH . 'wp-admin/admin-ajax.php';

		if ( ! file_exists( $ajax_file ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'AJAX endpoint file (admin-ajax.php) is missing from WordPress installation.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/ajax_endpoint_availability',
				'meta'        => array(
					'ajax_file' => $ajax_file,
					'exists'    => false,
				),
			);
		}

		// Check if file is readable.
		if ( ! is_readable( $ajax_file ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'AJAX endpoint file exists but is not readable. Check file permissions.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/ajax_endpoint_availability',
				'meta'        => array(
					'ajax_file'  => $ajax_file,
					'readable'   => false,
					'perms'      => substr( sprintf( '%o', fileperms( $ajax_file ) ), -4 ),
				),
			);
		}

		// Test with a HEAD request to check availability without executing.
		$response = wp_remote_head(
			$ajax_url,
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Error message */
					__( 'AJAX endpoint could not be reached: %s', 'wpshadow' ),
					$response->get_error_message()
				),
				'severity'    => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/ajax_endpoint_availability',
				'meta'        => array(
					'ajax_url' => $ajax_url,
					'error'    => $response->get_error_message(),
				),
			);
		}

		// AJAX endpoint is available.
		return null;
	}
}
