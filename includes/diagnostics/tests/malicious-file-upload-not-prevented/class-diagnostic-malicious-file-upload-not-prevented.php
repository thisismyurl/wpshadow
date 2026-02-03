<?php
/**
 * Malicious File Upload Not Prevented Diagnostic
 *
 * Checks file upload.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Malicious_File_Upload_Not_Prevented Class
 *
 * Performs diagnostic check for Malicious File Upload Not Prevented.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Malicious_File_Upload_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'malicious-file-upload-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Malicious File Upload Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks file upload';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'validate_file_uploads' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Malicious file upload not prevented. Validate file type,
						'severity'   =>   'high',
						'threat_level'   =>   85,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/malicious-file-upload-not-prevented'
						);
						);,
						);
						}
						return null;
						}
						return null;
						}
						return null;
	}
}
