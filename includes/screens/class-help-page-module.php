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
			'video'       => 'https://wpshadow.com/academy/getting-started',
		),
		array(
			'title'       => __( 'Understanding Diagnostics', 'wpshadow' ),
			'description' => __( 'Discover what each diagnostic check does and what issues it helps identify.', 'wpshadow' ),
			'icon'        => 'dashicons-search',
			'url'         => 'https://wpshadow.com/kb/diagnostics',
			'video'       => 'https://wpshadow.com/academy/diagnostics',
		),
		array(
			'title'       => __( 'Applying Treatments', 'wpshadow' ),
			'description' => __( 'Learn how to safely apply fixes to your site with one-click treatments and undo support.', 'wpshadow' ),
			'icon'        => 'dashicons-admin-tools',
			'url'         => 'https://wpshadow.com/kb/treatments',
			'video'       => 'https://wpshadow.com/academy/treatments',
		),
		array(
			'title'       => __( 'Workflows & Automation', 'wpshadow' ),
			'description' => __( 'Set up automated workflows to keep your site healthy without manual intervention.', 'wpshadow' ),
			'icon'        => 'dashicons-schedule',
			'url'         => 'https://wpshadow.com/kb/workflows',
			'video'       => 'https://wpshadow.com/academy/workflows',
		),
		array(
			'title'       => __( 'Monitoring & Alerts', 'wpshadow' ),
			'description' => __( 'Stay informed with real-time monitoring and custom alert notifications.', 'wpshadow' ),
			'icon'        => 'dashicons-bell',
			'url'         => 'https://wpshadow.com/kb/monitoring',
			'video'       => 'https://wpshadow.com/academy/monitoring',
		),
		array(
			'title'       => __( 'Privacy & Security', 'wpshadow' ),
			'description' => __( 'Learn about our privacy-first approach and how your data is protected.', 'wpshadow' ),
			'icon'        => 'dashicons-lock',
			'url'         => 'https://wpshadow.com/kb/privacy',
			'video'       => 'https://wpshadow.com/academy/privacy',
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
				<a href="https://github.com/thisismyurl/wpshadow/issues" target="_blank" rel="noopener noreferrer" class="wps-btn wps-btn--primary">
					<span class="dashicons dashicons-admin-comments"></span>
					<?php esc_html_e( 'Contact Support', 'wpshadow' ); ?>
				</a>
				<a href="https://wpshadow.com/academy" target="_blank" rel="noopener noreferrer" class="wps-btn wps-btn--secondary">
					<span class="dashicons dashicons-video-alt2"></span>
					<?php esc_html_e( 'Online Training', 'wpshadow' ); ?>
				</a>
				<a href="https://wpshadow.com/kb" target="_blank" rel="noopener noreferrer" class="wps-btn wps-btn--secondary">
					<span class="dashicons dashicons-book"></span>
					<?php esc_html_e( 'Knowledge Base', 'wpshadow' ); ?>
				</a>
			</div>
		</div>
	</div>
	<?php
}
