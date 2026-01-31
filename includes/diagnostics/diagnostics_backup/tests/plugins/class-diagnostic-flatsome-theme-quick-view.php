<?php
/**
 * Flatsome Theme Quick View Diagnostic
 *
 * Flatsome Theme Quick View needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1323.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flatsome Theme Quick View Diagnostic Class
 *
 * @since 1.1323.0000
 */
class Diagnostic_FlatsomeThemeQuickView extends Diagnostic_Base {

	protected static $slug = 'flatsome-theme-quick-view';
	protected static $title = 'Flatsome Theme Quick View';
	protected static $description = 'Flatsome Theme Quick View needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();

		// Check 1: Quick view enabled
		$enabled = get_option( 'flatsome_quick_view_enabled', 0 );
		if ( ! $enabled ) {
			$issues[] = 'Quick view feature not enabled';
		}

		// Check 2: Quick view performance
		$perf = get_option( 'flatsome_quick_view_performance_optimized', 0 );
		if ( ! $perf ) {
			$issues[] = 'Quick view performance not optimized';
		}

		// Check 3: Ajax loading
		$ajax = get_option( 'flatsome_quick_view_ajax_enabled', 0 );
		if ( ! $ajax ) {
			$issues[] = 'Quick view AJAX loading not enabled';
		}

		// Check 4: Lightbox optimization
		$lightbox = get_option( 'flatsome_quick_view_lightbox_optimized', 0 );
		if ( ! $lightbox ) {
			$issues[] = 'Lightbox optimization not configured';
		}

		// Check 5: Mobile responsiveness
		$mobile = get_option( 'flatsome_quick_view_mobile_responsive', 0 );
		if ( ! $mobile ) {
			$issues[] = 'Mobile responsiveness not optimized';
		}

		// Check 6: Analytics tracking
		$analytics = get_option( 'flatsome_quick_view_analytics_tracking_enabled', 0 );
		if ( ! $analytics ) {
			$issues[] = 'Quick view analytics tracking not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 35;
			$threat_multiplier = 6;
			$max_threat = 65;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d quick view issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/flatsome-theme-quick-view',
			);
		}

		return null;
	}
}
