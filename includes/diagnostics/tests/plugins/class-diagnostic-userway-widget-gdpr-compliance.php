<?php
/**
 * Userway Widget Gdpr Compliance Diagnostic
 *
 * Userway Widget Gdpr Compliance not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1101.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Userway Widget Gdpr Compliance Diagnostic Class
 *
 * @since 1.1101.0000
 */
class Diagnostic_UserwayWidgetGdprCompliance extends Diagnostic_Base {

	protected static $slug = 'userway-widget-gdpr-compliance';
	protected static $title = 'Userway Widget Gdpr Compliance';
	protected static $description = 'Userway Widget Gdpr Compliance not compliant';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'userway_widget_init' ) && ! defined( 'USERWAY_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Cookie consent.
		$cookie_consent = get_option( 'userway_cookie_consent', '0' );
		if ( '0' === $cookie_consent ) {
			$issues[] = 'cookie consent not enabled';
		}

		// Check 2: Data processing agreement.
		$dpa = get_option( 'userway_dpa_signed', '0' );
		if ( '0' === $dpa ) {
			$issues[] = 'data processing agreement not signed';
		}

		// Check 3: Privacy policy link.
		$privacy_link = get_option( 'userway_privacy_policy', '' );
		if ( empty( $privacy_link ) ) {
			$issues[] = 'privacy policy link missing';
		}

		// Check 4: EU user detection.
		$eu_detection = get_option( 'userway_eu_detection', '1' );
		if ( '0' === $eu_detection ) {
			$issues[] = 'EU user detection disabled';
		}

		// Check 5: Data retention period.
		$retention = get_option( 'userway_data_retention', 0 );
		if ( 0 === $retention ) {
			$issues[] = 'no data retention policy';
		}

		// Check 6: Right to erasure.
		$erasure = get_option( 'userway_enable_erasure', '0' );
		if ( '0' === $erasure ) {
			$issues[] = 'data erasure feature disabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 85, 70 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'GDPR compliance issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/userway-widget-gdpr-compliance',
			);
		}

		return null;
	}
}
