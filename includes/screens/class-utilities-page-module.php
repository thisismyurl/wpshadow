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
 * Note: Report-generation tools (Deep Scan, Quick Scan, etc.) moved to Reports section.
 * Utilities are true utility tools for site management and maintenance.
 *
 * @return array Utilities.
 */
function wpshadow_get_utilities_catalog() {
	return array(
		// Site Management Tools
		array(
			'title'   => __( 'WPShadow Cache', 'wpshadow' ),
			'desc'    => __( 'Manage site caching and clear cache when needed.', 'wpshadow' ),
			'tool'    => 'simple-cache',
			'icon'    => 'dashicons-database',
			'family'  => 'site-management',
			'enabled' => true,
		),

		// Cloud-Powered Utilities (Require WPShadow Cloud Registration)
		array(
			'title'   => __( 'Uptime Monitor', 'wpshadow' ),
			'desc'    => __( 'External monitoring pings your site every 5 minutes to detect downtime before your customers do. Free: 1 site.', 'wpshadow' ),
			'tool'    => 'uptime-monitor',
			'icon'    => 'dashicons-visibility',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
		),
		array(
			'title'   => __( 'SSL Certificate Monitor', 'wpshadow' ),
			'desc'    => __( 'Monitor SSL certificate expiration and validity from external validators. Free: 1 site, daily checks.', 'wpshadow' ),
			'tool'    => 'ssl-monitor',
			'icon'    => 'dashicons-lock',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
		),
		array(
			'title'   => __( 'Domain Expiration Monitor', 'wpshadow' ),
			'desc'    => __( 'Track domain registration expiration with WHOIS monitoring and alerts. Free: 3 domains.', 'wpshadow' ),
			'tool'    => 'domain-monitor',
			'icon'    => 'dashicons-admin-site',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
		),
		array(
			'title'   => __( 'AI Content Optimizer', 'wpshadow' ),
			'desc'    => __( 'AI-powered content analysis for readability, SEO, and accessibility. Auto-generate meta descriptions. Free: 1,000 credits per month.', 'wpshadow' ),
			'tool'    => 'ai-content-optimizer',
			'icon'    => 'dashicons-welcome-write-blog',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
		),
		array(
			'title'   => __( 'AI Image Alt Text Generator', 'wpshadow' ),
			'desc'    => __( 'Automatically generate WCAG-compliant alt text for images using computer vision AI. Free: 1,000 credits per month.', 'wpshadow' ),
			'tool'    => 'ai-image-alt',
			'icon'    => 'dashicons-format-image',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
		),
		array(
			'title'   => __( 'AI Spam Detection', 'wpshadow' ),
			'desc'    => __( 'Advanced ML-powered spam and malicious comment detection beyond basic filters. Free: 1,000 credits per month.', 'wpshadow' ),
			'tool'    => 'ai-spam-detection',
			'icon'    => 'dashicons-shield-alt',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
		),
		array(
			'title'   => __( 'External Malware Scanner', 'wpshadow' ),
			'desc'    => __( 'Independent security audit from external servers to detect malware, backdoors, and suspicious code. Free: Weekly scans.', 'wpshadow' ),
			'tool'    => 'external-malware-scanner',
			'icon'    => 'dashicons-warning',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
		),
		array(
			'title'   => __( 'Blacklist Monitor', 'wpshadow' ),
			'desc'    => __( 'Monitor if your site or IP appears on spam blacklists (Google Safe Browsing, Spamhaus, etc.). Free: 1 site, weekly checks.', 'wpshadow' ),
			'tool'    => 'blacklist-monitor',
			'icon'    => 'dashicons-shield',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
		),
		array(
			'title'   => __( 'DDoS Detection', 'wpshadow' ),
			'desc'    => __( 'Analyze traffic patterns to detect DDoS attacks and unusual traffic spikes. Free: Basic monitoring.', 'wpshadow' ),
			'tool'    => 'ddos-detection',
			'icon'    => 'dashicons-shield',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
		),
		array(
			'title'   => __( 'Global Performance Monitor', 'wpshadow' ),
			'desc'    => __( 'Test site load times from multiple continents (NA, EU, Asia, AU, SA). Free: 5 locations, 3 tests/day.', 'wpshadow' ),
			'tool'    => 'global-performance',
			'icon'    => 'dashicons-performance',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
		),
		array(
			'title'   => __( 'Keyword Rank Tracker', 'wpshadow' ),
			'desc'    => __( 'Track Google rankings for target keywords with weekly position updates. Free: 10 keywords.', 'wpshadow' ),
			'tool'    => 'keyword-tracker',
			'icon'    => 'dashicons-chart-line',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
		),
		array(
			'title'   => __( 'External Broken Link Checker', 'wpshadow' ),
			'desc'    => __( 'Check external links as search engines see them, validate redirects. Free: 500 URLs/month.', 'wpshadow' ),
			'tool'    => 'external-link-checker',
			'icon'    => 'dashicons-admin-links',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
		),
		array(
			'title'   => __( 'AI Writing Assistant', 'wpshadow' ),
			'desc'    => __( 'In-editor AI suggestions to expand, summarize, rewrite, and improve content tone. Free: 10 requests/day.', 'wpshadow' ),
			'tool'    => 'ai-writing-assistant',
			'icon'    => 'dashicons-edit',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
		),
		array(
			'title'   => __( 'AI Translation', 'wpshadow' ),
			'desc'    => __( 'Neural machine translation with context awareness, better than Google Translate. Free: 10,000 words/month.', 'wpshadow' ),
			'tool'    => 'ai-translation',
			'icon'    => 'dashicons-translation',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
		),
		array(
			'title'   => __( 'AI Chatbot Support', 'wpshadow' ),
			'desc'    => __( 'AI chatbot trained on your site content to answer visitor questions. Free: 100 conversations/month.', 'wpshadow' ),
			'tool'    => 'ai-chatbot',
			'icon'    => 'dashicons-format-chat',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
		),

		array(
			'title'   => __( 'WPShadow Vault Light', 'wpshadow' ),
			'desc'    => __( 'Schedule Vault Light snapshots, manage retention, and upgrade seamlessly to Vault.', 'wpshadow' ),
			'tool'    => 'vault-light',
			'icon'    => 'dashicons-backup',
			'family'  => 'site-management',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Dark Mode', 'wpshadow' ),
			'desc'    => __( 'Enable dark mode for the WordPress admin interface.', 'wpshadow' ),
			'tool'    => 'dark-mode',
			'icon'    => 'dashicons-visibility',
			'family'  => 'site-management',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Email Test & Configuration', 'wpshadow' ),
			'desc'    => __( 'Test email delivery and configure From Name/Email to ensure emails are sent properly.', 'wpshadow' ),
			'tool'    => 'email-test',
			'icon'    => 'dashicons-email',
			'family'  => 'site-management',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Magic Link Support', 'wpshadow' ),
			'desc'    => __( 'Generate secure one-time access links for support staff.', 'wpshadow' ),
			'tool'    => 'magic-link-support',
			'icon'    => 'dashicons-admin-users',
			'family'  => 'site-management',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Tips & Guidance', 'wpshadow' ),
			'desc'    => __( 'Friendly tooltips across wp-admin with opt-out controls and helpful guidance for beginners.', 'wpshadow' ),
			'tool'    => 'tips-coach',
			'icon'    => 'dashicons-lightbulb',
			'family'  => 'site-management',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Timezone Alignment', 'wpshadow' ),
			'desc'    => __( 'Verify and align your server, browser, and WordPress timezone settings.', 'wpshadow' ),
			'tool'    => 'timezone-alignment',
			'icon'    => 'dashicons-clock',
			'family'  => 'site-management',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Safe Mode', 'wpshadow' ),
			'desc'    => __( 'Temporarily disable plugins and themes for your session only to isolate conflicts without affecting the live site.', 'wpshadow' ),
			'tool'    => 'safe-mode',
			'icon'    => 'dashicons-shield',
			'family'  => 'site-management',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Asset Impact Explorer', 'wpshadow' ),
			'desc'    => __( 'Analyze scripts and stylesheets loading on your site to identify performance optimization opportunities.', 'wpshadow' ),
			'tool'    => 'asset-impact',
			'icon'    => 'dashicons-performance',
			'family'  => 'site-management',
			'enabled' => true,
		),
		array(
			'title'   => __( '404 Monitor', 'wpshadow' ),
			'desc'    => __( 'Track 404 errors and identify broken links. Set up redirects to fix broken URLs and improve SEO.', 'wpshadow' ),
			'tool'    => '404-monitor',
			'icon'    => 'dashicons-warning',
			'family'  => 'site-management',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Update Safety Check', 'wpshadow' ),
			'desc'    => __( 'Pre-update safety verification and Vault Light snapshot creation before WordPress updates.', 'wpshadow' ),
			'tool'    => 'update-safety',
			'icon'    => 'dashicons-update',
			'family'  => 'site-management',
			'enabled' => true,
		),

		// Killer Utilities (Added 1.2601.2200)
		array(
			'title'   => __( 'Site Cloner', 'wpshadow' ),
			'desc'    => __( 'One-click site cloning to subdomain or subdirectory. Perfect for staging, testing, and development. Free: 2 clones.', 'wpshadow' ),
			'tool'    => 'site-cloner',
			'icon'    => 'dashicons-admin-site-alt3',
			'family'  => 'site-management',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Smart Code Snippets', 'wpshadow' ),
			'desc'    => __( 'Add PHP, JavaScript, and CSS snippets safely without editing theme files. Syntax validation and sandboxed testing. Free: 10 snippets.', 'wpshadow' ),
			'tool'    => 'code-snippets',
			'icon'    => 'dashicons-editor-code',
			'family'  => 'site-management',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Plugin Conflict Detector', 'wpshadow' ),
			'desc'    => __( 'Automatically identify which plugin is causing an issue using binary search. Finds conflicts in minutes, not hours.', 'wpshadow' ),
			'tool'    => 'plugin-conflict',
			'icon'    => 'dashicons-admin-plugins',
			'family'  => 'site-management',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Bulk Find & Replace', 'wpshadow' ),
			'desc'    => __( 'Search and replace across posts, pages, custom fields, and options. Perfect for domain changes and bulk updates.', 'wpshadow' ),
			'tool'    => 'bulk-find-replace',
			'icon'    => 'dashicons-search',
			'family'  => 'site-management',
			'enabled' => true,
		),
		array(
			'title'   => __( 'Regenerate Thumbnails', 'wpshadow' ),
			'desc'    => __( 'Batch regenerate image thumbnails for all registered sizes. Perfect after theme changes or adding custom image sizes.', 'wpshadow' ),
			'tool'    => 'regenerate-thumbnails',
			'icon'    => 'dashicons-image-rotate',
			'family'  => 'site-management',
			'enabled' => true,
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
		if ( 'backup' === $tab ) {
			$tab = 'vault-light';
		}

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
		// Group utilities by family
		$families = array();
		foreach ( $catalog as $item ) {
			$family = ! empty( $item['family'] ) ? $item['family'] : 'other';
			if ( ! isset( $families[ $family ] ) ) {
				$families[ $family ] = array();
			}
			$families[ $family ][] = $item;
		}

		$family_titles = array(
			'cloud-tools'      => __( 'Cloud-Powered Tools (Free with Registration)', 'wpshadow' ),
			'page-analysis'    => __( 'Page Analysis Tools', 'wpshadow' ),
			'site-management'  => __( 'Site Management', 'wpshadow' ),
			'other'            => __( 'Other Utilities', 'wpshadow' ),
		);
		?>
	<div class="wps-page-container">
		<!-- Page Header -->
		<?php wpshadow_render_page_header(
			__( 'WPShadow Utilities', 'wpshadow' ),
			__( 'Additional utilities for site analysis and optimization.', 'wpshadow' ),
			'dashicons-admin-tools'
		); ?>

		<!-- Privacy Dashboard Quick Access -->
		<div style="margin-bottom: 40px;">
			<div class="wps-grid wps-grid-auto-320">
				<div class="wps-card wps-card--link" role="link" tabindex="0" aria-label="<?php esc_attr_e( 'Open Privacy Dashboard', 'wpshadow' ); ?>" onclick="window.location.href='<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-privacy' ) ); ?>';">
					<div class="wps-card-header wps-pb-3 wps-border-bottom">
						<div class="wps-flex wps-gap-3 wps-items-start">
							<span class="dashicons dashicons-lock wps-text-3xl wps-text-primary"></span>
							<div>
								<h3 class="wps-card-title wps-m-0">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-privacy' ) ); ?>" style="color: inherit; text-decoration: none;">
										<?php esc_html_e( 'Privacy Dashboard', 'wpshadow' ); ?>
									</a>
								</h3>
								<p class="wps-card-description wps-m-0">
									<?php esc_html_e( 'Manage data export, deletion, and privacy preferences.', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="wps-card-body">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-privacy' ) ); ?>" class="wps-btn wps-btn--secondary">
							<span class="dashicons dashicons-arrow-right-alt"></span>
							<?php esc_html_e( 'Open', 'wpshadow' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>

		<?php foreach ( $families as $family_key => $family_items ) : ?>
			<?php if ( ! empty( $family_items ) ) : ?>
				<!-- Family Section -->
				<div class="wps-utilities-family" style="margin-bottom: 40px;">
					<h2 style="font-size: 20px; margin-bottom: 15px; color: #1d2327;">
						<?php echo esc_html( isset( $family_titles[ $family_key ] ) ? $family_titles[ $family_key ] : ucwords( str_replace( '-', ' ', $family_key ) ) ); ?>
					</h2>

					<!-- Utilities Grid -->
					<div class="wps-grid wps-grid-auto-320">
						<?php
						foreach ( $family_items as $item ) :
							$icon_class = ! empty( $item['icon'] ) ? $item['icon'] : 'dashicons-admin-generic';
							$utility_url = admin_url( 'admin.php?page=wpshadow-utilities&tab=' . $item['tool'] );
							?>
			<div class="wps-card<?php echo ! empty( $item['enabled'] ) ? ' wps-card--link' : ''; ?>"<?php if ( ! empty( $item['enabled'] ) ) : ?> data-utility-url="<?php echo esc_url( $utility_url ); ?>" role="link" tabindex="0" aria-label="<?php echo esc_attr( sprintf( __( 'Open %s utility', 'wpshadow' ), $item['title'] ) ); ?>"<?php endif; ?>>
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
			<?php endif; ?>
		<?php endforeach; ?>

		<!-- Activity History Section -->
		<div style="margin-top: 60px; border-top: 1px solid #e0e0e0; padding-top: 40px;">
			<?php
			if ( function_exists( 'wpshadow_render_page_activities' ) ) {
				wpshadow_render_page_activities( 'utilities', 10 );
			}
			?>
		</div>
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
		if ( function_exists( '_deprecated_function' ) ) {
			_deprecated_function( __FUNCTION__, '1.2601.2200', 'wpshadow_render_utilities' );
		}
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
