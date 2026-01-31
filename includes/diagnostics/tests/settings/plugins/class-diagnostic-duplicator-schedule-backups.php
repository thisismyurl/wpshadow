<?php
/**
 * Duplicator Schedule Backups Diagnostic
 *
 * Duplicator scheduled backups misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.396.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Duplicator Schedule Backups Diagnostic Class
 *
 * @since 1.396.0000
 */
class Diagnostic_DuplicatorScheduleBackups extends Diagnostic_Base {

	protected static $slug = 'duplicator-schedule-backups';
	protected static $title = 'Duplicator Schedule Backups';
	protected static $description = 'Duplicator scheduled backups misconfigured';
	protected static $family = 'functionality';

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
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/duplicator-schedule-backups',
			);
		}
		
		return null;
	}
}
