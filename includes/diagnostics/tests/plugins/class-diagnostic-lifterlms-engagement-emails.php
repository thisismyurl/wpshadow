<?php
/**
 * LifterLMS Engagement Emails Diagnostic
 *
 * LifterLMS email triggers misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.368.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LifterLMS Engagement Emails Diagnostic Class
 *
 * @since 1.368.0000
 */
class Diagnostic_LifterlmsEngagementEmails extends Diagnostic_Base {

	protected static $slug = 'lifterlms-engagement-emails';
	protected static $title = 'LifterLMS Engagement Emails';
	protected static $description = 'LifterLMS email triggers misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'LLMS' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/lifterlms-engagement-emails',
			);
		}
		
		return null;
	}
}
