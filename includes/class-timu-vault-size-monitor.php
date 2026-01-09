<?php
/**
 * Vault Size Monitoring - Real-time admin notices for vault size threshold.
 *
 * Displays dismissible admin notices when vault size exceeds configured threshold.
 *
 * @package TIMU_CORE_SUPPORT
 */

declare(strict_types=1);

namespace TIMU\CoreSupport;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vault Size Monitor Class
 *
 * Monitors vault size and displays alerts when thresholds are exceeded.
 */
class TIMU_Vault_Size_Monitor {

	/**
	 * Initialize monitoring.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_notices', array( __CLASS__, 'show_size_alert' ) );
		add_action( 'network_admin_notices', array( __CLASS__, 'show_size_alert' ) );
	}

	/**
	 * Display admin notice when vault size exceeds threshold.
	 *
	 * @return void
	 */
	public static function show_size_alert(): void {
		// Get vault settings.
		if ( ! class_exists( 'TIMU\\CoreSupport\\TIMU_Vault' ) ) {
			return;
		}

		$settings    = TIMU_Vault::get_settings();
		$max_size_mb = (int) ( $settings['max_size_mb'] ?? 0 );

		// If no limit set, skip.
		if ( $max_size_mb <= 0 ) {
			return;
		}

		// Compute current vault size.
		$vault_size_bytes = TIMU_Vault::compute_vault_size_bytes();
		$vault_size_mb    = ceil( $vault_size_bytes / 1048576 );

		// If under threshold, skip.
		if ( $vault_size_mb <= $max_size_mb ) {
			return;
		}

		// Exceeded threshold - show notice and set transient to throttle.
		$settings_url = is_network_admin()
			? network_admin_url( 'admin.php?page=timu-core-network-settings' )
			: admin_url( 'admin.php?page=timu-core-settings' );

		$percentage = round( ( $vault_size_mb / $max_size_mb ) * 100 );

		// Use Notice Manager for persistent dismissal.
		$message = wp_kses_post(
			sprintf(
				/* translators: 1: Current vault size in MB, 2: Threshold in MB, 3: Percentage */
				__( '<strong>Vault Alert:</strong> Storage usage is at %1$s MB of %2$s MB (%3$s%%). <a href="%4$s">Manage retention settings</a> to prevent space issues.', 'wordpress-support-thisismyurl' ),
				esc_html( number_format_i18n( $vault_size_mb ) ),
				esc_html( number_format_i18n( $max_size_mb ) ),
				esc_html( number_format_i18n( $percentage ) ),
				esc_url( $settings_url )
			)
		);

		// Notice key format: type_identifier (type extracted for suppression duration).
		$notice_key = 'warning_vault_size_' . $max_size_mb;

		TIMU_Notice_Manager::render_notice(
			$notice_key,
			$message,
			'warning',
			array( 'capability' => 'manage_options' )
		);
	}
}
