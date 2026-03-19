<?php
/**
 * CPT Third-Party API Integration Feature
 *
 * Provides third-party API integrations for custom post types with support for
 * webhooks, data sync, and external service connections.
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
 * CPT API Integration Class
 *
 * Handles third-party API integrations for custom post types.
 *
 * @since 1.6093.1200
 */
class CPT_API_Integration extends Hook_Subscriber_Base {

	/**
	 * Register WordPress hooks.
	 *
	 * @since 1.6093.1200
	 * @return array Hook configuration array.
	 */
	protected static function get_hooks(): array {
		return array(
			'actions' => array(
				array( 'admin_menu', array( __CLASS__, 'register_api_page' ) ),
				array( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) ),
				array( 'rest_api_init', array( __CLASS__, 'register_rest_routes' ) ),
				array( 'wp_ajax_wpshadow_test_api_connection', array( __CLASS__, 'ajax_test_connection' ) ),
				array( 'wp_ajax_wpshadow_sync_api_data', array( __CLASS__, 'ajax_sync_data' ) ),
				array( 'save_post', array( __CLASS__, 'trigger_webhooks' ), 10, 2 ),
			),
			'filters' => array(),
		);
	}

	protected static function get_required_version(): string {
		return '1.6365.2359';
	}

	/**
	 * Register API integration admin page.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register_api_page(): void {
		add_submenu_page(
			'wpshadow',
			__( 'API Integrations', 'wpshadow' ),
			__( 'API Integrations', 'wpshadow' ),
			'manage_options',
			'wpshadow-api-integrations',
			array( __CLASS__, 'render_api_page' )
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
		if ( 'wpshadow_page_wpshadow-api-integrations' !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'wpshadow-api-integrations',
			plugins_url( 'assets/js/cpt-api-integrations.js', WPSHADOW_FILE ),
			array( 'jquery', 'wp-util' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-api-integrations',
			'wpShadowAPI',
			array(
				'nonce'   => wp_create_nonce( 'wpshadow_api_integrations' ),
				'restUrl' => rest_url( 'wpshadow/v1' ),
				'i18n'    => array(
					'testing'       => __( 'Testing API connection...', 'wpshadow' ),
					'connected'     => __( 'API connected successfully', 'wpshadow' ),
					'syncing'       => __( 'Syncing data...', 'wpshadow' ),
					'sync_complete' => __( 'Data sync complete', 'wpshadow' ),
				),
			)
		);
	}

	/**
	 * Render API integrations admin page.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function render_api_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions', 'wpshadow' ) );
		}

		?>
		<div class="wrap wpshadow-api-integrations">
			<h1><?php esc_html_e( 'Third-Party API Integrations', 'wpshadow' ); ?></h1>

			<?php do_action( 'wpshadow_after_page_header' ); ?>
			<p class="description">
				<?php esc_html_e( 'Connect your custom post types to external services via API integrations and webhooks.', 'wpshadow' ); ?>
			</p>

			<div class="wpshadow-api-tabs">
				<button class="tab-button active" data-tab="connections"><?php esc_html_e( 'API Connections', 'wpshadow' ); ?></button>
				<button class="tab-button" data-tab="webhooks"><?php esc_html_e( 'Webhooks', 'wpshadow' ); ?></button>
				<button class="tab-button" data-tab="sync"><?php esc_html_e( 'Data Sync', 'wpshadow' ); ?></button>
				<button class="tab-button" data-tab="logs"><?php esc_html_e( 'API Logs', 'wpshadow' ); ?></button>
			</div>

			<div id="connections-tab" class="tab-content active">
				<h2><?php esc_html_e( 'API Connections', 'wpshadow' ); ?></h2>
				<button type="button" class="button button-primary" id="add-connection">
					<?php esc_html_e( 'Add New Connection', 'wpshadow' ); ?>
				</button>

				<div id="connections-list" style="margin-top:20px;">
					<!-- API connections loaded dynamically -->
				</div>

				<div id="connection-form" style="display:none; margin-top:30px;">
					<h3><?php esc_html_e( 'Add API Connection', 'wpshadow' ); ?></h3>
					<form id="api-connection-form">
						<table class="form-table">
							<tr>
								<th><label for="api_name"><?php esc_html_e( 'Connection Name', 'wpshadow' ); ?></label></th>
								<td><input type="text" id="api_name" class="regular-text" required /></td>
							</tr>
							<tr>
								<th><label for="api_url"><?php esc_html_e( 'API URL', 'wpshadow' ); ?></label></th>
								<td><input type="url" id="api_url" class="regular-text" required /></td>
							</tr>
							<tr>
								<th><label for="api_method"><?php esc_html_e( 'Method', 'wpshadow' ); ?></label></th>
								<td>
									<select id="api_method">
										<option value="GET">GET</option>
										<option value="POST">POST</option>
										<option value="PUT">PUT</option>
										<option value="DELETE">DELETE</option>
									</select>
								</td>
							</tr>
							<tr>
								<th><label for="api_auth_type"><?php esc_html_e( 'Authentication', 'wpshadow' ); ?></label></th>
								<td>
									<select id="api_auth_type">
										<option value="none"><?php esc_html_e( 'None', 'wpshadow' ); ?></option>
										<option value="api_key"><?php esc_html_e( 'API Key', 'wpshadow' ); ?></option>
										<option value="bearer"><?php esc_html_e( 'Bearer Token', 'wpshadow' ); ?></option>
										<option value="basic"><?php esc_html_e( 'Basic Auth', 'wpshadow' ); ?></option>
										<option value="oauth"><?php esc_html_e( 'OAuth 2.0', 'wpshadow' ); ?></option>
									</select>
								</td>
							</tr>
							<tr id="auth_credentials" style="display:none;">
								<th><label for="api_credentials"><?php esc_html_e( 'Credentials', 'wpshadow' ); ?></label></th>
								<td><input type="password" id="api_credentials" class="regular-text" /></td>
							</tr>
							<tr>
								<th><label for="api_headers"><?php esc_html_e( 'Custom Headers', 'wpshadow' ); ?></label></th>
								<td>
									<textarea id="api_headers" rows="4" class="large-text" placeholder='{"Content-Type": "application/json"}'></textarea>
									<p class="description"><?php esc_html_e( 'JSON format', 'wpshadow' ); ?></p>
								</td>
							</tr>
						</table>
						<p class="submit">
							<button type="submit" class="button button-primary"><?php esc_html_e( 'Test & Save Connection', 'wpshadow' ); ?></button>
							<button type="button" class="button" id="cancel-connection"><?php esc_html_e( 'Cancel', 'wpshadow' ); ?></button>
						</p>
					</form>
				</div>
			</div>

			<div id="webhooks-tab" class="tab-content" style="display:none;">
				<h2><?php esc_html_e( 'Webhook Configuration', 'wpshadow' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Configure webhooks to notify external services when posts are created, updated, or deleted.', 'wpshadow' ); ?>
				</p>

				<table class="form-table">
					<tr>
						<th><label for="webhook_url"><?php esc_html_e( 'Webhook URL', 'wpshadow' ); ?></label></th>
						<td><input type="url" id="webhook_url" class="regular-text" /></td>
					</tr>
					<tr>
						<th><label for="webhook_events"><?php esc_html_e( 'Trigger Events', 'wpshadow' ); ?></label></th>
						<td>
							<fieldset>
								<label><input type="checkbox" name="webhook_events[]" value="post_created" /> <?php esc_html_e( 'Post Created', 'wpshadow' ); ?></label><br />
								<label><input type="checkbox" name="webhook_events[]" value="post_updated" /> <?php esc_html_e( 'Post Updated', 'wpshadow' ); ?></label><br />
								<label><input type="checkbox" name="webhook_events[]" value="post_deleted" /> <?php esc_html_e( 'Post Deleted', 'wpshadow' ); ?></label><br />
							</fieldset>
						</td>
					</tr>
				</table>
				<p class="submit">
					<button type="button" class="button button-primary" id="save-webhook">
						<?php esc_html_e( 'Save Webhook', 'wpshadow' ); ?>
					</button>
				</p>
			</div>

			<div id="sync-tab" class="tab-content" style="display:none;">
				<h2><?php esc_html_e( 'Data Synchronization', 'wpshadow' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Sync data between your custom post types and external services.', 'wpshadow' ); ?>
				</p>

				<div id="sync-jobs-list"></div>

				<p class="submit">
					<button type="button" class="button button-primary" id="run-sync">
						<?php esc_html_e( 'Run Manual Sync', 'wpshadow' ); ?>
					</button>
				</p>
			</div>

			<div id="logs-tab" class="tab-content" style="display:none;">
				<h2><?php esc_html_e( 'API Request Logs', 'wpshadow' ); ?></h2>
				<div id="api-logs-container"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Register REST API routes.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register_rest_routes(): void {
		register_rest_route(
			'wpshadow/v1',
			'/api/connections',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'rest_get_connections' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		register_rest_route(
			'wpshadow/v1',
			'/api/connections',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'rest_create_connection' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		register_rest_route(
			'wpshadow/v1',
			'/api/webhook',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'rest_handle_webhook' ),
				'permission_callback' => array( __CLASS__, 'rest_authorize_webhook' ),
			)
		);
	}

	/**
	 * Authorize webhook requests.
	 *
	 * Allows site administrators and external webhook callers that provide
	 * the configured shared secret in the X-WPShadow-Webhook-Secret header.
	 *
	 * @since 1.6093.1200
	 * @param  \WP_REST_Request $request Request object.
	 * @return bool|\WP_Error True when authorized, WP_Error otherwise.
	 */
	public static function rest_authorize_webhook( $request ) {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		$stored_secret = (string) get_option( 'wpshadow_webhook_secret', '' );
		if ( '' === $stored_secret ) {
			return new \WP_Error(
				'wpshadow_webhook_secret_missing',
				__( 'Webhook secret is not configured.', 'wpshadow' ),
				array( 'status' => 403 )
			);
		}

		$provided_secret = (string) $request->get_header( 'x-wpshadow-webhook-secret' );
		if ( '' === $provided_secret ) {
			return new \WP_Error(
				'wpshadow_webhook_secret_required',
				__( 'Webhook secret is required.', 'wpshadow' ),
				array( 'status' => 401 )
			);
		}

		if ( ! hash_equals( $stored_secret, $provided_secret ) ) {
			return new \WP_Error(
				'wpshadow_webhook_secret_invalid',
				__( 'Webhook secret is invalid.', 'wpshadow' ),
				array( 'status' => 401 )
			);
		}

		return true;
	}

	/**
	 * REST: Get API connections.
	 *
	 * @since 1.6093.1200
	 * @param  WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public static function rest_get_connections( $request ) {
		$connections = get_option( 'wpshadow_api_connections', array() );
		return rest_ensure_response( $connections );
	}

	/**
	 * REST: Create API connection.
	 *
	 * @since 1.6093.1200
	 * @param  WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public static function rest_create_connection( $request ) {
		$connection = array(
			'name'        => sanitize_text_field( $request->get_param( 'name' ) ),
			'url'         => esc_url_raw( $request->get_param( 'url' ) ),
			'method'      => sanitize_key( $request->get_param( 'method' ) ),
			'auth_type'   => sanitize_key( $request->get_param( 'auth_type' ) ),
			'credentials' => sanitize_text_field( $request->get_param( 'credentials' ) ),
			'headers'     => $request->get_param( 'headers' ),
		);

		$connections   = get_option( 'wpshadow_api_connections', array() );
		$connections[] = $connection;
		update_option( 'wpshadow_api_connections', $connections );

		return rest_ensure_response( array( 'success' => true, 'connection' => $connection ) );
	}

	/**
	 * REST: Handle incoming webhook.
	 *
	 * @since 1.6093.1200
	 * @param  WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public static function rest_handle_webhook( $request ) {
		$payload = $request->get_json_params();
		if ( ! is_array( $payload ) ) {
			$payload = array();
		}

		self::log_api_activity( 'webhook_received', $payload );

		return rest_ensure_response( array( 'received' => true ) );
	}

	/**
	 * Handle test connection AJAX request.
	 *
	 * @since 1.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function ajax_test_connection(): void {
		check_ajax_referer( 'wpshadow_api_integrations', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$connection_id = isset( $_POST['connection_id'] ) ? absint( $_POST['connection_id'] ) : 0;

		$result = self::test_api_connection( $connection_id );

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Connection successful', 'wpshadow' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Connection failed', 'wpshadow' ) ) );
		}
	}

	/**
	 * Handle sync data AJAX request.
	 *
	 * @since 1.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function ajax_sync_data(): void {
		check_ajax_referer( 'wpshadow_api_integrations', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$synced = self::sync_api_data();

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: %d: number of records synced */
					_n( '%d record synced', '%d records synced', $synced, 'wpshadow' ),
					$synced
				),
			)
		);
	}

	/**
	 * Trigger webhooks on post save.
	 *
	 * @since 1.6093.1200
	 * @param  int     $post_id Post ID.
	 * @param  WP_Post $post Post object.
	 * @return void
	 */
	public static function trigger_webhooks( int $post_id, $post ): void {
		$webhook_url = get_option( 'wpshadow_webhook_url', '' );
		$webhook_secret = (string) get_option( 'wpshadow_webhook_secret', '' );

		if ( empty( $webhook_url ) ) {
			return;
		}

		$payload = array(
			'event'   => 'post_saved',
			'post_id' => $post_id,
			'post'    => array(
				'title'   => $post->post_title,
				'content' => $post->post_content,
				'status'  => $post->post_status,
			),
		);

		$headers = array( 'Content-Type' => 'application/json' );
		if ( '' !== $webhook_secret ) {
			$headers['X-WPShadow-Webhook-Secret'] = $webhook_secret;
		}

		wp_remote_post(
			$webhook_url,
			array(
				'body'    => wp_json_encode( $payload ),
				'headers' => $headers,
				'timeout' => 15,
			)
		);

		self::log_api_activity( 'webhook_sent', $payload );
	}

	/**
	 * Test API connection.
	 *
	 * @since 1.6093.1200
	 * @param  int $connection_id Connection ID.
	 * @return bool Connection status.
	 */
	private static function test_api_connection( int $connection_id ): bool {
		$connections = get_option( 'wpshadow_api_connections', array() );

		if ( ! isset( $connections[ $connection_id ] ) ) {
			return false;
		}

		$connection = $connections[ $connection_id ];
		$response   = wp_remote_request( $connection['url'], array( 'method' => $connection['method'] ) );

		return ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response );
	}

	/**
	 * Sync data with external API.
	 *
	 * @since 1.6093.1200
	 * @return int Number of records synced.
	 */
	private static function sync_api_data(): int {
		// Sync logic would go here
		return 0;
	}

	/**
	 * Log API activity.
	 *
	 * @since 1.6093.1200
	 * @param  string $type Activity type.
	 * @param  mixed  $data Activity data.
	 * @return void
	 */
	private static function log_api_activity( string $type, $data ): void {
		$logs = get_option( 'wpshadow_api_logs', array() );

		$logs[] = array(
			'timestamp' => current_time( 'mysql' ),
			'type'      => $type,
			'data'      => $data,
		);

		// Keep only last 100 logs.
		if ( count( $logs ) > 100 ) {
			$logs = array_slice( $logs, -100 );
		}

		update_option( 'wpshadow_api_logs', $logs );
	}
}
