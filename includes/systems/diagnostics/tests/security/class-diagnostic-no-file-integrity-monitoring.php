<?php
/**
 * No File Integrity Monitoring Diagnostic
 *
 * Detects when file integrity monitoring is not implemented,
 * missing detection of unauthorized file modifications.
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
 * Diagnostic: No File Integrity Monitoring
 *
 * Checks whether file integrity monitoring is in place
 * to detect unauthorized modifications.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_File_Integrity_Monitoring extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-file-integrity-monitoring';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No File Integrity Monitoring';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether file integrity monitoring is enabled';

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
		// Check for file monitoring plugins
		$has_file_monitoring = is_plugin_active( 'wordfence-security/wordfence.php' ) ||
			is_plugin_active( 'sucuri-scanner/sucuri.php' ) ||
			is_plugin_active( 'jetpack/jetpack.php' );

		if ( ! $has_file_monitoring ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not monitoring file integrity, which means you won\'t know if hackers modify your files. File integrity monitoring creates a "fingerprint" of all your WordPress files, then alerts you if anything changes unexpectedly. This catches: malware injection, backdoors, unauthorized code changes. Without it, you could have a compromised site for weeks without noticing.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Intrusion Detection',
					'potential_gain' => 'Detect compromises within hours',
					'roi_explanation' => 'File monitoring detects intrusions within hours instead of weeks, limiting damage and enabling faster response.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/file-integrity-monitoring',
			);
		}

		return null;
	}
}
