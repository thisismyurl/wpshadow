<?php
/**
 * File Upload Restrictions Not Enforced Diagnostic
 *
 * Checks if file upload security is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * File Upload Restrictions Not Enforced Diagnostic Class
 *
 * Detects missing file upload security.
 *
 * @since 1.2601.2310
 */
class Diagnostic_File_Upload_Restrictions_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'file-upload-restrictions-not-enforced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'File Upload Restrictions Not Enforced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if file upload restrictions are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if there are no file type restrictions
		$allowed_types = get_allowed_mime_types();

		if ( empty( $allowed_types ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No file upload type restrictions are configured. Users can upload executable files, creating security vulnerabilities.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 85,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/file-upload-restrictions-not-enforced',
			);
		}

		// Check for dangerous file types
		$dangerous_types = array( 'exe', 'php', 'sh', 'bat', 'com', 'scr' );
		foreach ( $dangerous_types as $type ) {
			foreach ( $allowed_types as $mime => $label ) {
				if ( strpos( strtolower( $label ), $type ) !== false || strpos( strtolower( $mime ), $type ) !== false ) {
					return array(
						'id'            => self::$slug,
						'title'         => self::$title,
						'description'   => sprintf(
							__( 'Dangerous file type ".%s" is allowed to upload. This is a critical security risk.', 'wpshadow' ),
							$type
						),
						'severity'      => 'critical',
						'threat_level'  => 95,
						'auto_fixable'  => false,
						'kb_link'       => 'https://wpshadow.com/kb/file-upload-restrictions-not-enforced',
					);
				}
			}
		}

		return null;
	}
}
