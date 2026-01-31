<?php
/**
 * Data Export Request Handler Missing Diagnostic
 *
 * Checks if GDPR data export functionality is available.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data Export Request Handler Missing Diagnostic Class
 *
 * Detects missing GDPR data export functionality.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Data_Export_Request_Handler_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'data-export-request-handler-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Data Export Request Handler Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if data export handlers are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if privacy tools are accessible
		if ( ! function_exists( 'wp_privacy_personal_data_exporters' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Personal data export functionality is not available. GDPR requires ability to export user data on request.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/data-export-request-handler-missing',
			);
		}

		// Check if any exporters are registered
		$exporters = wp_privacy_personal_data_exporters();

		if ( empty( $exporters ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No data exporters are registered. Users cannot request their data export as required by GDPR.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/data-export-request-handler-missing',
			);
		}

		return null;
	}
}
