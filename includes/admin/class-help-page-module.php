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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get help catalog.
 *
 * @return array Help items.
 */
function wpshadow_get_help_catalog() {
	return array(
		array(
			'title'       => __( 'Getting Started with WPShadow', 'wpshadow' ),
			'description' => __( 'Learn the basics of how WPShadow helps you maintain a healthy WordPress site.', 'wpshadow' ),
			'icon'        => 'dashicons-info',
			'url'         => 'https://wpshadow.com/kb/getting-started',
			'video'       => 'https://wpshadow.com/training/getting-started',
		),
		array(
			'title'       => __( 'Understanding Diagnostics', 'wpshadow' ),
			'description' => __( 'Discover what each diagnostic check does and what issues it helps identify.', 'wpshadow' ),
			'icon'        => 'dashicons-stethoscope',
			'url'         => 'https://wpshadow.com/kb/diagnostics',
			'video'       => 'https://wpshadow.com/training/diagnostics',
		),
		array(
			'title'       => __( 'Applying Treatments', 'wpshadow' ),
			'description' => __( 'Learn how to safely apply fixes to your site with one-click treatments and undo support.', 'wpshadow' ),
			'icon'        => 'dashicons-healing',
			'url'         => 'https://wpshadow.com/kb/treatments',
			'video'       => 'https://wpshadow.com/training/treatments',
		),
		array(
			'title'       => __( 'Workflows & Automation', 'wpshadow' ),
			'description' => __( 'Set up automated workflows to keep your site healthy without manual intervention.', 'wpshadow' ),
			'icon'        => 'dashicons-schedule',
			'url'         => 'https://wpshadow.com/kb/workflows',
			'video'       => 'https://wpshadow.com/training/workflows',
		),
		array(
			'title'       => __( 'Monitoring & Alerts', 'wpshadow' ),
			'description' => __( 'Stay informed with real-time monitoring and custom alert notifications.', 'wpshadow' ),
			'icon'        => 'dashicons-bell',
			'url'         => 'https://wpshadow.com/kb/monitoring',
			'video'       => 'https://wpshadow.com/training/monitoring',
		),
		array(
			'title'       => __( 'Privacy & Security', 'wpshadow' ),
			'description' => __( 'Learn about our privacy-first approach and how your data is protected.', 'wpshadow' ),
			'icon'        => 'dashicons-lock',
			'url'         => 'https://wpshadow.com/kb/privacy',
			'video'       => 'https://wpshadow.com/training/privacy',
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
	<div class="wps-page-container">
		<!-- Page Header -->
		<div class="wps-page-header">
			<h1 class="wps-page-title">
				<span class="dashicons dashicons-editor-help" style="color: var(--wps-primary);"></span>
				<?php esc_html_e( 'WPShadow Help & Learning', 'wpshadow' ); ?>
			</h1>
			<p class="wps-page-subtitle">
				<?php esc_html_e( 'Explore tutorials, guides, and resources to get the most out of WPShadow.', 'wpshadow' ); ?>
			</p>
		</div>

		<!-- Help Resources Grid -->
		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
			<?php foreach ( $catalog as $item ) : ?>
				<div class="wps-card">
					<div class="wps-card-header" style="padding-bottom: 12px; border-bottom: 1px solid #eee;">
						<div style="display: flex; align-items: flex-start; gap: 12px;">
							<span class="dashicons <?php echo esc_attr( $item['icon'] ); ?>" 
								  style="font-size: 32px; width: 32px; height: 32px; color: #0073aa;"></span>
							<div>
								<h3 class="wps-card-title" style="margin: 0 0 4px; font-size: 16px;">
									<?php echo esc_html( $item['title'] ); ?>
								</h3>
								<p class="wps-card-description" style="margin: 0; font-size: 13px;">
									<?php echo esc_html( $item['description'] ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="wps-card-body" style="padding: 12px 0;">
						<div style="display: flex; gap: 8px; flex-wrap: wrap;">
							<?php if ( ! empty( $item['url'] ) ) : ?>
								<a href="<?php echo esc_url( $item['url'] ); ?>" 
								   target="_blank" rel="noopener noreferrer"
								   class="wps-btn wps-btn-secondary"
								   style="text-decoration: none; padding: 6px 12px; font-size: 13px;">
									<span class="dashicons dashicons-external" style="margin-right: 4px; font-size: 14px;"></span>
									<?php esc_html_e( 'Read Article', 'wpshadow' ); ?>
								</a>
							<?php endif; ?>
							<?php if ( ! empty( $item['video'] ) ) : ?>
								<a href="<?php echo esc_url( $item['video'] ); ?>" 
								   target="_blank" rel="noopener noreferrer"
								   class="wps-btn wps-btn-secondary"
								   style="text-decoration: none; padding: 6px 12px; font-size: 13px;">
									<span class="dashicons dashicons-video-alt2" style="margin-right: 4px; font-size: 14px;"></span>
									<?php esc_html_e( 'Watch Video', 'wpshadow' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Contact Support & Resources -->
		<div class="wps-card" style="margin-top: 32px;">
			<div class="wps-card-header">
				<div>
					<h2 class="wps-card-title" style="margin: 0 0 8px;">
						<span class="dashicons dashicons-sos"></span>
						<?php esc_html_e( 'Need More Help?', 'wpshadow' ); ?>
					</h2>
					<p class="wps-card-description" style="margin: 0;">
						<?php esc_html_e( 'Access our knowledge base, training videos, and community support.', 'wpshadow' ); ?>
					</p>
				</div>
			</div>
			<div class="wps-card-body" style="display: flex; gap: 12px; flex-wrap: wrap;">
				<a href="https://github.com/thisismyurl/wpshadow/issues" target="_blank" rel="noopener noreferrer" class="wps-btn wps-btn-primary">
					<span class="dashicons dashicons-admin-comments"></span>
					<?php esc_html_e( 'Contact Support', 'wpshadow' ); ?>
				</a>
				<a href="https://wpshadow.com/training" target="_blank" rel="noopener noreferrer" class="wps-btn wps-btn-secondary">
					<span class="dashicons dashicons-video-alt2"></span>
					<?php esc_html_e( 'Online Training', 'wpshadow' ); ?>
				</a>
				<a href="https://wpshadow.com/kb" target="_blank" rel="noopener noreferrer" class="wps-btn wps-btn-secondary">
					<span class="dashicons dashicons-book"></span>
					<?php esc_html_e( 'Knowledge Base', 'wpshadow' ); ?>
				</a>
			</div>
		</div>
	</div>
	<?php
}
