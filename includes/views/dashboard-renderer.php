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
	<div class="wrap">
		<h1><?php echo esc_html( $page_title ); ?></h1>
		
		<?php wpshadow_render_breadcrumbs( $tab ); ?>
		
		<style>
			/* Two-column layout: 66% / 33% */
			#dashboard-widgets .postbox-container {
				width: 100% !important;
				float: none !important;
			}
			@media screen and (min-width: 800px) {
				#dashboard-widgets #postbox-container-1 {
					width: 66% !important;
					float: left !important;
					margin-right: 0 !important;
				}
				#dashboard-widgets #postbox-container-2 {
					width: 33% !important;
					float: right !important;
					margin-left: 0 !important;
				}
			}
			#dashboard-widgets .postbox-container {
				padding: 0 8px !important;
				box-sizing: border-box !important;
			}
			#dashboard-widgets {
				overflow: hidden;
			}
			.wpshadow-widget-content {
				padding: 12px;
			}
		</style>

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
	switch ( $tab ) {
		case 'dashboard':
			wpshadow_register_dashboard_metaboxes( $screen_id );
			break;
		case 'features':
			wpshadow_register_features_metaboxes( $screen_id );
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
	// Left column (66%) - Feature list.
	add_meta_box(
		'wpshadow_features_list',
		__( 'Available Features', 'wpshadow' ),
		__NAMESPACE__ . '\\wpshadow_render_features_list_widget',
		$screen_id,
		'normal',
		'high'
	);

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
 * Render features list widget.
 *
 * @return void
 */
function wpshadow_render_features_list_widget(): void {
	$network_scope = is_multisite() && is_network_admin();
	$features = WPSHADOW_Feature_Registry::get_features_by_scope( 'core', '', '', $network_scope );
	?>
	<div class="wpshadow-widget-content">
		<?php if ( empty( $features ) ) : ?>
			<p><?php esc_html_e( 'No features available.', 'wpshadow' ); ?></p>
		<?php else : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Feature', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Status', 'wpshadow' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $features as $feature ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $feature['name'] ?? $feature['id'] ); ?></strong></td>
							<td><?php echo $feature['enabled'] ? '<span style="color: #00a32a;">●</span> ' . esc_html__( 'Active', 'wpshadow' ) : '<span style="color: #dba617;">●</span> ' . esc_html__( 'Inactive', 'wpshadow' ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
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
	?>
	<div class="wpshadow-widget-content">
		<h4><?php esc_html_e( 'About Features', 'wpshadow' ); ?></h4>
		<p><?php esc_html_e( 'Features extend WPShadow functionality. Enable or disable features based on your needs.', 'wpshadow' ); ?></p>
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
function wpshadow_render_breadcrumbs( string $tab ): void {
	$base_url = admin_url( 'admin.php?page=wpshadow' );
	
	$breadcrumbs = array(
		array(
			'label' => __( 'Dashboard', 'wpshadow' ),
			'url'   => $base_url . '&wpshadow_tab=dashboard',
			'active' => 'dashboard' === $tab,
		),
		array(
			'label' => __( 'Features', 'wpshadow' ),
			'url'   => $base_url . '&wpshadow_tab=features',
			'active' => 'features' === $tab,
		),
		array(
			'label' => __( 'Help', 'wpshadow' ),
			'url'   => $base_url . '&wpshadow_tab=help',
			'active' => 'help' === $tab,
		),
	);
	
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
