<?php
/**
 * GDPR User Data Export Functionality Diagnostic
 *
 * Checks if data export hooks are registered.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GDPR User Data Export Functionality Diagnostic
 *
 * Validates that personal data exporters are registered.
 *
 * @since 1.6030.2240
 */
class Diagnostic_GDPR_User_Data_Export_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-user-data-export-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR User Data Export Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if data export hooks are registered';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$has_exporters = has_filter( 'wp_privacy_personal_data_exporters' );

		if ( $has_exporters ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No personal data exporters registered', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/gdpr-user-data-export-functionality',
			'details'      => array(
				'issues' => array(
					__( 'No handlers registered on wp_privacy_personal_data_exporters', 'wpshadow' ),
				),
			),
		);
	}
}
