<?php
/**
 * Multisite Disk Space Enforcement Diagnostic
 *
 * Multisite Disk Space Enforcement misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.972.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Disk Space Enforcement Diagnostic Class
 *
 * @since 1.972.0000
 */
class Diagnostic_MultisiteDiskSpaceEnforcement extends Diagnostic_Base {

	protected static $slug = 'multisite-disk-space-enforcement';
	protected static $title = 'Multisite Disk Space Enforcement';
	protected static $description = 'Multisite Disk Space Enforcement misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Upload space quota enabled
		$upload_space = get_site_option( 'upload_space_check_disabled', 1 );
		if ( $upload_space ) {
			$issues[] = __( 'Upload space quota checking disabled', 'wpshadow' );
		}
		
		// Check 2: Default blog upload space
		$blog_upload_space = get_site_option( 'blog_upload_space', 100 );
		if ( $blog_upload_space === 0 ) {
			$issues[] = __( 'No default upload space limit (unlimited storage per site)', 'wpshadow' );
		} elseif ( $blog_upload_space > 5000 ) {
			$issues[] = sprintf( __( 'Very high default upload limit: %d MB per site', 'wpshadow' ), $blog_upload_space );
		}
		
		// Check 3: Sites exceeding quota
		$sites = get_sites( array( 'number' => 1000 ) );
		$over_quota_count = 0;
		
		foreach ( $sites as $site ) {
			$space_used = get_space_used( $site->blog_id );
			$space_allowed = get_space_allowed( $site->blog_id );
			
			if ( $space_allowed > 0 && $space_used > $space_allowed ) {
				$over_quota_count++;
			}
		}
		
		if ( $over_quota_count > 0 ) {
			$issues[] = sprintf( __( '%d sites exceeding upload quota', 'wpshadow' ), $over_quota_count );
		}
		
		// Check 4: Upload file size limits
		$fileupload_maxk = get_site_option( 'fileupload_maxk', 1500 );
		if ( $fileupload_maxk > 10240 ) { // 10MB
			$issues[] = sprintf( __( 'High max upload size: %d KB (abuse potential)', 'wpshadow' ), $fileupload_maxk );
		}
		
		// Check 5: Disk space monitoring
		$monitor_space = get_site_option( 'ms_monitor_disk_space', false );
		if ( ! $monitor_space ) {
			$issues[] = __( 'Disk space monitoring not enabled', 'wpshadow' );
		}
		
		
		// Check 6: Feature initialization
		if ( ! (get_option( "features_init" ) !== false) ) {
			$issues[] = __( 'Feature initialization', 'wpshadow' );
		}

		// Check 7: Database tables
		if ( ! (! empty( $GLOBALS["wpdb"] )) ) {
			$issues[] = __( 'Database tables', 'wpshadow' );
		}

		// Check 8: Hook registration
		if ( ! (has_action( "init" )) ) {
			$issues[] = __( 'Hook registration', 'wpshadow' );
		}
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = (40 + min(35, count($issues) * 8));
		if ( count( $issues ) >= 4 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of disk space issues */
				__( 'Multisite disk space enforcement has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/multisite-disk-space-enforcement',
		);
	}
}
