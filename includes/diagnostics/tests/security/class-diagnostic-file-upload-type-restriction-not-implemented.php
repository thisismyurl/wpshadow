<?php
/**
 * File Upload Type Restriction Not Implemented Diagnostic
 *
 * Checks if file upload types are restricted.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2320
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * File Upload Type Restriction Not Implemented Diagnostic Class
 *
 * Detects unrestricted file uploads.
 *
 * @since 1.2601.2320
 */
class Diagnostic_File_Upload_Type_Restriction_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'file-upload-type-restriction-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'File Upload Type Restriction Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if file uploads are restricted';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2320
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check allowed file types
		$mimes = get_allowed_mime_types();

		// If more than 30 mime types, might be too permissive
		if ( count( $mimes ) > 30 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( '%d file types are allowed for upload. Consider restricting to only necessary types (jpg, png, pdf, etc.) to prevent malicious uploads.', 'wpshadow' ),
					count( $mimes )
				),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/file-upload-type-restriction-not-implemented',
			);
		}

		return null;
	}
}
