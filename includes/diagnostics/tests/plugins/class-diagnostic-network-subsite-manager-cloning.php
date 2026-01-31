<?php
/**
 * Network Subsite Manager Cloning Diagnostic
 *
 * Network Subsite Manager Cloning misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.961.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Network Subsite Manager Cloning Diagnostic Class
 *
 * @since 1.961.0000
 */
class Diagnostic_NetworkSubsiteManagerCloning extends Diagnostic_Base {

	protected static $slug = 'network-subsite-manager-cloning';
	protected static $title = 'Network Subsite Manager Cloning';
	protected static $description = 'Network Subsite Manager Cloning misconfigured';
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
				'kb_link'     => 'https://wpshadow.com/kb/network-subsite-manager-cloning',
			);
		}
		
		return null;
	}
}
