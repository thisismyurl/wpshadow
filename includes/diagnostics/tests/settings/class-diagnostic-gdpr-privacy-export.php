<?php
/**
 * GDPR Privacy Data Export Diagnostic
 *
 * Checks that WordPress GDPR privacy export functionality is available
 * and that WPShadow implements proper user data export capabilities.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_GDPR_Privacy_Export Class
 *
 * Verifies that privacy data export is configured per GDPR requirements.
 *
 * @since 0.6093.1200
 */
class Diagnostic_GDPR_Privacy_Export extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-privacy-export';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Privacy Data Export';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures WordPress privacy data export functionality is available for compliance';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding if privacy export is not properly configured.
	 */
	public static function check() {
		// Check if WordPress privacy exporter callbacks are registered
		$exporters = apply_filters( 'wp_privacy_personal_data_exporters', array() );

		// Check for WPShadow data exporter
		$has_wpshadow_exporter = false;
		foreach ( $exporters as $exporter_callback ) {
			if ( is_array( $exporter_callback ) && isset( $exporter_callback[0] ) && 'WPShadow\Privacy\Privacy_Exporter' === $exporter_callback[0] ) {
				$has_wpshadow_exporter = true;
				break;
			}
		}

		// If no WPShadow exporter, this is an issue
		if ( ! $has_wpshadow_exporter ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'WPShadow privacy data export is not registered with WordPress', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/gdpr-compliance?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		// Check if privacy policy page is set
		$privacy_policy_page = get_option( 'wp_page_for_privacy_policy' );
		if ( empty( $privacy_policy_page ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'WordPress privacy policy page is not configured. Set one in Settings > Privacy', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/privacy-policy-setup?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		// All checks passed
		return null;
	}
}
