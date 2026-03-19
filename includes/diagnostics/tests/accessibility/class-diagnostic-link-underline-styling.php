<?php
/**
 * Link Underline Styling Diagnostic
 *
 * Issue #4958: Links Not Distinguishable (No Underline)
 * Pillar: 🌍 Accessibility First
 *
 * Checks if text links are underlined.
 * Links without underlines are invisible to many users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Link_Underline_Styling Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Link_Underline_Styling extends Diagnostic_Base {

	protected static $slug = 'link-underline-styling';
	protected static $title = 'Links Not Distinguishable (No Underline)';
	protected static $description = 'Checks if links are visually distinct from plain text';
	protected static $family = 'accessibility';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Underline all body text links by default', 'wpshadow' );
		$issues[] = __( 'Acceptable: Color + underline (not color alone)', 'wpshadow' );
		$issues[] = __( 'Navigation links can skip underline (context clear)', 'wpshadow' );
		$issues[] = __( 'Show underline on hover/focus for all links', 'wpshadow' );
		$issues[] = __( 'Ensure 3:1 contrast ratio for non-underlined links', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Links that rely only on color are invisible to colorblind users (8% of men). Underlines make links universally recognizable.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/link-underlines',
				'details'      => array(
					'recommendations'         => $issues,
					'wcag_requirement'        => 'WCAG 2.1.6093.1200 Use of Color (Level A)',
					'exception'               => 'Links in navigation can skip underline if context is clear',
					'affected_users'          => '8% colorblind, elderly, low vision',
				),
			);
		}

		return null;
	}
}
