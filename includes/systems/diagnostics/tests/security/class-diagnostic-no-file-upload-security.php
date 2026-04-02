<?php
/**
 * No File Upload Security Diagnostic
 *
 * Detects when file upload security is not properly configured,
 * allowing malicious file uploads.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No File Upload Security
 *
 * Checks whether file upload security is
 * properly configured and validated.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_File_Upload_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-file-upload-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'File Upload Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether file uploads are secured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check uploads directory for execution protection
		$upload_dir = wp_upload_dir();
		$htaccess_path = $upload_dir['basedir'] . '/.htaccess';

		$has_protection = false;
		if ( file_exists( $htaccess_path ) ) {
			$htaccess = file_get_contents( $htaccess_path );
			// Check for PHP execution prevention
			if ( strpos( $htaccess, 'php_flag engine off' ) !== false || 
			     strpos( $htaccess, 'RemoveHandler' ) !== false ) {
				$has_protection = true;
			}
		}

		if ( ! $has_protection ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'File upload security isn\'t configured, which allows malicious uploads. Attack: attacker uploads PHP shell disguised as image, executes it = full site access. Protection: disable PHP execution in uploads directory (via .htaccess), validate file types (MIME and extension), rename uploaded files, scan for malware. WordPress validates uploads, but this adds defense in depth. Add to /wp-content/uploads/.htaccess: php_flag engine off. Critical for sites allowing user uploads.',
					'wpshadow'
				),
				'severity'      => 'critical',
				'threat_level'  => 85,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Malicious Upload Prevention',
					'potential_gain' => 'Block PHP shell uploads and remote code execution',
					'roi_explanation' => 'File upload security prevents attackers from uploading malicious code that executes on your server.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/file-upload-security',
			);
		}

		return null;
	}
}
