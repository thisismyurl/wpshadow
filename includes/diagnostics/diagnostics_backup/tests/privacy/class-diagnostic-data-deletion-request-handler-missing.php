<?php
/**
 * Data Deletion Request Handler Missing Diagnostic
 *
 * Checks if GDPR data deletion functionality is available.
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
 * Data Deletion Request Handler Missing Diagnostic Class
 *
 * Detects missing GDPR data deletion functionality.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Data_Deletion_Request_Handler_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'data-deletion-request-handler-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Data Deletion Request Handler Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if data deletion handlers are configured';

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
		if ( ! function_exists( 'wp_privacy_personal_data_erasers' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Personal data deletion functionality is not available. GDPR requires ability to delete user data on request.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/data-deletion-request-handler-missing',
			);
		}

		// Check if any erasers are registered
		$erasers = wp_privacy_personal_data_erasers();

		if ( empty( $erasers ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No data erasers are registered. Users cannot request their data deletion as required by GDPR and CCPA.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 80,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/data-deletion-request-handler-missing',
			);
		}

		return null;
	}
}
