<?php
/**
 * Real Cookie Banner Content Blocker Diagnostic
 *
 * Real Cookie Banner Content Blocker not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1119.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Real Cookie Banner Content Blocker Diagnostic Class
 *
 * @since 1.1119.0000
 */
class Diagnostic_RealCookieBannerContentBlocker extends Diagnostic_Base {

	protected static $slug = 'real-cookie-banner-content-blocker';
	protected static $title = 'Real Cookie Banner Content Blocker';
	protected static $description = 'Real Cookie Banner Content Blocker not compliant';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Real Cookie Banner
		$has_rcb = defined( 'RCB_VERSION' ) ||
		           class_exists( 'DevOwl\RealCookieBanner\Core' ) ||
		           function_exists( 'real_cookie_banner_init' );
		
		if ( ! $has_rcb ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Content blocker enabled
		$blocker_enabled = get_option( 'rcb_content_blocker', 'yes' );
		if ( 'no' === $blocker_enabled ) {
			$issues[] = __( 'Content blocker disabled (scripts load without consent)', 'wpshadow' );
		}
		
		// Check 2: Service templates configured
		$services = get_option( 'rcb_services', array() );
		if ( empty( $services ) ) {
			$issues[] = __( 'No service templates configured (manual blocking)', 'wpshadow' );
		}
		
		// Check 3: Content blocker rules
		$blocker_rules = get_option( 'rcb_blocker_rules', array() );
		if ( count( $blocker_rules ) === 0 && 'yes' === $blocker_enabled ) {
			$issues[] = __( 'Content blocker active but no rules defined', 'wpshadow' );
		}
		
		// Check 4: Google Consent Mode
		$consent_mode = get_option( 'rcb_google_consent_mode', 'no' );
		if ( 'no' === $consent_mode ) {
			// Check if Google services are used
			$has_google = false;
			foreach ( $services as $service ) {
				if ( isset( $service['name'] ) && strpos( strtolower( $service['name'] ), 'google' ) !== false ) {
					$has_google = true;
					break;
				}
			}
			
			if ( $has_google ) {
				$issues[] = __( 'Google services used but Consent Mode disabled', 'wpshadow' );
			}
		}
		
		// Check 5: Geo-targeting
		$geo_enabled = get_option( 'rcb_geo_targeting', 'no' );
		if ( 'no' === $geo_enabled ) {
			$issues[] = __( 'Geo-targeting disabled (global banner for all regions)', 'wpshadow' );
		}
		
		// Check 6: TCF 2.0 integration
		$tcf_enabled = get_option( 'rcb_tcf', 'no' );
		if ( 'no' === $tcf_enabled ) {
			$issues[] = __( 'TCF 2.0 disabled (ad tech compliance issue)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of content blocker issues */
				__( 'Real Cookie Banner content blocker has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/real-cookie-banner-content-blocker',
		);
	}
}
