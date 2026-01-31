<?php
/**
 * Wordpress Application Passwords Security Diagnostic
 *
 * Wordpress Application Passwords Security issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1251.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Application Passwords Security Diagnostic Class
 *
 * @since 1.1251.0000
 */
class Diagnostic_WordpressApplicationPasswordsSecurity extends Diagnostic_Base {

	protected static $slug = 'wordpress-application-passwords-security';
	protected static $title = 'Wordpress Application Passwords Security';
	protected static $description = 'Wordpress Application Passwords Security issue detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! true // WordPress core feature ) {
			return null;
		}
		
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
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wordpress-application-passwords-security',
			);
		}
		
		return null;
	}
}
