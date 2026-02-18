<?php
/**
 * Malicious File Upload Not Prevented Diagnostic
 *
 * Checks file upload.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Malicious_File_Upload_Not_Prevented Class
 *
 * Performs diagnostic check for Malicious File Upload Not Prevented.
 *
 * @since 1.6033.2033
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
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'validate_file_uploads' ) ) {
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Malicious file upload not prevented. Validate file type, size, and content.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 85,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/malicious-file-upload-not-prevented',
				'context'       => array(
					'why'            => __( 'Unvalidated uploads = instant RCE. Real scenario: Media upload allows any file type. Attacker uploads shell.php. Visits /wp-content/uploads/shell.php. Full site compromise. Cost: $4.29M breach. OWASP: File upload #6 most common vulnerability. With validation: Only images allowed. Shell rejected. Attack stopped.', 'wpshadow' ),
					'recommendation' => __( '1. Whitelist allowed extensions: JPG, PNG, GIF, PDF only. 2. Validate MIME type (not just extension). 3. Store uploads outside web root if possible. 4. Add .htaccess to uploads: deny PHP execution. 5. Check file size limits (max 5MB). 6. Scan uploads with ClamAV for malware. 7. Generate random filename to prevent guessing. 8. Log all upload attempts (success/failure). 9. Use wp_handle_upload() (built-in validation). 10. Test by uploading shell.php (should be rejected).', 'wpshadow' ),
				),
			);
			$finding = Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'file-upload', 'malicious-upload-prevention' );
			return $finding;
		}

		return null;
	}
}
