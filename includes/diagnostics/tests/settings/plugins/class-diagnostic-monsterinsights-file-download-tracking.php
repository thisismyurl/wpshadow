<?php
/**
 * MonsterInsights File Download Tracking Diagnostic
 *
 * MonsterInsights file download tracking disabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.231.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MonsterInsights File Download Tracking Diagnostic Class
 *
 * @since 1.231.0000
 */
class Diagnostic_MonsterinsightsFileDownloadTracking extends Diagnostic_Base {

	protected static $slug = 'monsterinsights-file-download-tracking';
	protected static $title = 'MonsterInsights File Download Tracking';
	protected static $description = 'MonsterInsights file download tracking disabled';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'MONSTERINSIGHTS_VERSION' ) ) {
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
				'severity'    => 25,
				'threat_level' => 25,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/monsterinsights-file-download-tracking',
			);
		}
		
		return null;
	}
}
