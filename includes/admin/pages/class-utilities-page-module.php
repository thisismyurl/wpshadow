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
require_once WPSHADOW_PATH . 'includes/systems/dashboard/widgets/class-tooltip-manager.php';

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
			'title'   => __( 'Settings Backup & Restore', 'wpshadow' ),
			'desc'    => __( 'Save your WPShadow settings to a file and copy them to other sites (like packing up configurations to move them).', 'wpshadow' ),
			'tool'    => 'import-export',
			'icon'    => 'dashicons-upload',
			'family'  => 'site-management',
			'enabled' => true,
			'since'   => '1.6180.1200', // Release 1.6180 (June 2026)
		),
		array(
			'title'   => __( 'Clear Site Memory', 'wpshadow' ),
			'desc'    => __( 'Clear stored copies of pages to force fresh versions (like refreshing your browser when things look wrong).', 'wpshadow' ),
			'tool'    => 'simple-cache',
			'icon'    => 'dashicons-database',
			'family'  => 'site-management',
			'enabled' => true,
		'since'   => '1.6037.1200', // Release 1.6037 (February 2026)
			'icon'    => 'dashicons-visibility',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
			'since'   => '1.6364.1200', // Release 1.6364 (December 2026)
		),
		array(
			'title'   => __( 'Security Certificate Watcher', 'wpshadow' ),
			'desc'    => __( 'Makes sure your site\'s security certificate doesn\'t expire (like renewing your driver\'s license before it expires). Free: 1 site, daily checks.', 'wpshadow' ),
			'tool'    => 'ssl-monitor',
			'icon'    => 'dashicons-lock',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
			'since'   => '1.6364.1200', // Release 1.6364 (December 2026)
		),
		array(
			'title'   => __( 'Domain Name Expiration Reminder', 'wpshadow' ),
			'desc'    => __( 'Warns you before your domain name expires so your site doesn\'t disappear (like a bill payment reminder). Free: 3 domains.', 'wpshadow' ),
			'tool'    => 'domain-monitor',
			'icon'    => 'dashicons-admin-site',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
			'since'   => '1.6364.1200', // Release 1.6364 (December 2026)
		),
		array(
			'title'   => __( 'Writing Helper', 'wpshadow' ),
			'desc'    => __( 'Checks your writing for readability and suggests improvements (like having an editor review your work). Free: 1,000 checks per month.', 'wpshadow' ),
			'tool'    => 'ai-content-optimizer',
			'icon'    => 'dashicons-welcome-write-blog',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
			'since'   => '1.6119.1200', // Release 1.6119 (April 2026)
		),
		array(
			'title'   => __( 'Image Description Writer', 'wpshadow' ),
			'desc'    => __( 'Automatically writes descriptions for your images so screen readers can describe them to blind visitors. Free: 1,000 images per month.', 'wpshadow' ),
			'tool'    => 'ai-image-alt',
			'icon'    => 'dashicons-format-image',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
			'since'   => '1.6150.1200', // Release 1.6150 (May 2026)
		),
		array(
			'title'   => __( 'Smart Spam Blocker', 'wpshadow' ),
			'desc'    => __( 'Catches spam comments that basic filters miss (like having a bouncer who recognizes troublemakers). Free: 1,000 checks per month.', 'wpshadow' ),
			'tool'    => 'ai-spam-detection',
			'icon'    => 'dashicons-shield-alt',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
			'since'   => '1.6150.1200', // Release 1.6150 (May 2026)
		),
		array(
			'title'   => __( 'Outside Security Check', 'wpshadow' ),
			'desc'    => __( 'Scans your site from the outside to find hidden malware (like getting a second opinion from another doctor). Free: Weekly scans.', 'wpshadow' ),
			'tool'    => 'external-malware-scanner',
			'icon'    => 'dashicons-warning',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
			'since'   => '1.6364.1200', // Release 1.6364 (December 2026)
		),
		array(
			'title'   => __( 'Spam List Checker', 'wpshadow' ),
			'desc'    => __( 'Checks if your site is on any spam blacklists that could block your emails (like checking your credit report). Free: 1 site, weekly checks.', 'wpshadow' ),
			'tool'    => 'blacklist-monitor',
			'icon'    => 'dashicons-shield',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
			'since'   => '1.6272.1200', // Release 1.6272 (September 2026)
		),
		array(
			'title'   => __( 'Traffic Attack Detector', 'wpshadow' ),
			'desc'    => __( 'Watches for sudden traffic spikes that could be attacks trying to overwhelm your site (like spotting a flash mob at your store). Free: Basic monitoring.', 'wpshadow' ),
			'tool'    => 'ddos-detection',
			'icon'    => 'dashicons-shield',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
			'since'   => '1.6364.1200', // Release 1.6364 (December 2026)
		),
		array(
			'title'   => __( 'Worldwide Speed Test', 'wpshadow' ),
			'desc'    => __( 'Tests how fast your site loads from different countries (like having friends around the world test it for you). Free: 5 locations, 3 tests per day.', 'wpshadow' ),
			'tool'    => 'global-performance',
			'icon'    => 'dashicons-performance',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
			'since'   => '1.6364.1200', // Release 1.6364 (December 2026)
		),
		array(
			'title'   => __( 'Search Position Tracker', 'wpshadow' ),
			'desc'    => __( 'Tracks where your site appears in Google search results for important words (like checking your store\'s ranking on Yelp). Free: 10 keywords.', 'wpshadow' ),
			'tool'    => 'keyword-tracker',
			'icon'    => 'dashicons-chart-line',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
			'since'   => '1.6364.1200', // Release 1.6364 (December 2026)
		),
		array(
			'title'   => __( 'Outside Link Tester', 'wpshadow' ),
			'desc'    => __( 'Checks if your links work from the outside (like Google sees them), catches redirects and broken pages. Free: 500 links per month.', 'wpshadow' ),
			'tool'    => 'external-link-checker',
			'icon'    => 'dashicons-admin-links',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
			'since'   => '1.6364.1200', // Release 1.6364 (December 2026)
		),
		array(
			'title'   => __( 'Writing Suggestions', 'wpshadow' ),
			'desc'    => __( 'Get suggestions to improve your writing while you type (like having a helpful editor looking over your shoulder). Free: 10 suggestions per day.', 'wpshadow' ),
			'tool'    => 'ai-writing-assistant',
			'icon'    => 'dashicons-edit',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
			'since'   => '1.6180.1200', // Release 1.6180 (June 2026)
		),
		array(
			'title'   => __( 'Language Translator', 'wpshadow' ),
			'desc'    => __( 'Translates your content into other languages accurately (better than basic online translators). Free: 10,000 words per month.', 'wpshadow' ),
			'tool'    => 'ai-translation',
			'icon'    => 'dashicons-translation',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
			'since'   => '1.6150.1200', // Release 1.6150 (May 2026)
		),
		array(
			'title'   => __( 'Visitor Help Chat', 'wpshadow' ),
			'desc'    => __( 'Answers visitor questions automatically using your site\'s content (like having a helpful assistant available 24/7). Free: 100 conversations per month.', 'wpshadow' ),
			'tool'    => 'ai-chatbot',
			'icon'    => 'dashicons-format-chat',
			'family'  => 'cloud-tools',
			'enabled' => true,
			'requires_cloud' => true,
			'since'   => '1.6119.1200', // Release 1.6119 (April 2026)
		),

		array(
			'title'   => __( 'WPShadow Vault Light', 'wpshadow' ),
			'desc'    => __( 'Schedule automatic backups and manage saved copies of your site (like keeping spare copies of important files).', 'wpshadow' ),
			'tool'    => 'vault-light',
			'icon'    => 'dashicons-backup',
			'family'  => 'site-management',
			'enabled' => true,
			'since'   => '1.6035.2150', // Release 1.6035 (February 2026)
		),
		array(
			'title'   => __( 'Dark Mode', 'wpshadow' ),
			'desc'    => __( 'Switch to dark colors for easier nighttime viewing (easier on your eyes in low light).', 'wpshadow' ),
			'tool'    => 'dark-mode',
			'icon'    => 'dashicons-visibility',
			'family'  => 'site-management',
			'enabled' => true,
			'since'   => '1.6364.1200', // Release 1.6364 (December 2026)
		),
		array(
			'title'   => __( 'Profile Page Simplifier', 'wpshadow' ),
			'desc'    => __( 'Choose which profile page sections non-admin users can see, so the profile screen feels cleaner and less overwhelming.', 'wpshadow' ),
			'tool'    => 'profile-sections',
			'icon'    => 'dashicons-id-alt',
			'family'  => 'site-management',
			'enabled' => true,
			'since'   => '1.6030.2200',
		),
		array(
			'title'   => __( 'Email Delivery Checker', 'wpshadow' ),
			'desc'    => __( 'Test if your site can send emails properly and fix the sender name/address (like making sure your mail goes through).', 'wpshadow' ),
			'tool'    => 'email-test',
			'icon'    => 'dashicons-email',
			'family'  => 'site-management',
			'enabled' => true,
			'since'   => '1.6364.1200', // Release 1.6364 (December 2026)
		),
		array(
			'title'   => __( 'Temporary Support Access', 'wpshadow' ),
			'desc'    => __( 'Create one-time access links for support staff (like giving someone a key that only works once).', 'wpshadow' ),
			'tool'    => 'magic-link-support',
			'icon'    => 'dashicons-admin-users',
			'family'  => 'site-management',
			'enabled' => true,
			'since'   => '1.6364.1200', // Release 1.6364 (December 2026)
		),
		array(
			'title'   => __( 'Helpful Hints', 'wpshadow' ),
			'desc'    => __( 'Shows friendly tips throughout WordPress to help you understand what things do (like having a guide point things out).', 'wpshadow' ),
			'tool'    => 'tips-coach',
			'icon'    => 'dashicons-lightbulb',
			'family'  => 'site-management',
			'enabled' => true,
			'since'   => '1.6364.1200', // Release 1.6364 (December 2026)
		),
		array(
			'title'   => __( 'Time Zone Fixer', 'wpshadow' ),
			'desc'    => __( 'Makes sure your site shows the correct time everywhere (like setting all clocks to match).', 'wpshadow' ),
			'tool'    => 'timezone-alignment',
			'icon'    => 'dashicons-clock',
			'family'  => 'site-management',
			'enabled' => true,
			'since'   => '1.6364.1200', // Release 1.6364 (December 2026)
		),
		array(
			'title'   => __( 'Script Speed Analyzer', 'wpshadow' ),
			'desc'    => __( 'Shows which code files are slowing down your site (like finding which apps drain your phone battery).', 'wpshadow' ),
			'tool'    => 'asset-impact',
			'icon'    => 'dashicons-performance',
			'family'  => 'site-management',
			'enabled' => true,
			'since'   => '1.6180.1200', // Release 1.6180 (June 2026)
		),
		array(
			'title'   => __( 'Broken Page Tracker', 'wpshadow' ),
			'desc'    => __( 'Finds pages that don\'t exist anymore and helps you redirect visitors to the right place (like fixing broken road signs).', 'wpshadow' ),
			'tool'    => '404-monitor',
			'icon'    => 'dashicons-warning',
			'family'  => 'site-management',
			'enabled' => true,
			'since'   => '1.6035.2150', // Release 1.6035 (February 2026)
		),
		array(
			'title'   => __( 'Safe Update Checker', 'wpshadow' ),
			'desc'    => __( 'Checks if it\'s safe to update WordPress and makes a backup first (like testing the water before jumping in).', 'wpshadow' ),
			'tool'    => 'update-safety',
			'icon'    => 'dashicons-update',
			'family'  => 'site-management',
			'enabled' => true,
			'since'   => '1.6364.1200', // Release 1.6364 (December 2026)
		),

		// Killer Utilities (Added 1.6030.2200)
		array(
			'title'   => __( 'Site Duplicator', 'wpshadow' ),
			'desc'    => __( 'Makes an exact copy of your site for testing changes safely (like having a practice version before making real changes). Free: 2 copies.', 'wpshadow' ),
			'tool'    => 'site-cloner',
			'icon'    => 'dashicons-admin-site-alt3',
			'family'  => 'site-management',
			'enabled' => true,
			'since'   => '1.6364.1200', // Release 1.6364 (December 2026)
		),
		array(
			'title'   => __( 'Code Addition Tool', 'wpshadow' ),
			'desc'    => __( 'Add custom code safely without editing theme files (like adding notes that won\'t break anything). Free: 10 code snippets.', 'wpshadow' ),
			'tool'    => 'code-snippets',
			'icon'    => 'dashicons-editor-code',
			'family'  => 'site-management',
			'enabled' => true,
			'since'   => '1.6364.1200', // Release 1.6364 (December 2026)
		),
		array(
			'title'   => __( 'Plugin Problem Finder', 'wpshadow' ),
			'desc'    => __( 'Finds which plugin is causing problems by testing them one at a time (like finding which bulb is making the lights flicker).', 'wpshadow' ),
			'tool'    => 'plugin-conflict',
			'icon'    => 'dashicons-admin-plugins',
			'family'  => 'site-management',
			'enabled' => true,
			'since'   => '1.6364.1200', // Release 1.6364 (December 2026)
		),
		array(
			'title'   => __( 'Text Search & Replace', 'wpshadow' ),
			'desc'    => __( 'Find and replace text across your entire site at once (like using "Find & Replace" in a word processor but for everything).', 'wpshadow' ),
			'tool'    => 'bulk-find-replace',
			'icon'    => 'dashicons-search',
			'family'  => 'site-management',
			'enabled' => true,
			'since'   => '1.6272.1200', // Release 1.6272 (September 2026)
		),
		array(
			'title'   => __( 'Image Size Regenerator', 'wpshadow' ),
			'desc'    => __( 'Recreates all your image sizes when needed (like reprinting photos in new sizes after changing frames).', 'wpshadow' ),
			'tool'    => 'regenerate-thumbnails',
			'icon'    => 'dashicons-image-rotate',
			'family'  => 'site-management',
			'enabled' => true,
			'since'   => '1.6364.1200', // Release 1.6364 (December 2026)
		),
		array(
			'title'   => __( 'Privacy Manager', 'wpshadow' ),
			'desc'    => __( 'Handle user data requests like downloads and deletions (helps you follow privacy laws).', 'wpshadow' ),
			'tool'    => 'privacy-dashboard',
			'icon'    => 'dashicons-lock',
			'family'  => 'site-management',
			'enabled' => true,
			'since'   => '1.6364.1200', // Release 1.6364 (December 2026)
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
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
		if ( 'backup' === $tab ) {
			$tab = 'vault-light';
		}

		// Get utilities catalog and filter by feature status
		$all_utilities = wpshadow_get_utilities_catalog();
		$catalog = wpshadow_filter_features_by_status( $all_utilities );

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

			// If the utility is not found or disabled, show a message.
			if ( ! $found_utility || empty( $found_utility['enabled'] ) ) {
				?>
				<div class="wrap wps-page-container">
					<?php wpshadow_render_page_header(
						__( 'WPShadow Utilities', 'wpshadow' ),
						__( 'Extra tools to help you manage, test, and improve your site.', 'wpshadow' ),
						'dashicons-admin-tools'
					); ?>

					<?php
					wpshadow_render_card(
						array(
							'card_class' => 'wps-card--warning',
							'body'       => function() {
								?>
								<p>
									<?php esc_html_e( 'That tool isn\'t available right now. You might have clicked an old link.', 'wpshadow' ); ?>
								</p>
								<p>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-utilities' ) ); ?>" class="wps-btn wps-btn--secondary">
										&larr; <?php esc_html_e( 'Back to Utilities', 'wpshadow' ); ?>
									</a>
								</p>
								<?php
							},
						)
					);
					?>
				</div>
				<?php
				return;
			}

			// Load and render the utility
			$utility_file = WPSHADOW_PATH . 'includes/ui/tools/' . $tab . '.php';
			if ( file_exists( $utility_file ) ) {
				require $utility_file;
				return;
			}

			// Fallback if file doesn't exist
			?>
			<div class="wrap wps-page-container">
				<?php wpshadow_render_page_header(
					esc_html( $found_utility['title'] ),
					'',
					'dashicons-admin-tools'
				); ?>

				<?php
				wpshadow_render_card(
					array(
						'card_class' => 'wps-card--error',
						'body'       => function() {
							?>
							<p>
								<?php esc_html_e( 'We couldn\'t load this tool. The file might be missing or moved.', 'wpshadow' ); ?>
							</p>
							<p>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-utilities' ) ); ?>" class="wps-btn wps-btn--secondary">
									&larr; <?php esc_html_e( 'Back to Utilities', 'wpshadow' ); ?>
								</a>
							</p>
							<?php
							},
						)
					);
					?>
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
	<div class="wrap wps-page-container">
		<!-- Page Header -->
		<?php wpshadow_render_page_header(
			__( 'WPShadow Utilities', 'wpshadow' ),
			__( 'Extra tools to help you manage, test, and improve your site.', 'wpshadow' ),
			'dashicons-admin-tools'
		); ?>

		<?php foreach ( $families as $family_key => $family_items ) : ?>
			<?php if ( ! empty( $family_items ) ) : ?>
				<!-- Family Section -->
				<div class="wps-utilities-family" style="margin-bottom: 40px;">
					<h2 style="font-size: 20px; margin-bottom: 15px; color: #1d2327;">
						<?php echo esc_html( isset( $family_titles[ $family_key ] ) ? $family_titles[ $family_key ] : ucwords( str_replace( '-', ' ', $family_key ) ) ); ?>
					</h2>

					<!-- Utilities Grid -->
					<div class="wps-grid wps-grid-cols-2" style="gap: var(--wps-space-6);">
						<?php
						foreach ( $family_items as $item ) :
							$icon_class = ! empty( $item['icon'] ) ? $item['icon'] : 'dashicons-admin-generic';
							$utility_url = admin_url( 'admin.php?page=wpshadow-utilities&tab=' . $item['tool'] );

							// Get feature status
							$feature_status = wpshadow_get_feature_status( $item['since'] );
							$is_coming_soon = ( 'coming_soon' === $feature_status['status'] );
							$card_class     = ( ! empty( $item['enabled'] ) && ! $is_coming_soon ) ? 'wps-card--link' : '';
							$card_attrs     = array();
							$width_class    = '';
							$card_width     = $item['width'] ?? 'half';
							if ( 'full' === $card_width ) {
								$width_class = 'wps-grid-span-full';
							} elseif ( 'half' === $card_width ) {
								$width_class = 'wps-grid-span-half';
							}
							$card_class = trim( $card_class . ' ' . $width_class );

							if ( ! empty( $item['enabled'] ) && ! $is_coming_soon ) {
								$card_attrs = array(
									'data-utility-url' => $utility_url,
									'role'             => 'link',
									'tabindex'         => '0',
									'aria-label'       => sprintf( __( 'Open %s utility', 'wpshadow' ), $item['title'] ),
								);
							}

							$badge = array();
							if ( $is_coming_soon ) {
								$badge = array(
									'label' => sprintf( __( 'Available %s', 'wpshadow' ), $feature_status['launch_date'] ),
									'class' => 'wps-badge--info',
								);
							}
							?>
							<?php
							wpshadow_render_card(
								array(
									'title'       => $item['title'],
									'title_url'   => ( ! empty( $item['enabled'] ) && ! $is_coming_soon ) ? $utility_url : '',
									'description' => $item['desc'],
									'icon'        => $icon_class,
									'icon_class'  => 'wps-text-primary',
									'badge'       => $badge,
									'card_class'  => $card_class,
									'attrs'       => $card_attrs,
									'body'        => function() use ( $is_coming_soon, $item, $utility_url ) {
										?>
										<?php if ( $is_coming_soon ) : ?>
											<button class="wps-btn wps-btn--secondary" disabled>
												<span class="dashicons dashicons-hourglass"></span>
												<?php esc_html_e( 'Coming Soon', 'wpshadow' ); ?>
											</button>
										<?php elseif ( ! empty( $item['enabled'] ) ) : ?>
											<a href="<?php echo esc_url( $utility_url ); ?>" class="wps-btn wps-btn--secondary">
												<span class="dashicons dashicons-arrow-right-alt2"></span>
												<?php esc_html_e( 'Open This Tool', 'wpshadow' ); ?>
											</a>
										<?php else : ?>
											<button class="wps-btn wps-btn--secondary" disabled>
												<span class="dashicons dashicons-hourglass"></span>
												<?php esc_html_e( 'Available Soon', 'wpshadow' ); ?>
											</button>
										<?php endif; ?>
										<?php
									},
								)
							);
							?>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>

		<!-- Activity History Section -->
		<div style="margin-top: 60px; border-top: 1px solid #e0e0e0; padding-top: 40px;">
			<?php
			if ( function_exists( 'wpshadow_render_page_activities' ) ) {
				wpshadow_render_page_activities( 'tools', 10 );
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
			_deprecated_function( __FUNCTION__, '1.6030.2200', 'wpshadow_render_utilities' );
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
