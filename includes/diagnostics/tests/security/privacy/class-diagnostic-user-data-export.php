<?php
/**
 * User Data Export Diagnostic
 *
 * Checks whether users can export their data.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Privacy
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Data Export Diagnostic Class
 *
 * Verifies that personal data export tools are available.
 *
 * @since 0.6093.1200
 */
class Diagnostic_User_Data_Export extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'user-data-export';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Way for Users to Export Their Data';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if personal data export tools are available';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$exporters = function_exists( 'wp_privacy_personal_data_exporters' )
			? wp_privacy_personal_data_exporters()
			: array();

		$stats['exporter_count'] = is_array( $exporters ) ? count( $exporters ) : 0;

		if ( empty( $exporters ) ) {
			$issues[] = __( 'No personal data exporters are registered', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'People should be able to download their data. WordPress includes export tools, and adding exporters for custom data keeps you compliant and transparent.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-data-export?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
