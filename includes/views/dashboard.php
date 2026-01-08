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
$modules            = isset( $modules ) && is_array( $modules ) ? $modules : array();
$activity_logs      = isset( $activity_logs ) && is_array( $activity_logs ) ? $activity_logs : array();
$pending_uploads    = isset( $pending_uploads ) && is_array( $pending_uploads ) ? $pending_uploads : array();
$schedule_snapshot  = isset( $schedule_snapshot ) && is_array( $schedule_snapshot ) ? $schedule_snapshot : array();
$run_now_nonce      = isset( $run_now_nonce ) ? (string) $run_now_nonce : '';
$hub_modules        = array_filter( $modules, static fn( $m ) => ( $m['type'] ?? '' ) === 'hub' );
$spoke_modules      = array_filter( $modules, static fn( $m ) => ( $m['type'] ?? '' ) === 'spoke' );

$modules_url = is_network_admin()
	? network_admin_url( 'admin.php?page=timu-core-modules' )
	: admin_url( 'admin.php?page=timu-core-modules' );
?>

<div class="wrap timu-dashboard-wrap">
	<div class="timu-dashboard-header">
		<h1><?php esc_html_e( 'Support Dashboard', 'core-support-thisismyurl' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Operational view of module activity, scheduled tasks, and pending reviews.', 'core-support-thisismyurl' ); ?></p>
		<a href="<?php echo esc_url( $modules_url ); ?>" class="button button-primary"><?php esc_html_e( 'Go to Modules', 'core-support-thisismyurl' ); ?></a>
	</div>

	<div class="timu-dashboard-stats">
		<div class="timu-stat-card" role="group" aria-label="<?php esc_attr_e( 'Total modules', 'core-support-thisismyurl' ); ?>">
			<div class="timu-stat-icon"><span class="dashicons dashicons-admin-plugins"></span></div>
			<div class="timu-stat-content">
				<div class="timu-stat-value"><?php echo esc_html( number_format_i18n( (int) ( $total_count ?? 0 ) ) ); ?></div>
				<div class="timu-stat-label"><?php esc_html_e( 'Total', 'core-support-thisismyurl' ); ?></div>
			</div>
		</div>
		<div class="timu-stat-card" role="group" aria-label="<?php esc_attr_e( 'Enabled modules', 'core-support-thisismyurl' ); ?>">
			<div class="timu-stat-icon timu-stat-enabled"><span class="dashicons dashicons-yes-alt"></span></div>
			<div class="timu-stat-content">
				<div class="timu-stat-value"><?php echo esc_html( number_format_i18n( (int) ( $enabled_count ?? 0 ) ) ); ?></div>
				<div class="timu-stat-label"><?php esc_html_e( 'Enabled', 'core-support-thisismyurl' ); ?></div>
			</div>
		</div>
		<div class="timu-stat-card" role="group" aria-label="<?php esc_attr_e( 'Available modules', 'core-support-thisismyurl' ); ?>">
			<div class="timu-stat-icon timu-stat-available"><span class="dashicons dashicons-plus-alt"></span></div>
			<div class="timu-stat-content">
				<div class="timu-stat-value"><?php echo esc_html( number_format_i18n( (int) ( $available_count ?? 0 ) ) ); ?></div>
				<div class="timu-stat-label"><?php esc_html_e( 'Available', 'core-support-thisismyurl' ); ?></div>
			</div>
		</div>
		<div class="timu-stat-card" role="group" aria-label="<?php esc_attr_e( 'Updates available', 'core-support-thisismyurl' ); ?>">
			<div class="timu-stat-icon timu-stat-update"><span class="dashicons dashicons-update"></span></div>
			<div class="timu-stat-content">
				<div class="timu-stat-value"><?php echo esc_html( number_format_i18n( (int) ( $updates_count ?? 0 ) ) ); ?></div>
				<div class="timu-stat-label"><?php esc_html_e( 'Updates', 'core-support-thisismyurl' ); ?></div>
			</div>
		</div>
		<div class="timu-stat-card" role="group" aria-label="<?php esc_attr_e( 'Hubs', 'core-support-thisismyurl' ); ?>">
			<div class="timu-stat-icon timu-stat-hub"><span class="dashicons dashicons-networking"></span></div>
			<div class="timu-stat-content">
				<div class="timu-stat-value"><?php echo esc_html( number_format_i18n( (int) ( $hubs_count ?? count( $hub_modules ) ) ) ); ?></div>
				<div class="timu-stat-label"><?php esc_html_e( 'Hubs', 'core-support-thisismyurl' ); ?></div>
			</div>
		</div>
		<div class="timu-stat-card" role="group" aria-label="<?php esc_attr_e( 'Spokes', 'core-support-thisismyurl' ); ?>">
			<div class="timu-stat-icon timu-stat-spoke"><span class="dashicons dashicons-admin-tools"></span></div>
			<div class="timu-stat-content">
				<div class="timu-stat-value"><?php echo esc_html( number_format_i18n( (int) ( $spokes_count ?? count( $spoke_modules ) ) ) ); ?></div>
				<div class="timu-stat-label"><?php esc_html_e( 'Spokes', 'core-support-thisismyurl' ); ?></div>
			</div>
		</div>
	</div>

	<div class="timu-dashboard-grid">
		<div class="timu-card">
			<h2><?php esc_html_e( 'Activity Log', 'core-support-thisismyurl' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Recent module installs, updates, toggles, and Vault events.', 'core-support-thisismyurl' ); ?></p>
			<?php if ( ! empty( $activity_logs ) ) : ?>
				<table class="widefat fixed striped">
					<thead>
					<tr>
						<th><?php esc_html_e( 'Time', 'core-support-thisismyurl' ); ?></th>
						<th><?php esc_html_e( 'Level', 'core-support-thisismyurl' ); ?></th>
						<th><?php esc_html_e( 'Task', 'core-support-thisismyurl' ); ?></th>
						<th><?php esc_html_e( 'File', 'core-support-thisismyurl' ); ?></th>
						<th><?php esc_html_e( 'User', 'core-support-thisismyurl' ); ?></th>
						<th><?php esc_html_e( 'Details', 'core-support-thisismyurl' ); ?></th>
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
								$user = get_user_by( 'id', (int) $entry['user_id'] );
								$user_name = $user && $user->exists() ? $user->display_name : '';
							}
							$reason = $entry['reason'] ?? '';
							$level  = $entry['level'] ?? 'info';
							?>
							<tr>
								<td><?php echo esc_html( $time_text ); ?></td>
								<td><?php echo esc_html( ucfirst( $level ) ); ?></td>
								<td><?php echo $task ? esc_html( $task ) : '—'; ?></td>
								<td><?php echo $file ? esc_html( $file ) : '—'; ?></td>
								<td><?php echo $user_name ? esc_html( $user_name ) : '—'; ?></td>
								<td><?php echo $reason ? esc_html( $reason ) : '—'; ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><em><?php esc_html_e( 'No recent activity yet.', 'core-support-thisismyurl' ); ?></em></p>
			<?php endif; ?>
		</div>

		<div class="timu-card">
			<h2><?php esc_html_e( 'Scheduled Tasks', 'core-support-thisismyurl' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Upcoming suite jobs with their next run window.', 'core-support-thisismyurl' ); ?></p>
			<?php if ( isset( $_GET['timu_run_now'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Task started immediately.', 'core-support-thisismyurl' ); ?></p></div>
			<?php endif; ?>
			<?php if ( ! empty( $schedule_snapshot ) ) : ?>
				<table class="widefat fixed striped">
					<thead>
					<tr>
						<th><?php esc_html_e( 'Task', 'core-support-thisismyurl' ); ?></th>
						<th><?php esc_html_e( 'Next run', 'core-support-thisismyurl' ); ?></th>
						<th><?php esc_html_e( 'Last run', 'core-support-thisismyurl' ); ?></th>
						<th><?php esc_html_e( 'Status', 'core-support-thisismyurl' ); ?></th>
						<th><?php esc_html_e( 'Action', 'core-support-thisismyurl' ); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php foreach ( $schedule_snapshot as $task_key => $task_data ) : ?>
							<?php
							$next_run = isset( $task_data['next_run'] ) ? (int) $task_data['next_run'] : 0;
							$last_run = isset( $task_data['last_run'] ) ? (int) $task_data['last_run'] : 0;
							$status   = $task_data['queue_state'] ?? __( 'Scheduled', 'core-support-thisismyurl' );
							$next_txt = $next_run ? date_i18n( 'M j, Y g:i a', $next_run ) : __( 'Not scheduled', 'core-support-thisismyurl' );
							$last_txt = $last_run ? date_i18n( 'M j, Y g:i a', $last_run ) : '—';
							$hook     = $task_data['hook'] ?? '';
							?>
							<tr>
								<td><?php echo esc_html( $task_data['label'] ?? ucfirst( $task_key ) ); ?></td>
								<td><?php echo esc_html( $next_txt ); ?></td>
								<td><?php echo esc_html( $last_txt ); ?></td>
								<td><?php echo esc_html( ucfirst( $status ) ); ?></td>
								<td>
									<?php if ( ! empty( $hook ) ) : ?>
										<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display: inline;">
											<?php wp_nonce_field( 'timu_run_task_now', 'nonce' ); ?>
											<input type="hidden" name="action" value="timu_run_task_now" />
											<input type="hidden" name="hook" value="<?php echo esc_attr( $hook ); ?>" />
											<button type="submit" class="button button-small"><?php esc_html_e( 'Run now', 'core-support-thisismyurl' ); ?></button>
										</form>
									<?php else : ?>
										—
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><em><?php esc_html_e( 'No scheduled tasks found.', 'core-support-thisismyurl' ); ?></em></p>
			<?php endif; ?>
		</div>
	</div>

	<div class="timu-card">
		<h2><?php esc_html_e( 'Pending Contributor Uploads', 'core-support-thisismyurl' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Uploads from contributors are optimized by default and held for Editor+ review.', 'core-support-thisismyurl' ); ?></p>
		<?php if ( ! empty( $pending_uploads ) ) : ?>
			<table class="widefat fixed striped">
				<thead>
				<tr>
					<th><?php esc_html_e( 'File', 'core-support-thisismyurl' ); ?></th>
					<th><?php esc_html_e( 'Uploaded by', 'core-support-thisismyurl' ); ?></th>
					<th><?php esc_html_e( 'Uploaded at', 'core-support-thisismyurl' ); ?></th>
					<th><?php esc_html_e( 'Optimized', 'core-support-thisismyurl' ); ?></th>
					<th><?php esc_html_e( 'Action', 'core-support-thisismyurl' ); ?></th>
				</tr>
				</thead>
				<tbody>
					<?php foreach ( $pending_uploads as $item ) : ?>
						<?php
						$uploaded = ! empty( $item['date'] ) ? date_i18n( 'M j, Y g:i a', strtotime( $item['date'] ) ) : '—';
						?>
						<tr>
							<td><?php echo esc_html( $item['file'] ?: $item['title'] ); ?></td>
							<td><?php echo esc_html( $item['user'] ?: __( 'Unknown', 'core-support-thisismyurl' ) ); ?></td>
							<td><?php echo esc_html( $uploaded ); ?></td>
							<td><?php echo ! empty( $item['optimized'] ) ? esc_html__( 'Yes', 'core-support-thisismyurl' ) : esc_html__( 'No', 'core-support-thisismyurl' ); ?></td>
							<td>
								<?php if ( ! empty( $item['edit_link'] ) ) : ?>
									<a class="button" href="<?php echo esc_url( $item['edit_link'] ); ?>"><?php esc_html_e( 'Review', 'core-support-thisismyurl' ); ?></a>
								<?php else : ?>
									—
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<p><em><?php esc_html_e( 'No pending contributor uploads.', 'core-support-thisismyurl' ); ?></em></p>
		<?php endif; ?>
	</div>
</div>
