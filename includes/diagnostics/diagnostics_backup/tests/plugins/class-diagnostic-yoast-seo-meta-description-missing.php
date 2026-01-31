<?php
/**
 * Yoast Seo Meta Description Missing Diagnostic
 *
 * Yoast Seo Meta Description Missing configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.690.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Yoast Seo Meta Description Missing Diagnostic Class
 *
 * @since 1.690.0000
 */
class Diagnostic_YoastSeoMetaDescriptionMissing extends Diagnostic_Base {

	protected static $slug = 'yoast-seo-meta-description-missing';
	protected static $title = 'Yoast Seo Meta Description Missing';
	protected static $description = 'Yoast Seo Meta Description Missing configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'WPSEO_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Posts without meta descriptions
		$missing_desc = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT p.ID) 
				FROM {$wpdb->posts} p 
				LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
				WHERE p.post_status = %s 
				AND p.post_type IN ('post', 'page') 
				AND (pm.meta_value IS NULL OR pm.meta_value = '')",
				'_yoast_wpseo_metadesc',
				'publish'
			)
		);
		
		if ( $missing_desc > 10 ) {
			$issues[] = sprintf( __( '%d posts missing meta descriptions', 'wpshadow' ), $missing_desc );
		}
		
		// Check 2: Auto-generated descriptions
		$auto_generate = get_option( 'wpseo_titles' );
		if ( isset( $auto_generate['metadesc-post'] ) && ! empty( $auto_generate['metadesc-post'] ) ) {
			$issues[] = __( 'Auto-generated descriptions (generic, not optimized)', 'wpshadow' );
		}
		
		// Check 3: Descriptions too short
		$short_desc = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) 
				FROM {$wpdb->postmeta} pm 
				INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
				WHERE pm.meta_key = %s 
				AND LENGTH(pm.meta_value) < 120
				AND p.post_status = %s",
				'_yoast_wpseo_metadesc',
				'publish'
			)
		);
		
		if ( $short_desc > 5 ) {
			$issues[] = sprintf( __( '%d descriptions <120 characters (too short)', 'wpshadow' ), $short_desc );
		}
		
		// Check 4: Duplicate descriptions
		$duplicates = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM (
					SELECT meta_value FROM {$wpdb->postmeta}
					WHERE meta_key = %s AND meta_value != ''
					GROUP BY meta_value HAVING COUNT(*) > 1
				) AS dupe",
				'_yoast_wpseo_metadesc'
			)
		);
		
		if ( $duplicates > 0 ) {
			$issues[] = sprintf( __( '%d duplicate meta descriptions', 'wpshadow' ), $duplicates );
		}
		
		// Check 5: Cornerstone content without descriptions
		$cornerstone_missing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT p.ID) 
				FROM {$wpdb->posts} p 
				INNER JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = %s AND pm1.meta_value = '1'
				LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = %s
				WHERE p.post_status = %s 
				AND (pm2.meta_value IS NULL OR pm2.meta_value = '')",
				'_yoast_wpseo_is_cornerstone',
				'_yoast_wpseo_metadesc',
				'publish'
			)
		);
		
		if ( $cornerstone_missing > 0 ) {
			$issues[] = sprintf( __( '%d cornerstone posts without descriptions', 'wpshadow' ), $cornerstone_missing );
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
				/* translators: %s: list of meta description issues */
				__( 'Yoast SEO has %d meta description issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/yoast-seo-meta-description-missing',
		);
	}
}
