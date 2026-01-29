<?php
/**
 * Caldera Forms Spam Protection Diagnostic
 *
 * Caldera Forms spam protection disabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.471.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Caldera Forms Spam Protection Diagnostic Class
 *
 * @since 1.471.0000
 */
class Diagnostic_CalderaFormsSpamProtection extends Diagnostic_Base {

	protected static $slug = 'caldera-forms-spam-protection';
	protected static $title = 'Caldera Forms Spam Protection';
	protected static $description = 'Caldera Forms spam protection disabled';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Caldera_Forms' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/caldera-forms-spam-protection',
			);
		}
		
		return null;
	}
}
