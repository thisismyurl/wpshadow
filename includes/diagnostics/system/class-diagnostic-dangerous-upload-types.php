<?php
declare(strict_types=1);
/**
 * Dangerous File Upload Types Diagnostic
 *
 * Philosophy: File security - validate upload file types
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for dangerous file upload types allowed.
 */
class Diagnostic_Dangerous_Upload_Types extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$allowed_types = get_allowed_mime_types();
		
		$dangerous = array( 'php', 'phtml', 'php5', 'php7', 'phar', 'exe', 'sh', 'bat', 'cmd' );
		$found_dangerous = array();
		
		foreach ( $allowed_types as $ext => $mime ) {
			foreach ( $dangerous as $bad_ext ) {
				if ( preg_match( '/' . preg_quote( $bad_ext, '/' ) . '/i', $ext ) ) {
					$found_dangerous[] = $ext;
				}
			}
		}
		
		if ( ! empty( $found_dangerous ) ) {
			return array(
				'id'          => 'dangerous-upload-types',
				'title'       => 'Dangerous File Types Allowed for Upload',
				'description' => sprintf(
					'Dangerous file types allowed: %s. Uploading executable files allows code execution. Restrict uploads to safe types (jpg, png, pdf, doc).',
					implode( ', ', array_slice( $found_dangerous, 0, 3 ) )
				),
				'severity'    => 'critical',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/restrict-upload-file-types/',
				'training_link' => 'https://wpshadow.com/training/upload-security/',
				'auto_fixable' => false,
				'threat_level' => 90,
			);
		}
		
		return null;
	}
}
