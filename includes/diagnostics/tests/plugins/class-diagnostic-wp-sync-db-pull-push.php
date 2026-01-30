<?php
/**
 * Wp Sync Db Pull Push Diagnostic
 *
 * Wp Sync Db Pull Push issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1067.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Sync Db Pull Push Diagnostic Class
 *
 * @since 1.1067.0000
 */
class Diagnostic_WpSyncDbPullPush extends Diagnostic_Base {

	protected static $slug = 'wp-sync-db-pull-push';
	protected static $title = 'Wp Sync Db Pull Push';
	protected static $description = 'Wp Sync Db Pull Push issue detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WPSDB_Utils' ) && ! function_exists( 'wpsdb_setup' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify connection profiles are secured
		$profiles = get_option( 'wpsdb_profiles', array() );
		if ( ! empty( $profiles ) ) {
			foreach ( $profiles as $profile ) {
				if ( isset( $profile['connection_info'] ) && strpos( $profile['connection_info'], 'http://' ) === 0 ) {
					$issues[] = 'Connection profile using insecure HTTP';
					break;
				}
			}
		}
		
		// Check 2: Check for remote key security
		$remote_key = get_option( 'wpsdb_remote_key', '' );
		if ( empty( $remote_key ) || strlen( $remote_key ) < 32 ) {
			$issues[] = 'Remote key not properly configured or too weak';
		}
		
		// Check 3: Verify table backup before sync
		$backup_option = get_option( 'wpsdb_backup_option', '' );
		if ( $backup_option !== 'backup' ) {
			$issues[] = 'Database backup not enabled before sync operations';
		}
		
		// Check 4: Check for find/replace validation
		$find_replace = get_option( 'wpsdb_find_replace', array() );
		if ( empty( $find_replace ) ) {
			$issues[] = 'Find/replace rules not configured (URL changes may break site)';
		}
		
		// Check 5: Verify table selection
		$selected_tables = get_option( 'wpsdb_select_tables', 'all' );
		if ( $selected_tables === 'all' ) {
			$issues[] = 'All tables selected (consider excluding sensitive tables)';
		}
		
		// Check 6: Check for post-type exclusions
		$exclude_post_types = get_option( 'wpsdb_exclude_post_types', 0 );
		if ( ! $exclude_post_types ) {
			$issues[] = 'Post type exclusions not configured';
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
					'Found %d WP Sync DB issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-sync-db-pull-push',
			);
		}
		
		return null;
	}
}
