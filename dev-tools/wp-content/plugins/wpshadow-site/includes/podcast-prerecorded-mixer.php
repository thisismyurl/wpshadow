<?php
/**
 * Podcast Mixer - Support for Pre-Recorded Audio Segments
 *
 * Extension to WPShadow_Podcast_Studio_Mixer that allows using
 * pre-recorded audio segments (intro, main, outro) instead of
 * generating them on the fly.
 *
 * @package WPShadow_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Example: Using Pre-Recorded Audio Segments
 *
 * Your podcast.mp3 has been split into:
 * - intro.mp3 (0:00 - 0:32, 32 seconds)
 * - main.mp3 (0:32 - 1:56, 84 seconds)
 * - outro.mp3 (1:56 - 2:30, 34 seconds)
 */

function wpshadow_example_use_prerecorded_segments() {
	/**
	 * If you have pre-recorded segments, you can mix them directly
	 * without going through TTS synthesis and individual mixing.
	 */

	$audio_dir = WP_CONTENT_DIR . '/plugins/wpshadow-site/assets/audio';

	// Your pre-recorded segments.
	$intro_file = $audio_dir . '/intro.mp3';
	$main_file  = $audio_dir . '/main.mp3';
	$outro_file = $audio_dir . '/outro.mp3';

	// If all three segments exist, you can use them directly.
	if ( file_exists( $intro_file ) && file_exists( $main_file ) && file_exists( $outro_file ) ) {
		return wpshadow_mix_prerecorded_podcast(
			$intro_file,
			$main_file,
			$outro_file
		);
	}

	return new WP_Error( 'missing-segments', 'One or more audio segments not found' );
}

/**
 * Mix pre-recorded podcast segments without TTS
 *
 * @param string $intro_file Path to intro segment.
 * @param string $main_file  Path to main podcast segment.
 * @param string $outro_file Path to outro segment.
 * @return array|WP_Error Array with final file on success.
 */
function wpshadow_mix_prerecorded_podcast( $intro_file, $main_file, $outro_file ) {
	// Validate files exist.
	foreach ( array( 'intro' => $intro_file, 'main' => $main_file, 'outro' => $outro_file ) as $name => $file ) {
		if ( ! file_exists( $file ) ) {
			return new WP_Error( 'missing-file', "Missing $name segment: $file" );
		}
	}

	$ffmpeg = wpshadow_find_ffmpeg();
	if ( ! $ffmpeg ) {
		return new WP_Error( 'ffmpeg-missing', 'FFmpeg is required' );
	}

	// Use FFmpeg to concatenate the segments.
	$audio_dir   = WP_CONTENT_DIR . '/uploads/wpshadow-podcast-audio';
	$concat_file = $audio_dir . '/concat_' . wp_generate_password( 8, false, false ) . '.txt';
	$output_file = $audio_dir . '/final_podcast_' . gmdate( 'Y-m-d_His' ) . '.mp3';

	// Create concat demuxer file.
	$concat_content = "file '" . str_replace( "'", "'\\''", $intro_file ) . "'\n";
	$concat_content .= "file '" . str_replace( "'", "'\\''", $main_file ) . "'\n";
	$concat_content .= "file '" . str_replace( "'", "'\\''", $outro_file ) . "'\n";

	$written = file_put_contents( $concat_file, $concat_content );
	if ( false === $written ) {
		return new WP_Error( 'concat-write-failed', 'Failed to write concat file' );
	}

	// FFmpeg concatenation command.
	$cmd = sprintf(
		'%s -f concat -safe 0 -i %s -c copy %s 2>&1',
		escapeshellarg( $ffmpeg ),
		escapeshellarg( $concat_file ),
		escapeshellarg( $output_file )
	);

	$result = shell_exec( $cmd );

	// Clean up concat file.
	unlink( $concat_file );

	// Verify output.
	if ( ! file_exists( $output_file ) || 0 === filesize( $output_file ) ) {
		return new WP_Error( 'concatenation-failed', 'FFmpeg concatenation failed: ' . $result );
	}

	return array(
		'podcast_file' => $output_file,
		'segments'     => array(
			'intro' => $intro_file,
			'main'  => $main_file,
			'outro' => $outro_file,
		),
		'method'       => 'prerecorded',
	);
}

/**
 * Find FFmpeg executable
 */
function wpshadow_find_ffmpeg() {
	$commands = array( 'ffmpeg', '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg' );

	foreach ( $commands as $cmd ) {
		$output = shell_exec( "which $cmd 2>/dev/null" );
		if ( ! empty( $output ) ) {
			return trim( $output );
		}
	}

	return false;
}

// ============================================================================
// SPLIT YOUR PODCAST AUDIO
// ============================================================================

/**
 * WordPress CLI Command to Split Audio
 *
 * Usage:
 * wp wpshadow split-podcast --source=/path/to/podcast.mp3 \
 *   --intro-end=32 --main-end=116 --outro-end=150
 *
 * Or with defaults (for your podcast.mp3):
 * wp wpshadow split-podcast
 */
if ( defined( 'WP_CLI' ) ) {

	WP_CLI::add_command( 'wpshadow split-podcast', function( $args, $assoc_args ) {
		$source = isset( $assoc_args['source'] )
			? $assoc_args['source']
			: WP_CONTENT_DIR . '/plugins/wpshadow-site/assets/audio/podcast.mp3';

		$intro_end = isset( $assoc_args['intro-end'] ) ? intval( $assoc_args['intro-end'] ) : 32;
		$main_end  = isset( $assoc_args['main-end'] ) ? intval( $assoc_args['main-end'] ) : 116;
		$outro_end = isset( $assoc_args['outro-end'] ) ? intval( $assoc_args['outro-end'] ) : 150;

		WP_CLI::log( "Splitting podcast audio..." );
		WP_CLI::log( "Source: $source" );
		WP_CLI::log( "Segments: intro(0-$intro_end), main($intro_end-$main_end), outro($main_end-$outro_end)" );

		$segments = array(
			'intro' => array( 'start' => 0,         'end' => $intro_end ),
			'main'  => array( 'start' => $intro_end, 'end' => $main_end ),
			'outro' => array( 'start' => $main_end,  'end' => $outro_end ),
		);

		$result = wpshadow_split_podcast_audio( $source, $segments );

		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}

		WP_CLI::success( "Podcast split successfully!" );

		foreach ( $result as $segment => $info ) {
			WP_CLI::log( sprintf(
				"  ✓ %s: %d seconds (%s)",
				$segment,
				$info['duration'],
				size_format( $info['size'] )
			) );
		}
	} );
}

/**
 * Function to split podcast audio
 */
function wpshadow_split_podcast_audio( $source_file, $segments ) {
	$ffmpeg = wpshadow_find_ffmpeg();
	if ( ! $ffmpeg ) {
		return new WP_Error( 'ffmpeg-not-found', 'FFmpeg not found on server' );
	}

	if ( ! file_exists( $source_file ) ) {
		return new WP_Error( 'file-not-found', 'Source file not found: ' . $source_file );
	}

	$output_dir = dirname( $source_file );
	$results    = array();

	foreach ( $segments as $name => $times ) {
		$start       = $times['start'];
		$end         = $times['end'];
		$output_file = $output_dir . '/' . $name . '.mp3';

		// Skip if already exists.
		if ( file_exists( $output_file ) ) {
			$results[ $name ] = array(
				'file'     => $output_file,
				'duration' => $end - $start,
				'size'     => filesize( $output_file ),
				'status'   => 'already exists',
			);
			continue;
		}

		$cmd = sprintf(
			'%s -i %s -ss %d -to %d -acodec copy %s 2>&1',
			escapeshellarg( $ffmpeg ),
			escapeshellarg( $source_file ),
			$start,
			$end,
			escapeshellarg( $output_file )
		);

		$output = shell_exec( $cmd );

		if ( ! file_exists( $output_file ) ) {
			return new WP_Error(
				'split-failed',
				sprintf( 'Failed to create %s: %s', $name, $output )
			);
		}

		$results[ $name ] = array(
			'file'     => $output_file,
			'duration' => $end - $start,
			'size'     => filesize( $output_file ),
			'status'   => 'created',
		);
	}

	return $results;
}
