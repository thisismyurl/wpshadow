<?php
/**
 * Crazy Egg Heatmap Data Storage Diagnostic
 *
 * Crazy Egg Heatmap Data Storage misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1374.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Crazy Egg Heatmap Data Storage Diagnostic Class
 *
 * @since 1.1374.0000
 */
class Diagnostic_CrazyEggHeatmapDataStorage extends Diagnostic_Base {

	protected static $slug = 'crazy-egg-heatmap-data-storage';
	protected static $title = 'Crazy Egg Heatmap Data Storage';
	protected static $description = 'Crazy Egg Heatmap Data Storage misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();

		// Check 1: Data storage enabled
		$storage = get_option( 'crazy_egg_heatmap_data_storage_enabled', 0 );
		if ( ! $storage ) {
			$issues[] = 'Heatmap data storage not enabled';
		}

		// Check 2: Data retention policy
		$retention = get_option( 'crazy_egg_data_retention_policy_set', 0 );
		if ( ! $retention ) {
			$issues[] = 'Data retention policy not configured';
		}

		// Check 3: Storage location
		$location = get_option( 'crazy_egg_storage_location_configured', 0 );
		if ( ! $location ) {
			$issues[] = 'Storage location not configured';
		}

		// Check 4: Backup configuration
		$backup = get_option( 'crazy_egg_backup_enabled', 0 );
		if ( ! $backup ) {
			$issues[] = 'Backup not configured';
		}

		// Check 5: Compression
		$compression = get_option( 'crazy_egg_data_compression_enabled', 0 );
		if ( ! $compression ) {
			$issues[] = 'Data compression not enabled';
		}

		// Check 6: Storage quota
		$quota = absint( get_option( 'crazy_egg_storage_quota_mb', 0 ) );
		if ( $quota <= 0 ) {
			$issues[] = 'Storage quota not configured';
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
					'Found %d heatmap data storage issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/crazy-egg-heatmap-data-storage',
			);
		}

		return null;
	}
}
