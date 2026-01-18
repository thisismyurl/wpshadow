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

	add_meta_box(
		'wpshadow_dashboard_activity',
		__( 'Recent Activity', 'wpshadow' ),
		__NAMESPACE__ . '\\wpshadow_render_dashboard_activity_widget',
		$screen_id,
		'normal',
		'default'
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
			'high'
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
	?>
	<div class="wpshadow-widget-content">
		<div style="text-align: center; padding: 20px;">
			<div style="width: 80px; height: 80px; border-radius: 50%; background: #00a32a; color: white; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: bold; margin: 0 auto 10px;">95</div>
			<p style="margin: 0; color: #00a32a; font-weight: 600;"><?php esc_html_e( 'Excellent Health', 'wpshadow' ); ?></p>
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
		$allowed_keys = array();

		if ( isset( $config_sub_features[ $requested_sub_key ] ) ) {
			$allowed_keys[] = $requested_sub_key;
		}

		$child_config_map = array(
			'asset-version-removal' => array(
				'remove_css_versions'      => array( 'css_ignore_rules' ),
				'remove_js_versions'       => array( 'js_ignore_rules' ),
				'preserve_plugin_versions' => array( 'plugin_ignore_list' ),
			),
		);

		if ( isset( $child_config_map[ $feature['id'] ][ $requested_sub_key ] ) ) {
			$allowed_keys = array_merge( $allowed_keys, $child_config_map[ $feature['id'] ][ $requested_sub_key ] );
		}

		if ( ! empty( $allowed_keys ) ) {
			$config_sub_features = array_intersect_key( $config_sub_features, array_flip( $allowed_keys ) );
		} else {
			$config_sub_features = array();
		}
	}
	
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
				<div style="margin-bottom: 12px;">
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
				<?php
				$config_renderers = array(
					__NAMESPACE__ . '\wpshadow_render_' . $feature['id'] . '_' . $sub_key . '_config', // namespaced feature-scoped
					__NAMESPACE__ . '\wpshadow_render_' . $sub_key . '_config', // namespaced generic
					'wpshadow_render_' . $feature['id'] . '_' . $sub_key . '_config', // global feature-scoped (fallback)
					'wpshadow_render_' . $sub_key . '_config', // global generic (fallback)
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
						$btn.css('background-color', '#90EE90');
						setTimeout(function() {
							$btn.css('background-color', '').prop('disabled', false).text('<?php echo esc_js( __( 'Save CSS Rules', 'wpshadow' ) ); ?>');
						}, 1500);
					}
				},
				error: function() {
					$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Save CSS Rules', 'wpshadow' ) ); ?>');
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
						$btn.css('background-color', '#90EE90');
						setTimeout(function() {
							$btn.css('background-color', '').prop('disabled', false).text('<?php echo esc_js( __( 'Save JS Rules', 'wpshadow' ) ); ?>');
						}, 1500);
					}
				},
				error: function() {
					$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Save JS Rules', 'wpshadow' ) ); ?>');
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
						$btn.css('background-color', '#90EE90');
						setTimeout(function() {
							$btn.css('background-color', '').prop('disabled', false).text('<?php echo esc_js( __( 'Save Plugin List', 'wpshadow' ) ); ?>');
						}, 1500);
					}
				},
				error: function() {
					$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Save Plugin List', 'wpshadow' ) ); ?>');
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
					<tr class="wpshadow-child-feature wpshadow-current-feature" data-parent-feature="<?php echo esc_attr( $parent_id ); ?>" data-subfeature-key="<?php echo esc_attr( $sub_key ); ?>" data-default-enabled="<?php echo esc_attr( $sub_feature['default_enabled'] ?? true ? '1' : '0' ); ?>" style="background: #f9f9f9;" id="wpshadow-scroll-target">
						<td style="padding: 12px 16px 12px 48px;">
							<div style="margin-bottom: 2px;">
								<span style="font-size: 13px; font-weight: 600;">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&wpshadow_tab=features&feature=' . urlencode( $sub_key ) ) ); ?>" style="color: #000; text-decoration: none;">
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
			<script>
			jQuery(document).ready(function($) {
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
							// Trigger custom event for history refresh
							if (response.data && response.data.feature_id) {
								$(document).trigger('wpshadow:feature_toggled', [response.data.feature_id]);
							}
						} else {
							// Revert toggle on error
							$toggle.prop('checked', !enabled);
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
							// Trigger custom event for history refresh
							if (response.data && response.data.feature_id) {
								$(document).trigger('wpshadow:feature_toggled', [response.data.feature_id]);
							}
						} else {
							// Revert toggle on error
							$toggle.prop('checked', !enabled);
						}
					});
				});
			});
			</script>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Render features info widget.
 *
 * @return void
 */
function wpshadow_render_features_info_widget(): void {
	$network_scope = is_multisite() && is_network_admin();
	$all_features = WPSHADOW_Feature_Registry::get_features( $network_scope );
	$enabled_count = 0;
	$total_count = count( $all_features );
	
	foreach ( $all_features as $feature ) {
		if ( ! empty( $feature['enabled'] ) ) {
			$enabled_count++;
		}
	}
	
	?>
	<div class="wpshadow-widget-content" style="margin: 15px;">
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
