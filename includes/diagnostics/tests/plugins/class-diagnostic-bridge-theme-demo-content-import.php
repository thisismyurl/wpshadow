<?php
/**
 * Bridge Theme Demo Content Import Diagnostic
 *
 * Bridge Theme Demo Content Import needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1316.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bridge Theme Demo Content Import Diagnostic Class
 *
 * @since 1.1316.0000
 */
class Diagnostic_BridgeThemeDemoContentImport extends Diagnostic_Base {

	protected static $slug = 'bridge-theme-demo-content-import';
	protected static $title = 'Bridge Theme Demo Content Import';
	protected static $description = 'Bridge Theme Demo Content Import needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();
		
		// Check 1: Demo content imported
		$demo = get_option( 'bridge_demo_content_imported', 0 );
		if ( ! $demo ) {
			$issues[] = 'Demo content not imported';
		}
		
		// Check 2: Import progress
		$progress = get_option( 'bridge_demo_import_progress_tracking_enabled', 0 );
		if ( ! $progress ) {
			$issues[] = 'Import progress tracking not enabled';
		}
		
		// Check 3: Media import settings
		$media = get_option( 'bridge_demo_media_import_enabled', 0 );
		if ( ! $media ) {
			$issues[] = 'Media import not enabled';
		}
		
		// Check 4: Content revisions
		$revisions = get_option( 'bridge_demo_post_revisions_enabled', 0 );
		if ( ! $revisions ) {
			$issues[] = 'Post revisions not enabled';
		}
		
		// Check 5: Import logging
		$logging = get_option( 'bridge_demo_import_logging_enabled', 0 );
		if ( ! $logging ) {
			$issues[] = 'Import logging not enabled';
		}
		
		// Check 6: Cleanup after import
		$cleanup = get_option( 'bridge_demo_post_import_cleanup_enabled', 0 );
		if ( ! $cleanup ) {
			$issues[] = 'Post-import cleanup not enabled';
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
					'Found %d demo import issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/bridge-theme-demo-content-import',
			);
		}
		
		return null;
	}
}
