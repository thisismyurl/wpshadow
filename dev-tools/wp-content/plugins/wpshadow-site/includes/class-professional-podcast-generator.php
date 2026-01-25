<?php
/**
 * Enhanced Podcast Generator with Professional Audio Mixing
 *
 * Extends WPShadow_Podcast_Generator to support:
 * - Two-person podcast narration via ElevenLabs
 * - Professional intro with music ducking
 * - Background music loops under main content
 * - Host outro with sponsor mention and fade
 *
 * @package WPShadow_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPShadow_Professional_Podcast_Generator
 */
class WPShadow_Professional_Podcast_Generator {

	/**
	 * Audio mixer instance.
	 *
	 * @var WPShadow_Podcast_Audio_Mixer
	 */
	private $mixer;

	/**
	 * Working directory for temporary files.
	 *
	 * @var string
	 */
	private $work_dir;

	/**
	 * Constructor.
	 *
	 * @param string $work_dir Optional. Working directory for temp files.
	 */
	public function __construct( $work_dir = null ) {
		if ( null === $work_dir ) {
			$upload_dir = wp_upload_dir();
			$work_dir   = $upload_dir['basedir'] . '/wpshadow-podcasts';
		}

		$this->work_dir = $work_dir;

		// Ensure work directory exists.
		if ( ! file_exists( $this->work_dir ) ) {
			wp_mkdir_p( $this->work_dir );
		}

		// Load mixer class.
		require_once dirname( __FILE__ ) . '/class-podcast-audio-mixer.php';
		$this->mixer = new WPShadow_Podcast_Audio_Mixer( $this->work_dir );
	}

	/**
	 * Generate professional podcast for a KB article.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $options Optional. Generation options.
	 *   - episode_number: Episode number (used in intro).
	 *   - host_voice_id: ElevenLabs voice ID for host (intro/outro).
	 *   - speaker_1_voice_id: First speaker in main content.
	 *   - speaker_2_voice_id: Second speaker in main content.
	 *   - sponsor_name: Sponsor name to mention in outro.
	 *   - music_ducking_level: Volume for music during narration (0.0-1.0).
	 *   - background_music_level: Volume for background music under content (0.0-1.0).
	 *
	 * @return array|WP_Error Array with podcast_id on success, WP_Error on failure.
	 */
	public function generate_professional_podcast( $post_id, $options = array() ) {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return new WP_Error( 'wpshadow-prof-podcast-post-not-found', 'Post not found.' );
		}

		// Get podcast settings.
		$settings = $this->get_podcast_settings();

		// Merge with provided options.
		$options = wp_parse_args(
			$options,
			array(
				'episode_number'          => 1,
				'host_voice_id'           => $settings['host_voice_id'] ?? $settings['title_voice_id'],
				'speaker_1_voice_id'      => $settings['speaker_1_voice_id'] ?? $settings['content_voice_id'],
				'speaker_2_voice_id'      => $settings['speaker_2_voice_id'] ?? $settings['title_voice_id'],
				'sponsor_name'            => $settings['sponsor_name'] ?? 'our sponsors',
				'music_ducking_level'     => $settings['music_ducking_level'] ?? 0.3,
				'background_music_level'  => $settings['background_music_level'] ?? 0.2,
			)
		);

		// Validate required files.
		$required_audio_ids = array(
			'intro_music_id'       => $settings['intro_music_id'] ?? null,
			'background_music_id'  => $settings['background_music_id'] ?? null,
			'outro_music_id'       => $settings['outro_music_id'] ?? null,
		);

		foreach ( $required_audio_ids as $key => $audio_id ) {
			if ( empty( $audio_id ) ) {
				return new WP_Error(
					'wpshadow-prof-podcast-missing-music',
					sprintf( 'Required audio file setting "%s" not configured.', $key )
				);
			}
		}

		// Extract content for narration.
		$content = $this->extract_podcast_content( $post->post_content );
		if ( empty( $content ) ) {
			return new WP_Error( 'wpshadow-prof-podcast-empty-content', 'No content to synthesize.' );
		}

		// Split content for two-person podcast.
		$speaker_content = $this->split_content_for_speakers( $content );

		// Generate audio segments.
		$intro_narration = $this->generate_intro_narration(
			$post->post_title,
			$options['episode_number'],
			$options['host_voice_id']
		);

		if ( is_wp_error( $intro_narration ) ) {
			return $intro_narration;
		}

		// Generate main content as two-person podcast.
		$main_podcast = $this->generate_two_person_podcast(
			$speaker_content,
			$options['speaker_1_voice_id'],
			$options['speaker_2_voice_id']
		);

		if ( is_wp_error( $main_podcast ) ) {
			return $main_podcast;
		}

		// Generate outro narration.
		$outro_narration = $this->generate_outro_narration(
			$options['sponsor_name'],
			$options['host_voice_id']
		);

		if ( is_wp_error( $outro_narration ) ) {
			return $outro_narration;
		}

		// Get audio file paths from media library.
		$intro_music       = $this->get_audio_file_path( $required_audio_ids['intro_music_id'] );
		$background_music  = $this->get_audio_file_path( $required_audio_ids['background_music_id'] );
		$outro_music       = $this->get_audio_file_path( $required_audio_ids['outro_music_id'] );

		if ( ! $intro_music || ! $background_music || ! $outro_music ) {
			return new WP_Error(
				'wpshadow-prof-podcast-audio-files-not-found',
				'One or more required music files could not be found in media library.'
			);
		}

		// Prepare output file.
		$output_file = $this->work_dir . '/' . 'podcast_' . $post_id . '_' . time() . '.mp3';

		// Check if mixer is available.
		if ( ! $this->mixer->is_ffmpeg_available() ) {
			return new WP_Error(
				'wpshadow-prof-podcast-no-ffmpeg',
				'FFmpeg is required for professional podcast mixing. Please install FFmpeg.'
			);
		}

		// Mix professional podcast.
		$mix_result = $this->mixer->mix_professional_podcast(
			array(
				'intro_music_file'     => $intro_music,
				'intro_narration_file' => $intro_narration,
				'main_content_file'    => $main_podcast,
				'background_music_file' => $background_music,
				'outro_narration_file' => $outro_narration,
				'outro_music_file'     => $outro_music,
				'output_file'          => $output_file,
				'music_ducking_level'  => $options['music_ducking_level'],
				'content_bg_music_level' => $options['background_music_level'],
			)
		);

		if ( is_wp_error( $mix_result ) ) {
			return $mix_result;
		}

		// Upload to media library.
		$podcast_id = $this->upload_podcast_to_media_library( $output_file, $post_id, $post->post_title );
		if ( is_wp_error( $podcast_id ) ) {
			return $podcast_id;
		}

		// Store metadata.
		update_post_meta( $post_id, '_wpshadow_professional_podcast_id', $podcast_id );
		update_post_meta( $post_id, '_wpshadow_podcast_generated', current_time( 'mysql' ) );
		update_post_meta( $post_id, '_wpshadow_podcast_episode_number', $options['episode_number'] );

		// Cleanup temporary files.
		$this->cleanup_temporary_files(
			array( $intro_narration, $main_podcast, $outro_narration, $output_file )
		);

		return array( 'podcast_id' => $podcast_id );
	}

	/**
	 * Generate intro narration for host introducing episode and subject.
	 *
	 * @param string $subject Subject/title of the episode.
	 * @param int    $episode_number Episode number.
	 * @param string $voice_id ElevenLabs voice ID.
	 * @return string|WP_Error Path to audio file on success, WP_Error on failure.
	 */
	private function generate_intro_narration( $subject, $episode_number, $voice_id ) {
		$intro_text = sprintf(
			'Welcome to the podcast. Today on episode %d, we\'re discussing "%s".',
			$episode_number,
			$subject
		);

		return $this->synthesize_text( $intro_text, $voice_id );
	}

	/**
	 * Generate two-person podcast from content.
	 *
	 * Splits content between two speakers for a conversational feel.
	 *
	 * @param array  $speaker_content Content split for two speakers.
	 * @param string $speaker_1_voice Voice ID for speaker 1.
	 * @param string $speaker_2_voice Voice ID for speaker 2.
	 * @return string|WP_Error Path to audio file on success, WP_Error on failure.
	 */
	private function generate_two_person_podcast( $speaker_content, $speaker_1_voice, $speaker_2_voice ) {
		$segments = array();

		// Generate audio for each speaker's portions.
		foreach ( $speaker_content as $idx => $content_piece ) {
			$voice_id = ( $idx % 2 === 0 ) ? $speaker_1_voice : $speaker_2_voice;
			$audio    = $this->synthesize_text( $content_piece['text'], $voice_id );

			if ( is_wp_error( $audio ) ) {
				return $audio;
			}

			$segments[] = $audio;
		}

		// Stitch segments together.
		$output_file = $this->work_dir . '/' . 'two_person_' . time() . '.mp3';

		return $this->stitch_audio_files( $segments, $output_file );
	}

	/**
	 * Generate outro narration with thanks and sponsor mention.
	 *
	 * @param string $sponsor_name Name of sponsor to thank.
	 * @param string $voice_id ElevenLabs voice ID.
	 * @return string|WP_Error Path to audio file on success, WP_Error on failure.
	 */
	private function generate_outro_narration( $sponsor_name, $voice_id ) {
		$outro_text = sprintf(
			'Thanks for listening to this episode. We\'d like to thank %s for their support. ' .
			'If you enjoyed this episode, please like, share, and subscribe. See you next time!',
			$sponsor_name
		);

		return $this->synthesize_text( $outro_text, $voice_id );
	}

	/**
	 * Split content into alternating segments for two speakers.
	 *
	 * @param string $content Full podcast content.
	 * @return array Array of segments with text for each speaker.
	 */
	private function split_content_for_speakers( $content ) {
		// Split by sentences.
		$sentences = preg_split( '/(?<=[.!?])\s+/', trim( $content ), -1, PREG_SPLIT_NO_EMPTY );

		if ( empty( $sentences ) ) {
			return array( array( 'text' => $content, 'speaker' => 1 ) );
		}

		$segments      = array();
		$current_block = '';
		$block_size    = max( 1, (int) ( count( $sentences ) / 4 ) ); // Aim for ~4 blocks per speaker.
		$sentence_count = 0;
		$speaker       = 1;

		foreach ( $sentences as $sentence ) {
			$current_block .= ( ! empty( $current_block ) ? ' ' : '' ) . $sentence;
			$sentence_count++;

			if ( $sentence_count >= $block_size ) {
				$segments[] = array(
					'text'    => trim( $current_block ),
					'speaker' => $speaker,
				);

				$current_block  = '';
				$sentence_count = 0;
				$speaker        = ( $speaker === 1 ) ? 2 : 1;
			}
		}

		// Add remaining content.
		if ( ! empty( $current_block ) ) {
			$segments[] = array(
				'text'    => trim( $current_block ),
				'speaker' => $speaker,
			);
		}

		return $segments;
	}

	/**
	 * Extract and clean content for podcast.
	 *
	 * @param string $content Post content.
	 * @return string Cleaned content.
	 */
	private function extract_podcast_content( $content ) {
		// Remove shortcodes.
		$content = preg_replace( '/\[.*?\]/', '', $content );

		// Strip HTML tags.
		$content = wp_strip_all_tags( $content );

		// Remove extra whitespace.
		$content = preg_replace( '/\s+/', ' ', $content );
		$content = trim( $content );

		// Limit to reasonable length.
		$words      = explode( ' ', $content );
		$max_words  = apply_filters( 'wpshadow_podcast_max_words', 5000 );
		if ( count( $words ) > $max_words ) {
			$words   = array_slice( $words, 0, $max_words );
			$content = implode( ' ', $words ) . '...';
		}

		return $content;
	}

	/**
	 * Synthesize text using ElevenLabs TTS.
	 *
	 * @param string $text     Text to synthesize.
	 * @param string $voice_id Voice ID.
	 * @return string|WP_Error Path to audio file on success, WP_Error on failure.
	 */
	private function synthesize_text( $text, $voice_id ) {
		if ( ! function_exists( 'wpshadow_site_elevenlabs_tts' ) ) {
			return new WP_Error( 'wpshadow-prof-podcast-function-missing', 'ElevenLabs TTS function not found.' );
		}

		if ( empty( $voice_id ) ) {
			return new WP_Error( 'wpshadow-prof-podcast-no-voice-id', 'Voice ID not provided.' );
		}

		$result = wpshadow_site_elevenlabs_tts(
			$text,
			array( 'voice_id' => $voice_id )
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Save audio to temporary file.
		$temp_file = $this->work_dir . '/' . 'temp_' . md5( $text . time() ) . '.mp3';

		$bytes = file_put_contents( $temp_file, $result['audio'] );
		if ( false === $bytes ) {
			return new WP_Error( 'wpshadow-prof-podcast-save-failed', 'Failed to save synthesized audio.' );
		}

		return $temp_file;
	}

	/**
	 * Stitch audio files together.
	 *
	 * @param array  $audio_files Array of audio file paths.
	 * @param string $output_file Output file path.
	 * @return string|WP_Error Output file path on success, WP_Error on failure.
	 */
	private function stitch_audio_files( $audio_files, $output_file ) {
		if ( empty( $audio_files ) ) {
			return new WP_Error( 'wpshadow-prof-podcast-no-files', 'No audio files provided.' );
		}

		if ( ! $this->mixer->is_ffmpeg_available() ) {
			return new WP_Error( 'wpshadow-prof-podcast-no-ffmpeg', 'FFmpeg is not available.' );
		}

		// Use mixer's sequential mix method.
		$segments = array();
		foreach ( $audio_files as $idx => $file ) {
			$segments[ 'segment_' . $idx ] = $file;
		}

		$result = $this->mixer->sequential_mix( $segments, $output_file );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $output_file;
	}

	/**
	 * Get audio file path from attachment ID.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return string|false File path or false if not found.
	 */
	private function get_audio_file_path( $attachment_id ) {
		$file = get_attached_file( $attachment_id );

		return $file ? $file : false;
	}

	/**
	 * Upload podcast to media library.
	 *
	 * @param string $file_path Path to podcast file.
	 * @param int    $post_id   Associated post ID.
	 * @param string $title     Podcast title.
	 * @return int|WP_Error Attachment ID on success, WP_Error on failure.
	 */
	private function upload_podcast_to_media_library( $file_path, $post_id, $title ) {
		if ( ! file_exists( $file_path ) ) {
			return new WP_Error( 'wpshadow-prof-podcast-file-not-found', 'Podcast file not found.' );
		}

		$file_name = 'podcast_' . $post_id . '_' . wp_get_current_user()->ID . '.mp3';

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$upload_dir = wp_upload_dir();
		$dest_path  = $upload_dir['path'] . '/' . $file_name;

		if ( ! copy( $file_path, $dest_path ) ) {
			return new WP_Error( 'wpshadow-prof-podcast-copy-failed', 'Failed to copy podcast file to uploads.' );
		}

		$attachment = array(
			'post_mime_type' => 'audio/mpeg',
			'post_title'     => $title . ' - Professional Podcast',
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$attachment_id = wp_insert_attachment( $attachment, $dest_path, $post_id );

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		$attach_data = wp_generate_attachment_metadata( $attachment_id, $dest_path );
		wp_update_attachment_metadata( $attachment_id, $attach_data );

		return $attachment_id;
	}

	/**
	 * Get podcast settings.
	 *
	 * @return array
	 */
	private function get_podcast_settings() {
		$defaults = array(
			'enabled'                 => false,
			'host_voice_id'           => '',
			'speaker_1_voice_id'      => '',
			'speaker_2_voice_id'      => '',
			'content_voice_id'        => '',
			'title_voice_id'          => '',
			'intro_music_id'          => '',
			'background_music_id'     => '',
			'outro_music_id'          => '',
			'sponsor_name'            => '',
			'music_ducking_level'     => 0.3,
			'background_music_level'  => 0.2,
		);

		$stored = get_option( 'wpshadow_professional_podcast_settings', array() );

		return wp_parse_args( is_array( $stored ) ? $stored : array(), $defaults );
	}

	/**
	 * Cleanup temporary files.
	 *
	 * @param array $files Array of file paths to delete.
	 */
	private function cleanup_temporary_files( $files ) {
		foreach ( $files as $file ) {
			if ( ! empty( $file ) && file_exists( $file ) && strpos( $file, 'temp_' ) !== false ) {
				unlink( $file );
			}
		}
	}
}
