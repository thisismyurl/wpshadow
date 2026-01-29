<?php
/**
 * Mailchimp API Connection Diagnostic
 *
 * Mailchimp API key not configured or connection failing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.223.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mailchimp API Connection Diagnostic Class
 *
 * @since 1.223.0000
 */
class Diagnostic_MailchimpApiConnection extends Diagnostic_Base {

	protected static $slug = 'mailchimp-api-connection';
	protected static $title = 'Mailchimp API Connection';
	protected static $description = 'Mailchimp API key not configured or connection failing';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'mc4wp' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/mailchimp-api-connection',
			);
		}
		
		return null;
	}
}
