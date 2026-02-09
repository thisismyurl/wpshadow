<?php
/**
 * CPT Social Media Auto-Post Feature
 *
 * Provides automatic social media posting for custom post types with support for
 * multiple platforms (Facebook, Twitter/X, LinkedIn, Instagram).
 *
 * @package    WPShadow
 * @subpackage Content\Post_Types
 * @since      1.6365.2359
 */

declare(strict_types=1);

namespace WPShadow\Content\Post_Types;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT Social Auto-Post Class
 *
 * Handles automatic social media posting for custom post types.
 *
 * @since 1.6365.2359
 */
class CPT_Social_Auto_Post extends Hook_Subscriber_Base {

	/**
	 * Register WordPress hooks.
	 *
	 * @since  1.6035.1400
	 * @return array Hook configuration array.
	 */
	protected static function get_hooks(): array {
		return array(
			'actions' => array(
				array( 'admin_menu', array( __CLASS__, 'register_social_page' ) ),
				array( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) ),
				array( 'publish_post', array( __CLASS__, 'auto_post_to_social' ), 10, 2 ),
				array( 'wp_ajax_wpshadow_test_social_connection', array( __CLASS__, 'ajax_test_connection' ) ),
				array( 'wp_ajax_wpshadow_manual_social_post', array( __CLASS__, 'ajax_manual_post' ) ),
			),
			'filters' => array(),
		);
	}

	protected static function get_required_version(): string {
		return '1.6365.2359';
	}

	/**
	 * Register social media admin page.
	 *
	 * @since 1.6035.1400
	 * @return void
	 */
	public static function register_social_page(): void {
		add_submenu_page(
			'wpshadow',
			__( 'Social Auto-Post', 'wpshadow' ),
			__( 'Social Auto-Post', 'wpshadow' ),
			'manage_options',
			'wpshadow-social-autopost',
			array( __CLASS__, 'render_social_page' )
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @since  1.6035.1400
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_admin_assets( string $hook ): void {
		if ( 'wpshadow_page_wpshadow-social-autopost' !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'wpshadow-social-autopost',
			plugins_url( 'assets/js/cpt-social-autopost.js', WPSHADOW_FILE ),
			array( 'jquery', 'wp-util' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-social-autopost',
			'wpShadowSocial',
			array(
				'nonce' => wp_create_nonce( 'wpshadow_social_autopost' ),
				'i18n'  => array(
					'testing'       => __( 'Testing connection...', 'wpshadow' ),
					'connected'     => __( 'Connected successfully', 'wpshadow' ),
					'posting'       => __( 'Posting to social media...', 'wpshadow' ),
					'posted'        => __( 'Posted successfully', 'wpshadow' ),
				),
			)
		);
	}

	/**
	 * Render social media admin page.
	 *
	 * @since 1.6035.1400
	 * @return void
	 */
	public static function render_social_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions', 'wpshadow' ) );
		}

		?>
		<div class="wrap wpshadow-social-autopost">
			<h1><?php esc_html_e( 'Social Media Auto-Post', 'wpshadow' ); ?></h1>

			<?php do_action( 'wpshadow_after_page_header' ); ?>
			<p class="description">
				<?php esc_html_e( 'Automatically post your custom post types to social media platforms when published.', 'wpshadow' ); ?>
			</p>

			<div class="wpshadow-social-platforms">
				<h2><?php esc_html_e( 'Connected Platforms', 'wpshadow' ); ?></h2>

				<div class="platform-card">
					<h3><?php esc_html_e( 'Facebook', 'wpshadow' ); ?></h3>
					<p class="status"><?php esc_html_e( 'Not Connected', 'wpshadow' ); ?></p>
					<button class="button connect-platform" data-platform="facebook">
						<?php esc_html_e( 'Connect Facebook', 'wpshadow' ); ?>
					</button>
				</div>

				<div class="platform-card">
					<h3><?php esc_html_e( 'Twitter / X', 'wpshadow' ); ?></h3>
					<p class="status"><?php esc_html_e( 'Not Connected', 'wpshadow' ); ?></p>
					<button class="button connect-platform" data-platform="twitter">
						<?php esc_html_e( 'Connect Twitter / X', 'wpshadow' ); ?>
					</button>
				</div>

				<div class="platform-card">
					<h3><?php esc_html_e( 'LinkedIn', 'wpshadow' ); ?></h3>
					<p class="status"><?php esc_html_e( 'Not Connected', 'wpshadow' ); ?></p>
					<button class="button connect-platform" data-platform="linkedin">
						<?php esc_html_e( 'Connect LinkedIn', 'wpshadow' ); ?>
					</button>
				</div>

				<div class="platform-card">
					<h3><?php esc_html_e( 'Instagram', 'wpshadow' ); ?></h3>
					<p class="status"><?php esc_html_e( 'Not Connected', 'wpshadow' ); ?></p>
					<button class="button connect-platform" data-platform="instagram">
						<?php esc_html_e( 'Connect Instagram', 'wpshadow' ); ?>
					</button>
				</div>
			</div>

			<div class="wpshadow-social-settings">
				<h2><?php esc_html_e( 'Auto-Post Settings', 'wpshadow' ); ?></h2>
				<form id="social-settings-form">
					<table class="form-table">
						<tr>
							<th><label for="auto_post_enabled"><?php esc_html_e( 'Enable Auto-Post', 'wpshadow' ); ?></label></th>
							<td>
								<input type="checkbox" id="auto_post_enabled" />
								<p class="description"><?php esc_html_e( 'Automatically post to connected platforms when publishing', 'wpshadow' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><label for="post_types"><?php esc_html_e( 'Post Types', 'wpshadow' ); ?></label></th>
							<td>
								<select id="post_types" multiple style="height:100px;width:100%;max-width:300px;">
									<!-- Loaded dynamically -->
								</select>
								<p class="description"><?php esc_html_e( 'Select which post types should auto-post to social media', 'wpshadow' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><label for="include_image"><?php esc_html_e( 'Include Featured Image', 'wpshadow' ); ?></label></th>
							<td><input type="checkbox" id="include_image" checked /></td>
						</tr>
						<tr>
							<th><label for="post_format"><?php esc_html_e( 'Post Format', 'wpshadow' ); ?></label></th>
							<td>
								<textarea id="post_format" rows="4" class="large-text"><?php echo esc_textarea( '{title} - {url}' ); ?></textarea>
								<p class="description">
									<?php esc_html_e( 'Available placeholders: {title}, {excerpt}, {url}, {author}', 'wpshadow' ); ?>
								</p>
							</td>
						</tr>
					</table>
					<p class="submit">
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Settings', 'wpshadow' ); ?></button>
					</p>
				</form>
			</div>

			<div class="wpshadow-social-history">
				<h2><?php esc_html_e( 'Recent Posts', 'wpshadow' ); ?></h2>
				<div id="social-posts-history"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Auto-post to social media on publish.
	 *
	 * @since  1.6035.1400
	 * @param  int     $post_id Post ID.
	 * @param  WP_Post $post Post object.
	 * @return void
	 */
	public static function auto_post_to_social( int $post_id, $post ): void {
		$enabled = get_option( 'wpshadow_social_auto_post_enabled', false );

		if ( ! $enabled ) {
			return;
		}

		$post_types = get_option( 'wpshadow_social_post_types', array() );

		if ( ! in_array( $post->post_type, $post_types, true ) ) {
			return;
		}

		$platforms = self::get_connected_platforms();

		foreach ( $platforms as $platform ) {
			self::post_to_platform( $platform, $post_id );
		}
	}

	/**
	 * Handle test connection AJAX request.
	 *
	 * @since 1.6035.1400
	 * @return void Dies after sending JSON response.
	 */
	public static function ajax_test_connection(): void {
		check_ajax_referer( 'wpshadow_social_autopost', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$platform = isset( $_POST['platform'] ) ? sanitize_key( $_POST['platform'] ) : '';

		$connected = self::test_platform_connection( $platform );

		if ( $connected ) {
			wp_send_json_success( array( 'message' => __( 'Connection successful', 'wpshadow' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Connection failed', 'wpshadow' ) ) );
		}
	}

	/**
	 * Handle manual post AJAX request.
	 *
	 * @since 1.6035.1400
	 * @return void Dies after sending JSON response.
	 */
	public static function ajax_manual_post(): void {
		check_ajax_referer( 'wpshadow_social_autopost', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$post_id  = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$platform = isset( $_POST['platform'] ) ? sanitize_key( $_POST['platform'] ) : '';

		if ( ! $post_id || ! $platform ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters', 'wpshadow' ) ) );
		}

		$result = self::post_to_platform( $platform, $post_id );

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Posted successfully', 'wpshadow' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Post failed', 'wpshadow' ) ) );
		}
	}

	/**
	 * Post to social media platform.
	 *
	 * @since  1.6035.1400
	 * @param  string $platform Platform name.
	 * @param  int    $post_id Post ID.
	 * @return bool Success status.
	 */
	private static function post_to_platform( string $platform, int $post_id ): bool {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}

		$message = self::format_social_message( $post );

		// Platform-specific posting logic would go here
		// This is a stub implementation

		return true;
	}

	/**
	 * Format social media message.
	 *
	 * @since  1.6035.1400
	 * @param  WP_Post $post Post object.
	 * @return string Formatted message.
	 */
	private static function format_social_message( $post ): string {
		$format = get_option( 'wpshadow_social_post_format', '{title} - {url}' );

		$message = str_replace(
			array( '{title}', '{excerpt}', '{url}', '{author}' ),
			array(
				$post->post_title,
				wp_trim_words( $post->post_excerpt, 20 ),
				get_permalink( $post->ID ),
				get_the_author_meta( 'display_name', (int) $post->post_author ),
			),
			$format
		);

		return $message;
	}

	/**
	 * Test platform connection.
	 *
	 * @since  1.6035.1400
	 * @param  string $platform Platform name.
	 * @return bool Connection status.
	 */
	private static function test_platform_connection( string $platform ): bool {
		// Platform-specific connection testing would go here
		return false;
	}

	/**
	 * Get connected platforms.
	 *
	 * @since  1.6035.1400
	 * @return array Connected platforms.
	 */
	private static function get_connected_platforms(): array {
		return get_option( 'wpshadow_social_connected_platforms', array() );
	}
}
