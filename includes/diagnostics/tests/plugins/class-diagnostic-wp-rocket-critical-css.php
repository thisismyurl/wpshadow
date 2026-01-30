<?php
/**
 * WP Rocket Critical CSS Diagnostic
 *
 * WP Rocket critical CSS not generated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.444.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Rocket Critical CSS Diagnostic Class
 *
 * @since 1.444.0000
 */
class Diagnostic_WpRocketCriticalCss extends Diagnostic_Base {

	protected static $slug = 'wp-rocket-critical-css';
	protected static $title = 'WP Rocket Critical CSS';
	protected static $description = 'WP Rocket critical CSS not generated';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'WP_ROCKET_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		$settings = get_option( 'wp_rocket_settings', array() );
		
		// Check 1: Verify critical CSS is enabled
		$critical_css = isset( $settings['critical_css'] ) ? (bool) $settings['critical_css'] : false;
		if ( ! $critical_css ) {
			$issues[] = 'Critical CSS not enabled';
		}
		
		// Check 2: Check for async CSS
		$async_css = isset( $settings['async_css'] ) ? (bool) $settings['async_css'] : false;
		if ( ! $async_css ) {
			$issues[] = 'Async CSS not enabled';
		}
		
		// Check 3: Verify unused CSS removal
		$unused_css = isset( $settings['remove_unused_css'] ) ? (bool) $settings['remove_unused_css'] : false;
		if ( ! $unused_css ) {
			$issues[] = 'Remove unused CSS not enabled';
		}
		
		// Check 4: Check for critical CSS cache
		$critical_css_cache = isset( $settings['critical_css_cache'] ) ? (bool) $settings['critical_css_cache'] : false;
		if ( $critical_css && ! $critical_css_cache ) {
			$issues[] = 'Critical CSS cache not enabled';
		}
		
		// Check 5: Verify fallback CSS configuration
		$fallback_css = isset( $settings['critical_css_fallback'] ) ? (bool) $settings['critical_css_fallback'] : false;
		if ( $critical_css && ! $fallback_css ) {
			$issues[] = 'Critical CSS fallback not configured';
		}
		
		// Check 6: Check for critical CSS generation failures
		$generation_failures = get_option( 'wp_rocket_critical_css_failures', 0 );
		if ( $generation_failures > 0 ) {
			$issues[] = 'Critical CSS generation failures detected';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d WP Rocket critical CSS issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-rocket-critical-css',
			);
		}
		
		return null;
	}
}
