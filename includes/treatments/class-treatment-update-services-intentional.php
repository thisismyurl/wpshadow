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
				'message' => __( 'Update Services is already empty. No changes made.', 'wpshadow' ),
			);
		}

		static::save_backup_value( 'wpshadow_ping_sites_prev', $current );
		update_option( 'ping_sites', '' );

		return array(
			'success' => true,
			'message' => __( 'Update Services cleared. WordPress will stop pinging external blog aggregators on publish.', 'wpshadow' ),
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
			'wpshadow_ping_sites_prev',
			__( 'No previous Update Services value was stored.', 'wpshadow' ),
			__( 'Update Services restored to the previous configured value.', 'wpshadow' )
		);
	}
}