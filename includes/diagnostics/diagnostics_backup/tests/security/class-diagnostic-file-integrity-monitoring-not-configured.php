<?php
/**
 * File Integrity Monitoring Not Configured Diagnostic
 *
 * Checks if file integrity monitoring is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * File Integrity Monitoring Not Configured Diagnostic Class
 *
 * Detects missing file integrity monitoring.
 *
 * @since 1.2601.2352
 */
class Diagnostic_File_Integrity_Monitoring_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'file-integrity-monitoring-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'File Integrity Monitoring Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if file integrity monitoring is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if file monitoring plugin is active
		if ( ! is_plugin_active( 'wordfence/wordfence.php' ) && ! is_plugin_active( 'sucuri-scanner/sucuri.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'File integrity monitoring is not configured. Set up file integrity monitoring to detect unauthorized changes and malware.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/file-integrity-monitoring-not-configured',
			);
		}

		return null;
	}
}
