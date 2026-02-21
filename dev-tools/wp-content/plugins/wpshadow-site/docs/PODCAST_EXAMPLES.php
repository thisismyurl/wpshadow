<?php
/**
 * Example: How to Use the Podcast Integration
 *
 * This file shows practical examples of using the ElevenLabs podcast integration
 * in your WordPress templates and custom code.
 */

// ============================================================================
// EXAMPLE 1: Display Podcast Player in KB Article Template
// ============================================================================

// Add this to your KB article template (e.g., single-kb_article.php):

if ( is_singular( 'kb_article' ) ) {
	$post_id    = get_the_ID();
	$podcast_id = get_post_meta( $post_id, '_wpshadow_podcast_id', true );
	$generated  = get_post_meta( $post_id, '_wpshadow_podcast_generated', true );

	if ( $podcast_id ) {
		echo '<div class="kb-podcast-player">';
		echo '<h3>Listen to this article</h3>';
		echo wp_audio_shortcode( array(
			'src' => wp_get_attachment_url( $podcast_id ),
		) );
		echo '<p class="podcast-generated">Generated: ' . esc_html( $generated ) . '</p>';
		echo '</div>';
	}
}

// ============================================================================
// EXAMPLE 2: Create Shortcode to Display Podcast
// ============================================================================

add_shortcode( 'kb_podcast', function( $atts ) {
	$atts = shortcode_atts( array(
		'id' => get_the_ID(),
	), $atts );

	$podcast_id = get_post_meta( absint( $atts['id'] ), '_wpshadow_podcast_id', true );

	if ( ! $podcast_id ) {
		return '<p>No podcast available for this article.</p>';
	}

	return wp_audio_shortcode( array(
		'src' => wp_get_attachment_url( $podcast_id ),
	) );
});

// Usage in editor: [kb_podcast id="123"]

// ============================================================================
// EXAMPLE 3: Block Rendering Podcast in Gutenberg
// ============================================================================

// Add to your plugin or theme's functions.php:

add_action( 'init', function() {
	register_block_type( 'wpshadow/podcast-player', array(
		'render_callback' => function( $attributes ) {
			global $post;

			if ( ! $post ) {
				return '';
			}

			$podcast_id = get_post_meta( $post->ID, '_wpshadow_podcast_id', true );

			if ( ! $podcast_id ) {
				return '';
			}

			return wp_audio_shortcode( array(
				'src' => wp_get_attachment_url( $podcast_id ),
			) );
		},
	) );
});

// ============================================================================
// EXAMPLE 4: Custom Podcast Generation Processing
// ============================================================================

// Manually trigger podcast generation with custom settings:

function my_custom_podcast_generation( $post_id ) {
	global $wpshadow_podcast_generator;

	if ( ! isset( $wpshadow_podcast_generator ) ) {
		return new WP_Error( 'generator-not-available', 'Podcast generator not initialized.' );
	}

	// Queue the post for processing
	$wpshadow_podcast_generator->queue_podcast_generation( get_post( $post_id ), get_post( $post_id ) );

	// Optionally trigger immediate processing
	WPShadow_Podcast_Generator::trigger_queue_processing();
}

// ============================================================================
// EXAMPLE 5: Get Podcast Generation Status
// ============================================================================

function get_podcast_generation_status( $post_id ) {
	$queue_items = get_option( 'wpshadow_podcast_queue', array() );

	if ( ! is_array( $queue_items ) ) {
		return null;
	}

	$latest = null;

	foreach ( $queue_items as $item ) {
		if ( (int) ( $item['post_id'] ?? 0 ) !== (int) $post_id ) {
			continue;
		}

		if ( null === $latest || strtotime( (string) $item['created_at'] ) > strtotime( (string) $latest['created_at'] ) ) {
			$latest = $item;
		}
	}

	if ( null === $latest ) {
		return null;
	}

	return array(
		'status'  => (string) ( $latest['status'] ?? '' ),
		'error'   => (string) ( $latest['error_message'] ?? '' ),
		'created' => (string) ( $latest['created_at'] ?? '' ),
		'updated' => (string) ( $latest['updated_at'] ?? '' ),
	);
}

// Usage:
// $status = get_podcast_generation_status( 123 );
// if ( $status['status'] === 'completed' ) {
//     echo 'Podcast ready!';
// }

// ============================================================================
// EXAMPLE 6: Custom Voice Settings by Post Meta
// ============================================================================

// Allow per-article voice customization via post meta:

add_filter( 'wpshadow_podcast_settings', function( $settings ) {
	global $post;

	if ( ! $post ) {
		return $settings;
	}

	// Check for custom voice IDs stored as post meta
	$custom_title_voice   = get_post_meta( $post->ID, '_podcast_title_voice', true );
	$custom_content_voice = get_post_meta( $post->ID, '_podcast_content_voice', true );

	if ( $custom_title_voice ) {
		$settings['title_voice_id'] = $custom_title_voice;
	}

	if ( $custom_content_voice ) {
		$settings['content_voice_id'] = $custom_content_voice;
	}

	return $settings;
});

// In post editor, add custom meta box:

add_action( 'add_meta_boxes', function() {
	add_meta_box(
		'podcast-settings',
		'Podcast Voice Settings',
		function( $post ) {
			$title_voice   = get_post_meta( $post->ID, '_podcast_title_voice', true );
			$content_voice = get_post_meta( $post->ID, '_podcast_content_voice', true );

			wp_nonce_field( 'save_podcast_voice', 'podcast_voice_nonce' );
			?>
			<p>
				<label>Title Voice ID:</label><br>
				<input type="text" name="podcast_title_voice" value="<?php echo esc_attr( $title_voice ); ?>" placeholder="Leave blank to use default">
			</p>
			<p>
				<label>Content Voice ID:</label><br>
				<input type="text" name="podcast_content_voice" value="<?php echo esc_attr( $content_voice ); ?>" placeholder="Leave blank to use default">
			</p>
			<?php
		},
		'kb_article'
	);
});

add_action( 'save_post', function( $post_id ) {
	if ( ! isset( $_POST['podcast_voice_nonce'] ) || ! wp_verify_nonce( $_POST['podcast_voice_nonce'], 'save_podcast_voice' ) ) {
		return;
	}

	if ( isset( $_POST['podcast_title_voice'] ) ) {
		update_post_meta( $post_id, '_podcast_title_voice', sanitize_text_field( $_POST['podcast_title_voice'] ) );
	}

	if ( isset( $_POST['podcast_content_voice'] ) ) {
		update_post_meta( $post_id, '_podcast_content_voice', sanitize_text_field( $_POST['podcast_content_voice'] ) );
	}
});

// ============================================================================
// EXAMPLE 7: WP-CLI Commands
// ============================================================================

// Add to your plugin or custom code:

if ( class_exists( 'WP_CLI' ) ) {
	class Podcast_Generator_CLI {
		/**
		 * Process podcast queue
		 *
		 * wp podcast generate
		 */
		public function generate() {
			global $wpshadow_podcast_generator;

			if ( ! isset( $wpshadow_podcast_generator ) ) {
				WP_CLI::error( 'Podcast generator not initialized.' );
			}

			$wpshadow_podcast_generator->process_queue_item();
			WP_CLI::success( 'Podcast queue processed.' );
		}

		/**
		 * Show podcast status
		 *
		 * wp podcast status
		 */
		public function status() {
			$queue_items = get_option( 'wpshadow_podcast_queue', array() );
			if ( ! is_array( $queue_items ) ) {
				$queue_items = array();
			}

			$pending = 0;
			$failed  = 0;

			foreach ( $queue_items as $item ) {
				$status = isset( $item['status'] ) ? $item['status'] : '';

				if ( 'pending' === $status ) {
					++$pending;
				}

				if ( 'failed' === $status ) {
					++$failed;
				}
			}

			WP_CLI::log( "Pending: $pending" );
			WP_CLI::log( "Failed: $failed" );
		}

		/**
		 * Regenerate podcast for specific article
		 *
		 * wp podcast regenerate <post_id>
		 */
		public function regenerate( $args ) {
			$post_id = absint( $args[0] );
			$post    = get_post( $post_id );

			if ( ! $post ) {
				WP_CLI::error( "Post $post_id not found." );
			}

			// Clear existing podcast
			delete_post_meta( $post_id, '_wpshadow_podcast_id' );
			delete_post_meta( $post_id, '_wpshadow_podcast_generated' );

			// Queue for regeneration
			global $wpshadow_podcast_generator;
			$wpshadow_podcast_generator->queue_podcast_generation( $post_id, $post );

			WP_CLI::success( "Post $post_id queued for podcast regeneration." );
		}
	}

	WP_CLI::add_command( 'podcast', 'Podcast_Generator_CLI' );
}

// ============================================================================
// EXAMPLE 8: REST API Endpoint
// ============================================================================

// Add podcast info to REST API response:

add_filter( 'rest_prepare_kb_article', function( $response, $post ) {
	$podcast_id = get_post_meta( $post->ID, '_wpshadow_podcast_id', true );
	$generated  = get_post_meta( $post->ID, '_wpshadow_podcast_generated', true );

	$response->data['podcast'] = array(
		'id'          => $podcast_id ? absint( $podcast_id ) : null,
		'url'         => $podcast_id ? wp_get_attachment_url( $podcast_id ) : null,
		'generated'   => $generated ? $generated : null,
	);

	return $response;
}, 10, 2 );

// ============================================================================
// EXAMPLE 9: Error Handling in Custom Code
// ============================================================================

function my_safe_podcast_call( $post_id ) {
	global $wpshadow_podcast_generator;

	try {
		$result = $wpshadow_podcast_generator->process_queue_item( $post_id );

		if ( is_wp_error( $result ) ) {
			error_log( 'Podcast generation error: ' . $result->get_error_message() );
			return false;
		}

		return true;
	} catch ( Exception $e ) {
		error_log( 'Podcast exception: ' . $e->getMessage() );
		return false;
	}
}

// ============================================================================
// EXAMPLE 10: Notification on Completion
// ============================================================================

// Send email when podcast is ready:

add_action( 'wpshadow_process_podcast_queue', function() {
	$queue_items = get_option( 'wpshadow_podcast_queue', array() );

	if ( ! is_array( $queue_items ) ) {
		return;
	}

	$cutoff = time() - ( 5 * MINUTE_IN_SECONDS );

	foreach ( $queue_items as $item ) {
		if ( 'completed' !== ( $item['status'] ?? '' ) ) {
			continue;
		}

		$updated_at = isset( $item['updated_at'] ) ? strtotime( (string) $item['updated_at'] ) : 0;
		if ( $updated_at < $cutoff ) {
			continue;
		}

		$post = get_post( (int) ( $item['post_id'] ?? 0 ) );

		if ( ! $post ) {
			continue;
		}

		$author = get_userdata( $post->post_author );

		wp_mail(
			$author->user_email,
			'Your podcast is ready: ' . $post->post_title,
			'The podcast for "' . $post->post_title . '" has been generated and is ready for distribution.',
			array( 'Content-Type: text/html; charset=UTF-8' )
		);
	}
});
