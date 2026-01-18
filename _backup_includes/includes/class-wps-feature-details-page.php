<?php
/**
 * Feature Details Page - Centralized feature management interface
 *
 * Replaces individual feature submenus with a unified details page
 * accessible from Quick Links widget.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.76000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Feature Details Page Manager
 */
class WPSHADOW_Feature_Details_Page {

	/**
	 * Initialize the feature details system.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'register_hidden_page' ), 99 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_ajax_wpshadow_toggle_feature', array( __CLASS__, 'ajax_toggle_feature' ) );
		add_action( 'wp_ajax_wpshadow_toggle_feature_setting', array( __CLASS__, 'ajax_toggle_feature_setting' ) );
		add_action( 'wp_ajax_wpshadow_get_feature_log', array( __CLASS__, 'ajax_get_feature_log' ) );
		add_action( 'wp_ajax_wpshadow_clear_feature_log', array( __CLASS__, 'ajax_clear_feature_log' ) );
		add_filter( 'admin_title', array( __CLASS__, 'filter_admin_title' ), 10, 2 );
		add_action( 'load-admin_page_wpshadow-feature-details', array( __CLASS__, 'set_page_title' ) );
	}

	/**
	 * Set the page title global to prevent strip_tags() null parameter warning.
	 *
	 * @return void
	 */
	public static function set_page_title(): void {
		global $title;
		
		$feature_id = isset( $_GET['feature'] ) ? sanitize_key( $_GET['feature'] ) : '';
		if ( $feature_id ) {
			$feature = WPSHADOW_Feature_Registry::get_feature( $feature_id );
			if ( $feature ) {
				$title = $feature['name'] ?? __( 'Feature Details', 'wpshadow' );
				return;
			}
		}
		$title = __( 'Feature Details', 'wpshadow' );
	}

	/**
	 * Filter admin title for feature details page.
	 *
	 * @param string $admin_title The admin title.
	 * @param string $title       The title.
	 * @return string
	 */
	public static function filter_admin_title( string $admin_title, string $title ): string {
		$screen = get_current_screen();
		if ( $screen && 'admin_page_wpshadow-feature-details' === $screen->id ) {
			$feature_id = isset( $_GET['feature'] ) ? sanitize_key( $_GET['feature'] ) : '';
			if ( $feature_id ) {
				$feature = WPSHADOW_Feature_Registry::get_feature( $feature_id );
				if ( $feature ) {
					$feature_name = $feature['name'] ?? $feature_id;
					return $feature_name . ' ' . $admin_title;
				}
			}
			return __( 'Feature Details', 'wpshadow' ) . ' ' . $admin_title;
		}
		return $admin_title;
	}

	/**
	 * Register hidden submenu page (not visible in menu).
	 *
	 * @return void
	 */
	public static function register_hidden_page(): void {
		$hook = add_submenu_page(
			null, // Hidden from menu
			__( 'Feature Details', 'wpshadow' ),
			__( 'Feature Details', 'wpshadow' ),
			'manage_options',
			'wpshadow-feature-details',
			array( __CLASS__, 'render_page' )
		);
		
		// Add screen options and metabox setup on page load
		add_action( "load-{$hook}", array( __CLASS__, 'setup_screen_options' ) );
	}
	
	/**
	 * Setup screen options and metabox configuration.
	 *
	 * @return void
	 */
	public static function setup_screen_options(): void {
		// Get current screen
		$screen = get_current_screen();
		
		if ( ! $screen ) {
			return;
		}
		
		// Disable block editor for this admin page to prevent REST API errors
		$screen->is_block_editor = false;
		
		// Enable screen options for metaboxes (allows showing/hiding widgets)
		add_screen_option(
			'layout_columns',
			array(
				'max'     => 1,
				'default' => 1,
			)
		);
	}

	/**
	 * Enqueue assets for feature details page.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( string $hook ): void {
		if ( 'admin_page_wpshadow-feature-details' !== $hook ) {
			return;
		}

		// Remove Gutenberg/block editor scripts to prevent REST API errors
		wp_dequeue_script( 'wp-edit-post' );
		wp_dequeue_script( 'wp-components' );
		wp_dequeue_style( 'wp-edit-post' );
		wp_dequeue_style( 'wp-components' );

		// Note: postbox script removed to prevent History API SecurityError on GitHub Codespaces

		wp_enqueue_style(
			'wpshadow-feature-details',
			WPSHADOW_URL . 'assets/css/feature-details.css',
			array(),
			WPSHADOW_VERSION . '-' . time()
		);

		wp_enqueue_script(
			'wpshadow-feature-details',
			WPSHADOW_URL . 'assets/js/feature-details.js',
			array( 'jquery' ),
			WPSHADOW_VERSION . '-' . time(),
			true
		);

		wp_localize_script(
			'wpshadow-feature-details',
			'wpshadowFeatureDetails',
			array(
				'nonce'      => wp_create_nonce( 'wpshadow_feature_details' ),
				'ajaxurl'    => admin_url( 'admin-ajax.php' ),
				'strings'    => array(
					'enabling'  => __( 'Enabling...', 'wpshadow' ),
					'disabling' => __( 'Disabling...', 'wpshadow' ),
					'enabled'   => __( 'Enabled', 'wpshadow' ),
					'disabled'  => __( 'Disabled', 'wpshadow' ),
					'error'     => __( 'Oops: ', 'wpshadow' ),
				),
			)
		);
	}

	/**
	 * Render feature details page.
	 *
	 * @return void
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpshadow' ) );
		}

		$feature_id = isset( $_GET['feature'] ) ? sanitize_key( $_GET['feature'] ) : '';
		
		if ( empty( $feature_id ) ) {
			wp_die( esc_html__( 'No feature specified.', 'wpshadow' ) );
		}

		$feature = WPSHADOW_Feature_Registry::get_feature( $feature_id );
		
		if ( ! $feature ) {
			wp_die( esc_html__( 'Feature not found.', 'wpshadow' ) );
		}

		// Track feature access for commonly accessed list (Issues #447 & #448).
		if ( class_exists( '\\WPShadow\\CoreSupport\\WPSHADOW_Feature_Search' ) ) {
			WPSHADOW_Feature_Search::track_feature_access( $feature_id );
		}

		// Store feature data in global for metabox callbacks
		$GLOBALS['wpshadow_feature_details'] = $feature;
		$GLOBALS['wpshadow_feature_id'] = $feature_id;
		
		// Get current screen for proper metabox registration
		$screen = get_current_screen();
		$screen_id = $screen ? $screen->id : 'admin_page_wpshadow-feature-details';

		// Register metaboxes
		add_meta_box(
			'wpshadow-feature-info',
			__( 'Feature Information', 'wpshadow' ),
			array( __CLASS__, 'render_feature_info_metabox' ),
			$screen_id,
			'normal',
			'high'
		);

		add_meta_box(
			'wpshadow-feature-log',
			__( 'Activity Log', 'wpshadow' ),
			array( __CLASS__, 'render_activity_log_metabox' ),
			$screen_id,
			'normal',
			'default'
		);

		/**
		 * Allow features to register additional metaboxes on the feature details page.
		 *
		 * @param string   $feature_id Feature ID.
		 * @param string   $screen_id  Screen ID for registering metaboxes.
		 * @param array    $feature    Feature data array.
		 */
		do_action( 'wpshadow_feature_details_metaboxes', $feature_id, $screen_id, $feature );

		$feature_name  = $feature['name'] ?? $feature_id;
		$feature_icon  = $feature['icon'] ?? 'dashicons-admin-generic';
		
		?>
		<div class="wrap wpshadow-feature-details">
			<h1>
				<span class="dashicons <?php echo esc_attr( $feature_icon ); ?>"></span>
				<?php echo esc_html( $feature_name ); ?>
			</h1>

			<form method="post" action="">
				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
				
				<div class="wpshadow-feature-details-container">
					<div id="poststuff">
						<div id="post-body" class="metabox-holder columns-1">
							<div id="postbox-container-1" class="postbox-container">
								<?php do_meta_boxes( $screen_id, 'normal', null ); ?>
							</div>
						</div>
					</div>
				</div>
			</form>

			<p class="back-link">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow' ) ); ?>">
					&larr; <?php esc_html_e( 'Back to Dashboard', 'wpshadow' ); ?>
				</a>
			</p>
		</div>

		<script type="text/javascript">
		jQuery(document).ready(function($) {
			// Postbox initialization removed - handled by feature-details.js
			// Note: postbox script no longer enqueued due to History API SecurityError fix
		});
		</script>
		<?php
	}

	/**
	 * Render feature information metabox.
	 *
	 * @return void
	 */
	public static function render_feature_info_metabox(): void {
		$feature = $GLOBALS['wpshadow_feature_details'] ?? null;
		$feature_id = $GLOBALS['wpshadow_feature_id'] ?? '';

		if ( ! $feature ) {
			return;
		}

		$is_enabled    = ! empty( $feature['enabled'] );
		$feature_desc  = $feature['description'] ?? '';
		$sub_features  = $feature['sub_features'] ?? array();
		
		?>
		<table class="wp-list-table widefat fixed striped" style="border: none;">
			<tbody>
				<tr>
					<td class="check-column" style="width: 60px; padding: 12px; border: none;">
						<label class="wps-feature-toggle-label">
							<input 
								type="checkbox" 
								class="wps-feature-toggle-input wpshadow-feature-toggle"
								data-feature-id="<?php echo esc_attr( $feature_id ); ?>"
								<?php checked( $is_enabled ); ?>
							/>
							<span class="wps-feature-toggle-switch"></span>
							<span class="screen-reader-text">
								<?php esc_html_e( 'Enable this feature', 'wpshadow' ); ?>
							</span>
						</label>
					</td>
					<td style="border: none;">
						<strong><?php echo esc_html( $feature['name'] ?? $feature_id ); ?></strong>
						<?php if ( ! empty( $feature_desc ) ) : ?>
							<p style="margin: 4px 0 0; color: #666;">
								<?php echo esc_html( $feature_desc ); ?>
							</p>
						<?php endif; ?>
					</td>
				</tr>
			</tbody>
		</table>

		<!-- Sub-Features / Settings -->
		<?php if ( ! empty( $sub_features ) ) : ?>
			<div class="feature-sub-features-section <?php echo ! $is_enabled ? 'feature-settings-disabled' : ''; ?>" style="margin-top: 15px;">
				<h3 style="margin-top: 0;"><?php esc_html_e( 'Feature Settings', 'wpshadow' ); ?></h3>
				<p class="description">
					<?php esc_html_e( 'Individual settings for this feature:', 'wpshadow' ); ?>
				</p>
				
				<table class="wp-list-table widefat fixed striped">
					<tbody>
						<?php foreach ( $sub_features as $sub_key => $sub_label ) : ?>
							<?php 
							$sub_enabled = get_option( 'wpshadow_' . $feature_id . '_' . $sub_key, false );
							?>
							<tr>
								<td class="check-column" style="width: 60px; padding: 12px;">
									<label class="wps-feature-toggle-label">
										<input 
											type="checkbox" 
											class="wpshadow-sub-feature-toggle"
											data-feature-id="<?php echo esc_attr( $feature_id ); ?>"
											data-setting-key="<?php echo esc_attr( $sub_key ); ?>"
											<?php checked( $sub_enabled ); ?>
											<?php disabled( ! $is_enabled ); ?>
										/>
										<span class="wps-feature-toggle-switch"></span>
										<span class="screen-reader-text">
											<?php echo esc_html( $sub_label ); ?>
										</span>
									</label>
								</td>
								<td>
									<strong><?php echo esc_html( $sub_label ); ?></strong>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render activity log metabox.
	 *
	 * @return void
	 */
	public static function render_activity_log_metabox(): void {
		$feature_id = $GLOBALS['wpshadow_feature_id'] ?? '';
		
		if ( empty( $feature_id ) ) {
			return;
		}

		$activity_log = self::get_feature_activity_log( $feature_id );
		?>
		<div class="postbox-actions" style="float: right; margin: -10px 0 10px 10px;">
			<button type="button" 
					class="button button-small wpshadow-refresh-log" 
					data-feature-id="<?php echo esc_attr( $feature_id ); ?>">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Refresh', 'wpshadow' ); ?>
			</button>
			<?php if ( ! empty( $activity_log ) ) : ?>
				<button type="button" 
						class="button button-small wpshadow-clear-log" 
						data-feature-id="<?php echo esc_attr( $feature_id ); ?>"
						style="margin-left: 5px;">
					<span class="dashicons dashicons-trash"></span>
					<?php esc_html_e( 'Clear History', 'wpshadow' ); ?>
				</button>
			<?php endif; ?>
		</div>
		
		<div class="feature-activity-log" data-feature-id="<?php echo esc_attr( $feature_id ); ?>">
			<?php if ( empty( $activity_log ) ) : ?>
				<p class="no-activity">
					<?php esc_html_e( 'No activity recorded yet.', 'wpshadow' ); ?>
				</p>
			<?php else : ?>
				<table class="widefat striped activity-log-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Time', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Action', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Details', 'wpshadow' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( array_reverse( $activity_log ) as $entry ) : ?>
							<tr>
								<td class="log-time">
									<?php echo esc_html( wp_date( 'Y-m-d H:i:s', $entry['timestamp'] ) ); ?>
								</td>
								<td class="log-action">
									<span class="log-level log-level-<?php echo esc_attr( $entry['level'] ?? 'info' ); ?>">
										<?php echo esc_html( $entry['action'] ?? 'N/A' ); ?>
									</span>
								</td>
								<td class="log-details">
									<?php echo esc_html( $entry['message'] ?? '' ); ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * AJAX: Toggle feature enabled/disabled.
	 *
	 * @return void
	 */
	public static function ajax_toggle_feature(): void {
		check_ajax_referer( 'wpshadow_feature_details', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$feature_id = isset( $_POST['feature_id'] ) ? sanitize_key( $_POST['feature_id'] ) : '';
		$enabled    = isset( $_POST['enabled'] ) && 'true' === $_POST['enabled'];

		if ( empty( $feature_id ) ) {
			wp_send_json_error( array( 'message' => __( 'That feature doesn\'t exist.', 'wpshadow' ) ) );
		}

		// Toggle feature state
		$result = WPSHADOW_Feature_Registry::set_feature_enabled( $feature_id, $enabled );

		if ( $result ) {
			// Log activity
			self::log_feature_activity(
				$feature_id,
				$enabled ? 'enabled' : 'disabled',
				sprintf(
					__( 'Feature %s by %s', 'wpshadow' ),
					$enabled ? 'enabled' : 'disabled',
					wp_get_current_user()->display_name
				),
				'info'
			);

			wp_send_json_success( array(
				'message' => $enabled ? __( 'Feature enabled.', 'wpshadow' ) : __( 'Feature disabled.', 'wpshadow' ),
				'enabled' => $enabled,
				'feature_id' => $feature_id,
				'debug' => 'Save successful',
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Couldn\'t toggle that feature.', 'wpshadow' ), 'debug' => 'set_feature_enabled returned false' ) );
		}
	}

	/**
	 * AJAX: Toggle feature setting.
	 *
	 * @return void
	 */
	public static function ajax_toggle_feature_setting(): void {
		check_ajax_referer( 'wpshadow_feature_details', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$feature_id  = isset( $_POST['feature_id'] ) ? sanitize_key( $_POST['feature_id'] ) : '';
		$setting_key = isset( $_POST['setting_key'] ) ? sanitize_key( $_POST['setting_key'] ) : '';
		$enabled     = isset( $_POST['enabled'] ) && 'true' === $_POST['enabled'];

		if ( empty( $feature_id ) || empty( $setting_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Something\'s not right with those settings.', 'wpshadow' ) ) );
		}

		// Update setting
		$option_name = 'wpshadow_' . $feature_id . '_' . $setting_key;
		update_option( $option_name, $enabled );

		// Log activity
		self::log_feature_activity(
			$feature_id,
			'setting_changed',
			sprintf(
				__( 'Setting "%s" %s by %s', 'wpshadow' ),
				$setting_key,
				$enabled ? 'enabled' : 'disabled',
				wp_get_current_user()->display_name
			),
			'info'
		);

		wp_send_json_success( array(
			'message' => __( 'Setting updated.', 'wpshadow' ),
			'enabled' => $enabled,
		) );
	}

	/**
	 * AJAX: Get feature activity log.
	 *
	 * @return void
	 */
	public static function ajax_get_feature_log(): void {
		check_ajax_referer( 'wpshadow_feature_details', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$feature_id = isset( $_POST['feature_id'] ) ? sanitize_key( $_POST['feature_id'] ) : '';

		if ( empty( $feature_id ) ) {
			wp_send_json_error( array( 'message' => __( 'That feature doesn\'t exist.', 'wpshadow' ) ) );
		}

		$log = self::get_feature_activity_log( $feature_id );

		wp_send_json_success( array( 'log' => $log ) );
	}

	/**
	 * AJAX: Clear feature activity log.
	 *
	 * @return void
	 */
	public static function ajax_clear_feature_log(): void {
		error_log( '🗑️ WPShadow: ajax_clear_feature_log called' );
		
		check_ajax_referer( 'wpshadow_feature_details', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			error_log( '❌ WPShadow: Insufficient permissions' );
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$feature_id = isset( $_POST['feature_id'] ) ? sanitize_key( $_POST['feature_id'] ) : '';
		error_log( '🗑️ WPShadow: Feature ID received: ' . $feature_id );

		if ( empty( $feature_id ) ) {
			error_log( '❌ WPShadow: Invalid feature ID' );
			wp_send_json_error( array( 'message' => __( 'Invalid feature ID.', 'wpshadow' ) ) );
		}

		// Clear the activity log for this specific feature only.
		$result = self::clear_feature_activity_log( $feature_id );
		error_log( '🗑️ WPShadow: Clear log result: ' . ( $result ? 'success' : 'failed' ) );

		if ( $result ) {
			wp_send_json_success( array(
				'message' => __( 'Activity log cleared successfully.', 'wpshadow' ),
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to clear activity log.', 'wpshadow' ) ) );
		}
	}

	/**
	 * Log feature activity.
	 *
	 * @param string $feature_id Feature ID.
	 * @param string $action     Action performed.
	 * @param string $message    Log message.
	 * @param string $level      Log level (info, warning, error, success).
	 * @return bool True on success.
	 */
	public static function log_feature_activity( string $feature_id, string $action, string $message, string $level = 'info' ): bool {
		$log_key = 'wpshadow_feature_log_' . $feature_id;
		$log     = get_option( $log_key, array() );

		if ( ! is_array( $log ) ) {
			$log = array();
		}

		$log[] = array(
			'timestamp' => time(),
			'action'    => sanitize_text_field( $action ),
			'message'   => sanitize_text_field( $message ),
			'level'     => sanitize_key( $level ),
			'user_id'   => get_current_user_id(),
		);

		// Keep only last 100 entries
		if ( count( $log ) > 100 ) {
			$log = array_slice( $log, -100 );
		}

		return update_option( $log_key, $log, false );
	}

	/**
	 * Get feature activity log.
	 *
	 * @param string $feature_id Feature ID.
	 * @param int    $limit      Maximum entries to return.
	 * @return array Activity log entries.
	 */
	public static function get_feature_activity_log( string $feature_id, int $limit = 50 ): array {
		$log_key = 'wpshadow_feature_log_' . $feature_id;
		$log     = get_option( $log_key, array() );

		if ( ! is_array( $log ) ) {
			return array();
		}

		return array_slice( $log, -$limit );
	}

	/**
	 * Clear feature activity log.
	 *
	 * @param string $feature_id Feature ID.
	 * @return bool True on success.
	 */
	public static function clear_feature_activity_log( string $feature_id ): bool {
		$log_key = 'wpshadow_feature_log_' . $feature_id;
		return delete_option( $log_key );
	}

	/**
	 * Get feature details URL.
	 *
	 * @param string $feature_id Feature ID.
	 * @return string Feature details URL.
	 */
	public static function get_feature_url( string $feature_id ): string {
		return add_query_arg(
			array(
				'page'    => 'wpshadow-feature-details',
				'feature' => $feature_id,
			),
			admin_url( 'admin.php' )
		);
	}
}
