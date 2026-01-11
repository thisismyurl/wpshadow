<?php
/**
 * Modules Dashboard View
 *
 * @package wp_support_Support
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Normalize inputs.
$modules           = isset( $modules ) && is_array( $modules ) ? $modules : array();
$activity_logs     = isset( $activity_logs ) && is_array( $activity_logs ) ? $activity_logs : array();
$pending_uploads   = isset( $pending_uploads ) && is_array( $pending_uploads ) ? $pending_uploads : array();
$schedule_snapshot = isset( $schedule_snapshot ) && is_array( $schedule_snapshot ) ? $schedule_snapshot : array();
$run_now_nonce     = isset( $run_now_nonce ) ? (string) $run_now_nonce : '';
$hub_modules       = array_filter( $modules, static fn( $m ) => ( $m['type'] ?? '' ) === 'hub' );
$spoke_modules     = array_filter( $modules, static fn( $m ) => ( $m['type'] ?? '' ) === 'spoke' );

$modules_url = is_network_admin()
	? network_admin_url( 'admin.php?page=wps-core-modules' )
	: admin_url( 'admin.php?page=wps-core-modules' );
?>

<div class="wrap wps-dashboard-wrap wps-container" id="wps-dashboard-main" role="main">
	<a class="wps-skip-link wps-sr-only" href="#wps-dashboard-grid"><?php esc_html_e( 'Skip to dashboard content', 'plugin-wp-support-thisismyurl' ); ?></a>

	<div style="margin-bottom: var(--wps-space-2xl);">
		<h1 style="margin-bottom: var(--wps-space-sm);"><?php esc_html_e( 'Support Dashboard', 'plugin-wp-support-thisismyurl' ); ?></h1>
		<p class="wps-text-secondary" style="margin: 0;"><?php esc_html_e( 'Operational view of module activity, scheduled tasks, and pending reviews.', 'plugin-wp-support-thisismyurl' ); ?></p>
	</div>

	<!-- Stats Grid -->
	<div class="wps-grid wps-grid-3" style="margin-bottom: var(--wps-space-2xl);">
		<div class="wps-card" role="group" aria-label="<?php esc_attr_e( 'Total modules', 'plugin-wp-support-thisismyurl' ); ?>">
			<div style="display: flex; align-items: center; gap: var(--wps-space-md); margin-bottom: var(--wps-space-md);">
				<span class="dashicons dashicons-admin-plugins" style="font-size: 32px; color: var(--wps-accent-primary); width: auto; height: auto;"></span>
				<div>
					<div style="font-size: var(--wps-text-2xl); font-weight: var(--wps-font-bold); color: var(--wps-text-primary);"><?php echo esc_html( number_format_i18n( (int) ( $total_count ?? 0 ) ) ); ?></div>
					<div style="font-size: var(--wps-text-sm); color: var(--wps-text-muted);"><?php esc_html_e( 'Total', 'plugin-wp-support-thisismyurl' ); ?></div>
				</div>
			</div>
		</div>
		<div class="wps-card" role="group" aria-label="<?php esc_attr_e( 'Enabled modules', 'plugin-wp-support-thisismyurl' ); ?>">
			<div style="display: flex; align-items: center; gap: var(--wps-space-md); margin-bottom: var(--wps-space-md);">
				<span class="dashicons dashicons-yes-alt" style="font-size: 32px; color: var(--wps-accent-success); width: auto; height: auto;"></span>
				<div>
					<div style="font-size: var(--wps-text-2xl); font-weight: var(--wps-font-bold); color: var(--wps-text-primary);"><?php echo esc_html( number_format_i18n( (int) ( $enabled_count ?? 0 ) ) ); ?></div>
					<div style="font-size: var(--wps-text-sm); color: var(--wps-text-muted);"><?php esc_html_e( 'Enabled', 'plugin-wp-support-thisismyurl' ); ?></div>
				</div>
			</div>
		</div>
		<div class="wps-card" role="group" aria-label="<?php esc_attr_e( 'Available modules', 'plugin-wp-support-thisismyurl' ); ?>">
			<div style="display: flex; align-items: center; gap: var(--wps-space-md); margin-bottom: var(--wps-space-md);">
				<span class="dashicons dashicons-plus-alt" style="font-size: 32px; color: var(--wps-accent-info); width: auto; height: auto;"></span>
				<div>
					<div style="font-size: var(--wps-text-2xl); font-weight: var(--wps-font-bold); color: var(--wps-text-primary);"><?php echo esc_html( number_format_i18n( (int) ( $available_count ?? 0 ) ) ); ?></div>
					<div style="font-size: var(--wps-text-sm); color: var(--wps-text-muted);"><?php esc_html_e( 'Available', 'plugin-wp-support-thisismyurl' ); ?></div>
				</div>
			</div>
		</div>
		<div class="wps-card" role="group" aria-label="<?php esc_attr_e( 'Updates available', 'plugin-wp-support-thisismyurl' ); ?>">
			<div style="display: flex; align-items: center; gap: var(--wps-space-md); margin-bottom: var(--wps-space-md);">
				<span class="dashicons dashicons-update" style="font-size: 32px; color: var(--wps-accent-warning); width: auto; height: auto;"></span>
				<div>
					<div style="font-size: var(--wps-text-2xl); font-weight: var(--wps-font-bold); color: var(--wps-text-primary);"><?php echo esc_html( number_format_i18n( (int) ( $updates_count ?? 0 ) ) ); ?></div>
					<div style="font-size: var(--wps-text-sm); color: var(--wps-text-muted);"><?php esc_html_e( 'Updates', 'plugin-wp-support-thisismyurl' ); ?></div>
				</div>
			</div>
		</div>
		<div class="wps-card" role="group" aria-label="<?php esc_attr_e( 'Hubs', 'plugin-wp-support-thisismyurl' ); ?>">
			<div style="display: flex; align-items: center; gap: var(--wps-space-md); margin-bottom: var(--wps-space-md);">
				<span class="dashicons dashicons-networking" style="font-size: 32px; color: var(--wps-accent-primary); width: auto; height: auto;"></span>
				<div>
					<div style="font-size: var(--wps-text-2xl); font-weight: var(--wps-font-bold); color: var(--wps-text-primary);"><?php echo esc_html( number_format_i18n( (int) ( $hubs_count ?? count( $hub_modules ) ) ) ); ?></div>
					<div style="font-size: var(--wps-text-sm); color: var(--wps-text-muted);"><?php esc_html_e( 'Hubs', 'plugin-wp-support-thisismyurl' ); ?></div>
				</div>
			</div>
		</div>
		<div class="wps-card" role="group" aria-label="<?php esc_attr_e( 'Spokes', 'plugin-wp-support-thisismyurl' ); ?>">
			<div style="display: flex; align-items: center; gap: var(--wps-space-md); margin-bottom: var(--wps-space-md);">
				<span class="dashicons dashicons-admin-tools" style="font-size: 32px; color: var(--wps-accent-info); width: auto; height: auto;"></span>
				<div>
					<div style="font-size: var(--wps-text-2xl); font-weight: var(--wps-font-bold); color: var(--wps-text-primary);"><?php echo esc_html( number_format_i18n( (int) ( $spokes_count ?? count( $spoke_modules ) ) ) ); ?></div>
					<div style="font-size: var(--wps-text-sm); color: var(--wps-text-muted);"><?php esc_html_e( 'Spokes', 'plugin-wp-support-thisismyurl' ); ?></div>
				</div>
			</div>
		</div>
	</div>

	<!-- WordPress Metabox Layout with Drag & Drop Support -->
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-<?php echo 1 === (int) get_current_screen()->get_columns() ? '1' : '2'; ?>">
			<div id="postbox-container-1" class="postbox-container">
				<?php do_meta_boxes( get_current_screen()->id, 'side', array() ); ?>
			</div>
			<div id="postbox-container-2" class="postbox-container">
				<?php do_meta_boxes( get_current_screen()->id, 'normal', array() ); ?>
			</div>
		</div>
		<br class="clear">
	</div>

	<!-- Legacy Static Content (for reference - can be removed after full metabox migration) -->
	<div class="wps-grid wps-grid-2" id="wps-dashboard-grid" style="display: none;">
		<div class="wps-card">
			<div class="wps-card-header">
				<h2 style="margin: 0;"><?php esc_html_e( 'Activity Log', 'plugin-wp-support-thisismyurl' ); ?></h2>
			</div>
			<div class="wps-card-body">
				<p style="margin: 0 0 var(--wps-space-md) 0; font-size: var(--wps-text-sm); color: var(--wps-text-secondary);"><?php esc_html_e( 'Recent module installs, updates, toggles, and Vault events.', 'plugin-wp-support-thisismyurl' ); ?></p>
				<?php if ( ! empty( $activity_logs ) ) : ?>
					<div class="wps-table-responsive">
						<table class="wps-table">
							<caption class="wps-sr-only"><?php esc_html_e( 'Recent activity entries for the Support Dashboard.', 'plugin-wp-support-thisismyurl' ); ?></caption>
							<thead>
							<tr>
								<th scope="col"><?php esc_html_e( 'Time', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Level', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Task', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th scope="col"><?php esc_html_e( 'File', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th scope="col"><?php esc_html_e( 'User', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Details', 'plugin-wp-support-thisismyurl' ); ?></th>
							</tr>
							</thead>
							<tbody>
							<?php foreach ( $activity_logs as $entry ) : ?>
								<?php
								$timestamp = isset( $entry['timestamp'] ) ? strtotime( $entry['timestamp'] ) : 0;
								$time_text = $timestamp ? date_i18n( 'M j, Y g:i a', $timestamp ) : '—';
								$task      = $entry['task'] ?? ( $entry['operation'] ?? '' );
								$file      = $entry['file'] ?? '';
								if ( empty( $file ) && ! empty( $entry['attachment_id'] ) ) {
									$file = wp_basename( (string) get_attached_file( (int) $entry['attachment_id'] ) );
								}
								$user_name = $entry['user'] ?? '';
								if ( empty( $user_name ) && ! empty( $entry['user_id'] ) ) {
									$user      = get_user_by( 'id', (int) $entry['user_id'] );
									$user_name = $user && $user->exists() ? $user->display_name : '';
								}
								$reason      = $entry['reason'] ?? '';
								$level       = $entry['level'] ?? 'info';
								$level_class = 'wps-badge-info';
								if ( 'warning' === $level ) {
									$level_class = 'wps-badge-warning';
								} elseif ( 'error' === $level ) {
									$level_class = 'wps-badge-danger';
								}
								?>
								<tr>
									<td><?php echo esc_html( $time_text ); ?></td>
									<td><span class="wps-badge <?php echo esc_attr( $level_class ); ?>"><?php echo esc_html( ucfirst( $level ) ); ?></span></td>
									<td><?php echo $task ? esc_html( $task ) : '—'; ?></td>
									<td><?php echo $file ? esc_html( $file ) : '—'; ?></td>
									<td><?php echo $user_name ? esc_html( $user_name ) : '—'; ?></td>
									<td><?php echo $reason ? esc_html( $reason ) : '—'; ?></td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					</div>
					<div style="margin-top: var(--wps-space-md); padding-top: var(--wps-space-md); border-top: 1px solid var(--wps-border-subtle);">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wps-support-settings#wps-vault-logs' ) ); ?>" class="wps-btn wps-btn-secondary wps-btn-sm">
							<?php esc_html_e( 'View Full Log History →', 'plugin-wp-support-thisismyurl' ); ?>
						</a>
					</div>
				<?php else : ?>
					<p style="color: var(--wps-text-muted); font-style: italic; margin: 0;"><?php esc_html_e( 'No recent activity yet.', 'plugin-wp-support-thisismyurl' ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<div class="wps-card">
			<div class="wps-card-header">
				<h2 style="margin: 0;"><?php esc_html_e( 'Scheduled Tasks', 'plugin-wp-support-thisismyurl' ); ?></h2>
			</div>
			<div class="wps-card-body">
				<p style="margin: 0 0 var(--wps-space-md) 0; font-size: var(--wps-text-sm); color: var(--wps-text-secondary);"><?php esc_html_e( 'Upcoming suite jobs with their next run window.', 'plugin-wp-support-thisismyurl' ); ?></p>
				<?php if ( isset( $_GET['WPS_run_now'] ) ) : ?>
					<div class="wps-alert wps-alert-success" role="status" aria-live="polite" style="margin-bottom: var(--wps-space-md);">
						<p style="margin: 0;"><?php esc_html_e( 'Task started immediately.', 'plugin-wp-support-thisismyurl' ); ?></p>
					</div>
				<?php endif; ?>
				<?php if ( ! empty( $schedule_snapshot ) ) : ?>
					<div class="wps-table-responsive">
						<table class="wps-table">
							<caption class="wps-sr-only"><?php esc_html_e( 'Scheduled suite tasks with next and last run times.', 'plugin-wp-support-thisismyurl' ); ?></caption>
							<thead>
							<tr>
								<th scope="col"><?php esc_html_e( 'Task', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Next run', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Last run', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Status', 'plugin-wp-support-thisismyurl' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Action', 'plugin-wp-support-thisismyurl' ); ?></th>
							</tr>
							</thead>
							<tbody>
							<?php foreach ( $schedule_snapshot as $task_key => $task_data ) : ?>
								<?php
								$next_run = isset( $task_data['next_run'] ) ? (int) $task_data['next_run'] : 0;
								$last_run = isset( $task_data['last_run'] ) ? (int) $task_data['last_run'] : 0;
								$status   = $task_data['queue_state'] ?? __( 'Scheduled', 'plugin-wp-support-thisismyurl' );
								$next_txt = $next_run ? date_i18n( 'M j, Y g:i a', $next_run ) : __( 'Not scheduled', 'plugin-wp-support-thisismyurl' );
								$last_txt = $last_run ? date_i18n( 'M j, Y g:i a', $last_run ) : '—';
								$hook     = $task_data['hook'] ?? '';
								$task_lbl = isset( $task_data['label'] ) ? wp_strip_all_tags( (string) $task_data['label'] ) : ucfirst( $task_key );
								?>
								<tr>
									<td><?php echo esc_html( $task_lbl ); ?></td>
									<td><?php echo esc_html( $next_txt ); ?></td>
									<td><?php echo esc_html( $last_txt ); ?></td>
									<td><?php echo esc_html( ucfirst( $status ) ); ?></td>
									<td>
										<?php if ( ! empty( $hook ) ) : ?>
											<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display: inline;">
												<?php wp_nonce_field( 'WPS_run_task_now', 'nonce' ); ?>
												<input type="hidden" name="action" value="WPS_run_task_now" />
												<input type="hidden" name="hook" value="<?php echo esc_attr( $hook ); ?>" />
												<button type="submit" class="wps-btn wps-btn-secondary wps-btn-sm" aria-label="<?php echo esc_attr( sprintf( __( 'Run %s now', 'plugin-wp-support-thisismyurl' ), $task_lbl ) ); ?>"><?php esc_html_e( 'Run now', 'plugin-wp-support-thisismyurl' ); ?></button>
											</form>
										<?php else : ?>
											—
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else : ?>
					<p style="color: var(--wps-text-muted); font-style: italic; margin: 0;"><?php esc_html_e( 'No scheduled tasks found.', 'plugin-wp-support-thisismyurl' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
</div>

	<div class="wps-card">
		<div class="wps-card-header">
			<h2 style="margin: 0;"><?php esc_html_e( 'Pending Contributor Uploads', 'plugin-wp-support-thisismyurl' ); ?></h2>
		</div>
		<div class="wps-card-body">
			<p style="margin: 0 0 var(--wps-space-md) 0; font-size: var(--wps-text-sm); color: var(--wps-text-secondary);"><?php esc_html_e( 'Uploads from contributors are optimized by default and held for Editor+ review.', 'plugin-wp-support-thisismyurl' ); ?></p>
			<?php if ( ! empty( $pending_uploads ) ) : ?>
				<div class="wps-table-responsive">
					<table class="wps-table">
						<caption class="wps-sr-only"><?php esc_html_e( 'Pending contributor uploads awaiting review.', 'plugin-wp-support-thisismyurl' ); ?></caption>
						<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'File', 'plugin-wp-support-thisismyurl' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Uploaded by', 'plugin-wp-support-thisismyurl' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Uploaded at', 'plugin-wp-support-thisismyurl' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Optimized', 'plugin-wp-support-thisismyurl' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Action', 'plugin-wp-support-thisismyurl' ); ?></th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ( $pending_uploads as $item ) : ?>
							<?php
							$uploaded = ! empty( $item['date'] ) ? date_i18n( 'M j, Y g:i a', strtotime( $item['date'] ) ) : '—';
							?>
							<tr>
								<td><?php echo esc_html( $item['file'] ?: $item['title'] ); ?></td>
								<td><?php echo esc_html( $item['user'] ?: __( 'Unknown', 'plugin-wp-support-thisismyurl' ) ); ?></td>
								<td><?php echo esc_html( $uploaded ); ?></td>
								<td><?php echo ! empty( $item['optimized'] ) ? '<span class="wps-badge wps-badge-success">' . esc_html__( 'Yes', 'plugin-wp-support-thisismyurl' ) . '</span>' : '<span class="wps-badge">' . esc_html__( 'No', 'plugin-wp-support-thisismyurl' ) . '</span>'; ?></td>
								<td>
									<?php if ( ! empty( $item['edit_link'] ) ) : ?>
										<a class="wps-btn wps-btn-primary wps-btn-sm" href="<?php echo esc_url( $item['edit_link'] ); ?>"><?php esc_html_e( 'Review', 'plugin-wp-support-thisismyurl' ); ?></a>
									<?php else : ?>
										—
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else : ?>
				<p style="color: var(--wps-text-muted); font-style: italic; margin: 0;"><?php esc_html_e( 'No pending contributor uploads.', 'plugin-wp-support-thisismyurl' ); ?></p>
			<?php endif; ?>
		</div>
	</div>


