<?php
/**
 * No Automatic Security Patching Diagnostic
 *
 * Detects when security patches are not applied automatically,
 * leaving known vulnerabilities unpatched.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Automatic Security Patching
 *
 * Checks whether automatic security updates
 * are enabled for WordPress, plugins, and themes.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Automatic_Security_Patching extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-automatic-security-patching';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Automatic Security Patching';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether auto-updates are enabled';

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
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if automatic updates are enabled
		$auto_updates_enabled = (
			defined( 'WP_AUTO_UPDATE_CORE' ) && WP_AUTO_UPDATE_CORE === true
		) || (
			defined( 'AUTOMATIC_UPDATER_DISABLED' ) && AUTOMATIC_UPDATER_DISABLED === false
		);

		if ( ! $auto_updates_enabled ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Automatic security patching is disabled, which means you\'re running unpatched vulnerabilities. Each day you delay patches, more attackers know about the vulnerability. WordPress 0-days are actively exploited within hours. Even minor version updates often contain critical security fixes. Enable auto-updates in wp-config.php (define( \'WP_AUTO_UPDATE_CORE\', true )) or use hosting with auto-updates built-in.',
					'wpshadow'
				),
				'severity'      => 'critical',
				'threat_level'  => 90,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Vulnerability Exposure Window',
					'potential_gain' => 'Close vulnerability gaps within hours, not days',
					'roi_explanation' => 'Automatic patches eliminate delays between vulnerability disclosure and patching.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/automatic-security-patching?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
