<?php
/**
 * Audio File Upload Limits Diagnostic
 *
 * Validates audio file upload configuration and PHP limits for audio files.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Audio File Upload Limits Diagnostic Class
 *
 * Validates that WordPress has appropriate upload limits configured
 * for audio files including MP3, WAV, OGG, and other audio formats.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Audio_File_Upload_Limits extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'audio-file-upload-limits';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Audio File Upload Limits';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates audio file upload configuration and PHP limits';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests if WordPress allows audio uploads and if PHP limits
	 * are sufficient for typical audio file sizes.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Get allowed MIME types for audio.
		$allowed_mimes = get_allowed_mime_types();

		// Check for specific audio format support.
		$supports_mp3 = false;
		$supports_wav = false;
		$supports_ogg = false;
		$supports_m4a = false;
		$supports_flac = false;
		$audio_formats_count = 0;

		foreach ( $allowed_mimes as $ext => $mime ) {
			if ( strpos( $mime, 'audio/' ) === 0 ) {
				$audio_formats_count++;
				if ( strpos( $ext, 'mp3' ) !== false || strpos( $mime, 'mpeg' ) !== false ) {
					$supports_mp3 = true;
				}
				if ( strpos( $ext, 'wav' ) !== false ) {
					$supports_wav = true;
				}
				if ( strpos( $ext, 'ogg' ) !== false ) {
					$supports_ogg = true;
				}
				if ( strpos( $ext, 'm4a' ) !== false ) {
					$supports_m4a = true;
				}
				if ( strpos( $ext, 'flac' ) !== false ) {
					$supports_flac = true;
				}
			}
		}

		// Get upload limits.
		$max_upload_size = wp_max_upload_size();
		$max_upload_mb   = $max_upload_size / ( 1024 * 1024 );

		// Get PHP settings.
		$post_max_size       = ini_get( 'post_max_size' );
		$upload_max_filesize = ini_get( 'upload_max_filesize' );
		$memory_limit        = ini_get( 'memory_limit' );

		// Convert to bytes for comparison.
		$post_max_bytes   = wp_convert_hr_to_bytes( $post_max_size );
		$upload_max_bytes = wp_convert_hr_to_bytes( $upload_max_filesize );
		$memory_bytes     = wp_convert_hr_to_bytes( $memory_limit );

		// Check for audio processing capabilities.
		$sox_available = false;
		if ( function_exists( 'shell_exec' ) ) {
			$sox_check = @shell_exec( 'which sox' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			$sox_available = ! empty( $sox_check );
		}

		$ffmpeg_available = false;
		if ( function_exists( 'shell_exec' ) ) {
			$ffmpeg_check = @shell_exec( 'which ffmpeg' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			$ffmpeg_available = ! empty( $ffmpeg_check );
		}

		// Check uploads directory.
		$upload_dir = wp_upload_dir();
		$uploads_writable = wp_is_writable( $upload_dir['path'] );

		// Get recent audio uploads.
		global $wpdb;
		$recent_audio_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			 WHERE post_type = 'attachment' 
			 AND post_mime_type LIKE 'audio/%'
			 AND post_date > DATE_SUB(NOW(), INTERVAL 30 DAY)"
		);

		// Get average audio file size.
		$avg_audio_size = $wpdb->get_var(
			"SELECT AVG(CAST(meta_value AS UNSIGNED)) 
			 FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			 WHERE p.post_type = 'attachment' 
			 AND p.post_mime_type LIKE 'audio/%'
			 AND p.post_date > DATE_SUB(NOW(), INTERVAL 30 DAY)
			 AND pm.meta_key = '_wp_attachment_metadata'"
		);

		// Typical audio file sizes.
		$typical_mp3_size    = 5 * 1024 * 1024;   // 5 MB per 10 minutes.
		$typical_wav_size    = 50 * 1024 * 1024;  // 50 MB for 10 minutes.
		$typical_podcast_size = 30 * 1024 * 1024; // 30 MB typical podcast.

		// Check for issues.
		$issues = array();

		// Issue 1: No audio format support.
		if ( $audio_formats_count === 0 ) {
			$issues[] = array(
				'type'        => 'no_audio_formats',
				'description' => __( 'No audio file formats are allowed for upload', 'wpshadow' ),
			);
		}

		// Issue 2: Only MP3 supported (limited compatibility).
		if ( $supports_mp3 && ! $supports_ogg && ! $supports_wav && ! $supports_m4a ) {
			$issues[] = array(
				'type'        => 'limited_formats',
				'description' => __( 'Only MP3 audio format supported; should support WAV, OGG, or M4A for compatibility', 'wpshadow' ),
			);
		}

		// Issue 3: Upload limit too low for typical audio files.
		if ( $max_upload_mb < 50 ) {
			$issues[] = array(
				'type'        => 'low_upload_limit',
				'description' => sprintf(
					/* translators: %d: upload limit in MB */
					__( 'Upload limit is only %d MB; typical audio files are 20-100 MB', 'wpshadow' ),
					round( $max_upload_mb, 2 )
				),
			);
		}

		// Issue 4: Mismatched PHP settings.
		if ( $post_max_bytes < $upload_max_bytes ) {
			$issues[] = array(
				'type'        => 'mismatched_limits',
				'description' => sprintf(
					/* translators: 1: post_max_size, 2: upload_max_filesize */
					__( 'post_max_size (%1$s) is smaller than upload_max_filesize (%2$s)', 'wpshadow' ),
					$post_max_size,
					$upload_max_filesize
				),
			);
		}

		// Issue 5: Low memory limit for audio processing.
		if ( $memory_bytes < ( 128 * 1024 * 1024 ) ) {
			$issues[] = array(
				'type'        => 'low_memory',
				'description' => __( 'Memory limit below 128 MB; may be insufficient for audio processing', 'wpshadow' ),
			);
		}

		// Issue 6: Uploads directory not writable.
		if ( ! $uploads_writable ) {
			$issues[] = array(
				'type'        => 'not_writable',
				'description' => __( 'Uploads directory is not writable', 'wpshadow' ),
			);
		}

		// Issue 7: No audio processing tools.
		if ( ! $sox_available && ! $ffmpeg_available ) {
			$issues[] = array(
				'type'        => 'no_processing_tools',
				'description' => __( 'No audio processing tools (SoX or FFmpeg) available for format conversion or optimization', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Audio file upload has configuration issues that may prevent or limit audio uploads', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/audio-file-upload-limits',
				'details'      => array(
					'supports_mp3'            => $supports_mp3,
					'supports_wav'            => $supports_wav,
					'supports_ogg'            => $supports_ogg,
					'supports_m4a'            => $supports_m4a,
					'supports_flac'           => $supports_flac,
					'total_audio_formats'     => $audio_formats_count,
					'max_upload_size'         => size_format( $max_upload_size ),
					'max_upload_mb'           => round( $max_upload_mb, 2 ),
					'post_max_size'           => $post_max_size,
					'upload_max_filesize'     => $upload_max_filesize,
					'memory_limit'            => $memory_limit,
					'uploads_writable'        => $uploads_writable,
					'sox_available'           => $sox_available,
					'ffmpeg_available'        => $ffmpeg_available,
					'recent_audio_uploads'    => absint( $recent_audio_count ),
					'typical_mp3_size'        => size_format( $typical_mp3_size ),
					'typical_wav_size'        => size_format( $typical_wav_size ),
					'typical_podcast_size'    => size_format( $typical_podcast_size ),
					'issues_detected'         => $issues,
					'recommendation'          => __( 'Increase PHP upload limits to at least 100MB and ensure audio processing tools are available', 'wpshadow' ),
					'php_ini_changes'         => array(
						'upload_max_filesize' => '100M',
						'post_max_size'       => '100M',
						'memory_limit'        => '256M',
						'max_execution_time'  => '300',
					),
					'supported_formats'       => array(
						'MP3'  => 'Most compatible format',
						'WAV'  => 'Uncompressed, higher quality',
						'OGG'  => 'Open source, good compression',
						'M4A'  => 'Apple native format',
						'FLAC' => 'Lossless compression',
					),
				),
			);
		}

		return null;
	}
}
