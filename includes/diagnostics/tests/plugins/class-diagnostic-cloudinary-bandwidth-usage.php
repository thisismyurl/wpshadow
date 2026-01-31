<?php
/**
 * Cloudinary Bandwidth Usage Diagnostic
 *
 * Cloudinary Bandwidth Usage detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.787.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cloudinary Bandwidth Usage Diagnostic Class
 *
 * @since 1.787.0000
 */
class Diagnostic_CloudinaryBandwidthUsage extends Diagnostic_Base {

	protected static $slug = 'cloudinary-bandwidth-usage';
	protected static $title = 'Cloudinary Bandwidth Usage';
	protected static $description = 'Cloudinary Bandwidth Usage detected';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();

		// Check 1: Bandwidth monitoring enabled
		$monitor = get_option( 'cloudinary_bandwidth_monitoring_enabled', 0 );
		if ( ! $monitor ) {
			$issues[] = 'Bandwidth monitoring not enabled';
		}

		// Check 2: Optimization enabled
		$opt = get_option( 'cloudinary_bandwidth_optimization_enabled', 0 );
		if ( ! $opt ) {
			$issues[] = 'Bandwidth optimization not enabled';
		}

		// Check 3: Responsive images
		$responsive = get_option( 'cloudinary_responsive_images_enabled', 0 );
		if ( ! $responsive ) {
			$issues[] = 'Responsive images not enabled';
		}

		// Check 4: Format selection
		$format = get_option( 'cloudinary_format_selection_enabled', 0 );
		if ( ! $format ) {
			$issues[] = 'Automatic format selection not enabled';
		}

		// Check 5: Quality optimization
		$quality = get_option( 'cloudinary_quality_optimization_enabled', 0 );
		if ( ! $quality ) {
			$issues[] = 'Quality optimization not enabled';
		}

		// Check 6: Usage alerts
		$alerts = get_option( 'cloudinary_usage_alerts_enabled', 0 );
		if ( ! $alerts ) {
			$issues[] = 'Usage alerts not configured';
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
					'Found %d bandwidth usage issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cloudinary-bandwidth-usage',
			);
		}

		return null;
	}
}
