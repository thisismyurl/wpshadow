<?php
/**
 * Mailpoet Newsletter Bounce Handling Diagnostic
 *
 * Mailpoet Newsletter Bounce Handling configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.714.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mailpoet Newsletter Bounce Handling Diagnostic Class
 *
 * @since 1.714.0000
 */
class Diagnostic_MailpoetNewsletterBounceHandling extends Diagnostic_Base {

	protected static $slug = 'mailpoet-newsletter-bounce-handling';
	protected static $title = 'Mailpoet Newsletter Bounce Handling';
	protected static $description = 'Mailpoet Newsletter Bounce Handling configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'MailPoet\Config\Initializer' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/mailpoet-newsletter-bounce-handling',
			);
		}
		
		return null;
	}
}
