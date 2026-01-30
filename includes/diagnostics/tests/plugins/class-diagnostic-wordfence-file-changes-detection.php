<?php
/**
 * Wordfence File Changes Detection Diagnostic
 *
 * Wordfence File Changes Detection misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.845.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordfence File Changes Detection Diagnostic Class
 *
 * @since 1.845.0000
 */
class Diagnostic_WordfenceFileChangesDetection extends Diagnostic_Base {

	protected static $slug = 'wordfence-file-changes-detection';
	protected static $title = 'Wordfence File Changes Detection';
	protected static $description = 'Wordfence File Changes Detection misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WORDFENCE_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify file change detection is enabled
		$scan_enabled = get_option( 'wordfence_scansEnabled_fileContentsChange', 0 );
		if ( ! $scan_enabled ) {
			$issues[] = 'File change detection not enabled';
		}
		
		// Check 2: Check scan frequency
		$scan_schedule = get_option( 'wordfenceActivated', 0 );
		$last_scan = get_option( 'wordfence_lastScanCompleted', 0 );
		if ( $scan_schedule && ( time() - $last_scan > 604800 ) ) {
			$issues[] = 'File scan has not run in over 7 days';
		}
		
		// Check 3: Verify core files are monitored
		$monitor_core = get_option( 'wordfence_scansEnabled_core', 0 );
		if ( ! $monitor_core ) {
			$issues[] = 'WordPress core file monitoring not enabled';
		}
		
		// Check 4: Check if email notifications are configured
		$email_summary = get_option( 'wordfence_email_summary_enabled', 0 );
		if ( ! $email_summary ) {
			$issues[] = 'Email notifications for file changes not configured';
		}
		
		// Check 5: Verify scan includes all plugin/theme files
		$scan_plugins = get_option( 'wordfence_scansEnabled_plugins', 0 );
		$scan_themes = get_option( 'wordfence_scansEnabled_themes', 0 );
		if ( ! $scan_plugins || ! $scan_themes ) {
			$issues[] = 'Plugin/theme file scanning not fully enabled';
		}
		
		// Check 6: Check for detected file changes
		$file_changes = get_option( 'wordfence_totalFilesChanged', 0 );
		if ( $file_changes > 0 ) {
			$issues[] = sprintf( '%d file changes detected and not reviewed', $file_changes );
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 70;
			$threat_multiplier = 5;
			$max_threat = 95;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Wordfence file changes detection issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wordfence-file-changes-detection',
			);
		}
		
		return null;
	}
}
