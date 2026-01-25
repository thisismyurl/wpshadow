<?php
/**
 * Admin Settings for Podcast Generation
 *
 * @package WPShadow_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register podcast generation settings.
 */
function wpshadow_site_register_podcast_settings() {
	register_setting(
		'wpshadow_site_podcast_settings',
		'wpshadow_podcast_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'wpshadow_site_sanitize_podcast_settings',
			'default'           => array(),
		)
	);

	add_settings_section(
		'wpshadow_site_podcast_section',
		__( 'Podcast Generation Settings', 'wpshadow-site' ),
		function () {
			echo '<p>' . esc_html__( 'Configure automatic podcast generation for KB articles.', 'wpshadow-site' ) . '</p>';
		},
		'wpshadow-site-podcast'
	);

	// Enable podcasts.
	add_settings_field(
		'wpshadow_podcast_enabled',
		__( 'Enable Podcast Generation', 'wpshadow-site' ),
		'wpshadow_site_render_podcast_enabled_field',
		'wpshadow-site-podcast',
		'wpshadow_site_podcast_section'
	);

	// Title voice.
	add_settings_field(
		'wpshadow_podcast_title_voice',
		__( 'Title Voice', 'wpshadow-site' ),
		'wpshadow_site_render_podcast_title_voice_field',
		'wpshadow-site-podcast',
		'wpshadow_site_podcast_section'
	);

	// Content voice.
	add_settings_field(
		'wpshadow_podcast_content_voice',
		__( 'Content Voice', 'wpshadow-site' ),
		'wpshadow_site_render_podcast_content_voice_field',
		'wpshadow-site-podcast',
		'wpshadow_site_podcast_section'
	);

	// Include title in podcast.
	add_settings_field(
		'wpshadow_podcast_include_title',
		__( 'Include Article Title in Podcast', 'wpshadow-site' ),
		'wpshadow_site_render_podcast_include_title_field',
		'wpshadow-site-podcast',
		'wpshadow_site_podcast_section'
	);

	// Intro audio.
	add_settings_field(
		'wpshadow_podcast_intro_audio',
		__( 'Intro Audio (Optional)', 'wpshadow-site' ),
		'wpshadow_site_render_podcast_intro_audio_field',
		'wpshadow-site-podcast',
		'wpshadow_site_podcast_section'
	);

	// Outro audio.
	add_settings_field(
		'wpshadow_podcast_outro_audio',
		__( 'Outro Audio (Optional)', 'wpshadow-site' ),
		'wpshadow_site_render_podcast_outro_audio_field',
		'wpshadow-site-podcast',
		'wpshadow_site_podcast_section'
	);

	// Auto-create podcast post.
	add_settings_field(
		'wpshadow_podcast_auto_create_post',
		__( 'Auto-Create Podcast Post', 'wpshadow-site' ),
		'wpshadow_site_render_podcast_auto_create_post_field',
		'wpshadow-site-podcast',
		'wpshadow_site_podcast_section'
	);
}
add_action( 'admin_init', 'wpshadow_site_register_podcast_settings' );

/**
 * Sanitize podcast settings.
 *
 * @param array $input Raw input.
 * @return array Sanitized settings.
 */
function wpshadow_site_sanitize_podcast_settings( $input ) {
	$input = is_array( $input ) ? $input : array();

	return array(
		'enabled'            => isset( $input['enabled'] ) ? true : false,
		'title_voice_id'     => isset( $input['title_voice_id'] ) ? sanitize_text_field( trim( $input['title_voice_id'] ) ) : '',
		'content_voice_id'   => isset( $input['content_voice_id'] ) ? sanitize_text_field( trim( $input['content_voice_id'] ) ) : '',
		'intro_audio_id'     => isset( $input['intro_audio_id'] ) ? absint( $input['intro_audio_id'] ) : 0,
		'outro_audio_id'     => isset( $input['outro_audio_id'] ) ? absint( $input['outro_audio_id'] ) : 0,
		'include_title'      => isset( $input['include_title'] ) ? true : false,
		'auto_create_post'   => isset( $input['auto_create_post'] ) ? true : false,
	);
}

/**
 * Get podcast settings with defaults.
 *
 * @return array
 */
function wpshadow_site_get_podcast_settings() {
	$defaults = array(
		'enabled'            => false,
		'title_voice_id'     => '',
		'content_voice_id'   => '',
		'intro_audio_id'     => 0,
		'outro_audio_id'     => 0,
		'include_title'      => true,
		'auto_create_post'   => false,
	);

	$stored = get_option( 'wpshadow_podcast_settings', array() );

	return wp_parse_args( is_array( $stored ) ? $stored : array(), $defaults );
}

/**
 * Render enabled field.
 */
function wpshadow_site_render_podcast_enabled_field() {
	$settings = wpshadow_site_get_podcast_settings();
	printf(
		'<input type="checkbox" name="wpshadow_podcast_settings[enabled]" value="1" %s />',
		checked( $settings['enabled'], true, false )
	);
	echo '<p class="description">' . esc_html__( 'Enable automatic podcast generation when KB articles are published.', 'wpshadow-site' ) . '</p>';
}

/**
 * Render title voice field.
 */
function wpshadow_site_render_podcast_title_voice_field() {
	$settings = wpshadow_site_get_podcast_settings();
	printf(
		'<input type="text" name="wpshadow_podcast_settings[title_voice_id]" value="%s" class="regular-text" placeholder="%s" />',
		esc_attr( $settings['title_voice_id'] ),
		esc_attr__( 'e.g., 21m00Tcm4TlvDq8ikWAM', 'wpshadow-site' )
	);
	echo '<p class="description">' . esc_html__( 'Voice ID for article title narration.', 'wpshadow-site' ) . '</p>';
}

/**
 * Render content voice field.
 */
function wpshadow_site_render_podcast_content_voice_field() {
	$settings = wpshadow_site_get_podcast_settings();
	printf(
		'<input type="text" name="wpshadow_podcast_settings[content_voice_id]" value="%s" class="regular-text" placeholder="%s" />',
		esc_attr( $settings['content_voice_id'] ),
		esc_attr__( 'e.g., pNInz6obpgc4xHEEFXtD', 'wpshadow-site' )
	);
	echo '<p class="description">' . esc_html__( 'Voice ID for article content narration.', 'wpshadow-site' ) . '</p>';
}

/**
 * Render include title field.
 */
function wpshadow_site_render_podcast_include_title_field() {
	$settings = wpshadow_site_get_podcast_settings();
	printf(
		'<input type="checkbox" name="wpshadow_podcast_settings[include_title]" value="1" %s />',
		checked( $settings['include_title'], true, false )
	);
	echo '<p class="description">' . esc_html__( 'Include the article title as narrated audio at the beginning.', 'wpshadow-site' ) . '</p>';
}

/**
 * Render intro audio field (media picker).
 */
function wpshadow_site_render_podcast_intro_audio_field() {
	$settings = wpshadow_site_get_podcast_settings();
	$intro_id = $settings['intro_audio_id'];

	echo '<div id="wpshadow-intro-audio-preview">';
	if ( $intro_id ) {
		$intro = get_post( $intro_id );
		if ( $intro ) {
			echo '<p><strong>' . esc_html__( 'Selected:', 'wpshadow-site' ) . '</strong> ' . esc_html( $intro->post_title ) . '</p>';
		}
	}
	echo '</div>';

	printf(
		'<input type="hidden" name="wpshadow_podcast_settings[intro_audio_id]" id="wpshadow-intro-audio-id" value="%d" />',
		absint( $intro_id )
	);

	printf(
		'<button type="button" class="button" id="wpshadow-intro-audio-button">%s</button>',
		esc_html__( 'Select Intro Audio', 'wpshadow-site' )
	);

	if ( $intro_id ) {
		echo ' <button type="button" class="button" id="wpshadow-intro-audio-clear">' . esc_html__( 'Clear', 'wpshadow-site' ) . '</button>';
	}

	echo '<p class="description">' . esc_html__( 'Optional: Audio file to play before the article content.', 'wpshadow-site' ) . '</p>';

	// Inline script to handle media picker.
	?>
	<script>
	(function($) {
		$(document).ready(function() {
			var mediaFrame;

			$('#wpshadow-intro-audio-button').on('click', function(e) {
				e.preventDefault();

				if (mediaFrame) {
					mediaFrame.open();
					return;
				}

				mediaFrame = wp.media({
					title: '<?php echo esc_js( __( 'Select Intro Audio', 'wpshadow-site' ) ); ?>',
					button: {
						text: '<?php echo esc_js( __( 'Select', 'wpshadow-site' ) ); ?>'
					},
					multiple: false,
					library: { type: 'audio' }
				});

				mediaFrame.on('select', function() {
					var attachment = mediaFrame.state().get('selection').first().toJSON();
					$('#wpshadow-intro-audio-id').val(attachment.id);
					$('#wpshadow-intro-audio-preview').html(
						'<p><strong><?php echo esc_js( __( 'Selected:', 'wpshadow-site' ) ); ?></strong> ' +
						attachment.title +
						' (<a href="#" id="wpshadow-intro-audio-clear"><?php echo esc_js( __( 'Clear', 'wpshadow-site' ) ); ?></a>)</p>'
					);

					$('#wpshadow-intro-audio-clear').on('click', function(e2) {
						e2.preventDefault();
						$('#wpshadow-intro-audio-id').val('');
						$('#wpshadow-intro-audio-preview').html('');
						location.reload();
					});
				});

				mediaFrame.open();
			});

			$('#wpshadow-intro-audio-clear').on('click', function(e) {
				e.preventDefault();
				$('#wpshadow-intro-audio-id').val('');
				$('#wpshadow-intro-audio-preview').html('');
				location.reload();
			});
		});
	})(jQuery);
	</script>
	<?php
}

/**
 * Render outro audio field (media picker).
 */
function wpshadow_site_render_podcast_outro_audio_field() {
	$settings = wpshadow_site_get_podcast_settings();
	$outro_id = $settings['outro_audio_id'];

	echo '<div id="wpshadow-outro-audio-preview">';
	if ( $outro_id ) {
		$outro = get_post( $outro_id );
		if ( $outro ) {
			echo '<p><strong>' . esc_html__( 'Selected:', 'wpshadow-site' ) . '</strong> ' . esc_html( $outro->post_title ) . '</p>';
		}
	}
	echo '</div>';

	printf(
		'<input type="hidden" name="wpshadow_podcast_settings[outro_audio_id]" id="wpshadow-outro-audio-id" value="%d" />',
		absint( $outro_id )
	);

	printf(
		'<button type="button" class="button" id="wpshadow-outro-audio-button">%s</button>',
		esc_html__( 'Select Outro Audio', 'wpshadow-site' )
	);

	if ( $outro_id ) {
		echo ' <button type="button" class="button" id="wpshadow-outro-audio-clear">' . esc_html__( 'Clear', 'wpshadow-site' ) . '</button>';
	}

	echo '<p class="description">' . esc_html__( 'Optional: Audio file to play after the article content.', 'wpshadow-site' ) . '</p>';

	// Inline script to handle media picker.
	?>
	<script>
	(function($) {
		$(document).ready(function() {
			var mediaFrame;

			$('#wpshadow-outro-audio-button').on('click', function(e) {
				e.preventDefault();

				if (mediaFrame) {
					mediaFrame.open();
					return;
				}

				mediaFrame = wp.media({
					title: '<?php echo esc_js( __( 'Select Outro Audio', 'wpshadow-site' ) ); ?>',
					button: {
						text: '<?php echo esc_js( __( 'Select', 'wpshadow-site' ) ); ?>'
					},
					multiple: false,
					library: { type: 'audio' }
				});

				mediaFrame.on('select', function() {
					var attachment = mediaFrame.state().get('selection').first().toJSON();
					$('#wpshadow-outro-audio-id').val(attachment.id);
					$('#wpshadow-outro-audio-preview').html(
						'<p><strong><?php echo esc_js( __( 'Selected:', 'wpshadow-site' ) ); ?></strong> ' +
						attachment.title +
						' (<a href="#" id="wpshadow-outro-audio-clear"><?php echo esc_js( __( 'Clear', 'wpshadow-site' ) ); ?></a>)</p>'
					);

					$('#wpshadow-outro-audio-clear').on('click', function(e2) {
						e2.preventDefault();
						$('#wpshadow-outro-audio-id').val('');
						$('#wpshadow-outro-audio-preview').html('');
						location.reload();
					});
				});

				mediaFrame.open();
			});

			$('#wpshadow-outro-audio-clear').on('click', function(e) {
				e.preventDefault();
				$('#wpshadow-outro-audio-id').val('');
				$('#wpshadow-outro-audio-preview').html('');
				location.reload();
			});
		});
	})(jQuery);
	</script>
	<?php
}

/**
 * Render auto-create post field.
 */
function wpshadow_site_render_podcast_auto_create_post_field() {
	$settings = wpshadow_site_get_podcast_settings();
	printf(
		'<input type="checkbox" name="wpshadow_podcast_settings[auto_create_post]" value="1" %s />',
		checked( $settings['auto_create_post'], true, false )
	);
	echo '<p class="description">' . esc_html__( 'Automatically create a podcast post linked to each generated audio.', 'wpshadow-site' ) . '</p>';
}

/**
 * Add podcast tab to admin page.
 */
function wpshadow_site_add_podcast_tab() {
	global $pagenow;

	if ( 'admin.php' !== $pagenow || ! isset( $_GET['page'] ) || 'wpshadow-site' !== $_GET['page'] ) {
		return;
	}

	// Add tab navigation.
	?>
	<style>
		.wpshadow-site-tabs {
			display: flex;
			gap: 10px;
			margin-bottom: 20px;
			border-bottom: 1px solid #ccc;
		}

		.wpshadow-site-tabs a {
			padding: 10px 15px;
			text-decoration: none;
			border-bottom: 3px solid transparent;
			transition: border-color 0.3s;
		}

		.wpshadow-site-tabs a.active {
			border-bottom-color: #0073aa;
			color: #0073aa;
		}

		.wpshadow-site-tab-content {
			display: none;
		}

		.wpshadow-site-tab-content.active {
			display: block;
		}
	</style>
	<script>
	(function($) {
		$(document).ready(function() {
			$('.wpshadow-site-tabs a').on('click', function(e) {
				e.preventDefault();
				var tab = $(this).attr('data-tab');
				$('.wpshadow-site-tab-content').removeClass('active');
				$('.wpshadow-site-tabs a').removeClass('active');
				$(this).addClass('active');
				$('.wpshadow-site-tab-content[data-tab="' + tab + '"]').addClass('active');
			});
		});
	})(jQuery);
	</script>
	<?php
}
add_action( 'admin_head', 'wpshadow_site_add_podcast_tab' );
