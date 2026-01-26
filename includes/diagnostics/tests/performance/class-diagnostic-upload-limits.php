<?php
/**
 * Diagnostic: File Upload Limits
 *
 * Checks if file upload limits allow reasonably sized media files.
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Upload_Limits Class
 *
 * Detects if file upload limits are too restrictive. WordPress has several
 * limits that can restrict file uploads:
 *
 * - upload_max_filesize: Maximum size of one uploaded file (default 2M)
 * - post_max_size: Maximum size of entire POST request (default 8M)
 * - memory_limit: PHP memory available (indirectly affects uploads)
 *
 * The effective upload limit is the MINIMUM of these values. If any are too
 * low, users can't upload large images, videos, or other media files.
 *
 * Returns different threat levels based on the smallest limit encountered.
 *
 * @since 1.2601.2200
 */
class Diagnostic_Upload_Limits extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @var string
	 */
	protected static $slug = 'upload-limits';

	/**
	 * Diagnostic title (user-facing)
	 *
	 * @var string
	 */
	protected static $title = 'File Upload Limits';

	/**
	 * Diagnostic description (plain language)
	 *
	 * @var string
	 */
	protected static $description = 'Verifies file upload limits allow reasonably sized media files';

	/**
	 * Family grouping for batch operations
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Family label (human-readable)
	 *
	 * @var string
	 */
	protected static $family_label = 'Performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks upload_max_filesize and post_max_size, taking the lower value
	 * as the effective limit.
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if limits are too low, null if adequate.
	 */
	public static function check() {
		$upload_max_filesize = self::convert_to_bytes( ini_get( 'upload_max_filesize' ) );
		$post_max_size       = self::convert_to_bytes( ini_get( 'post_max_size' ) );

		// The effective limit is the minimum of the two
		$effective_limit = min( $upload_max_filesize, $post_max_size );

		$minimum_recommended = 20 * 1024 * 1024;   // 20MB
		$optimal             = 50 * 1024 * 1024;   // 50MB

		// High: Below 20MB (restrictive)
		if ( $effective_limit < $minimum_recommended ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: 1: current limit in MB, 2: recommended minimum in MB */
					esc_html__( 'Your maximum upload size is %1$dMB. This is quite restrictive for modern media. We recommend at least %2$dMB to allow users to upload reasonably sized images and videos.', 'wpshadow' ),
					(int) ( $effective_limit / 1024 / 1024 ),
					20
				),
				'severity'           => 'medium',
				'threat_level'       => 50,
				'site_health_status' => 'recommended',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/performance-upload-limits',
				'family'             => self::$family,
				'details'            => array(
					'upload_max_filesize' => ini_get( 'upload_max_filesize' ),
					'post_max_size'       => ini_get( 'post_max_size' ),
					'effective_limit_mb'  => (int) ( $effective_limit / 1024 / 1024 ),
					'minimum_recommended' => 20,
					'optimal_mb'          => 50,
					'recommendation'      => 'Contact hosting provider to increase upload limits to 50MB+',
				),
			);
		}

		// Low: Between 20-50MB (acceptable but tight for video)
		if ( $effective_limit < $optimal ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: 1: current limit in MB, 2: optimal limit in MB */
					esc_html__( 'Your maximum upload size is %1$dMB, which is adequate for most images but restrictive for high-quality video. We recommend %2$dMB or higher for flexibility.', 'wpshadow' ),
					(int) ( $effective_limit / 1024 / 1024 ),
					50
				),
				'severity'           => 'low',
				'threat_level'       => 25,
				'site_health_status' => 'recommended',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/performance-upload-limits',
				'family'             => self::$family,
				'details'            => array(
					'upload_max_filesize' => ini_get( 'upload_max_filesize' ),
					'post_max_size'       => ini_get( 'post_max_size' ),
					'effective_limit_mb'  => (int) ( $effective_limit / 1024 / 1024 ),
					'minimum_recommended' => 20,
					'optimal_mb'          => 50,
					'recommendation'      => 'Consider requesting increase to 50MB+ from hosting provider',
				),
			);
		}

		// All good - upload limits are adequate
		return null;
	}

	/**
	 * Convert size notation to bytes.
	 *
	 * Converts PHP size notations like "256M" or "512MB" to bytes.
	 *
	 * @since  1.2601.2200
	 * @param  string $value Size string.
	 * @return int Size in bytes.
	 */
	private static function convert_to_bytes( string $value ): int {
		$value = trim( $value );

		if ( is_numeric( $value ) ) {
			return (int) $value;
		}

		$unit  = strtoupper( substr( $value, -1 ) );
		$bytes = (int) $value;

		switch ( $unit ) {
			case 'K':
				return $bytes * 1024;
			case 'M':
				return $bytes * 1024 * 1024;
			case 'G':
				return $bytes * 1024 * 1024 * 1024;
			default:
				return $bytes;
		}
	}
}
