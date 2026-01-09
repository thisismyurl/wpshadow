<?php
/**
 * Modules Dashboard View
 *
 * @package TIMU_Core_Support
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
	? network_admin_url( 'admin.php?page=timu-core-modules' )
	: admin_url( 'admin.php?page=timu-core-modules' );
?>

<div class="wrap timu-dashboard-wrap timu-container" id="timu-dashboard-main" role="main">
	<a class="timu-skip-link timu-sr-only" href="#timu-dashboard-grid"><?php esc_html_e( 'Skip to dashboard content', 'wordpress-support-thisismyurl' ); ?></a>

	<div style="margin-bottom: var(--timu-space-2xl);">
		<h1 style="margin-bottom: var(--timu-space-sm);"><?php esc_html_e( 'Support Dashboard', 'wordpress-support-thisismyurl' ); ?></h1>
		<p class="timu-text-secondary" style="margin: 0;"><?php esc_html_e( 'Operational view of module activity, scheduled tasks, and pending reviews.', 'wordpress-support-thisismyurl' ); ?></p>
	</div>

	<!-- Stats Grid -->
	<div class="timu-grid timu-grid-3" style="margin-bottom: var(--timu-space-2xl);">
		<div class="timu-card" role="group" aria-label="<?php esc_attr_e( 'Total modules', 'wordpress-support-thisismyurl' ); ?>">
			<div style="display: flex; align-items: center; gap: var(--timu-space-md); margin-bottom: var(--timu-space-md);">
				<span class="dashicons dashicons-admin-plugins" style="font-size: 32px; color: var(--timu-accent-primary); width: auto; height: auto;"></span>
				<div>
					<div style="font-size: var(--timu-text-2xl); font-weight: var(--timu-font-bold); color: var(--timu-text-primary);"><?php echo esc_html( number_format_i18n( (int) ( $total_count ?? 0 ) ) ); ?></div>
					<div style="font-size: var(--timu-text-sm); color: var(--timu-text-muted);"><?php esc_html_e( 'Total', 'wordpress-support-thisismyurl' ); ?></div>
				</div>
			</div>
		</div>
		<div class="timu-card" role="group" aria-label="<?php esc_attr_e( 'Enabled modules', 'wordpress-support-thisismyurl' ); ?>">
			<div style="display: flex; align-items: center; gap: var(--timu-space-md); margin-bottom: var(--timu-space-md);">
				<span class="dashicons dashicons-yes-alt" style="font-size: 32px; color: var(--timu-accent-success); width: auto; height: auto;"></span>
				<div>
					<div style="font-size: var(--timu-text-2xl); font-weight: var(--timu-font-bold); color: var(--timu-text-primary);"><?php echo esc_html( number_format_i18n( (int) ( $enabled_count ?? 0 ) ) ); ?></div>
					<div style="font-size: var(--timu-text-sm); color: var(--timu-text-muted);"><?php esc_html_e( 'Enabled', 'wordpress-support-thisismyurl' ); ?></div>
				</div>
			</div>
		</div>
		<div class="timu-card" role="group" aria-label="<?php esc_attr_e( 'Available modules', 'wordpress-support-thisismyurl' ); ?>">
			<div style="display: flex; align-items: center; gap: var(--timu-space-md); margin-bottom: var(--timu-space-md);">
				<span class="dashicons dashicons-plus-alt" style="font-size: 32px; color: var(--timu-accent-info); width: auto; height: auto;"></span>
				<div>
					<div style="font-size: var(--timu-text-2xl); font-weight: var(--timu-font-bold); color: var(--timu-text-primary);"><?php echo esc_html( number_format_i18n( (int) ( $available_count ?? 0 ) ) ); ?></div>
					<div style="font-size: var(--timu-text-sm); color: var(--timu-text-muted);"><?php esc_html_e( 'Available', 'wordpress-support-thisismyurl' ); ?></div>
				</div>
			</div>
		</div>
		<div class="timu-card" role="group" aria-label="<?php esc_attr_e( 'Updates available', 'wordpress-support-thisismyurl' ); ?>">
			<div style="display: flex; align-items: center; gap: var(--timu-space-md); margin-bottom: var(--timu-space-md);">
				<span class="dashicons dashicons-update" style="font-size: 32px; color: var(--timu-accent-warning); width: auto; height: auto;"></span>
				<div>
					<div style="font-size: var(--timu-text-2xl); font-weight: var(--timu-font-bold); color: var(--timu-text-primary);"><?php echo esc_html( number_format_i18n( (int) ( $updates_count ?? 0 ) ) ); ?></div>
					<div style="font-size: var(--timu-text-sm); color: var(--timu-text-muted);"><?php esc_html_e( 'Updates', 'wordpress-support-thisismyurl' ); ?></div>
				</div>
			</div>
		</div>
		<div class="timu-card" role="group" aria-label="<?php esc_attr_e( 'Hubs', 'wordpress-support-thisismyurl' ); ?>">
			<div style="display: flex; align-items: center; gap: var(--timu-space-md); margin-bottom: var(--timu-space-md);">
				<span class="dashicons dashicons-networking" style="font-size: 32px; color: var(--timu-accent-primary); width: auto; height: auto;"></span>
				<div>
					<div style="font-size: var(--timu-text-2xl); font-weight: var(--timu-font-bold); color: var(--timu-text-primary);"><?php echo esc_html( number_format_i18n( (int) ( $hubs_count ?? count( $hub_modules ) ) ) ); ?></div>
					<div style="font-size: var(--timu-text-sm); color: var(--timu-text-muted);"><?php esc_html_e( 'Hubs', 'wordpress-support-thisismyurl' ); ?></div>
				</div>
			</div>
		</div>
		<div class="timu-card" role="group" aria-label="<?php esc_attr_e( 'Spokes', 'wordpress-support-thisismyurl' ); ?>">
			<div style="display: flex; align-items: center; gap: var(--timu-space-md); margin-bottom: var(--timu-space-md);">
				<span class="dashicons dashicons-admin-tools" style="font-size: 32px; color: var(--timu-accent-info); width: auto; height: auto;"></span>
				<div>
					<div style="font-size: var(--timu-text-2xl); font-weight: var(--timu-font-bold); color: var(--timu-text-primary);"><?php echo esc_html( number_format_i18n( (int) ( $spokes_count ?? count( $spoke_modules ) ) ) ); ?></div>
					<div style="font-size: var(--timu-text-sm); color: var(--timu-text-muted);"><?php esc_html_e( 'Spokes', 'wordpress-support-thisismyurl' ); ?></div>
				</div>
			</div>
		</div>
	</div>

	<div class="timu-grid timu-grid-2" id="timu-dashboard-grid">
		<div class="timu-card">
			<div class="timu-card-header">
				<h2 style="margin: 0;"><?php esc_html_e( 'Activity Log', 'wordpress-support-thisismyurl' ); ?></h2>
			</div>
			<div class="timu-card-body">
				<p style="margin: 0 0 var(--timu-space-md) 0; font-size: var(--timu-text-sm); color: var(--timu-text-secondary);"><?php esc_html_e( 'Recent module installs, updates, toggles, and Vault events.', 'wordpress-support-thisismyurl' ); ?></p>
				<?php if ( ! empty( $activity_logs ) ) : ?>
					<div class="timu-table-responsive">
						<table class="timu-table">
							<caption class="timu-sr-only"><?php esc_html_e( 'Recent activity entries for the Support Dashboard.', 'wordpress-support-thisismyurl' ); ?></caption>
							<thead>
							<tr>
								<th scope="col"><?php esc_html_e( 'Time', 'wordpress-support-thisismyurl' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Level', 'wordpress-support-thisismyurl' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Task', 'wordpress-support-thisismyurl' ); ?></th>
								<th scope="col"><?php esc_html_e( 'File', 'wordpress-support-thisismyurl' ); ?></th>
								<th scope="col"><?php esc_html_e( 'User', 'wordpress-support-thisismyurl' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Details', 'wordpress-support-thisismyurl' ); ?></th>
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
								$level_class = 'timu-badge-info';
								if ( 'warning' === $level ) {
									$level_class = 'timu-badge-warning';
								} elseif ( 'error' === $level ) {
									$level_class = 'timu-badge-danger';
								}
								?>
								<tr>
									<td><?php echo esc_html( $time_text ); ?></td>
									<td><span class="timu-badge <?php echo esc_attr( $level_class ); ?>"><?php echo esc_html( ucfirst( $level ) ); ?></span></td>
									<td><?php echo $task ? esc_html( $task ) : '—'; ?></td>
									<td><?php echo $file ? esc_html( $file ) : '—'; ?></td>
									<td><?php echo $user_name ? esc_html( $user_name ) : '—'; ?></td>
									<td><?php echo $reason ? esc_html( $reason ) : '—'; ?></td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					</div>
					<div style="margin-top: var(--timu-space-md); padding-top: var(--timu-space-md); border-top: 1px solid var(--timu-border-subtle);">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=timu-support-settings#timu-vault-logs' ) ); ?>" class="timu-btn timu-btn-secondary timu-btn-sm">
							<?php esc_html_e( 'View Full Log History →', 'wordpress-support-thisismyurl' ); ?>
						</a>
					</div>
				<?php else : ?>
					<p style="color: var(--timu-text-muted); font-style: italic; margin: 0;"><?php esc_html_e( 'No recent activity yet.', 'wordpress-support-thisismyurl' ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<div class="timu-card">
			<div class="timu-card-header">
				<h2 style="margin: 0;"><?php esc_html_e( 'Scheduled Tasks', 'wordpress-support-thisismyurl' ); ?></h2>
			</div>
			<div class="timu-card-body">
				<p style="margin: 0 0 var(--timu-space-md) 0; font-size: var(--timu-text-sm); color: var(--timu-text-secondary);"><?php esc_html_e( 'Upcoming suite jobs with their next run window.', 'wordpress-support-thisismyurl' ); ?></p>
				<?php if ( isset( $_GET['timu_run_now'] ) ) : ?>
					<div class="timu-alert timu-alert-success" role="status" aria-live="polite" style="margin-bottom: var(--timu-space-md);">
						<p style="margin: 0;"><?php esc_html_e( 'Task started immediately.', 'wordpress-support-thisismyurl' ); ?></p>
					</div>
				<?php endif; ?>
				<?php if ( ! empty( $schedule_snapshot ) ) : ?>
					<div class="timu-table-responsive">
						<table class="timu-table">
							<caption class="timu-sr-only"><?php esc_html_e( 'Scheduled suite tasks with next and last run times.', 'wordpress-support-thisismyurl' ); ?></caption>
							<thead>
							<tr>
								<th scope="col"><?php esc_html_e( 'Task', 'wordpress-support-thisismyurl' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Next run', 'wordpress-support-thisismyurl' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Last run', 'wordpress-support-thisismyurl' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Status', 'wordpress-support-thisismyurl' ); ?></th>
								<th scope="col"><?php esc_html_e( 'Action', 'wordpress-support-thisismyurl' ); ?></th>
							</tr>
							</thead>
							<tbody>
							<?php foreach ( $schedule_snapshot as $task_key => $task_data ) : ?>
								<?php
								$next_run = isset( $task_data['next_run'] ) ? (int) $task_data['next_run'] : 0;
								$last_run = isset( $task_data['last_run'] ) ? (int) $task_data['last_run'] : 0;
								$status   = $task_data['queue_state'] ?? __( 'Scheduled', 'wordpress-support-thisismyurl' );
								$next_txt = $next_run ? date_i18n( 'M j, Y g:i a', $next_run ) : __( 'Not scheduled', 'wordpress-support-thisismyurl' );
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
												<?php wp_nonce_field( 'timu_run_task_now', 'nonce' ); ?>
												<input type="hidden" name="action" value="timu_run_task_now" />
												<input type="hidden" name="hook" value="<?php echo esc_attr( $hook ); ?>" />
												<button type="submit" class="timu-btn timu-btn-secondary timu-btn-sm" aria-label="<?php echo esc_attr( sprintf( __( 'Run %s now', 'wordpress-support-thisismyurl' ), $task_lbl ) ); ?>"><?php esc_html_e( 'Run now', 'wordpress-support-thisismyurl' ); ?></button>
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
					<p style="color: var(--timu-text-muted); font-style: italic; margin: 0;"><?php esc_html_e( 'No scheduled tasks found.', 'wordpress-support-thisismyurl' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
</div>

	<div class="timu-card">
		<div class="timu-card-header">
			<h2 style="margin: 0;"><?php esc_html_e( 'Pending Contributor Uploads', 'wordpress-support-thisismyurl' ); ?></h2>
		</div>
		<div class="timu-card-body">
			<p style="margin: 0 0 var(--timu-space-md) 0; font-size: var(--timu-text-sm); color: var(--timu-text-secondary);"><?php esc_html_e( 'Uploads from contributors are optimized by default and held for Editor+ review.', 'wordpress-support-thisismyurl' ); ?></p>
			<?php if ( ! empty( $pending_uploads ) ) : ?>
				<div class="timu-table-responsive">
					<table class="timu-table">
						<caption class="timu-sr-only"><?php esc_html_e( 'Pending contributor uploads awaiting review.', 'wordpress-support-thisismyurl' ); ?></caption>
						<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'File', 'wordpress-support-thisismyurl' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Uploaded by', 'wordpress-support-thisismyurl' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Uploaded at', 'wordpress-support-thisismyurl' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Optimized', 'wordpress-support-thisismyurl' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Action', 'wordpress-support-thisismyurl' ); ?></th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ( $pending_uploads as $item ) : ?>
							<?php
							$uploaded = ! empty( $item['date'] ) ? date_i18n( 'M j, Y g:i a', strtotime( $item['date'] ) ) : '—';
							?>
							<tr>
								<td><?php echo esc_html( $item['file'] ?: $item['title'] ); ?></td>
								<td><?php echo esc_html( $item['user'] ?: __( 'Unknown', 'wordpress-support-thisismyurl' ) ); ?></td>
								<td><?php echo esc_html( $uploaded ); ?></td>
								<td><?php echo ! empty( $item['optimized'] ) ? '<span class="timu-badge timu-badge-success">' . esc_html__( 'Yes', 'wordpress-support-thisismyurl' ) . '</span>' : '<span class="timu-badge">' . esc_html__( 'No', 'wordpress-support-thisismyurl' ) . '</span>'; ?></td>
								<td>
									<?php if ( ! empty( $item['edit_link'] ) ) : ?>
										<a class="timu-btn timu-btn-primary timu-btn-sm" href="<?php echo esc_url( $item['edit_link'] ); ?>"><?php esc_html_e( 'Review', 'wordpress-support-thisismyurl' ); ?></a>
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
				<p style="color: var(--timu-text-muted); font-style: italic; margin: 0;"><?php esc_html_e( 'No pending contributor uploads.', 'wordpress-support-thisismyurl' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
