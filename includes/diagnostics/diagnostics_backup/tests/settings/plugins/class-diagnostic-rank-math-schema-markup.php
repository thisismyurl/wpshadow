<?php
/**
 * Rank Math Schema Markup Diagnostic
 *
 * Rank Math Schema Markup configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.694.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rank Math Schema Markup Diagnostic Class
 *
 * @since 1.694.0000
 */
class Diagnostic_RankMathSchemaMarkup extends Diagnostic_Base {

	protected static $slug = 'rank-math-schema-markup';
	protected static $title = 'Rank Math Schema Markup';
	protected static $description = 'Rank Math Schema Markup configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'RANK_MATH_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		$threat_level = 0;

		// Check if schema module is enabled
		$schema_enabled = get_option( 'rank_math_modules', array() );
		if ( ! in_array( 'rich-snippet', $schema_enabled, true ) ) {
			$issues[] = 'schema_module_disabled';
			$threat_level += 20;
		}

		// Check default schema type
		$default_schema = get_option( 'rank_math_snippet_type', '' );
		if ( empty( $default_schema ) ) {
			$issues[] = 'no_default_schema';
			$threat_level += 10;
		}

		// Check organization schema
		$org_name = get_option( 'rank_math_knowledgegraph_name', '' );
		$org_logo = get_option( 'rank_math_knowledgegraph_logo', '' );
		if ( empty( $org_name ) || empty( $org_logo ) ) {
			$issues[] = 'incomplete_organization_schema';
			$threat_level += 15;
		}

		// Check for posts with schema
		$posts_with_schema = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta}
				 WHERE meta_key = %s AND meta_value != ''",
				'rank_math_rich_snippet'
			)
		);
		$total_posts = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts}
				 WHERE post_status = %s AND post_type = %s",
				'publish',
				'post'
			)
		);
		if ( $total_posts > 0 && ( $posts_with_schema / $total_posts ) < 0.5 ) {
			$issues[] = 'low_schema_coverage';
			$threat_level += 10;
		}

		// Check breadcrumb schema
		$breadcrumbs_enabled = get_option( 'rank_math_breadcrumbs', false );
		if ( ! $breadcrumbs_enabled ) {
			$issues[] = 'breadcrumb_schema_disabled';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of schema markup issues */
				__( 'Rank Math schema markup has configuration issues: %s. This reduces rich snippet opportunities and search visibility.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/rank-math-schema-markup',
			);
		}
		
		return null;
	}
}
