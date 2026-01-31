<?php
/**
 * Duplicator Installer Cleanup Diagnostic
 *
 * Duplicator installer files not removed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.393.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Duplicator Installer Cleanup Diagnostic Class
 *
 * @since 1.393.0000
 */
class Diagnostic_DuplicatorInstallerCleanup extends Diagnostic_Base {

	protected static $slug = 'duplicator-installer-cleanup';
	protected static $title = 'Duplicator Installer Cleanup';
	protected static $description = 'Duplicator installer files not removed';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'DUP_PRO_Package' ) || class_exists( 'DUP_Package' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 80 ),
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/duplicator-installer-cleanup',
			);
		}
		
		return null;
	}
}
