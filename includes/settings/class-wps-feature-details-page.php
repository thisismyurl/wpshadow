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

		// Enqueue the feature toggle styles and scripts (for badge animations and feature toggles)
		wp_enqueue_style( 'wpshadow-feature-toggle' );
		wp_enqueue_script( 'wpshadow-feature-toggle' );
		wp_enqueue_script( 'postbox' );
		wp_enqueue_script( 'jquery-ui-sortable' );

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
	 * Handle form submission for feature settings.
	 *
	 * @param array $features Features array.
	 * @return void
	 */
	private static function handle_form_submit( array $features ): void {
		// Check for form submission
		if ( ! isset( $_POST['wpshadow_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpshadow_nonce'] ) ), 'wpshadow_features_nonce' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Process submitted data (identical logic to features.php)
		$submitted_data = isset( $_POST['wpshadow_features'] ) ? wp_unslash( $_POST['wpshadow_features'] ) : array();

		if ( ! is_array( $submitted_data ) ) {
			return;
		}

		foreach ( $features as $feature ) {
			$feature_id = $feature['id'] ?? '';
			$feature_key = 'feature_' . $feature_id;

			if ( ! isset( $submitted_data[ $feature_key ] ) ) {
				continue;
			}

			$is_enabled = ! empty( $submitted_data[ $feature_key ] );
			update_option( 'wpshadow_feature_' . $feature_id . '_enabled', $is_enabled );

			// Track the change in feature activity log
			if ( class_exists( '\\WPShadow\\CoreSupport\\WPSHADOW_Feature_Logger' ) ) {
				$action = $is_enabled ? 'enabled' : 'disabled';
				WPSHADOW_Feature_Logger::log_feature_activity( $feature_id, $action );
			}
		}
	}

	/**
	 * Build filtered feature list containing only target feature and its children.
	 *
	 * @param array  $all_features All features.
	 * @param string $feature_id   Target feature ID.
	 * @return array Filtered features array.
	 */
	private static function build_filtered_feature_list( array $all_features, string $feature_id ): array {
		$filtered = array();

		// Find the target feature
		foreach ( $all_features as $feature ) {
			if ( $feature['id'] === $feature_id ) {
				$filtered[] = $feature;

				// Add any child features
				foreach ( $all_features as $potential_child ) {
					if ( isset( $potential_child['parent'] ) && $potential_child['parent'] === $feature_id ) {
						$filtered[] = $potential_child;
					}
				}
				break;
			}
		}

		return $filtered;
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

		// First, try to get as a top-level feature
		$feature = WPSHADOW_Feature_Registry::get_feature( $feature_id );
		$is_sub_feature = false;
		$parent_feature_id = null;

		// If not found as top-level, search for it as a sub-feature
		if ( ! $feature ) {
			$all_features = WPSHADOW_Feature_Registry::get_all_features();
			foreach ( $all_features as $potential_parent ) {
				if ( isset( $potential_parent['sub_features'][ $feature_id ] ) ) {
					$feature = $potential_parent;
					$parent_feature_id = $potential_parent['id'];
					$is_sub_feature = true;
					break;
				}
			}
		}

		if ( ! $feature ) {
			wp_die( esc_html__( 'Feature not found.', 'wpshadow' ) );
		}

		// Track feature access for commonly accessed list (Issues #447 & #448).
		$track_id = $is_sub_feature ? $parent_feature_id : $feature_id;
		if ( class_exists( '\\WPShadow\\CoreSupport\\WPSHADOW_Feature_Search' ) ) {
			WPSHADOW_Feature_Search::track_feature_access( $track_id );
		}

		// Get all features from registry
		$all_features = WPSHADOW_Feature_Registry::get_all_features();

		// Handle form submission using the same logic as the Features tab
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['wpshadow_features_nonce'] ) ) {
			check_admin_referer( 'wpshadow_save_features', 'wpshadow_features_nonce' );

			$enabled_ids      = array();
			$sub_features_map = array();

			// Build a map of sub-feature IDs to their parent and key (based on the full registry)
			foreach ( $all_features as $feat ) {
				if ( ! empty( $feat['sub_features'] ) ) {
					$parent_id = $feat['id'] ?? '';
					foreach ( $feat['sub_features'] as $sub_key => $sub_data ) {
						$sub_features_map[ $sub_key ] = array(
							'parent_id' => $parent_id,
							'sub_key'   => $sub_key,
						);
					}
				}
			}

			if ( isset( $_POST['features'] ) && is_array( $_POST['features'] ) ) {
				foreach ( $_POST['features'] as $posted_id => $flag ) {
					$posted_id = sanitize_key( (string) $posted_id );

					// Check if this is a sub-feature ID
					if ( isset( $sub_features_map[ $posted_id ] ) ) {
						$sub_map     = $sub_features_map[ $posted_id ];
						$option_name = 'wpshadow_' . $sub_map['parent_id'] . '_' . $sub_map['sub_key'];
						$is_enabled  = ! empty( $flag );
						update_option( $option_name, $is_enabled ? 1 : 0 );
					} else {
						// Regular feature toggle
						$enabled_ids[] = $posted_id;
					}
				}
			}

			// Save unchecked sub-features as disabled (they won't be in POST data)
			foreach ( $sub_features_map as $sub_id => $sub_map ) {
				if ( ! isset( $_POST['features'][ $sub_id ] ) ) {
					$option_name = 'wpshadow_' . $sub_map['parent_id'] . '_' . $sub_map['sub_key'];
					update_option( $option_name, 0 );
				}
			}

			// Save parent features enabled state
			$all_feature_ids = wp_list_pluck( $all_features, 'id' );
			foreach ( $all_feature_ids as $id ) {
				$option_name = 'wpshadow_feature_' . $id . '_enabled';
				$is_enabled  = in_array( $id, $enabled_ids, true );
				update_option( $option_name, $is_enabled ? 1 : 0 );
			}
		}

		// If it's a sub-feature, render the parent and its children (keeps original UI/JS intact)
		if ( $is_sub_feature && $parent_feature_id ) {
			$features    = self::build_filtered_feature_list( $all_features, $parent_feature_id );
			$feature_id  = $parent_feature_id; // ensure template/JS see the parent context
		} else {
			$features = self::build_filtered_feature_list( $all_features, $feature_id );
		}

		// Enrich features with enabled status
		foreach ( $features as &$f ) {
			$enabled = get_option( 'wpshadow_feature_' . $f['id'] . '_enabled', true );
			$f['enabled'] = $enabled;
		}
		unset( $f );

		// Set up variables expected by features.php template
		$level            = 'core';
		$hub_id           = '';
		$spoke_id         = '';
		$network_scope    = is_multisite() && is_network_admin();
		$form_action      = add_query_arg(
			array(
				'page'    => 'wpshadow-feature-details',
				'feature' => $feature_id,
			),
			admin_url( 'admin.php' )
		);

		// Group features by widget group (same logic as in wpshadow_render_features_page)
		$grouped_features = array();
		foreach ( $features as $feature_item ) {
			$group = $feature_item['widget_group'] ?? 'general';
			if ( ! isset( $grouped_features[ $group ] ) ) {
				$grouped_features[ $group ] = array(
					'label'       => $feature_item['widget_label'] ?? 'General',
					'description' => $feature_item['widget_description'] ?? 'Features',
					'features'    => array(),
				);
			}
			$grouped_features[ $group ]['features'][] = $feature_item;
		}

		// Store grouped_features in global for metabox callbacks
		$GLOBALS['wpshadow_grouped_features'] = $grouped_features;

		// Register metaboxes using the same screen ID as the Features tab to reuse drag/drop + JS
		$screen_id = 'toplevel_page_wpshadow';
		foreach ( $grouped_features as $group_id => $group_data ) {
			add_meta_box(
				'wpshadow_features_' . sanitize_key( $group_id ),
				esc_html( $group_data['label'] ),
				'\\WPShadow\\wpshadow_render_feature_group_metabox',
				$screen_id,
				'normal',
				'default',
				array( 'group_id' => $group_id )
			);
		}

		// Include the shared features template with filtered list
		$views_file = WPSHADOW_PATH . 'includes/views/features.php';
		if ( file_exists( $views_file ) ) {
			// Override the screen ID for template use
			global $screen_base;
			$original_screen_base = $screen_base ?? null;
			$screen_base = $screen_id;

			// Temporarily modify the template to use our screen ID by setting it in global
			$GLOBALS['wpshadow_details_page_screen_id'] = $screen_id;

			include $views_file;

			// Restore original screen base
			if ( $original_screen_base ) {
				$screen_base = $original_screen_base;
			}
		}
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
	 * Check if we're on the feature details page for a specific feature.
	 *
	 * @param string $feature_id Feature ID to check.
	 * @return bool True if we're on the details page for this feature.
	 */
	public static function is_details_page( string $feature_id ): bool {
		$current_page   = sanitize_key( $_GET['page'] ?? '' );
		$current_tab    = sanitize_key( $_GET['wpshadow_tab'] ?? '' );
		$current_feature = sanitize_key( $_GET['feature'] ?? '' );

		return ( 'wpshadow' === $current_page && 'features' === $current_tab && $feature_id === $current_feature );
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
				'page'         => 'wpshadow',
				'wpshadow_tab' => 'features',
				'feature'      => $feature_id,
			),
			admin_url( 'admin.php' )
		);
	}
}
