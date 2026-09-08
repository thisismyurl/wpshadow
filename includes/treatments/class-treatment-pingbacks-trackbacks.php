<?php
/**
 * Treatment: Disable Pingbacks and Trackbacks
 *
 * Sets default_ping_status to 'closed' and default_pingback_flag to 0.
 * This prevents new posts from accepting pingbacks or sending trackback
 * notifications by default. Existing posts are unaffected (see details).
 *
 * Note: this treatment only changes the default for new posts. Closing
 * pingbacks on all existing posts requires a bulk database update which is
 * presented as a separate recommendation in the finding details.
 *
 * Risk level: safe — two option updates, fully reversible.
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
 * Disables pingbacks and trackbacks for new posts.
 */
class Treatment_Pingbacks_Trackbacks extends Treatment_Base {

	/**
	 * @var string
	 */
	protected static $slug = 'pingbacks-trackbacks';

	/** @return string */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Set default_ping_status to 'closed' and default_pingback_flag to 0.
	 *
	 * @return array
	 */
	public static function apply() {
		$prev_ping_status    = get_option( 'default_ping_status', 'open' );
		$prev_pingback_flag  = get_option( 'default_pingback_flag', 1 );

		update_option( 'wpshadow_prev_default_ping_status', $prev_ping_status, false );
		update_option( 'wpshadow_prev_default_pingback_flag', $prev_pingback_flag, false );

		update_option( 'default_ping_status', 'closed' );
		update_option( 'default_pingback_flag', 0 );

		return array(
			'success' => true,
			'message' => __( 'Pingbacks and trackbacks disabled for new posts. Existing posts are unaffected — visit Settings → Discussion to bulk-close if needed.', 'wpshadow' ),
			'details' => array(
				'previous_ping_status'   => $prev_ping_status,
				'previous_pingback_flag' => $prev_pingback_flag,
			),
		);
	}

	/**
	 * Restore previous pingback defaults.
	 *
	 * @return array
	 */
	public static function undo() {
		$prev_ping_status   = get_option( 'wpshadow_prev_default_ping_status' );
		$prev_pingback_flag = get_option( 'wpshadow_prev_default_pingback_flag' );

		if ( false !== $prev_ping_status ) {
			update_option( 'default_ping_status', $prev_ping_status );
		}

		if ( false !== $prev_pingback_flag ) {
			update_option( 'default_pingback_flag', $prev_pingback_flag );
		}

		delete_option( 'wpshadow_prev_default_ping_status' );
		delete_option( 'wpshadow_prev_default_pingback_flag' );

		return array(
			'success' => true,
			'message' => __( 'Pingback and trackback defaults restored.', 'wpshadow' ),
		);
	}
}
