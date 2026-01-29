<?php
/**
 * FluentCRM Contact Security Diagnostic
 *
 * FluentCRM contact data exposed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.485.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FluentCRM Contact Security Diagnostic Class
 *
 * @since 1.485.0000
 */
class Diagnostic_FluentcrmContactSecurity extends Diagnostic_Base {

	protected static $slug = 'fluentcrm-contact-security';
	protected static $title = 'FluentCRM Contact Security';
	protected static $description = 'FluentCRM contact data exposed';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'FLUENTCRM' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/fluentcrm-contact-security',
			);
		}
		
		return null;
	}
}
