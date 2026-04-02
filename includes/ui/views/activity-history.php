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

use WPShadow\Core\Form_Param_Helper;

// Get filters from query string
$filter_category = Form_Param_Helper::get( 'activity_category', 'key', '' );
$filter_action   = Form_Param_Helper::get( 'activity_action', 'key', '' );
$filter_user     = Form_Param_Helper::get( 'activity_user', 'int', 0 );
$filter_search   = Form_Param_Helper::get( 'activity_search', 'text', '' );
$page_num        = max( 1, Form_Param_Helper::get( 'activity_page', 'int', 1 ) );
$per_page        = 25;

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
$result      = \WPShadow\Core\Activity_Logger::get_activities( $filters, $per_page, ( $page_num - 1 ) * $per_page );
$activities  = $result['activities'];
$total       = $result['total'];
$total_pages = ceil( $total / $per_page );

// Get counts for filters
$action_counts   = \WPShadow\Core\Activity_Logger::get_action_counts();
$category_counts = \WPShadow\Core\Activity_Logger::get_category_counts();

// Category metadata
$category_meta = array(
	'security'         => array(
		'label' => __( 'Security', 'wpshadow' ),
		'color' => '#dc2626',
	),
	'performance'      => array(
		'label' => __( 'Performance', 'wpshadow' ),
		'color' => '#0891b2',
	),
	'code_quality'     => array(
		'label' => __( 'Code Quality', 'wpshadow' ),
		'color' => '#7c3aed',
	),
	'seo'              => array(
		'label' => __( 'SEO', 'wpshadow' ),
		'color' => '#2563eb',
	),
	'design'           => array(
		'label' => __( 'Design', 'wpshadow' ),
		'color' => '#8e44ad',
	),
	'settings'         => array(
		'label' => __( 'Settings', 'wpshadow' ),
		'color' => '#4b5563',
	),
	'wordpress_config' => array(
		'label' => __( 'WordPress Config', 'wpshadow' ),
		'color' => '#0073aa',
	),
	'monitoring'       => array(
		'label' => __( 'Monitoring', 'wpshadow' ),
		'color' => '#059669',
	),
	'workflows'        => array(
		'label' => __( 'Workflows', 'wpshadow' ),
		'color' => '#ea580c',
	),
	'site_health'      => array(
		'label' => __( 'Site Health', 'wpshadow' ),
		'color' => '#db2777',
	),
);

// Action type labels
$action_labels = array(
	'diagnostic_run'              => __( 'Diagnostic Run', 'wpshadow' ),
	'treatment_applied'           => __( 'Treatment Applied', 'wpshadow' ),
	'treatment_undone'            => __( 'Treatment Undone', 'wpshadow' ),
	'finding_dismissed'           => __( 'Finding Dismissed', 'wpshadow' ),
	'finding_status_change'       => __( 'Status Changed', 'wpshadow' ),
	'finding_excluded'            => __( 'Finding Excluded', 'wpshadow' ),
	'finding_resolved'            => __( 'Finding Resolved', 'wpshadow' ),
	'plugin_activated'            => __( 'Plugin Activated', 'wpshadow' ),
	'plugin_deactivated'          => __( 'Plugin Deactivated', 'wpshadow' ),
	'workflow_created'            => __( 'Workflow Created', 'wpshadow' ),
	'workflow_executed'           => __( 'Workflow Executed', 'wpshadow' ),
	'workflow_enabled'            => __( 'Workflow Enabled', 'wpshadow' ),
	'workflow_disabled'           => __( 'Workflow Disabled', 'wpshadow' ),
	'workflow_saved'              => __( 'Workflow Saved', 'wpshadow' ),
	'workflow_deleted'            => __( 'Workflow Deleted', 'wpshadow' ),
	'settings_changed'            => __( 'Settings Changed', 'wpshadow' ),
	'user_login'                  => __( 'User Login', 'wpshadow' ),
	'finding_action'              => __( 'Finding Activity', 'wpshadow' ),
	'site_settings_changed'       => __( 'Site Settings Changed', 'wpshadow' ),
	'cache_settings_changed'      => __( 'Cache Settings Changed', 'wpshadow' ),
	'cache_cleared'               => __( 'Cache Cleared', 'wpshadow' ),
	'user_preferences_changed'    => __( 'User Preferences Changed', 'wpshadow' ),
	'retention_setting_updated'   => __( 'Retention Setting Updated', 'wpshadow' ),
	'data_cleanup_completed'      => __( 'Data Cleanup Completed', 'wpshadow' ),
	'diagnostic_failed'           => __( 'Diagnostic Failed', 'wpshadow' ),
);
?>

<div class="wrap wps-page-container">
	<?php wpshadow_render_page_header(
		__( 'Activity History', 'wpshadow' ),
		__( 'Comprehensive audit log of all WPShadow actions and system changes.', 'wpshadow' )
	); ?>

	<!-- Filters -->
	<div class="wps-card wps-m-20">
		<form method="get" class="wps-grid wps-grid-auto-200 wps-gap-4 wps-items-end">
			<input type="hidden" name="page" value="wpshadow-activity" />

			<!-- Search -->
			<div>
				<label for="activity_search" class="wps-form-label">
					<?php esc_html_e( 'Search', 'wpshadow' ); ?>
				</label>
				<input type="text" id="activity_search" name="activity_search" value="<?php echo esc_attr( $filter_search ); ?>" placeholder="<?php esc_attr_e( 'Search activities...', 'wpshadow' ); ?>" class="wps-input" />
			</div>

			<!-- Category Filter -->
			<div>
				<label for="activity_category" class="wps-form-label">
					<?php esc_html_e( 'Category', 'wpshadow' ); ?>
				</label>
				<select id="activity_category" name="activity_category" class="wps-select">
					<option value=""><?php esc_html_e( 'All Categories', 'wpshadow' ); ?></option>
					<?php
					foreach ( $category_counts as $cat => $count ) :
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
				<label for="activity_action" class="wps-form-label">
					<?php esc_html_e( 'Action Type', 'wpshadow' ); ?>
				</label>
				<select id="activity_action" name="activity_action" class="wps-select">
					<option value=""><?php esc_html_e( 'All Actions', 'wpshadow' ); ?></option>
					<?php
					foreach ( $action_counts as $action => $count ) :
						$action_label = isset( $action_labels[ $action ] ) ? $action_labels[ $action ] : ucfirst( str_replace( '_', ' ', $action ) );
						?>
						<option value="<?php echo esc_attr( $action ); ?>" <?php selected( $filter_action, $action ); ?>>
							<?php echo esc_html( sprintf( '%s (%d)', $action_label, $count ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<!-- User Filter -->
			<div>
				<label for="activity_user" class="wps-form-label">
					<?php esc_html_e( 'User', 'wpshadow' ); ?>
				</label>
				<select id="activity_user" name="activity_user" class="wps-select">
					<option value=""><?php esc_html_e( 'All Users', 'wpshadow' ); ?></option>
					<?php
					// Get unique users from activity log (get all stored, max 500)
					$all_activities = \WPShadow\Core\Activity_Logger::get_activities( array(), 500, 0 );
					$unique_users   = array();
					foreach ( $all_activities['activities'] as $activity ) {
						if ( ! empty( $activity['user_id'] ) && ! isset( $unique_users[ $activity['user_id'] ] ) ) {
							$user                        = get_user_by( 'id', $activity['user_id'] );
							$unique_users[ $activity['user_id'] ] = $user ? $user->display_name : __( 'Unknown User', 'wpshadow' );
						}
					}

					// Sort users by display name
					asort( $unique_users );

					foreach ( $unique_users as $user_id => $user_name ) :
						?>
						<option value="<?php echo esc_attr( (string) $user_id ); ?>" <?php selected( (string) $filter_user, (string) $user_id ); ?>>
							<?php echo esc_html( $user_name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<!-- Submit -->
			<div class="wps-flex-gap-8">
				<button type="submit" class="wps-btn wps-btn-primary wps-flex-1" aria-label="<?php esc_attr_e( 'Apply activity history filters', 'wpshadow' ); ?>">
					<?php esc_html_e( 'Filter', 'wpshadow' ); ?>
				</button>
			</div>
		</form>

		<?php if ( ! empty( $filters ) ) : ?>
			<div class="wps-mt-4 wps-pt-4 wps-border-t-gray-200">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-activity' ) ); ?>" class="wps-text-blue-600 wps-no-underline">
					<span class="dashicons dashicons-dismiss wps-align-middle"></span>
					<?php esc_html_e( 'Clear Filters', 'wpshadow' ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>

	<!-- Activity Table -->
	<?php if ( empty( $activities ) ) : ?>
		<div class="wps-p-40-rounded-4">
			<span class="dashicons dashicons-info wps-text-4xl wps-text-gray-400 wps-mb-4"></span>
			<p class="wps-m-0">
				<?php echo empty( $filters ) ? esc_html__( 'No activities recorded yet.', 'wpshadow' ) : esc_html__( 'No activities match your filters.', 'wpshadow' ); ?>
			</p>
		</div>
	<?php else : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th class="wps-w-40"><?php esc_html_e( 'Date/Time', 'wpshadow' ); ?></th>
					<th class="wps-w-30"><?php esc_html_e( 'User', 'wpshadow' ); ?></th>
					<th class="wps-w-30"><?php esc_html_e( 'Category', 'wpshadow' ); ?></th>
					<th class="wps-w-37"><?php esc_html_e( 'Action', 'wpshadow' ); ?></th>
					<th><?php esc_html_e( 'Details', 'wpshadow' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $activities as $activity ) :
					$cat_color    = isset( $category_meta[ $activity['category'] ]['color'] ) ? $category_meta[ $activity['category'] ]['color'] : '#666';
					$cat_label    = isset( $category_meta[ $activity['category'] ]['label'] ) ? $category_meta[ $activity['category'] ]['label'] : ucfirst( $activity['category'] );
					$action_label = isset( $action_labels[ $activity['action'] ] ) ? $action_labels[ $activity['action'] ] : ucfirst( str_replace( '_', ' ', $activity['action'] ) );

					$kb_link       = '';
					$training_link = '';
					if ( ! empty( $activity['metadata']['finding_id'] ) && function_exists( 'wpshadow_get_kb_link' ) && function_exists( 'wpshadow_get_training_link' ) ) {
						$slug          = sanitize_title( (string) $activity['metadata']['finding_id'] );
						$kb_link       = wpshadow_get_kb_link( $slug );
						$training_link = wpshadow_get_training_link( $slug );
					}
					?>
					<tr>
						<td><?php echo esc_html( $activity['date'] ); ?></td>
						<td><?php echo esc_html( $activity['user_name'] ); ?></td>
						<td>
							<?php if ( ! empty( $activity['category'] ) ) : ?>
								<span class="wps-inline-block-p-4-rounded-3">
									<?php echo esc_html( $cat_label ); ?>
								</span>
							<?php else : ?>
								<span class="wps-text-gray-400">—</span>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $action_label ); ?></td>
						<td>
							<div><?php echo esc_html( $activity['details'] ); ?></div>
							<?php if ( ! empty( $kb_link ) || ! empty( $training_link ) ) : ?>
								<div class="wps-flex-gap-10">
									<?php if ( ! empty( $kb_link ) ) : ?>
										<a href="<?php echo esc_url( $kb_link ); ?>" target="_blank" class="wps-text-blue-600 wps-no-underline wps-font-600">
											<?php esc_html_e( 'Learn more (KB)', 'wpshadow' ); ?>
										</a>
									<?php endif; ?>
									<?php if ( ! empty( $training_link ) ) : ?>
										<a href="<?php echo esc_url( $training_link ); ?>" target="_blank" class="wps-text-green-600 wps-no-underline wps-font-600">
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
			<div class="tablenav wps-mt-5">
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
					echo esc_html( sprintf( __( 'Page %1$d of %2$d', 'wpshadow' ), $page_num, $total_pages ) );
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
