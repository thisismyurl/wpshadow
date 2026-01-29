<?php
/**
 * Directory Email Alerts Diagnostic
 *
 * Directory email alerts excessive.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.567.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Directory Email Alerts Diagnostic Class
 *
 * @since 1.567.0000
 */
class Diagnostic_DirectoryEmailAlerts extends Diagnostic_Base {

	protected static $slug = 'directory-email-alerts';
	protected static $title = 'Directory Email Alerts';
	protected static $description = 'Directory email alerts excessive';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'wpbdp' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/directory-email-alerts',
			);
		}
		
		return null;
	}
}
