<?php
/**
 * Multisite Staging Environment Diagnostic
 *
 * Multisite Staging Environment misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.980.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Staging Environment Diagnostic Class
 *
 * @since 1.980.0000
 */
class Diagnostic_MultisiteStagingEnvironment extends Diagnostic_Base {

	protected static $slug = 'multisite-staging-environment';
	protected static $title = 'Multisite Staging Environment';
	protected static $description = 'Multisite Staging Environment misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Staging environment detection
		$is_staging = defined( 'WP_ENVIRONMENT_TYPE' ) && 'staging' === WP_ENVIRONMENT_TYPE;
		$staging_domain = get_option( 'staging_domain', '' );
		
		if ( ! $is_staging && empty( $staging_domain ) ) {
			return null; // Not a staging environment
		}
		
		// Check 2: Database prefix
		global $wpdb;
		if ( strpos( $wpdb->prefix, 'staging_' ) === false && strpos( $wpdb->prefix, 'stg_' ) === false ) {
			$issues[] = __( 'Same DB prefix as production (data collision risk)', 'wpshadow' );
		}
		
		// Check 3: Search/replace validation
		$sr_log = get_option( 'staging_search_replace_log', array() );
		if ( empty( $sr_log ) ) {
			$issues[] = __( 'No search/replace log (URL issues possible)', 'wpshadow' );
		}
		
		// Check 4: Production URL in database
		$production_url = get_site_option( 'production_url', '' );
		if ( ! empty( $production_url ) ) {
			$results = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_content LIKE %s",
					'%' . $wpdb->esc_like( $production_url ) . '%'
				)
			);
			
			if ( $results > 10 ) {
				$issues[] = sprintf( __( '%d posts contain production URLs', 'wpshadow' ), $results );
			}
		}
		
		// Check 5: Noindex status
		$noindex = get_option( 'blog_public', 1 );
		if ( 1 === (int) $noindex ) {
			$issues[] = __( 'Site public (staging should be noindex)', 'wpshadow' );
		}
		
		// Check 6: Email interception
		$intercept_email = get_option( 'staging_intercept_email', 'no' );
		if ( 'no' === $intercept_email ) {
			$issues[] = __( 'Email interception disabled (may email real users)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of staging environment issues */
				__( 'Multisite staging environment has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/multisite-staging-environment',
		);
	}
}
