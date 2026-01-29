<?php
/**
 * Yoast Seo Duplicate Content Diagnostic
 *
 * Yoast Seo Duplicate Content configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.692.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Yoast Seo Duplicate Content Diagnostic Class
 *
 * @since 1.692.0000
 */
class Diagnostic_YoastSeoDuplicateContent extends Diagnostic_Base {

	protected static $slug = 'yoast-seo-duplicate-content';
	protected static $title = 'Yoast Seo Duplicate Content';
	protected static $description = 'Yoast Seo Duplicate Content configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'WPSEO_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		$threat_level = 0;

		// Check duplicate titles
		$duplicate_titles = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM (
					SELECT pm.meta_value, COUNT(*) as cnt
					FROM {$wpdb->postmeta} pm
					INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
					WHERE pm.meta_key = %s 
					AND p.post_status = %s
					AND pm.meta_value != ''
					GROUP BY pm.meta_value
					HAVING cnt > 1
				) as dups",
				'_yoast_wpseo_title',
				'publish'
			)
		);
		if ( $duplicate_titles > 0 ) {
			$issues[] = 'duplicate_seo_titles';
			$threat_level += 15;
		}

		// Check duplicate meta descriptions
		$duplicate_descriptions = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM (
					SELECT pm.meta_value, COUNT(*) as cnt
					FROM {$wpdb->postmeta} pm
					INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
					WHERE pm.meta_key = %s 
					AND p.post_status = %s
					AND pm.meta_value != ''
					GROUP BY pm.meta_value
					HAVING cnt > 1
				) as dups",
				'_yoast_wpseo_metadesc',
				'publish'
			)
		);
		if ( $duplicate_descriptions > 0 ) {
			$issues[] = 'duplicate_meta_descriptions';
			$threat_level += 15;
		}

		// Check noindex configuration
		$options = get_option( 'wpseo_titles', array() );
		$noindex_archives = isset( $options['noindex-archive'] ) ? $options['noindex-archive'] : false;
		if ( ! $noindex_archives ) {
			$issues[] = 'archive_pages_indexable';
			$threat_level += 10;
		}

		// Check for pagination canonical issues
		$disable_pagination_canonical = get_option( 'wpseo_pagination_canonical_disabled', false );
		if ( $disable_pagination_canonical ) {
			$issues[] = 'pagination_canonical_disabled';
			$threat_level += 15;
		}

		// Check category/tag duplicate content
		$noindex_category = isset( $options['noindex-tax-category'] ) ? $options['noindex-tax-category'] : false;
		$noindex_tag = isset( $options['noindex-tax-post_tag'] ) ? $options['noindex-tax-post_tag'] : false;
		if ( ! $noindex_category && ! $noindex_tag ) {
			$issues[] = 'taxonomy_archives_indexable';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of duplicate content issues */
				__( 'Yoast SEO duplicate content issues detected: %s. This can hurt search rankings due to duplicate content penalties and indexation problems.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/yoast-seo-duplicate-content',
			);
		}
		
		return null;
	}
}
