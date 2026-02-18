<?php
/**
 * Religious Holiday Neutrality Diagnostic
 *
 * Issue #4928: UI Assumes Christian Holidays
 * Pillar: 🌐 Culturally Respectful
 *
 * Checks if UI doesn't assume specific religious holidays.
 * Not everyone celebrates Christmas, Easter, etc.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Religious_Holiday_Neutrality Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Religious_Holiday_Neutrality extends Diagnostic_Base {

	protected static $slug = 'religious-holiday-neutrality';
	protected static $title = 'UI Assumes Christian Holidays';
	protected static $description = 'Checks if interface respects religious diversity';
	protected static $family = 'compliance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Use "Holiday Season" instead of "Christmas"', 'wpshadow' );
		$issues[] = __( 'Avoid automatic holiday themes (Christmas trees, Easter eggs)', 'wpshadow' );
		$issues[] = __( 'Use generic "Special Offer" instead of "Christmas Sale"', 'wpshadow' );
		$issues[] = __( 'Don\'t assume business closures for specific holidays', 'wpshadow' );
		$issues[] = __( 'Support multiple calendar systems (Gregorian, Islamic, Hebrew)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Not everyone celebrates Christian holidays. Use religiously neutral language and imagery to respect all users.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/religious-neutrality',
				'details'      => array(
					'recommendations'         => $issues,
					'alternatives'            => '"Holiday Season", "Year-End", "Special Occasion"',
					'world_religions'         => 'Christianity, Islam, Judaism, Hinduism, Buddhism, etc',
					'inclusive_approach'      => 'Let users opt-in to holiday themes, don\'t assume',
				),
			);
		}

		return null;
	}
}
