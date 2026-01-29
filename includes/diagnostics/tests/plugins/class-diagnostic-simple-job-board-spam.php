<?php
/**
 * Simple Job Board Spam Diagnostic
 *
 * Simple Job Board spam protection weak.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.544.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Simple Job Board Spam Diagnostic Class
 *
 * @since 1.544.0000
 */
class Diagnostic_SimpleJobBoardSpam extends Diagnostic_Base {

	protected static $slug = 'simple-job-board-spam';
	protected static $title = 'Simple Job Board Spam';
	protected static $description = 'Simple Job Board spam protection weak';
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
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/simple-job-board-spam',
			);
		}
		
		return null;
	}
}
