<?php
/**
 * Empty Taxonomy Terms
 *
 * Identifies categories, tags, and custom taxonomy terms with no posts,
 * creating navigation clutter and poor user experience.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since      1.6028.1047
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Empty Taxonomy Terms Diagnostic Class
 *
 * Detects taxonomy terms (categories, tags, etc.) with zero posts,
 * which create navigation clutter.
 *
 * @since 1.6028.1047
 */
class Diagnostic_Empty_Taxonomy_Terms extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'empty-taxonomy-terms';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Empty Taxonomy Terms';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies categories, tags, and taxonomy terms with no posts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1047
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_empty_taxonomy_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$empty_terms = self::find_empty_terms();

		if ( empty( $empty_terms ) ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$empty_count = count( $empty_terms );

		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of empty terms */
				__( 'Found %d taxonomy terms with no posts, creating navigation clutter.', 'wpshadow' ),
				$empty_count
			),
			'severity'     => $empty_count >= 20 ? 'medium' : 'low',
			'threat_level' => min( 40, 20 + $empty_count ),
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/empty-taxonomy-terms',
			'meta'         => array(
				'empty_count' => $empty_count,
				'empty_terms' => array_slice( $empty_terms, 0, 15 ),
			),
			'details'      => array(
				__( 'Empty terms create clutter in navigation menus and archives', 'wpshadow' ),
				__( 'Can confuse users and search engines', 'wpshadow' ),
				__( 'Indicates poor taxonomy management', 'wpshadow' ),
			),
			'recommendation' => __( 'Delete unused taxonomy terms or assign posts to them.', 'wpshadow' ),
		);

		set_transient( $cache_key, $finding, 24 * HOUR_IN_SECONDS );
		return $finding;
	}

	/**
	 * Find empty taxonomy terms.
	 *
	 * @since  1.6028.1047
	 * @return array Array of empty terms.
	 */
	private static function find_empty_terms() {
		$taxonomies = get_taxonomies( array( 'public' => true ), 'names' );
		$empty      = array();

		foreach ( $taxonomies as $taxonomy ) {
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
				)
			);

			if ( is_wp_error( $terms ) || empty( $terms ) ) {
				continue;
			}

			foreach ( $terms as $term ) {
				if ( 0 === $term->count ) {
					$empty[] = array(
						'term_id'  => $term->term_id,
						'name'     => $term->name,
						'taxonomy' => $taxonomy,
						'slug'     => $term->slug,
					);
				}
			}
		}

		return $empty;
	}
}
