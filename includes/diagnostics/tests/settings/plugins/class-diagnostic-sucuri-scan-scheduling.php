<?php
/**
 * Sucuri Scan Scheduling Diagnostic
 *
 * Sucuri Scan Scheduling misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.851.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sucuri Scan Scheduling Diagnostic Class
 *
 * @since 1.851.0000
 */
class Diagnostic_SucuriScanScheduling extends Diagnostic_Base {

	protected static $slug = 'sucuri-scan-scheduling';
	protected static $title = 'Sucuri Scan Scheduling';
	protected static $description = 'Sucuri Scan Scheduling misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'SUCURISCAN_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/sucuri-scan-scheduling',
			);
		}
		
		return null;
	}
}
