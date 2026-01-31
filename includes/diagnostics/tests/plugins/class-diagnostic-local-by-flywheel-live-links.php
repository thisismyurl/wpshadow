<?php
/**
 * Local By Flywheel Live Links Diagnostic
 *
 * Local By Flywheel Live Links issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1068.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Local By Flywheel Live Links Diagnostic Class
 *
 * @since 1.1068.0000
 */
class Diagnostic_LocalByFlywheelLiveLinks extends Diagnostic_Base {

	protected static $slug = 'local-by-flywheel-live-links';
	protected static $title = 'Local By Flywheel Live Links';
	protected static $description = 'Local By Flywheel Live Links issue detected';
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
				'kb_link'     => 'https://wpshadow.com/kb/local-by-flywheel-live-links',
			);
		}
		
		return null;
	}
}
