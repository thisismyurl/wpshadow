<?php
/**
 * Tools Page Module for WPShadow
 *
 * Handles tools page rendering and broken links scanning.
 *
 * @package WPShadow
 * @subpackage Admin
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load tooltip manager functions (needed by tips-coach tool)
require_once WPSHADOW_PATH . 'includes/dashboard/widgets/class-tooltip-manager.php';

/**
 * Get tools catalog.
 *
 * @return array Tools.
 */
function wpshadow_get_tools_catalog() {
	return array(
		array(
			'title'   => __( 'Quick Scan', 'wpshadow' ),
			'desc'    => __( 'Run a fast, lightweight scan of your site for common issues and security concerns.', 'wpshadow' ),
			'tool'    => 'quick-scan',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Deep Scan', 'wpshadow' ),
			'desc'    => __( 'Run a comprehensive scan that checks database health, performance, and advanced compatibility issues.', 'wpshadow' ),
			'tool'    => 'deep-scan',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Accessibility Audit', 'wpshadow' ),
			'desc'    => __( 'Scan your site for accessibility issues and WCAG compliance.', 'wpshadow' ),
			'tool'    => 'a11y-audit',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Broken Link Checker', 'wpshadow' ),
			'desc'    => __( 'Find and fix broken links across your site.', 'wpshadow' ),
			'tool'    => 'broken-links',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Color Contrast Checker', 'wpshadow' ),
			'desc'    => __( 'Check color combinations for accessibility compliance.', 'wpshadow' ),
			'tool'    => 'color-contrast',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Dark Mode', 'wpshadow' ),
			'desc'    => __( 'Enable dark mode for the WordPress admin interface.', 'wpshadow' ),
			'tool'    => 'dark-mode',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Email Test & Configuration', 'wpshadow' ),
			'desc'    => __( 'Test email delivery and configure From Name/Email to ensure emails are sent properly.', 'wpshadow' ),
			'tool'    => 'email-test',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Cache Management', 'wpshadow' ),
			'desc'    => __( 'Manage site caching and clear cache when needed.', 'wpshadow' ),
			'tool'    => 'simple-cache',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Magic Link Support', 'wpshadow' ),
			'desc'    => __( 'Generate secure one-time access links for support staff.', 'wpshadow' ),
			'tool'    => 'magic-link-support',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Mobile Friendliness', 'wpshadow' ),
			'desc'    => __( 'Test your site for mobile compatibility and responsive design.', 'wpshadow' ),
			'tool'    => 'mobile-friendliness',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Tips & Guidance', 'wpshadow' ),
			'desc'    => __( 'Friendly tooltips across wp-admin with opt-out controls and helpful guidance for beginners.', 'wpshadow' ),
			'tool'    => 'tips-coach',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Kanban Report', 'wpshadow' ),
			'desc'    => __( 'Visual board to organize and track findings by status with drag-and-drop interface.', 'wpshadow' ),
			'tool'    => 'kanban-report',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Activity History', 'wpshadow' ),
			'desc'    => __( 'Comprehensive audit log of all actions, changes, and fixes performed on your site.', 'wpshadow' ),
			'tool'    => 'activity-history',
			'enabled' => true,
		),
	);
}

/**
 * Render tools page.
 *
 * NOTE: This function is already declared in wpshadow.php (line 1443)
 * This file only provides helper functions like wpshadow_run_broken_links_scan()
 * If wpshadow_render_tools() is not yet declared, uncomment below.
 *
 * @return void
 */
if ( ! function_exists( 'wpshadow_render_tools' ) ) {
	function wpshadow_render_tools() {
		if ( ! current_user_can( 'read' ) ) {
			wp_die( 'Insufficient permissions.' );
		}

		$tool = isset( $_GET['tool'] ) ? sanitize_key( $_GET['tool'] ) : '';

		// Route to specific tool if requested
		if ( ! empty( $tool ) ) {
			$tool_file = WPSHADOW_PATH . 'includes/views/tools/' . $tool . '.php';
			if ( file_exists( $tool_file ) ) {
				include $tool_file;
				return;
			}
		}

		$catalog = wpshadow_get_tools_catalog();

		// Show tools index
		?>
	<div class="wps-page-container">
		<!-- Page Header -->
		<div class="wps-page-header">
			<h1 class="wps-page-title">
				<span class="dashicons dashicons-admin-tools" style="color: var(--wps-primary);"></span>
				<?php esc_html_e( 'WPShadow Tools', 'wpshadow' ); ?>
			</h1>
			<p class="wps-page-subtitle">
				<?php esc_html_e( 'Additional tools for site analysis and optimization.', 'wpshadow' ); ?>
			</p>
		</div>

		<!-- Tools Grid -->
		<div class="wps-grid">
			<?php
			foreach ( $catalog as $item ) :
				$tool_url = admin_url( 'admin.php?page=wpshadow-tools&tool=' . $item['tool'] );
				?>
			<div class="wps-card">
				<div class="wps-card-header">
					<div>
						<h3 class="wps-card-title">
							<?php echo esc_html( $item['title'] ); ?>
						</h3>
						<p class="wps-card-description">
							<?php echo esc_html( $item['desc'] ); ?>
						</p>
					</div>
				</div>
				<div class="wps-card-body" style="padding-top: 12px;">
					<?php if ( ! empty( $item['enabled'] ) ) : ?>
						<a href="<?php echo esc_url( $tool_url ); ?>" class="wps-btn wps-btn-primary">
							<span class="dashicons dashicons-external" style="font-size: 14px;"></span>
							<?php esc_html_e( 'Open Tool', 'wpshadow' ); ?>
						</a>
					<?php else : ?>
						<button class="wps-btn wps-btn-secondary" disabled>
							<span class="dashicons dashicons-hourglass" style="font-size: 14px;"></span>
							<?php esc_html_e( 'Coming Soon', 'wpshadow' ); ?>
						</button>
					<?php endif; ?>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
		<?php
	}
} // End if ( ! function_exists( 'wpshadow_render_tools' ) )

/**
 * Run broken links scan programmatically for tools/workflows.
 *
 * @param array $args Scan options.
 * @return array Results with broken_links, posts_checked, links_checked.
 */
function wpshadow_run_broken_links_scan( $args = array() ) {
	$defaults = array(
		'check_internal' => true,
		'check_external' => true,
		'check_images'   => false,
		'limit'          => -1,
	);
	$args     = wp_parse_args( $args, $defaults );

	$broken_links  = array();
	$posts_checked = 0;
	$links_checked = 0;

	$query_args = array(
		'post_type'      => array( 'post', 'page' ),
		'posts_per_page' => $args['limit'],
		'post_status'    => 'publish',
	);

	$posts         = get_posts( $query_args );
	$posts_checked = count( $posts );

	foreach ( $posts as $post ) {
		$content = $post->post_content;

		// Links
		preg_match_all( '/<a\s+(?:[^>]*?\s+)?href=["\']([^"\']+)["\']/', $content, $matches );
		if ( ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $url ) {
				++$links_checked;
				if ( strpos( $url, '#' ) === 0 ) {
					continue;
				}
				$is_internal = strpos( $url, home_url() ) === 0 || strpos( $url, '/' ) === 0;
				if ( $is_internal && ! $args['check_internal'] ) {
					continue;
				}
				if ( ! $is_internal && ! $args['check_external'] ) {
					continue;
				}
				if ( strpos( $url, '/' ) === 0 ) {
					$url = home_url( $url );
				}
				$response = wp_remote_head(
					$url,
					array(
						'timeout'     => 5,
						'redirection' => 2,
					)
				);
				if ( is_wp_error( $response ) ) {
					$broken_links[] = array(
						'url'         => $url,
						'post_title'  => $post->post_title,
						'edit_url'    => get_edit_post_link( $post->ID, 'raw' ),
						'status_code' => 'ERROR',
					);
				} else {
					$code = wp_remote_retrieve_response_code( $response );
					if ( $code >= 400 ) {
						$broken_links[] = array(
							'url'         => $url,
							'post_title'  => $post->post_title,
							'edit_url'    => get_edit_post_link( $post->ID, 'raw' ),
							'status_code' => $code,
						);
					}
				}
			}
		}

		// Images
		if ( $args['check_images'] ) {
			preg_match_all( '/<img\s+(?:[^>]*?\s+)?src=["\']([^"\']+)["\']/', $content, $img_matches );
			if ( ! empty( $img_matches[1] ) ) {
				foreach ( $img_matches[1] as $img_url ) {
					++$links_checked;
					$response = wp_remote_head(
						$img_url,
						array(
							'timeout'     => 5,
							'redirection' => 2,
						)
					);
					if ( is_wp_error( $response ) ) {
						$broken_links[] = array(
							'url'         => $img_url,
							'post_title'  => $post->post_title . ' (image)',
							'edit_url'    => get_edit_post_link( $post->ID, 'raw' ),
							'status_code' => 'ERROR',
						);
					} else {
						$code = wp_remote_retrieve_response_code( $response );
						if ( $code >= 400 ) {
							$broken_links[] = array(
								'url'         => $img_url,
								'post_title'  => $post->post_title . ' (image)',
								'edit_url'    => get_edit_post_link( $post->ID, 'raw' ),
								'status_code' => $code,
							);
						}
					}
				}
			}
		}
	}

	return array(
		'broken_links'  => $broken_links,
		'posts_checked' => $posts_checked,
		'links_checked' => $links_checked,
	);
}
