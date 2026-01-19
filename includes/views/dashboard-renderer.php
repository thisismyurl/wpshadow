<?php
/**
 * Unified layout renderer for Dashboard, Features, and Help tabs.
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render unified two-column layout for main tabs.
 *
 * @param string $tab Current tab (dashboard, features, help).
 * @param string $hub_id Optional hub identifier.
 * @param string $spoke_id Optional spoke identifier.
 * @return void
 */
function wpshadow_render_unified_layout( string $tab = 'dashboard', string $hub_id = '', string $spoke_id = '' ): void {
	if ( ! WPSHADOW_can_access_dashboard() ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpshadow' ) );
	}

	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}

	// Enqueue postbox script for draggable/closable widgets.
	wp_enqueue_script( 'postbox' );
	wp_enqueue_style( 'dashboard' );

	// Add nonces for AJAX meta box saving
	wp_localize_script(
		'postbox',
		'postBoxL10n',
		array(
			'postBoxEmptyString' => __( 'Drag boxes here', 'wpshadow' ),
		)
	);
	wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
	wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );

	// Determine page title based on tab.
	$page_titles = array(
		'dashboard' => __( 'WPShadow Dashboard', 'wpshadow' ),
		'features'  => __( 'Features', 'wpshadow' ),
		'help'      => __( 'Help & Documentation', 'wpshadow' ),
	);
	$page_title = $page_titles[ $tab ] ?? __( 'WPShadow', 'wpshadow' );
	
	// If viewing a specific feature, show feature name in title
	if ( 'features' === $tab ) {
		$current_feature = isset( $_GET['feature'] ) ? sanitize_text_field( wp_unslash( $_GET['feature'] ) ) : '';
		if ( ! empty( $current_feature ) ) {
			// Get feature name from registry for proper title
			$all_features = WPSHADOW_Feature_Registry::get_features();
			foreach ( $all_features as $feature ) {
				if ( $feature['id'] === $current_feature ) {
					$page_title = $feature['name'] ?? ucwords( str_replace( array( '_', '-' ), ' ', $current_feature ) );
					break;
				}
				// Check sub-features
				if ( ! empty( $feature['sub_features'] ) ) {
					foreach ( $feature['sub_features'] as $sub_key => $sub_feature ) {
						if ( $sub_key === $current_feature ) {
							$page_title = $sub_feature['name'] ?? ucwords( str_replace( array( '_', '-' ), ' ', $current_feature ) );
							break 2;
						}
					}
				}
			}
		}
	}

	// Enable screen options for column layout.
	add_screen_option(
		'layout_columns',
		array(
			'max'     => 2,
			'default' => 2,
		)
	);

	// Register metaboxes for the current tab.
	wpshadow_register_tab_metaboxes( $tab, $screen->id );

	// Initialize postboxes with automatic state saving.
	add_action(
		'admin_print_footer_scripts',
		static function () use ( $screen ): void {
			?>
			<script>
			jQuery(document).ready(function($){
				if (typeof postboxes !== 'undefined') {
					// Initialize postboxes with our screen ID
					postboxes.add_postbox_toggles('<?php echo esc_js( $screen->id ); ?>');
					
					// Automatically save widget order when dragged
					$('.meta-box-sortables').sortable({
						update: function(event, ui) {
							if (typeof postboxes.save_order === 'function') {
								postboxes.save_order('<?php echo esc_js( $screen->id ); ?>');
							}
						}
					});
					
					// Save state when widgets are toggled
					$('.postbox .hndle, .postbox .handlediv').on('click', function() {
						setTimeout(function() {
							if (typeof postboxes.save_state === 'function') {
								postboxes.save_state('<?php echo esc_js( $screen->id ); ?>');
							}
						}, 100);
					});
				}
			});
			</script>
			<?php
		}
	);

	// Render the two-column layout.
	?>
	<div class="wrap wpshadow_tab_<?php echo esc_attr( $tab ); ?>">
		<h1><?php echo esc_html( $page_title ); ?></h1>
		
		<?php 
		$current_feature = isset( $_GET['feature'] ) ? sanitize_text_field( wp_unslash( $_GET['feature'] ) ) : '';
		wpshadow_render_breadcrumbs( $tab, $current_feature ); 
		?>
		
		<div id="dashboard-widgets" class="metabox-holder">
			<div id="postbox-container-1" class="postbox-container">
				<?php do_meta_boxes( $screen->id, 'normal', null ); ?>
			</div>
			<div id="postbox-container-2" class="postbox-container">
				<?php do_meta_boxes( $screen->id, 'side', null ); ?>
			</div>
		</div>
		<div class="clear"></div>
	</div>
	<?php
}

/**
 * Register metaboxes for each tab.
 *
 * @param string $tab Current tab.
 * @param string $screen_id Current screen ID.
 * @return void
 */
function wpshadow_register_tab_metaboxes( string $tab, string $screen_id ): void {
	// Check if viewing a specific feature
	$current_feature = isset( $_GET['feature'] ) ? sanitize_text_field( wp_unslash( $_GET['feature'] ) ) : '';
	
	switch ( $tab ) {
		case 'dashboard':
			wpshadow_register_dashboard_metaboxes( $screen_id );
			break;
		case 'features':
			if ( ! empty( $current_feature ) ) {
				// Viewing a specific feature
				wpshadow_register_feature_detail_metaboxes( $screen_id );
			} else {
				// Viewing features list
				wpshadow_register_features_metaboxes( $screen_id );
			}
			break;
		case 'help':
			wpshadow_register_help_metaboxes( $screen_id );
			break;
	}
}

/**
 * Register metaboxes for Dashboard tab.
 *
 * @param string $screen_id Current screen ID.
 * @return void
 */
function wpshadow_register_dashboard_metaboxes( string $screen_id ): void {
	// Left column (66%) - Main content.
	add_meta_box(
		'wpshadow_dashboard_overview',
		__( 'Dashboard Overview', 'wpshadow' ),
		__NAMESPACE__ . '\\wpshadow_render_dashboard_overview_widget',
		$screen_id,
		'normal',
		'high'
	);

	// Right column (33%) - Sidebar widgets.
	add_meta_box(
		'wpshadow_dashboard_health',
		__( 'System Health', 'wpshadow' ),
		__NAMESPACE__ . '\\wpshadow_render_dashboard_health_widget',
		$screen_id,
		'side',
		'high'
	);

	add_meta_box(
		'wpshadow_dashboard_history',
		__( 'Activity History', 'wpshadow' ),
		__NAMESPACE__ . '\\wpshadow_render_dashboard_history_widget',
		$screen_id,
		'side',
		'default'
	);

	add_meta_box(
		'wpshadow_dashboard_quick_actions',
		__( 'Quick Actions', 'wpshadow' ),
		__NAMESPACE__ . '\\wpshadow_render_dashboard_quick_actions_widget',
		$screen_id,
		'side',
		'default'
	);
}

/**
 * Register metaboxes for Features tab.
 *
 * @param string $screen_id Current screen ID.
 * @return void
 */
function wpshadow_register_features_metaboxes( string $screen_id ): void {
	// Check if a specific feature is being viewed
	$current_feature = isset( $_GET['feature'] ) ? sanitize_text_field( wp_unslash( $_GET['feature'] ) ) : '';
	
	// Left column (66%) - Feature list.
	add_meta_box(
		'wpshadow_features_list',
		__( 'Available Features', 'wpshadow' ),
		__NAMESPACE__ . '\\wpshadow_render_features_list_widget',
		$screen_id,
		'normal',
		'high'
	);
	
	// If a specific feature is selected, add a Settings widget
	if ( ! empty( $current_feature ) ) {
		add_meta_box(
			'wpshadow_feature_settings',
			__( 'Feature Settings', 'wpshadow' ),
			function() use ( $current_feature ) {
				wpshadow_render_feature_settings_widget( $current_feature );
			},
			$screen_id,
			'normal',
			'high'
		);
	}

	// Right column (33%) - Feature info.
	add_meta_box(
		'wpshadow_features_info',
		__( 'Feature Information', 'wpshadow' ),
		__NAMESPACE__ . '\\wpshadow_render_features_info_widget',
		$screen_id,
		'side',
		'high'
	);
}

/**
 * Register metaboxes for individual Feature detail pages.
 *
 * @param string $screen_id Current screen ID.
 * @return void
 */
function wpshadow_register_feature_detail_metaboxes( string $screen_id ): void {
	// Get the feature parameter from the URL
	$current_feature = isset( $_GET['feature'] ) ? sanitize_text_field( wp_unslash( $_GET['feature'] ) ) : '';
	
	// Get feature data for the action hook
	$network_scope = is_multisite() && is_network_admin();
	$all_features = WPSHADOW_Feature_Registry::get_features_by_scope( 'core', '', '', $network_scope );
	$feature_data = null;
	
	foreach ( $all_features as $f ) {
		if ( $f['id'] === $current_feature ) {
			$feature_data = $f;
			break;
		}
		// Check sub-features
		if ( ! empty( $f['sub_features'] ) && isset( $f['sub_features'][ $current_feature ] ) ) {
			$feature_data = $f;
			break;
		}
	}
	
	// Allow features to register their own metaboxes
	if ( ! empty( $current_feature ) && $feature_data ) {
		do_action( 'wpshadow_feature_details_metaboxes', $current_feature, $screen_id, $feature_data );
	}
	
	// Left column (66%) - Feature Settings
	if ( ! empty( $current_feature ) ) {
		add_meta_box(
			'wpshadow_feature_settings',
			__( 'Feature Settings', 'wpshadow' ),
			function() use ( $current_feature ) {
				wpshadow_render_feature_settings_widget( $current_feature );
			},
			$screen_id,
			'normal',
			'default'
		);
	}
	
	// Left column (66%) - Use EXACT SAME widget as features list page
	add_meta_box(
		'wpshadow_features_list',
		__( 'Available Features', 'wpshadow' ),
		__NAMESPACE__ . '\\wpshadow_render_features_list_widget',
		$screen_id,
		'normal',
		'high'
	);

	// Right column (33%) - Feature info (same as features list page)
	add_meta_box(
		'wpshadow_features_info',
		__( 'Feature Information', 'wpshadow' ),
		__NAMESPACE__ . '\\wpshadow_render_features_info_widget',
		$screen_id,
		'side',
		'high'
	);
	
	// Right column (33%) - Feature Log (only on feature detail pages)
	if ( ! empty( $current_feature ) ) {
		add_meta_box(
			'wpshadow_feature_log',
			__( 'Feature Log', 'wpshadow' ),
			function() use ( $current_feature ) {
				wpshadow_render_feature_log_widget( $current_feature );
			},
			$screen_id,
			'side',
			'default'
		);
	}
}

/**
 * Register metaboxes for Help tab.
 *
 * @param string $screen_id Current screen ID.
 * @return void
 */
function wpshadow_register_help_metaboxes( string $screen_id ): void {
	// Left column (66%) - Help content.
	add_meta_box(
		'wpshadow_help_content',
		__( 'Documentation', 'wpshadow' ),
		__NAMESPACE__ . '\\wpshadow_render_help_content_widget',
		$screen_id,
		'normal',
		'high'
	);

	// Right column (33%) - Help resources.
	add_meta_box(
		'wpshadow_help_resources',
		__( 'Support Resources', 'wpshadow' ),
		__NAMESPACE__ . '\\wpshadow_render_help_resources_widget',
		$screen_id,
		'side',
		'high'
	);
}

/**
 * Render dashboard overview widget.
 *
 * @return void
 */
function wpshadow_render_dashboard_overview_widget(): void {
	?>
	<div class="wpshadow-widget-content">
		<p><?php esc_html_e( 'Welcome to WPShadow Dashboard. Monitor your WordPress site health, performance, and security.', 'wpshadow' ); ?></p>
		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
			<div style="background: #f0f6fc; padding: 15px; border-radius: 4px; border-left: 4px solid #0073aa;">
				<h4 style="margin: 0 0 8px;"><?php esc_html_e( 'Features Active', 'wpshadow' ); ?></h4>
				<div style="font-size: 24px; font-weight: 600; color: #0073aa;">1</div>
			</div>
			<div style="background: #f0f6fc; padding: 15px; border-radius: 4px; border-left: 4px solid #00a32a;">
				<h4 style="margin: 0 0 8px;"><?php esc_html_e( 'System Status', 'wpshadow' ); ?></h4>
				<div style="font-size: 24px; font-weight: 600; color: #00a32a;"><?php esc_html_e( 'Healthy', 'wpshadow' ); ?></div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Render dashboard activity widget.
 *
 * @return void
 */
function wpshadow_render_dashboard_activity_widget(): void {
	?>
	<div class="wpshadow-widget-content">
		<p><?php esc_html_e( 'No recent activity to display.', 'wpshadow' ); ?></p>
	</div>
	<?php
}

/**
 * Render dashboard health widget.
 *
 * @return void
 */
function wpshadow_render_dashboard_health_widget(): void {
	// Gather system metrics
	$metrics = wpshadow_get_system_metrics();
	$health_score = wpshadow_calculate_health_score( $metrics );
	$health_status = wpshadow_get_health_status( $health_score );
	$indicators = wpshadow_get_health_indicators( $metrics );
	
	?>
	<div class="wpshadow-widget-content" style="padding: 15px;">
		<!-- Health Score Circle -->
		<div style="text-align: center; padding: 15px 0; border-bottom: 1px solid #dcdcde; margin-bottom: 15px;">
			<?php
			// Calculate stroke dash offset for circular progress
			$radius = 30;
			$circumference = 2 * pi() * $radius;
			$offset = $circumference - ( $health_score / 100 ) * $circumference;
			?>
			<div style="position: relative; width: 80px; height: 80px; margin: 0 auto 8px;">
				<svg width="80" height="80" style="transform: rotate(-90deg);">
					<!-- Background circle -->
					<circle
						cx="40"
						cy="40"
						r="<?php echo esc_attr( $radius ); ?>"
						fill="none"
						stroke="#f0f0f1"
						stroke-width="8"
					/>
					<!-- Progress circle -->
					<circle
						cx="40"
						cy="40"
						r="<?php echo esc_attr( $radius ); ?>"
						fill="none"
						stroke="<?php echo esc_attr( $health_status['color'] ); ?>"
						stroke-width="8"
						stroke-dasharray="<?php echo esc_attr( $circumference ); ?>"
						stroke-dashoffset="<?php echo esc_attr( $offset ); ?>"
						stroke-linecap="round"
						style="transition: stroke-dashoffset 0.5s ease;"
					/>
				</svg>
				<!-- Score text in center -->
				<div class="wpshadow-health-score-text" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 24px; font-weight: bold; color: #1d2327;">
					<?php echo esc_html( $health_score ); ?>
				</div>
			</div>
			<p class="wpshadow-health-status-label" style="margin: 0; color: <?php echo esc_attr( $health_status['color'] ); ?>; font-weight: 600; font-size: 14px;">
				<?php echo esc_html( $health_status['label'] ); ?>
			</p>
		</div>
		
		<!-- System Metrics -->
		<div style="margin-bottom: 12px;">
			<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
				<span style="font-size: 12px; color: #646970; font-weight: 600; display: flex; align-items: center; gap: 6px;">
					<?php esc_html_e( 'PHP Memory', 'wpshadow' ); ?>
					<span class="wpshadow-health-indicator" data-indicator="memory" title="<?php echo esc_attr( $indicators['memory']['tooltip'] ); ?>" style="cursor: help; font-size: 14px;">
						<?php echo $indicators['memory']['icon']; ?>
					</span>
				</span>
				<span data-metric="memory-usage" style="font-size: 12px; color: #1d2327; font-weight: 500;">
					<?php echo esc_html( $metrics['memory_usage'] ); ?> / <?php echo esc_html( $metrics['memory_limit'] ); ?>
				</span>
			</div>
			<div style="width: 100%; height: 6px; background: #f0f0f1; border-radius: 3px; overflow: hidden;">
				<div data-metric="memory-bar" style="width: <?php echo esc_attr( $metrics['memory_percent'] ); ?>%; height: 100%; background: <?php echo esc_attr( wpshadow_get_metric_color( $metrics['memory_percent'] ) ); ?>; transition: width 0.3s;"></div>
			</div>
		</div>
		
		<div style="margin-bottom: 12px;">
			<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
				<span style="font-size: 12px; color: #646970; font-weight: 600; display: flex; align-items: center; gap: 6px;">
					<?php esc_html_e( 'Disk Space', 'wpshadow' ); ?>
					<span class="wpshadow-health-indicator" data-indicator="disk" title="<?php echo esc_attr( $indicators['disk']['tooltip'] ); ?>" style="cursor: help; font-size: 14px;">
						<?php echo $indicators['disk']['icon']; ?>
					</span>
				</span>
				<span data-metric="disk-usage" style="font-size: 12px; color: #1d2327; font-weight: 500;">
					<?php echo esc_html( $metrics['disk_used'] ); ?> / <?php echo esc_html( $metrics['disk_total'] ); ?>
				</span>
			</div>
			<?php if ( $metrics['disk_percent'] > 0 ) : ?>
				<div style="width: 100%; height: 6px; background: #f0f0f1; border-radius: 3px; overflow: hidden;">
					<div data-metric="disk-bar" style="width: <?php echo esc_attr( $metrics['disk_percent'] ); ?>%; height: 100%; background: <?php echo esc_attr( wpshadow_get_metric_color( $metrics['disk_percent'] ) ); ?>; transition: width 0.3s;"></div>
				</div>
			<?php else : ?>
				<div style="font-size: 11px; color: #787c82; font-style: italic;">
					<?php esc_html_e( 'Disk space information not available', 'wpshadow' ); ?>
				</div>
			<?php endif; ?>
		</div>
		
		<!-- Quick Stats Grid -->
		<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #dcdcde;">
			<div style="background: #f6f7f7; padding: 10px; border-radius: 4px; text-align: center; position: relative;">
				<span class="wpshadow-health-indicator" data-indicator="php-version" title="<?php echo esc_attr( $indicators['php_version']['tooltip'] ); ?>" style="position: absolute; top: 8px; right: 8px; cursor: help; font-size: 12px;">
					<?php echo $indicators['php_version']['icon']; ?>
				</span>
				<div style="font-size: 11px; color: #646970; margin-bottom: 4px;">
					<?php esc_html_e( 'PHP Version', 'wpshadow' ); ?>
				</div>
				<div data-metric="php-version" style="font-size: 14px; color: #1d2327; font-weight: 600;">
					<?php echo esc_html( $metrics['php_version'] ); ?>
				</div>
			</div>
			
			<div style="background: #f6f7f7; padding: 10px; border-radius: 4px; text-align: center; position: relative;">
				<span class="wpshadow-health-indicator" data-indicator="wp-version" title="<?php echo esc_attr( $indicators['wp_version']['tooltip'] ); ?>" style="position: absolute; top: 8px; right: 8px; cursor: help; font-size: 12px;">
					<?php echo $indicators['wp_version']['icon']; ?>
				</span>
				<div style="font-size: 11px; color: #646970; margin-bottom: 4px;">
					<?php esc_html_e( 'WP Version', 'wpshadow' ); ?>
				</div>
				<div data-metric="wp-version" style="font-size: 14px; color: #1d2327; font-weight: 600;">
					<?php echo esc_html( $metrics['wp_version'] ); ?>
				</div>
			</div>
			
			<div style="background: #f6f7f7; padding: 10px; border-radius: 4px; text-align: center; position: relative;">
				<span class="wpshadow-health-indicator" data-indicator="max-upload" title="<?php echo esc_attr( $indicators['max_upload']['tooltip'] ); ?>" style="position: absolute; top: 8px; right: 8px; cursor: help; font-size: 12px;">
					<?php echo $indicators['max_upload']['icon']; ?>
				</span>
				<div style="font-size: 11px; color: #646970; margin-bottom: 4px;">
					<?php esc_html_e( 'Max Upload', 'wpshadow' ); ?>
				</div>
				<div data-metric="max-upload" style="font-size: 14px; color: #1d2327; font-weight: 600;">
					<?php echo esc_html( $metrics['max_upload'] ); ?>
				</div>
			</div>
			
			<div style="background: #f6f7f7; padding: 10px; border-radius: 4px; text-align: center; position: relative;">
				<span class="wpshadow-health-indicator" data-indicator="max-execution" title="<?php echo esc_attr( $indicators['max_execution']['tooltip'] ); ?>" style="position: absolute; top: 8px; right: 8px; cursor: help; font-size: 12px;">
					<?php echo $indicators['max_execution']['icon']; ?>
				</span>
				<div style="font-size: 11px; color: #646970; margin-bottom: 4px;">
					<?php esc_html_e( 'Max Execution', 'wpshadow' ); ?>
				</div>
				<div data-metric="max-execution" style="font-size: 14px; color: #1d2327; font-weight: 600;">
					<?php echo esc_html( $metrics['max_execution_time'] ); ?>s
				</div>
			</div>
		</div>
		
		<!-- Database Info -->
		<div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #dcdcde;">
			<div style="display: flex; justify-content: space-between; margin-bottom: 6px; align-items: center;">
				<span style="font-size: 12px; color: #646970; display: flex; align-items: center; gap: 6px;">
					<?php esc_html_e( 'Database Size:', 'wpshadow' ); ?>
					<span class="wpshadow-health-indicator" data-indicator="db-size" title="<?php echo esc_attr( $indicators['db_size']['tooltip'] ); ?>" style="cursor: help; font-size: 12px;">
						<?php echo $indicators['db_size']['icon']; ?>
					</span>
				</span>
				<span data-metric="db-size" style="font-size: 12px; color: #1d2327; font-weight: 500;"><?php echo esc_html( $metrics['db_size'] ); ?></span>
			</div>
			<div style="display: flex; justify-content: space-between; margin-bottom: 6px; align-items: center;">
				<span style="font-size: 12px; color: #646970; display: flex; align-items: center; gap: 6px;">
					<?php esc_html_e( 'Active Plugins:', 'wpshadow' ); ?>
					<span class="wpshadow-health-indicator" data-indicator="plugins" title="<?php echo esc_attr( $indicators['plugins']['tooltip'] ); ?>" style="cursor: help; font-size: 12px;">
						<?php echo $indicators['plugins']['icon']; ?>
					</span>
				</span>
				<span data-metric="active-plugins" style="font-size: 12px; color: #1d2327; font-weight: 500;"><?php echo esc_html( $metrics['active_plugins'] ); ?></span>
			</div>
			<div style="display: flex; justify-content: space-between; align-items: center;">
				<span style="font-size: 12px; color: #646970; display: flex; align-items: center; gap: 6px;">
					<?php esc_html_e( 'Active Theme:', 'wpshadow' ); ?>
					<span class="wpshadow-health-indicator" data-indicator="theme" title="<?php echo esc_attr( $indicators['theme']['tooltip'] ); ?>" style="cursor: help; font-size: 12px;">
						<?php echo $indicators['theme']['icon']; ?>
					</span>
				</span>
				<span data-metric="active-theme" style="font-size: 12px; color: #1d2327; font-weight: 500;"><?php echo esc_html( $metrics['active_theme'] ); ?></span>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Render dashboard quick actions widget.
 *
 * @return void
 */
function wpshadow_render_dashboard_quick_actions_widget(): void {
	?>
	<div class="wpshadow-widget-content">
		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&wpshadow_tab=features' ) ); ?>" class="button button-primary" style="width: 100%; text-align: center;"><?php esc_html_e( 'Manage Features', 'wpshadow' ); ?></a></p>
		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&wpshadow_tab=help' ) ); ?>" class="button button-secondary" style="width: 100%; text-align: center;"><?php esc_html_e( 'View Documentation', 'wpshadow' ); ?></a></p>
	</div>
	<?php
}

/**
 * Render dashboard activity history widget.
 *
 * @return void
 */
function wpshadow_render_dashboard_history_widget(): void {
	$logs = wpshadow_get_all_feature_logs( 15 );
	
	if ( empty( $logs ) ) {
		?>
		<div class="wpshadow-widget-content" style="margin: 15px; padding: 12px;">
			<p style="color: #646970; font-style: italic; margin: 0; text-align: center;">
				<?php esc_html_e( 'No activity logged yet.', 'wpshadow' ); ?>
			</p>
		</div>
		<?php
		return;
	}
	
	?>
	<div class="wpshadow-widget-content" style="margin: 15px;">
		<div class="wpshadow-feature-log-timeline">
			<?php foreach ( $logs as $log ) : ?>
				<div class="wpshadow-log-entry" data-action="<?php echo esc_attr( $log['action'] ); ?>">
					<div class="wpshadow-log-dot"></div>
					<div class="wpshadow-log-line"></div>
					<div class="wpshadow-log-content">
						<div class="wpshadow-log-header">
							<span class="wpshadow-log-action">
								<?php echo esc_html( $log['action_label'] ); ?>
							</span>
							<span class="wpshadow-log-time" title="<?php echo esc_attr( $log['timestamp_full'] ); ?>">
								<?php echo esc_html( $log['timestamp_human'] ); ?>
							</span>
						</div>
						<div class="wpshadow-log-feature">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&wpshadow_tab=features&feature=' . urlencode( $log['feature_id'] ) ) ); ?>">
								<?php echo esc_html( $log['feature_name'] ); ?>
							</a>
						</div>
						<?php if ( ! empty( $log['message'] ) ) : ?>
							<div class="wpshadow-log-message"><?php echo esc_html( $log['message'] ); ?></div>
						<?php endif; ?>
						<?php if ( ! empty( $log['user'] ) ) : ?>
							<div class="wpshadow-log-user">by <?php echo esc_html( $log['user'] ); ?></div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		
		<?php if ( count( $logs ) >= 15 ) : ?>
			<div style="text-align: center; padding-top: 10px; border-top: 1px solid #dcdcde; margin-top: 10px;">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&wpshadow_tab=features' ) ); ?>" class="button button-small">
					<?php esc_html_e( 'View All Activity', 'wpshadow' ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render feature settings widget for a specific feature.
 *
 * @param string $feature_id The feature ID to show settings for.
 * @return void
 */
function wpshadow_render_feature_settings_widget( string $feature_id ): void {
	$network_scope = is_multisite() && is_network_admin();
	$features = WPSHADOW_Feature_Registry::get_features_by_scope( 'core', '', '', $network_scope );
	
	// Find the feature - first try direct match, then check if it's a sub-feature
	$feature = null;
	$parent_feature = null;
	$requested_sub_key = null;
	
	foreach ( $features as $f ) {
		if ( $f['id'] === $feature_id ) {
			$feature = $f;
			break;
		}
		// Check if this feature has the requested feature as a sub-feature
		if ( ! empty( $f['sub_features'] ) && isset( $f['sub_features'][ $feature_id ] ) ) {
			$parent_feature = $f;
			$requested_sub_key = $feature_id;
			break;
		}
	}
	
	// If the requested ID is a sub-feature, use the parent to surface any configurable children.
	if ( $parent_feature ) {
		$feature = $parent_feature;
	}
	
	// Get sub-features that have configuration UI (not just toggles)
	$config_sub_features = wpshadow_get_configurable_sub_features( $feature );

	// When viewing a sub-feature, scope settings to itself and its child configs, not siblings.
	if ( $requested_sub_key && ! empty( $config_sub_features ) ) {
		// Check if the requested sub-feature itself has settings (is a config sub-feature)
		$is_config_feature = isset( $config_sub_features[ $requested_sub_key ] );
		
		// If it's a toggle sub-feature (not a config feature), show it prominently with its related configs
		if ( ! $is_config_feature && isset( $feature['sub_features'][ $requested_sub_key ] ) ) {
			$sub_feature_data = $feature['sub_features'][ $requested_sub_key ];
			$sub_enabled = (bool) get_option( "wpshadow_{$feature['id']}_{$requested_sub_key}", $sub_feature_data['default_enabled'] ?? true );
			
			// Map of which config sub-features belong to which toggle sub-features
			$child_config_map = array(
				'asset-version-removal' => array(
					'remove_css_versions'      => array( 'css_ignore_rules' ),
					'remove_js_versions'       => array( 'js_ignore_rules' ),
					'preserve_plugin_versions' => array( 'plugin_ignore_list' ),
				),
			);
			
			// Get related config sub-features for this toggle sub-feature
			$related_configs = array();
			if ( isset( $child_config_map[ $feature['id'] ][ $requested_sub_key ] ) ) {
				$config_keys = $child_config_map[ $feature['id'] ][ $requested_sub_key ];
				foreach ( $config_keys as $config_key ) {
					if ( isset( $config_sub_features[ $config_key ] ) ) {
						$related_configs[ $config_key ] = $config_sub_features[ $config_key ];
					}
				}
			}
			?>
			<div class="wpshadow-widget-content">
				<!-- Only show related configuration sub-features, not the toggle sub-feature itself -->
				<?php if ( ! empty( $related_configs ) ) : ?>
					<?php foreach ( $related_configs as $config_key => $config_data ) :
						$config_enabled = (bool) get_option( "wpshadow_{$feature['id']}_{$config_key}", $config_data['default_enabled'] ?? true );
					?>
						<div style="padding: 16px; border-bottom: 1px solid #e5e5e5;">
							<div style="margin-bottom: 12px;">
								<strong style="font-size: 13px;">
									<?php echo esc_html( $config_data['name'] ?? $config_key ); ?>
								</strong>
								<?php if ( ! empty( $config_data['description'] ) ) : ?>
									<div style="color: #646970; font-size: 12px; margin-top: 4px;">
										<?php echo esc_html( $config_data['description'] ); ?>
									</div>
								<?php endif; ?>
							</div>
							<?php
							$config_renderers = array(
								__NAMESPACE__ . '\wpshadow_render_' . $feature['id'] . '_' . $config_key . '_config',
								__NAMESPACE__ . '\wpshadow_render_' . $config_key . '_config',
								'wpshadow_render_' . $feature['id'] . '_' . $config_key . '_config',
								'wpshadow_render_' . $config_key . '_config',
							);

							foreach ( $config_renderers as $config_renderer ) {
								if ( function_exists( $config_renderer ) ) {
									call_user_func( $config_renderer, $config_enabled );
									break;
								}
							}
							?>
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<p style="color: #646970; padding: 12px;"><?php esc_html_e( 'No settings available for this feature.', 'wpshadow' ); ?></p>
				<?php endif; ?>
			</div>
			<?php
			return;
		}
		
		// If it's a config feature itself, just show that one
		$allowed_keys = array();
		if ( isset( $config_sub_features[ $requested_sub_key ] ) ) {
			$allowed_keys[] = $requested_sub_key;
		}

		if ( ! empty( $allowed_keys ) ) {
			$config_sub_features = array_intersect_key( $config_sub_features, array_flip( $allowed_keys ) );
		} else {
			$config_sub_features = array();
		}
	}
	
	// Regular feature or parent feature with configurable children
	$config_sub_features = $config_sub_features ?? wpshadow_get_configurable_sub_features( $feature );

	if ( ! $feature || empty( $config_sub_features ) ) {
		?>
		<div class="wpshadow-widget-content">
			<p style="color: #646970; padding: 12px;"><?php esc_html_e( 'No settings available for this feature.', 'wpshadow' ); ?></p>
		</div>
		<?php
		return;
	}
	
	?>
	<div class="wpshadow-widget-content">
		<?php foreach ( $config_sub_features as $sub_key => $sub_feature ) :
			$sub_enabled = (bool) get_option( "wpshadow_{$feature['id']}_{$sub_key}", $sub_feature['default_enabled'] ?? true );
		?>
			<div style="padding: 16px; border-bottom: 1px solid #e5e5e5;">
				<div style="margin-bottom: 12px; display: flex; justify-content: space-between; align-items: flex-start;">
					<div style="flex: 1;">
						<div style="margin-bottom: 4px;">
							<strong style="font-size: 13px;">
								<?php echo esc_html( $sub_feature['name'] ?? $sub_key ); ?>
							</strong>
						</div>
						<?php if ( ! empty( $sub_feature['description'] ) ) : ?>
							<div style="color: #646970; font-size: 12px;">
								<?php echo esc_html( $sub_feature['description'] ); ?>
							</div>
						<?php endif; ?>
					</div>
					<label class="wpshadow-feature-toggle" style="margin-left: 16px; flex-shrink: 0;">
						<input type="checkbox" 
							   class="wpshadow-subfeature-toggle-input" 
							   data-feature-id="<?php echo esc_attr( $feature['id'] ); ?>"
							   data-subfeature-key="<?php echo esc_attr( $sub_key ); ?>"
							   <?php checked( $sub_enabled ); ?>>
						<span class="wpshadow-feature-toggle-slider"></span>
					</label>
				</div>
				<?php
				$config_renderers = array(
					__NAMESPACE__ . '\wpshadow_render_' . $feature['id'] . '_' . $sub_key . '_config',
					__NAMESPACE__ . '\wpshadow_render_' . $sub_key . '_config',
					'wpshadow_render_' . $feature['id'] . '_' . $sub_key . '_config',
					'wpshadow_render_' . $sub_key . '_config',
				);

				foreach ( $config_renderers as $config_renderer ) {
					if ( function_exists( $config_renderer ) ) {
						call_user_func( $config_renderer, $sub_enabled );
						break;
					}
				}
				?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

function wpshadow_get_configurable_sub_features( array $feature ): array {
	if ( empty( $feature['sub_features'] ) ) {
		return array();
	}

	$configurable = array();
	foreach ( $feature['sub_features'] as $sub_key => $sub_feature ) {
		// Check if this sub-feature declares it has settings
		if ( ! empty( $sub_feature['has_settings'] ) ) {
			$configurable[ $sub_key ] = $sub_feature;
		}
	}

	return $configurable;
}

/**
 * Render CSS ignore rules configuration.
 *
 * @param bool $enabled Whether this setting is enabled.
 * @return void
 */
function wpshadow_render_css_ignore_rules_config( bool $enabled ): void {
	$patterns = get_option( 'wpshadow_asset-version-removal_css_ignore_patterns', array() );
	?>
	<div style="margin-top: 12px; padding: 12px; background: #f5f5f5; border-radius: 4px;">
		<?php if ( ! $enabled ) : ?>
			<p style="margin: 0 0 8px 0; font-size: 12px; color: #d63638;"><strong><?php esc_html_e( 'This setting is currently disabled, but you can update its values below.', 'wpshadow' ); ?></strong></p>
		<?php endif; ?>
		<p style="margin: 0 0 8px 0; font-size: 12px; color: #646970;"><strong><?php esc_html_e( 'Ignore Patterns (one per line):', 'wpshadow' ); ?></strong></p>
		<textarea id="wpshadow-css-ignore-patterns" 
			style="width: 100%; height: 100px; font-family: monospace; font-size: 12px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
			placeholder="<?php esc_attr_e( 'Example: /googleapis.com/, *.cloudflare.*, or /^https:.*cdn.*\\.js$/', 'wpshadow' ); ?>"><?php echo esc_textarea( implode( "\n", $patterns ) ); ?></textarea>
		<p style="margin: 8px 0 0 0; font-size: 11px; color: #999;">
			<?php esc_html_e( 'Patterns starting and ending with / are treated as regex. Others use simple wildcard matching.', 'wpshadow' ); ?>
		</p>
		<button type="button" class="button button-small" id="wpshadow-save-css-ignore-rules" style="margin-top: 8px;">
			<?php esc_html_e( 'Save CSS Rules', 'wpshadow' ); ?>
		</button>
	</div>
	<script>
	jQuery(document).ready(function($) {
		$('#wpshadow-save-css-ignore-rules').on('click', function() {
			var $btn = $(this);
			var patterns = $('#wpshadow-css-ignore-patterns').val().split('\n').filter(Boolean);
			
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_save_css_ignore_rules',
					nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_save_css_ignore_rules' ) ); ?>',
					patterns: patterns
				},
				beforeSend: function() {
					$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Saving...', 'wpshadow' ) ); ?>');
				},
				success: function(response) {
					if (response.success) {
						$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Save CSS Rules', 'wpshadow' ) ); ?>');
						// Trigger toast notification
						if (typeof $(document).trigger === 'function') {
							// Show toast by triggering a custom event that the global toast handler can listen to
							// Or call showToast directly if it's available
							try {
								showToast('<?php echo esc_js( __( 'CSS ignore patterns saved', 'wpshadow' ) ); ?>', true, 3000);
							} catch (e) {
								// Fallback if showToast not available
								console.log('CSS patterns saved successfully');
							}
						}
					} else {
						$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Save CSS Rules', 'wpshadow' ) ); ?>');
						try {
							showToast('<?php echo esc_js( __( 'Failed to save CSS patterns', 'wpshadow' ) ); ?>', false, 5000);
						} catch (e) {
							console.error('Failed to save patterns');
						}
					}
				},
				error: function() {
					$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Save CSS Rules', 'wpshadow' ) ); ?>');
					try {
						showToast('<?php echo esc_js( __( 'Error saving CSS patterns', 'wpshadow' ) ); ?>', false, 5000);
					} catch (e) {
						console.error('Error during save');
					}
				}
			});
		});
	});
	</script>
	<?php
}

/**
 * Render JavaScript ignore rules configuration.
 *
 * @param bool $enabled Whether this setting is enabled.
 * @return void
 */
function wpshadow_render_js_ignore_rules_config( bool $enabled ): void {
	$patterns = get_option( 'wpshadow_asset-version-removal_js_ignore_patterns', array() );
	?>
	<div style="margin-top: 12px; padding: 12px; background: #f5f5f5; border-radius: 4px;">
		<?php if ( ! $enabled ) : ?>
			<p style="margin: 0 0 8px 0; font-size: 12px; color: #d63638;"><strong><?php esc_html_e( 'This setting is currently disabled, but you can update its values below.', 'wpshadow' ); ?></strong></p>
		<?php endif; ?>
		<p style="margin: 0 0 8px 0; font-size: 12px; color: #646970;"><strong><?php esc_html_e( 'Ignore Patterns (one per line):', 'wpshadow' ); ?></strong></p>
		<textarea id="wpshadow-js-ignore-patterns" 
			style="width: 100%; height: 100px; font-family: monospace; font-size: 12px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
			placeholder="<?php esc_attr_e( 'Example: /googleapis.com/, *.cloudflare.*, or /^https:.*cdn.*\\.js$/', 'wpshadow' ); ?>"><?php echo esc_textarea( implode( "\n", $patterns ) ); ?></textarea>
		<p style="margin: 8px 0 0 0; font-size: 11px; color: #999;">
			<?php esc_html_e( 'Patterns starting and ending with / are treated as regex. Others use simple wildcard matching.', 'wpshadow' ); ?>
		</p>
		<button type="button" class="button button-small" id="wpshadow-save-js-ignore-rules" style="margin-top: 8px;">
			<?php esc_html_e( 'Save JS Rules', 'wpshadow' ); ?>
		</button>
	</div>
	<script>
	jQuery(document).ready(function($) {
		$('#wpshadow-save-js-ignore-rules').on('click', function() {
			var $btn = $(this);
			var patterns = $('#wpshadow-js-ignore-patterns').val().split('\n').filter(Boolean);
			
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_save_js_ignore_rules',
					nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_save_js_ignore_rules' ) ); ?>',
					patterns: patterns
				},
				beforeSend: function() {
					$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Saving...', 'wpshadow' ) ); ?>');
				},
				success: function(response) {
					if (response.success) {
						$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Save JS Rules', 'wpshadow' ) ); ?>');
						// Trigger toast notification
						try {
							showToast('<?php echo esc_js( __( 'JavaScript ignore patterns saved', 'wpshadow' ) ); ?>', true, 3000);
						} catch (e) {
							console.log('JS patterns saved successfully');
						}
					} else {
						$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Save JS Rules', 'wpshadow' ) ); ?>');
						try {
							showToast('<?php echo esc_js( __( 'Failed to save JS patterns', 'wpshadow' ) ); ?>', false, 5000);
						} catch (e) {
							console.error('Failed to save patterns');
						}
					}
				},
				error: function() {
					$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Save JS Rules', 'wpshadow' ) ); ?>');
					try {
						showToast('<?php echo esc_js( __( 'Error saving JS patterns', 'wpshadow' ) ); ?>', false, 5000);
					} catch (e) {
						console.error('Error during save');
					}
				}
			});
		});
	});
	</script>
	<?php
}

/**
 * Render plugin ignore list configuration.
 *
 * @param bool $enabled Whether this setting is enabled.
 * @return void
 */
function wpshadow_render_plugin_ignore_list_config( bool $enabled ): void {
	$ignored_plugins = get_option( 'wpshadow_asset-version-removal_ignored_plugins', array() );
	$active_plugins = array_keys( get_plugins() );
	
	// Get plugin data for better display.
	$plugin_list = array();
	foreach ( $active_plugins as $plugin_path ) {
		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_path );
		$plugin_slug = dirname( $plugin_path );
		$plugin_list[ $plugin_slug ] = $plugin_data['Name'] ?? $plugin_slug;
	}
	
	asort( $plugin_list );
	?>
	<div style="margin-top: 12px; padding: 12px; background: #f5f5f5; border-radius: 4px;">
		<?php if ( ! $enabled ) : ?>
			<p style="margin: 0 0 8px 0; font-size: 12px; color: #d63638;"><strong><?php esc_html_e( 'This setting is currently disabled, but you can update its values below.', 'wpshadow' ); ?></strong></p>
		<?php endif; ?>
		<p style="margin: 0 0 12px 0; font-size: 12px; color: #646970;"><strong><?php esc_html_e( 'Select plugins to ignore:', 'wpshadow' ); ?></strong></p>
		<div id="wpshadow-plugin-ignore-list" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; padding: 8px;">
			<?php foreach ( $plugin_list as $plugin_slug => $plugin_name ) : 
				$is_checked = in_array( $plugin_slug, $ignored_plugins, true );
			?>
				<label style="display: block; margin: 6px 0; cursor: pointer;">
					<input type="checkbox" 
						   class="wpshadow-plugin-ignore-checkbox" 
						   data-plugin-slug="<?php echo esc_attr( $plugin_slug ); ?>"
						   value="<?php echo esc_attr( $plugin_slug ); ?>"
						   <?php checked( $is_checked ); ?>>
					<span><?php echo esc_html( $plugin_name ); ?></span>
				</label>
			<?php endforeach; ?>
		</div>
		<button type="button" class="button button-small" id="wpshadow-save-plugin-ignore-list" style="margin-top: 8px;">
			<?php esc_html_e( 'Save Plugin List', 'wpshadow' ); ?>
		</button>
	</div>
	<script>
	jQuery(document).ready(function($) {
		$('#wpshadow-save-plugin-ignore-list').on('click', function() {
			var $btn = $(this);
			var plugins = [];
			$('.wpshadow-plugin-ignore-checkbox:checked').each(function() {
				plugins.push($(this).val());
			});
			
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_save_plugin_ignore_list',
					nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_save_plugin_ignore_list' ) ); ?>',
					plugins: plugins
				},
				beforeSend: function() {
					$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Saving...', 'wpshadow' ) ); ?>');
				},
				success: function(response) {
					if (response.success) {
						$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Save Plugin List', 'wpshadow' ) ); ?>');
						// Trigger toast notification
						try {
							showToast('<?php echo esc_js( __( 'Plugin ignore list saved', 'wpshadow' ) ); ?>', true, 3000);
						} catch (e) {
							console.log('Plugin list saved successfully');
						}
					} else {
						$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Save Plugin List', 'wpshadow' ) ); ?>');
						try {
							showToast('<?php echo esc_js( __( 'Failed to save plugin list', 'wpshadow' ) ); ?>', false, 5000);
						} catch (e) {
							console.error('Failed to save list');
						}
					}
				},
				error: function() {
					$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Save Plugin List', 'wpshadow' ) ); ?>');
					try {
						showToast('<?php echo esc_js( __( 'Error saving plugin list', 'wpshadow' ) ); ?>', false, 5000);
					} catch (e) {
						console.error('Error during save');
					}
				}
			});
		});
	});
	</script>
	<?php
}

/**
 * Render advanced settings configuration for external fonts disabler.
 *
 * @param bool $enabled Whether this setting is enabled.
 * @return void
 */
function wpshadow_render_advanced_settings_config( bool $enabled ): void {
	$whitelist = get_option( 'wpshadow_external_fonts_whitelist', '' );
	$system_fallback = get_option( 'wpshadow_external_fonts_system_fallback', '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif' );
	$admin_only = get_option( 'wpshadow_external_fonts_admin_only', false );
	$log_blocked = get_option( 'wpshadow_external_fonts_log_blocked', false );
	?>
	<div style="margin-top: 12px; padding: 12px; background: #f5f5f5; border-radius: 4px;">
		<?php if ( ! $enabled ) : ?>
			<p style="margin: 0 0 8px 0; font-size: 12px; color: #d63638;"><strong><?php esc_html_e( 'This setting is currently disabled, but you can update its values below.', 'wpshadow' ); ?></strong></p>
		<?php endif; ?>
		
		<div style="margin-bottom: 16px;">
			<p style="margin: 0 0 8px 0; font-size: 12px; color: #646970;"><strong><?php esc_html_e( 'Allowed Font URLs (Whitelist):', 'wpshadow' ); ?></strong></p>
			<textarea id="wpshadow-external-fonts-whitelist" 
				style="width: 100%; height: 100px; font-family: monospace; font-size: 12px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
				placeholder="<?php esc_attr_e( 'https://fonts.googleapis.com/css2?family=Roboto', 'wpshadow' ); ?>"><?php echo esc_textarea( $whitelist ); ?></textarea>
			<p style="margin: 8px 0 0 0; font-size: 11px; color: #999;">
				<?php esc_html_e( 'Enter font URLs (one per line) that should be allowed even when blocking is enabled.', 'wpshadow' ); ?>
			</p>
		</div>
		
		<div style="margin-bottom: 16px;">
			<p style="margin: 0 0 8px 0; font-size: 12px; color: #646970;"><strong><?php esc_html_e( 'System Font Fallback:', 'wpshadow' ); ?></strong></p>
			<input type="text" id="wpshadow-external-fonts-fallback" 
				value="<?php echo esc_attr( $system_fallback ); ?>"
				style="width: 100%; font-family: monospace; font-size: 12px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
				placeholder="-apple-system, BlinkMacSystemFont, sans-serif" />
			<p style="margin: 8px 0 0 0; font-size: 11px; color: #999;">
				<?php esc_html_e( 'CSS font-family stack to use as fallback when external fonts are blocked.', 'wpshadow' ); ?>
			</p>
		</div>
		
		<div style="margin-bottom: 16px;">
			<label style="display: block; margin-bottom: 8px;">
				<input type="checkbox" id="wpshadow-external-fonts-admin-only" <?php checked( $admin_only, true ); ?> />
				<span style="font-size: 12px;"><?php esc_html_e( 'Block on admin pages only (allow on frontend)', 'wpshadow' ); ?></span>
			</label>
			<label style="display: block;">
				<input type="checkbox" id="wpshadow-external-fonts-log-blocked" <?php checked( $log_blocked, true ); ?> />
				<span style="font-size: 12px;"><?php esc_html_e( 'Log blocked fonts to browser console (for debugging)', 'wpshadow' ); ?></span>
			</label>
		</div>
		
		<button type="button" class="button button-small" id="wpshadow-save-external-fonts-settings" style="margin-top: 8px;">
			<?php esc_html_e( 'Save Advanced Settings', 'wpshadow' ); ?>
		</button>
	</div>
	<script>
	jQuery(document).ready(function($) {
		$('#wpshadow-save-external-fonts-settings').on('click', function() {
			var $btn = $(this);
			var whitelist = $('#wpshadow-external-fonts-whitelist').val();
			var fallback = $('#wpshadow-external-fonts-fallback').val();
			var adminOnly = $('#wpshadow-external-fonts-admin-only').is(':checked');
			var logBlocked = $('#wpshadow-external-fonts-log-blocked').is(':checked');
			
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_save_external_fonts_settings',
					nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_save_external_fonts_settings' ) ); ?>',
					whitelist: whitelist,
					fallback: fallback,
					admin_only: adminOnly ? '1' : '0',
					log_blocked: logBlocked ? '1' : '0'
				},
				beforeSend: function() {
					$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Saving...', 'wpshadow' ) ); ?>');
				},
				success: function(response) {
					if (response.success) {
						$btn.css('background-color', '#90EE90');
						setTimeout(function() {
							$btn.css('background-color', '').prop('disabled', false).text('<?php echo esc_js( __( 'Save Advanced Settings', 'wpshadow' ) ); ?>');
						}, 1500);
					}
				},
				error: function() {
					$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Save Advanced Settings', 'wpshadow' ) ); ?>');
				}
			});
		});
	});
	</script>
	<?php
}

/**
 * Render features list widget.
 *
 * @return void
 */
function wpshadow_render_features_list_widget(): void {
	$network_scope   = is_multisite() && is_network_admin();
	$current_feature = isset( $_GET['feature'] ) ? sanitize_text_field( wp_unslash( $_GET['feature'] ) ) : '';
	$all_features    = WPSHADOW_Feature_Registry::get_features_by_scope( 'core', '', '', $network_scope );

	// Determine display mode and selection
	$mode = 'all'; // all | parent | child
	$selected_parent = array();
	$selected_child  = array(); // [ 'parent_id' => string, 'key' => string, 'data' => array ]

	if ( ! empty( $current_feature ) ) {
		foreach ( $all_features as $feature ) {
			$feature_id = $feature['id'] ?? '';
			if ( $feature_id === $current_feature ) {
				$mode = 'parent';
				$selected_parent = $feature;
				break;
			}
			if ( ! empty( $feature['sub_features'] ) && is_array( $feature['sub_features'] ) ) {
				foreach ( $feature['sub_features'] as $sub_key => $sub_feature ) {
					if ( $sub_key === $current_feature ) {
						$mode = 'child';
						$selected_child = array(
							'parent_id' => $feature_id,
							'key'       => $sub_key,
							'data'      => $sub_feature,
						);
						break 2;
					}
				}
			}
		}
	}

	// Resolve features to render based on mode
	$features = array();
	if ( 'all' === $mode ) {
		$features = $all_features;
	} elseif ( 'parent' === $mode && ! empty( $selected_parent ) ) {
		$features = array( $selected_parent );
	}
	?>
	<div class="wpshadow-widget-content">
		<?php if ( 'child' === $mode && ! empty( $selected_child ) ) : ?>
			<table class="wp-list-table widefat fixed striped">
				<tbody>
					<?php
						$parent_id   = $selected_child['parent_id'];
						$sub_key     = $selected_child['key'];
						$sub_feature  = is_array( $selected_child['data'] ) ? $selected_child['data'] : array();
						$sub_enabled  = get_option( "wpshadow_{$parent_id}_{$sub_key}", $sub_feature['default_enabled'] ?? true );
					?>
					<tr data-parent-feature="<?php echo esc_attr( $parent_id ); ?>" data-subfeature-key="<?php echo esc_attr( $sub_key ); ?>" data-default-enabled="<?php echo esc_attr( $sub_feature['default_enabled'] ?? true ? '1' : '0' ); ?>" id="wpshadow-scroll-target">
						<td style="padding: 16px;">
							<div style="margin-bottom: 4px;">
								<strong style="font-size: 14px;">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&wpshadow_tab=features&feature=' . urlencode( $sub_key ) ) ); ?>" style="color: #2271b1; text-decoration: none;">
										<?php echo esc_html( $sub_feature['name'] ?? $sub_key ); ?>
									</a>
								</strong>
							</div>
							<?php if ( ! empty( $sub_feature['description'] ) ) : ?>
								<div style="color: #646970; font-size: 13px;">
									<?php echo esc_html( $sub_feature['description'] ); ?>
								</div>
							<?php endif; ?>
						</td>
						<td style="width: 60px; text-align: center; vertical-align: top; padding: 16px;">
							<label class="wpshadow-feature-toggle">
								<input type="checkbox"
									   class="wpshadow-subfeature-toggle-input"
									   data-feature-id="<?php echo esc_attr( $parent_id ); ?>"
									   data-subfeature-key="<?php echo esc_attr( $sub_key ); ?>"
									   <?php checked( $sub_enabled ); ?>>
								<span class="wpshadow-feature-toggle-slider"></span>
							</label>
						</td>
					</tr>
				</tbody>
			</table>
		<?php elseif ( empty( $features ) ) : ?>
			<p style="padding: 12px;"><?php esc_html_e( 'No features available.', 'wpshadow' ); ?></p>
		<?php else : ?>
			<table class="wp-list-table widefat fixed striped">
				<tbody>
					<?php foreach ( $features as $feature ) : 
						$feature_id = $feature['id'] ?? '';
						$sub_features = $feature['sub_features'] ?? array();
					?>
						<tr data-parent-feature="<?php echo esc_attr( $feature_id ); ?>">
							<td style="padding: 16px;">
								<div style="margin-bottom: 4px;">
									<strong style="font-size: 14px;">
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&wpshadow_tab=features&feature=' . urlencode( $feature_id ) ) ); ?>" style="color: #2271b1; text-decoration: none;">
											<?php echo esc_html( $feature['name'] ?? $feature_id ); ?>
										</a>
									</strong>
								</div>
								<?php if ( ! empty( $feature['description'] ) ) : ?>
									<div style="color: #646970; font-size: 13px;">
										<?php echo esc_html( $feature['description'] ); ?>
									</div>
								<?php endif; ?>
							</td>
							<td style="width: 60px; text-align: center; vertical-align: top; padding: 16px;">
								<label class="wpshadow-feature-toggle">
									<input type="checkbox" 
										   class="wpshadow-feature-toggle-input" 
										   data-feature-id="<?php echo esc_attr( $feature_id ); ?>"
										   <?php checked( $feature['enabled'] ?? false ); ?>>
									<span class="wpshadow-feature-toggle-slider"></span>
								</label>
							</td>
						</tr>
						<?php if ( ! empty( $sub_features ) ) :
							// Filter out configurable settings from the list display using the declared has_settings flag.
							$configurable_keys        = array_keys( wpshadow_get_configurable_sub_features( $feature ) );
							$sub_features_to_display = array_filter( $sub_features, function( $key ) use ( $configurable_keys ) {
								return ! in_array( $key, $configurable_keys, true );
							}, ARRAY_FILTER_USE_KEY );
						?>
							<?php foreach ( $sub_features_to_display as $sub_key => $sub_feature ) : 
								$sub_enabled = get_option( "wpshadow_{$feature_id}_{$sub_key}", $sub_feature['default_enabled'] ?? true );
							?>
								<tr class="wpshadow-child-feature" data-parent-feature="<?php echo esc_attr( $feature_id ); ?>" data-subfeature-key="<?php echo esc_attr( $sub_key ); ?>" data-default-enabled="<?php echo esc_attr( $sub_feature['default_enabled'] ?? true ? '1' : '0' ); ?>" style="background: #f9f9f9;">
									<td style="padding: 12px 16px 12px 48px;">
										<div style="margin-bottom: 2px;">
											<span style="font-size: 13px; font-weight: 500;">
												<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&wpshadow_tab=features&feature=' . urlencode( $sub_key ) ) ); ?>" style="color: #2271b1; text-decoration: none;">
													<?php echo esc_html( $sub_feature['name'] ?? $sub_key ); ?>
												</a>
											</span>
										</div>
										<?php if ( ! empty( $sub_feature['description'] ) ) : ?>
											<div style="color: #646970; font-size: 12px;">
												<?php echo esc_html( $sub_feature['description'] ); ?>
											</div>
										<?php endif; ?>
									</td>
									<td style="width: 60px; text-align: center; vertical-align: top; padding: 12px;">
										<label class="wpshadow-feature-toggle">
											<input type="checkbox" 
												   class="wpshadow-subfeature-toggle-input" 
												   data-feature-id="<?php echo esc_attr( $feature_id ); ?>"
												   data-subfeature-key="<?php echo esc_attr( $sub_key ); ?>"
												   <?php checked( $sub_enabled ); ?>>
											<span class="wpshadow-feature-toggle-slider"></span>
										</label>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		
		<!-- JavaScript for feature toggles (always load regardless of view mode) -->
		<script>
		jQuery(document).ready(function($) {
			// Toast notification system
			function showToast(message, enabled = true, duration = 3000) {
				// Create toast container if it doesn't exist
				if ($('#wpshadow-toast-container').length === 0) {
					$('body').append('<div id="wpshadow-toast-container"></div>');
				}
				
				// Create toast element
				var toastId = 'toast-' + Date.now();
				var iconColor = enabled ? 'wpshadow-toast-icon-success' : 'wpshadow-toast-icon-disabled';
				var $toast = $('<div class="wpshadow-toast" id="' + toastId + '">' + 
					'<div class="wpshadow-toast-content">' + 
						'<span class="wpshadow-toast-icon ' + iconColor + '">✓</span>' + 
						'<span class="wpshadow-toast-message">' + message + '</span>' + 
					'</div>' + 
				'</div>');
				
				$('#wpshadow-toast-container').append($toast);
				
				// Trigger animation
				setTimeout(function() {
					$toast.addClass('wpshadow-toast-show');
				}, 10);
				
				// Remove after duration
				setTimeout(function() {
					$toast.removeClass('wpshadow-toast-show');
					setTimeout(function() {
						$toast.remove();
					}, 300);
				}, duration);
			}
			
			// Store child toggle states in memory
			var childStates = {};
			
			// Initialize: hide children if parent is off
			$('.wpshadow-feature-toggle-input').each(function() {
				var $toggle = $(this);
				var featureId = $toggle.data('feature-id');
				var enabled = $toggle.is(':checked');
				
				if (!enabled) {
					$('.wpshadow-child-feature[data-parent-feature="' + featureId + '"]').hide();
				}
			});
			
			// Main feature toggle
			$('.wpshadow-feature-toggle-input').on('change', function() {
				var $toggle = $(this);
				var featureId = $toggle.data('feature-id');
				var enabled = $toggle.is(':checked');
				var $childRows = $('.wpshadow-child-feature[data-parent-feature="' + featureId + '"]');
				
				if (enabled) {
					// Parent turned ON: show children and restore their states
					$childRows.show();
					$childRows.each(function() {
						var $row = $(this);
						var subKey = $row.data('subfeature-key');
						var stateKey = featureId + '_' + subKey;
						var $childToggle = $row.find('.wpshadow-subfeature-toggle-input');
						
						// Restore saved state or use default
						if (childStates.hasOwnProperty(stateKey)) {
							$childToggle.prop('checked', childStates[stateKey]);
						} else {
							var defaultEnabled = $row.data('default-enabled') == '1';
							$childToggle.prop('checked', defaultEnabled);
						}
					});
				} else {
					// Parent turned OFF: save child states, turn them off, and hide them
					$childRows.each(function() {
						var $row = $(this);
						var subKey = $row.data('subfeature-key');
						var stateKey = featureId + '_' + subKey;
						var $childToggle = $row.find('.wpshadow-subfeature-toggle-input');
						
						// Save current state before turning off
						childStates[stateKey] = $childToggle.is(':checked');
						
						// Turn off child toggle
						if ($childToggle.is(':checked')) {
							$childToggle.prop('checked', false).trigger('change');
						}
					});
					$childRows.hide();
				}
				
				$.post(ajaxurl, {
					action: 'wpshadow_toggle_feature',
					feature_id: featureId,
					enabled: enabled ? 1 : 0,
					nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_toggle_feature' ) ); ?>'
				}, function(response) {
					if (response.success) {
						// Show toast notification with the message and enabled state from response
						if (response.data && response.data.message) {
							showToast(response.data.message, response.data.enabled);
						}
						
						// Trigger custom event for history refresh
						if (response.data && response.data.feature_id) {
							$(document).trigger('wpshadow:feature_toggled', [response.data.feature_id]);
						}
					} else {
						// Revert toggle on error
						$toggle.prop('checked', !enabled);
						
						// Show error toast
						var errorMsg = (response.data && typeof response.data === 'string') ? response.data : '<?php echo esc_js( __( 'Failed to update feature', 'wpshadow' ) ); ?>';
						showToast(errorMsg, false, 5000);
					}
				});
			});
			
			// Sub-feature toggle
			$('.wpshadow-subfeature-toggle-input').on('change', function() {
				var $toggle = $(this);
				var featureId = $toggle.data('feature-id');
				var subfeatureKey = $toggle.data('subfeature-key');
				var enabled = $toggle.is(':checked');
				
				$.post(ajaxurl, {
					action: 'wpshadow_toggle_subfeature',
					feature_id: featureId,
					subfeature_key: subfeatureKey,
					enabled: enabled ? 1 : 0,
					nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_toggle_subfeature' ) ); ?>'
				}, function(response) {
					if (response.success) {
						// Show toast notification with the message and enabled state from response
						if (response.data && response.data.message) {
							showToast(response.data.message, response.data.enabled);
						}
						
						// Trigger custom event for history refresh
						if (response.data && response.data.feature_id) {
							$(document).trigger('wpshadow:feature_toggled', [response.data.feature_id]);
						}
					} else {
						// Revert toggle on error
						$toggle.prop('checked', !enabled);
						
						// Show error toast
						var errorMsg = (response.data && typeof response.data === 'string') ? response.data : '<?php echo esc_js( __( 'Failed to update sub-feature', 'wpshadow' ) ); ?>';
						showToast(errorMsg, false, 5000);
					}
				});
			});
		});
		</script>
	</div>
	<?php
}

/**
 * Render features info widget.
 *
 * @return void
 */
function wpshadow_render_features_info_widget(): void {
	$network_scope   = is_multisite() && is_network_admin();
	$current_feature = isset( $_GET['feature'] ) ? sanitize_text_field( wp_unslash( $_GET['feature'] ) ) : '';

	// If viewing a specific feature detail page, show that feature's summary.
	if ( ! empty( $current_feature ) ) {
		$detail        = null;
		$all_features  = WPSHADOW_Feature_Registry::get_features_by_scope( 'core', '', '', $network_scope );
		$parent_detail = null;

		foreach ( $all_features as $candidate ) {
			$candidate_id = $candidate['id'] ?? '';
			if ( $candidate_id === $current_feature ) {
				$detail = array(
					'id'          => $candidate_id,
					'name'        => $candidate['name'] ?? $candidate_id,
					'description' => $candidate['description_long'] ?? ( $candidate['description'] ?? '' ),
					'version'     => $candidate['version'] ?? '',
				);
				break;
			}

			if ( ! empty( $candidate['sub_features'] ) && isset( $candidate['sub_features'][ $current_feature ] ) ) {
				$sub             = $candidate['sub_features'][ $current_feature ];
				$parent_detail   = $candidate;
				$detail          = array(
					'id'          => $current_feature,
					'name'        => $sub['name'] ?? $current_feature,
					'description' => $sub['description_long'] ?? ( $sub['description'] ?? '' ),
					'version'     => $sub['version'] ?? ( $candidate['version'] ?? '' ),
				);
				break;
			}
		}

		if ( $detail ) {
			$title       = $detail['name'] ?? $detail['id'];
			$description = $detail['description'] ?? '';
			$version     = $detail['version'] ?? '';

			?>
			<div class="wpshadow-widget-content" style="margin: 15px; padding: 12px;">
				<h2 style="margin: 0 0 6px 0; font-size: 18px;">
					<?php echo esc_html( $title ); ?>
				</h2>
				<?php if ( ! empty( $description ) ) : ?>
					<p style="margin: 0 0 10px 0; color: #444; line-height: 1.6;">
						<?php echo esc_html( $description ); ?>
					</p>
				<?php endif; ?>
				<?php if ( ! empty( $version ) ) : ?>
					<p style="margin: 0; color: #646970;">
						<strong><?php esc_html_e( 'Version:', 'wpshadow' ); ?></strong> <?php echo esc_html( $version ); ?>
					</p>
				<?php endif; ?>
			</div>
			<?php
			return;
		}
	}

	$all_features   = WPSHADOW_Feature_Registry::get_features( $network_scope );
	$enabled_count  = 0;
	$total_count    = count( $all_features );

	foreach ( $all_features as $feature ) {
		if ( ! empty( $feature['enabled'] ) ) {
			$enabled_count++;
		}
	}

	?>
	<div class="wpshadow-widget-content" style="margin: 15px; padding: 12px;">
		<h4 style="margin-top: 0;"><?php esc_html_e( 'About Features', 'wpshadow' ); ?></h4>
		<p><?php esc_html_e( 'Features extend WPShadow functionality. Enable or disable features based on your needs.', 'wpshadow' ); ?></p>
		
		<div style="background: #f6f7f7; padding: 12px; border-radius: 4px; margin: 15px 0;">
			<p style="margin: 0 0 8px 0;"><strong><?php esc_html_e( 'System Statistics:', 'wpshadow' ); ?></strong></p>
			<p style="margin: 0 0 5px 0;"><?php echo esc_html( sprintf( __( 'Total Features: %d', 'wpshadow' ), $total_count ) ); ?></p>
			<p style="margin: 0 0 5px 0; color: #2271b1;"><?php echo esc_html( sprintf( __( 'Enabled: %d', 'wpshadow' ), $enabled_count ) ); ?></p>
			<p style="margin: 0; color: #646970;"><?php echo esc_html( sprintf( __( 'Disabled: %d', 'wpshadow' ), $total_count - $enabled_count ) ); ?></p>
		</div>
		
		<h4><?php esc_html_e( 'How to Activate Features', 'wpshadow' ); ?></h4>
		<ol style="padding-left: 20px;">
			<li><?php esc_html_e( 'Browse the features list in the main panel', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Click the toggle switch to enable or disable a feature', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Click on a feature name to view detailed settings', 'wpshadow' ); ?></li>
			<li><?php esc_html_e( 'Configure sub-features for advanced customization', 'wpshadow' ); ?></li>
		</ol>
		
		<p style="background: #fff3cd; border-left: 3px solid #ffc107; padding: 10px; margin: 15px 0 0 0;">
			<strong><?php esc_html_e( 'Tip:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'Some features have sub-features that can be toggled independently for fine-grained control.', 'wpshadow' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Render feature log widget with VS Code SCM-style timeline.
 *
 * @param string $feature_id The feature ID to show logs for.
 * @return void
 */
function wpshadow_render_feature_log_widget( string $feature_id ): void {
	$logs = wpshadow_get_feature_logs( $feature_id, 10 );
	
	if ( empty( $logs ) ) {
		?>
		<div class="wpshadow-widget-content" style="margin: 15px; padding: 12px;">
			<p style="color: #646970; font-style: italic; margin: 0;">
				<?php esc_html_e( 'No activity logged yet for this feature.', 'wpshadow' ); ?>
			</p>
		</div>
		<?php
		return;
	}
	
	?>
	<div class="wpshadow-widget-content" style="margin: 15px;">
		<div class="wpshadow-feature-log-timeline">
			<?php foreach ( $logs as $log ) : ?>
				<div class="wpshadow-log-entry" data-action="<?php echo esc_attr( $log['action'] ); ?>">
					<div class="wpshadow-log-dot"></div>
					<div class="wpshadow-log-line"></div>
					<div class="wpshadow-log-content">
						<div class="wpshadow-log-header">
							<span class="wpshadow-log-action"><?php echo esc_html( $log['action_label'] ); ?></span>
							<span class="wpshadow-log-time" title="<?php echo esc_attr( $log['timestamp_full'] ); ?>">
								<?php echo esc_html( $log['timestamp_human'] ); ?>
							</span>
						</div>
						<?php if ( ! empty( $log['message'] ) ) : ?>
							<div class="wpshadow-log-message"><?php echo esc_html( $log['message'] ); ?></div>
						<?php endif; ?>
						<?php if ( ! empty( $log['user'] ) ) : ?>
							<div class="wpshadow-log-user">by <?php echo esc_html( $log['user'] ); ?></div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		
		<?php if ( count( $logs ) >= 10 ) : ?>
			<div class="wpshadow-log-load-more-container" style="text-align: center; padding-top: 10px; border-top: 1px solid #dcdcde;">
				<button type="button" class="button button-small wpshadow-load-more-logs" data-feature-id="<?php echo esc_attr( $feature_id ); ?>" data-offset="10">
					<?php esc_html_e( 'Load More', 'wpshadow' ); ?>
				</button>
			</div>
		<?php endif; ?>
	</div>
	
	<script>
	jQuery(document).ready(function($) {
		var currentOffset = 10;
		
		$(document).on('click', '.wpshadow-load-more-logs', function() {
			var $btn = $(this);
			var featureId = $btn.data('feature-id');
			var offset = $btn.data('offset');
			
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_load_more_logs',
					feature_id: featureId,
					offset: offset,
					nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_load_more_logs' ) ); ?>'
				},
				beforeSend: function() {
					$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Loading...', 'wpshadow' ) ); ?>');
				},
				success: function(response) {
					if (response.success && response.data.html) {
						$('.wpshadow-feature-log-timeline').append(response.data.html);
						
						if (response.data.has_more) {
							currentOffset += 10;
							$btn.data('offset', currentOffset).prop('disabled', false).text('<?php echo esc_js( __( 'Load More', 'wpshadow' ) ); ?>');
						} else {
							$btn.parent().remove();
						}
					} else {
						$btn.parent().remove();
					}
				},
				error: function() {
					$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Load More', 'wpshadow' ) ); ?>');
					alert('<?php echo esc_js( __( 'Failed to load logs. Please try again.', 'wpshadow' ) ); ?>');
				}
			});
		});
	});
	</script>
	<?php
}

/**
 * Get feature logs for a specific feature.
 *
 * @param string $feature_id The feature ID.
 * @param int    $limit      Maximum number of logs to retrieve.
 * @param int    $offset     Offset for pagination.
 * @return array Array of log entries.
 */
function wpshadow_get_feature_logs( string $feature_id, int $limit = 10, int $offset = 0 ): array {
	$all_logs = get_option( 'wpshadow_feature_logs', array() );
	
	if ( empty( $all_logs[ $feature_id ] ) ) {
		return array();
	}
	
	$feature_logs = $all_logs[ $feature_id ];
	
	// Sort by timestamp descending (newest first)
	usort( $feature_logs, function( $a, $b ) {
		return $b['timestamp'] - $a['timestamp'];
	});
	
	// Apply pagination
	$feature_logs = array_slice( $feature_logs, $offset, $limit );
	
	// Format logs for display
	foreach ( $feature_logs as &$log ) {
		$log['timestamp_full'] = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $log['timestamp'] );
		$log['timestamp_human'] = human_time_diff( $log['timestamp'], current_time( 'timestamp' ) ) . ' ago';
		$log['action_label'] = wpshadow_get_log_action_label( $log['action'] );
	}
	
	return $feature_logs;
}

/**
 * Get human-readable label for log action.
 *
 * @param string $action The action type.
 * @return string The action label.
 */
function wpshadow_get_log_action_label( string $action ): string {
	$labels = array(
		'enabled'           => __( 'Enabled', 'wpshadow' ),
		'disabled'          => __( 'Disabled', 'wpshadow' ),
		'settings_updated'  => __( 'Settings Updated', 'wpshadow' ),
		'sub_feature_enabled'  => __( 'Sub-feature Enabled', 'wpshadow' ),
		'sub_feature_disabled' => __( 'Sub-feature Disabled', 'wpshadow' ),
		'error'             => __( 'Error', 'wpshadow' ),
		'action_performed'  => __( 'Action Performed', 'wpshadow' ),
	);
	
	return $labels[ $action ] ?? ucfirst( str_replace( '_', ' ', $action ) );
}

/**
 * Get all feature logs from all features combined.
 *
 * @param int $limit  Maximum number of logs to return.
 * @param int $offset Offset for pagination.
 * @return array Array of log entries with feature information.
 */
function wpshadow_get_all_feature_logs( int $limit = 15, int $offset = 0 ): array {
	$all_logs = get_option( 'wpshadow_feature_logs', array() );
	
	if ( empty( $all_logs ) ) {
		return array();
	}
	
	$combined_logs = array();
	
	// Combine logs from all features
	foreach ( $all_logs as $feature_id => $feature_logs ) {
		foreach ( $feature_logs as $log ) {
			$log['feature_id'] = $feature_id;
			
			// Get feature name from registry
			$feature = WPSHADOW_Feature_Registry::get_feature_object( $feature_id );
			$log['feature_name'] = $feature ? $feature->get_name() : ucwords( str_replace( array( '-', '_' ), ' ', $feature_id ) );
			
			$combined_logs[] = $log;
		}
	}
	
	// Sort by timestamp descending (newest first)
	usort( $combined_logs, function( $a, $b ) {
		return $b['timestamp'] - $a['timestamp'];
	});
	
	// Apply pagination
	$combined_logs = array_slice( $combined_logs, $offset, $limit );
	
	// Format logs for display
	foreach ( $combined_logs as &$log ) {
		$log['timestamp_full'] = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $log['timestamp'] );
		$log['timestamp_human'] = human_time_diff( $log['timestamp'], current_time( 'timestamp' ) ) . ' ago';
		$log['action_label'] = wpshadow_get_log_action_label( $log['action'] );
	}
	
	return $combined_logs;
}

/**
 * Render help content widget.
 *
 * @return void
 */
function wpshadow_render_help_content_widget(): void {
	$help_content = WPSHADOW_Help_Content_API::get_content();
	?>
	<div class="wpshadow-widget-content">
		<?php if ( ! empty( $help_content['overview'] ) && is_array( $help_content['overview'] ) ) : ?>
			<h3><?php echo esc_html( $help_content['overview']['title'] ?? __( 'Overview', 'wpshadow' ) ); ?></h3>
			<?php
			if ( ! empty( $help_content['overview']['content'] ) && is_array( $help_content['overview']['content'] ) ) {
				foreach ( $help_content['overview']['content'] as $item ) {
					if ( ! empty( $item['heading'] ) ) {
						echo '<h4>' . esc_html( $item['heading'] ) . '</h4>';
					}
					if ( ! empty( $item['text'] ) ) {
						echo '<p>' . wp_kses_post( $item['text'] ) . '</p>';
					}
				}
			}
			?>
		<?php endif; ?>
		
		<?php if ( ! empty( $help_content['getting-started'] ) && is_array( $help_content['getting-started'] ) ) : ?>
			<h3><?php echo esc_html( $help_content['getting-started']['title'] ?? __( 'Getting Started', 'wpshadow' ) ); ?></h3>
			<?php
			if ( ! empty( $help_content['getting-started']['content'] ) && is_array( $help_content['getting-started']['content'] ) ) {
				foreach ( $help_content['getting-started']['content'] as $item ) {
					if ( ! empty( $item['heading'] ) ) {
						echo '<h4>' . esc_html( $item['heading'] ) . '</h4>';
					}
					if ( ! empty( $item['text'] ) ) {
						echo '<p>' . wp_kses_post( $item['text'] ) . '</p>';
					}
				}
			}
			?>
		<?php endif; ?>
		
		<?php if ( ! empty( $help_content['faq'] ) && is_array( $help_content['faq'] ) ) : ?>
			<h3><?php echo esc_html( $help_content['faq']['title'] ?? __( 'FAQ', 'wpshadow' ) ); ?></h3>
			<?php
			if ( ! empty( $help_content['faq']['content'] ) && is_array( $help_content['faq']['content'] ) ) {
				foreach ( $help_content['faq']['content'] as $item ) {
					if ( ! empty( $item['heading'] ) ) {
						echo '<h4>' . esc_html( $item['heading'] ) . '</h4>';
					}
					if ( ! empty( $item['text'] ) ) {
						echo '<p>' . wp_kses_post( $item['text'] ) . '</p>';
					}
				}
			}
			?>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render help resources widget.
 *
 * @return void
 */
function wpshadow_render_help_resources_widget(): void {
	?>
	<div class="wpshadow-widget-content">
		<h4><?php esc_html_e( 'Support Links', 'wpshadow' ); ?></h4>
		<ul style="list-style: none; padding: 0;">
			<li style="margin-bottom: 10px;"><a href="https://wpshadow.com/docs/" target="_blank" rel="noopener"><span class="dashicons dashicons-book"></span> <?php esc_html_e( 'Documentation', 'wpshadow' ); ?></a></li>
			<li style="margin-bottom: 10px;"><a href="https://wpshadow.com/support/" target="_blank" rel="noopener"><span class="dashicons dashicons-sos"></span> <?php esc_html_e( 'Support Forum', 'wpshadow' ); ?></a></li>
			<li style="margin-bottom: 10px;"><a href="https://wpshadow.com/contact/" target="_blank" rel="noopener"><span class="dashicons dashicons-email"></span> <?php esc_html_e( 'Contact Us', 'wpshadow' ); ?></a></li>
		</ul>
	</div>
	<?php
}

/**
 * Legacy function for backward compatibility.
 *
 * @param string $hub_id Optional hub identifier for hub-level dashboards.
 * @param string $spoke_id Optional spoke identifier for spoke-level dashboards.
 * @return void
 */
function wpshadow_render_dashboard( string $hub_id = '', string $spoke_id = '' ): void {
	wpshadow_render_unified_layout( 'dashboard', $hub_id, $spoke_id );
}

/**
 * Render breadcrumb navigation.
 *
 * @param string $tab Current tab (dashboard, features, help).
 * @return void
 */
function wpshadow_render_breadcrumbs( string $tab, string $current_feature = '' ): void {
	$base_url = admin_url( 'admin.php?page=wpshadow' );
	
	// Start with Dashboard
	$breadcrumbs = array(
		array(
			'label' => __( 'Dashboard', 'wpshadow' ),
			'url'   => $base_url . '&wpshadow_tab=dashboard',
			'active' => false,
		),
	);
	
	// Add the tab level (Features or Help)
	if ( 'features' === $tab ) {
		$breadcrumbs[] = array(
			'label' => __( 'Features', 'wpshadow' ),
			'url'   => $base_url . '&wpshadow_tab=features',
			'active' => empty( $current_feature ),
		);
	} elseif ( 'help' === $tab ) {
		$breadcrumbs[] = array(
			'label' => __( 'Help', 'wpshadow' ),
			'url'   => $base_url . '&wpshadow_tab=help',
			'active' => true,
		);
	}
	
	// Add feature details if viewing a specific feature
	if ( ! empty( $current_feature ) && 'features' === $tab ) {
		// Get feature information from the registry
		if ( class_exists( '\WPShadow\CoreSupport\WPSHADOW_Feature_Registry' ) ) {
			// Get all features from all scopes (core, site, network, hub, spoke)
			$all_features = \WPShadow\CoreSupport\WPSHADOW_Feature_Registry::get_features();
			
			// Check if current feature is a parent or child
			$feature_data = null;
			$parent_feature_data = null;
			
			foreach ( $all_features as $feature ) {
				$feature_id = $feature['id'] ?? '';
				
				// Check if it's a parent feature
				if ( $feature_id === $current_feature ) {
					$feature_data = $feature;
					break;
				}
				
				// Check if it's a child feature
				if ( ! empty( $feature['sub_features'] ) ) {
					foreach ( $feature['sub_features'] as $sub_key => $sub_feature ) {
						if ( $sub_key === $current_feature ) {
							$parent_feature_data = $feature;
							$feature_data = $sub_feature;
							$feature_data['id'] = $sub_key;
							break 2;
						}
					}
				}
			}
			
			// Add parent feature breadcrumb if this is a child feature
			if ( $parent_feature_data ) {
				$breadcrumbs[] = array(
					'label' => $parent_feature_data['name'] ?? ucwords( str_replace( array( '_', '-' ), ' ', $parent_feature_data['id'] ) ),
					'url'   => $base_url . '&wpshadow_tab=features&feature=' . urlencode( $parent_feature_data['id'] ),
					'active' => false,
				);
			}
			
			// Add current feature breadcrumb
			if ( $feature_data ) {
				$breadcrumbs[] = array(
					'label' => $feature_data['name'] ?? ucwords( str_replace( array( '_', '-' ), ' ', $current_feature ) ),
					'url'   => $base_url . '&wpshadow_tab=features&feature=' . urlencode( $current_feature ),
					'active' => true,
				);
			}
		}
	}
	
	?>
	<div class="wpshadow-breadcrumbs" style="margin: 10px 0 20px 0; padding: 8px 0; border-bottom: 1px solid #dcdcde;">
		<nav aria-label="<?php esc_attr_e( 'Breadcrumb navigation', 'wpshadow' ); ?>">
			<?php
			foreach ( $breadcrumbs as $index => $crumb ) {
				if ( $crumb['active'] ) {
					echo '<span class="wpshadow-breadcrumb-current" style="color: #2271b1; font-weight: 600;">';
					echo esc_html( $crumb['label'] );
					echo '</span>';
				} else {
					echo '<a href="' . esc_url( $crumb['url'] ) . '" class="wpshadow-breadcrumb-link" style="color: #2271b1; text-decoration: none;">';
					echo esc_html( $crumb['label'] );
					echo '</a>';
				}
				
				// Add separator except for last item
				if ( $index < count( $breadcrumbs ) - 1 ) {
					echo ' <span class="wpshadow-breadcrumb-separator" style="color: #787c82; margin: 0 8px;">›</span> ';
				}
			}
			?>
		</nav>
	</div>
	<?php
}

/**
 * Get health indicators for each metric with tooltips.
 *
 * @param array $metrics System metrics array.
 * @return array Array of indicators with icons and tooltips.
 */
function wpshadow_get_health_indicators( array $metrics ): array {
	$indicators = array();
	
	// PHP Memory indicator
	if ( $metrics['memory_percent'] < 50 ) {
		$indicators['memory'] = array(
			'icon'    => '<span style="color: #00a32a;">✓</span>',
			'tooltip' => __( 'PHP memory usage is healthy. You have plenty of memory available.', 'wpshadow' ),
		);
	} elseif ( $metrics['memory_percent'] < 75 ) {
		$indicators['memory'] = array(
			'icon'    => '<span style="color: #f0b849;">⚠</span>',
			'tooltip' => __( 'PHP memory usage is moderate. Consider increasing memory_limit if you experience issues.', 'wpshadow' ),
		);
	} else {
		$indicators['memory'] = array(
			'icon'    => '<span style="color: #d63638;">✗</span>',
			'tooltip' => __( 'PHP memory usage is high. Increase memory_limit in php.ini or wp-config.php to avoid errors.', 'wpshadow' ),
		);
	}
	
	// Disk Space indicator
	if ( $metrics['disk_percent'] === 0 || $metrics['disk_percent'] < 70 ) {
		$indicators['disk'] = array(
			'icon'    => '<span style="color: #00a32a;">✓</span>',
			'tooltip' => __( 'Disk space is healthy. You have sufficient storage available.', 'wpshadow' ),
		);
	} elseif ( $metrics['disk_percent'] < 85 ) {
		$indicators['disk'] = array(
			'icon'    => '<span style="color: #f0b849;">⚠</span>',
			'tooltip' => __( 'Disk space is getting low. Consider cleaning up old files or increasing storage.', 'wpshadow' ),
		);
	} else {
		$indicators['disk'] = array(
			'icon'    => '<span style="color: #d63638;">✗</span>',
			'tooltip' => __( 'Disk space is critically low. Free up space immediately to prevent site issues.', 'wpshadow' ),
		);
	}
	
	// PHP Version indicator
	$php_version = $metrics['php_version'];
	$min_recommended = '8.1';
	if ( version_compare( $php_version, $min_recommended, '>=' ) ) {
		$indicators['php_version'] = array(
			'icon'    => '<span style="color: #00a32a;">✓</span>',
			'tooltip' => sprintf( __( 'PHP %s is up to date and secure. No action needed.', 'wpshadow' ), $php_version ),
		);
	} elseif ( version_compare( $php_version, '7.4', '>=' ) ) {
		$indicators['php_version'] = array(
			'icon'    => '<span style="color: #f0b849;">⚠</span>',
			'tooltip' => sprintf( __( 'PHP %s is outdated. Upgrade to PHP 8.1+ for better performance and security.', 'wpshadow' ), $php_version ),
		);
	} else {
		$indicators['php_version'] = array(
			'icon'    => '<span style="color: #d63638;">✗</span>',
			'tooltip' => sprintf( __( 'PHP %s is severely outdated and unsupported. Upgrade immediately to prevent security risks.', 'wpshadow' ), $php_version ),
		);
	}
	
	// WordPress Version indicator
	$wp_version = $metrics['wp_version'];
	$latest_wp_version = get_site_transient( 'update_core' );
	if ( $latest_wp_version && isset( $latest_wp_version->updates[0]->version ) ) {
		$latest = $latest_wp_version->updates[0]->version;
		if ( version_compare( $wp_version, $latest, '>=' ) ) {
			$indicators['wp_version'] = array(
				'icon'    => '<span style="color: #00a32a;">✓</span>',
				'tooltip' => sprintf( __( 'WordPress %s is up to date. No updates available.', 'wpshadow' ), $wp_version ),
			);
		} else {
			$indicators['wp_version'] = array(
				'icon'    => '<span style="color: #d63638;">✗</span>',
				'tooltip' => sprintf( __( 'WordPress %s is outdated. Update to version %s for security and features.', 'wpshadow' ), $wp_version, $latest ),
			);
		}
	} else {
		$indicators['wp_version'] = array(
			'icon'    => '<span style="color: #00a32a;">✓</span>',
			'tooltip' => sprintf( __( 'WordPress %s is installed.', 'wpshadow' ), $wp_version ),
		);
	}
	
	// Max Upload Size indicator
	$max_upload_bytes = wp_max_upload_size();
	$min_recommended_upload = 32 * MB_IN_BYTES; // 32MB
	if ( $max_upload_bytes >= $min_recommended_upload ) {
		$indicators['max_upload'] = array(
			'icon'    => '<span style="color: #00a32a;">✓</span>',
			'tooltip' => sprintf( __( 'Max upload size (%s) is sufficient for most media files.', 'wpshadow' ), $metrics['max_upload'] ),
		);
	} else {
		$indicators['max_upload'] = array(
			'icon'    => '<span style="color: #f0b849;">⚠</span>',
			'tooltip' => sprintf( __( 'Max upload size (%s) is low. Increase upload_max_filesize in php.ini if needed.', 'wpshadow' ), $metrics['max_upload'] ),
		);
	}
	
	// Max Execution Time indicator
	$max_exec = (int) $metrics['max_execution_time'];
	if ( $max_exec >= 60 ) {
		$indicators['max_execution'] = array(
			'icon'    => '<span style="color: #00a32a;">✓</span>',
			'tooltip' => sprintf( __( 'Max execution time (%ds) is adequate for most operations.', 'wpshadow' ), $max_exec ),
		);
	} elseif ( $max_exec >= 30 ) {
		$indicators['max_execution'] = array(
			'icon'    => '<span style="color: #f0b849;">⚠</span>',
			'tooltip' => sprintf( __( 'Max execution time (%ds) may be low for large operations. Consider increasing to 60s+.', 'wpshadow' ), $max_exec ),
		);
	} else {
		$indicators['max_execution'] = array(
			'icon'    => '<span style="color: #d63638;">✗</span>',
			'tooltip' => sprintf( __( 'Max execution time (%ds) is too low. Increase max_execution_time in php.ini.', 'wpshadow' ), $max_exec ),
		);
	}
	
	// Database Size indicator (always green, informational)
	$indicators['db_size'] = array(
		'icon'    => '<span style="color: #00a32a;">✓</span>',
		'tooltip' => sprintf( __( 'Database size is %s. Regular optimization recommended for large databases.', 'wpshadow' ), $metrics['db_size'] ),
	);
	
	// Active Plugins indicator
	$plugin_count = (int) $metrics['active_plugins'];
	if ( $plugin_count <= 20 ) {
		$indicators['plugins'] = array(
			'icon'    => '<span style="color: #00a32a;">✓</span>',
			'tooltip' => sprintf( __( '%d active plugins. This is a healthy amount for most sites.', 'wpshadow' ), $plugin_count ),
		);
	} elseif ( $plugin_count <= 40 ) {
		$indicators['plugins'] = array(
			'icon'    => '<span style="color: #f0b849;">⚠</span>',
			'tooltip' => sprintf( __( '%d active plugins. Consider reviewing and removing unused plugins for better performance.', 'wpshadow' ), $plugin_count ),
		);
	} else {
		$indicators['plugins'] = array(
			'icon'    => '<span style="color: #d63638;">✗</span>',
			'tooltip' => sprintf( __( '%d active plugins is excessive. Too many plugins can slow your site. Audit and remove unnecessary ones.', 'wpshadow' ), $plugin_count ),
		);
	}
	
	// Active Theme indicator (always green, informational)
	$indicators['theme'] = array(
		'icon'    => '<span style="color: #00a32a;">✓</span>',
		'tooltip' => sprintf( __( 'Active theme: %s', 'wpshadow' ), $metrics['active_theme'] ),
	);
	
	return $indicators;
}

/**
 * Get system metrics for health widget.
 *
 * @return array System metrics.
 */
function wpshadow_get_system_metrics(): array {
	global $wpdb;
	
	// Memory
	$memory_limit = ini_get( 'memory_limit' );
	$memory_usage = memory_get_usage( true );
	$memory_limit_bytes = wp_convert_hr_to_bytes( $memory_limit );
	$memory_percent = ( $memory_limit_bytes > 0 ) ? round( ( $memory_usage / $memory_limit_bytes ) * 100 ) : 0;
	
	// Disk space (WordPress root directory) - with fallback
	$disk_free = @disk_free_space( ABSPATH );
	$disk_total = @disk_total_space( ABSPATH );
	
	// If disk functions fail, try alternative methods
	if ( false === $disk_free || false === $disk_total ) {
		// Try exec df command as fallback
		$df_output = @shell_exec( 'df -k ' . escapeshellarg( ABSPATH ) . ' | tail -1' );
		if ( $df_output ) {
			$df_parts = preg_split( '/\s+/', trim( $df_output ) );
			if ( isset( $df_parts[1], $df_parts[2] ) ) {
				$disk_total = (int) $df_parts[1] * 1024; // Convert KB to bytes
				$disk_used = (int) $df_parts[2] * 1024;
				$disk_free = $disk_total - $disk_used;
			}
		}
	}
	
	// Calculate disk usage
	if ( false !== $disk_free && false !== $disk_total && $disk_total > 0 ) {
		$disk_used = $disk_total - $disk_free;
		$disk_percent = round( ( $disk_used / $disk_total ) * 100 );
		$disk_used_formatted = size_format( $disk_used );
		$disk_total_formatted = size_format( $disk_total );
		$disk_free_formatted = size_format( $disk_free );
	} else {
		// Fallback values if disk space cannot be determined
		$disk_used = 0;
		$disk_percent = 0;
		$disk_used_formatted = __( 'N/A', 'wpshadow' );
		$disk_total_formatted = __( 'N/A', 'wpshadow' );
		$disk_free_formatted = __( 'N/A', 'wpshadow' );
	}
	
	// Database size
	$db_size = 0;
	$tables = $wpdb->get_results( "SHOW TABLE STATUS", ARRAY_A );
	if ( is_array( $tables ) ) {
		foreach ( $tables as $table ) {
			$db_size += isset( $table['Data_length'] ) ? (int) $table['Data_length'] : 0;
			$db_size += isset( $table['Index_length'] ) ? (int) $table['Index_length'] : 0;
		}
	}
	
	// Active plugins
	$active_plugins = count( get_option( 'active_plugins', array() ) );
	if ( is_multisite() ) {
		$network_plugins = get_site_option( 'active_sitewide_plugins', array() );
		$active_plugins += count( $network_plugins );
	}
	
	// Active theme
	$theme = wp_get_theme();
	$active_theme = $theme->get( 'Name' );
	
	return array(
		'memory_usage'        => size_format( $memory_usage ),
		'memory_limit'        => $memory_limit,
		'memory_percent'      => $memory_percent,
		'disk_used'           => $disk_used_formatted,
		'disk_total'          => $disk_total_formatted,
		'disk_free'           => $disk_free_formatted,
		'disk_percent'        => $disk_percent,
		'php_version'         => PHP_VERSION,
		'wp_version'          => get_bloginfo( 'version' ),
		'max_upload'          => size_format( wp_max_upload_size() ),
		'max_execution_time'  => ini_get( 'max_execution_time' ),
		'db_size'             => size_format( $db_size ),
		'active_plugins'      => $active_plugins,
		'active_theme'        => $active_theme,
	);
}

/**
 * Calculate overall health score based on metrics.
 *
 * @param array $metrics System metrics.
 * @return int Health score (0-100).
 */
function wpshadow_calculate_health_score( array $metrics ): int {
	$score = 100;
	
	// Deduct points for high memory usage
	if ( $metrics['memory_percent'] >= 75 ) {
		$score -= 15; // Red zone
	} elseif ( $metrics['memory_percent'] >= 50 ) {
		$score -= 8; // Yellow zone (warning)
	}
	
	// Deduct points for high disk usage
	if ( $metrics['disk_percent'] >= 85 ) {
		$score -= 15; // Red zone
	} elseif ( $metrics['disk_percent'] >= 70 ) {
		$score -= 8; // Yellow zone (warning)
	}
	
	// Deduct points for old PHP version
	$php_version = $metrics['php_version'];
	if ( version_compare( $php_version, '7.4', '<' ) ) {
		$score -= 20; // Severely outdated
	} elseif ( version_compare( $php_version, '8.1', '<' ) ) {
		$score -= 10; // Outdated (warning)
	}
	
	// Check WordPress version
	$wp_version = $metrics['wp_version'];
	$latest_wp_version = get_site_transient( 'update_core' );
	if ( $latest_wp_version && isset( $latest_wp_version->updates[0]->version ) ) {
		$latest = $latest_wp_version->updates[0]->version;
		if ( version_compare( $wp_version, $latest, '<' ) ) {
			$score -= 10; // WordPress outdated
		}
	}
	
	// Deduct points for low max execution time
	$max_execution = (int) $metrics['max_execution_time'];
	if ( $max_execution > 0 && $max_execution < 30 ) {
		$score -= 12; // Red zone
	} elseif ( $max_execution > 0 && $max_execution < 60 ) {
		$score -= 7; // Yellow zone (warning)
	}
	
	// Deduct points for low max upload size
	$max_upload_bytes = wp_max_upload_size();
	$min_recommended_upload = 32 * MB_IN_BYTES;
	if ( $max_upload_bytes < $min_recommended_upload ) {
		$score -= 5; // Low upload size (warning)
	}
	
	// Deduct points for too many active plugins
	if ( $metrics['active_plugins'] > 40 ) {
		$score -= 12; // Excessive plugins
	} elseif ( $metrics['active_plugins'] > 20 ) {
		$score -= 6; // Many plugins (warning)
	}
	
	return max( 0, min( 100, $score ) );
}

/**
 * Get health status label and color based on score.
 *
 * @param int $score Health score.
 * @return array Status with label and color.
 */
function wpshadow_get_health_status( int $score ): array {
	if ( $score >= 90 ) {
		return array(
			'label' => __( 'Excellent Health', 'wpshadow' ),
			'color' => '#00a32a',
		);
	} elseif ( $score >= 70 ) {
		return array(
			'label' => __( 'Good Health', 'wpshadow' ),
			'color' => '#2271b1',
		);
	} elseif ( $score >= 50 ) {
		return array(
			'label' => __( 'Fair Health', 'wpshadow' ),
			'color' => '#f0b849',
		);
	} else {
		return array(
			'label' => __( 'Needs Attention', 'wpshadow' ),
			'color' => '#d63638',
		);
	}
}

/**
 * Get color for metric percentage bar.
 *
 * @param int $percent Percentage value.
 * @return string Hex color code.
 */
/**
 * Get color for metric based on percentage.
 *
 * @param int|float $percent Percentage value.
 * @return string Hex color code.
 */
function wpshadow_get_metric_color( $percent ): string {
	$percent = (float) $percent;
	
	if ( $percent >= 90 ) {
		return '#d63638'; // Red
	} elseif ( $percent >= 75 ) {
		return '#f0b849'; // Yellow
	} elseif ( $percent >= 50 ) {
		return '#2271b1'; // Blue
	} else {
		return '#00a32a'; // Green
	}
}
