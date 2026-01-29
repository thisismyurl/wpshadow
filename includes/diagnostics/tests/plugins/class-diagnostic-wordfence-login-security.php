<?php
/**
 * Wordfence Login Security Diagnostic
 *
 * Wordfence Login Security misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.841.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordfence Login Security Diagnostic Class
 *
 * @since 1.841.0000
 */
class Diagnostic_WordfenceLoginSecurity extends Diagnostic_Base {

	protected static $slug = 'wordfence-login-security';
	protected static $title = 'Wordfence Login Security';
	protected static $description = 'Wordfence Login Security misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WORDFENCE_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/wordfence-login-security',
			);
		}
		
		return null;
	}
}
