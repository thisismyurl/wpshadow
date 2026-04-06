<?php
/**
 * Treatment: Rename category with slug "uncategorized" to "General"
 *
 * The "Uncategorized" category appears in post URLs, RSS feeds, and sitemaps.
 * Leaving it in place signals the site was never properly configured and
 * hurts SEO with zero-context taxonomy terms. This treatment renames the
 * default category (or any remaining "uncategorized" slug) to "General".
 *
 * Undo: restores the previous name and slug.
 *
 * @package WPShadow
 * @since   0.6095
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renames the "uncategorized" default category slug to "general".
 */
class Treatment_Uncategorized_Usage extends Treatment_Base {

	/** @var string */
	protected static $slug = 'uncategorized-usage';

	/** @return string */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Rename the default category away from "uncategorized".
	 *
	 * @return array
	 */
	public static function apply(): array {
		$cat_id = (int) get_option( 'default_category', 1 );
		$term   = get_term( $cat_id, 'category' );

		if ( is_wp_error( $term ) || ! $term ) {
			// Fall back to a direct slug lookup.
			$term = get_term_by( 'slug', 'uncategorized', 'category' );
		}

		if ( ! $term || is_wp_error( $term ) ) {
			return array(
				'success' => true,
				'message' => __( 'No "uncategorized" category found — it may have been renamed already.', 'wpshadow' ),
			);
		}

		// Store original for undo().
		static::save_backup_value(
			'wpshadow_uncategorized_prev',
			array(
				'id'   => (int) $term->term_id,
				'name' => $term->name,
				'slug' => $term->slug,
			)
		);

		$result = wp_update_term(
			(int) $term->term_id,
			'category',
			array(
				'name' => 'General',
				'slug' => 'general',
			)
		);

		if ( is_wp_error( $result ) ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %s: WP_Error message */
					__( 'Could not rename the category: %s', 'wpshadow' ),
					$result->get_error_message()
				),
			);
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %s: Original category name */
				__( 'Category "%s" renamed to "General" (slug: general).', 'wpshadow' ),
				esc_html( $term->name )
			),
		);
	}

	/**
	 * Restore the previous category name and slug.
	 *
	 * @return array
	 */
	public static function undo(): array {
		$loaded = static::load_backup_array( 'wpshadow_uncategorized_prev', array( 'id', 'name', 'slug' ), true );
		$prev   = $loaded['value'];

		if ( ! $loaded['found'] || ! is_array( $prev ) ) {
			return array(
				'success' => false,
				'message' => __( 'No stored category data to restore.', 'wpshadow' ),
			);
		}

		$result = wp_update_term(
			(int) $prev['id'],
			'category',
			array(
				'name' => $prev['name'],
				'slug' => $prev['slug'],
			)
		);

		if ( is_wp_error( $result ) ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %s: WP_Error message */
					__( 'Could not restore the category: %s', 'wpshadow' ),
					$result->get_error_message()
				),
			);
		}

		return array(
			'success' => true,
			'message' => sprintf(
						/* translators: 1: restored category name, 2: restored category slug. */
						__( 'Category restored to "%1$s" (slug: %2$s).', 'wpshadow' ),
				esc_html( $prev['name'] ),
				esc_html( $prev['slug'] )
			),
		);
	}
}
