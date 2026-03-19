<?php
/**
 * File Upload Success Diagnostic
 *
 * Verifies that the uploads directory is writable and correctly configured.
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
 * Diagnostic_File_Upload_Success Class
 *
 * Ensures WordPress can write to the uploads directory.
 *
 * @since 1.6093.1200
 */
class Diagnostic_File_Upload_Success extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'file-upload-success';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'File Upload Success';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether uploads directory is writable';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'wordpress-health';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$uploads = wp_get_upload_dir();
		if ( ! empty( $uploads['error'] ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: error message */
					__( 'Uploads directory error: %s', 'wpshadow' ),
					esc_html( $uploads['error'] )
				),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/file-upload-success',
			);
		}

		if ( empty( $uploads['basedir'] ) || ! is_writable( $uploads['basedir'] ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Uploads directory is not writable. Media uploads may fail.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/file-upload-success',
				'meta'         => array(
					'uploads_dir' => $uploads['basedir'],
				),
			);
		}

		return null;
	}
}