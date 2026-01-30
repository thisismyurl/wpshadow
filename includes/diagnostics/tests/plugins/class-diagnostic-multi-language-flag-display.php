<?php
/**
 * Multi Language Flag Display Diagnostic
 *
 * Multi Language Flag Display misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1184.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multi Language Flag Display Diagnostic Class
 *
 * @since 1.1184.0000
 */
class Diagnostic_MultiLanguageFlagDisplay extends Diagnostic_Base {

	protected static $slug = 'multi-language-flag-display';
	protected static $title = 'Multi Language Flag Display';
	protected static $description = 'Multi Language Flag Display misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();
		
		// Check 1: Flag display enabled
		$display = get_option( 'multilang_flag_display_enabled', 0 );
		if ( ! $display ) {
			$issues[] = 'Language flag display not enabled';
		}
		
		// Check 2: Flag position
		$position = get_option( 'multilang_flag_position', '' );
		if ( empty( $position ) ) {
			$issues[] = 'Flag position not configured';
		}
		
		// Check 3: Country flags configured
		$countries = get_option( 'multilang_country_flags_configured', 0 );
		if ( ! $countries ) {
			$issues[] = 'Country flag mapping not configured';
		}
		
		// Check 4: Flag accessibility
		$accessibility = get_option( 'multilang_flag_accessibility_enabled', 0 );
		if ( ! $accessibility ) {
			$issues[] = 'Flag accessibility (alt text) not enabled';
		}
		
		// Check 5: CDN/Cache compatibility
		$cdn = get_option( 'multilang_flag_cdn_compatible', 0 );
		if ( ! $cdn ) {
			$issues[] = 'Flag CDN compatibility not verified';
		}
		
		// Check 6: Mobile display
		$mobile = get_option( 'multilang_flag_mobile_optimized', 0 );
		if ( ! $mobile ) {
			$issues[] = 'Mobile flag display not optimized';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 30;
			$threat_multiplier = 6;
			$max_threat = 60;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d flag display issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/multi-language-flag-display',
			);
		}
		
		return null;
	}
}
