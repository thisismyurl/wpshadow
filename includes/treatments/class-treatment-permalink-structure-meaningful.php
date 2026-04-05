<?php
/**
 * Treatment: Switch to a meaningful permalink structure
 *
 * Plain or numeric permalink structures provide poor URLs for users and search
 * engines. This treatment changes the permalink structure to /%postname%/
 * and flushes rewrite rules so the new structure takes effect immediately.
 *
 * Undo: restores the previous permalink_structure value and flushes rewrite rules.
 *
 * @package WPShadow
 * @since   0.7056
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets permalink_structure to /%postname%/.
 */
class Treatment_Permalink_Structure_Meaningful extends Treatment_Base {

	/** @var string */
	protected static $slug = 'permalink-structure-meaningful';

	/** @return string */
	public static function get_risk_level(): string {
		return 'moderate';
	}

	/**
	 * Switch to /%postname%/ and flush rewrite rules.
	 *
	 * @return array
	 */
	public static function apply(): array {
		$current = (string) get_option( 'permalink_structure', '' );

		if ( '/%postname%/' === $current ) {
			return array(
				'success' => true,
				'message' => __( 'Permalink structure is already set to /%postname%/. No changes made.', 'wpshadow' ),
			);
		}

		static::save_backup_value( 'wpshadow_permalink_structure_prev', $current );
		update_option( 'permalink_structure', '/%postname%/' );

		if ( function_exists( 'flush_rewrite_rules' ) ) {
			flush_rewrite_rules( false );
		}

		return array(
			'success' => true,
			'message' => __( 'Permalink structure changed to /%postname%/ and rewrite rules were refreshed.', 'wpshadow' ),
		);
	}

	/**
	 * Restore the previous permalink structure.
	 *
	 * @return array
	 */
	public static function undo(): array {
		$loaded = static::load_backup_value( 'wpshadow_permalink_structure_prev', true );

		if ( ! $loaded['found'] ) {
			return array(
				'success' => false,
				'message' => __( 'No previous permalink structure was stored.', 'wpshadow' ),
			);
		}

		update_option( 'permalink_structure', (string) $loaded['value'] );

		if ( function_exists( 'flush_rewrite_rules' ) ) {
			flush_rewrite_rules( false );
		}

		return array(
			'success' => true,
			'message' => __( 'Permalink structure restored to its previous value and rewrite rules were refreshed.', 'wpshadow' ),
		);
	}
}