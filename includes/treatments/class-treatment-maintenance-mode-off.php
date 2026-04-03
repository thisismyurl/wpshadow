<?php
/**
 * Treatment: Disable maintenance mode and coming-soon flags
 *
 * Clears the common maintenance-mode sources checked by the matching
 * diagnostic: the WordPress core `.maintenance` file and a small set of
 * popular plugin options.
 *
 * Undo restores the original option payloads and recreates `.maintenance`
 * when WPShadow removed it.
 *
 * @package WPShadow
 * @since   0.7056.0200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Treatment_Maintenance_Mode_Off extends Treatment_Base {

	/** @var string */
	protected static $slug = 'maintenance-mode-off';

	private const BACKUP_OPTION = 'wpshadow_maintenance_mode_off_backup';

	public static function get_risk_level(): string {
		return 'moderate';
	}

	public static function apply(): array {
		$maintenance_file = ABSPATH . '.maintenance';

		if ( file_exists( $maintenance_file ) && ! is_writable( $maintenance_file ) ) {
			return array(
				'success' => false,
				'message' => __( 'The .maintenance file is not writable. Disable maintenance mode manually or adjust file permissions first.', 'wpshadow' ),
			);
		}

		$backup = array(
			'maintenance_file' => array(
				'exists'   => file_exists( $maintenance_file ),
				'contents' => file_exists( $maintenance_file ) && is_readable( $maintenance_file )
					? base64_encode( (string) file_get_contents( $maintenance_file ) )
					: '',
			),
			'options'          => array(
				'seedprod_page_settings'        => self::capture_option( 'seedprod_page_settings' ),
				'maintenance_mode'              => self::capture_option( 'maintenance_mode' ),
				'wpmm_settings'                 => self::capture_option( 'wpmm_settings' ),
				'colorlib_coming_soon_settings' => self::capture_option( 'colorlib_coming_soon_settings' ),
				'wp_maintenance'                => self::capture_option( 'wp_maintenance' ),
			),
		);

		static::save_backup_value( self::BACKUP_OPTION, $backup );

		$changes = array();

		$seedprod = get_option( 'seedprod_page_settings', null );
		if ( is_array( $seedprod ) && ! empty( $seedprod['enable_maintenance_mode'] ) ) {
			$seedprod['enable_maintenance_mode'] = 0;
			update_option( 'seedprod_page_settings', $seedprod );
			$changes[] = 'SeedProd maintenance mode disabled';
		}

		if ( get_option( 'maintenance_mode', false ) ) {
			update_option( 'maintenance_mode', false );
			$changes[] = 'maintenance_mode option cleared';
		}

		$wpmm = get_option( 'wpmm_settings', null );
		if ( is_array( $wpmm ) && ! empty( $wpmm['general']['status'] ) ) {
			$wpmm['general']['status'] = 0;
			update_option( 'wpmm_settings', $wpmm );
			$changes[] = 'WP Maintenance Mode disabled';
		}

		$colorlib = get_option( 'colorlib_coming_soon_settings', null );
		if ( is_array( $colorlib ) && ! empty( $colorlib['status'] ) && '1' === (string) $colorlib['status'] ) {
			$colorlib['status'] = '0';
			update_option( 'colorlib_coming_soon_settings', $colorlib );
			$changes[] = 'Colorlib coming soon mode disabled';
		}

		$webfactory = get_option( 'wp_maintenance', null );
		if ( is_array( $webfactory ) && ! empty( $webfactory['state'] ) ) {
			$webfactory['state'] = 0;
			update_option( 'wp_maintenance', $webfactory );
			$changes[] = 'WP Maintenance state disabled';
		}

		if ( file_exists( $maintenance_file ) ) {
			if ( ! @unlink( $maintenance_file ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				return array(
					'success' => false,
					'message' => __( 'Maintenance settings were updated, but the .maintenance file could not be removed. Delete it manually.', 'wpshadow' ),
				);
			}
			$changes[] = '.maintenance file removed';
		}

		if ( empty( $changes ) ) {
			return array(
				'success' => true,
				'message' => __( 'Maintenance mode was already off. No changes made.', 'wpshadow' ),
			);
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %s: comma-separated list of maintenance sources disabled */
				__( 'Maintenance mode disabled: %s.', 'wpshadow' ),
				implode( ', ', $changes )
			),
		);
	}

	public static function undo(): array {
		$backup = get_option( self::BACKUP_OPTION );
		if ( ! is_array( $backup ) ) {
			return array(
				'success' => false,
				'message' => __( 'No maintenance-mode backup was stored.', 'wpshadow' ),
			);
		}

		if ( isset( $backup['options'] ) && is_array( $backup['options'] ) ) {
			foreach ( $backup['options'] as $option_name => $snapshot ) {
				self::restore_captured_option( (string) $option_name, $snapshot );
			}
		}

		$maintenance_file = ABSPATH . '.maintenance';
		$file_backup      = isset( $backup['maintenance_file'] ) && is_array( $backup['maintenance_file'] )
			? $backup['maintenance_file']
			: array();

		if ( ! empty( $file_backup['exists'] ) ) {
			$contents = isset( $file_backup['contents'] ) ? base64_decode( (string) $file_backup['contents'] ) : false;
			if ( false !== $contents ) {
				file_put_contents( $maintenance_file, $contents ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			}
		} elseif ( file_exists( $maintenance_file ) ) {
			@unlink( $maintenance_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}

		delete_option( self::BACKUP_OPTION );

		return array(
			'success' => true,
			'message' => __( 'Maintenance-mode settings restored from backup.', 'wpshadow' ),
		);
	}

	private static function capture_option( string $option_name ): array {
		$value = get_option( $option_name, null );
		return array(
			'exists' => false !== get_option( $option_name, false ) || false === $value ? ( null !== $value || false !== get_option( $option_name, false ) ) : true,
			'value'  => $value,
		);
	}

	private static function restore_captured_option( string $option_name, $snapshot ): void {
		if ( ! is_array( $snapshot ) ) {
			return;
		}

		if ( ! empty( $snapshot['exists'] ) ) {
			update_option( $option_name, $snapshot['value'] );
			return;
		}

		delete_option( $option_name );
	}
}