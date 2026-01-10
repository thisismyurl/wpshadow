<?php
/**
 * Activity Logger for TIMU Suite operations.
 *
 * Tracks module lifecycle, vault operations, license events, and settings changes
 * for display in WordPress Dashboard Activity widget.
 *
 * @package TIMU_Core_Support
 * @since 1.0.0
 */

declare(strict_types=1);

namespace TIMU\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activity Logger Class
 *
 * Provides structured logging of TIMU Suite operations with WordPress
 * Dashboard integration.
 */
class TIMU_Activity_Logger {

	/**
	 * Activity event types.
	 */
	public const EVENT_MODULE_ACTIVATED   = 'module_activated';
	public const EVENT_MODULE_DEACTIVATED = 'module_deactivated';
	public const EVENT_MODULE_INSTALLED   = 'module_installed';
	public const EVENT_MODULE_UPDATED     = 'module_updated';
	public const EVENT_VAULT_FILE_ADDED   = 'vault_file_added';
	public const EVENT_VAULT_FILE_REMOVED = 'vault_file_removed';
	public const EVENT_VAULT_VERIFIED     = 'vault_verified';
	public const EVENT_VAULT_RESTORED     = 'vault_restored';
	public const EVENT_LICENSE_REGISTERED = 'license_registered';
	public const EVENT_LICENSE_VERIFIED   = 'license_verified';
	public const EVENT_LICENSE_EXPIRED    = 'license_expired';
	public const EVENT_SETTINGS_CHANGED   = 'settings_changed';
	public const EVENT_ENCRYPTION_CHANGED = 'encryption_changed';
	public const EVENT_ERROR              = 'error';

	/**
	 * Maximum number of events to store.
	 */
	private const MAX_EVENTS = 100;

	/**
	 * Transient key for activity storage.
	 */
	private const TRANSIENT_KEY = 'timu_activity_log';

	/**
	 * Transient expiration (7 days in seconds).
	 */
	private const TRANSIENT_EXPIRATION = 7 * DAY_IN_SECONDS;

	/**
	 * Initialize the activity logger.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'activity_box_end', array( __CLASS__, 'render_dashboard_activity' ) );
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'maybe_add_dashboard_widget' ) );

		// Hook into WordPress plugin lifecycle.
		add_action( 'activated_plugin', array( __CLASS__, 'on_plugin_activated' ), 10, 2 );
		add_action( 'deactivated_plugin', array( __CLASS__, 'on_plugin_deactivated' ), 10, 2 );
	}

	/**
	 * Handle plugin activation event.
	 *
	 * @param string $plugin Plugin basename.
	 * @param bool   $network_wide Whether activation is network-wide.
	 * @return void
	 */
	public static function on_plugin_activated( string $plugin, bool $network_wide = false ): void {
		// Only log TIMU suite plugins.
		if ( ! str_contains( $plugin, 'thisismyurl' ) ) {
			return;
		}

		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin, false, false );
		$plugin_name = $plugin_data['Name'] ?? basename( $plugin, '.php' );

		self::log(
			self::EVENT_MODULE_ACTIVATED,
			sprintf(
				/* translators: %s: Plugin name */
				__( 'Activated %s', 'plugin-wp-support-thisismyurl' ),
				$plugin_name
			),
			array(
				'plugin'       => $plugin,
				'network_wide' => $network_wide,
			)
		);
	}

	/**
	 * Handle plugin deactivation event.
	 *
	 * @param string $plugin Plugin basename.
	 * @param bool   $network_wide Whether deactivation is network-wide.
	 * @return void
	 */
	public static function on_plugin_deactivated( string $plugin, bool $network_wide = false ): void {
		// Only log TIMU suite plugins.
		if ( ! str_contains( $plugin, 'thisismyurl' ) ) {
			return;
		}

		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin, false, false );
		$plugin_name = $plugin_data['Name'] ?? basename( $plugin, '.php' );

		self::log(
			self::EVENT_MODULE_DEACTIVATED,
			sprintf(
				/* translators: %s: Plugin name */
				__( 'Deactivated %s', 'plugin-wp-support-thisismyurl' ),
				$plugin_name
			),
			array(
				'plugin'       => $plugin,
				'network_wide' => $network_wide,
			)
		);
	}

	/**
	 * Log an activity event.
	 *
	 * @param string $event_type Event type constant.
	 * @param string $description Human-readable description.
	 * @param array  $metadata Additional metadata.
	 * @return bool True on success, false on failure.
	 */
	public static function log( string $event_type, string $description, array $metadata = array() ): bool {
		$activity = array(
			'type'        => $event_type,
			'description' => $description,
			'metadata'    => $metadata,
			'user_id'     => get_current_user_id(),
			'timestamp'   => time(),
		);

		$events = self::get_events();
		array_unshift( $events, $activity );

		// Keep only the most recent MAX_EVENTS.
		$events = array_slice( $events, 0, self::MAX_EVENTS );

		return set_transient( self::TRANSIENT_KEY, $events, self::TRANSIENT_EXPIRATION );
	}

	/**
	 * Get all logged events.
	 *
	 * @param int $limit Optional limit on number of events to retrieve.
	 * @return array Array of event data.
	 */
	public static function get_events( int $limit = 0 ): array {
		$events = get_transient( self::TRANSIENT_KEY );

		if ( ! is_array( $events ) ) {
			$events = array();
		}

		if ( $limit > 0 ) {
			$events = array_slice( $events, 0, $limit );
		}

		return $events;
	}

	/**
	 * Clear all logged events.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function clear(): bool {
		return delete_transient( self::TRANSIENT_KEY );
	}

	/**
	 * Render TIMU activity in WordPress Dashboard Activity widget.
	 *
	 * @return void
	 */
	public static function render_dashboard_activity(): void {
		$events = self::get_events( 5 );

		if ( empty( $events ) ) {
			return;
		}

		echo '<div class="timu-activity-section">';
		echo '<h3>' . esc_html__( 'TIMU Suite Activity', 'plugin-wp-support-thisismyurl' ) . '</h3>';
		echo '<ul>';

		foreach ( $events as $event ) {
			$icon        = self::get_event_icon( $event['type'] );
			$description = esc_html( $event['description'] );
			$timestamp   = human_time_diff( $event['timestamp'] ) . ' ' . __( 'ago', 'plugin-wp-support-thisismyurl' );
			$user        = get_userdata( $event['user_id'] );
			$username    = $user ? $user->display_name : __( 'Unknown', 'plugin-wp-support-thisismyurl' );

			printf(
				'<li><span class="dashicons %s" style="color: %s;"></span> <strong>%s</strong> - %s <small style="color: #666;">(%s)</small></li>',
				esc_attr( $icon['class'] ),
				esc_attr( $icon['color'] ),
				esc_html( $description ),
				esc_html( $timestamp ),
				esc_html( $username )
			);
		}

		echo '</ul>';

		$dashboard_url = admin_url( 'admin.php?page=timu-dashboard&tab=activity' );
		printf(
			'<p><a href="%s">%s →</a></p>',
			esc_url( $dashboard_url ),
			esc_html__( 'View all activity', 'plugin-wp-support-thisismyurl' )
		);

		echo '</div>';
	}

	/**
	 * Maybe add standalone TIMU activity dashboard widget.
	 *
	 * Only adds if no WordPress activity widget exists or in multisite.
	 *
	 * @return void
	 */
	public static function maybe_add_dashboard_widget(): void {
		// Always add for multisite network admin.
		if ( is_multisite() && is_network_admin() ) {
			wp_add_dashboard_widget(
				'timu_activity',
				__( 'TIMU Suite Activity', 'plugin-wp-support-thisismyurl' ),
				array( __CLASS__, 'render_standalone_widget' )
			);
		}
	}

	/**
	 * Render standalone activity widget.
	 *
	 * @return void
	 */
	public static function render_standalone_widget(): void {
		$events = self::get_events( 10 );

		if ( empty( $events ) ) {
			echo '<p>' . esc_html__( 'No recent TIMU activity.', 'plugin-wp-support-thisismyurl' ) . '</p>';
			return;
		}

		echo '<ul>';

		foreach ( $events as $event ) {
			$icon        = self::get_event_icon( $event['type'] );
			$description = esc_html( $event['description'] );
			$timestamp   = human_time_diff( $event['timestamp'] ) . ' ' . __( 'ago', 'plugin-wp-support-thisismyurl' );

			printf(
				'<li style="padding: 8px 0; border-bottom: 1px solid #e5e5e5;"><span class="dashicons %s" style="color: %s;"></span> %s <small style="color: #666; display: block; margin-top: 4px;">%s</small></li>',
				esc_attr( $icon['class'] ),
				esc_attr( $icon['color'] ),
				esc_html( $description ),
				esc_html( $timestamp )
			);
		}

		echo '</ul>';
	}

	/**
	 * Get icon configuration for event type.
	 *
	 * @param string $event_type Event type constant.
	 * @return array Icon class and color.
	 */
	private static function get_event_icon( string $event_type ): array {
		$icons = array(
			self::EVENT_MODULE_ACTIVATED   => array(
				'class' => 'dashicons-yes-alt',
				'color' => '#00a32a',
			),
			self::EVENT_MODULE_DEACTIVATED => array(
				'class' => 'dashicons-dismiss',
				'color' => '#d63638',
			),
			self::EVENT_MODULE_INSTALLED   => array(
				'class' => 'dashicons-download',
				'color' => '#2271b1',
			),
			self::EVENT_MODULE_UPDATED     => array(
				'class' => 'dashicons-update',
				'color' => '#2271b1',
			),
			self::EVENT_VAULT_FILE_ADDED   => array(
				'class' => 'dashicons-vault',
				'color' => '#00a32a',
			),
			self::EVENT_VAULT_FILE_REMOVED => array(
				'class' => 'dashicons-trash',
				'color' => '#d63638',
			),
			self::EVENT_VAULT_VERIFIED     => array(
				'class' => 'dashicons-yes-alt',
				'color' => '#00a32a',
			),
			self::EVENT_VAULT_RESTORED     => array(
				'class' => 'dashicons-backup',
				'color' => '#2271b1',
			),
			self::EVENT_LICENSE_REGISTERED => array(
				'class' => 'dashicons-awards',
				'color' => '#00a32a',
			),
			self::EVENT_LICENSE_VERIFIED   => array(
				'class' => 'dashicons-yes',
				'color' => '#00a32a',
			),
			self::EVENT_LICENSE_EXPIRED    => array(
				'class' => 'dashicons-warning',
				'color' => '#dba617',
			),
			self::EVENT_SETTINGS_CHANGED   => array(
				'class' => 'dashicons-admin-settings',
				'color' => '#2271b1',
			),
			self::EVENT_ENCRYPTION_CHANGED => array(
				'class' => 'dashicons-lock',
				'color' => '#2271b1',
			),
			self::EVENT_ERROR              => array(
				'class' => 'dashicons-warning',
				'color' => '#d63638',
			),
		);

		return $icons[ $event_type ] ?? array(
			'class' => 'dashicons-admin-generic',
			'color' => '#666',
		);
	}

	/**
	 * Log module activation.
	 *
	 * @param string $module_name Module name.
	 * @param string $module_slug Module slug.
	 * @return bool True on success.
	 */
	public static function log_module_activated( string $module_name, string $module_slug ): bool {
		return self::log(
			self::EVENT_MODULE_ACTIVATED,
			sprintf(
				/* translators: %s: Module name */
				__( 'Activated %s', 'plugin-wp-support-thisismyurl' ),
				$module_name
			),
			array( 'module_slug' => $module_slug )
		);
	}

	/**
	 * Log module deactivation.
	 *
	 * @param string $module_name Module name.
	 * @param string $module_slug Module slug.
	 * @return bool True on success.
	 */
	public static function log_module_deactivated( string $module_name, string $module_slug ): bool {
		return self::log(
			self::EVENT_MODULE_DEACTIVATED,
			sprintf(
				/* translators: %s: Module name */
				__( 'Deactivated %s', 'plugin-wp-support-thisismyurl' ),
				$module_name
			),
			array( 'module_slug' => $module_slug )
		);
	}

	/**
	 * Log vault verification.
	 *
	 * @param int $files_verified Number of files verified.
	 * @param int $files_failed Number of files that failed verification.
	 * @return bool True on success.
	 */
	public static function log_vault_verified( int $files_verified, int $files_failed = 0 ): bool {
		if ( $files_failed > 0 ) {
			$description = sprintf(
				/* translators: 1: Files verified, 2: Files failed */
				__( 'Vault verified: %1$d files passed, %2$d failed', 'plugin-wp-support-thisismyurl' ),
				$files_verified,
				$files_failed
			);
		} else {
			$description = sprintf(
				/* translators: %d: Number of files */
				__( 'Vault verified: %d files passed', 'plugin-wp-support-thisismyurl' ),
				$files_verified
			);
		}

		return self::log(
			self::EVENT_VAULT_VERIFIED,
			$description,
			array(
				'verified' => $files_verified,
				'failed'   => $files_failed,
			)
		);
	}

	/**
	 * Log license verification.
	 *
	 * @param bool   $is_valid Whether license is valid.
	 * @param string $license_type License type.
	 * @return bool True on success.
	 */
	public static function log_license_verified( bool $is_valid, string $license_type = '' ): bool {
		if ( $is_valid ) {
			$description = $license_type
				? sprintf(
					/* translators: %s: License type */
					__( 'License verified: %s', 'plugin-wp-support-thisismyurl' ),
					$license_type
				)
				: __( 'License verified successfully', 'plugin-wp-support-thisismyurl' );
		} else {
			$description = __( 'License verification failed', 'plugin-wp-support-thisismyurl' );
		}

		return self::log(
			self::EVENT_LICENSE_VERIFIED,
			$description,
			array(
				'is_valid'     => $is_valid,
				'license_type' => $license_type,
			)
		);
	}

	/**
	 * Log settings change.
	 *
	 * @param string $setting_name Setting that was changed.
	 * @param mixed  $old_value Old value.
	 * @param mixed  $new_value New value.
	 * @return bool True on success.
	 */
	public static function log_settings_changed( string $setting_name, $old_value, $new_value ): bool {
		return self::log(
			self::EVENT_SETTINGS_CHANGED,
			sprintf(
				/* translators: %s: Setting name */
				__( 'Updated setting: %s', 'plugin-wp-support-thisismyurl' ),
				$setting_name
			),
			array(
				'setting'   => $setting_name,
				'old_value' => $old_value,
				'new_value' => $new_value,
			)
		);
	}

	/**
	 * Log error event.
	 *
	 * @param string $error_message Error message.
	 * @param array  $context Additional error context.
	 * @return bool True on success.
	 */
	public static function log_error( string $error_message, array $context = array() ): bool {
		return self::log(
			self::EVENT_ERROR,
			$error_message,
			$context
		);
	}
}

/* @changelog Added TIMU_Activity_Logger for WordPress Dashboard Activity integration */
