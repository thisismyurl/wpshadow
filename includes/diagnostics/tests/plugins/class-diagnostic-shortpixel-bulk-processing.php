<?php
/**
 * Shortpixel Bulk Processing Diagnostic
 *
 * Shortpixel Bulk Processing detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.745.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortpixel Bulk Processing Diagnostic Class
 *
 * @since 1.745.0000
 */
class Diagnostic_ShortpixelBulkProcessing extends Diagnostic_Base {

	protected static $slug = 'shortpixel-bulk-processing';
	protected static $title = 'Shortpixel Bulk Processing';
	protected static $description = 'Shortpixel Bulk Processing detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'SHORTPIXEL_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify bulk optimization is enabled
		$bulk_enabled = get_option( 'shortpixel_bulk_optimization', 0 );
		if ( ! $bulk_enabled ) {
			$issues[] = 'Bulk optimization not enabled';
		}
		
		// Check 2: Check for bulk queue size
		$bulk_queue = get_option( 'shortpixel_bulk_queue', 0 );
		if ( $bulk_queue > 1000 ) {
			$issues[] = 'Bulk queue size high (over 1000)';
		}
		
		// Check 3: Verify bulk processing is not paused
		$bulk_paused = get_option( 'shortpixel_bulk_paused', 0 );
		if ( $bulk_paused ) {
			$issues[] = 'Bulk processing is paused';
		}
		
		// Check 4: Check for processing limits
		$processing_limit = get_option( 'shortpixel_processing_limit', 0 );
		if ( $processing_limit <= 0 ) {
			$issues[] = 'Processing limit not configured';
		}
		
		// Check 5: Verify API quota monitoring
		$api_quota = get_option( 'shortpixel_api_quota', 0 );
		if ( $api_quota <= 0 ) {
			$issues[] = 'API quota not configured';
		}
		
		// Check 6: Check for backup creation
		$backup_enabled = get_option( 'shortpixel_backup_enabled', 0 );
		if ( ! $backup_enabled ) {
			$issues[] = 'Image backup not enabled before optimization';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d ShortPixel bulk processing issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/shortpixel-bulk-processing',
			);
		}
		
		return null;
	}
}
