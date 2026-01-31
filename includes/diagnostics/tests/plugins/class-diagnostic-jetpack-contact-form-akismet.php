<?php
/**
 * Jetpack Contact Form Akismet Diagnostic
 *
 * Jetpack Contact Form Akismet issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1220.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Jetpack Contact Form Akismet Diagnostic Class
 *
 * @since 1.1220.0000
 */
class Diagnostic_JetpackContactFormAkismet extends Diagnostic_Base {

	protected static $slug = 'jetpack-contact-form-akismet';
	protected static $title = 'Jetpack Contact Form Akismet';
	protected static $description = 'Jetpack Contact Form Akismet issue found';
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
				'kb_link'     => 'https://wpshadow.com/kb/jetpack-contact-form-akismet',
			);
		}
		
		return null;
	}
}
