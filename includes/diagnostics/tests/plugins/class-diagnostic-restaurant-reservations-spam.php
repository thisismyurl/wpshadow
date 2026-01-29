<?php
/**
 * Restaurant Reservations Spam Diagnostic
 *
 * Restaurant spam reservations accumulating.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.599.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restaurant Reservations Spam Diagnostic Class
 *
 * @since 1.599.0000
 */
class Diagnostic_RestaurantReservationsSpam extends Diagnostic_Base {

	protected static $slug = 'restaurant-reservations-spam';
	protected static $title = 'Restaurant Reservations Spam';
	protected static $description = 'Restaurant spam reservations accumulating';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'rtbInit' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/restaurant-reservations-spam',
			);
		}
		
		return null;
	}
}
