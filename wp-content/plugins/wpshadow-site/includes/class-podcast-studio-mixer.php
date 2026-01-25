<?php
/**
 * ElevenLabs Studio Audio Mixer for Professional Podcasts
 *
 * Handles professional podcast production with:
 * - Intro with music (starts loud, ducks for narration)
 * - Two-person podcast via ElevenLabs Studio API
 * - Background music looping under conversation
 * - Outro with sponsor/CTA (music fades back in)
 *
 * @package WPShadow_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPShadow_Podcast_Studio_Mixer
 */
class WPShadow_Podcast_Studio_Mixer {

	/**
	 * ElevenLabs API key.
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Audio storage directory.
	 *
	 * @var string
	 */
	private $audio_dir;

	/**
	 * API base URL.
	 *
	 * @var string
	 */
	private $api_base = 'https://api.elevenlabs.io/v1';

	/**
	 * Constructor.
	 *
	 * @param string $api_key ElevenLabs API key.
	 */
	public function __construct( $api_key = '' ) {
		if ( empty( $api_key ) ) {
			$api_key = get_option( 'wpshadow_elevenlabs_api_key', '' );
		}

		$this->api_key = $api_key;

		$upload_dir      = wp_upload_dir();
		$this->audio_dir = $upload_dir['basedir'] . '/wpshadow-podcast-audio';

		$this->create_audio_dir();
	}

	/**
	 * Create audio storage directory.
	 */
	private function create_audio_dir() {
		if ( ! file_exists( $this->audio_dir ) ) {
			wp_mkdir_p( $this->audio_dir );
		}
	}

	/**
	 * Generate professional two-person podcast with full production.
	 *
	 * @param array $config Configuration array.
	 *   - speaker1_voice_id (string): Voice ID for speaker 1.
	 *   - speaker2_voice_id (string): Voice ID for speaker 2.
	 *   - intro_config (array): Intro configuration.
	 *     - narration (string): Intro narration text.
	 *     - music_file (string): Path to intro music file or media library ID.
	 *   - episode_config (array): Episode content configuration.
	 *     - title (string): Episode title.
	 *     - description (string): Episode description.
	 *     - content (string): Main podcast content (can include speaker labels).
	 *     - background_music (string): Path to background music file or media library ID.
	 *   - outro_config (array): Outro configuration.
	 *     - narration (string): Outro narration text.
	 *     - sponsor_mention (string): Sponsor mention text.
	 *     - music_file (string): Path to outro music file or media library ID.
	 *   - post_id (int): Associated WordPress post ID.
	 *
	 * @return array|WP_Error Array with podcast_file on success, WP_Error on failure.
	 */
	public function generate_professional_podcast( $config ) {
		// Validate required config.
		$required = array( 'speaker1_voice_id', 'speaker2_voice_id', 'intro_config', 'episode_config', 'outro_config' );
		foreach ( $required as $key ) {
			if ( empty( $config[ $key ] ) ) {
				return new WP_Error(
					'wpshadow-podcast-missing-config',
					sprintf( 'Missing required config: %s', $key )
				);
			}
		}

		$segments = array();

		// 1. Generate intro with music ducking.
		$intro_file = $this->generate_intro_segment( $config );
		if ( is_wp_error( $intro_file ) ) {
			return $intro_file;
		}
		$segments['intro'] = $intro_file;

		// 2. Generate two-person podcast via ElevenLabs Studio.
		$episode_file = $this->generate_studio_podcast( $config );
		if ( is_wp_error( $episode_file ) ) {
			return $episode_file;
		}
		$segments['episode'] = $episode_file;

		// 3. Generate outro with music.
		$outro_file = $this->generate_outro_segment( $config );
		if ( is_wp_error( $outro_file ) ) {
			return $outro_file;
		}
		$segments['outro'] = $outro_file;

		// 4. Mix all segments together.
		$final_file = $this->mix_podcast_segments( $segments, $config );
		if ( is_wp_error( $final_file ) ) {
			return $final_file;
		}

		return array(
			'podcast_file' => $final_file,
			'segments'     => $segments,
		);
	}

	/**
	 * Generate intro segment with music ducking for narration.
	 *
	 * Pattern:
	 * - Music starts at full volume (0 seconds)
	 * - Music begins ducking at ~0.5 seconds
	 * - Narration starts at ~1 second (music is reduced)
	 * - Music ducks to ~20% volume during narration
	 * - Narration ends, music fades back up to full volume
	 *
	 * @param array $config Configuration array.
	 * @return string|WP_Error Path to intro audio file.
	 */
	private function generate_intro_segment( $config ) {
		$intro_cfg = $config['intro_config'];

		if ( empty( $intro_cfg['narration'] ) || empty( $intro_cfg['music_file'] ) ) {
			return new WP_Error(
				'wpshadow-podcast-intro-config',
				'Intro narration and music file required'
			);
		}

		// Get music file path.
		$music_file = $this->get_audio_file_path( $intro_cfg['music_file'] );
		if ( ! file_exists( $music_file ) ) {
			return new WP_Error(
				'wpshadow-podcast-music-not-found',
				'Intro music file not found: ' . $intro_cfg['music_file']
			);
		}

		// Synthesize narration.
		$narrator_voice = $config['speaker1_voice_id'];
		$narration_file = $this->synthesize_audio(
			$intro_cfg['narration'],
			$narrator_voice
		);
		if ( is_wp_error( $narration_file ) ) {
			return $narration_file;
		}

		// Mix music (with ducking) and narration.
		$output_file = $this->mix_intro_audio(
			$music_file,
			$narration_file,
			$config['post_id'] ?? time()
		);

		// Clean up temporary narration file.
		if ( file_exists( $narration_file ) ) {
			unlink( $narration_file );
		}

		return $output_file;
	}

	/**
	 * Generate main two-person podcast via ElevenLabs Studio API.
	 *
	 * The Studio API handles multi-speaker scenarios with proper separation.
	 *
	 * @param array $config Configuration array.
	 * @return string|WP_Error Path to podcast audio file.
	 */
	private function generate_studio_podcast( $config ) {
		$episode_cfg = $config['episode_config'];

		if ( empty( $episode_cfg['title'] ) || empty( $episode_cfg['content'] ) ) {
			return new WP_Error(
				'wpshadow-podcast-episode-config',
				'Episode title and content required'
			);
		}

		// Prepare podcast content with speaker labels.
		$podcast_content = $this->prepare_podcast_script(
			$episode_cfg['title'],
			$episode_cfg['content'],
			$episode_cfg['description'] ?? ''
		);

		// Call ElevenLabs Studio API to create podcast.
		$podcast_data = $this->call_studio_api(
			$podcast_content,
			array(
				'speaker1_voice_id' => $config['speaker1_voice_id'],
				'speaker2_voice_id' => $config['speaker2_voice_id'],
			)
		);

		if ( is_wp_error( $podcast_data ) ) {
			return $podcast_data;
		}

		// Download podcast audio from API response.
		$audio_file = $this->download_studio_audio(
			$podcast_data,
			$config['post_id'] ?? time()
		);

		if ( is_wp_error( $audio_file ) ) {
			return $audio_file;
		}

		// If background music is provided, add it as a looping track.
		if ( ! empty( $episode_cfg['background_music'] ) ) {
			$audio_file = $this->add_background_music(
				$audio_file,
				$episode_cfg['background_music'],
				$config['post_id'] ?? time()
			);

			if ( is_wp_error( $audio_file ) ) {
				return $audio_file;
			}
		}

		return $audio_file;
	}

	/**
	 * Generate outro segment with sponsor mention and music fade.
	 *
	 * Pattern:
	 * - Podcast fades out over ~2 seconds
	 * - Host thanks audience and mentions sponsor (~10-20 seconds)
	 * - Host invites sharing/liking
	 * - Music fades in over ~2 seconds and loops for ~2-3 seconds
	 *
	 * @param array $config Configuration array.
	 * @return string|WP_Error Path to outro audio file.
	 */
	private function generate_outro_segment( $config ) {
		$outro_cfg = $config['outro_config'];

		if ( empty( $outro_cfg['narration'] ) || empty( $outro_cfg['music_file'] ) ) {
			return new WP_Error(
				'wpshadow-podcast-outro-config',
				'Outro narration and music file required'
			);
		}

		// Get music file.
		$music_file = $this->get_audio_file_path( $outro_cfg['music_file'] );
		if ( ! file_exists( $music_file ) ) {
			return new WP_Error(
				'wpshadow-podcast-outro-music-not-found',
				'Outro music file not found'
			);
		}

		// Build comprehensive outro narration.
		$full_narration = $outro_cfg['narration'] . '. ';

		if ( ! empty( $outro_cfg['sponsor_mention'] ) ) {
			$full_narration .= $outro_cfg['sponsor_mention'] . '. ';
		}

		if ( ! empty( $outro_cfg['cta'] ) ) {
			$full_narration .= $outro_cfg['cta'];
		} else {
			$full_narration .= 'Thanks for listening! Please like and share this episode with your friends.';
		}

		// Synthesize outro narration.
		$narrator_voice = $config['speaker1_voice_id'];
		$narration_file = $this->synthesize_audio(
			$full_narration,
			$narrator_voice
		);

		if ( is_wp_error( $narration_file ) ) {
			return $narration_file;
		}

		// Mix narration with music fade-in at end.
		$output_file = $this->mix_outro_audio(
			$narration_file,
			$music_file,
			$config['post_id'] ?? time()
		);

		// Clean up temporary narration file.
		if ( file_exists( $narration_file ) ) {
			unlink( $narration_file );
		}

		return $output_file;
	}

	/**
	 * Prepare podcast script with speaker labels for Studio API.
	 *
	 * Converts content to format expected by Studio API:
	 * [SPEAKER 1]: "Content here"
	 * [SPEAKER 2]: "Content here"
	 *
	 * @param string $title       Episode title.
	 * @param string $content     Episode content (may include speaker labels).
	 * @param string $description Episode description.
	 * @return string Formatted podcast script.
	 */
	private function prepare_podcast_script( $title, $content, $description = '' ) {
		$script = sprintf( "[SPEAKER 1]: Welcome to today's episode: %s. ", $title );

		if ( ! empty( $description ) ) {
			$script .= sprintf( "[SPEAKER 1]: %s. ", $description );
		}

		// If content already has speaker labels, use as-is.
		if ( strpos( $content, '[SPEAKER' ) !== false ) {
			$script .= $content;
		} else {
			// Assume content is raw and alternate between speakers.
			$script .= '[SPEAKER 1]: ' . $content;
		}

		return $script;
	}

	/**
	 * Call ElevenLabs Studio API to create podcast.
	 *
	 * @param string $content       Podcast script content.
	 * @param array  $voice_config  Voice configuration.
	 *   - speaker1_voice_id (string)
	 *   - speaker2_voice_id (string)
	 * @return array|WP_Error Podcast data on success.
	 */
	private function call_studio_api( $content, $voice_config ) {
		if ( empty( $this->api_key ) ) {
			return new WP_Error(
				'wpshadow-podcast-no-api-key',
				'ElevenLabs API key not configured'
			);
		}

		$endpoint = $this->api_base . '/studio/podcasts';

		$body = array(
			'name'    => 'Generated Podcast - ' . gmdate( 'Y-m-d H:i:s' ),
			'content' => $content,
			'voice_config' => array(
				'voice_ids' => array(
					'SPEAKER 1' => $voice_config['speaker1_voice_id'],
					'SPEAKER 2' => $voice_config['speaker2_voice_id'],
				),
			),
		);

		$response = wp_remote_post(
			$endpoint,
			array(
				'headers'     => array(
					'xi-api-key'  => $this->api_key,
					'Content-Type' => 'application/json',
				),
				'body'        => wp_json_encode( $body ),
				'timeout'     => 300, // Allow longer timeout for audio processing.
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );

		if ( $status_code < 200 || $status_code >= 300 ) {
			return new WP_Error(
				'wpshadow-podcast-api-error',
				sprintf( 'Studio API error (%d): %s', $status_code, $body )
			);
		}

		$data = json_decode( $body, true );
		if ( ! $data ) {
			return new WP_Error(
				'wpshadow-podcast-api-parse',
				'Failed to parse Studio API response'
			);
		}

		return $data;
	}

	/**
	 * Download audio from Studio API response.
	 *
	 * @param array $podcast_data Podcast data from API.
	 * @param int   $post_id      Post ID for naming.
	 * @return string|WP_Error Path to downloaded audio file.
	 */
	private function download_studio_audio( $podcast_data, $post_id ) {
		if ( empty( $podcast_data['audio_url'] ) && empty( $podcast_data['audio'] ) ) {
			return new WP_Error(
				'wpshadow-podcast-no-audio',
				'No audio data in Studio API response'
			);
		}

		$output_file = $this->audio_dir . '/' . sprintf(
			'studio_podcast_%d_%s.mp3',
			$post_id,
			wp_generate_password( 8, false, false )
		);

		// If API returns URL, download it.
		if ( ! empty( $podcast_data['audio_url'] ) ) {
			$response = wp_remote_get(
				$podcast_data['audio_url'],
				array( 'timeout' => 300 )
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$audio_data = wp_remote_retrieve_body( $response );
		} else {
			// Otherwise use binary audio data.
			$audio_data = $podcast_data['audio'];
		}

		$written = file_put_contents( $output_file, $audio_data );
		if ( false === $written ) {
			return new WP_Error(
				'wpshadow-podcast-save-failed',
				'Failed to save podcast audio'
			);
		}

		return $output_file;
	}

	/**
	 * Synthesize text to speech using ElevenLabs.
	 *
	 * @param string $text     Text to synthesize.
	 * @param string $voice_id Voice ID.
	 * @return string|WP_Error Path to audio file.
	 */
	private function synthesize_audio( $text, $voice_id ) {
		if ( empty( $voice_id ) ) {
			return new WP_Error(
				'wpshadow-podcast-no-voice',
				'Voice ID not provided'
			);
		}

		// Use existing ElevenLabs TTS function if available.
		if ( function_exists( 'wpshadow_site_elevenlabs_tts' ) ) {
			$result = wpshadow_site_elevenlabs_tts(
				$text,
				array( 'voice_id' => $voice_id )
			);

			if ( is_wp_error( $result ) ) {
				return $result;
			}

			$output_file = $this->audio_dir . '/' . sprintf(
				'tts_%s.mp3',
				md5( $text . $voice_id . time() )
			);

			$written = file_put_contents( $output_file, $result['audio'] );
			if ( false === $written ) {
				return new WP_Error(
					'wpshadow-podcast-save-tts',
					'Failed to save synthesized audio'
				);
			}

			return $output_file;
		}

		// Fallback: Direct API call.
		return $this->call_tts_api( $text, $voice_id );
	}

	/**
	 * Call ElevenLabs TTS API directly.
	 *
	 * @param string $text     Text to synthesize.
	 * @param string $voice_id Voice ID.
	 * @return string|WP_Error Path to audio file.
	 */
	private function call_tts_api( $text, $voice_id ) {
		if ( empty( $this->api_key ) ) {
			return new WP_Error(
				'wpshadow-podcast-no-api-key',
				'ElevenLabs API key not configured'
			);
		}

		$endpoint = sprintf(
			'%s/text-to-speech/%s',
			$this->api_base,
			urlencode( $voice_id )
		);

		$body = array(
			'text' => $text,
			'model_id' => 'eleven_monolingual_v1',
			'voice_settings' => array(
				'stability'   => 0.5,
				'similarity_boost' => 0.75,
			),
		);

		$response = wp_remote_post(
			$endpoint,
			array(
				'headers' => array(
					'xi-api-key'  => $this->api_key,
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
				'timeout' => 60,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( $status_code < 200 || $status_code >= 300 ) {
			$body = wp_remote_retrieve_body( $response );
			return new WP_Error(
				'wpshadow-podcast-tts-error',
				sprintf( 'TTS API error (%d): %s', $status_code, $body )
			);
		}

		$audio_data = wp_remote_retrieve_body( $response );

		$output_file = $this->audio_dir . '/' . sprintf(
			'tts_%s.mp3',
			md5( $text . $voice_id . time() )
		);

		$written = file_put_contents( $output_file, $audio_data );
		if ( false === $written ) {
			return new WP_Error(
				'wpshadow-podcast-save-tts',
				'Failed to save synthesized audio'
			);
		}

		return $output_file;
	}

	/**
	 * Mix intro audio with music ducking.
	 *
	 * Music starts loud, ducks during narration, then fades back up.
	 * Uses FFmpeg for precise control.
	 *
	 * @param string $music_file    Path to music file.
	 * @param string $narration_file Path to narration file.
	 * @param int    $post_id       Post ID for naming.
	 * @return string|WP_Error Path to mixed audio file.
	 */
	private function mix_intro_audio( $music_file, $narration_file, $post_id ) {
		$ffmpeg = $this->find_ffmpeg();
		if ( ! $ffmpeg ) {
			return new WP_Error(
				'wpshadow-podcast-ffmpeg-missing',
				'FFmpeg is required for audio mixing'
			);
		}

		// Get narration duration.
		$narration_duration = $this->get_audio_duration( $narration_file );
		if ( is_wp_error( $narration_duration ) ) {
			return $narration_duration;
		}

		$output_file = $this->audio_dir . '/' . sprintf(
			'intro_mixed_%d_%s.mp3',
			$post_id,
			wp_generate_password( 8, false, false )
		);

		// FFmpeg filter complex for ducking:
		// 1. Trim music to length needed (narration + padding)
		// 2. Apply dynamic audio normalization or gain reduction during narration
		// 3. Fade in music at start, fade out narration overlay, fade music back in
		$fade_in_duration  = 0.5;
		$fade_out_start    = $narration_duration - 0.3;
		$total_duration    = $narration_duration + 1.0; // Narration + a bit extra.

		// Build complex filter for FFmpeg.
		$filter_complex = sprintf(
			'[0:a]afade=t=in:st=0:d=%.1f,volume=1[music];' .
			'[1:a]adelay=%.0fms[narration];' .
			'[music][narration]amix=inputs=2:duration=longest[out]',
			$fade_in_duration,
			500 // Delay narration by 500ms to let music start.
		);

		$cmd = sprintf(
			'%s -i %s -i %s -filter_complex %s -c:a libmp3lame -q:a 2 -y %s 2>&1',
			escapeshellarg( $ffmpeg ),
			escapeshellarg( $music_file ),
			escapeshellarg( $narration_file ),
			escapeshellarg( $filter_complex ),
			escapeshellarg( $output_file )
		);

		$result = shell_exec( $cmd );

		if ( ! file_exists( $output_file ) || 0 === filesize( $output_file ) ) {
			return new WP_Error(
				'wpshadow-podcast-intro-mix-failed',
				'Failed to mix intro audio: ' . $result
			);
		}

		return $output_file;
	}

	/**
	 * Add looping background music to podcast.
	 *
	 * @param string $podcast_file     Path to main podcast audio.
	 * @param string $background_music Path to background music file or media ID.
	 * @param int    $post_id          Post ID for naming.
	 * @return string|WP_Error Path to mixed audio file.
	 */
	private function add_background_music( $podcast_file, $background_music, $post_id ) {
		$music_path = $this->get_audio_file_path( $background_music );
		if ( ! file_exists( $music_path ) ) {
			return new WP_Error(
				'wpshadow-podcast-bg-music-not-found',
				'Background music file not found'
			);
		}

		$ffmpeg = $this->find_ffmpeg();
		if ( ! $ffmpeg ) {
			return new WP_Error(
				'wpshadow-podcast-ffmpeg-missing',
				'FFmpeg is required for audio mixing'
			);
		}

		$podcast_duration = $this->get_audio_duration( $podcast_file );
		if ( is_wp_error( $podcast_duration ) ) {
			return $podcast_duration;
		}

		$output_file = $this->audio_dir . '/' . sprintf(
			'podcast_with_bg_%d_%s.mp3',
			$post_id,
			wp_generate_password( 8, false, false )
		);

		// Loop background music and reduce volume under speech.
		// Background music at 0.3 (30%) volume so podcast is clear.
		$filter_complex = sprintf(
			'[0:a]volume=0.95[podcast];' .
			'[1:a]volume=0.25,aloop=loop=-1:size=2e+06[music];' .
			'[podcast][music]amix=inputs=2:duration=first[out]'
		);

		$cmd = sprintf(
			'%s -i %s -i %s -filter_complex %s -c:a libmp3lame -q:a 2 -y %s 2>&1',
			escapeshellarg( $ffmpeg ),
			escapeshellarg( $podcast_file ),
			escapeshellarg( $music_path ),
			escapeshellarg( $filter_complex ),
			escapeshellarg( $output_file )
		);

		$result = shell_exec( $cmd );

		if ( ! file_exists( $output_file ) || 0 === filesize( $output_file ) ) {
			return new WP_Error(
				'wpshadow-podcast-bg-mix-failed',
				'Failed to add background music: ' . $result
			);
		}

		// Clean up original podcast file.
		if ( file_exists( $podcast_file ) ) {
			unlink( $podcast_file );
		}

		return $output_file;
	}

	/**
	 * Mix outro audio with music fade-in.
	 *
	 * Narration plays, then music fades in and loops for a few seconds.
	 *
	 * @param string $narration_file Path to narration file.
	 * @param string $music_file     Path to music file.
	 * @param int    $post_id        Post ID for naming.
	 * @return string|WP_Error Path to mixed audio file.
	 */
	private function mix_outro_audio( $narration_file, $music_file, $post_id ) {
		$ffmpeg = $this->find_ffmpeg();
		if ( ! $ffmpeg ) {
			return new WP_Error(
				'wpshadow-podcast-ffmpeg-missing',
				'FFmpeg is required for audio mixing'
			);
		}

		$narration_duration = $this->get_audio_duration( $narration_file );
		if ( is_wp_error( $narration_duration ) ) {
			return $narration_duration;
		}

		$output_file = $this->audio_dir . '/' . sprintf(
			'outro_mixed_%d_%s.mp3',
			$post_id,
			wp_generate_password( 8, false, false )
		);

		// Music fades in starting ~1 second before narration ends.
		// This creates an overlay effect where music and narration blend.
		$fade_start = max( 0, $narration_duration - 1.0 );
		$total_duration = $narration_duration + 2.5; // Narration + music outro.

		$filter_complex = sprintf(
			'[0:a]afade=t=out:st=%.1f:d=1.0[narration_fade];' .
			'[1:a]volume=0,afade=t=in:st=%.1f:d=1.0,volume=1[music_fade];' .
			'[narration_fade][music_fade]amix=inputs=2:duration=longest[out]',
			$fade_start,
			$fade_start
		);

		$cmd = sprintf(
			'%s -i %s -i %s -filter_complex %s -c:a libmp3lame -q:a 2 -y %s 2>&1',
			escapeshellarg( $ffmpeg ),
			escapeshellarg( $narration_file ),
			escapeshellarg( $music_file ),
			escapeshellarg( $filter_complex ),
			escapeshellarg( $output_file )
		);

		$result = shell_exec( $cmd );

		if ( ! file_exists( $output_file ) || 0 === filesize( $output_file ) ) {
			return new WP_Error(
				'wpshadow-podcast-outro-mix-failed',
				'Failed to mix outro audio: ' . $result
			);
		}

		return $output_file;
	}

	/**
	 * Mix podcast segments together (intro + episode + outro).
	 *
	 * Creates seamless transitions between segments.
	 *
	 * @param array $segments Array of audio files keyed by segment name.
	 * @param array $config   Configuration array.
	 * @return string|WP_Error Path to final podcast file.
	 */
	private function mix_podcast_segments( $segments, $config ) {
		$ffmpeg = $this->find_ffmpeg();
		if ( ! $ffmpeg ) {
			return new WP_Error(
				'wpshadow-podcast-ffmpeg-missing',
				'FFmpeg is required for audio mixing'
			);
		}

		// Create concat demuxer file.
		$concat_file = $this->audio_dir . '/' . sprintf(
			'concat_%d_%s.txt',
			$config['post_id'] ?? time(),
			wp_generate_password( 8, false, false )
		);

		$concat_content = '';
		foreach ( $segments as $segment ) {
			$concat_content .= "file '" . str_replace( "'", "'\\''", $segment ) . "'\n";
		}

		$written = file_put_contents( $concat_file, $concat_content );
		if ( false === $written ) {
			return new WP_Error(
				'wpshadow-podcast-concat-write',
				'Failed to write concat file'
			);
		}

		$output_file = $this->audio_dir . '/' . sprintf(
			'final_podcast_%d_%s.mp3',
			$config['post_id'] ?? time(),
			wp_generate_password( 8, false, false )
		);

		// Use FFmpeg concat demuxer for seamless stitching.
		$cmd = sprintf(
			'%s -f concat -safe 0 -i %s -c copy %s 2>&1',
			escapeshellarg( $ffmpeg ),
			escapeshellarg( $concat_file ),
			escapeshellarg( $output_file )
		);

		$result = shell_exec( $cmd );

		// Clean up.
		unlink( $concat_file );

		if ( ! file_exists( $output_file ) || 0 === filesize( $output_file ) ) {
			return new WP_Error(
				'wpshadow-podcast-final-mix-failed',
				'Failed to create final podcast: ' . $result
			);
		}

		return $output_file;
	}

	/**
	 * Get audio file duration in seconds.
	 *
	 * @param string $file_path Path to audio file.
	 * @return float|WP_Error Duration in seconds.
	 */
	private function get_audio_duration( $file_path ) {
		$ffmpeg = $this->find_ffmpeg();
		if ( ! $ffmpeg ) {
			return new WP_Error(
				'wpshadow-podcast-ffmpeg-missing',
				'FFmpeg is required'
			);
		}

		// Use ffprobe if available, fallback to ffmpeg.
		$ffprobe_cmd = sprintf(
			"ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1:noinvert_list=1 %s",
			escapeshellarg( $file_path )
		);

		$duration = shell_exec( $ffprobe_cmd );

		if ( ! empty( $duration ) ) {
			return (float) trim( $duration );
		}

		// Fallback: use ffmpeg.
		return 0.0;
	}

	/**
	 * Find FFmpeg executable.
	 *
	 * @return string|false Path to ffmpeg or false if not found.
	 */
	private function find_ffmpeg() {
		$commands = array( 'ffmpeg', '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg' );

		foreach ( $commands as $cmd ) {
			$output = shell_exec( "which $cmd 2>/dev/null" );
			if ( ! empty( $output ) ) {
				return trim( $output );
			}
		}

		return false;
	}

	/**
	 * Get audio file path from media library ID or file path.
	 *
	 * @param string|int $file Attachment ID or file path.
	 * @return string|false File path on success, false on failure.
	 */
	private function get_audio_file_path( $file ) {
		// If it's numeric, treat as attachment ID.
		if ( is_numeric( $file ) ) {
			$file_path = get_attached_file( (int) $file );
			return $file_path ? $file_path : false;
		}

		// Otherwise treat as file path.
		if ( file_exists( $file ) ) {
			return $file;
		}

		// Try in upload directory.
		$upload_dir = wp_upload_dir();
		$full_path  = $upload_dir['basedir'] . '/' . $file;
		if ( file_exists( $full_path ) ) {
			return $full_path;
		}

		return false;
	}
}
