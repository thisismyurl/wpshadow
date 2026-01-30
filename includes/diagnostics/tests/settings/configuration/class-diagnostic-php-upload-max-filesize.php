<?php
/**
 * Diagnostic: PHP upload_max_filesize
 *
 * Checks if PHP upload_max_filesize is adequate for WordPress uploads.
 * Should be aligned with post_max_size and typical media sizes.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Php_Upload_Max_Filesize
 *
 * Tests PHP upload_max_filesize configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Php_Upload_Max_Filesize extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-upload-max-filesize';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP upload_max_filesize';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP upload_max_filesize is adequate';

	/**
	 * Convert PHP size notation to bytes.
	 *
	 * @param string $size Size string.
	 * @return int Size in bytes.
	 */
	private static function convert_to_bytes( $size ) {
		$size = trim( (string) $size );

		if ( is_numeric( $size ) ) {
			return (int) $size;
		}

		$last  = strtoupper( substr( $size, -1 ) );
		$value = (int) substr( $size, 0, -1 );

		switch ( $last ) {
			case 'G':
				return $value * 1024 * 1024 * 1024;
			case 'M':
				return $value * 1024 * 1024;
			case 'K':
				return $value * 1024;
			default:
				return (int) $size;
		}
	}

	/**
	 * Check PHP upload_max_filesize setting.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$upload_max_filesize = ini_get( 'upload_max_filesize' );
		$post_max_size       = ini_get( 'post_max_size' );

		$upload_bytes = self::convert_to_bytes( $upload_max_filesize );
		$post_bytes   = self::convert_to_bytes( $post_max_size );

		// Minimum recommended for WordPress media uploads.
		$minimum_bytes = 8 * 1024 * 1024;

		if ( $upload_bytes < $minimum_bytes ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Current upload_max_filesize */
					__( 'PHP upload_max_filesize is set to %s, which is low for WordPress media uploads. Consider setting it to at least 8M.', 'wpshadow' ),
					$upload_max_filesize
				),
				'severity'    => 'info',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_upload_max_filesize',
				'meta'        => array(
					'upload_max_filesize' => $upload_max_filesize,
					'upload_bytes'        => $upload_bytes,
				),
			);
		}

		// upload_max_filesize should not exceed post_max_size.
		if ( $post_bytes > 0 && $upload_bytes > $post_bytes ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: upload_max_filesize, 2: post_max_size */
					__( 'PHP upload_max_filesize (%1$s) is larger than post_max_size (%2$s). Uploads above post_max_size will fail. Align these values so post_max_size is slightly larger.', 'wpshadow' ),
					$upload_max_filesize,
					$post_max_size
				),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_upload_max_filesize',
				'meta'        => array(
					'upload_max_filesize' => $upload_max_filesize,
					'post_max_size'       => $post_max_size,
				),
			);
		}

		return null;
	}
}
