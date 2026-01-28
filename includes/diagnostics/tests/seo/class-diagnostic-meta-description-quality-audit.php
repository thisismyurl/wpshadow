<?php
/**
 * Meta Description Quality Audit Diagnostic
 *
 * Validates meta descriptions exist, are unique, and optimized for click-through.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta Description Quality Audit Class
 *
 * Tests meta description quality.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Meta_Description_Quality_Audit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'meta-description-quality-audit';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Meta Description Quality Audit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates meta descriptions exist, are unique, and optimized for click-through';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$audit = self::audit_meta_descriptions();
		
		if ( $audit['total_issues'] > 0 ) {
			$issues = array();
			
			if ( $audit['missing_count'] > 0 ) {
				$issues[] = sprintf(
					/* translators: 1: number missing, 2: percentage */
					__( '%1$d pages (%2$d%%) missing meta descriptions', 'wpshadow' ),
					$audit['missing_count'],
					$audit['missing_percentage']
				);
			}

			if ( $audit['too_short'] > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number too short */
					__( '%d meta descriptions <120 characters (too short)', 'wpshadow' ),
					$audit['too_short']
				);
			}

			if ( $audit['too_long'] > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number too long */
					__( '%d meta descriptions >160 characters (will be truncated)', 'wpshadow' ),
					$audit['too_long']
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/meta-description-quality-audit',
				'meta'         => array(
					'total_pages'         => $audit['total_pages'],
					'missing_count'       => $audit['missing_count'],
					'missing_percentage'  => $audit['missing_percentage'],
					'too_short'           => $audit['too_short'],
					'too_long'            => $audit['too_long'],
					'duplicate_count'     => $audit['duplicate_count'],
				),
			);
		}

		return null;
	}

	/**
	 * Audit meta descriptions.
	 *
	 * @since  1.26028.1905
	 * @return array Audit results.
	 */
	private static function audit_meta_descriptions() {
		global $wpdb;

		$audit = array(
			'total_pages'        => 0,
			'missing_count'      => 0,
			'missing_percentage' => 0,
			'too_short'          => 0,
			'too_long'           => 0,
			'duplicate_count'    => 0,
			'total_issues'       => 0,
		);

		// Detect SEO plugin meta key.
		$meta_key = self::get_meta_description_key();
		
		if ( ! $meta_key ) {
			return $audit; // No SEO plugin, can't check.
		}

		// Get all published posts/pages.
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID
				FROM {$wpdb->posts}
				WHERE post_status = %s
				AND post_type IN ('post', 'page')
				LIMIT 500",
				'publish'
			)
		);

		$audit['total_pages'] = count( $posts );
		$descriptions = array();

		foreach ( $posts as $post ) {
			$meta_desc = get_post_meta( $post->ID, $meta_key, true );
			
			if ( empty( $meta_desc ) ) {
				++$audit['missing_count'];
			} else {
				$length = strlen( $meta_desc );
				$descriptions[] = $meta_desc;

				if ( $length < 120 ) {
					++$audit['too_short'];
				} elseif ( $length > 160 ) {
					++$audit['too_long'];
				}
			}
		}

		// Check for duplicates.
		$unique_descriptions = array_unique( $descriptions );
		$audit['duplicate_count'] = count( $descriptions ) - count( $unique_descriptions );

		// Calculate percentage missing.
		if ( $audit['total_pages'] > 0 ) {
			$audit['missing_percentage'] = round( ( $audit['missing_count'] / $audit['total_pages'] ) * 100 );
		}

		// Count total issues.
		$audit['total_issues'] = $audit['missing_count'] + $audit['too_short'] + $audit['too_long'];

		return $audit;
	}

	/**
	 * Get meta description key for active SEO plugin.
	 *
	 * @since  1.26028.1905
	 * @return string|false Meta key or false.
	 */
	private static function get_meta_description_key() {
		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			return '_yoast_wpseo_metadesc';
		} elseif ( is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) {
			return 'rank_math_description';
		} elseif ( is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ) ) {
			return '_aioseop_description';
		}

		return false;
	}
}
