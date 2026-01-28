<?php
/**
 * Utilities Page Module for WPShadow
 *
 * Handles utilities page rendering and tool loading.
 *
 * @package WPShadow
 * @subpackage Admin
 */

declare(strict_types=1);

use WPShadow\Core\Form_Param_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load tooltip manager functions (needed by tips-coach tool)
require_once WPSHADOW_PATH . 'includes/dashboard/widgets/class-tooltip-manager.php';

/**
 * Get utilities catalog.
 *
 * @return array Utilities.
 */
function wpshadow_get_utilities_catalog() {
	return array(
		array(
			'title'   => __( 'Accessibility Audit', 'wpshadow' ),
			'desc'    => __( 'Scan your site for accessibility issues and WCAG compliance.', 'wpshadow' ),
			'tool'    => 'a11y-audit',
			'icon'    => 'dashicons-universal-access',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Broken Link Checker', 'wpshadow' ),
			'desc'    => __( 'Find and fix broken links across your site.', 'wpshadow' ),
			'tool'    => 'broken-links',
			'icon'    => 'dashicons-admin-links',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Color Contrast Checker', 'wpshadow' ),
			'desc'    => __( 'Check color combinations for accessibility compliance.', 'wpshadow' ),
			'tool'    => 'color-contrast-checker',
			'icon'    => 'dashicons-art',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Dark Mode', 'wpshadow' ),
			'desc'    => __( 'Enable dark mode for the WordPress admin interface.', 'wpshadow' ),
			'tool'    => 'dark-mode',
			'icon'    => 'dashicons-visibility',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Email Test & Configuration', 'wpshadow' ),
			'desc'    => __( 'Test email delivery and configure From Name/Email to ensure emails are sent properly.', 'wpshadow' ),
			'tool'    => 'email-test',
			'icon'    => 'dashicons-email',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Cache Management', 'wpshadow' ),
			'desc'    => __( 'Manage site caching and clear cache when needed.', 'wpshadow' ),
			'tool'    => 'simple-cache',
			'icon'    => 'dashicons-database',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Magic Link Support', 'wpshadow' ),
			'desc'    => __( 'Generate secure one-time access links for support staff.', 'wpshadow' ),
			'tool'    => 'magic-link-support',
			'icon'    => 'dashicons-admin-users',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Mobile Friendliness', 'wpshadow' ),
			'desc'    => __( 'Test your site for mobile compatibility and responsive design.', 'wpshadow' ),
			'tool'    => 'mobile-friendliness',
			'icon'    => 'dashicons-smartphone',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Tips & Guidance', 'wpshadow' ),
			'desc'    => __( 'Friendly tooltips across wp-admin with opt-out controls and helpful guidance for beginners.', 'wpshadow' ),
			'tool'    => 'tips-coach',
			'icon'    => 'dashicons-lightbulb',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Customization Audit', 'wpshadow' ),
			'desc'    => __( 'Analyze custom themes, plugins, and code modifications for potential issues.', 'wpshadow' ),
			'tool'    => 'customization-audit',
			'icon'    => 'dashicons-admin-generic',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Timezone Alignment', 'wpshadow' ),
			'desc'    => __( 'Verify and align your server, browser, and WordPress timezone settings.', 'wpshadow' ),
			'tool'    => 'timezone-alignment',
			'icon'    => 'dashicons-clock',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Visual Comparisons', 'wpshadow' ),
			'desc'    => __( 'Visual regression testing and screenshot comparison tool.', 'wpshadow' ),
			'tool'    => 'visual-comparisons',
			'icon'    => 'dashicons-format-image',
			'enabled' => false, // Coming soon
		),
	);
}

/**
 * Render utilities page.
 *
 * @return void
 */
if ( ! function_exists( 'wpshadow_render_utilities' ) ) {
	function wpshadow_render_utilities() {
		if ( ! current_user_can( 'read' ) ) {
			wp_die( 'Insufficient permissions.' );
		}

		// Check if a specific utility is requested via tab parameter
		$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : '';

		// Get utilities catalog
		$catalog = wpshadow_get_utilities_catalog();

		// If a specific utility is requested, load and render it
		if ( ! empty( $tab ) ) {
			// Find the requested utility in the catalog
			$found_utility = null;
			foreach ( $catalog as $item ) {
				if ( $item['tool'] === $tab ) {
					$found_utility = $item;
					break;
				}
			}

			// If utility not found or not enabled, show error
			if ( ! $found_utility || empty( $found_utility['enabled'] ) ) {
				?>
				<div class="wps-page-container">
					<?php wpshadow_render_page_header(
						__( 'Utility Not Found', 'wpshadow' ),
						'',
						'dashicons-admin-tools'
					); ?>
					
					<div class="wps-card wps-card--warning">
						<div class="wps-card-body">
							<p>
								<?php esc_html_e( 'This utility is not available. Please check the URL or select a different utility.', 'wpshadow' ); ?>
							</p>
							<p>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-utilities' ) ); ?>" class="wps-btn wps-btn--secondary">
									&larr; <?php esc_html_e( 'Back to Utilities', 'wpshadow' ); ?>
								</a>
							</p>
						</div>
					</div>
				</div>
				<?php
				return;
			}

			// Load and render the utility
			$utility_file = WPSHADOW_PATH . 'includes/views/tools/' . $tab . '.php';
			if ( file_exists( $utility_file ) ) {
				require $utility_file;
				return;
			}

			// Fallback if file doesn't exist
			?>
			<div class="wps-page-container">
				<?php wpshadow_render_page_header(
					esc_html( $found_utility['title'] ),
					'',
					'dashicons-admin-tools'
				); ?>
				
				<div class="wps-card wps-card--error">
					<div class="wps-card-body">
						<p>
							<?php esc_html_e( 'This utility could not be loaded. The utility file may be missing.', 'wpshadow' ); ?>
						</p>
						<p>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-utilities' ) ); ?>" class="wps-btn wps-btn--secondary">
								&larr; <?php esc_html_e( 'Back to Utilities', 'wpshadow' ); ?>
							</a>
						</p>
					</div>
				</div>
			</div>
			<?php
			return;
		}

		// Show utilities overview grid
		?>
	<div class="wps-page-container">
		<!-- Page Header -->
		<?php wpshadow_render_page_header(
			__( 'WPShadow Utilities', 'wpshadow' ),
			__( 'Additional utilities for site analysis and optimization.', 'wpshadow' ),
			'dashicons-admin-tools'
		); ?>

		<!-- Utilities Grid -->
		<div class="wps-grid wps-grid-auto-320">
			<?php
			foreach ( $catalog as $item ) :
				$icon_class = ! empty( $item['icon'] ) ? $item['icon'] : 'dashicons-admin-generic';
				$utility_url = admin_url( 'admin.php?page=wpshadow-utilities&tab=' . $item['tool'] );
				?>
			<div class="wps-card">
				<div class="wps-card-header wps-pb-3 wps-border-bottom">
					<div class="wps-flex wps-gap-3 wps-items-start">
						<span class="dashicons <?php echo esc_attr( $icon_class ); ?> wps-text-3xl wps-text-primary"></span>
						<div>
							<h3 class="wps-card-title wps-m-0">
							<?php if ( ! empty( $item['enabled'] ) ) : ?>
								<a href="<?php echo esc_url( $utility_url ); ?>" style="color: inherit; text-decoration: none;">
									<?php echo esc_html( $item['title'] ); ?>
								</a>
							<?php else : ?>
								<?php echo esc_html( $item['title'] ); ?>
							<?php endif; ?>
			</h3>
							<p class="wps-card-description wps-m-0">
								<?php echo esc_html( $item['desc'] ); ?>
							</p>
						</div>
					</div>
				</div>
				<div class="wps-card-body">
					<?php if ( ! empty( $item['enabled'] ) ) : ?>
						<a href="<?php echo esc_url( $utility_url ); ?>" class="wps-btn wps-btn--secondary">
							<span class="dashicons dashicons-external"></span>
							<?php esc_html_e( 'Open Utility', 'wpshadow' ); ?>
						</a>
					<?php else : ?>
						<button class="wps-btn wps-btn--secondary" disabled>
							<span class="dashicons dashicons-hourglass"></span>
							<?php esc_html_e( 'Coming Soon', 'wpshadow' ); ?>
						</button>
					<?php endif; ?>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>

	<!-- Activity History Section -->
	<div style="margin-top: 60px; border-top: 1px solid #e0e0e0; padding-top: 40px;">
		<?php
		if ( function_exists( 'wpshadow_render_page_activities' ) ) {
			wpshadow_render_page_activities( 'utilities', 10 );
		}
		?>
	</div>

		<?php
	}
} // End if ( ! function_exists( 'wpshadow_render_utilities' ) )

// Legacy compatibility alias
if ( ! function_exists( 'wpshadow_render_tools' ) ) {
	/**
	 * Legacy function name - redirects to wpshadow_render_utilities()
	 * 
	 * @deprecated Use wpshadow_render_utilities() instead
	 */
	function wpshadow_render_tools() {
		wpshadow_render_utilities();
	}
}

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
