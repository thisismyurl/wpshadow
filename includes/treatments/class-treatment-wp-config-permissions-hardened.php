<?php
/**
 * Treatment: wp-config Permissions Hardened
 *
 * Changes wp-config.php file permissions to 0600 (owner read/write only).
 * This prevents other system users and web-server worker processes from reading
 * the file, which contains database credentials and secret keys.
 *
 * The original permissions are saved to a WP option before the change so that
 * `undo()` can restore them exactly.
 *
 * Requirement: The PHP process running WordPress must own wp-config.php or have
 * sufficient OS-level privileges to call chmod(). On managed hosts this is
 * usually satisfied. If chmod() fails, the method returns a descriptive error
 * and no change is made.
 *
 * @package WPShadow
 * @subpackage Treatments
 * @since 0.6095
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restricts wp-config.php to octal 0600.
 */
class Treatment_Wp_Config_Permissions_Hardened extends Treatment_Base {

	/** @var string */
	protected static $slug = 'wp-config-permissions-hardened';

	const OPTION_KEY      = 'wpshadow_wp_config_perms_backup';
	const TARGET_MODE     = 0600;
	const TARGET_MODE_STR = '0600';

	// =========================================================================
	// Treatment_Base contract
	// =========================================================================

	public static function get_finding_id(): string {
		return self::$slug;
	}

	public static function get_risk_level(): string {
		return 'medium';
	}

	/**
	 * Save current permissions and set wp-config.php to 0600.
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function apply(): array {
		$path = ABSPATH . 'wp-config.php';

		if ( ! file_exists( $path ) ) {
			return [
				'success' => false,
				'message' => __( 'wp-config.php not found at the expected path.', 'wpshadow' ),
			];
		}

		$current = (int) ( fileperms( $path ) & 0777 );

		// Already at target?
		if ( self::TARGET_MODE === $current ) {
			update_option( self::OPTION_KEY, $current, false );
			return [
				'success' => true,
				'message' => __( 'wp-config.php is already set to 0600 — no change needed.', 'wpshadow' ),
			];
		}

		// Back up original permissions before changing.
		update_option( self::OPTION_KEY, $current, false );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_chmod
		if ( ! chmod( $path, self::TARGET_MODE ) ) {
			return [
				'success' => false,
				'message' => sprintf(
					/* translators: %s: file path */
					__( 'chmod() failed on %s. The PHP process may not own the file. Check file ownership or apply permissions manually via SFTP.', 'wpshadow' ),
					$path
				),
			];
		}

		// Clear the stat cache so the next fileperms() call reflects the change.
		clearstatcache( true, $path );

		return [
			'success' => true,
			'message' => sprintf(
				/* translators: 1: old mode, 2: new mode, 3: file path */
				__( 'wp-config.php permissions changed from %1$s to %2$s (%3$s).', 'wpshadow' ),
				sprintf( '%04o', $current ),
				self::TARGET_MODE_STR,
				$path
			),
		];
	}

	/**
	 * Restore original permissions saved during apply().
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function undo(): array {
		$path   = ABSPATH . 'wp-config.php';
		$backed = get_option( self::OPTION_KEY, null );

		if ( null === $backed ) {
			return [
				'success' => false,
				'message' => __( 'No backup permissions found — cannot restore. You may set permissions manually via SFTP.', 'wpshadow' ),
			];
		}

		$old_mode = (int) $backed;

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_chmod
		if ( file_exists( $path ) && ! chmod( $path, $old_mode ) ) {
			return [
				'success' => false,
				'message' => __( 'chmod() failed when restoring original permissions.', 'wpshadow' ),
			];
		}

		delete_option( self::OPTION_KEY );
		clearstatcache( true, $path );

		return [
			'success' => true,
			'message' => sprintf(
				/* translators: %s: restored octal mode */
				__( 'wp-config.php permissions restored to %s.', 'wpshadow' ),
				sprintf( '%04o', $old_mode )
			),
		];
	}
}
