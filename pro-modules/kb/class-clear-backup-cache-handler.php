<?php
/**
 * Clear Backup Cache AJAX Handler
 *
 * Clears the backup timestamp transient to force refresh.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow_Pro\Modules\KB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle backup cache clear requests.
 */
class Clear_Backup_Cache_Handler {

	/**
	 * Register AJAX action.
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_clear_backup_cache', [ __CLASS__, 'handle' ] );
		add_action( 'wp_ajax_nopriv_wpshadow_clear_backup_cache', [ __CLASS__, 'handle' ] );
	}

	/**
	 * Handle the AJAX request.
	 */
	public static function handle(): void {
		// Verify nonce
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		// Clear transient
		delete_transient( 'wpshadow_last_backup_time' );

		wp_send_json_success( [ 'message' => 'Cache cleared' ] );
	}
}

// Initialization happens from module.php, no auto-registration needed
