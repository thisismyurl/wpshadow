<?php
/**
 * Help Page Module for WPShadow
 *
 * Help page rendering.
 *
 * @package WPShadow
 * @subpackage Admin
 */

declare(strict_types=1);

use WPShadow\Core\UTM_Link_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get help catalog.
 *
 * @return array Help items.
 */
function wpshadow_get_help_catalog() {
	// Initialize UTM Link Manager for tracking
	$utm = UTM_Link_Manager::class;
	
	return array(
		array(
			'title'       => __( 'Getting Started with WPShadow', 'wpshadow' ),
			'description' => __( 'Learn the basics of how WPShadow helps you maintain a healthy WordPress site.', 'wpshadow' ),
			'icon'        => 'dashicons-info',
			'url'         => $utm::kb_link( 'getting-started', 'help_page' ),
			'video'       => 'https://wpshadow.com/academy/getting-started?utm_source=wpshadow&utm_medium=plugin&utm_campaign=help_page&utm_content=video_getting_started',
		),
		array(
			'title'       => __( 'Understanding Diagnostics', 'wpshadow' ),
			'description' => __( 'Discover what each diagnostic check does and what issues it helps identify.', 'wpshadow' ),
			'icon'        => 'dashicons-search',
			'url'         => $utm::kb_link( 'diagnostics', 'help_page' ),
			'video'       => 'https://wpshadow.com/academy/diagnostics?utm_source=wpshadow&utm_medium=plugin&utm_campaign=help_page&utm_content=video_diagnostics',
		),
		array(
			'title'       => __( 'Applying Treatments', 'wpshadow' ),
			'description' => __( 'Learn how to safely apply fixes to your site with one-click treatments and undo support.', 'wpshadow' ),
			'icon'        => 'dashicons-admin-tools',
			'url'         => $utm::kb_link( 'treatments', 'help_page' ),
			'video'       => 'https://wpshadow.com/academy/treatments?utm_source=wpshadow&utm_medium=plugin&utm_campaign=help_page&utm_content=video_treatments',
		),
		array(
			'title'       => __( 'Workflows & Automation', 'wpshadow' ),
			'description' => __( 'Set up automated workflows to keep your site healthy without manual intervention.', 'wpshadow' ),
			'icon'        => 'dashicons-schedule',
			'url'         => $utm::kb_link( 'workflows', 'help_page' ),
			'video'       => 'https://wpshadow.com/academy/workflows?utm_source=wpshadow&utm_medium=plugin&utm_campaign=help_page&utm_content=video_workflows',
		),
		array(
			'title'       => __( 'Monitoring & Alerts', 'wpshadow' ),
			'description' => __( 'Stay informed with real-time monitoring and custom alert notifications.', 'wpshadow' ),
			'icon'        => 'dashicons-bell',
			'url'         => $utm::kb_link( 'monitoring', 'help_page' ),
			'video'       => 'https://wpshadow.com/academy/monitoring?utm_source=wpshadow&utm_medium=plugin&utm_campaign=help_page&utm_content=video_monitoring',
		),
		array(
			'title'       => __( 'Privacy & Security', 'wpshadow' ),
			'description' => __( 'Learn about our privacy-first approach and how your data is protected.', 'wpshadow' ),
			'icon'        => 'dashicons-lock',
			'url'         => $utm::kb_link( 'privacy', 'help_page' ),
			'video'       => 'https://wpshadow.com/academy/privacy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=help_page&utm_content=video_privacy',
		),
	);
}

/**
 * Render help page.
 *
 * @return void
 */
function wpshadow_render_help() {
	if ( ! current_user_can( 'read' ) ) {
		wp_die( 'Insufficient permissions.' );
	}

	$catalog = wpshadow_get_help_catalog();

	?>
	<div class="wrap wps-page-container">
		<!-- Page Header -->
		<?php wpshadow_render_page_header(
			__( 'WPShadow Help & Learning', 'wpshadow' ),
			__( 'Explore tutorials, guides, and resources to get the most out of WPShadow.', 'wpshadow' ),
			'dashicons-editor-help'
		); ?>

		<!-- Help Resources Grid -->
		<div class="wps-grid wps-grid-auto-320">
			<?php foreach ( $catalog as $item ) : ?>
				<div class="wps-card">
					<div class="wps-card-header wps-pb-3 wps-border-bottom">
						<div class="wps-flex wps-gap-3 wps-items-start">
							<span class="dashicons <?php echo esc_attr( $item['icon'] ); ?> wps-text-3xl wps-text-primary"></span>
							<div>
								<h3 class="wps-card-title wps-m-0">
								<?php if ( ! empty( $item['url'] ) ) : ?>
									<a href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: none;">
										<?php echo esc_html( $item['title'] ); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html( $item['title'] ); ?>
								<?php endif; ?>
								</h3>
								<p class="wps-card-description wps-m-0">
									<?php echo esc_html( $item['description'] ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="wps-card-body">
						<div class="wps-flex wps-gap-2">
							<?php if ( ! empty( $item['url'] ) ) : ?>
								<a href="<?php echo esc_url( $item['url'] ); ?>" 
									target="_blank" rel="noopener noreferrer"
									class="wps-btn wps-btn--secondary">
									<span class="dashicons dashicons-external"></span>
									<?php esc_html_e( 'Read Article', 'wpshadow' ); ?>
								</a>
							<?php endif; ?>
							<?php if ( ! empty( $item['video'] ) ) : ?>
								<a href="<?php echo esc_url( $item['video'] ); ?>" 
									target="_blank" rel="noopener noreferrer"
									class="wps-btn wps-btn--secondary">
									<span class="dashicons dashicons-video-alt2"></span>
									<?php esc_html_e( 'Watch Video', 'wpshadow' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Contact Support & Resources -->
		<div class="wps-card wps-mt-8">
			<div class="wps-card-header">
				<div>
					<h2 class="wps-card-title wps-m-0">
						<span class="dashicons dashicons-sos"></span>
						<?php esc_html_e( 'Need More Help?', 'wpshadow' ); ?>
					</h2>
					<p class="wps-card-description wps-m-0">
						<?php esc_html_e( 'Access our knowledge base, training videos, and community support.', 'wpshadow' ); ?>
					</p>
				</div>
			</div>
			<div class="wps-card-body wps-flex wps-gap-3">
				<a href="https://github.com/thisismyurl/wpshadow/issues?utm_source=wpshadow&utm_medium=plugin&utm_campaign=help_page&utm_content=contact_support" target="_blank" rel="noopener noreferrer" class="wps-btn wps-btn--primary">
					<span class="dashicons dashicons-admin-comments"></span>
					<?php esc_html_e( 'Contact Support', 'wpshadow' ); ?>
				</a>
				<a href="https://wpshadow.com/academy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=help_page&utm_content=academy_cta" target="_blank" rel="noopener noreferrer" class="wps-btn wps-btn--secondary">
					<span class="dashicons dashicons-video-alt2"></span>
					<?php esc_html_e( 'Online Training', 'wpshadow' ); ?>
				</a>
				<a href="<?php echo esc_url( UTM_Link_Manager::kb_link( '', 'help_page' ) ); ?>" target="_blank" rel="noopener noreferrer" class="wps-btn wps-btn--secondary">
					<span class="dashicons dashicons-book"></span>
					<?php esc_html_e( 'Knowledge Base', 'wpshadow' ); ?>
				</a>
			</div>
		</div>

		<!-- Recent Activity -->
		<?php
		$recent_activities = \WPShadow\Core\Activity_Logger::get_activities( array(), 10, 0 );
		if ( ! empty( $recent_activities['activities'] ) ) :
		?>
		<div class="wps-card wps-mt-8">
			<div class="wps-card-header">
				<div class="wps-flex wps-items-center wps-justify-between">
					<div>
						<h2 class="wps-card-title wps-m-0">
							<span class="dashicons dashicons-clock"></span>
							<?php esc_html_e( 'Recent Activity', 'wpshadow' ); ?>
						</h2>
						<p class="wps-card-description wps-m-0">
							<?php esc_html_e( 'Your latest WPShadow actions and events.', 'wpshadow' ); ?>
						</p>
					</div>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-reports&tab=activity' ) ); ?>" class="wps-btn wps-btn--secondary wps-btn--small">
						<?php esc_html_e( 'View All', 'wpshadow' ); ?>
					</a>
				</div>
			</div>
			<div class="wps-card-body">
				<div class="wps-activity-list">
					<?php foreach ( $recent_activities['activities'] as $activity ) : ?>
						<div class="wps-activity-item wps-flex wps-gap-3 wps-py-3 wps-border-bottom">
							<div class="wps-activity-icon wps-flex-shrink-0">
								<?php
								// Icon based on action type
								$icon_class = 'dashicons-admin-generic';
								if ( strpos( $activity['action'], 'diagnostic' ) !== false ) {
									$icon_class = 'dashicons-search';
								} elseif ( strpos( $activity['action'], 'treatment' ) !== false ) {
									$icon_class = 'dashicons-admin-tools';
								} elseif ( strpos( $activity['action'], 'workflow' ) !== false ) {
									$icon_class = 'dashicons-update';
								} elseif ( strpos( $activity['action'], 'scan' ) !== false ) {
									$icon_class = 'dashicons-shield';
								}
								?>
								<span class="dashicons <?php echo esc_attr( $icon_class ); ?> wps-text-2xl wps-text-muted"></span>
							</div>
							<div class="wps-flex-1">
								<div class="wps-font-medium wps-text-sm">
									<?php echo esc_html( $activity['details'] ); ?>
								</div>
								<?php if ( ! empty( $activity['category'] ) ) : ?>
									<div class="wps-text-xs wps-text-muted wps-mt-1">
										<span class="wps-badge wps-badge--<?php echo esc_attr( $activity['category'] ); ?>">
											<?php echo esc_html( ucfirst( $activity['category'] ) ); ?>
										</span>
									</div>
								<?php endif; ?>
							</div>
							<div class="wps-activity-meta wps-text-xs wps-text-muted wps-text-right wps-flex-shrink-0">
								<div><?php echo esc_html( $activity['user_name'] ); ?></div>
								<div title="<?php echo esc_attr( $activity['date'] ); ?>">
									<?php echo esc_html( human_time_diff( $activity['timestamp'], current_time( 'timestamp' ) ) ); ?>
									<?php esc_html_e( 'ago', 'wpshadow' ); ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>
	<?php
}
