<?php
/**
 * Google Analytics Cross Domain Tracking Diagnostic
 *
 * Google Analytics Cross Domain Tracking misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1342.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Analytics Cross Domain Tracking Diagnostic Class
 *
 * @since 1.1342.0000
 */
class Diagnostic_GoogleAnalyticsCrossDomainTracking extends Diagnostic_Base {

	protected static $slug = 'google-analytics-cross-domain-tracking';
	protected static $title = 'Google Analytics Cross Domain Tracking';
	protected static $description = 'Google Analytics Cross Domain Tracking misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'ga_load_options' ) && ! defined( 'MONSTERINSIGHTS_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Cross-domain tracking configuration
		$ga_settings = get_option( 'google_analytics_settings', array() );
		if ( empty( $ga_settings['cross_domain_tracking'] ) || ! $ga_settings['cross_domain_tracking'] ) {
			$issues[] = 'cross-domain tracking not enabled';
		}

		// Check 2: Multiple domains configured
		if ( ! empty( $ga_settings['cross_domain_tracking'] ) ) {
			$domains = isset( $ga_settings['cross_domains'] ) ? $ga_settings['cross_domains'] : '';
			if ( empty( $domains ) ) {
				$issues[] = 'no domains configured for tracking';
			}
		}

		// Check 3: Tracking ID configured
		$tracking_id = get_option( 'ga_tracking_id', '' );
		if ( empty( $tracking_id ) ) {
			$issues[] = 'Google Analytics tracking ID not set';
		}

		// Check 4: Linker parameter in URLs
		if ( ! empty( $ga_settings['cross_domain_tracking'] ) ) {
			$linker_enabled = isset( $ga_settings['linker_param'] ) ? $ga_settings['linker_param'] : false;
			if ( ! $linker_enabled ) {
				$issues[] = 'linker parameter not enabled (sessions may break)';
			}
		}

		// Check 5: Enhanced e-commerce tracking with cross-domain
		$ecommerce_enabled = get_option( 'ga_enhanced_ecommerce', '0' );
		if ( '1' === $ecommerce_enabled && ! empty( $issues ) ) {
			$issues[] = 'enhanced e-commerce enabled without proper cross-domain setup';
		}

		// Check 6: MonsterInsights premium features
		if ( defined( 'MONSTERINSIGHTS_VERSION' ) ) {
			$mi_settings = get_option( 'monsterinsights_settings', array() );
			if ( ! empty( $mi_settings ) && ! isset( $mi_settings['cross_domain_tracking'] ) ) {
				$issues[] = 'MonsterInsights cross-domain tracking not configured';
			}
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Google Analytics cross-domain issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/google-analytics-cross-domain-tracking',
			);
		}

		return null;
	}
}
