<?php
/**
 * Upload Size Limit Diagnostic
 *
 * Checks upload file size limits.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1508
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Upload Size Limit Diagnostic Class
 *
 * Verifies adequate upload file size limits are configured.
 *
 * @since 1.6035.1508
 */
class Diagnostic_Upload_Size_Limit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'upload-size-limit';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Upload Size Limit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks upload file size limits';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'hosting-environment';

	/**
	 * Minimum upload size
	 *
	 * @var int
	 */
	private const MIN_UPLOAD_SIZE = 64 * 1024 * 1024; // 64MB

	/**
	 * Recommended upload size
	 *
	 * @var int
	 */
	private const RECOMMENDED_UPLOAD_SIZE = 128 * 1024 * 1024; // 128MB

	/**
	 * Run the upload size diagnostic check.
	 *
	 * @since  1.6035.1508
	 * @return array|null Finding array if upload issue detected, null otherwise.
	 */
	public static function check() {
		$upload_max = self::parse_size( ini_get( 'upload_max_filesize' ) );
		$post_max = self::parse_size( ini_get( 'post_max_size' ) );

		$max_size = min( $upload_max, $post_max );

		if ( $max_size < self::MIN_UPLOAD_SIZE ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: current limit, 2: minimum recommended */
					__( 'Upload limit is %1$s, below minimum %2$s. Large file uploads will fail.', 'wpshadow' ),
					self::format_bytes( $max_size ),
					self::format_bytes( self::MIN_UPLOAD_SIZE )
				),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/increase-upload-limit',
				'meta'        => array(
					'upload_max_filesize' => ini_get( 'upload_max_filesize' ),
					'post_max_size'       => ini_get( 'post_max_size' ),
					'max_effective_size'  => self::format_bytes( $max_size ),
				),
			);
		}

		if ( $max_size < self::RECOMMENDED_UPLOAD_SIZE ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: current limit, 2: recommended limit */
					__( 'Upload limit is %1$s. Recommended minimum is %2$s for optimal flexibility.', 'wpshadow' ),
					self::format_bytes( $max_size ),
					self::format_bytes( self::RECOMMENDED_UPLOAD_SIZE )
				),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/increase-upload-limit',
				'meta'        => array(
					'upload_max_filesize' => ini_get( 'upload_max_filesize' ),
					'post_max_size'       => ini_get( 'post_max_size' ),
				),
			);
		}

		return null;
	}

	/**
	 * Parse size string to bytes.
	 *
	 * @since  1.6035.1508
	 * @param  string $value Size value (e.g., '64M', '2G').
	 * @return int Size in bytes.
	 */
	private static function parse_size( string $value ): int {
		$value = trim( $value );
		$value = (int) $value;
		$last_char = strtoupper( substr( $value, -1 ) );

		switch ( $last_char ) {
			case 'G':
				$value *= 1024;
				// Fall through.
			case 'M':
				$value *= 1024;
				// Fall through.
			case 'K':
				$value *= 1024;
				break;
		}

		return $value;
	}

	/**
	 * Format bytes to human readable.
	 *
	 * @since  1.6035.1508
	 * @param  int $bytes Bytes to format.
	 * @return string Formatted bytes.
	 */
	private static function format_bytes( int $bytes ): string {
		$units = array( 'B', 'KB', 'MB', 'GB' );
		$bytes = max( $bytes, 0 );
		$pow = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow = min( $pow, count( $units ) - 1 );
		$bytes /= ( 1 << ( 10 * $pow ) );

		return round( $bytes, 2 ) . ' ' . $units[ $pow ];
	}
}
