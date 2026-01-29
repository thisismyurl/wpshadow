<?php
/**
 * Multisite Activity Monitoring Diagnostic
 *
 * Multisite Activity Monitoring misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.977.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Activity Monitoring Diagnostic Class
 *
 * @since 1.977.0000
 */
class Diagnostic_MultisiteActivityMonitoring extends Diagnostic_Base {

	protected static $slug = 'multisite-activity-monitoring';
	protected static $title = 'Multisite Activity Monitoring';
	protected static $description = 'Multisite Activity Monitoring misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/multisite-activity-monitoring',
			);
		}
		
		return null;
	}
}
