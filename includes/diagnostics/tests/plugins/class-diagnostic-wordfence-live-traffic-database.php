<?php
/**
 * Wordfence Live Traffic Database Diagnostic
 *
 * Wordfence Live Traffic Database misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.840.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordfence Live Traffic Database Diagnostic Class
 *
 * @since 1.840.0000
 */
class Diagnostic_WordfenceLiveTrafficDatabase extends Diagnostic_Base {

	protected static $slug = 'wordfence-live-traffic-database';
	protected static $title = 'Wordfence Live Traffic Database';
	protected static $description = 'Wordfence Live Traffic Database misconfiguration';
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
				'kb_link'     => 'https://wpshadow.com/kb/wordfence-live-traffic-database',
			);
		}
		
		return null;
	}
}
