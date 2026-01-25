<?php
/**
 * Audio Splitter Utility
 * 
 * Splits a podcast audio file into segments (intro, main, outro)
 * Requires FFmpeg to be installed on the server
 * 
 * @package WPShadow_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Split podcast audio into segments
 */
function wpshadow_split_podcast_audio( $source_file, $segments ) {
	/**
	 * Split a single audio file into multiple segments
	 * 
	 * Usage:
	 * $segments = array(
	 *     'intro' => array( 'start' => 0, 'end' => 32 ),           // 0:00 - 0:32
	 *     'main'  => array( 'start' => 32, 'end' => 116 ),         // 0:32 - 1:56
	 *     'outro' => array( 'start' => 116, 'end' => 150 ),        // 1:56 - 2:30
	 * );
	 * 
	 * wpshadow_split_podcast_audio( '/path/to/podcast.mp3', $segments );
	 * 
	 * Creates:
	 * - intro.mp3 (32 seconds)
	 * - main.mp3 (84 seconds)
	 * - outro.mp3 (34 seconds)
	 */
	
	$ffmpeg = wpshadow_find_ffmpeg();
	if ( ! $ffmpeg ) {
		return new WP_Error( 'ffmpeg-not-found', 'FFmpeg not found on server' );
	}
	
	if ( ! file_exists( $source_file ) ) {
		return new WP_Error( 'file-not-found', 'Source audio file not found: ' . $source_file );
	}
	
	$output_dir = dirname( $source_file );
	$results    = array();
	
	foreach ( $segments as $name => $times ) {
		$start = $times['start'];
		$end   = $times['end'];
		
		$output_file = $output_dir . '/' . $name . '.mp3';
		
		// FFmpeg command to extract segment.
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
				sprintf( 'Failed to create %s segment: %s', $name, $output )
			);
		}
		
		$results[ $name ] = array(
			'file'     => $output_file,
			'duration' => $end - $start,
			'size'     => filesize( $output_file ),
		);
	}
	
	return $results;
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

// Example usage (uncomment to run).
/*
$segments = array(
	'intro' => array( 'start' => 0,   'end' => 32 ),    // 0:00 - 0:32
	'main'  => array( 'start' => 32,  'end' => 116 ),   // 0:32 - 1:56
	'outro' => array( 'start' => 116, 'end' => 150 ),   // 1:56 - 2:30
);

$result = wpshadow_split_podcast_audio(
	'/path/to/podcast.mp3',
	$segments
);

if ( is_wp_error( $result ) ) {
	error_log( 'Error: ' . $result->get_error_message() );
} else {
	foreach ( $result as $segment => $info ) {
		error_log( sprintf(
			'✓ %s: %d seconds, %s',
			$segment,
			$info['duration'],
			size_format( $info['size'] )
		) );
	}
}
*/
