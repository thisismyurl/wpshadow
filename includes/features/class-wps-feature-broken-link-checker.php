<?php
/**
 * Broken Link Checker feature definition.
 *
 * Automatically crawls HTML and CSS to find "404 Not Found" errors
 * or non-working outbound links.
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Broken_Link_Checker extends WPSHADOW_Abstract_Feature {
	/**
	 * Initialize the feature.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'broken-link-checker',
				'name'               => __( 'Broken Link Checker', 'plugin-wpshadow' ),
				'description'        => __( 'Automatically crawls your HTML and CSS to find "404 Not Found" errors or non-working outbound links. Prevents "link rot" which frustrates users and negatively impacts search rankings.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'version'            => '1.0.0',
				'default_enabled'    => false,
				'widget_group'       => 'diagnostics',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'category'           => 'seo',
				'icon'               => 'dashicons-admin-links',
				'priority'           => 15,
			)
		);
	}

	/**
	 * Enable details page for this feature.
	 *
	 * @return bool
	 */
	public function has_details_page(): bool {
		return true;
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Register Site Health test.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		$this->log_activity( 'feature_initialized', 'Broken Link Checker initialized', 'info' );
	}

	/**
	 * Initialize the broken link checker.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Register hooks only if feature is enabled
		if ( ! self::is_enabled() ) {
			return;
		}

		// Schedule periodic link checking
		add_action( 'wpshadow_check_broken_links', array( __CLASS__, 'check_all_links' ) );
		
		// Register AJAX handlers
		add_action( 'wp_ajax_wpshadow_check_links_now', array( __CLASS__, 'ajax_check_links_now' ) );
		add_action( 'wp_ajax_wpshadow_dismiss_broken_link', array( __CLASS__, 'ajax_dismiss_broken_link' ) );
		
		// Schedule cron if not already scheduled
		if ( ! wp_next_scheduled( 'wpshadow_check_broken_links' ) ) {
			wp_schedule_event( time(), 'daily', 'wpshadow_check_broken_links' );
		}
	}

	/**
	 * Render the admin page.
	 *
	 * @return void
	 */
	public static function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You don\'t have permission to access this page.', 'plugin-wpshadow' ) );
		}

		$broken_links = self::get_broken_links();
		$last_check   = get_option( 'wpshadow_broken_links_last_check', 0 );
		
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Broken Link Checker', 'plugin-wpshadow' ); ?></h1>
			
			<div class="wpshadow-broken-links-header" style="margin: 20px 0;">
				<p><?php esc_html_e( 'This tool automatically scans your posts, pages, and CSS files for broken links and 404 errors.', 'plugin-wpshadow' ); ?></p>
				
				<?php if ( $last_check ) : ?>
					<p>
						<strong><?php esc_html_e( 'Last check:', 'plugin-wpshadow' ); ?></strong>
						<?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_check ) ); ?>
					</p>
				<?php endif; ?>
				
				<p>
					<button type="button" id="wpshadow-check-links-now" class="button button-primary">
						<?php esc_html_e( 'Check Links Now', 'plugin-wpshadow' ); ?>
					</button>
					<span class="spinner" style="float: none; margin: 0 10px;"></span>
					<span id="wpshadow-check-status"></span>
				</p>
			</div>

			<?php if ( empty( $broken_links ) ) : ?>
				<div class="notice notice-success">
					<p><?php esc_html_e( 'No broken links found! Your site is in good shape.', 'plugin-wpshadow' ); ?></p>
				</div>
			<?php else : ?>
				<div class="notice notice-warning">
					<p>
						<?php
						printf(
							/* translators: %d: number of broken links */
							esc_html( _n( '%d broken link found.', '%d broken links found.', count( $broken_links ), 'plugin-wpshadow' ) ),
							count( $broken_links )
						);
						?>
					</p>
				</div>

				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'URL', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Found In', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Status', 'plugin-wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'plugin-wpshadow' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $broken_links as $link ) : ?>
							<tr>
								<td>
									<a href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html( $link['url'] ); ?>
									</a>
								</td>
								<td>
									<?php if ( ! empty( $link['post_id'] ) ) : ?>
										<a href="<?php echo esc_url( get_edit_post_link( $link['post_id'] ) ); ?>">
											<?php echo esc_html( get_the_title( $link['post_id'] ) ); ?>
										</a>
									<?php else : ?>
										<?php echo esc_html( $link['source'] ?? __( 'Unknown', 'plugin-wpshadow' ) ); ?>
									<?php endif; ?>
								</td>
								<td>
									<span class="wpshadow-link-status wpshadow-link-status-<?php echo esc_attr( $link['status_code'] ); ?>">
										<?php echo esc_html( $link['status_code'] . ' - ' . $link['status_text'] ); ?>
									</span>
								</td>
								<td>
									<button type="button" class="button button-small wpshadow-dismiss-link" data-link-id="<?php echo esc_attr( $link['id'] ); ?>">
										<?php esc_html_e( 'Dismiss', 'plugin-wpshadow' ); ?>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<style>
			.wpshadow-link-status {
				padding: 3px 8px;
				border-radius: 3px;
				font-weight: 500;
			}
			.wpshadow-link-status-404 {
				background: #dc3232;
				color: #fff;
			}
			.wpshadow-link-status-500,
			.wpshadow-link-status-502,
			.wpshadow-link-status-503 {
				background: #f56e28;
				color: #fff;
			}
		</style>

		<script>
		jQuery(document).ready(function($) {
			$('#wpshadow-check-links-now').on('click', function() {
				var $btn = $(this);
				var $spinner = $btn.next('.spinner');
				var $status = $('#wpshadow-check-status');
				
				$btn.prop('disabled', true);
				$spinner.addClass('is-active');
				$status.text('<?php esc_html_e( 'Checking links...', 'plugin-wpshadow' ); ?>');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wpshadow_check_links_now',
						nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_check_links' ) ); ?>'
					},
					success: function(response) {
						if (response.success) {
							$status.text(response.data.message);
							setTimeout(function() {
								location.reload();
							}, 1000);
						} else {
							$status.text(response.data.message || '<?php esc_html_e( 'Error checking links.', 'plugin-wpshadow' ); ?>');
						}
					},
					error: function() {
						$status.text('<?php esc_html_e( 'Error checking links.', 'plugin-wpshadow' ); ?>');
					},
					complete: function() {
						$btn.prop('disabled', false);
						$spinner.removeClass('is-active');
					}
				});
			});

			$('.wpshadow-dismiss-link').on('click', function() {
				var $btn = $(this);
				var linkId = $btn.data('link-id');
				
				$btn.prop('disabled', true);
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wpshadow_dismiss_broken_link',
						nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_dismiss_link' ) ); ?>',
						link_id: linkId
					},
					success: function(response) {
						if (response.success) {
							$btn.closest('tr').fadeOut(function() {
								$(this).remove();
								if ($('.wp-list-table tbody tr').length === 0) {
									location.reload();
								}
							});
						}
					},
					complete: function() {
						$btn.prop('disabled', false);
					}
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * AJAX handler to check links now.
	 *
	 * @return void
	 */
	public static function ajax_check_links_now(): void {
		check_ajax_referer( 'wpshadow_check_links', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wpshadow' ) ) );
		}

		$checked = self::check_all_links();

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %d: number of links checked */
					__( 'Checked %d links.', 'plugin-wpshadow' ),
					$checked
				),
			)
		);
	}

	/**
	 * AJAX handler to dismiss a broken link.
	 *
	 * @return void
	 */
	public static function ajax_dismiss_broken_link(): void {
		check_ajax_referer( 'wpshadow_dismiss_link', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wpshadow' ) ) );
		}

		$link_id = isset( $_POST['link_id'] ) ? sanitize_text_field( wp_unslash( $_POST['link_id'] ) ) : '';

		if ( ! empty( $link_id ) ) {
			self::dismiss_broken_link( $link_id );
			wp_send_json_success();
		}

		wp_send_json_error( array( 'message' => __( 'Invalid link ID.', 'plugin-wpshadow' ) ) );
	}

	/**
	 * Check all links in posts, pages, and CSS files.
	 *
	 * @return int Number of links checked.
	 */
	public static function check_all_links(): int {
		global $wpdb;

		// Clear existing broken links
		delete_option( 'wpshadow_broken_links' );

		$broken_links = array();
		$checked      = 0;

		// Get all published posts and pages
		$posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		foreach ( $posts as $post_id ) {
			$post = get_post( $post_id );
			if ( ! $post ) {
				continue;
			}

			// Extract links from content
			$links = self::extract_links_from_content( $post->post_content );

			foreach ( $links as $url ) {
				$checked++;
				$status = self::check_link_status( $url );

				if ( $status['is_broken'] ) {
					$broken_links[] = array(
						'id'          => md5( $url . $post_id ),
						'url'         => $url,
						'post_id'     => $post_id,
						'source'      => 'content',
						'status_code' => $status['code'],
						'status_text' => $status['message'],
					);
				}
			}
		}

		// Store broken links
		update_option( 'wpshadow_broken_links', $broken_links );
		update_option( 'wpshadow_broken_links_last_check', time() );

		// Log activity
		if ( class_exists( '\\WPShadow\\CoreSupport\\WPSHADOW_Activity_Logger' ) ) {
			\WPShadow\CoreSupport\WPSHADOW_Activity_Logger::log(
				'broken_link_checker',
				sprintf(
					/* translators: 1: number of links checked, 2: number of broken links */
					__( 'Checked %1$d links and found %2$d broken links.', 'plugin-wpshadow' ),
					$checked,
					count( $broken_links )
				),
				array(
					'links_checked' => $checked,
					'broken_links'  => count( $broken_links ),
				),
				'broken_link_checker'
			);
		}

		return $checked;
	}

	/**
	 * Extract links from HTML content.
	 *
	 * @param string $content HTML content.
	 * @return array Array of URLs.
	 */
	private static function extract_links_from_content( string $content ): array {
		$links = array();

		// Extract <a> tag hrefs
		if ( preg_match_all( '/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $content, $matches ) ) {
			$links = array_merge( $links, $matches[1] );
		}

		// Extract <img> tag srcs
		if ( preg_match_all( '/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches ) ) {
			$links = array_merge( $links, $matches[1] );
		}

		// Extract <link> tag hrefs (for CSS)
		if ( preg_match_all( '/<link[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $content, $matches ) ) {
			$links = array_merge( $links, $matches[1] );
		}

		// Filter out internal anchors, javascript, and mailto links
		$links = array_filter(
			$links,
			function ( $url ) {
				return ! empty( $url )
					&& ! str_starts_with( $url, '#' )
					&& ! str_starts_with( $url, 'javascript:' )
					&& ! str_starts_with( $url, 'mailto:' );
			}
		);

		return array_unique( $links );
	}

	/**
	 * Check if a link is broken.
	 *
	 * @param string $url URL to check.
	 * @return array Status information.
	 */
	private static function check_link_status( string $url ): array {
		// Skip checking internal relative URLs
		if ( ! str_starts_with( $url, 'http://' ) && ! str_starts_with( $url, 'https://' ) ) {
			$url = home_url( $url );
		}

		$response = wp_remote_head(
			$url,
			array(
				'timeout'     => 10,
				'redirection' => 5,
				'user-agent'  => 'WPShadow Broken Link Checker',
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'is_broken' => true,
				'code'      => 0,
				'message'   => $response->get_error_message(),
			);
		}

		$code = wp_remote_retrieve_response_code( $response );

		// Consider 404, 500, 502, 503 as broken
		$is_broken = in_array( $code, array( 404, 500, 502, 503 ), true );

		return array(
			'is_broken' => $is_broken,
			'code'      => $code,
			'message'   => wp_remote_retrieve_response_message( $response ),
		);
	}

	/**
	 * Get broken links.
	 *
	 * @return array Array of broken links.
	 */
	private static function get_broken_links(): array {
		return get_option( 'wpshadow_broken_links', array() );
	}

	/**
	 * Dismiss a broken link.
	 *
	 * @param string $link_id Link ID to dismiss.
	 * @return void
	 */
	private static function dismiss_broken_link( string $link_id ): void {
		$broken_links = self::get_broken_links();

		foreach ( $broken_links as $key => $link ) {
			if ( isset( $link['id'] ) && $link['id'] === $link_id ) {
				unset( $broken_links[ $key ] );
				break;
			}
		}

		update_option( 'wpshadow_broken_links', array_values( $broken_links ) );
	}
}
