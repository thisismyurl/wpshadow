<?php
/**
 * CPT Live Preview
 *
 * Provides live preview functionality for custom post types,
 * allowing users to preview content before publishing.
 *
 * @package    WPShadow
 * @subpackage Content
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Content;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT_Live_Preview Class
 *
 * Enables live preview mode for all custom post types with
 * iframe-based rendering and real-time updates.
 *
 * @since 0.6093.1200
 */
class CPT_Live_Preview {

	/**
	 * Initialize the live preview system.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_preview_meta_box' ) );
		add_action( 'wp_ajax_wpshadow_preview_content', array( __CLASS__, 'handle_preview_ajax' ) );
		add_filter( 'preview_post_link', array( __CLASS__, 'modify_preview_link' ), 10, 2 );
	}

	/**
	 * Enqueue preview assets.
	 *
	 * @since 0.6093.1200
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || ! self::is_supported_post_type( $screen->post_type ) ) {
			return;
		}

		wp_enqueue_script(
			'wpshadow-live-preview',
			WPSHADOW_URL . 'assets/js/cpt-live-preview.js',
			array( 'jquery', 'wp-util' ),
			WPSHADOW_VERSION,
			true
		);

		\WPShadow\Core\Admin_Asset_Registry::localize_with_ajax_nonce(
			'wpshadow-live-preview',
			'wpShadowPreview',
			'wpshadow_live_preview',
			array(
				'previewUrl' => get_preview_post_link(),
			)
		);

		wp_enqueue_style(
			'wpshadow-live-preview',
			WPSHADOW_URL . 'assets/css/cpt-live-preview.css',
			array(),
			WPSHADOW_VERSION
		);
	}

	/**
	 * Add preview meta box.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function add_preview_meta_box() {
		$post_types = self::get_supported_post_types();

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'wpshadow_live_preview',
				__( 'Live Preview', 'wpshadow' ),
				array( __CLASS__, 'render_preview_meta_box' ),
				$post_type,
				'side',
				'high'
			);
		}
	}

	/**
	 * Render preview meta box.
	 *
	 * @since 0.6093.1200
	 * @param  \WP_Post $post Current post object.
	 * @return void
	 */
	public static function render_preview_meta_box( $post ) {
		?>
		<div class="wpshadow-preview-controls">
			<button type="button" class="button button-primary button-large wpshadow-preview-toggle" data-preview-open="false">
				<span class="dashicons dashicons-visibility"></span>
				<?php esc_html_e( 'Show Live Preview', 'wpshadow' ); ?>
			</button>

			<div class="wpshadow-preview-devices" style="display:none;">
				<label><?php esc_html_e( 'Device:', 'wpshadow' ); ?></label>
				<button type="button" class="button wpshadow-device-btn active" data-device="desktop">
					<span class="dashicons dashicons-desktop"></span>
				</button>
				<button type="button" class="button wpshadow-device-btn" data-device="tablet">
					<span class="dashicons dashicons-tablet"></span>
				</button>
				<button type="button" class="button wpshadow-device-btn" data-device="mobile">
					<span class="dashicons dashicons-smartphone"></span>
				</button>
			</div>
		</div>

		<div class="wpshadow-preview-container" style="display:none;">
			<div class="wpshadow-preview-toolbar">
				<button type="button" class="button wpshadow-refresh-preview">
					<span class="dashicons dashicons-update"></span>
					<?php esc_html_e( 'Refresh', 'wpshadow' ); ?>
				</button>
				<button type="button" class="button wpshadow-close-preview">
					<span class="dashicons dashicons-no-alt"></span>
					<?php esc_html_e( 'Close', 'wpshadow' ); ?>
				</button>
			</div>
			<div class="wpshadow-preview-frame-wrapper">
				<iframe id="wpshadow-preview-iframe" frameborder="0"></iframe>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle AJAX preview request.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function handle_preview_ajax() {
		check_ajax_referer( 'wpshadow_live_preview', 'nonce' );

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid post ID', 'wpshadow' ) ) );
		}

		$preview_url = get_preview_post_link( $post_id );

		wp_send_json_success( array(
			'url' => $preview_url,
		) );
	}

	/**
	 * Modify preview link for better compatibility.
	 *
	 * @since 0.6093.1200
	 * @param  string   $link Preview link.
	 * @param  \WP_Post $post Post object.
	 * @return string Modified preview link.
	 */
	public static function modify_preview_link( $link, $post ) {
		if ( ! self::is_supported_post_type( $post->post_type ) ) {
			return $link;
		}

		return add_query_arg( 'wpshadow_preview', '1', $link );
	}

	/**
	 * Check if post type supports live preview.
	 *
	 * @since 0.6093.1200
	 * @param  string $post_type Post type slug.
	 * @return bool True if supported.
	 */
	private static function is_supported_post_type( $post_type ) {
		// First check if post type actually exists.
		if ( ! post_type_exists( $post_type ) ) {
			return false;
		}

		return in_array(
			$post_type,
			self::get_supported_post_types(),
			true
		);
	}

	/**
	 * Get supported post types.
	 *
	 * @since 0.6093.1200
	 * @return array Supported post types.
	 */
	private static function get_supported_post_types() {
		return array(
			'testimonial',
			'team_member',
			'portfolio_item',
			'wps_event',
			'resource',
			'case_study',
			'service',
			'location',
			'documentation',
			'wps_product',
		);
	}
}
