<?php
/**
 * Imagify Compression Level Diagnostic
 *
 * Imagify Compression Level detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.742.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Imagify Compression Level Diagnostic Class
 *
 * @since 1.742.0000
 */
class Diagnostic_ImagifyCompressionLevel extends Diagnostic_Base {

	protected static $slug = 'imagify-compression-level';
	protected static $title = 'Imagify Compression Level';
	protected static $description = 'Imagify Compression Level detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'IMAGIFY_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Compression level configured
		$compression = get_option( 'imagify_compression_level', '' );
		if ( empty( $compression ) ) {
			$issues[] = 'Compression level not configured';
		}
		
		// Check 2: Ultra compression available
		$ultra_available = get_option( 'imagify_ultra_compression_available', 0 );
		if ( ! $ultra_available ) {
			$issues[] = 'Ultra compression not available';
		}
		
		// Check 3: WebP conversion enabled
		$webp_enabled = get_option( 'imagify_webp_conversion', 0 );
		if ( ! $webp_enabled ) {
			$issues[] = 'WebP conversion not enabled';
		}
		
		// Check 4: Backup original enabled
		$backup_original = get_option( 'imagify_backup_original', 0 );
		if ( ! $backup_original ) {
			$issues[] = 'Original image backup not enabled';
		}
		
		// Check 5: Bulk optimization available
		$bulk_available = get_option( 'imagify_bulk_optimization_available', 0 );
		if ( ! $bulk_available ) {
			$issues[] = 'Bulk optimization not available';
		}
		
		// Check 6: API key validation
		$api_key = get_option( 'imagify_api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = 'Imagify API key not configured';
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
					'Found %d Imagify compression issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/imagify-compression-level',
			);
		}
		
		return null;
	}
}
