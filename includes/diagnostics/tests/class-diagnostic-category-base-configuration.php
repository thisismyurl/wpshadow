<?php
/**
 * Category Base Configuration Diagnostic
 *
 * Verifies category URL structure is properly configured for SEO.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1900
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Category Base Configuration Diagnostic Class
 *
 * Checks category URL structure (slug) configuration for optimal SEO.
 *
 * @since 1.26032.1900
 */
class Diagnostic_Category_Base_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'category-base-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Category Base Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies category URL structure configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'reading';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26032.1900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if categories are enabled.
		if ( ! taxonomy_exists( 'category' ) ) {
			return null; // Categories disabled.
		}

		// Get category base setting.
		$category_base = get_option( 'category_base', 'category' );

		if ( empty( $category_base ) ) {
			$issues[] = __( 'Category base is empty - using default "category" prefix', 'wpshadow' );
		}

		// Check for common SEO issues.
		if ( $category_base === 'category' ) {
			// Default is acceptable but not customized.
			return null;
		}

		// Check if category base contains special characters or spaces.
		if ( preg_match( '/[^a-z0-9-_]/', $category_base ) ) {
			$issues[] = sprintf(
				/* translators: %s: category base */
				__( 'Category base "%s" contains invalid characters - may cause URL issues', 'wpshadow' ),
				esc_attr( $category_base )
			);
		}

		// Check for conflicts with other permalinks.
		global $wp_rewrite;
		if ( isset( $wp_rewrite->tag_base ) && $wp_rewrite->tag_base === $category_base ) {
			$issues[] = __( 'Category base conflicts with tag base - both use same URL structure', 'wpshadow' );
		}

		// Check category count.
		$category_count = wp_count_terms( array( 'taxonomy' => 'category' ) );
		if ( $category_count > 500 ) {
			$issues[] = sprintf(
				/* translators: %d: number of categories */
				__( 'Large number of categories (%d) may impact performance', 'wpshadow' ),
				$category_count
			);
		}

		// Check for empty categories.
		global $wpdb;
		$empty_categories = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->term_taxonomy}
			WHERE taxonomy = 'category' AND count = 0"
		);

		if ( $empty_categories > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number of empty categories */
				__( 'Found %d empty categories - may clutter site structure', 'wpshadow' ),
				$empty_categories
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/category-base-configuration',
			);
		}

		return null;
	}
}
