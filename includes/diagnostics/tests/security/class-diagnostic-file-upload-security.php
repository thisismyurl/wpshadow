<?php
/**
 * File Upload Security Diagnostic
 *
 * Detects dangerous file upload configurations that allow execution
 * of uploaded files, enabling remote code execution attacks.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_File_Upload_Security Class
 *
 * Detects file upload vulnerabilities.
 *
 * @since 1.2601.2148
 */
class Diagnostic_File_Upload_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'file-upload-security';

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
	protected static $description = 'Detects dangerous file upload configurations';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if vulnerabilities found, null otherwise.
	 */
	public static function check() {
		$upload_check = self::analyze_upload_security();

		if ( empty( $upload_check['vulnerabilities'] ) ) {
			return null; // Upload security good
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of vulnerabilities */
				__( '%d file upload vulnerabilities detected. Attackers upload malicious PHP files for remote code execution. Complete site takeover possible.', 'wpshadow' ),
				count( $upload_check['vulnerabilities'] )
			),
			'severity'     => 'critical',
			'threat_level' => 90,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/upload-security',
			'family'       => self::$family,
			'meta'         => array(
				'vulnerabilities'   => $upload_check['vulnerabilities'],
				'uploads_directory' => $upload_check['uploads_path'],
			),
			'details'      => array(
				'upload_attack_scenarios'   => array(
					'PHP Shell Upload' => array(
						'Attacker uploads malicious.php',
						'Accesses https://site.com/wp-content/uploads/malicious.php',
						'Executes commands, steals database',
					),
					'Double Extension Bypass' => array(
						'Upload: image.php.jpg',
						'Server executes as PHP despite .jpg',
						'Bypasses extension filters',
					),
					'MIME Type Spoofing' => array(
						'PHP file with fake image MIME type',
						'Passes WordPress checks',
						'Executes when accessed',
					),
				),
				'securing_uploads_directory' => array(
					'Disable PHP Execution (.htaccess)' => array(
						'File: /wp-content/uploads/.htaccess',
						'Content:',
						'<Files *.php>',
						'  deny from all',
						'</Files>',
					),
					'Disable PHP Execution (Nginx)' => array(
						'File: /etc/nginx/sites-available/yoursite.conf',
						'Add:',
						'location ~* /wp-content/uploads/.*\\.php$ {',
						'  deny all;',
						'}',
					),
				),
				'file_type_restrictions'    => array(
					'WordPress Default Allowed' => array(
						'Images: jpg, jpeg, png, gif, webp',
						'Documents: pdf, doc, docx, ppt, pptx',
						'Media: mp3, mp4, mov, avi',
						'Archives: zip, tar, gz',
					),
					'Never Allow' => array(
						'Executables: php, exe, sh, bat',
						'Scripts: js, cgi, pl',
						'Config files: htaccess, ini',
					),
					'Restrict via Code' => array(
						'add_filter(\'upload_mimes\', function($mimes) {',
						'  unset($mimes[\'svg\']); // Remove SVG',
						'  return $mimes;',
						'});',
					),
				),
				'file_validation_best_practices' => array(
					__( 'Check file extension AND MIME type' ),
					__( 'Validate file content (not just extension)' ),
					__( 'Rename uploaded files to random names' ),
					__( 'Store outside web root if possible' ),
					__( 'Scan uploads with antivirus (ClamAV)' ),
				),
				'wordpress_upload_filters'  => array(
					'wp_check_filetype_and_ext' => 'Validates file type',
					'upload_mimes' => 'Filter allowed MIME types',
					'wp_handle_upload_prefilter' => 'Pre-upload processing',
					'wp_handle_upload' => 'Main upload handler',
				),
			),
		);
	}

	/**
	 * Analyze upload security.
	 *
	 * @since  1.2601.2148
	 * @return array Upload security analysis.
	 */
	private static function analyze_upload_security() {
		$vulnerabilities = array();
		$upload_dir = wp_upload_dir();
		$uploads_path = $upload_dir['basedir'];

		// Check if .htaccess exists in uploads directory
		$htaccess_file = $uploads_path . '/.htaccess';
		if ( ! file_exists( $htaccess_file ) ) {
			$vulnerabilities[] = __( 'No .htaccess in uploads directory (PHP execution not blocked)', 'wpshadow' );
		} else {
			// Check if .htaccess blocks PHP
			$htaccess_content = file_get_contents( $htaccess_file );
			if ( strpos( $htaccess_content, 'php' ) === false ) {
				$vulnerabilities[] = __( '.htaccess exists but doesn\'t block PHP execution', 'wpshadow' );
			}
		}

		// Check if uploads directory is writable by server
		if ( ! is_writable( $uploads_path ) ) {
			// Actually good for security but might break uploads
			// Not a vulnerability
		}

		return array(
			'vulnerabilities' => $vulnerabilities,
			'uploads_path'    => $uploads_path,
		);
	}
}
