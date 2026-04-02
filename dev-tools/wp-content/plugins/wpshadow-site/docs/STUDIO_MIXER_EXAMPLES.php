<?php
/**
 * Studio Mixer Examples
 *
 * Real-world examples of how to use WPShadow_Podcast_Studio_Mixer
 * for professional two-person podcast production.
 *
 * @package WPShadow_Site
 */

// NOTE: These are example code snippets. See STUDIO_MIXER_GUIDE.md for full usage.

// ==============================================================================
// EXAMPLE 1: Basic Podcast Generation
// ==============================================================================

function wpshadow_example_basic_podcast() {
	/**
	 * Simple example: Generate a podcast from article content.
	 */
	
	$mixer = new WPShadow_Podcast_Studio_Mixer();
	
	$config = array(
		// Voice IDs from your ElevenLabs account.
		'speaker1_voice_id' => '21m00Tcm4TlvDq8ikWAM', // Host
		'speaker2_voice_id' => 'EXAVITQu4vr4xnSDxMaL', // Guest
		
		// Intro: Music with host introduction.
		'intro_config' => array(
			'narration'  => 'Welcome to the Tech Talks podcast! Today we discuss the future of AI. I\'m your host John Smith.',
			'music_file' => 'theme-intro.mp3', // File in uploads directory or attachment ID.
		),
		
		// Main episode.
		'episode_config' => array(
			'title'            => 'The Future of Artificial Intelligence',
			'description'      => 'A discussion about trends in AI for 2026.',
			'content'          => '[SPEAKER 1]: Today we have Dr. Jane Doe, an AI expert. Welcome!
[SPEAKER 2]: Thanks for having me. I\'m excited to discuss this.
[SPEAKER 1]: Let\'s start with the latest developments in machine learning.
[SPEAKER 2]: Well, I see three major trends emerging...',
			'background_music' => 'ambient-bg.mp3', // Plays softly under conversation.
		),
		
		// Outro: Host thanks audience and mentions sponsor.
		'outro_config' => array(
			'narration'       => 'Thank you so much for the insightful conversation!',
			'sponsor_mention' => 'This episode brought to you by TechCorp, leaders in enterprise AI.',
			'cta'             => 'Please subscribe to our podcast and share with colleagues interested in AI trends.',
			'music_file'      => 'theme-outro.mp3',
		),
		
		'post_id' => 42, // Optional: link to WordPress post.
	);
	
	// Generate the podcast.
	$result = $mixer->generate_professional_podcast( $config );
	
	if ( is_wp_error( $result ) ) {
		echo 'Error: ' . $result->get_error_message();
		return false;
	}
	
	echo 'Podcast generated: ' . $result['podcast_file'];
	return $result['podcast_file'];
}


// ==============================================================================
// EXAMPLE 2: Using WordPress Media Library Attachments
// ==============================================================================

function wpshadow_example_media_library_podcast() {
	/**
	 * Use audio files uploaded to WordPress media library by attachment ID.
	 */
	
	// Assuming these are uploaded and you have the attachment IDs:
	$intro_music_attachment_id   = 456;
	$bg_music_attachment_id      = 457;
	$outro_music_attachment_id   = 458;
	
	$mixer = new WPShadow_Podcast_Studio_Mixer();
	
	$config = array(
		'speaker1_voice_id' => '21m00Tcm4TlvDq8ikWAM',
		'speaker2_voice_id' => 'EXAVITQu4vr4xnSDxMaL',
		
		'intro_config' => array(
			'narration'  => 'Welcome to the podcast!',
			'music_file' => $intro_music_attachment_id, // Use attachment ID directly.
		),
		
		'episode_config' => array(
			'title'            => 'Episode Title',
			'description'      => 'Episode description',
			'content'          => '...',
			'background_music' => $bg_music_attachment_id, // Attachment ID.
		),
		
		'outro_config' => array(
			'narration'       => 'Thanks for listening!',
			'sponsor_mention' => 'Sponsor message',
			'music_file'      => $outro_music_attachment_id,
		),
	);
	
	return $mixer->generate_professional_podcast( $config );
}


// ==============================================================================
// EXAMPLE 3: Integration with KB Article Publishing
// ==============================================================================

/**
 * Hook into KB article publishing to auto-generate podcasts.
 */
function wpshadow_example_kb_auto_podcast( $post_id ) {
	if ( 'kb_article' !== get_post_type( $post_id ) ) {
		return;
	}
	
	$post = get_post( $post_id );
	
	// Get stored settings.
	$settings = get_option( 'wpshadow_podcast_settings', array() );
	
	if ( empty( $settings['speaker1_voice_id'] ) ) {
		return; // Not configured.
	}
	
	// Prepare content: extract from post.
	$content = wp_strip_all_tags( $post->post_content );
	$content = substr( $content, 0, 5000 ); // Limit length.
	
	// Convert to two-speaker format.
	$sentences = explode( '.', $content );
	$podcast_content = '';
	$speaker = 1;
	
	foreach ( $sentences as $sentence ) {
		if ( ! empty( trim( $sentence ) ) ) {
			$podcast_content .= '[SPEAKER ' . $speaker . ']: ' . trim( $sentence ) . '. ';
			$speaker = ( $speaker === 1 ) ? 2 : 1; // Alternate speakers.
		}
	}
	
	// Build configuration.
	$config = array(
		'speaker1_voice_id' => $settings['speaker1_voice_id'],
		'speaker2_voice_id' => $settings['speaker2_voice_id'] ?? $settings['speaker1_voice_id'],
		
		'intro_config' => array(
			'narration'  => sprintf(
				'Welcome to episode about %s. Let\'s dive into this fascinating topic.',
				$post->post_title
			),
			'music_file' => $settings['intro_audio_id'] ?? 'theme-intro.mp3',
		),
		
		'episode_config' => array(
			'title'            => $post->post_title,
			'description'      => get_the_excerpt( $post_id ),
			'content'          => $podcast_content,
			'background_music' => $settings['background_music_id'] ?? null,
		),
		
		'outro_config' => array(
			'narration'       => 'That covers the key points!',
			'sponsor_mention' => sprintf( 'This episode brought to you by %s.', get_bloginfo( 'name' ) ),
			'cta'             => 'Subscribe to our podcast for more great content like this!',
			'music_file'      => $settings['outro_audio_id'] ?? 'theme-outro.mp3',
		),
		
		'post_id' => $post_id,
	);
	
	// Generate podcast.
	$mixer = new WPShadow_Podcast_Studio_Mixer();
	$result = $mixer->generate_professional_podcast( $config );
	
	if ( ! is_wp_error( $result ) ) {
		// Store podcast file reference.
		update_post_meta( $post_id, '_podcast_file', $result['podcast_file'] );
		update_post_meta( $post_id, '_podcast_generated', current_time( 'mysql' ) );
		
		// Optionally: upload to media library.
		$attachment_id = wpshadow_upload_podcast_to_media_library(
			$result['podcast_file'],
			$post_id,
			$post->post_title
		);
		
		update_post_meta( $post_id, '_podcast_attachment_id', $attachment_id );
	}
}

// Uncomment to enable:
// add_action( 'publish_kb_article', 'wpshadow_example_kb_auto_podcast' );


// ==============================================================================
// EXAMPLE 4: Structured Interview Format
// ==============================================================================

function wpshadow_example_interview_podcast() {
	/**
	 * Generate a structured interview with Q&A format.
	 */
	
	// Build interview transcript with clear speaker separation.
	$interview_script = '[SPEAKER 1]: Welcome back to the show! Today we have Jane Doe, CEO of TechCorp.
[SPEAKER 2]: Thanks for having me!
[SPEAKER 1]: So Jane, tell us about the new AI initiative at TechCorp.
[SPEAKER 2]: Well, we\'ve invested heavily in transformer models and natural language processing. Our goal is to make AI accessible to small businesses.
[SPEAKER 1]: That sounds ambitious. What challenges do you foresee?
[SPEAKER 2]: The main challenge is talent. There aren\'t enough AI engineers to go around. We\'re focusing on training programs to build the next generation of AI specialists.
[SPEAKER 1]: That\'s excellent. Any advice for startups entering the AI space?
[SPEAKER 2]: Focus on solving a specific problem. Don\'t try to build a general AI solution. Be specific, be focused, and iterate quickly.
[SPEAKER 1]: Great advice. Jane, thanks so much for joining us!
[SPEAKER 2]: My pleasure!';
	
	$mixer = new WPShadow_Podcast_Studio_Mixer();
	
	$config = array(
		'speaker1_voice_id' => '21m00Tcm4TlvDq8ikWAM', // Host
		'speaker2_voice_id' => 'EXAVITQu4vr4xnSDxMaL', // Guest (Jane)
		
		'intro_config' => array(
			'narration'  => 'Welcome to Tech Leaders Podcast, episode 15. I\'m your host, and today we have Jane Doe, CEO of TechCorp. Let\'s talk about AI and the future of business.',
			'music_file' => 'theme.mp3',
		),
		
		'episode_config' => array(
			'title'            => 'Tech Leaders: Jane Doe on AI Innovation',
			'description'      => 'An interview with Jane Doe, CEO of TechCorp, about AI initiatives and industry trends.',
			'content'          => $interview_script,
			'background_music' => 'ambient.mp3',
		),
		
		'outro_config' => array(
			'narration'       => 'That was a fantastic conversation with Jane about AI and the future of technology.',
			'sponsor_mention' => 'This episode is brought to you by CloudServer Pro, enterprise cloud infrastructure.',
			'cta'             => 'Subscribe and give us a five-star review! Share this episode with other tech leaders.',
			'music_file'      => 'theme.mp3',
		),
	);
	
	return $mixer->generate_professional_podcast( $config );
}


// ==============================================================================
// EXAMPLE 5: Batch Processing Multiple Episodes
// ==============================================================================

function wpshadow_example_batch_generate_podcasts() {
	/**
	 * Generate podcasts for multiple KB articles at once.
	 */
	
	// Get recent unpublished articles.
	$args = array(
		'post_type'      => 'kb_article',
		'posts_per_page' => 10,
		'meta_query'     => array(
			array(
				'key'     => '_podcast_attachment_id',
				'compare' => 'NOT EXISTS',
			),
		),
	);
	
	$query = new WP_Query( $args );
	
	if ( ! $query->have_posts() ) {
		echo 'No articles to process.';
		return;
	}
	
	$results = array(
		'success' => 0,
		'failed'  => 0,
		'errors'  => array(),
	);
	
	$mixer = new WPShadow_Podcast_Studio_Mixer();
	
	while ( $query->have_posts() ) {
		$query->the_post();
		$post_id = get_the_ID();
		
		try {
			$config = array(
				'speaker1_voice_id' => '21m00Tcm4TlvDq8ikWAM',
				'speaker2_voice_id' => 'EXAVITQu4vr4xnSDxMaL',
				
				'intro_config' => array(
					'narration'  => 'Welcome to episode about ' . get_the_title(),
					'music_file' => 'theme.mp3',
				),
				
				'episode_config' => array(
					'title'            => get_the_title(),
					'description'      => get_the_excerpt(),
					'content'          => wp_strip_all_tags( get_the_content() ),
					'background_music' => 'ambient.mp3',
				),
				
				'outro_config' => array(
					'narration'       => 'Thanks for listening!',
					'sponsor_mention' => 'Brought to you by our partners.',
					'music_file'      => 'theme.mp3',
				),
				
				'post_id' => $post_id,
			);
			
			$result = $mixer->generate_professional_podcast( $config );
			
			if ( is_wp_error( $result ) ) {
				throw new Exception( $result->get_error_message() );
			}
			
			// Store result.
			update_post_meta( $post_id, '_podcast_file', $result['podcast_file'] );
			
			$results['success']++;
			
		} catch ( Exception $e ) {
			$results['failed']++;
			$results['errors'][ $post_id ] = $e->getMessage();
		}
	}
	
	wp_reset_postdata();
	
	return $results;
}


// ==============================================================================
// EXAMPLE 6: Custom Audio Configuration per Post
// ==============================================================================

function wpshadow_example_post_specific_podcast( $post_id ) {
	/**
	 * Use post metadata to customize podcast generation per article.
	 */
	
	// Get custom settings from post meta.
	$speaker1_voice = get_post_meta( $post_id, '_podcast_speaker1_voice', true );
	$speaker2_voice = get_post_meta( $post_id, '_podcast_speaker2_voice', true );
	$intro_narration = get_post_meta( $post_id, '_podcast_intro_text', true );
	$outro_narration = get_post_meta( $post_id, '_podcast_outro_text', true );
	$sponsor_text    = get_post_meta( $post_id, '_podcast_sponsor', true );
	
	// Fallback to defaults if not set.
	$settings = get_option( 'wpshadow_podcast_settings', array() );
	
	$speaker1_voice = $speaker1_voice ? $speaker1_voice : $settings['speaker1_voice_id'];
	$speaker2_voice = $speaker2_voice ? $speaker2_voice : $settings['speaker2_voice_id'];
	$intro_narration = $intro_narration ? $intro_narration : 'Welcome to the show.';
	$outro_narration = $outro_narration ? $outro_narration : 'Thanks for listening.';
	$sponsor_text    = $sponsor_text ? $sponsor_text : 'Thanks to our sponsors.';
	
	// Generate podcast with post-specific settings.
	$mixer = new WPShadow_Podcast_Studio_Mixer();
	
	$post = get_post( $post_id );
	
	$config = array(
		'speaker1_voice_id' => $speaker1_voice,
		'speaker2_voice_id' => $speaker2_voice,
		
		'intro_config' => array(
			'narration'  => $intro_narration,
			'music_file' => 'theme.mp3',
		),
		
		'episode_config' => array(
			'title'            => $post->post_title,
			'description'      => get_the_excerpt( $post_id ),
			'content'          => wp_strip_all_tags( $post->post_content ),
			'background_music' => 'ambient.mp3',
		),
		
		'outro_config' => array(
			'narration'       => $outro_narration,
			'sponsor_mention' => $sponsor_text,
			'music_file'      => 'theme.mp3',
		),
		
		'post_id' => $post_id,
	);
	
	return $mixer->generate_professional_podcast( $config );
}


// ==============================================================================
// EXAMPLE 7: Error Handling and Logging
// ==============================================================================

function wpshadow_example_podcast_with_logging( $post_id ) {
	/**
	 * Generate podcast with comprehensive error logging.
	 */
	
	$log_file = WP_CONTENT_DIR . '/wpshadow-podcast.log';
	
	function log_podcast_event( $message, $level = 'info' ) {
		global $log_file;
		$timestamp = gmdate( 'Y-m-d H:i:s' );
		file_put_contents(
			$log_file,
			"[$timestamp] [$level] $message\n",
			FILE_APPEND
		);
	}
	
	log_podcast_event( "Starting podcast generation for post $post_id" );
	
	try {
		$mixer = new WPShadow_Podcast_Studio_Mixer();
		
		if ( ! $mixer ) {
			throw new Exception( 'Failed to initialize mixer' );
		}
		
		log_podcast_event( "Mixer initialized successfully" );
		
		$config = array(
			'speaker1_voice_id' => '21m00Tcm4TlvDq8ikWAM',
			'speaker2_voice_id' => 'EXAVITQu4vr4xnSDxMaL',
			
			'intro_config' => array(
				'narration'  => 'Welcome!',
				'music_file' => 'theme.mp3',
			),
			
			'episode_config' => array(
				'title'       => 'Episode',
				'description' => 'Description',
				'content'     => '[SPEAKER 1]: Content here',
				'background_music' => 'ambient.mp3',
			),
			
			'outro_config' => array(
				'narration'       => 'Thanks!',
				'sponsor_mention' => 'Sponsor',
				'music_file'      => 'theme.mp3',
			),
			
			'post_id' => $post_id,
		);
		
		log_podcast_event( "Configuration prepared for post $post_id" );
		
		$result = $mixer->generate_professional_podcast( $config );
		
		if ( is_wp_error( $result ) ) {
			throw new Exception( $result->get_error_message() );
		}
		
		log_podcast_event( "Podcast generated successfully: " . $result['podcast_file'] );
		
		return $result;
		
	} catch ( Exception $e ) {
		log_podcast_event( "Error: " . $e->getMessage(), 'error' );
		return new WP_Error( 'podcast-generation-failed', $e->getMessage() );
	}
}


// ==============================================================================
// EXAMPLE 8: Get Audio File Duration
// ==============================================================================

function wpshadow_example_check_podcast_duration( $podcast_file ) {
	/**
	 * Check the duration of a generated podcast.
	 * Useful for metadata and display.
	 */
	
	$ffprobe_cmd = sprintf(
		"ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1:noinvert_list=1 %s",
		escapeshellarg( $podcast_file )
	);
	
	$duration = shell_exec( $ffprobe_cmd );
	
	if ( ! empty( $duration ) ) {
		$seconds = (int) $duration;
		$minutes = intdiv( $seconds, 60 );
		$secs    = $seconds % 60;
		
		return sprintf( '%d:%02d', $minutes, $secs );
	}
	
	return 'Unknown';
}
