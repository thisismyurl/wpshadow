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
	}

	/**
	 * Register hidden submenu page (not visible in menu).
	 *
	 * @return void
	 */
	public static function register_hidden_page(): void {
		add_submenu_page(
			null, // Hidden from menu
			__( 'Feature Details', 'plugin-wpshadow' ),
			__( 'Feature Details', 'plugin-wpshadow' ),
			'manage_options',
			'wpshadow-feature-details',
			array( __CLASS__, 'render_page' )
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

		wp_enqueue_style(
			'wpshadow-feature-details',
			WPSHADOW_URL . 'assets/css/feature-details.css',
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-feature-details',
			WPSHADOW_URL . 'assets/js/feature-details.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-feature-details',
			'wpshadowFeatureDetails',
			array(
				'nonce'      => wp_create_nonce( 'wpshadow_feature_details' ),
				'ajaxurl'    => admin_url( 'admin-ajax.php' ),
				'strings'    => array(
					'enabling'  => __( 'Enabling...', 'plugin-wpshadow' ),
					'disabling' => __( 'Disabling...', 'plugin-wpshadow' ),
					'enabled'   => __( 'Enabled', 'plugin-wpshadow' ),
					'disabled'  => __( 'Disabled', 'plugin-wpshadow' ),
					'error'     => __( 'Error: ', 'plugin-wpshadow' ),
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
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wpshadow' ) );
		}

		$feature_id = isset( $_GET['feature'] ) ? sanitize_key( $_GET['feature'] ) : '';
		
		if ( empty( $feature_id ) ) {
			wp_die( esc_html__( 'No feature specified.', 'plugin-wpshadow' ) );
		}

		$feature = WPSHADOW_Feature_Registry::get_feature( $feature_id );
		
		if ( ! $feature ) {
			wp_die( esc_html__( 'Feature not found.', 'plugin-wpshadow' ) );
		}

		// Track feature access for commonly accessed list (Issues #447 & #448).
		if ( class_exists( '\\WPShadow\\CoreSupport\\WPSHADOW_Feature_Search' ) ) {
			WPSHADOW_Feature_Search::track_feature_access( $feature_id );
		}

		$is_enabled    = $feature::is_enabled();
		$feature_obj   = $feature;
		$sub_features  = $feature_obj->get_sub_features();
		$activity_log  = self::get_feature_activity_log( $feature_id );
		
		?>
		<div class="wrap wpshadow-feature-details">
			<h1>
				<span class="dashicons <?php echo esc_attr( $feature_obj->get_icon() ); ?>"></span>
				<?php echo esc_html( $feature_obj->get_name() ); ?>
			</h1>

			<div class="wpshadow-feature-details-container">
				<!-- Main Feature Info Widget -->
				<div class="wpshadow-feature-info-widget postbox">
					<div class="postbox-header">
						<h2><?php esc_html_e( 'Feature Information', 'plugin-wpshadow' ); ?></h2>
					</div>
					<div class="inside">
						<div class="feature-meta">
							<div class="feature-description">
								<p><?php echo esc_html( $feature_obj->get_description() ); ?></p>
							</div>
							
							<table class="widefat striped">
								<tbody>
									<tr>
										<th><?php esc_html_e( 'Version:', 'plugin-wpshadow' ); ?></th>
										<td><?php echo esc_html( $feature_obj->get_version() ); ?></td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Category:', 'plugin-wpshadow' ); ?></th>
										<td><?php echo esc_html( ucfirst( $feature_obj->get_category() ) ); ?></td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'Scope:', 'plugin-wpshadow' ); ?></th>
										<td><?php echo esc_html( ucfirst( $feature_obj->get_scope() ) ); ?></td>
									</tr>
									<tr>
										<th><?php esc_html_e( 'License Level:', 'plugin-wpshadow' ); ?></th>
										<td><?php echo absint( $feature_obj->get_license_level() ); ?></td>
									</tr>
								</tbody>
							</table>
						</div>

						<!-- Main Feature Toggle -->
						<div class="feature-toggle-section">
							<h3><?php esc_html_e( 'Feature Status', 'plugin-wpshadow' ); ?></h3>
							<label class="wpshadow-toggle-switch">
								<input type="checkbox" 
									   class="wpshadow-feature-toggle" 
									   data-feature-id="<?php echo esc_attr( $feature_id ); ?>"
									   <?php checked( $is_enabled ); ?>>
								<span class="wpshadow-toggle-slider"></span>
								<span class="wpshadow-toggle-label">
									<?php echo $is_enabled ? esc_html__( 'Enabled', 'plugin-wpshadow' ) : esc_html__( 'Disabled', 'plugin-wpshadow' ); ?>
								</span>
							</label>
						</div>

						<!-- Sub-Features / Settings -->
						<?php if ( ! empty( $sub_features ) ) : ?>
							<div class="feature-sub-features-section">
								<h3><?php esc_html_e( 'Feature Settings', 'plugin-wpshadow' ); ?></h3>
								<p class="description">
									<?php esc_html_e( 'Individual settings for this feature:', 'plugin-wpshadow' ); ?>
								</p>
								
								<div class="sub-features-list">
									<?php foreach ( $sub_features as $sub_key => $sub_label ) : ?>
										<?php 
										$sub_enabled = get_option( 'wpshadow_' . $feature_id . '_' . $sub_key, false );
										?>
										<div class="sub-feature-item">
											<label class="wpshadow-toggle-switch">
												<input type="checkbox" 
													   class="wpshadow-sub-feature-toggle" 
													   data-feature-id="<?php echo esc_attr( $feature_id ); ?>"
													   data-setting-key="<?php echo esc_attr( $sub_key ); ?>"
													   <?php checked( $sub_enabled ); ?>
													   <?php disabled( ! $is_enabled ); ?>>
												<span class="wpshadow-toggle-slider"></span>
												<span class="wpshadow-toggle-label">
													<?php echo esc_html( $sub_label ); ?>
												</span>
											</label>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<!-- Activity Log Widget -->
				<div class="wpshadow-feature-log-widget postbox">
					<div class="postbox-header">
						<h2><?php esc_html_e( 'Activity Log', 'plugin-wpshadow' ); ?></h2>
						<div class="postbox-actions">
							<button type="button" 
									class="button button-small wpshadow-refresh-log" 
									data-feature-id="<?php echo esc_attr( $feature_id ); ?>">
								<span class="dashicons dashicons-update"></span>
								<?php esc_html_e( 'Refresh', 'plugin-wpshadow' ); ?>
							</button>
						</div>
					</div>
					<div class="inside">
						<div class="feature-activity-log" data-feature-id="<?php echo esc_attr( $feature_id ); ?>">
							<?php if ( empty( $activity_log ) ) : ?>
								<p class="no-activity">
									<?php esc_html_e( 'No activity recorded yet.', 'plugin-wpshadow' ); ?>
								</p>
							<?php else : ?>
								<table class="widefat striped activity-log-table">
									<thead>
										<tr>
											<th><?php esc_html_e( 'Time', 'plugin-wpshadow' ); ?></th>
											<th><?php esc_html_e( 'Action', 'plugin-wpshadow' ); ?></th>
											<th><?php esc_html_e( 'Details', 'plugin-wpshadow' ); ?></th>
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
					</div>
				</div>
			</div>

			<p class="back-link">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow' ) ); ?>">
					&larr; <?php esc_html_e( 'Back to Dashboard', 'plugin-wpshadow' ); ?>
				</a>
			</p>
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
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wpshadow' ) ) );
		}

		$feature_id = isset( $_POST['feature_id'] ) ? sanitize_key( $_POST['feature_id'] ) : '';
		$enabled    = isset( $_POST['enabled'] ) && 'true' === $_POST['enabled'];

		if ( empty( $feature_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid feature ID.', 'plugin-wpshadow' ) ) );
		}

		// Toggle feature state
		$result = WPSHADOW_Feature_Registry::set_feature_enabled( $feature_id, $enabled );

		if ( $result ) {
			// Log activity
			self::log_feature_activity(
				$feature_id,
				$enabled ? 'enabled' : 'disabled',
				sprintf(
					__( 'Feature %s by %s', 'plugin-wpshadow' ),
					$enabled ? 'enabled' : 'disabled',
					wp_get_current_user()->display_name
				),
				'info'
			);

			wp_send_json_success( array(
				'message' => $enabled ? __( 'Feature enabled.', 'plugin-wpshadow' ) : __( 'Feature disabled.', 'plugin-wpshadow' ),
				'enabled' => $enabled,
			) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to toggle feature.', 'plugin-wpshadow' ) ) );
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
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wpshadow' ) ) );
		}

		$feature_id  = isset( $_POST['feature_id'] ) ? sanitize_key( $_POST['feature_id'] ) : '';
		$setting_key = isset( $_POST['setting_key'] ) ? sanitize_key( $_POST['setting_key'] ) : '';
		$enabled     = isset( $_POST['enabled'] ) && 'true' === $_POST['enabled'];

		if ( empty( $feature_id ) || empty( $setting_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters.', 'plugin-wpshadow' ) ) );
		}

		// Update setting
		$option_name = 'wpshadow_' . $feature_id . '_' . $setting_key;
		update_option( $option_name, $enabled );

		// Log activity
		self::log_feature_activity(
			$feature_id,
			'setting_changed',
			sprintf(
				__( 'Setting "%s" %s by %s', 'plugin-wpshadow' ),
				$setting_key,
				$enabled ? 'enabled' : 'disabled',
				wp_get_current_user()->display_name
			),
			'info'
		);

		wp_send_json_success( array(
			'message' => __( 'Setting updated.', 'plugin-wpshadow' ),
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
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wpshadow' ) ) );
		}

		$feature_id = isset( $_POST['feature_id'] ) ? sanitize_key( $_POST['feature_id'] ) : '';

		if ( empty( $feature_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid feature ID.', 'plugin-wpshadow' ) ) );
		}

		$log = self::get_feature_activity_log( $feature_id );

		wp_send_json_success( array( 'log' => $log ) );
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
