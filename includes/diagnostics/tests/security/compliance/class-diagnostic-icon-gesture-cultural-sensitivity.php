<?php
/**
 * Icon Gesture Cultural Sensitivity Diagnostic
 *
 * Issue #4927: Icons Use Culturally Offensive Gestures
 * Pillar: 🌐 Culturally Respectful
 *
 * Checks if icons avoid culturally sensitive gestures.
 * Thumbs up, OK sign, peace sign are offensive in some cultures.
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
 * Diagnostic_Icon_Gesture_Cultural_Sensitivity Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Icon_Gesture_Cultural_Sensitivity extends Diagnostic_Base {

	protected static $slug = 'icon-gesture-cultural-sensitivity';
	protected static $title = 'Icons Use Culturally Offensive Gestures';
	protected static $description = 'Checks if UI icons avoid culturally sensitive hand gestures';
	protected static $family = 'compliance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Avoid thumbs up icon (offensive in Middle East, West Africa)', 'wpshadow' );
		$issues[] = __( 'Avoid OK hand gesture 👌 (offensive in Brazil, Turkey)', 'wpshadow' );
		$issues[] = __( 'Avoid peace sign reversed (offensive in UK, Australia)', 'wpshadow' );
		$issues[] = __( 'Avoid pointing finger (rude in many Asian cultures)', 'wpshadow' );
		$issues[] = __( 'Use neutral icons: checkmark ✓, star ⭐, heart ❤️', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Hand gesture icons that seem friendly in one culture can be offensive in another. Use culturally neutral symbols instead.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/cultural-icons',
				'details'      => array(
					'recommendations'         => $issues,
					'offensive_gestures'      => 'Thumbs up (Middle East), OK (Brazil), Peace backwards (UK)',
					'safe_alternatives'       => 'Checkmark, star, heart, plus, arrow',
					'icon_libraries'          => 'Font Awesome, Material Icons (avoid hand gestures)',
				),
			);
		}

		return null;
	}
}
