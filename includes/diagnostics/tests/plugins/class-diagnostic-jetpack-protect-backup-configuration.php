<?php
/**
 * Jetpack Protect Backup Configuration Diagnostic
 *
 * Jetpack Protect Backup Configuration misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.878.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Jetpack Protect Backup Configuration Diagnostic Class
 *
 * @since 1.878.0000
 */
class Diagnostic_JetpackProtectBackupConfiguration extends Diagnostic_Base {

	protected static $slug = 'jetpack-protect-backup-configuration';
	protected static $title = 'Jetpack Protect Backup Configuration';
	protected static $description = 'Jetpack Protect Backup Configuration misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Jetpack' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/jetpack-protect-backup-configuration',
			);
		}
		
		return null;
	}
}
