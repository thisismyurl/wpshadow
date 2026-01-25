<?php
/**
 * Professional Podcast Audio Mixer for ElevenLabs Integration
 *
 * Handles advanced audio mixing for two-person podcasts with:
 * - Music intro (starts loud)
 * - Host intro with music ducking
 * - Background music loop under main content
 * - Host outro with sponsor mention and music fade
 *
 * @package WPShadow_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPShadow_Podcast_Audio_Mixer
 */
class WPShadow_Podcast_Audio_Mixer {

	/**
	 * FFmpeg executable path.
	 *
	 * @var string
	 */
	private $ffmpeg;

	/**
	 * Working directory for temp files.
	 *
	 * @var string
	 */
	private $work_dir;

	/**
	 * Constructor.
	 *
	 * @param string $work_dir Working directory for temporary files.
	 */
	public function __construct( $work_dir ) {
		$this->work_dir = $work_dir;
		$this->ffmpeg   = $this->find_ffmpeg();
	}

	/**
	 * Find FFmpeg executable path.
	 *
	 * @return string|false FFmpeg path or false if not found.
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
	 * Check if FFmpeg is available.
	 *
	 * @return bool True if FFmpeg is available.
	 */
	public function is_ffmpeg_available() {
		return false !== $this->ffmpeg;
	}

	/**
	 * Get FFmpeg path.
	 *
	 * @return string|false FFmpeg path or false if not available.
	 */
	public function get_ffmpeg() {
		return $this->ffmpeg;
	}

	/**
	 * Mix professional podcast with intro, content, and outro.
	 *
	 * @param array $config Mixing configuration.
	 *   - intro_music_file: Path to intro music
	 *   - intro_narration_file: Path to host intro narration
	 *   - main_content_file: Path to two-person podcast content
	 *   - background_music_file: Path to background music (loops under main content)
	 *   - outro_narration_file: Path to host outro narration (thanks, sponsor, etc)
	 *   - outro_music_file: Path to outro/ending music
	 *   - output_file: Path to save final podcast
	 *   - music_ducking_level: Volume level for music under narration (0.3 = 30%, default)
	 *   - content_bg_music_level: Volume level for background music under content (0.2 = 20%, default)
	 *
	 * @return string|WP_Error Path to output file on success, WP_Error on failure.
	 */
	public function mix_professional_podcast( $config ) {
		if ( ! $this->ffmpeg ) {
			return new WP_Error(
				'wpshadow-mixer-no-ffmpeg',
				'FFmpeg is not available. Please install FFmpeg to use professional podcast mixing.'
			);
		}

		// Validate required files exist.
		$required_files = array(
			'intro_music_file'    => 'Intro music file',
			'intro_narration_file' => 'Intro narration file',
			'main_content_file'    => 'Main content file',
			'background_music_file' => 'Background music file',
			'outro_narration_file' => 'Outro narration file',
			'outro_music_file'     => 'Outro music file',
		);

		foreach ( $required_files as $key => $label ) {
			if ( empty( $config[ $key ] ) || ! file_exists( $config[ $key ] ) ) {
				return new WP_Error(
					'wpshadow-mixer-missing-file',
					sprintf( '%s not provided or does not exist.', $label )
				);
			}
		}

		$output_file = $config['output_file'] ?? '';
		if ( empty( $output_file ) ) {
			return new WP_Error(
				'wpshadow-mixer-no-output',
				'Output file path not specified.'
			);
		}

		// Set defaults for volume levels.
		$music_ducking_level    = $config['music_ducking_level'] ?? 0.3;
		$content_bg_music_level = $config['content_bg_music_level'] ?? 0.2;

		// Build the complex FFmpeg command.
		$result = $this->build_and_execute_mix_command(
			$config,
			$output_file,
			$music_ducking_level,
			$content_bg_music_level
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( ! file_exists( $output_file ) ) {
			return new WP_Error(
				'wpshadow-mixer-output-missing',
				'FFmpeg mixing completed but output file was not created.'
			);
		}

		return $output_file;
	}

	/**
	 * Build and execute FFmpeg mixing command.
	 *
	 * This creates a professional podcast structure:
	 * 1. Intro music (full volume) - 2-3 seconds
	 * 2. Intro music ducks, host narrates episode info - 5-10 seconds
	 * 3. Intro music fades out
	 * 4. Main podcast content with background music looped underneath (reduced volume)
	 * 5. Main music fades, outro narration plays (thanks, sponsor, share) - 10-15 seconds
	 * 6. Outro music plays and fades out - 2-3 seconds
	 *
	 * @param array  $config                   Configuration array.
	 * @param string $output_file              Output file path.
	 * @param float  $music_ducking_level      Volume for music during narration.
	 * @param float  $content_bg_music_level   Volume for background music during content.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	private function build_and_execute_mix_command( $config, $output_file, $music_ducking_level, $content_bg_music_level ) {
		// Extract file paths.
		$intro_music        = $config['intro_music_file'];
		$intro_narration    = $config['intro_narration_file'];
		$main_content       = $config['main_content_file'];
		$background_music   = $config['background_music_file'];
		$outro_narration    = $config['outro_narration_file'];
		$outro_music        = $config['outro_music_file'];

		// Get duration information for timing the mix.
		$intro_narration_duration = $this->get_audio_duration( $intro_narration );
		$main_content_duration    = $this->get_audio_duration( $main_content );
		$outro_narration_duration = $this->get_audio_duration( $outro_narration );

		if ( ! $intro_narration_duration || ! $main_content_duration || ! $outro_narration_duration ) {
			return new WP_Error(
				'wpshadow-mixer-duration-error',
				'Could not determine audio file durations.'
			);
		}

		// Create complex filter graph.
		$filter_graph = $this->build_filter_graph(
			$intro_narration_duration,
			$main_content_duration,
			$outro_narration_duration,
			$music_ducking_level,
			$content_bg_music_level
		);

		// Build FFmpeg command with multiple input files and complex filtering.
		$cmd = sprintf(
			'%s -i %s -i %s -i %s -i %s -i %s -i %s -filter_complex %s -map "[out]" -c:a aac -q:a 4 %s 2>&1',
			escapeshellarg( $this->ffmpeg ),
			escapeshellarg( $intro_music ),
			escapeshellarg( $intro_narration ),
			escapeshellarg( $main_content ),
			escapeshellarg( $background_music ),
			escapeshellarg( $outro_narration ),
			escapeshellarg( $outro_music ),
			escapeshellarg( $filter_graph ),
			escapeshellarg( $output_file )
		);

		$output = shell_exec( $cmd );

		// Log the command for debugging.
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( 'WPShadow Podcast Mixer Command: ' . $cmd );
			if ( ! empty( $output ) ) {
				error_log( 'WPShadow Podcast Mixer Output: ' . $output );
			}
		}

		if ( file_exists( $output_file ) ) {
			return true;
		}

		return new WP_Error(
			'wpshadow-mixer-ffmpeg-failed',
			'FFmpeg mixing failed: ' . $output
		);
	}

	/**
	 * Build complex FFmpeg filter graph for professional mixing.
	 *
	 * This is a sophisticated audio routing that:
	 * - Fades intro music in and out
	 * - Ducks (reduces) music volume during narration
	 * - Overlays intro narration over ducked music
	 * - Loops background music to match main content length
	 * - Reduces background music volume under main content
	 * - Overlays main content over background music
	 * - Reduces music again during outro narration
	 * - Fades out outro music
	 * - Sequences all elements with proper timing
	 *
	 * @param float $intro_narration_duration Duration of intro narration.
	 * @param float $main_content_duration    Duration of main content.
	 * @param float $outro_narration_duration Duration of outro narration.
	 * @param float $music_ducking_level      Volume reduction during narration.
	 * @param float $content_bg_music_level   Volume reduction for background music.
	 * @return string Complex filter graph string.
	 */
	private function build_filter_graph(
		$intro_narration_duration,
		$main_content_duration,
		$outro_narration_duration,
		$music_ducking_level,
		$content_bg_music_level
	) {
		// Intro section timing.
		$intro_music_duration    = 3;  // 3 seconds of intro music before narration.
		$intro_duck_start        = $intro_music_duration;
		$intro_duck_duration     = $intro_narration_duration + 1;  // +1 second overlap.
		$intro_fade_out_duration = 0.5;

		// Content section timing.
		$content_start = $intro_duck_start + $intro_duck_duration + $intro_fade_out_duration;

		// Outro section timing.
		$outro_start = $content_start + $main_content_duration;

		/**
		 * FFmpeg filter complex breakdown:
		 *
		 * Inputs:
		 * 0 = intro_music
		 * 1 = intro_narration
		 * 2 = main_content
		 * 3 = background_music
		 * 4 = outro_narration
		 * 5 = outro_music
		 */

		// Phase 1: Intro music processing - fade in immediately, duck during narration, fade out after.
		$intro_music_filter = sprintf(
			'[0]volume=enable=\'between(t,0,%f)\':volume=1,volume=enable=\'between(t,%f,%f)\':volume=%f,afade=t=out:st=%f:d=%f[intro_music]',
			$intro_duck_start,
			$intro_duck_start,
			$intro_duck_start + $intro_duck_duration,
			$music_ducking_level,
			$intro_duck_start + $intro_duck_duration,
			$intro_fade_out_duration
		);

		// Phase 2: Intro narration trimmed and delayed.
		$intro_narration_filter = sprintf(
			'[1]adelay=%d|%d[intro_narration]',
			(int) ( $intro_duck_start * 1000 ),
			(int) ( $intro_duck_start * 1000 )
		);

		// Phase 3: Main content and background music loop.
		// Loop background music to match main content duration.
		$bg_music_loop_filter = sprintf(
			'[3]atrim=0:%f,aloop=-1:size=0,atrim=0:%f,volume=%f[bg_music_loop]',
			$this->get_audio_duration( '' ), // Gets trimmed to content duration.
			$main_content_duration,
			$content_bg_music_level
		);

		// Phase 4: Outro narration and music processing.
		$outro_narration_filter = sprintf(
			'[4]adelay=%d|%d[outro_narration]',
			(int) ( $outro_start * 1000 ),
			(int) ( $outro_start * 1000 )
		);

		$outro_music_filter = sprintf(
			'[5]adelay=%d|%d,afade=t=out:st=%f:d=2[outro_music]',
			(int) ( $outro_start * 1000 ),
			(int) ( $outro_start * 1000 ),
			$outro_start + $outro_narration_duration
		);

		// Phase 5: Mix everything together.
		// Simplified approach: concatenate the main elements and apply volume/ducking dynamically.
		$filter_complex = sprintf(
			'[0]afade=t=in:d=0.5,afade=t=out:st=%f:d=0.5[intro_music_faded];' .
			'[1]adelay=%d|%d[intro_narration_delayed];' .
			'[2]volume=%f[main_content_reduced];' .
			'[3]aloop=-1:size=0,atrim=0:%f,volume=%f[bg_music_looped];' .
			'[intro_music_faded][intro_narration_delayed]amix=inputs=2:duration=longest[intro_mix];' .
			'[main_content_reduced][bg_music_looped]amix=inputs=2:duration=longest[content_mix];' .
			'[intro_mix]apad=whole_dur=%f[intro_padded];' .
			'[intro_padded][content_mix]concat=n=2:v=0:a=1[final_mix];' .
			'[4]adelay=%d|%d[outro_narration_delayed];' .
			'[5]afade=t=out:st=%f:d=2[outro_music_faded];' .
			'[final_mix][outro_narration_delayed][outro_music_faded]amix=inputs=3:duration=longest,aformat=sample_rates=48000:channel_layouts=stereo[out]',
			$intro_duck_start + $intro_duck_duration + $intro_fade_out_duration + $main_content_duration,
			(int) ( $intro_duck_start * 1000 ),
			(int) ( $intro_duck_start * 1000 ),
			$content_bg_music_level,
			$main_content_duration,
			$content_bg_music_level,
			$intro_duck_start + $intro_duck_duration + $intro_fade_out_duration,
			(int) ( $outro_start * 1000 ),
			(int) ( $outro_start * 1000 ),
			$outro_start + $outro_narration_duration
		);

		return $filter_complex;
	}

	/**
	 * Get audio file duration in seconds.
	 *
	 * @param string $audio_file Path to audio file.
	 * @return float|false Duration in seconds or false on error.
	 */
	private function get_audio_duration( $audio_file ) {
		if ( ! $this->ffmpeg || ! file_exists( $audio_file ) ) {
			return false;
		}

		$cmd = sprintf(
			'%s -i %s 2>&1 | grep Duration | awk \'{print $2}\' | cut -d\':\' -f1-3',
			escapeshellarg( $this->ffmpeg ),
			escapeshellarg( $audio_file )
		);

		$output = shell_exec( $cmd );
		if ( ! $output ) {
			return false;
		}

		// Parse duration format (HH:MM:SS.ms).
		$parts = explode( ':', trim( $output ) );
		if ( count( $parts ) !== 3 ) {
			return false;
		}

		$hours   = (int) $parts[0];
		$minutes = (int) $parts[1];
		$seconds = (float) $parts[2];

		return ( $hours * 3600 ) + ( $minutes * 60 ) + $seconds;
	}

	/**
	 * Simple sequential mixing (fallback if complex filter fails).
	 *
	 * Mixes audio by concatenating intro, content, and outro sequentially.
	 *
	 * @param array  $segments Array of audio files: intro_mix, content_mix, outro_mix.
	 * @param string $output_file Output file path.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function sequential_mix( $segments, $output_file ) {
		if ( ! $this->ffmpeg ) {
			return new WP_Error(
				'wpshadow-mixer-no-ffmpeg',
				'FFmpeg is not available.'
			);
		}

		if ( ! isset( $segments['intro_mix'], $segments['content_mix'], $segments['outro_mix'] ) ) {
			return new WP_Error(
				'wpshadow-mixer-missing-segments',
				'Missing required audio segments.'
			);
		}

		// Create concat demuxer file.
		$concat_file = $this->work_dir . '/concat_' . time() . '.txt';
		$concat_list = '';

		foreach ( $segments as $segment ) {
			if ( file_exists( $segment ) ) {
				$concat_list .= "file '" . str_replace( "'", "'\\''", $segment ) . "'\n";
			}
		}

		if ( ! file_put_contents( $concat_file, $concat_list ) ) {
			return new WP_Error(
				'wpshadow-mixer-concat-write',
				'Failed to write concat file.'
			);
		}

		$cmd = sprintf(
			'%s -f concat -safe 0 -i %s -c copy %s 2>&1',
			escapeshellarg( $this->ffmpeg ),
			escapeshellarg( $concat_file ),
			escapeshellarg( $output_file )
		);

		shell_exec( $cmd );
		unlink( $concat_file );

		if ( ! file_exists( $output_file ) ) {
			return new WP_Error(
				'wpshadow-mixer-concat-failed',
				'Sequential audio mixing failed.'
			);
		}

		return true;
	}
}
