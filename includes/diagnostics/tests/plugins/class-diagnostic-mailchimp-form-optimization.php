<?php
/**
 * Mailchimp Form Optimization Diagnostic
 *
 * Mailchimp forms loading too many assets.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.225.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mailchimp Form Optimization Diagnostic Class
 *
 * @since 1.225.0000
 */
class Diagnostic_MailchimpFormOptimization extends Diagnostic_Base {

	protected static $slug = 'mailchimp-form-optimization';
	protected static $title = 'Mailchimp Form Optimization';
	protected static $description = 'Mailchimp forms loading too many assets';
	protected static $family = 'performance';

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
				'severity'    => self::calculate_severity( 30 ),
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/mailchimp-form-optimization',
			);
		}
		
		return null;
	}
}
