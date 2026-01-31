<?php
/**
 * User Data Retention Policies Diagnostic
 *
 * Checks retention policies for personal data.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Data Retention Policies Diagnostic
 *
 * Validates retention policy configuration and documentation.
 *
 * @since 1.2601.2240
 */
class Diagnostic_User_Data_Retention_Policies extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-data-retention-policies';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Data Retention Policies';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks retention policies for personal data';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$details = array();

		$privacy_page = (int) get_option( 'wp_page_for_privacy_policy', 0 );
		$retention_days = (int) get_option( 'wpshadow_data_retention_days', 0 );

		if ( 0 === $privacy_page ) {
			$issues[] = __( 'Privacy policy page not configured', 'wpshadow' );
		}

		if ( 0 === $retention_days ) {
			$issues[] = __( 'No data retention period configured', 'wpshadow' );
		} elseif ( $retention_days > 3650 ) {
			$issues[] = __( 'Data retention period exceeds 10 years', 'wpshadow' );
		}

		$details['retention_days'] = $retention_days;

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'User data retention policies are not properly configured', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-data-retention-policies',
				'details'      => array(
					'issues'   => $issues,
					'info'     => $details,
				),
			);
		}

		return null;
	}
}
