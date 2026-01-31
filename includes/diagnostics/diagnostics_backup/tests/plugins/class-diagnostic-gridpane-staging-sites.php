<?php
/**
 * Gridpane Staging Sites Diagnostic
 *
 * Gridpane Staging Sites needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1028.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gridpane Staging Sites Diagnostic Class
 *
 * @since 1.1028.0000
 */
class Diagnostic_GridpaneStagingSites extends Diagnostic_Base {

	protected static $slug = 'gridpane-staging-sites';
	protected static $title = 'Gridpane Staging Sites';
	protected static $description = 'Gridpane Staging Sites needs attention';
	protected static $family = 'functionality';

	public static function check() {
		// Check for GridPane hosting environment
		if ( ! defined( 'GRIDPANE_PLATFORM' ) && ! isset( $_SERVER['HTTP_X_GRIDPANE'] ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Staging environment detection
		$is_staging = defined( 'WP_ENV' ) && 'staging' === WP_ENV;
		$staging_marker = get_option( 'gridpane_staging_site', false );
		
		if ( ! $is_staging && ! $staging_marker ) {
			return null; // Not a staging site
		}
		
		// Check 2: Search engine indexing
		$public = get_option( 'blog_public', 1 );
		if ( $public == 1 ) {
			$issues[] = __( 'Staging site visible to search engines', 'wpshadow' );
		}
		
		// Check 3: Production URL references
		global $wpdb;
		$prod_url = get_option( 'gridpane_production_url', '' );
		
		if ( ! empty( $prod_url ) ) {
			$prod_refs = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_content LIKE %s OR post_excerpt LIKE %s",
					'%' . $wpdb->esc_like( $prod_url ) . '%',
					'%' . $wpdb->esc_like( $prod_url ) . '%'
				)
			);
			
			if ( $prod_refs > 10 ) {
				$issues[] = sprintf( __( '%d references to production URL (search-replace needed)', 'wpshadow' ), $prod_refs );
			}
		}
		
		// Check 4: Database prefix isolation
		if ( ! empty( $prod_url ) && $wpdb->prefix === 'wp_' ) {
			$issues[] = __( 'Staging uses default database prefix (no isolation from production)', 'wpshadow' );
		}
		
		// Check 5: Staging sync status
		$last_sync = get_option( 'gridpane_last_sync', 0 );
		if ( $last_sync > 0 ) {
			$days_since = floor( ( time() - $last_sync ) / 86400 );
			if ( $days_since > 30 ) {
				$issues[] = sprintf( __( 'Staging not synced from production in %d days', 'wpshadow' ), $days_since );
			}
		}
		
		// Check 6: Email sending on staging
		$disable_emails = get_option( 'gridpane_disable_staging_emails', false );
		if ( ! $disable_emails ) {
			$issues[] = __( 'Staging emails not disabled (users may receive test emails)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 65;
		} elseif ( count( $issues ) >= 2 ) {
			$threat_level = 58;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of staging issues */
				__( 'GridPane staging site has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/gridpane-staging-sites',
		);
	}
}
