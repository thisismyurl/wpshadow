<?php
/**
 * Treatment: Clear WordPress update services
 *
 * On brochure-style sites that rarely publish posts, keeping blog ping/update
 * services configured adds little value and leaks publish activity to third
 * parties. This treatment clears the native ping_sites option.
 *
 * Undo: restores the previous ping_sites value.
 *
 * @package ThisIsMyURL\Shadow
 * @since   0.7056
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Treatments;

use ThisIsMyURL\Shadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clears the ping_sites list.
 */
class Treatment_Update_Services_Intentional extends Treatment_Base {

	/** @var string */
	protected static $slug = 'update-services-intentional';

	/** @return string */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Clear ping_sites.
	 *
	 * @return array
	 */
	public static function apply(): array {
		$current = trim( (string) get_option( 'ping_sites', '' ) );

		if ( '' === $current ) {
			return array(
				'success' => true,
				'message' => __( 'Update Services is already empty. No changes made.', 'thisismyurl-shadow' ),
			);
		}

		static::save_backup_value( 'thisismyurl_shadow_ping_sites_prev', $current );
		update_option( 'ping_sites', '' );

		return array(
			'success' => true,
			'message' => __( 'Update Services cleared. WordPress will stop pinging external blog aggregators on publish.', 'thisismyurl-shadow' ),
		);
	}

	/**
	 * Restore the previous ping_sites value.
	 *
	 * @return array
	 */
	public static function undo(): array {
		return static::restore_option_from_backup(
			'ping_sites',
			'thisismyurl_shadow_ping_sites_prev',
			__( 'No previous Update Services value was stored.', 'thisismyurl-shadow' ),
			__( 'Update Services restored to the previous configured value.', 'thisismyurl-shadow' )
		);
	}
}