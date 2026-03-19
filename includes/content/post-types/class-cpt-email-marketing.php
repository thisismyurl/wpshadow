<?php
/**
 * CPT Email Marketing Integration Feature
 *
 * Provides email marketing integration for custom post types with newsletter
 * creation, subscriber management, and campaign automation.
 *
 * @package    WPShadow
 * @subpackage Content\Post_Types
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Content\Post_Types;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT Email Marketing Class
 *
 * Handles email marketing integration for custom post types.
 *
 * @since 1.6093.1200
 */
class CPT_Email_Marketing extends Hook_Subscriber_Base {

	/**
	 * Register WordPress hooks.
	 *
	 * @since 1.6093.1200
	 * @return array Hook configuration array.
	 */
	protected static function get_hooks(): array {
		return array(
			'actions' => array(
				array( 'admin_menu', array( __CLASS__, 'register_email_marketing_page' ) ),
				array( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) ),
				array( 'wp_ajax_wpshadow_send_newsletter', array( __CLASS__, 'ajax_send_newsletter' ) ),
				array( 'wp_ajax_wpshadow_sync_subscribers', array( __CLASS__, 'ajax_sync_subscribers' ) ),
				array( 'publish_post', array( __CLASS__, 'auto_send_notification' ), 10, 2 ),
			),
			'filters' => array(),
		);
	}

	protected static function get_required_version(): string {
		return '1.6365.2359';
	}

	/**
	 * Register email marketing admin page.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register_email_marketing_page(): void {
		add_submenu_page(
			'wpshadow',
			__( 'Email Marketing', 'wpshadow' ),
			__( 'Email Marketing', 'wpshadow' ),
			'manage_options',
			'wpshadow-email-marketing',
			array( __CLASS__, 'render_email_marketing_page' )
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @since 1.6093.1200
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_admin_assets( string $hook ): void {
		if ( 'wpshadow_page_wpshadow-email-marketing' !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'wpshadow-email-marketing',
			plugins_url( 'assets/js/cpt-email-marketing.js', WPSHADOW_FILE ),
			array( 'jquery', 'wp-util' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-email-marketing',
			'wpShadowEmail',
			array(
				'nonce' => wp_create_nonce( 'wpshadow_email_marketing' ),
				'i18n'  => array(
					'sending'       => __( 'Sending newsletter...', 'wpshadow' ),
					'sent'          => __( 'Newsletter sent successfully', 'wpshadow' ),
					'syncing'       => __( 'Syncing subscribers...', 'wpshadow' ),
					'sync_complete' => __( 'Subscriber sync complete', 'wpshadow' ),
				),
			)
		);
	}

	/**
	 * Render email marketing admin page.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function render_email_marketing_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions', 'wpshadow' ) );
		}

		?>
		<div class="wrap wpshadow-email-marketing">
			<h1><?php esc_html_e( 'Email Marketing Integration', 'wpshadow' ); ?></h1>

			<?php do_action( 'wpshadow_after_page_header' ); ?>
			<p class="description">
				<?php esc_html_e( 'Send newsletters and manage email campaigns for your custom post types.', 'wpshadow' ); ?>
			</p>

			<div class="wpshadow-email-tabs">
				<button class="tab-button active" data-tab="newsletter"><?php esc_html_e( 'Create Newsletter', 'wpshadow' ); ?></button>
				<button class="tab-button" data-tab="subscribers"><?php esc_html_e( 'Subscribers', 'wpshadow' ); ?></button>
				<button class="tab-button" data-tab="campaigns"><?php esc_html_e( 'Campaigns', 'wpshadow' ); ?></button>
				<button class="tab-button" data-tab="settings"><?php esc_html_e( 'Settings', 'wpshadow' ); ?></button>
			</div>

			<div id="newsletter-tab" class="tab-content active">
				<h2><?php esc_html_e( 'Create Newsletter', 'wpshadow' ); ?></h2>
				<form id="newsletter-form">
					<table class="form-table">
						<tr>
							<th><label for="newsletter_subject"><?php esc_html_e( 'Subject Line', 'wpshadow' ); ?></label></th>
							<td><input type="text" id="newsletter_subject" class="regular-text" required /></td>
						</tr>
						<tr>
							<th><label for="newsletter_posts"><?php esc_html_e( 'Include Posts', 'wpshadow' ); ?></label></th>
							<td>
								<select id="newsletter_posts" multiple style="height:150px;width:100%;max-width:400px;">
									<!-- Posts loaded via AJAX -->
								</select>
								<p class="description"><?php esc_html_e( 'Select posts to include in newsletter', 'wpshadow' ); ?></p>
							</td>
						</tr>
					</table>
					<p class="submit">
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Send Newsletter', 'wpshadow' ); ?></button>
					</p>
				</form>
			</div>

			<div id="subscribers-tab" class="tab-content" style="display:none;">
				<h2><?php esc_html_e( 'Subscriber Management', 'wpshadow' ); ?></h2>
				<div id="subscribers-list"></div>
			</div>

			<div id="campaigns-tab" class="tab-content" style="display:none;">
				<h2><?php esc_html_e( 'Campaign History', 'wpshadow' ); ?></h2>
				<div id="campaigns-list"></div>
			</div>

			<div id="settings-tab" class="tab-content" style="display:none;">
				<h2><?php esc_html_e( 'Email Marketing Settings', 'wpshadow' ); ?></h2>
				<form id="email-settings-form">
					<table class="form-table">
						<tr>
							<th><label for="email_provider"><?php esc_html_e( 'Email Provider', 'wpshadow' ); ?></label></th>
							<td>
								<select id="email_provider">
									<option value="mailchimp"><?php esc_html_e( 'Mailchimp', 'wpshadow' ); ?></option>
									<option value="sendgrid"><?php esc_html_e( 'SendGrid', 'wpshadow' ); ?></option>
									<option value="constant_contact"><?php esc_html_e( 'Constant Contact', 'wpshadow' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="api_key"><?php esc_html_e( 'API Key', 'wpshadow' ); ?></label></th>
							<td><input type="password" id="api_key" class="regular-text" /></td>
						</tr>
					</table>
					<p class="submit">
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Settings', 'wpshadow' ); ?></button>
					</p>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle send newsletter AJAX request.
	 *
	 * @since 1.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function ajax_send_newsletter(): void {
		check_ajax_referer( 'wpshadow_email_marketing', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$subject  = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
		$post_ids = isset( $_POST['post_ids'] ) ? array_map( 'absint', (array) $_POST['post_ids'] ) : array();

		if ( empty( $subject ) || empty( $post_ids ) ) {
			wp_send_json_error( array( 'message' => __( 'Subject and posts are required', 'wpshadow' ) ) );
		}

		$sent_count = self::send_newsletter( $subject, $post_ids );

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %d: number of emails sent */
					_n( 'Newsletter sent to %d subscriber', 'Newsletter sent to %d subscribers', $sent_count, 'wpshadow' ),
					$sent_count
				),
			)
		);
	}

	/**
	 * Handle sync subscribers AJAX request.
	 *
	 * @since 1.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function ajax_sync_subscribers(): void {
		check_ajax_referer( 'wpshadow_email_marketing', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$synced = self::sync_subscribers_from_provider();

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %d: number of subscribers synced */
					_n( '%d subscriber synced', '%d subscribers synced', $synced, 'wpshadow' ),
					$synced
				),
			)
		);
	}

	/**
	 * Auto-send notification on post publish.
	 *
	 * @since 1.6093.1200
	 * @param  int     $post_id Post ID.
	 * @param  WP_Post $post Post object.
	 * @return void
	 */
	public static function auto_send_notification( int $post_id, $post ): void {
		$auto_send = get_option( 'wpshadow_email_auto_send', false );

		if ( ! $auto_send ) {
			return;
		}

		$post_types = self::get_enabled_post_types();

		if ( ! in_array( $post->post_type, $post_types, true ) ) {
			return;
		}

		self::send_newsletter( $post->post_title, array( $post_id ) );
	}

	/**
	 * Send newsletter to subscribers.
	 *
	 * @since 1.6093.1200
	 * @param  string $subject Email subject.
	 * @param  array  $post_ids Post IDs to include.
	 * @return int Number of emails sent.
	 */
	private static function send_newsletter( string $subject, array $post_ids ): int {
		$subscribers = self::get_subscribers();
		$content     = self::generate_newsletter_content( $post_ids );
		$sent        = 0;

		foreach ( $subscribers as $subscriber ) {
			$result = wp_mail( $subscriber['email'], $subject, $content, array( 'Content-Type: text/html; charset=UTF-8' ) );
			if ( $result ) {
				++$sent;
			}
		}

		return $sent;
	}

	/**
	 * Generate newsletter HTML content.
	 *
	 * @since 1.6093.1200
	 * @param  array $post_ids Post IDs.
	 * @return string HTML content.
	 */
	private static function generate_newsletter_content( array $post_ids ): string {
		$html = '<html><body>';

		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! $post ) {
				continue;
			}

			$html .= '<h2>' . esc_html( $post->post_title ) . '</h2>';
			$html .= '<p>' . wp_kses_post( $post->post_excerpt ) . '</p>';
			$html .= '<p><a href="' . esc_url( get_permalink( $post_id ) ) . '">' . __( 'Read More', 'wpshadow' ) . '</a></p>';
			$html .= '<hr />';
		}

		$html .= '</body></html>';

		return $html;
	}

	/**
	 * Get subscribers list.
	 *
	 * @since 1.6093.1200
	 * @return array Subscribers.
	 */
	private static function get_subscribers(): array {
		return get_option( 'wpshadow_email_subscribers', array() );
	}

	/**
	 * Sync subscribers from email provider.
	 *
	 * @since 1.6093.1200
	 * @return int Number of subscribers synced.
	 */
	private static function sync_subscribers_from_provider(): int {
		// Implementation would connect to email provider API
		return 0;
	}

	/**
	 * Get enabled post types for email marketing.
	 *
	 * @since 1.6093.1200
	 * @return array Enabled post types.
	 */
	private static function get_enabled_post_types(): array {
		return get_option( 'wpshadow_email_post_types', array() );
	}
}
