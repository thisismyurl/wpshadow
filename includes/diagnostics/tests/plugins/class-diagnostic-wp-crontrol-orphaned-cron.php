<?php
/**
 * Wp Crontrol Orphaned Cron Diagnostic
 *
 * Wp Crontrol Orphaned Cron issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1045.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Crontrol Orphaned Cron Diagnostic Class
 *
 * @since 1.1045.0000
 */
class Diagnostic_WpCrontrolOrphanedCron extends Diagnostic_Base {

	protected static $slug = 'wp-crontrol-orphaned-cron';
	protected static $title = 'Wp Crontrol Orphaned Cron';
	protected static $description = 'Wp Crontrol Orphaned Cron issue detected';
	protected static $family = 'functionality';

	public static function check() {
		
		$issues = array();
		$configured = get_option('diagnostic_' . self::$slug, false);
		if (!$configured) {
			$issues[] = 'not configured';
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
				'kb_link'     => 'https://wpshadow.com/kb/wp-crontrol-orphaned-cron',
			);
		}
		
		return null;
	}
}
