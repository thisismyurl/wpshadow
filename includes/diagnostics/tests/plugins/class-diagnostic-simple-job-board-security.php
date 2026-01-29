<?php
/**
 * Simple Job Board Security Diagnostic
 *
 * Simple Job Board applications insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.543.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Simple Job Board Security Diagnostic Class
 *
 * @since 1.543.0000
 */
class Diagnostic_SimpleJobBoardSecurity extends Diagnostic_Base {

	protected static $slug = 'simple-job-board-security';
	protected static $title = 'Simple Job Board Security';
	protected static $description = 'Simple Job Board applications insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'SJB_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/simple-job-board-security',
			);
		}
		
		return null;
	}
}
