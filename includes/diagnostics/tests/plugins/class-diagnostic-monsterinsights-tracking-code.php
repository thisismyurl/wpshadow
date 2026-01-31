<?php
/**
 * MonsterInsights Tracking Code Diagnostic
 *
 * MonsterInsights tracking code errors.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.425.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MonsterInsights Tracking Code Diagnostic Class
 *
 * @since 1.425.0000
 */
class Diagnostic_MonsterinsightsTrackingCode extends Diagnostic_Base {

	protected static $slug = 'monsterinsights-tracking-code';
	protected static $title = 'MonsterInsights Tracking Code';
	protected static $description = 'MonsterInsights tracking code errors';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'MONSTERINSIGHTS_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Google Analytics tracking ID
		$tracking_id = get_option( 'monsterinsights_tracking_id', '' );
		if ( empty( $tracking_id ) ) {
			$issues[] = 'Tracking ID not configured';
		}

		// Check 2: Tracking code placement
		$code_placement = get_option( 'monsterinsights_code_placement', '' );
		if ( empty( $code_placement ) || 'header' !== $code_placement ) {
			$issues[] = 'Tracking code not in header';
		}

		// Check 3: Duplicate tracking prevention
		$duplicate_prevention = get_option( 'monsterinsights_duplicate_prevention', false );
		if ( ! $duplicate_prevention ) {
			$issues[] = 'Duplicate tracking not prevented';
		}

		// Check 4: Bot filtering enabled
		$bot_filtering = get_option( 'monsterinsights_bot_filtering', false );
		if ( ! $bot_filtering ) {
			$issues[] = 'Bot filtering disabled';
		}

		// Check 5: Enhanced ecommerce tracking
		$enhanced_ecommerce = get_option( 'monsterinsights_enhanced_ecommerce', false );
		if ( class_exists( 'WooCommerce' ) && ! $enhanced_ecommerce ) {
			$issues[] = 'Enhanced ecommerce tracking disabled';
		}

		// Check 6: Demographics and interests
		$demographics = get_option( 'monsterinsights_demographics_enabled', false );
		if ( ! $demographics ) {
			$issues[] = 'Demographics tracking disabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 35 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'MonsterInsights tracking code issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/monsterinsights-tracking-code',
			);
		}
		// Verify core functionality
		if ( ! function_exists( 'get_post' ) ) {
			$issues[] = __( 'Post functionality not available', 'wpshadow' );
		}
		return null;
	}
}
