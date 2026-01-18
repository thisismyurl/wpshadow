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
				'name'               => __( 'Broken Link Checker', 'wpshadow' ),
				'description'        => __( 'Find and fix broken links that frustrate visitors and hurt your search rankings "404 Not Found" errors or non-working outbound links. Prevents "link rot" which frustrates users and negatively impacts search rankings.', 'wpshadow' ),
				'scope'              => 'core',
				'version'            => '1.0.0',
				'default_enabled'    => false,
			'widget_group'       => 'maintenance-tools',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'category'           => 'seo',
				'icon'               => 'dashicons-admin-links',
				'priority'           => 15,
				'aliases'            => array(
					'dead links',
					'404 errors',
					'link validation',
					'url checking',
					'seo audit',
					'broken urls',
					'link rot',
				),
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

		// Register settings metabox on feature details page
		add_action( 'wpshadow_feature_details_metaboxes', array( __CLASS__, 'register_settings_metabox' ), 10, 3 );

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
		add_action( 'wp_ajax_wpshadow_save_blc_settings', array( __CLASS__, 'ajax_save_settings' ) );
		
		// Register publishing assistant reviewer
		add_action( 'init', array( __CLASS__, 'register_publishing_assistant_reviewer' ) );
		
		// Schedule cron based on settings
		self::schedule_link_checker();
	}

	/**
	 * Schedule link checker based on settings.
	 *
	 * @return void
	 */
	private static function schedule_link_checker(): void {
		$frequency = self::get_blc_setting( 'check_frequency', 'daily' );
		$existing  = wp_next_scheduled( 'wpshadow_check_broken_links' );

		// Remove existing schedule if different
		if ( $existing && $frequency !== get_option( 'wpshadow_blc_last_frequency', 'daily' ) ) {
			wp_unschedule_event( $existing, 'wpshadow_check_broken_links' );
			$existing = false;
		}

		// Schedule if not already scheduled
		if ( ! $existing ) {
			wp_schedule_event( time(), $frequency, 'wpshadow_check_broken_links' );
			update_option( 'wpshadow_blc_last_frequency', $frequency );
		}
	}

	/**
	 * Register broken link checker as publishing assistant reviewer.
	 *
	 * @return void
	 */
	public static function register_publishing_assistant_reviewer(): void {
		if ( ! class_exists( 'WPSHADOW_Publishing_Assistant' ) ) {
			return;
		}

		WPSHADOW_Publishing_Assistant::register_reviewer(
			'broken-link-checker',
			array(
				'name'        => __( 'Link Checker', 'wpshadow' ),
				'description' => __( 'Verify all links in this post are working', 'wpshadow' ),
				'priority'    => 5,
				'icon'        => 'dashicons-admin-links',
				'post_types'  => array( 'post', 'page' ),
				'callback'    => array( __CLASS__, 'review_post_links' ),
			)
		);
	}

	/**
	 * Publishing assistant callback to review links in a post.
	 *
	 * @param \WP_Post $post Post object.
	 * @return array Review result.
	 */
	public static function review_post_links( \WP_Post $post ): array {
		$links = self::extract_links_from_content( $post->post_content );

		if ( empty( $links ) ) {
			return array(
				'status'  => 'success',
				'message' => __( 'No links found in this post.', 'wpshadow' ),
				'count'   => 0,
			);
		}

		$broken_links = array();
		$check_external = self::get_blc_setting( 'check_external', true );
		$check_internal = self::get_blc_setting( 'check_internal', true );

		foreach ( $links as $url ) {
			// Filter by internal/external setting
			$is_external = ! self::is_internal_url( $url );
			
			if ( ! $check_external && $is_external ) {
				continue;
			}
			
			if ( ! $check_internal && ! $is_external ) {
				continue;
			}

			$status = self::check_link_status( $url );

			if ( $status['is_broken'] ) {
				$broken_links[] = array(
					'url'    => $url,
					'code'   => $status['code'],
					'status' => $status['message'],
				);
			}
		}

		if ( empty( $broken_links ) ) {
			return array(
				'status'  => 'success',
				'message' => sprintf(
					/* translators: %d: number of links checked */
					__( 'Checked %d links. All links are working.', 'wpshadow' ),
					count( $links )
				),
				'count'   => 0,
			);
		}

		return array(
			'status'  => 'warning',
			'message' => sprintf(
				/* translators: 1: number of broken links, 2: total links checked */
				__( 'Found %1$d broken link(s) out of %2$d checked.', 'wpshadow' ),
				count( $broken_links ),
				count( $links )
			),
			'count'   => count( $broken_links ),
			'items'   => $broken_links,
		);
	}

	/**
	 * Check if a URL is internal to the current site.
	 *
	 * @param string $url URL to check.
	 * @return bool True if internal.
	 */
	private static function is_internal_url( string $url ): bool {
		$home_url = home_url();
		$site_url = site_url();

		return str_starts_with( $url, $home_url ) || 
		       str_starts_with( $url, $site_url ) || 
		       str_starts_with( $url, '/' );
	}

	/**
	 * Render the admin page.
	 *
	 * @return void
	 */
	public static function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You don\'t have permission to access this page.', 'wpshadow' ) );
		}

		$broken_links = self::get_broken_links();
		$last_check   = get_option( 'wpshadow_broken_links_last_check', 0 );
		
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Broken Link Checker', 'wpshadow' ); ?></h1>
			
			<div class="wpshadow-broken-links-header" style="margin: 20px 0;">
				<p><?php esc_html_e( 'This tool automatically scans your posts, pages, and CSS files for broken links and 404 errors.', 'wpshadow' ); ?></p>
				
				<?php if ( $last_check ) : ?>
					<p>
						<strong><?php esc_html_e( 'Last check:', 'wpshadow' ); ?></strong>
						<?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_check ) ); ?>
					</p>
				<?php endif; ?>
				
				<p>
					<button type="button" id="wpshadow-check-links-now" class="button button-primary">
						<?php esc_html_e( 'Check Links Now', 'wpshadow' ); ?>
					</button>
					<span class="spinner" style="float: none; margin: 0 10px;"></span>
					<span id="wpshadow-check-status"></span>
				</p>
			</div>

			<?php if ( empty( $broken_links ) ) : ?>
				<div class="notice notice-success">
					<p><?php esc_html_e( 'No broken links found! Your site is in good shape.', 'wpshadow' ); ?></p>
				</div>
			<?php else : ?>
				<div class="notice notice-warning">
					<p>
						<?php
						printf(
							/* translators: %d: number of broken links */
							esc_html( _n( '%d broken link found.', '%d broken links found.', count( $broken_links ), 'wpshadow' ) ),
							count( $broken_links )
						);
						?>
					</p>
				</div>

				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'URL', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Found In', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Status', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'wpshadow' ); ?></th>
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
										<?php echo esc_html( $link['source'] ?? __( 'Unknown', 'wpshadow' ) ); ?>
									<?php endif; ?>
								</td>
								<td>
									<span class="wpshadow-link-status wpshadow-link-status-<?php echo esc_attr( $link['status_code'] ); ?>">
										<?php echo esc_html( $link['status_code'] . ' - ' . $link['status_text'] ); ?>
									</span>
								</td>
								<td>
									<button type="button" class="button button-small wpshadow-dismiss-link" data-link-id="<?php echo esc_attr( $link['id'] ); ?>">
										<?php esc_html_e( 'Dismiss', 'wpshadow' ); ?>
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
				$status.text('<?php esc_html_e( 'Checking links...', 'wpshadow' ); ?>');
				
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
							$status.text(response.data.message || '<?php esc_html_e( 'Error checking links.', 'wpshadow' ); ?>');
						}
					},
					error: function() {
						$status.text('<?php esc_html_e( 'Error checking links.', 'wpshadow' ); ?>');
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
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$checked = self::check_all_links();

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %d: number of links checked */
					__( 'Checked %d links.', 'wpshadow' ),
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
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$link_id = isset( $_POST['link_id'] ) ? sanitize_text_field( wp_unslash( $_POST['link_id'] ) ) : '';

		if ( ! empty( $link_id ) ) {
			self::dismiss_broken_link( $link_id );
			wp_send_json_success();
		}

		wp_send_json_error( array( 'message' => __( 'That link doesn\'t exist.', 'wpshadow' ) ) );
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
					__( 'Checked %1$d links and found %2$d broken links.', 'wpshadow' ),
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

	/**
	 * Get a feature setting.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Default value.
	 * @return mixed Setting value.
	 */
	public static function get_blc_setting( string $key, $default = null ) {
		$settings = get_option( 'wpshadow_blc_settings', array() );
		return $settings[ $key ] ?? $default;
	}

	/**
	 * Update a feature setting.
	 *
	 * @param string $key   Setting key.
	 * @param mixed  $value Setting value.
	 * @return bool True if updated.
	 */
	public static function update_blc_setting( string $key, $value ): bool {
		$settings        = get_option( 'wpshadow_blc_settings', array() );
		$settings[ $key ] = $value;
		return update_option( 'wpshadow_blc_settings', $settings );
	}

	/**
	 * AJAX handler to save settings.
	 *
	 * @return void
	 */
	public static function ajax_save_settings(): void {
		check_ajax_referer( 'wpshadow_blc_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$check_frequency  = isset( $_POST['check_frequency'] ) ? sanitize_key( wp_unslash( $_POST['check_frequency'] ) ) : 'daily';
		$check_external   = isset( $_POST['check_external'] ) ? (bool) $_POST['check_external'] : true;
		$check_internal   = isset( $_POST['check_internal'] ) ? (bool) $_POST['check_internal'] : true;
		$off_hours_enabled = isset( $_POST['off_hours_enabled'] ) ? (bool) $_POST['off_hours_enabled'] : false;
		$off_hours_start  = isset( $_POST['off_hours_start'] ) ? sanitize_text_field( wp_unslash( $_POST['off_hours_start'] ) ) : '22:00';
		$off_hours_end    = isset( $_POST['off_hours_end'] ) ? sanitize_text_field( wp_unslash( $_POST['off_hours_end'] ) ) : '06:00';

		// Validate frequency
		$valid_frequencies = array( 'hourly', 'twicedaily', 'daily' );
		if ( ! in_array( $check_frequency, $valid_frequencies, true ) ) {
			$check_frequency = 'daily';
		}

		// Save settings
		self::update_blc_setting( 'check_frequency', $check_frequency );
		self::update_blc_setting( 'check_external', $check_external );
		self::update_blc_setting( 'check_internal', $check_internal );
		self::update_blc_setting( 'off_hours_enabled', $off_hours_enabled );
		self::update_blc_setting( 'off_hours_start', $off_hours_start );
		self::update_blc_setting( 'off_hours_end', $off_hours_end );

		// Reschedule cron with new frequency
		self::schedule_link_checker();

		wp_send_json_success(
			array(
				'message' => __( 'Settings saved successfully.', 'wpshadow' ),
			)
		);
	}

	/**
	 * Get settings panel for feature details page.
	 *
	 * @return string HTML for settings panel.
	 */
	public static function get_settings_panel(): string {
		$check_frequency   = self::get_blc_setting( 'check_frequency', 'daily' );
		$check_external    = self::get_blc_setting( 'check_external', true );
		$check_internal    = self::get_blc_setting( 'check_internal', true );
		$off_hours_enabled = self::get_blc_setting( 'off_hours_enabled', false );
		$off_hours_start   = self::get_blc_setting( 'off_hours_start', '22:00' );
		$off_hours_end     = self::get_blc_setting( 'off_hours_end', '06:00' );

		$nonce = wp_create_nonce( 'wpshadow_blc_nonce' );

		ob_start();
		?>
		<div class="wpshadow-blc-settings">
			<h3><?php esc_html_e( 'Broken Link Checker Settings', 'wpshadow' ); ?></h3>
			
			<table class="form-table">
				<tr>
					<th scope="row"><label for="check_frequency"><?php esc_html_e( 'Check Frequency', 'wpshadow' ); ?></label></th>
					<td>
						<select id="check_frequency" name="check_frequency">
							<option value="hourly" <?php selected( $check_frequency, 'hourly' ); ?>><?php esc_html_e( 'Hourly', 'wpshadow' ); ?></option>
							<option value="twicedaily" <?php selected( $check_frequency, 'twicedaily' ); ?>><?php esc_html_e( 'Twice Daily', 'wpshadow' ); ?></option>
							<option value="daily" <?php selected( $check_frequency, 'daily' ); ?>><?php esc_html_e( 'Daily', 'wpshadow' ); ?></option>
						</select>
						<p class="description"><?php esc_html_e( 'How often to automatically check for broken links.', 'wpshadow' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Link Type', 'wpshadow' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="check_internal" id="check_internal" value="1" <?php checked( $check_internal ); ?> />
							<?php esc_html_e( 'Check Internal Links', 'wpshadow' ); ?>
						</label>
						<br />
						<label>
							<input type="checkbox" name="check_external" id="check_external" value="1" <?php checked( $check_external ); ?> />
							<?php esc_html_e( 'Check External Links', 'wpshadow' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Select which types of links to scan.', 'wpshadow' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Off-Hours Checking', 'wpshadow' ); ?></th>
					<td>
						<label>
							<input type="checkbox" id="off_hours_enabled" name="off_hours_enabled" value="1" <?php checked( $off_hours_enabled ); ?> />
							<?php esc_html_e( 'Run checks only during off-hours', 'wpshadow' ); ?>
						</label>
						<div id="off-hours-settings" style="margin-top: 10px; padding: 10px; background: #f5f5f5; border-radius: 4px; <?php echo $off_hours_enabled ? '' : 'display: none;'; ?>">
							<p>
								<label for="off_hours_start"><?php esc_html_e( 'Start Time', 'wpshadow' ); ?></label><br />
								<input type="time" id="off_hours_start" name="off_hours_start" value="<?php echo esc_attr( $off_hours_start ); ?>" />
							</p>
							<p>
								<label for="off_hours_end"><?php esc_html_e( 'End Time', 'wpshadow' ); ?></label><br />
								<input type="time" id="off_hours_end" name="off_hours_end" value="<?php echo esc_attr( $off_hours_end ); ?>" />
							</p>
							<p class="description"><?php esc_html_e( 'Checks will only run between these hours (server timezone).', 'wpshadow' ); ?></p>
						</div>
					</td>
				</tr>
			</table>

			<p>
				<button type="button" class="button button-primary" id="save-blc-settings"><?php esc_html_e( 'Save Settings', 'wpshadow' ); ?></button>
				<span class="spinner" style="float: none; margin-left: 10px;"></span>
				<span id="blc-settings-status" style="margin-left: 10px;"></span>
			</p>

			<script>
			jQuery(document).ready(function($) {
				// Toggle off-hours settings visibility
				$('#off_hours_enabled').on('change', function() {
					$('#off-hours-settings').slideToggle();
				});

				// Save settings
				$('#save-blc-settings').on('click', function() {
					var $btn = $(this);
					var $spinner = $btn.next('.spinner');
					var $status = $('#blc-settings-status');

					$btn.prop('disabled', true);
					$spinner.addClass('is-active');
					$status.text('<?php esc_html_e( 'Saving...', 'wpshadow' ); ?>');

					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'wpshadow_save_blc_settings',
							nonce: '<?php echo esc_js( $nonce ); ?>',
							check_frequency: $('#check_frequency').val(),
							check_internal: $('#check_internal').is(':checked') ? 1 : 0,
							check_external: $('#check_external').is(':checked') ? 1 : 0,
							off_hours_enabled: $('#off_hours_enabled').is(':checked') ? 1 : 0,
							off_hours_start: $('#off_hours_start').val(),
							off_hours_end: $('#off_hours_end').val()
						},
						success: function(response) {
							if (response.success) {
								$status.text('<?php esc_html_e( 'Settings saved!', 'wpshadow' ); ?>').css('color', 'green');
								setTimeout(function() {
									$status.text('');
								}, 3000);
							} else {
								$status.text(response.data?.message || '<?php esc_html_e( 'Error saving settings.', 'wpshadow' ); ?>').css('color', 'red');
							}
						},
						error: function() {
							$status.text('<?php esc_html_e( 'Error saving settings.', 'wpshadow' ); ?>').css('color', 'red');
						},
						complete: function() {
							$btn.prop('disabled', false);
							$spinner.removeClass('is-active');
						}
					});
				});
			});
			</script>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Register settings metabox on feature details page.
	 *
	 * @param string $feature_id Feature ID.
	 * @param string $screen_id  Screen ID.
	 * @param array  $feature    Feature data.
	 * @return void
	 */
	public static function register_settings_metabox( string $feature_id, string $screen_id, array $feature ): void {
		if ( 'broken-link-checker' !== $feature_id ) {
			return;
		}

		add_meta_box(
			'wpshadow-blc-settings',
			__( 'Settings', 'wpshadow' ),
			array( __CLASS__, 'render_settings_metabox' ),
			$screen_id,
			'normal',
			'default'
		);
	}

	/**
	 * Render settings metabox.
	 *
	 * @return void
	 */
	public static function render_settings_metabox(): void {
		echo wp_kses_post( self::get_settings_panel() );
	}
}
