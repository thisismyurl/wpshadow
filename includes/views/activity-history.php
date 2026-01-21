<?php
/**
 * Activity History View - Comprehensive audit log
 * 
 * Philosophy: Show value (#9) - Prove impact with detailed history
 * Privacy: Beyond Pure (#10) - Local-only tracking, no external calls
 * 
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get filters from query string
$filter_category = isset( $_GET['activity_category'] ) ? sanitize_key( $_GET['activity_category'] ) : '';
$filter_action = isset( $_GET['activity_action'] ) ? sanitize_key( $_GET['activity_action'] ) : '';
$filter_user = isset( $_GET['activity_user'] ) ? intval( $_GET['activity_user'] ) : 0;
$filter_search = isset( $_GET['activity_search'] ) ? sanitize_text_field( $_GET['activity_search'] ) : '';
$page_num = isset( $_GET['activity_page'] ) ? max( 1, intval( $_GET['activity_page'] ) ) : 1;
$per_page = 25;

// Build filters array
$filters = array();
if ( ! empty( $filter_category ) ) {
	$filters['category'] = $filter_category;
}
if ( ! empty( $filter_action ) ) {
	$filters['action'] = $filter_action;
}
if ( ! empty( $filter_user ) ) {
	$filters['user_id'] = $filter_user;
}
if ( ! empty( $filter_search ) ) {
	$filters['search'] = $filter_search;
}

// Get activities
$result = \WPShadow\Core\Activity_Logger::get_activities( $filters, $per_page, ( $page_num - 1 ) * $per_page );
$activities = $result['activities'];
$total = $result['total'];
$total_pages = ceil( $total / $per_page );

// Get counts for filters
$action_counts = \WPShadow\Core\Activity_Logger::get_action_counts();
$category_counts = \WPShadow\Core\Activity_Logger::get_category_counts();

// Category metadata
$category_meta = array(
	'security'         => array( 'label' => __( 'Security', 'wpshadow' ), 'color' => '#dc2626' ),
	'performance'      => array( 'label' => __( 'Performance', 'wpshadow' ), 'color' => '#0891b2' ),
	'code_quality'     => array( 'label' => __( 'Code Quality', 'wpshadow' ), 'color' => '#7c3aed' ),
	'seo'              => array( 'label' => __( 'SEO', 'wpshadow' ), 'color' => '#2563eb' ),
	'design'           => array( 'label' => __( 'Design', 'wpshadow' ), 'color' => '#8e44ad' ),
	'settings'         => array( 'label' => __( 'Settings', 'wpshadow' ), 'color' => '#4b5563' ),
	'wordpress_config' => array( 'label' => __( 'WordPress Config', 'wpshadow' ), 'color' => '#0073aa' ),
	'monitoring'       => array( 'label' => __( 'Monitoring', 'wpshadow' ), 'color' => '#059669' ),
	'workflows'        => array( 'label' => __( 'Workflows', 'wpshadow' ), 'color' => '#ea580c' ),
	'site_health'      => array( 'label' => __( 'Site Health', 'wpshadow' ), 'color' => '#db2777' ),
);

// Action type labels
$action_labels = array(
	'diagnostic_run'                  => __( 'Diagnostic Run', 'wpshadow' ),
	'treatment_applied'               => __( 'Treatment Applied', 'wpshadow' ),
	'treatment_undone'                => __( 'Treatment Undone', 'wpshadow' ),
	'finding_dismissed'               => __( 'Finding Dismissed', 'wpshadow' ),
	'finding_status_change'           => __( 'Status Changed', 'wpshadow' ),
	'finding_excluded'                => __( 'Finding Excluded', 'wpshadow' ),
	'finding_resolved'                => __( 'Finding Resolved', 'wpshadow' ),
	'plugin_activated'                => __( 'Plugin Activated', 'wpshadow' ),
	'plugin_deactivated'              => __( 'Plugin Deactivated', 'wpshadow' ),
	'workflow_created'                => __( 'Workflow Created', 'wpshadow' ),
	'workflow_executed'               => __( 'Workflow Executed', 'wpshadow' ),
	'workflow_enabled'                => __( 'Workflow Enabled', 'wpshadow' ),
	'workflow_disabled'               => __( 'Workflow Disabled', 'wpshadow' ),
	'workflow_saved'                  => __( 'Workflow Saved', 'wpshadow' ),
	'workflow_deleted'                => __( 'Workflow Deleted', 'wpshadow' ),
	'settings_changed'                => __( 'Settings Changed', 'wpshadow' ),
	'site_settings_changed'           => __( 'Site Settings Changed', 'wpshadow' ),
	'cache_settings_changed'          => __( 'Cache Settings Changed', 'wpshadow' ),
	'autofix_permission_enabled'      => __( 'Auto-fix Enabled', 'wpshadow' ),
	'autofix_permission_disabled'     => __( 'Auto-fix Disabled', 'wpshadow' ),
	'user_preferences_changed'        => __( 'User Preferences Changed', 'wpshadow' ),
);
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Activity History', 'wpshadow' ); ?></h1>
	<p><?php esc_html_e( 'Comprehensive audit log of all WPShadow actions and system changes.', 'wpshadow' ); ?></p>
	
	<!-- Filters -->
	<div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; margin: 20px 0;">
		<form method="get" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: end;">
			<input type="hidden" name="page" value="wpshadow-activity" />
			
			<!-- Search -->
			<div>
				<label for="activity_search" style="display: block; margin-bottom: 4px; font-weight: 600;">
					<?php esc_html_e( 'Search', 'wpshadow' ); ?>
				</label>
				<input type="text" id="activity_search" name="activity_search" value="<?php echo esc_attr( $filter_search ); ?>" placeholder="<?php esc_attr_e( 'Search activities...', 'wpshadow' ); ?>" style="width: 100%;" />
			</div>
			
			<!-- Category Filter -->
			<div>
				<label for="activity_category" style="display: block; margin-bottom: 4px; font-weight: 600;">
					<?php esc_html_e( 'Category', 'wpshadow' ); ?>
				</label>
				<select id="activity_category" name="activity_category" style="width: 100%;">
					<option value=""><?php esc_html_e( 'All Categories', 'wpshadow' ); ?></option>
					<?php foreach ( $category_counts as $cat => $count ) : 
						$cat_label = isset( $category_meta[ $cat ]['label'] ) ? $category_meta[ $cat ]['label'] : ucfirst( $cat );
					?>
					<option value="<?php echo esc_attr( $cat ); ?>" <?php selected( $filter_category, $cat ); ?>>
						<?php echo esc_html( sprintf( '%s (%d)', $cat_label, $count ) ); ?>
					</option>
					<?php endforeach; ?>
				</select>
			</div>
			
			<!-- Action Filter -->
			<div>
				<label for="activity_action" style="display: block; margin-bottom: 4px; font-weight: 600;">
					<?php esc_html_e( 'Action Type', 'wpshadow' ); ?>
				</label>
				<select id="activity_action" name="activity_action" style="width: 100%;">
					<option value=""><?php esc_html_e( 'All Actions', 'wpshadow' ); ?></option>
					<?php foreach ( $action_counts as $action => $count ) : 
						$action_label = isset( $action_labels[ $action ] ) ? $action_labels[ $action ] : ucfirst( str_replace( '_', ' ', $action ) );
					?>
					<option value="<?php echo esc_attr( $action ); ?>" <?php selected( $filter_action, $action ); ?>>
						<?php echo esc_html( sprintf( '%s (%d)', $action_label, $count ) ); ?>
					</option>
					<?php endforeach; ?>
				</select>
			</div>
			
			<!-- Submit and Export -->
			<div style="display: flex; gap: 8px;">
				<button type="submit" class="button button-primary" style="flex: 1;">
					<?php esc_html_e( 'Filter', 'wpshadow' ); ?>
				</button>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-activity&export=csv' . ( ! empty( $filters ) ? '&' . http_build_query( $filters ) : '' ) ) ); ?>" class="button" style="flex: 1;" title="<?php esc_attr_e( 'Export to CSV', 'wpshadow' ); ?>">
					<span class="dashicons dashicons-download" style="vertical-align: middle;"></span>
					<?php esc_html_e( 'Export', 'wpshadow' ); ?>
				</a>
			</div>
		</form>
		
		<?php if ( ! empty( $filters ) ) : ?>
		<div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #ddd;">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-activity' ) ); ?>" style="color: #2271b1; text-decoration: none;">
				<span class="dashicons dashicons-dismiss" style="vertical-align: middle;"></span>
				<?php esc_html_e( 'Clear Filters', 'wpshadow' ); ?>
			</a>
		</div>
		<?php endif; ?>
	</div>
	
	<!-- Activity Table -->
	<?php if ( empty( $activities ) ) : ?>
	<div style="background: #fff; padding: 40px; text-align: center; border: 1px solid #ddd; border-radius: 4px;">
		<span class="dashicons dashicons-info" style="font-size: 48px; color: #999; margin-bottom: 16px;"></span>
		<p style="font-size: 16px; color: #666; margin: 0;">
			<?php echo empty( $filters ) ? esc_html__( 'No activities recorded yet.', 'wpshadow' ) : esc_html__( 'No activities match your filters.', 'wpshadow' ); ?>
		</p>
	</div>
	<?php else : ?>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th style="width: 160px;"><?php esc_html_e( 'Date/Time', 'wpshadow' ); ?></th>
				<th style="width: 120px;"><?php esc_html_e( 'User', 'wpshadow' ); ?></th>
				<th style="width: 120px;"><?php esc_html_e( 'Category', 'wpshadow' ); ?></th>
				<th style="width: 150px;"><?php esc_html_e( 'Action', 'wpshadow' ); ?></th>
				<th><?php esc_html_e( 'Details', 'wpshadow' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $activities as $activity ) : 
				$cat_color = isset( $category_meta[ $activity['category'] ]['color'] ) ? $category_meta[ $activity['category'] ]['color'] : '#666';
				$cat_label = isset( $category_meta[ $activity['category'] ]['label'] ) ? $category_meta[ $activity['category'] ]['label'] : ucfirst( $activity['category'] );
				$action_label = isset( $action_labels[ $activity['action'] ] ) ? $action_labels[ $activity['action'] ] : ucfirst( str_replace( '_', ' ', $activity['action'] ) );

				$kb_link = '';
				$training_link = '';
				if ( ! empty( $activity['metadata']['finding_id'] ) && function_exists( 'wpshadow_get_kb_link' ) && function_exists( 'wpshadow_get_training_link' ) ) {
					$slug = sanitize_title( (string) $activity['metadata']['finding_id'] );
					$kb_link = wpshadow_get_kb_link( $slug );
					$training_link = wpshadow_get_training_link( $slug );
				}
			?>
			<tr>
				<td><?php echo esc_html( $activity['date'] ); ?></td>
				<td><?php echo esc_html( $activity['user_name'] ); ?></td>
				<td>
					<?php if ( ! empty( $activity['category'] ) ) : ?>
					<span style="display: inline-block; padding: 4px 8px; background: <?php echo esc_attr( $cat_color ); ?>; color: #fff; border-radius: 3px; font-size: 11px; font-weight: 600;">
						<?php echo esc_html( $cat_label ); ?>
					</span>
					<?php else : ?>
					<span style="color: #999;">—</span>
					<?php endif; ?>
				</td>
				<td><?php echo esc_html( $action_label ); ?></td>
				<td>
					<div><?php echo esc_html( $activity['details'] ); ?></div>
					<?php if ( ! empty( $kb_link ) || ! empty( $training_link ) ) : ?>
					<div style="margin-top: 6px; display: flex; gap: 10px; flex-wrap: wrap; font-size: 11px;">
						<?php if ( ! empty( $kb_link ) ) : ?>
							<a href="<?php echo esc_url( $kb_link ); ?>" target="_blank" style="color: #2563eb; text-decoration: none; font-weight: 600;">
								<?php esc_html_e( 'Learn more (KB)', 'wpshadow' ); ?>
							</a>
						<?php endif; ?>
						<?php if ( ! empty( $training_link ) ) : ?>
							<a href="<?php echo esc_url( $training_link ); ?>" target="_blank" style="color: #16a34a; text-decoration: none; font-weight: 600;">
								<?php esc_html_e( 'Watch training', 'wpshadow' ); ?>
							</a>
						<?php endif; ?>
					</div>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	
	<!-- Pagination -->
	<?php if ( $total_pages > 1 ) : ?>
	<div class="tablenav" style="margin-top: 20px;">
		<div class="tablenav-pages">
			<span class="displaying-num"><?php echo esc_html( sprintf( _n( '%s item', '%s items', $total, 'wpshadow' ), number_format_i18n( $total ) ) ); ?></span>
			<?php
			$base_url = admin_url( 'admin.php?page=wpshadow-activity' );
			if ( ! empty( $filters ) ) {
				$base_url .= '&' . http_build_query( $filters );
			}
			
			if ( $page_num > 1 ) {
				echo '<a class="button" href="' . esc_url( $base_url . '&activity_page=' . ( $page_num - 1 ) ) . '">&laquo; ' . esc_html__( 'Previous', 'wpshadow' ) . '</a> ';
			}
			
			echo '<span class="paging-input">';
			echo esc_html( sprintf( __( 'Page %d of %d', 'wpshadow' ), $page_num, $total_pages ) );
			echo '</span>';
			
			if ( $page_num < $total_pages ) {
				echo ' <a class="button" href="' . esc_url( $base_url . '&activity_page=' . ( $page_num + 1 ) ) . '">' . esc_html__( 'Next', 'wpshadow' ) . ' &raquo;</a>';
			}
			?>
		</div>
	</div>
	<?php endif; ?>
	<?php endif; ?>
</div>
