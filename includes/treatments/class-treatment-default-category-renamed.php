<?php
/**
 * Treatment: Rename the default "Uncategorized" category
 *
 * WordPress ships with a default category named "Uncategorized". Shipping
 * posts under that name looks unpolished and implies no editorial effort.
 * This treatment renames the site's default category to "General" (with slug
 * "general") if it still carries the default "Uncategorized" / "uncategorized"
 * name and slug.
 *
 * Undo: restores the original name and slug.
 *
 * @package WPShadow
 * @since   0.6093.1900
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renames the default WordPress "Uncategorized" category to "General".
 */
class Treatment_Default_Category_Renamed extends Treatment_Base {

	/** @var string */
	protected static $slug = 'default-category-renamed';

	/** @return string */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Rename the default category from "Uncategorized" to "General".
	 *
	 * @return array
	 */
	public static function apply(): array {
		$cat_id = (int) get_option( 'default_category', 1 );
		$term   = get_term( $cat_id, 'category' );

		if ( is_wp_error( $term ) || ! $term ) {
			return array(
				'success' => false,
				'message' => __( 'Default category could not be found.', 'wpshadow' ),
			);
		}

		// Store original for undo().
		static::save_backup_value(
			'wpshadow_default_cat_prev',
			array(
				'id'   => $cat_id,
				'name' => $term->name,
				'slug' => $term->slug,
			)
		);

		$result = wp_update_term(
			$cat_id,
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
				__( 'Default category renamed from "%s" to "General".', 'wpshadow' ),
				esc_html( $term->name )
			),
		);
	}

	/**
	 * Restore the original category name and slug.
	 *
	 * @return array
	 */
	public static function undo(): array {
		$loaded = static::load_backup_array( 'wpshadow_default_cat_prev', array( 'id', 'name', 'slug' ), true );
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
				/* translators: %s: Restored category name */
				__( 'Default category restored to "%s".', 'wpshadow' ),
				esc_html( $prev['name'] )
			),
		);
	}
}
