<?php
/**
 * WPS Video Walkthroughs
 *
 * Auto-generated video walkthroughs of site functionality.
 * Provides video library management and generation interface.
 *
 * NOTE: This is a foundational implementation that requires external
 * video generation services (Puppeteer/Playwright-based microservice)
 * for actual screen recording and video encoding.
 *
 * @package WPSHADOW_WP_SUPPORT
 * @since 1.2601.73002
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * Class WPSHADOW_Video_Walkthroughs
 *
 * Manages auto-generated video walkthroughs:
 * - Video library organization
 * - Video generation requests
 * - Download and embed functionality
 * - Update detection and regeneration
 */
class WPSHADOW_Video_Walkthroughs {

	/**
	 * Database option key for video library.
	 */
	private const VIDEO_LIBRARY_KEY = 'wpshadow_video_library';

	/**
	 * Database option key for video settings.
	 */
	private const VIDEO_SETTINGS_KEY = 'wpshadow_video_settings';

	/**
	 * Available video types.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private static $video_types = array();

	/**
	 * Initialize video walkthroughs.
	 *
	 * @return void
	 */
	public static function init(): void {
		self::register_video_types();

		add_action( 'admin_menu', array( __CLASS__, 'register_admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_ajax_WPSHADOW_generate_video', array( __CLASS__, 'ajax_generate_video' ) );
		add_action( 'wp_ajax_WPSHADOW_download_video', array( __CLASS__, 'ajax_download_video' ) );
		add_action( 'wp_ajax_WPSHADOW_get_video_embed', array( __CLASS__, 'ajax_get_video_embed' ) );
		add_action( 'wp_ajax_WPSHADOW_check_video_service', array( __CLASS__, 'ajax_check_video_service' ) );
	}

	/**
	 * Register available video types.
	 *
	 * @return void
	 */
	private static function register_video_types(): void {
		self::$video_types = array(
			'foundation' => array(
				'label'  => __( 'Foundation Videos', 'plugin-wpshadow' ),
				'desc'   => __( 'Essential videos for all WordPress sites', 'plugin-wpshadow' ),
				'videos' => array(
					'dashboard-overview' => array(
						'title'       => __( 'Dashboard Overview', 'plugin-wpshadow' ),
						'description' => __( 'WordPress dashboard overview, navigation, and basic interface', 'plugin-wpshadow' ),
						'duration'    => '3:42',
						'steps'       => array(
							'Show WordPress dashboard overview',
							'Navigate to pages/posts/products',
							'Locate plugins, themes, settings',
							'Demonstrate admin navigation',
						),
					),
					'add-page'           => array(
						'title'       => __( 'Adding a Page', 'plugin-wpshadow' ),
						'description' => __( 'Step-by-step guide to creating a new WordPress page', 'plugin-wpshadow' ),
						'duration'    => '2:15',
						'steps'       => array(
							'Click Pages → Add New',
							'Enter page title',
							'Add content with block editor',
							'Publish the page',
						),
					),
					'add-post'           => array(
						'title'       => __( 'Adding a Blog Post', 'plugin-wpshadow' ),
						'description' => __( 'Creating and publishing a blog post', 'plugin-wpshadow' ),
						'duration'    => '2:08',
						'steps'       => array(
							'Click Posts → Add New',
							'Fill in title and content',
							'Add featured image',
							'Set categories and tags',
							'Publish the post',
						),
					),
					'manage-media'       => array(
						'title'       => __( 'Managing Media', 'plugin-wpshadow' ),
						'description' => __( 'Uploading and managing media files in WordPress', 'plugin-wpshadow' ),
						'duration'    => '1:54',
						'steps'       => array(
							'Navigate to Media Library',
							'Upload new files',
							'Organize media files',
							'Edit and manage media',
						),
					),
				),
			),
			'custom'     => array(
				'label'  => __( 'Your Custom Videos', 'plugin-wpshadow' ),
				'desc'   => __( 'Videos based on installed plugins and your site configuration', 'plugin-wpshadow' ),
				'videos' => array(),
			),
		);

		// Add custom videos based on installed plugins.
		self::detect_plugin_videos();
	}

	/**
	 * Detect and add videos for installed plugins.
	 *
	 * @return void
	 */
	private static function detect_plugin_videos(): void {
		// Check for Contact Form 7.
		if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
			self::$video_types['custom']['videos']['contact-form-7'] = array(
				'title'       => __( 'Using Contact Forms', 'plugin-wpshadow' ),
				'description' => __( 'How Contact Form 7 captures and manages form submissions', 'plugin-wpshadow' ),
				'duration'    => '1:47',
				'steps'       => array(
					'Navigate to Contact Forms',
					'Create a new form',
					'Add form fields',
					'Configure form settings',
					'View submissions',
				),
			);
		}

		// Check for WooCommerce.
		if ( class_exists( 'WooCommerce' ) ) {
			self::$video_types['custom']['videos']['woocommerce-products'] = array(
				'title'       => __( 'Managing Products', 'plugin-wpshadow' ),
				'description' => __( 'Adding products, setting prices, and managing inventory', 'plugin-wpshadow' ),
				'duration'    => '4:23',
				'steps'       => array(
					'Navigate to Products',
					'Add new product with images',
					'Set price and stock',
					'Configure product options',
					'Publish to shop',
				),
			);

			self::$video_types['custom']['videos']['woocommerce-orders'] = array(
				'title'       => __( 'Processing Orders', 'plugin-wpshadow' ),
				'description' => __( 'Managing customer orders and fulfillment', 'plugin-wpshadow' ),
				'duration'    => '3:12',
				'steps'       => array(
					'View order list',
					'Open order details',
					'Update order status',
					'Process refunds',
					'Manage shipping',
				),
			);
		}

		// Check for Gravity Forms.
		if ( class_exists( 'GFForms' ) ) {
			self::$video_types['custom']['videos']['gravity-forms'] = array(
				'title'       => __( 'Working with Gravity Forms', 'plugin-wpshadow' ),
				'description' => __( 'Creating forms and processing submissions', 'plugin-wpshadow' ),
				'duration'    => '2:34',
				'steps'       => array(
					'Create new form',
					'Add form fields',
					'Configure notifications',
					'Set up confirmations',
					'View entries',
				),
			);
		}

		// Check for Elementor.
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			self::$video_types['custom']['videos']['elementor-editing'] = array(
				'title'       => __( 'Editing with Elementor', 'plugin-wpshadow' ),
				'description' => __( 'Using Elementor page builder for custom layouts', 'plugin-wpshadow' ),
				'duration'    => '3:45',
				'steps'       => array(
					'Launch Elementor editor',
					'Add and configure widgets',
					'Customize layout and styling',
					'Preview and publish',
				),
			);
		}
	}

	/**
	 * Register admin page.
	 *
	 * @return void
	 */
	public static function register_admin_page(): void {
		add_submenu_page(
			'wpshadow',
			__( 'Video Library', 'plugin-wpshadow' ),
			__( 'Video Library', 'plugin-wpshadow' ),
			'manage_options',
			'wps-video-library',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Enqueue CSS/JS assets.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( string $hook ): void {
		if ( ! str_contains( $hook, 'wps-video-library' ) ) {
			return;
		}

		wp_enqueue_style( 'wps-video-library', plugins_url( '../assets/css/video-library.css', __FILE__ ), array(), '1.0.0' );
		wp_enqueue_script( 'wps-video-library', plugins_url( '../assets/js/video-library.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );

		wp_localize_script(
			'wps-video-library',
			'wpsVideoLibrary',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'wpshadow_video_library_nonce' ),
				'strings'  => array(
					'generating'      => __( 'Generating video...', 'plugin-wpshadow' ),
					'generationError' => __( 'Video generation failed. Please check service configuration.', 'plugin-wpshadow' ),
					'serviceOffline'  => __( 'Video generation service is not configured. Please configure in settings.', 'plugin-wpshadow' ),
				),
			)
		);
	}

	/**
	 * AJAX handler: Generate video.
	 *
	 * @return void
	 */
	public static function ajax_generate_video(): void {
		check_ajax_referer( 'wpshadow_video_library_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You don\'t have permission to do that', 'plugin-wpshadow' ) ) );
		}

		$video_id = \WPShadow\WPSHADOW_get_post_key( 'video_id' );
		if ( empty( $video_id ) ) {
			wp_send_json_error( array( 'message' => __( 'That video isn\'t available', 'plugin-wpshadow' ) ) );
		}

		$settings    = get_option( self::VIDEO_SETTINGS_KEY, array() );
		$service_url = $settings['service_url'] ?? '';

		if ( empty( $service_url ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Video generation service not configured. Please configure the service URL in settings.', 'plugin-wpshadow' ),
					'type'    => 'not_configured',
				)
			);
		}

		// Get video configuration.
		$video_config = self::get_video_config( $video_id );
		if ( ! $video_config ) {
			wp_send_json_error( array( 'message' => __( 'Video type not found', 'plugin-wpshadow' ) ) );
		}

		// In a real implementation, this would call the external video generation service.
		// For now, we'll simulate the request and return a placeholder response.
		$response = self::request_video_generation( $service_url, $video_id, $video_config );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error(
				array(
					'message' => $response->get_error_message(),
				)
			);
		}

		// Store video in library.
		self::add_video_to_library( $video_id, $response );

		wp_send_json_success(
			array(
				'message' => __( 'Video generation started. This may take a few minutes.', 'plugin-wpshadow' ),
				'video'   => $response,
			)
		);
	}

	/**
	 * Request video generation from external service.
	 *
	 * @param string               $service_url Service endpoint URL.
	 * @param string               $video_id    Video identifier.
	 * @param array<string, mixed> $config      Video configuration.
	 * @return array<string, mixed>|\WP_Error Response data or error.
	 */
	private static function request_video_generation( string $service_url, string $video_id, array $config ) {
		$request_body = array(
			'video_id' => $video_id,
			'site_url' => home_url(),
			'title'    => $config['title'],
			'steps'    => $config['steps'],
			'duration' => $config['duration'] ?? 'auto',
			'language' => get_locale(),
			'branding' => array(
				'site_name' => get_bloginfo( 'name' ),
				'logo_url'  => get_site_icon_url(),
			),
		);

		$response = wp_remote_post(
			trailingslashit( $service_url ) . 'generate',
			array(
				'body'    => wp_json_encode( $request_body ),
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			return new \WP_Error(
				'service_error',
				sprintf(
					/* translators: %d: HTTP status code */
					__( 'Video generation service returned error (status %d)', 'plugin-wpshadow' ),
					$status_code
				)
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! $data ) {
			return new \WP_Error( 'invalid_response', __( 'We had trouble generating your video', 'plugin-wpshadow' ) );
		}

		return $data;
	}

	/**
	 * Get video configuration by ID.
	 *
	 * @param string $video_id Video identifier.
	 * @return array<string, mixed>|null Video configuration or null if not found.
	 */
	private static function get_video_config( string $video_id ): ?array {
		foreach ( self::$video_types as $type ) {
			if ( isset( $type['videos'][ $video_id ] ) ) {
				return $type['videos'][ $video_id ];
			}
		}
		return null;
	}

	/**
	 * Add video to library.
	 *
	 * @param string               $video_id Video identifier.
	 * @param array<string, mixed> $video_data Video data from generation service.
	 * @return void
	 */
	private static function add_video_to_library( string $video_id, array $video_data ): void {
		$library = get_option( self::VIDEO_LIBRARY_KEY, array() );

		$library[ $video_id ] = array(
			'id'           => $video_id,
			'title'        => $video_data['title'] ?? '',
			'url'          => $video_data['url'] ?? '',
			'download_url' => $video_data['download_url'] ?? '',
			'embed_code'   => $video_data['embed_code'] ?? '',
			'duration'     => $video_data['duration'] ?? '',
			'generated_at' => time(),
			'status'       => $video_data['status'] ?? 'completed',
		);

		update_option( self::VIDEO_LIBRARY_KEY, $library );
	}

	/**
	 * AJAX handler: Download video.
	 *
	 * @return void
	 */
	public static function ajax_download_video(): void {
		check_ajax_referer( 'wpshadow_video_library_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You don\'t have permission to do that', 'plugin-wpshadow' ) ) );
		}

		$video_id = \WPShadow\WPSHADOW_get_post_key( 'video_id' );
		$library  = get_option( self::VIDEO_LIBRARY_KEY, array() );

		if ( ! isset( $library[ $video_id ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Video not found in library', 'plugin-wpshadow' ) ) );
		}

		$video = $library[ $video_id ];

		wp_send_json_success(
			array(
				'download_url' => $video['download_url'] ?? '',
			)
		);
	}

	/**
	 * AJAX handler: Get video embed code.
	 *
	 * @return void
	 */
	public static function ajax_get_video_embed(): void {
		check_ajax_referer( 'wpshadow_video_library_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You don\'t have permission to do that', 'plugin-wpshadow' ) ) );
		}

		$video_id = \WPShadow\WPSHADOW_get_post_key( 'video_id' );
		$library  = get_option( self::VIDEO_LIBRARY_KEY, array() );

		if ( ! isset( $library[ $video_id ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Video not found in library', 'plugin-wpshadow' ) ) );
		}

		$video = $library[ $video_id ];

		wp_send_json_success(
			array(
				'embed_code' => $video['embed_code'] ?? '',
			)
		);
	}

	/**
	 * AJAX handler: Check video service status.
	 *
	 * @return void
	 */
	public static function ajax_check_video_service(): void {
		check_ajax_referer( 'wpshadow_video_library_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You don\'t have permission to do that', 'plugin-wpshadow' ) ) );
		}

		$settings    = get_option( self::VIDEO_SETTINGS_KEY, array() );
		$service_url = $settings['service_url'] ?? '';

		if ( empty( $service_url ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Service URL not configured', 'plugin-wpshadow' ),
					'status'  => 'not_configured',
				)
			);
		}

		$response = wp_remote_get(
			trailingslashit( $service_url ) . 'status',
			array( 'timeout' => 10 )
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Service is offline or unreachable', 'plugin-wpshadow' ),
					'status'  => 'offline',
				)
			);
		}

		wp_send_json_success(
			array(
				'message' => __( 'Service is online and ready', 'plugin-wpshadow' ),
				'status'  => 'online',
			)
		);
	}

	/**
	 * Render video library admin page.
	 *
	 * @return void
	 */
	public static function render_page(): void {
		$library            = get_option( self::VIDEO_LIBRARY_KEY, array() );
		$settings           = get_option( self::VIDEO_SETTINGS_KEY, array() );
		$service_configured = ! empty( $settings['service_url'] );
		?>
		<div class="wrap wps-video-library-page">
			<h1><?php esc_html_e( 'Video Library', 'plugin-wpshadow' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Auto-generated video walkthroughs of your site functionality. Videos are created on-demand and can be downloaded, shared, or embedded on your help pages.', 'plugin-wpshadow' ); ?>
			</p>

			<?php if ( ! $service_configured ) : ?>
				<div class="notice notice-warning">
					<p>
						<strong><?php esc_html_e( 'Video Generation Service Not Configured', 'plugin-wpshadow' ); ?></strong>
					</p>
					<p>
						<?php
						echo wp_kses_post(
							sprintf(
								/* translators: %s: Settings page URL */
								__( 'To generate videos, you need to configure a video generation service. This requires an external Puppeteer/Playwright-based microservice for screen recording and video encoding. <a href="%s">Configure in Settings</a>', 'plugin-wpshadow' ),
								admin_url( 'admin.php?page=wps-settings' )
							)
						);
						?>
					</p>
					<p class="description">
						<?php esc_html_e( 'Recommended: Deploy a dedicated video generation microservice or use a third-party SaaS solution.', 'plugin-wpshadow' ); ?>
					</p>
				</div>
			<?php endif; ?>

			<?php foreach ( self::$video_types as $type_id => $type ) : ?>
				<?php if ( empty( $type['videos'] ) && 'custom' === $type_id ) : ?>
					<?php continue; ?>
				<?php endif; ?>

				<div class="wps-video-section">
					<h2><?php echo esc_html( $type['label'] ); ?></h2>
					<p class="description"><?php echo esc_html( $type['desc'] ); ?></p>

					<div class="wps-video-grid">
						<?php foreach ( $type['videos'] as $video_id => $video ) : ?>
							<?php
							$in_library   = isset( $library[ $video_id ] );
							$video_status = $in_library ? $library[ $video_id ]['status'] : 'not_generated';
							?>
							<div class="wps-video-card" data-video-id="<?php echo esc_attr( $video_id ); ?>">
								<div class="wps-video-thumbnail">
									<?php if ( $in_library && 'completed' === $video_status ) : ?>
										<span class="dashicons dashicons-video-alt3"></span>
									<?php else : ?>
										<span class="dashicons dashicons-format-video"></span>
									<?php endif; ?>
								</div>

								<div class="wps-video-info">
									<h3><?php echo esc_html( $video['title'] ); ?></h3>
									<p class="description"><?php echo esc_html( $video['description'] ); ?></p>

									<?php if ( isset( $video['duration'] ) ) : ?>
										<p class="wps-video-duration">
											<span class="dashicons dashicons-clock"></span>
											<?php echo esc_html( $video['duration'] ); ?>
										</p>
									<?php endif; ?>

									<?php if ( $in_library && 'completed' === $video_status ) : ?>
										<p class="wps-video-generated">
											<span class="dashicons dashicons-yes-alt"></span>
											<?php
											printf(
												/* translators: %s: Time ago string */
												esc_html__( 'Generated %s ago', 'plugin-wpshadow' ),
												esc_html( human_time_diff( $library[ $video_id ]['generated_at'] ) )
											);
											?>
										</p>
									<?php endif; ?>
								</div>

								<div class="wps-video-actions">
									<?php if ( $in_library && 'completed' === $video_status ) : ?>
										<button type="button" class="button wps-watch-video" data-video-id="<?php echo esc_attr( $video_id ); ?>">
											<span class="dashicons dashicons-controls-play"></span>
											<?php esc_html_e( 'Watch', 'plugin-wpshadow' ); ?>
										</button>
										<button type="button" class="button wps-download-video" data-video-id="<?php echo esc_attr( $video_id ); ?>">
											<span class="dashicons dashicons-download"></span>
											<?php esc_html_e( 'Download', 'plugin-wpshadow' ); ?>
										</button>
										<button type="button" class="button wps-embed-video" data-video-id="<?php echo esc_attr( $video_id ); ?>">
											<span class="dashicons dashicons-share"></span>
											<?php esc_html_e( 'Embed', 'plugin-wpshadow' ); ?>
										</button>
										<button type="button" class="button wps-regenerate-video" data-video-id="<?php echo esc_attr( $video_id ); ?>">
											<span class="dashicons dashicons-update"></span>
											<?php esc_html_e( 'Regenerate', 'plugin-wpshadow' ); ?>
										</button>
									<?php else : ?>
										<button type="button" class="button button-primary wps-generate-video" data-video-id="<?php echo esc_attr( $video_id ); ?>" <?php disabled( ! $service_configured ); ?>>
											<span class="dashicons dashicons-video-alt3"></span>
											<?php esc_html_e( 'Generate Video', 'plugin-wpshadow' ); ?>
										</button>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Video player modal -->
		<div id="wps-video-modal" class="wps-modal" style="display: none;">
			<div class="wps-modal-content">
				<span class="wps-modal-close">&times;</span>
				<div class="wps-modal-body">
					<div id="wps-video-player"></div>
				</div>
			</div>
		</div>

		<!-- Embed code modal -->
		<div id="wps-embed-modal" class="wps-modal" style="display: none;">
			<div class="wps-modal-content">
				<span class="wps-modal-close">&times;</span>
				<div class="wps-modal-body">
					<h2><?php esc_html_e( 'Embed Code', 'plugin-wpshadow' ); ?></h2>
					<p><?php esc_html_e( 'Copy this code to embed the video on your site:', 'plugin-wpshadow' ); ?></p>
					<textarea id="wps-embed-code" readonly rows="5"></textarea>
					<button type="button" class="button button-primary" id="wps-copy-embed-code">
						<?php esc_html_e( 'Copy to Clipboard', 'plugin-wpshadow' ); ?>
					</button>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get available video types.
	 *
	 * @return array<string, array<string, mixed>> Video types.
	 */
	public static function get_video_types(): array {
		return self::$video_types;
	}
}
