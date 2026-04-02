<?php
/**
 * Taxonomy Permalink Structure Diagnostic
 *
 * Tests custom taxonomy permalink structures and validates URL rewriting.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Taxonomy Permalink Structure Diagnostic Class
 *
 * Validates that custom taxonomy permalink structures are properly configured
 * and URL rewriting is working correctly.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Taxonomy_Permalink_Structure extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'taxonomy-permalink-structure';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Taxonomy Permalink Structure';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests custom taxonomy permalink structures and validates URL rewriting';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if permalinks are enabled and validates custom taxonomy URL rewriting.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_rewrite;

		// First check if permalinks are enabled at all.
		if ( ! $wp_rewrite || ! $wp_rewrite->using_permalinks() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Permalinks are not enabled. Custom taxonomy URLs will use query strings (?taxonomy=value) instead of clean URLs. Enable permalinks in Settings > Permalinks to improve SEO.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/taxonomy-permalink-structure',
			);
		}

		// Get all custom taxonomies.
		$taxonomies = get_taxonomies(
			array(
				'_builtin' => false,
				'public'   => true,
			),
			'objects'
		);

		// Check if we have any custom taxonomies to test.
		if ( empty( $taxonomies ) ) {
			// No custom taxonomies exist, nothing to check.
			return null;
		}

		// Check for problematic permalink structures with taxonomies.
		$issues = array();

		foreach ( $taxonomies as $taxonomy ) {
			// Check if taxonomy has rewrite rules.
			if ( empty( $taxonomy->rewrite ) ) {
				$issues[] = sprintf(
					/* translators: %s: taxonomy name */
					__( '%s has no rewrite rules configured', 'wpshadow' ),
					$taxonomy->label
				);
				continue;
			}

			// Check if rewrite slug is set.
			if ( is_array( $taxonomy->rewrite ) && empty( $taxonomy->rewrite['slug'] ) ) {
				$issues[] = sprintf(
					/* translators: %s: taxonomy name */
					__( '%s has empty rewrite slug', 'wpshadow' ),
					$taxonomy->label
				);
			}

			// Check for conflicting slugs with built-in structures.
			if ( is_array( $taxonomy->rewrite ) && ! empty( $taxonomy->rewrite['slug'] ) ) {
				$slug = $taxonomy->rewrite['slug'];

				// Check for conflicts with common WordPress structures.
				$reserved_slugs = array( 'page', 'category', 'tag', 'author', 'search', 'feed' );
				if ( in_array( $slug, $reserved_slugs, true ) ) {
					$issues[] = sprintf(
						/* translators: 1: taxonomy name, 2: conflicting slug */
						__( '%1$s uses reserved slug "%2$s" which may conflict with WordPress core', 'wpshadow' ),
						$taxonomy->label,
						$slug
					);
				}
			}
		}

		// Check if rewrite rules need to be flushed.
		$rules = get_option( 'rewrite_rules' );
		if ( empty( $rules ) || ! is_array( $rules ) ) {
			$issues[] = __( 'Rewrite rules are empty and may need to be flushed', 'wpshadow' );
		}

		// If we found issues, return them.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: list of issues found */
					__( 'Custom taxonomy permalink structure issues detected: %s', 'wpshadow' ),
					implode( '; ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/taxonomy-permalink-structure',
			);
		}

		return null;
	}
}
