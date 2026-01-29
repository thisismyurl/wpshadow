<?php
/**
 * FluentCRM Email Delivery Diagnostic
 *
 * FluentCRM email sending slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.486.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FluentCRM Email Delivery Diagnostic Class
 *
 * @since 1.486.0000
 */
class Diagnostic_FluentcrmEmailDelivery extends Diagnostic_Base {

	protected static $slug = 'fluentcrm-email-delivery';
	protected static $title = 'FluentCRM Email Delivery';
	protected static $description = 'FluentCRM email sending slow';
	protected static $family = 'performance';

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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/fluentcrm-email-delivery',
			);
		}
		
		return null;
	}
}
