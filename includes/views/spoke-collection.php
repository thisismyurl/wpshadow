<?php
/**
 * Spoke Collection Gallery View
 *
 * Displays the gamified collection of format-specific spoke plugins
 * with milestone tracking and progression mechanics.
 *
 * @package WPSHADOW_wpshadow_THISISMYURL
 * @since 1.2601.73002
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the Spoke Collection page.
 *
 * @return void
 */
function wpshadow_render_spoke_collection(): void {
	if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wpshadow' ) );
	}

	// Get collection data.
	$spokes     = WPSHADOW_Spoke_Collection::get_all_spokes();
	$stats      = WPSHADOW_Spoke_Collection::get_collection_stats();
	$milestones = WPSHADOW_Spoke_Collection::get_unlocked_milestones();

	// Check for new milestone notifications.
	$new_milestones = get_transient( 'wpshadow_new_milestones' );
	if ( $new_milestones && is_array( $new_milestones ) ) {
		delete_transient( 'wpshadow_new_milestones' );
	}

	?>
	<div class="wrap wps-spoke-collection">
		<h1><?php esc_html_e( 'Spoke Collection', 'plugin-wpshadow' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Install and activate format-specific plugins to expand your media capabilities. Complete milestones to unlock exclusive rewards and achievements.', 'plugin-wpshadow' ); ?>
		</p>

		<!-- Collection Progress -->
		<div class="wps-collection-stats">
			<div class="wps-stat-card">
				<span class="wps-stat-icon dashicons dashicons-portfolio"></span>
				<div class="wps-stat-content">
					<span class="wps-stat-value"><?php echo esc_html( $stats['installed'] . '/' . $stats['total_spokes'] ); ?></span>
					<span class="wps-stat-label"><?php esc_html_e( 'Spokes Installed', 'plugin-wpshadow' ); ?></span>
				</div>
			</div>

			<div class="wps-stat-card">
				<span class="wps-stat-icon dashicons dashicons-yes-alt"></span>
				<div class="wps-stat-content">
					<span class="wps-stat-value"><?php echo esc_html( $stats['active'] ); ?></span>
					<span class="wps-stat-label"><?php esc_html_e( 'Active Spokes', 'plugin-wpshadow' ); ?></span>
				</div>
			</div>

			<div class="wps-stat-card">
				<span class="wps-stat-icon dashicons dashicons-images-alt2"></span>
				<div class="wps-stat-content">
					<span class="wps-stat-value"><?php echo esc_html( number_format_i18n( $stats['total_files'] ) ); ?></span>
					<span class="wps-stat-label"><?php esc_html_e( 'Files Converted', 'plugin-wpshadow' ); ?></span>
				</div>
			</div>

			<div class="wps-stat-card">
				<span class="wps-stat-icon dashicons dashicons-archive"></span>
				<div class="wps-stat-content">
					<span class="wps-stat-value"><?php echo esc_html( size_format( $stats['total_saved'], 2 ) ); ?></span>
					<span class="wps-stat-label"><?php esc_html_e( 'Space Saved', 'plugin-wpshadow' ); ?></span>
				</div>
			</div>

			<div class="wps-stat-card wps-progress-card">
				<div class="wps-progress-circle" data-progress="<?php echo esc_attr( $stats['progress'] ); ?>">
					<svg class="wps-progress-svg" width="80" height="80">
						<circle class="wps-progress-bg" cx="40" cy="40" r="35" />
						<circle class="wps-progress-fill" cx="40" cy="40" r="35" 
							style="stroke-dasharray: <?php echo esc_attr( 2 * M_PI * 35 ); ?>; 
									stroke-dashoffset: <?php echo esc_attr( 2 * M_PI * 35 * ( 1 - $stats['progress'] / 100 ) ); ?>;" />
						<text x="40" y="45" class="wps-progress-text"><?php echo esc_html( $stats['progress'] . '%' ); ?></text>
					</svg>
				</div>
				<span class="wps-stat-label"><?php esc_html_e( 'Collection Complete', 'plugin-wpshadow' ); ?></span>
			</div>
		</div>

		<!-- Spoke Gallery Grid -->
		<div class="wps-spoke-gallery">
			<?php foreach ( $spokes as $spoke_id => $spoke ) : ?>
				<div class="wps-spoke-card wps-spoke-<?php echo esc_attr( $spoke['status'] ); ?>" 
					data-spoke="<?php echo esc_attr( $spoke_id ); ?>">
					
					<!-- Spoke Icon -->
					<div class="wps-spoke-icon-wrapper">
						<span class="wps-spoke-icon dashicons <?php echo esc_attr( $spoke['icon'] ); ?>"></span>
						
						<!-- Status Badge -->
						<span class="wps-spoke-badge wps-badge-<?php echo esc_attr( $spoke['status'] ); ?>">
							<?php
							switch ( $spoke['status'] ) {
								case 'locked':
									echo '<span class="dashicons dashicons-lock"></span>';
									break;
								case 'unlocked':
									echo '<span class="dashicons dashicons-unlock"></span>';
									break;
								case 'active':
									echo '<span class="dashicons dashicons-yes"></span>';
									break;
								case 'mastered':
									echo '<span class="dashicons dashicons-star-filled"></span>';
									break;
							}
							?>
						</span>
					</div>

					<!-- Spoke Title -->
					<h3 class="wps-spoke-title"><?php echo esc_html( $spoke['name'] ); ?></h3>

					<!-- Spoke Description -->
					<p class="wps-spoke-description"><?php echo esc_html( $spoke['description'] ); ?></p>

					<!-- Spoke Benefits -->
					<div class="wps-spoke-benefits">
						<span class="dashicons dashicons-yes-alt"></span>
						<span><?php echo esc_html( $spoke['benefits'] ); ?></span>
					</div>

					<!-- Browser Support -->
					<div class="wps-spoke-support">
						<span class="wps-support-label"><?php esc_html_e( 'Browser Support:', 'plugin-wpshadow' ); ?></span>
						<div class="wps-support-bar">
							<div class="wps-support-fill" style="width: <?php echo esc_attr( $spoke['browser_support'] . '%' ); ?>"></div>
							<span class="wps-support-percent"><?php echo esc_html( $spoke['browser_support'] . '%' ); ?></span>
						</div>
					</div>

					<!-- Metrics (Active/Mastered only) -->
					<?php if ( in_array( $spoke['status'], array( 'active', 'mastered' ), true ) ) : ?>
						<div class="wps-spoke-metrics">
							<div class="wps-metric">
								<span class="dashicons dashicons-images-alt2"></span>
								<span><?php echo esc_html( number_format_i18n( $spoke['files_processed'] ) . ' ' . __( 'files', 'plugin-wpshadow' ) ); ?></span>
							</div>
							<div class="wps-metric">
								<span class="dashicons dashicons-archive"></span>
								<span><?php echo esc_html( size_format( $spoke['space_saved'], 2 ) . ' ' . __( 'saved', 'plugin-wpshadow' ) ); ?></span>
							</div>
						</div>
					<?php endif; ?>

					<!-- Action Buttons -->
					<div class="wps-spoke-actions">
						<?php if ( 'locked' === $spoke['status'] ) : ?>
							<button class="button button-primary wps-install-spoke" data-spoke="<?php echo esc_attr( $spoke_id ); ?>">
								<span class="dashicons dashicons-download"></span>
								<?php esc_html_e( 'Install This Spoke', 'plugin-wpshadow' ); ?>
							</button>
						<?php elseif ( 'unlocked' === $spoke['status'] ) : ?>
							<button class="button button-primary wps-activate-spoke" data-spoke="<?php echo esc_attr( $spoke_id ); ?>">
								<span class="dashicons dashicons-yes"></span>
								<?php esc_html_e( 'Activate', 'plugin-wpshadow' ); ?>
							</button>
						<?php elseif ( in_array( $spoke['status'], array( 'active', 'mastered' ), true ) ) : ?>
							<button class="button wps-deactivate-spoke" data-spoke="<?php echo esc_attr( $spoke_id ); ?>">
								<span class="dashicons dashicons-no"></span>
								<?php esc_html_e( 'Deactivate', 'plugin-wpshadow' ); ?>
							</button>
						<?php endif; ?>
					</div>

					<!-- Status Text -->
					<div class="wps-spoke-status-text">
						<?php
						switch ( $spoke['status'] ) {
							case 'locked':
								esc_html_e( 'Not Installed', 'plugin-wpshadow' );
								break;
							case 'unlocked':
								esc_html_e( 'Ready to Activate', 'plugin-wpshadow' );
								break;
							case 'active':
								esc_html_e( 'Active & Processing', 'plugin-wpshadow' );
								break;
							case 'mastered':
								esc_html_e( 'Mastered!', 'plugin-wpshadow' );
								break;
						}
						?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Milestone Achievement Modal (hidden by default) -->
		<div id="wps-milestone-modal" class="wps-modal" style="display: none;">
			<div class="wps-modal-overlay"></div>
			<div class="wps-modal-content">
				<div class="wps-modal-header">
					<span class="wps-achievement-icon dashicons dashicons-star-filled"></span>
					<h2><?php esc_html_e( 'Achievement Unlocked!', 'plugin-wpshadow' ); ?></h2>
				</div>
				<div class="wps-modal-body">
					<h3 id="wps-milestone-name"></h3>
					<p id="wps-milestone-description"></p>
					<div class="wps-milestone-reward">
						<span class="dashicons dashicons-awards"></span>
						<span id="wps-milestone-reward"></span>
					</div>
				</div>
				<div class="wps-modal-footer">
					<button class="button button-primary wps-close-modal"><?php esc_html_e( 'Awesome!', 'plugin-wpshadow' ); ?></button>
				</div>
			</div>
		</div>

		<!-- Confirmation Modal (hidden by default) -->
		<div id="wps-confirm-modal" class="wps-modal" style="display: none;">
			<div class="wps-modal-overlay"></div>
			<div class="wps-modal-content">
				<div class="wps-modal-header">
					<span class="dashicons dashicons-warning" style="color: #f59e0b; font-size: 48px;"></span>
					<h2 id="wps-confirm-title"><?php esc_html_e( 'Confirm Action', 'plugin-wpshadow' ); ?></h2>
				</div>
				<div class="wps-modal-body">
					<p id="wps-confirm-message"></p>
				</div>
				<div class="wps-modal-footer" style="display: flex; gap: 10px; justify-content: center;">
					<button class="button wps-confirm-cancel"><?php esc_html_e( 'Cancel', 'plugin-wpshadow' ); ?></button>
					<button class="button button-primary wps-confirm-ok"><?php esc_html_e( 'Confirm', 'plugin-wpshadow' ); ?></button>
				</div>
			</div>
		</div>
	</div>

	<?php
	// Show milestone popup if there are new achievements.
	if ( $new_milestones && is_array( $new_milestones ) ) :
		$first_milestone  = reset( $new_milestones );
		$milestone_name   = $first_milestone['name'] ?? '';
		$milestone_desc   = $first_milestone['description'] ?? '';
		$milestone_reward = $first_milestone['reward'] ?? '';
		?>
		<script>
		jQuery(document).ready(function($) {
			// Show achievement modal on page load.
			$('#wps-milestone-name').text(<?php echo wp_json_encode( $milestone_name ); ?>);
			$('#wps-milestone-description').text(<?php echo wp_json_encode( $milestone_desc ); ?>);
			$('#wps-milestone-reward').text(<?php echo wp_json_encode( $milestone_reward ); ?>);
			$('#wps-milestone-modal').fadeIn(300);
			
			// Add celebration animation.
			$('.wps-modal-content').addClass('wps-animate-bounce');
		});
		</script>
	<?php endif; ?>
	<?php
}
