<?php
/**
 * Diagnostic: PHP post_max_size
 *
 * Checks if PHP post_max_size is adequate for WordPress operations.
 * Should be slightly larger than upload_max_filesize to account for form data.
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
 * Class Diagnostic_Php_Post_Max_Size
 *
 * Tests PHP post_max_size configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Php_Post_Max_Size extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-post-max-size';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP post_max_size';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP post_max_size is adequate';

	/**
	 * Convert PHP size notation to bytes.
	 *
	 * @param string $size Size string (e.g., "128M", "1G").
	 * @return int Size in bytes.
	 */
	private static function convert_to_bytes( $size ) {
		$size = (string) $size;
		$size = trim( $size );

		if ( is_numeric( $size ) ) {
			return (int) $size;
		}

		// Get the last character.
		$last = strtoupper( substr( $size, -1 ) );
		$value = (int) substr( $size, 0, -1 );

		switch ( $last ) {
			case 'K':
				return $value * 1024;
			case 'M':
				return $value * 1024 * 1024;
			case 'G':
				return $value * 1024 * 1024 * 1024;
			default:
				return (int) $size;
		}
	}

	/**
	 * Check PHP post_max_size setting.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Get post_max_size and upload_max_filesize.
		$post_max_size = ini_get( 'post_max_size' );
		$upload_max_filesize = ini_get( 'upload_max_filesize' );

		// Convert to bytes for comparison.
		$post_max_bytes = self::convert_to_bytes( $post_max_size );
		$upload_max_bytes = self::convert_to_bytes( $upload_max_filesize );

		// post_max_size should be at least as large as upload_max_filesize.
		if ( $post_max_bytes < $upload_max_bytes ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: post_max_size, 2: upload_max_filesize */
					__( 'PHP post_max_size (%1$s) is smaller than upload_max_filesize (%2$s). This may cause file uploads to fail. Set post_max_size to at least equal to or larger than upload_max_filesize.', 'wpshadow' ),
					$post_max_size,
					$upload_max_filesize
				),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_post_max_size',
				'meta'        => array(
					'post_max_size'         => $post_max_size,
					'upload_max_filesize'   => $upload_max_filesize,
					'post_max_bytes'        => $post_max_bytes,
					'upload_max_bytes'      => $upload_max_bytes,
				),
			);
		}

		// Warn if very low (below 8MB).
		$minimum_safe = 8 * 1024 * 1024;

		if ( $post_max_bytes < $minimum_safe ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Current post_max_size */
					__( 'PHP post_max_size is set to %s, which is very low. This may restrict file uploads and form submissions. Consider setting it to at least 8M.', 'wpshadow' ),
					$post_max_size
				),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_post_max_size',
				'meta'        => array(
					'post_max_size'  => $post_max_size,
					'post_max_bytes' => $post_max_bytes,
				),
			);
		}

		// PHP post_max_size is properly configured.
		return null;
	}
}
