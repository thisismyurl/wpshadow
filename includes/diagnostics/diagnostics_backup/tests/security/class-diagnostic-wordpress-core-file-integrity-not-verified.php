<?php
/**
 * WordPress Core File Integrity Not Verified Diagnostic
 *
 * Checks if WordPress core files have been modified.
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
 * WordPress Core File Integrity Not Verified Diagnostic Class
 *
 * Detects modified WordPress core files.
 *
 * @since 1.2601.2310
 */
class Diagnostic_WordPress_Core_File_Integrity_Not_Verified extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wordpress-core-file-integrity-not-verified';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Core File Integrity Not Verified';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if WordPress core files are intact';

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
		// Check for file integrity checking plugins
		$integrity_plugins = array(
			'wordfence/wordfence.php',
			'sucuri-scanner/sucuri.php',
			'iThemes-Security-Pro/iThemes-Security-Pro.php',
		);

		$integrity_active = false;
		foreach ( $integrity_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$integrity_active = true;
				break;
			}
		}

		if ( ! $integrity_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No file integrity monitoring is active. Modified WordPress core files indicate a compromise.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 80,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/wordpress-core-file-integrity-not-verified',
			);
		}

		return null;
	}
}
