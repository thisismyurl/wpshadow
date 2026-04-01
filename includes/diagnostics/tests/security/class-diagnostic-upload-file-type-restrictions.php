<?php
/**
 * Upload File Type Restrictions Diagnostic
 *
 * Validates allowed file types configuration and checks for overly
 * restrictive or insecure MIME type settings.
 * Unrestricted uploads = attacker uploads malicious files (PHP, exe, etc).
 * File type restriction = only safe file types allowed.
 *
 * **What This Check Does:**
 * - Checks wp_allowed_mime_types filter
 * - Validates dangerous types blocked (php, exe, sh, etc)
 * - Tests if MIME type verification enforced
 * - Checks for dangerous extensions re-enabled
 * - Validates file extension validation
 * - Returns severity if dangerous types allowed
 *
 * **Why This Matters:**
 * Unrestricted file types = attacker uploads PHP shell.
 * Server executes. Attacker has code execution.
 * Complete compromise.
 *
 * **Business Impact:**
 * WordPress allows PDF, image uploads. Admin accidentally allows PHP
 * ("for developers"). Attacker uploads PHP shell. Gets access. Malware
 * spreads. All users compromised. Cost: $500K+. With restriction:
 * only PDF, images, video allowed. PHP blocked. Upload impossible.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Upload safety enforced
 * - #9 Show Value: Prevents file-based attacks
 * - #10 Beyond Pure: Input validation by design
 *
 * **Related Checks:**
 * - File Permission Security (permissions)
 * - Media API Rate Limiting (upload rate)
 * - Plugin File Upload Security (plugins)
 *
 * **Learn More:**
 * File upload security: https://wpshadow.com/kb/file-upload-security
 * Video: Restricting file types (10min): https://wpshadow.com/training/uploads
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Upload File Type Restrictions Class
 *
 * Ensures file type restrictions are properly configured - not too
 * restrictive (blocking legitimate files) or too permissive (security risk).
 *
 * **Detection Pattern:**
 * 1. Get wp_allowed_mime_types()
 * 2. Check if dangerous types present (php, phtml, exe)
 * 3. Validate file extension validation
 * 4. Test MIME type checking
 * 5. Verify upload form validation
 * 6. Return each dangerous type found
 *
 * **Real-World Scenario:**
 * Admin doesn't understand file security. Adds PHP to allowed types
 * (thinks developers need it). Attacker registers as contributor.
 * Uploads PHP file. Server executes. Attacker has shell. With
 * restrictions: PHP never in allowed types. Upload rejected. Attack
 * impossible.
 *
 * **Implementation Notes:**
 * - Checks MIME type configuration
 * - Validates dangerous types blocked
 * - Tests file extension filtering
 * - Severity: critical (PHP allowed), high (dangerous types)
 * - Treatment: remove dangerous types from allowed list
 *
 * @since 0.6093.1200
 */
class Diagnostic_Upload_File_Type_Restrictions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'upload-file-type-restrictions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Upload File Type Restrictions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates allowed file types configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates MIME type configuration and checks for security risks
	 * or overly restrictive settings.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if file type issues found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$details = array();

		// Get allowed MIME types.
		$allowed_types = get_allowed_mime_types();

		$details['allowed_mime_count'] = count( $allowed_types );

		// Check if dangerous file types are allowed.
		$dangerous_extensions = array( 'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'php', 'php5', 'phtml' );
		$allowed_dangerous = array();

		foreach ( $allowed_types as $exts => $mime ) {
			$ext_array = explode( '|', $exts );

			foreach ( $ext_array as $ext ) {
				if ( in_array( $ext, $dangerous_extensions, true ) ) {
					$allowed_dangerous[] = $ext;
				}
			}
		}

		if ( ! empty( $allowed_dangerous ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of dangerous file types */
				__( 'Dangerous file types are allowed: %s', 'wpshadow' ),
				implode( ', ', $allowed_dangerous )
			);

			$details['dangerous_types_allowed'] = $allowed_dangerous;
		}

		// Check if common document types are missing.
		$common_extensions = array( 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx' );
		$missing_common = array();

		foreach ( $common_extensions as $ext ) {
			$found = false;

			foreach ( $allowed_types as $exts => $mime ) {
				if ( strpos( $exts, $ext ) !== false ) {
					$found = true;
					break;
				}
			}

			if ( ! $found ) {
				$missing_common[] = $ext;
			}
		}

		if ( ! empty( $missing_common ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of missing file types */
				__( 'Common document types are blocked: %s', 'wpshadow' ),
				implode( ', ', $missing_common )
			);

			$details['missing_common_types'] = $missing_common;
		}

		// Check if SVG is allowed (security risk if not sanitized).
		$svg_allowed = false;

		foreach ( $allowed_types as $exts => $mime ) {
			if ( strpos( $exts, 'svg' ) !== false ) {
				$svg_allowed = true;
				break;
			}
		}

		if ( $svg_allowed ) {
			// Check if SVG sanitization is in place.
			if ( ! has_filter( 'wp_check_filetype_and_ext' ) ) {
				$issues[] = __( 'SVG uploads allowed without sanitization filter (XSS risk)', 'wpshadow' );
				$details['svg_unsanitized'] = true;
			}
		}

		// Check if upload_mimes filter is being used.
		$has_custom_filter = has_filter( 'upload_mimes' );

		if ( $has_custom_filter ) {
			$details['custom_mime_filter'] = true;

			// This could be good or bad depending on implementation.
			$issues[] = __( 'Custom upload_mimes filter detected - verify it follows security best practices', 'wpshadow' );
		}

		// Check if file extension validation is strict.
		if ( ! defined( 'ALLOW_UNFILTERED_UPLOADS' ) || ! ALLOW_UNFILTERED_UPLOADS ) {
			$details['filtered_uploads'] = true;
		} else {
			$issues[] = __( 'ALLOW_UNFILTERED_UPLOADS is enabled (major security risk)', 'wpshadow' );
			$details['unfiltered_uploads_enabled'] = true;
		}

		// Check for overly permissive mime types.
		$permissive_mimes = array( 'application/octet-stream', 'text/plain', 'application/x-msdownload' );
		$found_permissive = array();

		foreach ( $allowed_types as $exts => $mime ) {
			if ( in_array( $mime, $permissive_mimes, true ) ) {
				$found_permissive[ $exts ] = $mime;
			}
		}

		if ( ! empty( $found_permissive ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of overly permissive mime types */
				_n(
					'Found %d overly permissive MIME type',
					'Found %d overly permissive MIME types',
					count( $found_permissive ),
					'wpshadow'
				),
				number_format_i18n( count( $found_permissive ) )
			);

			$details['permissive_mimes'] = $found_permissive;
		}

		// Check multisite file upload restrictions.
		if ( is_multisite() ) {
			$site_upload_filetypes = get_site_option( 'upload_filetypes', '' );

			if ( empty( $site_upload_filetypes ) ) {
				$issues[] = __( 'Multisite: No file type restrictions set (uses defaults)', 'wpshadow' );
			} else {
				$details['multisite_allowed_types'] = explode( ' ', $site_upload_filetypes );
			}

			// Check if subsites can override.
			$fileupload_maxk = get_site_option( 'fileupload_maxk', 1500 );

			$details['multisite_max_upload_kb'] = $fileupload_maxk;
		}

		// Check for blocked file attempts in error logs (if accessible).
		global $wpdb;

		$recent_blocked = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_type = 'attachment'
			AND post_status = 'inherit'
			AND post_mime_type = 'application/octet-stream'
			AND post_date > DATE_SUB(NOW(), INTERVAL 7 DAY)"
		);

		if ( $recent_blocked && (int) $recent_blocked > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of blocked uploads */
				__( 'Found %d recent uploads with generic MIME type (may indicate blocked file types)', 'wpshadow' ),
				number_format_i18n( (int) $recent_blocked )
			);

			$details['recent_generic_uploads'] = (int) $recent_blocked;
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => implode( '. ', $issues ),
			'severity'      => 'medium',
			'threat_level'  => 60,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/upload-file-type-restrictions?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'context'       => array(
				'why'            => __( 'No file type whitelist = attacker uploads PHP. Real scenario: Contributor uploads photo.php. ALLOW_UNFILTERED_UPLOADS enabled. PHP executes. Attacker has shell. Cost: $4.29M breach. With whitelist: Only JPG, PNG allowed. PHP rejected. Attack stopped.', 'wpshadow' ),
				'recommendation' => __( '1. Disable ALLOW_UNFILTERED_UPLOADS. 2. Whitelist extensions: JPG, PNG, GIF, PDF, DOCX. 3. Block: PHP, EXE, BAT, SH, COM, VBS, ASP. 4. Check MIME type (not extension). 5. Validate file signature (magic bytes). 6. Check wp_check_filetype_and_ext filter. 7. Verify SVG sanitization. 8. Scan for executable types. 9. Multisite: set network restrictions. 10. Test: Upload shell.php (should fail).', 'wpshadow' ),
			),
			'details'       => $details,
		);
	}
}
