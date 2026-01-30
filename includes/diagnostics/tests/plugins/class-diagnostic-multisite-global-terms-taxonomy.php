<?php
/**
 * Multisite Global Terms Taxonomy Diagnostic
 *
 * Multisite Global Terms Taxonomy misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.949.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Global Terms Taxonomy Diagnostic Class
 *
 * @since 1.949.0000
 */
class Diagnostic_MultisiteGlobalTermsTaxonomy extends Diagnostic_Base {

	protected static $slug = 'multisite-global-terms-taxonomy';
	protected static $title = 'Multisite Global Terms Taxonomy';
	protected static $description = 'Multisite Global Terms Taxonomy misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Global terms enabled (deprecated feature)
		$global_terms = get_site_option( 'global_terms_enabled', 0 );
		if ( $global_terms ) {
			$issues[] = __( 'Global terms enabled (deprecated WordPress feature)', 'wpshadow' );
		}
		
		// Check 2: Term ID conflicts across sites
		$site_count = get_blog_count();
		if ( $site_count > 1 ) {
			$sites = get_sites( array( 'number' => 5 ) );
			$term_conflicts = 0;
			
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				$term_count = wp_count_terms( array( 'taxonomy' => 'category', 'hide_empty' => false ) );
				if ( is_wp_error( $term_count ) ) {
					$term_conflicts++;
				}
				restore_current_blog();
			}
			
			if ( $term_conflicts > 0 ) {
				$issues[] = sprintf( __( '%d sites with term taxonomy issues', 'wpshadow' ), $term_conflicts );
			}
		}
		
		// Check 3: Shared taxonomy configuration
		$shared_taxonomies = get_site_option( 'shared_taxonomies', array() );
		if ( count( $shared_taxonomies ) > 0 ) {
			$issues[] = sprintf( __( '%d taxonomies configured for sharing (consistency risk)', 'wpshadow' ), count( $shared_taxonomies ) );
		}
		
		// Check 4: Term meta in multisite
		$term_meta_table = $wpdb->prefix . 'termmeta';
		$term_meta_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$term_meta_table}" );
		
		if ( $term_meta_count > 10000 ) {
			$issues[] = sprintf( __( '%s term meta rows (potential bloat)', 'wpshadow' ), number_format_i18n( $term_meta_count ) );
		}
		
		// Check 5: Cross-site term references
		$cross_site_refs = get_site_option( 'ms_cross_site_term_refs', false );
		if ( $cross_site_refs ) {
			$issues[] = __( 'Cross-site term references enabled (data integrity risk)', 'wpshadow' );
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
				/* translators: %s: list of taxonomy issues */
				__( 'Multisite global terms has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/multisite-global-terms-taxonomy',
		);
	}
}
