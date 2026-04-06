<?php
/**
 * Treatment: Add FORCE_SSL_ADMIN to wp-config.php
 *
 * Forces the WordPress login screen and admin panel to run over HTTPS even if
 * the site URL is configured as HTTP. This protects admin credentials and
 * session cookies from being intercepted on networks where the site may
 * still serve HTTP for the front end.
 *
 * Risk level: high — modifies wp-config.php directly. FORCE_SSL_ADMIN should
 * only be applied on servers with a valid TLS certificate. An active HTTPS
 * certificate check is performed before writing.
 *
 * Undo: removes the WPShadow-inserted define block.
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
 * Inserts define('FORCE_SSL_ADMIN', true) into wp-config.php.
 */
class Treatment_Force_Ssl_Admin extends Treatment_Base {

	/** @var string */
	protected static $slug = 'force-ssl-admin';

	private const DEFINE_LINE  = "define( 'FORCE_SSL_ADMIN', true ); // Added by WPShadow";
	private const MARKER_START = '// BEGIN WPShadow FORCE_SSL_ADMIN';
	private const MARKER_END   = '// END WPShadow FORCE_SSL_ADMIN';

	/** @return string */
	public static function get_risk_level(): string {
		return 'high';
	}

	/**
	 * Write FORCE_SSL_ADMIN into wp-config.php.
	 *
	 * @return array
	 */
	public static function apply(): array {
		if ( defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ) {
			return array(
				'success' => true,
				'message' => __( 'FORCE_SSL_ADMIN is already active. No changes made.', 'wpshadow' ),
			);
		}

		// Guard: do not enable if the site is not currently accessed over HTTPS.
		if ( 'https' !== substr( get_option( 'siteurl' ), 0, 5 ) ) {
			return array(
				'success' => false,
				'message' => __( 'Cannot enable FORCE_SSL_ADMIN: the site URL is not using HTTPS. Configure a TLS certificate first to avoid being locked out of the admin area.', 'wpshadow' ),
			);
		}

		$config_path = self::locate_wp_config();

		if ( null === $config_path || ! wp_is_writable( $config_path ) ) {
			return array(
				'success' => false,
				'message' => __( 'wp-config.php could not be located or is not writable. Add define(\'FORCE_SSL_ADMIN\', true); manually.', 'wpshadow' ),
			);
		}

		$original = file_get_contents( $config_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( false === $original ) {
			return array(
				'success' => false,
				'message' => __( 'Could not read wp-config.php.', 'wpshadow' ),
			);
		}

		if ( strpos( $original, self::MARKER_START ) !== false ) {
			return array(
				'success' => true,
				'message' => __( 'FORCE_SSL_ADMIN was already added by WPShadow.', 'wpshadow' ),
			);
		}

		update_option( 'wpshadow_wp_config_backup_force_ssl_admin', base64_encode( $original ), false ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

		$block    = PHP_EOL . self::MARKER_START . PHP_EOL
					. self::DEFINE_LINE . PHP_EOL
					. self::MARKER_END . PHP_EOL;
		$modified = preg_replace( '/^(<\?php\s*)/i', '$1' . $block, $original, 1 );

		if ( null === $modified || $modified === $original ) {
			return array(
				'success' => false,
				'message' => __( 'Could not insert FORCE_SSL_ADMIN into wp-config.php. Please add it manually.', 'wpshadow' ),
			);
		}

		$result = file_put_contents( $config_path, $modified ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

		if ( false === $result ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to write updated wp-config.php. Check file permissions.', 'wpshadow' ),
			);
		}

		return array(
			'success' => true,
			'message' => __( 'FORCE_SSL_ADMIN added to wp-config.php. WordPress login and admin pages now require HTTPS.', 'wpshadow' ),
			'details' => array( 'config_path' => $config_path ),
		);
	}

	/**
	 * Remove the WPShadow FORCE_SSL_ADMIN block from wp-config.php.
	 *
	 * @return array
	 */
	public static function undo(): array {
		$config_path = self::locate_wp_config();

		if ( null === $config_path || ! wp_is_writable( $config_path ) ) {
			return array(
				'success' => false,
				'message' => __( 'wp-config.php could not be located or is not writable.', 'wpshadow' ),
			);
		}

		$current = file_get_contents( $config_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( false === $current ) {
			return array(
				'success' => false,
				'message' => __( 'Could not read wp-config.php.', 'wpshadow' ),
			);
		}

		$pattern  = '/' . preg_quote( PHP_EOL . self::MARKER_START, '/' ) . '.*?' . preg_quote( self::MARKER_END . PHP_EOL, '/' ) . '/s';
		$modified = preg_replace( $pattern, '', $current );

		if ( null !== $modified && $modified !== $current ) {
			$result = file_put_contents( $config_path, $modified ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

			if ( false !== $result ) {
				delete_option( 'wpshadow_wp_config_backup_force_ssl_admin' );
				return array(
					'success' => true,
					'message' => __( 'FORCE_SSL_ADMIN removed from wp-config.php.', 'wpshadow' ),
				);
			}
		}

		$backup_encoded = get_option( 'wpshadow_wp_config_backup_force_ssl_admin' );

		if ( $backup_encoded ) {
			$backup = base64_decode( $backup_encoded ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			if ( false !== $backup ) {
				$result = file_put_contents( $config_path, $backup ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
				if ( false !== $result ) {
					delete_option( 'wpshadow_wp_config_backup_force_ssl_admin' );
					return array(
						'success' => true,
						'message' => __( 'wp-config.php restored from backup. FORCE_SSL_ADMIN removed.', 'wpshadow' ),
					);
				}
			}
		}

		return array(
			'success' => false,
			'message' => __( 'Could not automatically remove FORCE_SSL_ADMIN from wp-config.php. Remove the line manually or restore from your site backup.', 'wpshadow' ),
		);
	}

	/**
	 * Locate wp-config.php.
	 *
	 * @return string|null
	 */
	private static function locate_wp_config(): ?string {
		$candidates = array(
			ABSPATH . 'wp-config.php',
			dirname( ABSPATH ) . '/wp-config.php',
		);

		foreach ( $candidates as $path ) {
			if ( file_exists( $path ) && is_readable( $path ) ) {
				return $path;
			}
		}

		return null;
	}
}
