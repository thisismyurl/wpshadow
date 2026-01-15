<?php
/**
 * Activity Logger for WPS Suite operations.
 *
 * Tracks module lifecycle, vault operations, license events, and settings changes
 * for display in WordPress Dashboard Activity widget.
 *
 * @package wpshadow_Support
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activity Logger Class
 *
 * Provides structured logging of WPS Suite operations with WordPress
 * Dashboard integration.
 */
class WPSHADOW_Activity_Logger {

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
	public const EVENT_MEDIA_FILE_ADDED   = 'media_file_added';
	public const EVENT_LICENSE_REGISTERED = 'license_registered';
	public const EVENT_LICENSE_VERIFIED   = 'license_verified';
	public const EVENT_LICENSE_EXPIRED    = 'license_expired';
	public const EVENT_SETTINGS_CHANGED   = 'settings_changed';
	public const EVENT_ENCRYPTION_CHANGED = 'encryption_changed';
	public const EVENT_MEDIA_FILE_DELETED = 'media_file_deleted';
	public const EVENT_MEDIA_FILE_EDITED  = 'media_file_edited';
	public const EVENT_ERROR              = 'error';

	/**
	 * Maximum number of events to store.
	 */
	private const MAX_EVENTS = 100;

	/**
	 * Transient key for activity storage.
	 */
	private const TRANSIENT_KEY = 'wpshadow_activity_log';

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
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_dashboard_scripts' ) );
		add_action( 'wp_ajax_WPSHADOW_filter_activity', array( __CLASS__, 'ajax_filter_activity' ) );

		// Hook into WordPress plugin lifecycle.
		add_action( 'activated_plugin', array( __CLASS__, 'on_plugin_activated' ), 10, 2 );
		add_action( 'deactivated_plugin', array( __CLASS__, 'on_plugin_deactivated' ), 10, 2 );

		// Log media library uploads.
		add_action( 'add_attachment', array( __CLASS__, 'on_add_attachment' ), 10, 1 );

		// Log media library deletions.
		add_action( 'delete_attachment', array( __CLASS__, 'on_delete_attachment' ), 10, 1 );

		// Log media edits (metadata updates after crop/rotate/scale).
		add_filter( 'wp_update_attachment_metadata', array( __CLASS__, 'on_update_attachment_metadata' ), 10, 2 );
	}
		/**
		 * Handle attachment added (Media Library upload).
		 *
		 * @param int $post_id Attachment post ID.
		 * @return void
		 */
	public static function on_add_attachment( int $post_id ): void {
		$post = get_post( $post_id );
		if ( ! $post || 'attachment' !== $post->post_type ) {
			return;
		}

		$file_path = get_attached_file( $post_id );
		$mime_type = get_post_mime_type( $post_id );
		$title     = get_the_title( $post_id );

		self::log(
			self::EVENT_MEDIA_FILE_ADDED,
			sprintf(
				/* translators: %s: Attachment title */
				__( 'Uploaded media: %s', 'plugin-wpshadow' ),
				(string) $title
			),
			array(
				'post_id'   => $post_id,
				'file'      => $file_path ?: '',
				'mime_type' => $mime_type ?: '',
			),
			'media'
		);
	}

	/**
	 * Handle attachment deletion (Media Library).
	 *
	 * @param int $post_id Attachment post ID.
	 * @return void
	 */
	public static function on_delete_attachment( int $post_id ): void {
		// Ensure it's an attachment.
		$post = get_post( $post_id );
		if ( ! $post || 'attachment' !== $post->post_type ) {
			return;
		}

		$file_path = get_attached_file( $post_id );
		$mime_type = get_post_mime_type( $post_id );
		$title     = get_the_title( $post_id );

		self::log(
			self::EVENT_MEDIA_FILE_DELETED,
			sprintf(
				/* translators: %s: Attachment title */
				__( 'Deleted media: %s', 'plugin-wpshadow' ),
				(string) $title
			),
			array(
				'post_id'   => $post_id,
				'file'      => $file_path ?: '',
				'mime_type' => $mime_type ?: '',
			),
			'media'
		);
	}

	/**
	 * Handle attachment metadata update (Media edits).
	 *
	 * @param array $data    New attachment metadata.
	 * @param int   $post_id Attachment post ID.
	 * @return array Metadata (unmodified).
	 */
	public static function on_update_attachment_metadata( array $data, int $post_id ): array {
		$post = get_post( $post_id );
		if ( ! $post || 'attachment' !== $post->post_type ) {
			return $data;
		}

		$old_meta  = wp_get_attachment_metadata( $post_id );
		$old_sizes = isset( $old_meta['sizes'] ) && is_array( $old_meta['sizes'] ) ? array_keys( $old_meta['sizes'] ) : array();
		$new_sizes = isset( $data['sizes'] ) && is_array( $data['sizes'] ) ? array_keys( $data['sizes'] ) : array();
		$added     = array_values( array_diff( $new_sizes, $old_sizes ) );
		$removed   = array_values( array_diff( $old_sizes, $new_sizes ) );

		$title     = get_the_title( $post_id );
		$file_path = get_attached_file( $post_id );
		$mime_type = get_post_mime_type( $post_id );

		self::log(
			self::EVENT_MEDIA_FILE_EDITED,
			sprintf(
				/* translators: %s: Attachment title */
				__( 'Edited media: %s', 'plugin-wpshadow' ),
				(string) $title
			),
			array(
				'post_id'       => $post_id,
				'file'          => $file_path ?: '',
				'mime_type'     => $mime_type ?: '',
				'added_sizes'   => $added,
				'removed_sizes' => $removed,
			),
			'media'
		);

		// Attempt to back up edited file to Vault.
		self::backup_attachment_to_vault( $post_id, (string) ( $file_path ?? '' ) );

		return $data;
	}

	/**
	 * Back up an attachment file into the Vault directory, versioned by timestamp.
	 *
	 * Uses Vault Support implementation when available; otherwise falls back to a
	 * simple copy into the configured vault directory.
	 *
	 * @param int    $post_id   Attachment post ID.
	 * @param string $file_path Absolute path to attachment file.
	 * @return void
	 */
	private static function backup_attachment_to_vault( int $post_id, string $file_path ): void {
		if ( empty( $file_path ) || ! file_exists( $file_path ) ) {
			return;
		}

		// Prefer canonical Vault implementation when available.
		if ( class_exists( '\\WPShadow\\WPSHADOW_Vault' ) && method_exists( '\\WPShadow\\WPSHADOW_Vault', 'add_log' ) ) {
			// Log the intent in Vault logs; real backup is managed by Vault plugin when enabled.
			\WPS\CoreSupport\WPSHADOW_Vault::add_log( 'info', get_current_user_id(), 'Backing up edited media ID ' . $post_id, 'media_edit_backup' );
			if ( method_exists( '\\WPShadow\\WPSHADOW_Vault', 'backup_file' ) ) {
				try {
					\WPS\CoreSupport\WPSHADOW_Vault::backup_file(
						$file_path,
						array(
							'post_id' => $post_id,
							'reason'  => 'edit',
						)
					);
					return;
				} catch ( \Throwable $e ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

				}
			}
		}

		// Fallback: copy file into local vault directory.
		$upload_dir    = wp_upload_dir();
		$vault_dirname = get_option( 'wpshadow_vault_dirname' );
		$vault_root    = ! empty( $vault_dirname ) ? $upload_dir['basedir'] . '/' . $vault_dirname : '';

		if ( empty( $vault_root ) || ! is_dir( $vault_root ) ) {
			return;
		}

		$dest_dir = $vault_root . '/edits/' . $post_id;
		if ( ! is_dir( $dest_dir ) ) {
			wp_mkdir_p( $dest_dir );
		}

		$basename  = basename( $file_path );
		$timestamp = (string) time();
		$dest_file = $dest_dir . '/' . $basename . '.' . $timestamp . '.bak';

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_copy
		@copy( $file_path, $dest_file );
	}


	/**
	 * Handle plugin activation event.
	 *
	 * @param string $plugin Plugin basename.
	 * @param bool   $network_wide Whether activation is network-wide.
	 * @return void
	 */
	public static function on_plugin_activated( string $plugin, bool $network_wide = false ): void {
		// Only log WPS Suite plugins.
		if ( ! str_contains( $plugin, 'wpshadow' ) ) {
			return;
		}

		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin, false, false );
		$plugin_name = $plugin_data['Name'] ?? basename( $plugin, '.php' );

		self::log(
			self::EVENT_MODULE_ACTIVATED,
			sprintf(
				/* translators: %s: Plugin name */
				__( 'Activated %s', 'plugin-wpshadow' ),
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
		// Only log WPS Suite plugins.
		if ( ! str_contains( $plugin, 'wpshadow' ) ) {
			return;
		}

		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin, false, false );
		$plugin_name = $plugin_data['Name'] ?? basename( $plugin, '.php' );

		self::log(
			self::EVENT_MODULE_DEACTIVATED,
			sprintf(
				/* translators: %s: Plugin name */
				__( 'Deactivated %s', 'plugin-wpshadow' ),
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
	 * @param string $module_source Optional module identifier (e.g., 'core', 'media', 'vault', 'images').
	 * @return bool True on success, false on failure.
	 */
	public static function log( string $event_type, string $description, array $metadata = array(), string $module_source = 'core' ): bool {
		$activity = array(
			'type'          => $event_type,
			'description'   => $description,
			'metadata'      => $metadata,
			'module_source' => $module_source,
			'user_id'       => get_current_user_id(),
			'timestamp'     => time(),
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
	 * Get events filtered by module source.
	 *
	 * @param string $module_source Module identifier (e.g., 'core', 'media', 'vault', 'images').
	 * @param int    $limit Optional limit on number of events to retrieve.
	 * @return array Array of event data.
	 */
	public static function get_events_by_module( string $module_source, int $limit = 0 ): array {
		$events = self::get_events();

		$filtered = array_filter(
			$events,
			function ( $event ) use ( $module_source ) {
				return isset( $event['module_source'] ) && $event['module_source'] === $module_source;
			}
		);

		if ( $limit > 0 ) {
			$filtered = array_slice( $filtered, 0, $limit );
		}

		return array_values( $filtered );
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
	 * Render WPS activity in WordPress Dashboard Activity widget.
	 *
	 * @return void
	 */
	public static function render_dashboard_activity(): void {
		$events = self::get_events( 20 );

		if ( empty( $events ) ) {
			return;
		}

		// Get unique event types and modules.
		$event_types = array_unique( array_column( $events, 'type' ) );
		$modules     = array_unique( array_column( $events, 'module_source' ) );

		echo '<div class="wps-activity-section" id="wps-activity-widget">';
		echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">';
		echo '<h3 style="margin: 0;">' . esc_html__( 'WPS Suite Activity', 'plugin-wpshadow' ) . '</h3>';

		// Filter controls.
		echo '<div class="wps-activity-filters" style="display: flex; gap: 8px;">';

		// Event type filter.
		echo '<select id="wps-event-type-filter" style="font-size: 12px;">';
		echo '<option value="">' . esc_html__( 'All Types', 'plugin-wpshadow' ) . '</option>';
		foreach ( $event_types as $type ) {
			$label = self::get_event_type_label( $type );
			printf(
				'<option value="%s">%s</option>',
				esc_attr( $type ),
				esc_html( $label )
			);
		}
		echo '</select>';

		// Module source filter.
		echo '<select id="wps-module-filter" style="font-size: 12px;">';
		echo '<option value="">' . esc_html__( 'All Modules', 'plugin-wpshadow' ) . '</option>';
		foreach ( $modules as $module ) {
			printf(
				'<option value="%s">%s</option>',
				esc_attr( $module ),
				esc_html( ucfirst( $module ) )
			);
		}
		echo '</select>';

		// Clear filters button.
		echo '<button id="wps-clear-filters" class="button button-small" style="font-size: 12px; display: none;">' . esc_html__( 'Clear', 'plugin-wpshadow' ) . '</button>';

		echo '</div></div>';

		echo '<ul id="wps-activity-list" style="margin-top: 0;">';

		foreach ( $events as $event ) {
			self::render_activity_item( $event );
		}

		echo '</ul>';
		echo '<div id="wps-no-results" style="display: none; padding: 12px; color: #666; font-style: italic;">' . esc_html__( 'No activity found matching the selected filters.', 'plugin-wpshadow' ) . '</div>';

		$dashboard_url = admin_url( 'admin.php?page=wps-dashboard&tab=activity' );
		printf(
			'<p><a href="%s">%s →</a></p>',
			esc_url( $dashboard_url ),
			esc_html__( 'View all activity', 'plugin-wpshadow' )
		);

		echo '</div>';
	}

	/**
	 * Render a single activity item.
	 *
	 * @param array $event Event data.
	 * @return void
	 */
	private static function render_activity_item( array $event ): void {
		$icon          = self::get_event_icon( $event['type'] );
		$description   = esc_html( $event['description'] );
		$timestamp     = human_time_diff( $event['timestamp'] ) . ' ' . __( 'ago', 'plugin-wpshadow' );
		$user          = get_userdata( $event['user_id'] );
		$username      = $user ? $user->display_name : __( 'Unknown', 'plugin-wpshadow' );
		$module_source = $event['module_source'] ?? 'core';

		// Build optional action markup (e.g., Restore) for specific events.
		$actions_html = '';
		if (
			self::EVENT_MEDIA_FILE_DELETED === ( $event['type'] ?? '' )
			&& ! empty( $event['data']['post_id'] )
			&& current_user_can( 'upload_files' )
		) {
			$attachment_id = (int) $event['data']['post_id'];

			// Prepare a secure inline form to trigger Vault rehydrate for this attachment.
			$action_url = esc_url( admin_url( 'admin-post.php?action=WPSHADOW_vault_attachment_action' ) );
			// Nonce must match the Vault handler's action key.
			$nonce = wp_create_nonce( 'wpshadow_vault_attachment_action' );

			$actions_html = sprintf(
				'<form method="post" action="%1$s" style="display:inline;margin-left:8px;">'
				. '<input type="hidden" name="wpshadow_vault_attachment_nonce" value="%2$s" />'
				. '<input type="hidden" name="attachment_id" value="%3$d" />'
				. '<input type="hidden" name="wpshadow_vault_attachment_cmd" value="rehydrate" />'
				. '<button type="submit" class="button-link" style="padding:0 4px;">%4$s</button>'
				. '</form>',
				$action_url,
				$nonce,
				$attachment_id,
				esc_html__( 'Restore', 'plugin-wpshadow' )
			);
		}

		printf(
			'<li data-event-type="%s" data-module="%s"><span class="dashicons %s" style="color: %s;"></span> <strong>%s</strong> - %s <small style="color: #666;">(%s)</small>%s</li>',
			esc_attr( $event['type'] ),
			esc_attr( $module_source ),
			esc_attr( $icon['class'] ),
			esc_attr( $icon['color'] ),
			esc_html( $description ),
			esc_html( $timestamp ),
			esc_html( $username ),
			wp_kses_post( $actions_html )
		);
	}

	/**
	 * Get human-readable label for event type.
	 *
	 * @param string $event_type Event type constant.
	 * @return string Human-readable label.
	 */
	private static function get_event_type_label( string $event_type ): string {
		$labels = array(
			self::EVENT_MODULE_ACTIVATED   => __( 'Module Activated', 'plugin-wpshadow' ),
			self::EVENT_MODULE_DEACTIVATED => __( 'Module Deactivated', 'plugin-wpshadow' ),
			self::EVENT_MODULE_INSTALLED   => __( 'Module Installed', 'plugin-wpshadow' ),
			self::EVENT_MODULE_UPDATED     => __( 'Module Updated', 'plugin-wpshadow' ),
			self::EVENT_VAULT_FILE_ADDED   => __( 'Vault File Added', 'plugin-wpshadow' ),
			self::EVENT_VAULT_FILE_REMOVED => __( 'Vault File Removed', 'plugin-wpshadow' ),
			self::EVENT_VAULT_VERIFIED     => __( 'Vault Verified', 'plugin-wpshadow' ),
			self::EVENT_VAULT_RESTORED     => __( 'Vault Restored', 'plugin-wpshadow' ),
			self::EVENT_LICENSE_REGISTERED => __( 'License Registered', 'plugin-wpshadow' ),
			self::EVENT_LICENSE_VERIFIED   => __( 'License Verified', 'plugin-wpshadow' ),
			self::EVENT_LICENSE_EXPIRED    => __( 'License Expired', 'plugin-wpshadow' ),
			self::EVENT_SETTINGS_CHANGED   => __( 'Settings Changed', 'plugin-wpshadow' ),
			self::EVENT_ENCRYPTION_CHANGED => __( 'Encryption Changed', 'plugin-wpshadow' ),
			self::EVENT_MEDIA_FILE_ADDED   => __( 'Media File Uploaded', 'plugin-wpshadow' ),
			self::EVENT_MEDIA_FILE_EDITED  => __( 'Media File Edited', 'plugin-wpshadow' ),
			self::EVENT_MEDIA_FILE_DELETED => __( 'Media File Deleted', 'plugin-wpshadow' ),
			self::EVENT_ERROR              => __( 'Error', 'plugin-wpshadow' ),
		);

		return $labels[ $event_type ] ?? ucwords( str_replace( '_', ' ', $event_type ) );
	}

	/**
	 * Enqueue dashboard scripts for activity filtering.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_dashboard_scripts( string $hook ): void {
		if ( 'index.php' !== $hook ) {
			return;
		}

		wp_add_inline_script(
			'dashboard',
			"
			jQuery(function($) {
				const eventFilter = $('#wps-event-type-filter');
				const moduleFilter = $('#wps-module-filter');
				const clearButton = $('#wps-clear-filters');
				const activityList = $('#wps-activity-list');
				const noResults = $('#wps-no-results');

				function filterActivities() {
					const eventType = eventFilter.val();
					const module = moduleFilter.val();
					let visibleCount = 0;

					activityList.find('li').each(function() {
						const item = $(this);
						const itemType = item.data('event-type');
						const itemModule = item.data('module');

						const matchesType = !eventType || itemType === eventType;
						const matchesModule = !module || itemModule === module;

						if (matchesType && matchesModule) {
							item.show();
							visibleCount++;
						} else {
							item.hide();
						}
					});

					if (visibleCount === 0) {
						activityList.hide();
						noResults.show();
					} else {
						activityList.show();
						noResults.hide();
					}

					// Show/hide clear button
					if (eventType || module) {
						clearButton.show();
					} else {
						clearButton.hide();
					}
				}

				eventFilter.on('change', filterActivities);
				moduleFilter.on('change', filterActivities);

				clearButton.on('click', function() {
					eventFilter.val('').trigger('change');
					moduleFilter.val('').trigger('change');
				});
			});
			"
		);
	}

	/**
	 * AJAX handler for filtering activity (future enhancement for server-side filtering).
	 *
	 * @return void
	 */
	public static function ajax_filter_activity(): void {
		check_ajax_referer( 'wps-activity-filter', 'nonce' );

		$event_type    = \WPS\CoreSupport\WPSHADOW_get_post_text( 'event_type' );
		$module_source = \WPS\CoreSupport\WPSHADOW_get_post_text( 'module' );
		$limit         = \WPS\CoreSupport\WPSHADOW_get_post_int( 'limit', 20 );

		$events = self::get_events( 100 );

		// Filter events.
		if ( $event_type || $module_source ) {
			$events = array_filter(
				$events,
				function ( $event ) use ( $event_type, $module_source ) {
					$matches_type   = ! $event_type || ( $event['type'] ?? '' ) === $event_type;
					$matches_module = ! $module_source || ( $event['module_source'] ?? '' ) === $module_source;
					return $matches_type && $matches_module;
				}
			);
		}

		$events = array_slice( $events, 0, $limit );

		wp_send_json_success(
			array(
				'events' => $events,
				'count'  => count( $events ),
			)
		);
	}

	/**
	 * Maybe add standalone WPS activity dashboard widget.
	 *
	 * Only adds if no WordPress activity widget exists or in multisite.
	 * DISABLED: Activity is already injected into the Activity box via activity_box_end hook.
	 *
	 * @return void
	 */
	public static function maybe_add_dashboard_widget(): void {
		// Disabled: WPS activity is now injected into the WordPress Activity widget via activity_box_end hook.
		// This prevents duplicate widgets on the dashboard.
	}

	/**
	 * Render standalone activity widget.
	 *
	 * @return void
	 */
	public static function render_standalone_widget(): void {
		$events = self::get_events( 10 );

		if ( empty( $events ) ) {
			echo '<p>' . esc_html__( 'No recent WPS activity.', 'plugin-wpshadow' ) . '</p>';
			return;
		}

		echo '<ul>';

		foreach ( $events as $event ) {
			$icon        = self::get_event_icon( $event['type'] );
			$description = esc_html( $event['description'] );
			$timestamp   = human_time_diff( $event['timestamp'] ) . ' ' . __( 'ago', 'plugin-wpshadow' );

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
			self::EVENT_MEDIA_FILE_ADDED   => array(
				'class' => 'dashicons-upload',
				'color' => '#00a32a',
			),
			self::EVENT_MEDIA_FILE_EDITED  => array(
				'class' => 'dashicons-edit',
				'color' => '#dba617',
			),
			self::EVENT_MEDIA_FILE_DELETED => array(
				'class' => 'dashicons-trash',
				'color' => '#d63638',
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
	 * @param string $module_source Module source identifier.
	 * @return bool True on success.
	 */
	public static function log_module_activated( string $module_name, string $module_slug, string $module_source = 'core' ): bool {
		return self::log(
			self::EVENT_MODULE_ACTIVATED,
			sprintf(
				/* translators: %s: Module name */
				__( 'Activated %s', 'plugin-wpshadow' ),
				$module_name
			),
			array( 'module_slug' => $module_slug ),
			$module_source
		);
	}

	/**
	 * Log module deactivation.
	 *
	 * @param string $module_name Module name.
	 * @param string $module_slug Module slug.
	 * @param string $module_source Module source identifier.
	 * @return bool True on success.
	 */
	public static function log_module_deactivated( string $module_name, string $module_slug, string $module_source = 'core' ): bool {
		return self::log(
			self::EVENT_MODULE_DEACTIVATED,
			sprintf(
				/* translators: %s: Module name */
				__( 'Deactivated %s', 'plugin-wpshadow' ),
				$module_name
			),
			array( 'module_slug' => $module_slug ),
			$module_source
		);
	}

	/**
	 * Log vault verification.
	 *
	 * @param int    $files_verified Number of files verified.
	 * @param int    $files_failed Number of files that failed verification.
	 * @param string $module_source Module source identifier.
	 * @return bool True on success.
	 */
	public static function log_vault_verified( int $files_verified, int $files_failed = 0, string $module_source = 'vault' ): bool {
		if ( $files_failed > 0 ) {
			$description = sprintf(
				/* translators: 1: Files verified, 2: Files failed */
				__( 'Vault verified: %1$d files passed, %2$d failed', 'plugin-wpshadow' ),
				$files_verified,
				$files_failed
			);
		} else {
			$description = sprintf(
				/* translators: %d: Number of files */
				__( 'Vault verified: %d files passed', 'plugin-wpshadow' ),
				$files_verified
			);
		}

		return self::log(
			self::EVENT_VAULT_VERIFIED,
			$description,
			array(
				'verified' => $files_verified,
				'failed'   => $files_failed,
			),
			$module_source
		);
	}

	/**
	 * Log license verification.
	 *
	 * @param bool   $is_valid Whether license is valid.
	 * @param string $license_type License type.
	 * @param string $module_source Module source identifier.
	 * @return bool True on success.
	 */
	public static function log_license_verified( bool $is_valid, string $license_type = '', string $module_source = 'core' ): bool {
		if ( $is_valid ) {
			$description = $license_type
				? sprintf(
					/* translators: %s: License type */
					__( 'License verified: %s', 'plugin-wpshadow' ),
					$license_type
				)
				: __( 'License verified successfully', 'plugin-wpshadow' );
		} else {
			$description = __( 'License verification failed', 'plugin-wpshadow' );
		}

		return self::log(
			self::EVENT_LICENSE_VERIFIED,
			$description,
			array(
				'is_valid'     => $is_valid,
				'license_type' => $license_type,
			),
			$module_source
		);
	}

	/**
	 * Log settings change.
	 *
	 * @param string $setting_name Setting that was changed.
	 * @param mixed  $old_value Old value.
	 * @param mixed  $new_value New value.
	 * @param string $module_source Module source identifier.
	 * @return bool True on success.
	 */
	public static function log_settings_changed( string $setting_name, $old_value, $new_value, string $module_source = 'core' ): bool {
		return self::log(
			self::EVENT_SETTINGS_CHANGED,
			sprintf(
				/* translators: %s: Setting name */
				__( 'Updated setting: %s', 'plugin-wpshadow' ),
				$setting_name
			),
			array(
				'setting'   => $setting_name,
				'old_value' => $old_value,
				'new_value' => $new_value,
			),
			$module_source
		);
	}

	/**
	 * Log error event.
	 *
	 * @param string $error_message Error message.
	 * @param array  $context Additional error context.
	 * @param string $module_source Module source identifier.
	 * @return bool True on success.
	 */
	public static function log_error( string $error_message, array $context = array(), string $module_source = 'core' ): bool {
		return self::log(
			self::EVENT_ERROR,
			$error_message,
			$context,
			$module_source
		);
	}
}

/* @changelog Added WPSHADOW_Activity_Logger for WordPress Dashboard Activity integration */
/* @changelog Added module_source tracking to all log entries and get_events_by_module() method for filtering logs by module */
