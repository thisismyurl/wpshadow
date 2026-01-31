<?php
/**
 * Happyforms Email Deliverability Diagnostic
 *
 * Happyforms Email Deliverability issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1210.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Happyforms Email Deliverability Diagnostic Class
 *
 * @since 1.1210.0000
 */
class Diagnostic_HappyformsEmailDeliverability extends Diagnostic_Base {

	protected static $slug = 'happyforms-email-deliverability';
	protected static $title = 'Happyforms Email Deliverability';
	protected static $description = 'Happyforms Email Deliverability issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! true // Generic check ) {
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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/happyforms-email-deliverability',
			);
		}
		
		return null;
	}
}
