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
 * @package ThisIsMyURL\Shadow
 * @since   0.6095
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Treatments;

use ThisIsMyURL\Shadow\Core\Treatment_Base;

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
				'message' => __( 'Default category could not be found.', 'thisismyurl-shadow' ),
			);
		}

		// Store original for undo().
		static::save_backup_value(
			'thisismyurl_shadow_default_cat_prev',
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
					__( 'Could not rename the category: %s', 'thisismyurl-shadow' ),
					$result->get_error_message()
				),
			);
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %s: Original category name */
				__( 'Default category renamed from "%s" to "General".', 'thisismyurl-shadow' ),
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
		$loaded = static::load_backup_array( 'thisismyurl_shadow_default_cat_prev', array( 'id', 'name', 'slug' ), true );
		$prev   = $loaded['value'];

		if ( ! $loaded['found'] || ! is_array( $prev ) ) {
			return array(
				'success' => false,
				'message' => __( 'No stored category data to restore.', 'thisismyurl-shadow' ),
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
					__( 'Could not restore the category: %s', 'thisismyurl-shadow' ),
					$result->get_error_message()
				),
			);
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %s: Restored category name */
				__( 'Default category restored to "%s".', 'thisismyurl-shadow' ),
				esc_html( $prev['name'] )
			),
		);
	}
}
