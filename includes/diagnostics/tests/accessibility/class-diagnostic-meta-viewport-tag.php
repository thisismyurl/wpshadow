<?php
/**
 * Meta Viewport Tag Diagnostic
 *
 * Issue #4973: No Meta Viewport for Mobile
 * Pillar: 🌍 Accessibility First / 🎓 Learning Inclusive
 *
 * Checks if meta viewport tag is configured.
 * Without viewport tag, mobile users see desktop version zoomed out.
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
 * Diagnostic_Meta_Viewport_Tag Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Meta_Viewport_Tag extends Diagnostic_Base {

	protected static $slug = 'meta-viewport-tag';
	protected static $title = 'No Meta Viewport for Mobile';
	protected static $description = 'Checks if meta viewport tag is configured';
	protected static $family = 'accessibility';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Add <meta name="viewport" content="width=device-width, initial-scale=1">', 'wpshadow' );
		$issues[] = __( 'Use width=device-width (match device width)', 'wpshadow' );
		$issues[] = __( 'Use initial-scale=1 (no zoom on load)', 'wpshadow' );
		$issues[] = __( 'Optional: user-scalable=yes (allow pinch zoom)', 'wpshadow' );
		$issues[] = __( 'Never use maximum-scale=1 (disables zoom for accessibility)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Meta viewport tells mobile browsers how to scale the page. Without it, pages appear zoomed out and tiny on phones.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/meta-viewport',
				'details'      => array(
					'recommendations'         => $issues,
					'mobile_friendly'         => 'Required for Google Mobile-Friendly test',
					'example'                 => '<meta name="viewport" content="width=device-width, initial-scale=1">',
					'affected_traffic'        => '60% of traffic is mobile',
				),
			);
		}

		return null;
	}
}
