<?php
/**
 * Duplicator Package Size Diagnostic
 *
 * Duplicator packages not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.397.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Duplicator Package Size Diagnostic Class
 *
 * @since 1.397.0000
 */
class Diagnostic_DuplicatorPackageSizeOptimization extends Diagnostic_Base {

	protected static $slug = 'duplicator-package-size-optimization';
	protected static $title = 'Duplicator Package Size';
	protected static $description = 'Duplicator packages not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! ( class_exists( 'DUP_PRO_Package' ) || class_exists( 'DUP_Package' ) ) ) {
			return null;
		}

		$issues = array();

		// Check 1: File/folder exclusions
		$excludes = get_option( 'duplicator_file_exclusions_count', 0 );
		if ( $excludes <= 0 ) {
			$issues[] = 'File/folder exclusions not configured';
		}

		// Check 2: Database table selection
		$tables = get_option( 'duplicator_db_table_selection_enabled', 0 );
		if ( ! $tables ) {
			$issues[] = 'Database table selection not enabled';
		}

		// Check 3: Compression enabled
		$compress = get_option( 'duplicator_compression_enabled', 0 );
		if ( ! $compress ) {
			$issues[] = 'Package compression not enabled';
		}

		// Check 4: Compression level
		$level = absint( get_option( 'duplicator_compression_level', 0 ) );
		if ( $level <= 0 ) {
			$issues[] = 'Compression level not optimized';
		}

		// Check 5: Media file optimization
		$media = get_option( 'duplicator_media_optimization_enabled', 0 );
		if ( ! $media ) {
			$issues[] = 'Media file optimization not enabled';
		}

		// Check 6: Storage cleanup
		$cleanup = get_option( 'duplicator_old_package_cleanup_enabled', 0 );
		if ( ! $cleanup ) {
			$issues[] = 'Old package cleanup not enabled';
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
					'Found %d package size optimization issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/duplicator-package-size-optimization',
			);
		}

		return null;
	}
}
