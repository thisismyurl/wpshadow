<?php
/**
 * Google Tag Manager Consent Mode Diagnostic
 *
 * Google Tag Manager Consent Mode misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1349.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Tag Manager Consent Mode Diagnostic Class
 *
 * @since 1.1349.0000
 */
class Diagnostic_GoogleTagManagerConsentMode extends Diagnostic_Base {

	protected static $slug = 'google-tag-manager-consent-mode';
	protected static $title = 'Google Tag Manager Consent Mode';
	protected static $description = 'Google Tag Manager Consent Mode misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'GTM4WP_VERSION' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify consent mode enabled
		$consent_mode = get_option( 'gtm_consent_mode_enabled', false );
		if ( ! $consent_mode ) {
			$issues[] = __( 'Google Tag Manager consent mode not enabled', 'wpshadow' );
		}

		// Check 2: Check analytics consent
		$analytics_consent = get_option( 'gtm_analytics_consent_required', false );
		if ( ! $analytics_consent ) {
			$issues[] = __( 'Analytics consent tracking not configured', 'wpshadow' );
		}

		// Check 3: Verify advertising consent
		$ad_consent = get_option( 'gtm_advertising_consent_required', false );
		if ( ! $ad_consent ) {
			$issues[] = __( 'Advertising consent tracking not configured', 'wpshadow' );
		}

		// Check 4: Check personalization consent
		$personalization = get_option( 'gtm_personalization_consent', false );
		if ( ! $personalization ) {
			$issues[] = __( 'Personalization consent not configured', 'wpshadow' );
		}

		// Check 5: Verify denial default state
		$deny_default = get_option( 'gtm_consent_deny_by_default', false );
		if ( ! $deny_default ) {
			$issues[] = __( 'Consent deny-by-default mode not enabled', 'wpshadow' );
		}

		// Check 6: Check user consent banner
		$banner_enabled = get_option( 'gtm_consent_banner_enabled', false );
		if ( ! $banner_enabled ) {
			$issues[] = __( 'Consent banner not enabled for GTM', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 80, 50 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'Google Tag Manager consent mode issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/google-tag-manager-consent-mode',
			);
		}

		return null;
	}
}

	}
}
