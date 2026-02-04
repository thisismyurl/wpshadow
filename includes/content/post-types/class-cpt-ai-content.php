<?php
/**
 * CPT AI Content Suggestions (Cloud-only)
 *
 * Provides AI-powered content suggestions using OpenAI API.
 * Only active when user is registered for WPShadow Cloud.
 *
 * @package    WPShadow
 * @subpackage Content
 * @since      1.6034.1345
 */

declare(strict_types=1);

namespace WPShadow\Content;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT_AI_Content Class
 *
 * AI-powered content generation and suggestions (Cloud feature).
 *
 * @since 1.6034.1345
 */
class CPT_AI_Content {

	/**
	 * Initialize AI content system.
	 *
	 * @since 1.6034.1345
	 * @return void
	 */
	public static function init() {
		// Only initialize if Cloud is registered.
		if ( ! self::is_cloud_registered() ) {
			return;
		}

		add_action( 'add_meta_boxes', array( __CLASS__, 'add_ai_meta_box' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_ajax_wpshadow_ai_suggest', array( __CLASS__, 'handle_ai_suggestion' ) );
	}

	/**
	 * Check if Cloud is registered.
	 *
	 * @since  1.6034.1345
	 * @return bool True if registered.
	 */
	private static function is_cloud_registered() {
		// Check for Cloud registration.
		$cloud_key = get_option( 'wpshadow_cloud_api_key' );
		return ! empty( $cloud_key );
	}

	/**
	 * Add AI suggestions meta box.
	 *
	 * @since 1.6034.1345
	 * @return void
	 */
	public static function add_ai_meta_box() {
		$post_types = array(
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

		foreach ( $post_types as $post_type ) {
			// Only add meta box if post type exists.
			if ( ! post_type_exists( $post_type ) ) {
				continue;
			}

			add_meta_box(
				'wpshadow_ai_suggestions',
				__( 'AI Content Suggestions', 'wpshadow' ) . ' <span class="wpshadow-cloud-badge">Cloud</span>',
				array( __CLASS__, 'render_ai_meta_box' ),
				$post_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Render AI suggestions meta box.
	 *
	 * @since  1.6034.1345
	 * @param  \WP_Post $post Current post object.
	 * @return void
	 */
	public static function render_ai_meta_box( $post ) {
		?>
		<div class="wpshadow-ai-suggestions">
			<p class="description">
				<?php esc_html_e( 'Get AI-powered suggestions to improve your content.', 'wpshadow' ); ?>
			</p>

			<div class="wpshadow-ai-options">
				<label>
					<input type="radio" name="ai_suggestion_type" value="improve" checked />
					<?php esc_html_e( 'Improve Content', 'wpshadow' ); ?>
				</label>
				<label>
					<input type="radio" name="ai_suggestion_type" value="expand" />
					<?php esc_html_e( 'Expand Content', 'wpshadow' ); ?>
				</label>
				<label>
					<input type="radio" name="ai_suggestion_type" value="summarize" />
					<?php esc_html_e( 'Summarize', 'wpshadow' ); ?>
				</label>
				<label>
					<input type="radio" name="ai_suggestion_type" value="seo" />
					<?php esc_html_e( 'SEO Optimize', 'wpshadow' ); ?>
				</label>
			</div>

			<button type="button" class="button button-primary wpshadow-get-ai-suggestion">
				<span class="dashicons dashicons-lightbulb"></span>
				<?php esc_html_e( 'Get AI Suggestion', 'wpshadow' ); ?>
			</button>

			<div class="wpshadow-ai-result" style="display:none;">
				<h4><?php esc_html_e( 'AI Suggestion:', 'wpshadow' ); ?></h4>
				<div class="wpshadow-ai-content"></div>
				<button type="button" class="button wpshadow-apply-suggestion">
					<?php esc_html_e( 'Apply to Content', 'wpshadow' ); ?>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue AI assets.
	 *
	 * @since  1.6034.1345
	 * @param  string $hook Current page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		$post_types = array( 'testimonial', 'team_member', 'portfolio_item', 'wps_event', 'resource', 'case_study', 'service', 'location', 'documentation', 'wps_product' );

		if ( ! in_array( $screen->post_type, $post_types, true ) ) {
			return;
		}

		wp_enqueue_script(
			'wpshadow-ai-content',
			WPSHADOW_URL . 'assets/js/cpt-ai-content.js',
			array( 'jquery', 'wp-util' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-ai-content',
			'wpShadowAI',
			array(
				'nonce'   => wp_create_nonce( 'wpshadow_ai_content' ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			)
		);

		wp_enqueue_style(
			'wpshadow-ai-content',
			WPSHADOW_URL . 'assets/css/cpt-ai-content.css',
			array(),
			WPSHADOW_VERSION
		);
	}

	/**
	 * Handle AI suggestion AJAX request.
	 *
	 * @since 1.6034.1345
	 * @return void
	 */
	public static function handle_ai_suggestion() {
		check_ajax_referer( 'wpshadow_ai_content', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$type    = isset( $_POST['type'] ) ? sanitize_key( $_POST['type'] ) : 'improve';
		$content = isset( $_POST['content'] ) ? wp_kses_post( wp_unslash( $_POST['content'] ) ) : '';

		if ( ! $post_id || empty( $content ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request', 'wpshadow' ) ) );
		}

		// Make API call to WPShadow Cloud.
		$suggestion = self::get_ai_suggestion( $content, $type );

		if ( is_wp_error( $suggestion ) ) {
			wp_send_json_error( array( 'message' => $suggestion->get_error_message() ) );
		}

		wp_send_json_success( array(
			'suggestion' => $suggestion,
		) );
	}

	/**
	 * Get AI suggestion from Cloud API.
	 *
	 * @since  1.6034.1345
	 * @param  string $content Content to analyze.
	 * @param  string $type    Suggestion type.
	 * @return string|\WP_Error AI suggestion or error.
	 */
	private static function get_ai_suggestion( $content, $type ) {
		$api_key = get_option( 'wpshadow_cloud_api_key' );

		if ( empty( $api_key ) ) {
			return new \WP_Error( 'no_api_key', __( 'Cloud API key not configured', 'wpshadow' ) );
		}

		$response = wp_remote_post(
			'https://cloud.wpshadow.com/api/v1/ai-suggestions',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'content' => $content,
						'type'    => $type,
					)
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body['suggestion'] ) ) {
			return new \WP_Error( 'api_error', __( 'Failed to get AI suggestion', 'wpshadow' ) );
		}

		return $body['suggestion'];
	}
}
