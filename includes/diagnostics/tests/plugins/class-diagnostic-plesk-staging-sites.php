<?php
/**
 * Plesk Staging Sites Diagnostic
 *
 * Plesk Staging Sites needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1035.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plesk Staging Sites Diagnostic Class
 *
 * @since 1.1035.0000
 */
class Diagnostic_PleskStagingSites extends Diagnostic_Base {

	protected static $slug = 'plesk-staging-sites';
	protected static $title = 'Plesk Staging Sites';
	protected static $description = 'Plesk Staging Sites needs attention';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'PLESK_STAGING_VERSION' ) && ! is_dir( '/usr/local/psa' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Staging site identifier
		$is_staging = get_option( 'plesk_staging_site', '0' );
		if ( '1' === $is_staging ) {
			$staging_prefix = get_option( 'plesk_staging_prefix', '' );
			if ( empty( $staging_prefix ) ) {
				$issues[] = 'staging site without identifier prefix';
			}
		}
		
		// Check 2: Database sync configuration
		$db_sync = get_option( 'plesk_staging_db_sync', 'manual' );
		if ( '1' === $is_staging && 'auto' === $db_sync ) {
			$issues[] = 'automatic database sync enabled (may overwrite production)';
		}
		
		// Check 3: Search engine indexing
		if ( '1' === $is_staging ) {
			$blog_public = get_option( 'blog_public', '1' );
			if ( '1' === $blog_public ) {
				$issues[] = 'staging site visible to search engines';
			}
		}
		
		// Check 4: Production URL references
		if ( '1' === $is_staging ) {
			$site_url = get_option( 'siteurl', '' );
			$home_url = get_option( 'home', '' );
			if ( false === strpos( $site_url, 'staging' ) && false === strpos( $home_url, 'staging' ) ) {
				$issues[] = 'URLs do not indicate staging environment';
			}
		}
		
		// Check 5: File sync direction
		$file_sync = get_option( 'plesk_staging_file_sync', 'none' );
		if ( 'bidirectional' === $file_sync ) {
			$issues[] = 'bidirectional file sync (staging changes may affect production)';
		}
		
		// Check 6: Staging age
		$staging_created = get_option( 'plesk_staging_created', 0 );
		if ( ! empty( $staging_created ) ) {
			$days_old = round( ( time() - $staging_created ) / DAY_IN_SECONDS );
			if ( $days_old > 90 ) {
				$issues[] = "staging site {$days_old} days old (consider refreshing)";
			}
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Plesk staging site issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/plesk-staging-sites',
			);
		}
		
		return null;
	}
}
