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

use WPShadow\Core\Form_Param_Helper;

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
			'icon'    => 'dashicons-performance',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Deep Scan', 'wpshadow' ),
			'desc'    => __( 'Run a comprehensive scan that checks database health, performance, and advanced compatibility issues.', 'wpshadow' ),
			'tool'    => 'deep-scan',
			'icon'    => 'dashicons-search',
			'enabled' => true,
		),
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
			'title'   => __( 'Kanban Report', 'wpshadow' ),
			'desc'    => __( 'Visual board to organize and track findings by status with drag-and-drop interface.', 'wpshadow' ),
			'tool'    => 'kanban-report',
			'icon'    => 'dashicons-grid-view',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Activity History', 'wpshadow' ),
			'desc'    => __( 'Comprehensive audit log of all actions, changes, and fixes performed on your site.', 'wpshadow' ),
			'tool'    => 'activity-history',
			'icon'    => 'dashicons-backup',
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

		// Check for legacy ?tool= parameter
		$requested_tool = Form_Param_Helper::get( 'tool', 'key', '' );

		$catalog = wpshadow_get_tools_catalog();
		?>
	<div class="wps-page-container">
		<!-- Page Header -->
		<?php wpshadow_render_page_header(
			__( 'WPShadow Tools', 'wpshadow' ),
			__( 'Additional tools for site analysis and optimization.', 'wpshadow' ),
			'dashicons-admin-tools'
		); ?>

		<!-- Tools Tabbed Interface -->
		<div class="wps-tools-tab-container">
			<!-- Tab Navigation -->
			<div class="wps-tools-tab-nav" role="tablist">
				<div class="wps-tools-tab-index" id="wps-tools-tab-index" role="tab" aria-selected="true" aria-controls="wps-tools-tab-pane-index">
					<?php esc_html_e( 'All Tools', 'wpshadow' ); ?>
				</div>
				<?php foreach ( $catalog as $item ) : ?>
					<div 
						class="wps-tools-tab-button" 
						id="wps-tools-tab-<?php echo esc_attr( $item['tool'] ); ?>" 
						role="tab" 
						aria-selected="false" 
						aria-controls="wps-tools-tab-pane-<?php echo esc_attr( $item['tool'] ); ?>"
						data-tool="<?php echo esc_attr( $item['tool'] ); ?>"
					>
						<span class="dashicons <?php echo esc_attr( ! empty( $item['icon'] ) ? $item['icon'] : 'dashicons-admin-generic' ); ?>"></span>
						<?php echo esc_html( $item['title'] ); ?>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- Tab Content Panes -->
			<div class="wps-tools-tab-content">
				<!-- Index Pane -->
				<div class="wps-tools-tab-pane wps-tools-tab-pane-active" id="wps-tools-tab-pane-index" role="tabpanel" aria-labelledby="wps-tools-tab-index">
					<div class="wps-grid wps-grid-auto-320">
						<?php
						foreach ( $catalog as $item ) :
							$icon_class = ! empty( $item['icon'] ) ? $item['icon'] : 'dashicons-admin-generic';
							?>
						<div class="wps-card">
							<div class="wps-card-header wps-pb-3 wps-border-bottom">
								<div class="wps-flex wps-gap-3 wps-items-start">
									<span class="dashicons <?php echo esc_attr( $icon_class ); ?> wps-text-3xl wps-text-primary"></span>
									<div>
										<h3 class="wps-card-title wps-m-0">
										<?php if ( ! empty( $item['enabled'] ) ) : ?>
											<a href="#" class="wps-tool-link" data-tool="<?php echo esc_attr( $item['tool'] ); ?>" style="color: inherit; text-decoration: none;">
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
									<button class="wps-btn wps-btn--secondary wps-tool-open-btn" data-tool="<?php echo esc_attr( $item['tool'] ); ?>">
										<span class="dashicons dashicons-external"></span>
										<?php esc_html_e( 'Open Tool', 'wpshadow' ); ?>
									</button>
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

				<!-- Tool Panes -->
				<?php foreach ( $catalog as $item ) : ?>
					<div class="wps-tools-tab-pane wps-tools-tab-pane-loading" id="wps-tools-tab-pane-<?php echo esc_attr( $item['tool'] ); ?>" role="tabpanel" aria-labelledby="wps-tools-tab-<?php echo esc_attr( $item['tool'] ); ?>" data-tool="<?php echo esc_attr( $item['tool'] ); ?>">
						<div class="wps-loading-spinner">
							<span class="spinner"></span>
							<?php esc_html_e( 'Loading tool...', 'wpshadow' ); ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<style>
		.wps-tools-tab-container {
			display: flex;
			flex-direction: column;
			gap: 0;
		}

		.wps-tools-tab-nav {
			display: flex;
			gap: 0;
			border-bottom: 2px solid #e5e5e5;
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
		}

		.wps-tools-tab-index,
		.wps-tools-tab-button {
			padding: 12px 20px;
			cursor: pointer;
			background: none;
			border: none;
			border-bottom: 3px solid transparent;
			font-size: 14px;
			font-weight: 500;
			color: #666;
			transition: all 0.2s ease;
			display: flex;
			align-items: center;
			gap: 8px;
			white-space: nowrap;
			margin-bottom: -2px;
			position: relative;
		}

		.wps-tools-tab-index:hover,
		.wps-tools-tab-button:hover {
			color: #0073aa;
			background-color: #f5f5f5;
		}

		.wps-tools-tab-index[aria-selected="true"],
		.wps-tools-tab-button[aria-selected="true"] {
			color: #0073aa;
			border-bottom-color: #0073aa;
		}

		.wps-tools-tab-content {
			flex: 1;
			position: relative;
		}

		.wps-tools-tab-pane {
			display: none;
			animation: fadeIn 0.2s ease;
		}

		.wps-tools-tab-pane-active {
			display: block;
		}

		@keyframes fadeIn {
			from {
				opacity: 0;
			}
			to {
				opacity: 1;
			}
		}

		.wps-tools-tab-pane-loading {
			display: flex;
			align-items: center;
			justify-content: center;
			min-height: 400px;
			gap: 12px;
		}

		.wps-loading-spinner {
			display: flex;
			align-items: center;
			gap: 12px;
			font-size: 14px;
			color: #666;
		}

		.wps-loading-spinner .spinner {
			display: inline-block;
			background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48Y2lyY2xlIGN4PSIxMCIgY3k9IjEwIiByPSI4IiBzdHJva2U9IiNkZGQiIHN0cm9rZS13aWR0aD0iMiIgZmlsbD0ibm9uZSIvPjxwYXRoIGQ9Ik0xOCAxMGExIDEgMCAwIDEtMiAwIiBzdHJva2U9IiMwMDczYWEiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBmaWxsPSJub25lIiBzdHJva2Utb3BhY2l0eT0iMC44Ii8+PC9zdmc+');
			background-size: 20px 20px;
			width: 20px;
			height: 20px;
			animation: spin 1s linear infinite;
		}

		@keyframes spin {
			to {
				transform: rotate(360deg);
			}
		}

		.wps-tools-tab-pane-loading .spinner {
			background-color: transparent;
		}

		@media (max-width: 768px) {
			.wps-tools-tab-index,
			.wps-tools-tab-button {
				padding: 10px 12px;
				font-size: 12px;
			}

			.wps-tools-tab-button .dashicons {
				display: none;
			}
		}
	</style>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const tabIndex = document.getElementById('wps-tools-tab-index');
			const tabButtons = document.querySelectorAll('.wps-tools-tab-button');
			const openButtons = document.querySelectorAll('.wps-tool-open-btn');
			const tabLinks = document.querySelectorAll('.wps-tool-link');

			function switchTab(tabElement) {
				// Deactivate all tabs
				document.querySelectorAll('.wps-tools-tab-index, .wps-tools-tab-button').forEach(tab => {
					tab.setAttribute('aria-selected', 'false');
				});

				// Hide all panes
				document.querySelectorAll('.wps-tools-tab-pane').forEach(pane => {
					pane.classList.remove('wps-tools-tab-pane-active');
				});

				// Activate clicked tab
				tabElement.setAttribute('aria-selected', 'true');

				// Show corresponding pane
				const paneId = tabElement.getAttribute('aria-controls');
				const pane = document.getElementById(paneId);
				if (pane) {
					pane.classList.add('wps-tools-tab-pane-active');

					// Load tool if not already loaded
					if (pane.classList.contains('wps-tools-tab-pane-loading')) {
						loadTool(pane);
					}
				}
			}

			function loadTool(pane) {
				const tool = pane.getAttribute('data-tool');
				if (!tool) return;

				// Set loading state
				pane.innerHTML = '<div class="wps-loading-spinner"><span class="spinner"></span><?php esc_html_e( 'Loading tool...', 'wpshadow' ); ?></div>';

				// Fetch tool content via AJAX
				fetch(ajaxurl, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					body: new URLSearchParams({
						action: 'wpshadow_load_tool',
						tool: tool,
						nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_load_tool' ) ); ?>'
					})
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						pane.innerHTML = data.data.content;
						pane.classList.remove('wps-tools-tab-pane-loading');
					} else {
						pane.innerHTML = '<div class="wps-alert wps-alert--error"><p><?php esc_html_e( 'Failed to load tool.', 'wpshadow' ); ?></p></div>';
					}
				})
				.catch(error => {
					console.error('Error loading tool:', error);
					pane.innerHTML = '<div class="wps-alert wps-alert--error"><p><?php esc_html_e( 'Error loading tool.', 'wpshadow' ); ?></p></div>';
				});
			}

			// Tab index (All Tools)
			if (tabIndex) {
				tabIndex.addEventListener('click', function() {
					switchTab(this);
				});
			}

			// Tab buttons
			tabButtons.forEach(button => {
				button.addEventListener('click', function() {
					switchTab(this);
				});
			});

			// Open buttons
			openButtons.forEach(button => {
				button.addEventListener('click', function(e) {
					e.preventDefault();
					const tool = this.getAttribute('data-tool');
					const tabButton = document.getElementById('wps-tools-tab-' + tool);
					if (tabButton) {
						switchTab(tabButton);
					}
				});
			});

			// Tool links
			tabLinks.forEach(link => {
				link.addEventListener('click', function(e) {
					e.preventDefault();
					const tool = this.getAttribute('data-tool');
					const tabButton = document.getElementById('wps-tools-tab-' + tool);
					if (tabButton) {
						switchTab(tabButton);
					}
				});
			});

			// Auto-open tool if requested via URL parameter (backward compatibility)
			<?php if ( ! empty( $requested_tool ) ) : ?>
				const autoTool = '<?php echo esc_js( $requested_tool ); ?>';
				const autoTabButton = document.getElementById('wps-tools-tab-' + autoTool);
				if (autoTabButton) {
					setTimeout(function() {
						switchTab(autoTabButton);
					}, 100);
				}
			<?php endif; ?>
		});
	</script>
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
