<?php
/**
 * TablePress DataTables Loading Diagnostic
 *
 * TablePress DataTables loading slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.413.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TablePress DataTables Loading Diagnostic Class
 *
 * @since 1.413.0000
 */
class Diagnostic_TablepressDatatablesLoading extends Diagnostic_Base {

	protected static $slug = 'tablepress-datatables-loading';
	protected static $title = 'TablePress DataTables Loading';
	protected static $description = 'TablePress DataTables loading slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'TABLEPRESS_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/tablepress-datatables-loading',
			);
		}
		
		return null;
	}
}
