<?php
/**
 * Hotjar Heatmap Recording Performance Diagnostic
 *
 * Hotjar Heatmap Recording Performance misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1371.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hotjar Heatmap Recording Performance Diagnostic Class
 *
 * @since 1.1371.0000
 */
class Diagnostic_HotjarHeatmapRecordingPerformance extends Diagnostic_Base {

	protected static $slug = 'hotjar-heatmap-recording-performance';
	protected static $title = 'Hotjar Heatmap Recording Performance';
	protected static $description = 'Hotjar Heatmap Recording Performance misconfigured';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'hotjar_tracking_code' ) && ! defined( 'HOTJAR_SITE_ID' ) ) {
			return null;
		}

		$issues = array();

		// Check if Hotjar tracking is configured
		$site_id = get_option( 'hotjar_site_id', '' );
		if ( empty( $site_id ) && ! defined( 'HOTJAR_SITE_ID' ) ) {
			$issues[] = 'Hotjar site ID not configured';
		}

		// Check for recording on all pages
		$record_all = get_option( 'hotjar_record_all_pages', '1' );
		if ( '1' === $record_all ) {
			$issues[] = 'recording enabled on all pages (impacts performance globally)';
		}

		// Check for admin tracking
		$track_admins = get_option( 'hotjar_track_admins', '1' );
		if ( '1' === $track_admins ) {
			$issues[] = 'tracking admin users (unnecessary data collection)';
		}

		// Check for sampling rate
		$sample_rate = get_option( 'hotjar_sample_rate', 100 );
		if ( $sample_rate > 50 ) {
			$issues[] = "high sampling rate ({$sample_rate}%, increases page load time)";
		}

		// Check script loading position
		$load_position = get_option( 'hotjar_load_position', 'header' );
		if ( 'header' === $load_position ) {
			$issues[] = 'Hotjar script loaded in header (blocks initial render)';
		}

		// Check for privacy compliance
		$suppress_fields = get_option( 'hotjar_suppress_fields', '0' );
		if ( '0' === $suppress_fields ) {
			$issues[] = 'form field suppression disabled (may record sensitive data)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Hotjar heatmap recording configuration issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/hotjar-heatmap-recording-performance',
			);
		}

		return null;
	}
}
