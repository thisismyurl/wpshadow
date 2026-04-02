<?php
/**
 * Plugin Name: WPShadow Site
 * Description: Lightweight starter plugin scaffold for WPShadow-branded sites.
 * Version: 0.3.0
 * Author: WPShadow
 * License: GPL-2.0-or-later
 * Text Domain: wpshadow-site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Basic constants.
define( 'WPSHADOW_SITE_VERSION', '0.3.0' );
define( 'WPSHADOW_SITE_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPSHADOW_SITE_URL', plugin_dir_url( __FILE__ ) );

// Load podcast generator.
require_once WPSHADOW_SITE_PATH . 'includes/class-podcast-generator.php';
require_once WPSHADOW_SITE_PATH . 'includes/podcast-settings.php';

/**
 * Initialize podcast generator on admin_init.
 */
function wpshadow_site_init_podcast_generator() {
	global $wpshadow_podcast_generator;

	if ( ! isset( $wpshadow_podcast_generator ) ) {
		$wpshadow_podcast_generator = new WPShadow_Podcast_Generator();
	}
}
add_action( 'admin_init', 'wpshadow_site_init_podcast_generator', 0 );

/**
 * Plugin activation hook.
 */
function wpshadow_site_activate() {
	WPShadow_Podcast_Generator::create_queue_table();
}
register_activation_hook( __FILE__, 'wpshadow_site_activate' );

/**
 * Plugin deactivation hook.
 */
function wpshadow_site_deactivate() {
	// Optionally clean up queue on deactivation.
	// WPShadow_Podcast_Generator::drop_queue_table();
	wp_clear_scheduled_hook( 'wpshadow_process_podcast_queue' );
}
register_deactivation_hook( __FILE__, 'wpshadow_site_deactivate' );

/**
 * Register admin menu page.
 */
function wpshadow_site_register_menu() {
	add_menu_page(
		__( 'WPShadow Site', 'wpshadow-site' ),
		__( 'WPShadow Site', 'wpshadow-site' ),
		'manage_options',
		'wpshadow-site',
		'wpshadow_site_render_page',
		'dashicons-shield-alt',
		82
	);
}
add_action( 'admin_menu', 'wpshadow_site_register_menu' );

/**
 * Register settings for ElevenLabs integration.
 */
function wpshadow_site_register_settings() {
	register_setting(
		'wpshadow_site_elevenlabs',
		'wpshadow_site_elevenlabs',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'wpshadow_site_sanitize_elevenlabs',
			'default'           => array(),
		)
	);

	add_settings_section(
		'wpshadow_site_elevenlabs_section',
		__( 'ElevenLabs Settings', 'wpshadow-site' ),
		function () {
			echo '<p>' . esc_html__( 'Provide your ElevenLabs API credentials to enable text-to-speech calls.', 'wpshadow-site' ) . '</p>';
		},
		'wpshadow-site'
	);

	add_settings_field(
		'wpshadow_site_elevenlabs_api_key',
		__( 'API Key', 'wpshadow-site' ),
		'wpshadow_site_render_api_key_field',
		'wpshadow-site',
		'wpshadow_site_elevenlabs_section'
	);

	add_settings_field(
		'wpshadow_site_elevenlabs_voice_id',
		__( 'Voice ID', 'wpshadow-site' ),
		'wpshadow_site_render_voice_id_field',
		'wpshadow-site',
		'wpshadow_site_elevenlabs_section'
	);

	add_settings_field(
		'wpshadow_site_elevenlabs_model_id',
		__( 'Model ID (optional)', 'wpshadow-site' ),
		'wpshadow_site_render_model_id_field',
		'wpshadow-site',
		'wpshadow_site_elevenlabs_section'
	);
}
add_action( 'admin_init', 'wpshadow_site_register_settings' );

/**
 * Sanitize settings payload.
 *
 * @param array $input Raw input.
 * @return array Sanitized settings.
 */
function wpshadow_site_sanitize_elevenlabs( $input ) {
	$input = is_array( $input ) ? $input : array();

	return array(
		'api_key'  => isset( $input['api_key'] ) ? sanitize_text_field( trim( $input['api_key'] ) ) : '',
		'voice_id' => isset( $input['voice_id'] ) ? sanitize_text_field( trim( $input['voice_id'] ) ) : '',
		'model_id' => isset( $input['model_id'] ) ? sanitize_text_field( trim( $input['model_id'] ) ) : '',
	);
}

/**
 * Get ElevenLabs settings with defaults.
 *
 * @return array
 */
function wpshadow_site_get_elevenlabs_settings() {
	$defaults = array(
		'api_key'  => '',
		'voice_id' => '',
		'model_id' => 'eleven_multilingual_v2',
	);

	$stored = get_option( 'wpshadow_site_elevenlabs', array() );

	return wp_parse_args( is_array( $stored ) ? $stored : array(), $defaults );
}

/**
 * Enqueue admin assets for the plugin page only.
 */
function wpshadow_site_admin_assets( $hook ) {
	if ( 'toplevel_page_wpshadow-site' !== $hook ) {
		return;
	}

	wp_enqueue_style(
		'wpshadow-site-admin',
		WPSHADOW_SITE_URL . 'assets/admin.css',
		array(),
		WPSHADOW_SITE_VERSION
	);
}
add_action( 'admin_enqueue_scripts', 'wpshadow_site_admin_assets' );

/**
 * Render settings field: API key.
 */
function wpshadow_site_render_api_key_field() {
	$settings = wpshadow_site_get_elevenlabs_settings();
	printf(
		'<input type="password" name="wpshadow_site_elevenlabs[api_key]" value="%s" class="regular-text" autocomplete="off" placeholder="%s" />',
		esc_attr( $settings['api_key'] ),
		esc_attr__( 'Enter your ElevenLabs API key', 'wpshadow-site' )
	);
	echo '<p class="description">' . esc_html__( 'Stored in the database as plain text. Pair with a secrets manager plugin if needed.', 'wpshadow-site' ) . '</p>';
}

/**
 * Render settings field: Voice ID.
 */
function wpshadow_site_render_voice_id_field() {
	$settings = wpshadow_site_get_elevenlabs_settings();
	printf(
		'<input type="text" name="wpshadow_site_elevenlabs[voice_id]" value="%s" class="regular-text" placeholder="%s" />',
		esc_attr( $settings['voice_id'] ),
		esc_attr__( 'e.g. 21m00Tcm4TlvDq8ikWAM', 'wpshadow-site' )
	);
	echo '<p class="description">' . esc_html__( 'Default voice ID for text-to-speech.', 'wpshadow-site' ) . '</p>';
}

/**
 * Render settings field: Model ID.
 */
function wpshadow_site_render_model_id_field() {
	$settings = wpshadow_site_get_elevenlabs_settings();
	printf(
		'<input type="text" name="wpshadow_site_elevenlabs[model_id]" value="%s" class="regular-text" placeholder="%s" />',
		esc_attr( $settings['model_id'] ),
		esc_attr__( 'eleven_multilingual_v2', 'wpshadow-site' )
	);
	echo '<p class="description">' . esc_html__( 'Optional. Leave blank to use eleven_multilingual_v2.', 'wpshadow-site' ) . '</p>';
}

/**
 * Call ElevenLabs Text-to-Speech.
 *
 * @param string $text Text to synthesize.
 * @param array  $args Optional. model_id, voice_id, voice_settings (array), output_format.
 * @return array|WP_Error Array with audio and content_type, or WP_Error on failure.
 */
function wpshadow_site_elevenlabs_tts( $text, $args = array() ) {
	$text = trim( wp_strip_all_tags( (string) $text ) );
	if ( '' === $text ) {
		return new WP_Error( 'wpshadow-elevenlabs-empty-text', __( 'Text cannot be empty.', 'wpshadow-site' ) );
	}

	$settings = wpshadow_site_get_elevenlabs_settings();
	$api_key  = $settings['api_key'];
	$voice_id = $settings['voice_id'];
	$model_id = isset( $args['model_id'] ) && $args['model_id'] ? $args['model_id'] : $settings['model_id'];
	$output   = isset( $args['output_format'] ) && $args['output_format'] ? $args['output_format'] : 'mp3_44100_128';
	$voice_settings = isset( $args['voice_settings'] ) && is_array( $args['voice_settings'] ) ? $args['voice_settings'] : array(
		'stability'        => 0.5,
		'similarity_boost' => 0.75,
	);

	if ( empty( $api_key ) ) {
		return new WP_Error( 'wpshadow-elevenlabs-missing-key', __( 'ElevenLabs API key is missing.', 'wpshadow-site' ) );
	}

	if ( empty( $voice_id ) ) {
		return new WP_Error( 'wpshadow-elevenlabs-missing-voice', __( 'ElevenLabs voice ID is missing.', 'wpshadow-site' ) );
	}

	$endpoint = 'https://api.elevenlabs.io/v1/text-to-speech/' . rawurlencode( $voice_id );

	$response = wp_remote_post(
		$endpoint,
		array(
			'timeout' => 20,
			'headers' => array(
				'Accept'       => 'audio/mpeg',
				'Content-Type' => 'application/json',
				'xi-api-key'   => $api_key,
			),
			'body'    => wp_json_encode(
				array(
					'text'                       => $text,
					'model_id'                  => $model_id,
					'voice_settings'            => $voice_settings,
					'output_format'             => $output,
					'optimize_streaming_latency' => 0,
				)
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$status = wp_remote_retrieve_response_code( $response );
	if ( 200 !== (int) $status ) {
		$message = wp_remote_retrieve_body( $response );
		return new WP_Error( 'wpshadow-elevenlabs-http', sprintf( __( 'ElevenLabs request failed: %s', 'wpshadow-site' ), $message ) );
	}

	$body         = wp_remote_retrieve_body( $response );
	$content_type = wp_remote_retrieve_header( $response, 'content-type' );

	return array(
		'audio'        => $body,
		'content_type' => $content_type ? $content_type : 'audio/mpeg',
	);
}

/**
 * Render the admin page.
 */
function wpshadow_site_render_page() {
	$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'elevenlabs';
	?>
	<div class="wrap wpshadow-site">
		<h1><?php esc_html_e( 'WPShadow Site', 'wpshadow-site' ); ?></h1>
		<p><?php esc_html_e( 'Welcome to your WPShadow site plugin scaffold. Manage integrations and settings here.', 'wpshadow-site' ); ?></p>

		<!-- Tab Navigation -->
		<h2 class="nav-tab-wrapper">
			<a href="?page=wpshadow-site&tab=elevenlabs" class="nav-tab <?php echo 'elevenlabs' === $active_tab ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'ElevenLabs', 'wpshadow-site' ); ?>
			</a>
			<a href="?page=wpshadow-site&tab=podcast" class="nav-tab <?php echo 'podcast' === $active_tab ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'Podcast Generator', 'wpshadow-site' ); ?>
			</a>
		</h2>

		<!-- ElevenLabs Tab -->
		<?php if ( 'elevenlabs' === $active_tab ) : ?>
			<div class="wpshadow-card">
				<h2><?php esc_html_e( 'ElevenLabs Integration', 'wpshadow-site' ); ?></h2>
				<form method="post" action="options.php">
					<?php
					settings_fields( 'wpshadow_site_elevenlabs' );
					do_settings_sections( 'wpshadow-site' );
					submit_button( __( 'Save Settings', 'wpshadow-site' ) );
					?>
				</form>
			</div>

			<div class="wpshadow-card">
				<h3><?php esc_html_e( 'Usage (PHP)', 'wpshadow-site' ); ?></h3>
				<pre class="wpshadow-code"><code>\$result = wpshadow_site_elevenlabs_tts( 'Hello from WPShadow.' );
if ( ! is_wp_error( \$result ) ) {
    // Do something with \$result['audio'] (audio/mpeg bytes)
}</code></pre>
				<p class="description"><?php esc_html_e( 'You can store or stream the returned audio bytes as needed. A shortcode or block can be added next if desired.', 'wpshadow-site' ); ?></p>
			</div>
		<?php endif; ?>

		<!-- Podcast Tab -->
		<?php if ( 'podcast' === $active_tab ) : ?>
			<div class="wpshadow-card">
				<h2><?php esc_html_e( 'Podcast Generation Settings', 'wpshadow-site' ); ?></h2>
				<form method="post" action="options.php">
					<?php
					settings_fields( 'wpshadow_site_podcast_settings' );
					do_settings_sections( 'wpshadow-site-podcast' );
					submit_button( __( 'Save Podcast Settings', 'wpshadow-site' ) );
					?>
				</form>
			</div>

			<div class="wpshadow-card">
				<h3><?php esc_html_e( 'About Podcast Generation', 'wpshadow-site' ); ?></h3>
				<p><?php esc_html_e( 'When enabled, this feature automatically generates podcasts from KB articles when published. Configure:', 'wpshadow-site' ); ?></p>
				<ul style="margin: 10px 0 10px 20px; line-height: 1.8;">
					<li><strong><?php esc_html_e( 'Title Voice:', 'wpshadow-site' ); ?></strong> <?php esc_html_e( 'Voice for article title narration', 'wpshadow-site' ); ?></li>
					<li><strong><?php esc_html_e( 'Content Voice:', 'wpshadow-site' ); ?></strong> <?php esc_html_e( 'Voice for article body narration', 'wpshadow-site' ); ?></li>
					<li><strong><?php esc_html_e( 'Intro/Outro Audio:', 'wpshadow-site' ); ?></strong> <?php esc_html_e( 'Optional audio clips before/after content', 'wpshadow-site' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'Podcasts are stitched using FFmpeg (if available) and stored in the media library.', 'wpshadow-site' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
	<?php
}
