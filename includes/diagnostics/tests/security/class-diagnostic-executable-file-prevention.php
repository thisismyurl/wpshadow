<?php
/**
 * Executable File Prevention Diagnostic
 *
 * Checks prevention of executable uploads.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Executable_File_Prevention Class
 *
 * Detects risky upload settings and potentially executable uploaded files.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Executable_File_Prevention extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'executable-file-prevention';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Executable File Prevention';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates prevention of executable file uploads';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		if ( defined( 'ALLOW_UNFILTERED_UPLOADS' ) && ALLOW_UNFILTERED_UPLOADS ) {
			$issues[] = __( 'ALLOW_UNFILTERED_UPLOADS is enabled, which can allow risky file uploads.', 'wpshadow' );
		}

		$allowed      = get_allowed_mime_types();
		$dangerous_re = '/(^|\|)(php|phtml|phar|exe|sh|bat|cmd|com|cgi|pl|py|js|jar|asp|aspx)(\||$)/i';

		foreach ( $allowed as $extensions => $mime ) {
			if ( preg_match( $dangerous_re, (string) $extensions ) ) {
				$issues[] = sprintf(
					/* translators: %s: extension pattern */
					__( 'Potentially executable extension pattern is allowed: %s', 'wpshadow' ),
					$extensions
				);
				break;
			}
		}

		global $wpdb;
		$exec_files = (int) $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_wp_attached_file'
			AND (
				meta_value REGEXP '\\.(php|phtml|phar|exe|sh|bat|cmd|com|cgi|pl|py|js|jar|asp|aspx)$'
			)"
		);

		if ( 0 < $exec_files ) {
			$issues[] = sprintf(
				/* translators: %d: file count */
				_n( '%d potentially executable uploaded file found.', '%d potentially executable uploaded files found.', $exec_files, 'wpshadow' ),
				$exec_files
			);
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: issue count */
				_n( '%d executable upload issue detected.', '%d executable upload issues detected.', count( $issues ), 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'high',
			'threat_level' => 85,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/executable-file-prevention',
			'details'      => array(
				'issues'     => $issues,
				'exec_files' => $exec_files,
			),
		);

		return Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'file-upload', 'executable-prevention' );
	}
}
