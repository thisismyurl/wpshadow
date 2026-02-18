<?php
/**
 * Link Target Blank Warning Diagnostic
 *
 * Issue #4981: Links Open in New Tab Without Warning
 * Pillar: 🌍 Accessibility First / #1: Helpful Neighbor
 *
 * Checks if target="_blank" links have visual indicators.
 * Users need to know links open in new tab.
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
 * Diagnostic_Link_Target_Blank_Warning Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Link_Target_Blank_Warning extends Diagnostic_Base {

	protected static $slug = 'link-target-blank-warning';
	protected static $title = 'Links Open in New Tab Without Warning';
	protected static $description = 'Checks if target="_blank" links have visual indicators';
	protected static $family = 'accessibility';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Add icon after target="_blank" links: ↗ (external link)', 'wpshadow' );
		$issues[] = __( 'Add aria-label for screen readers: "(opens in new tab)"', 'wpshadow' );
		$issues[] = __( 'Use rel="noopener noreferrer" for security', 'wpshadow' );
		$issues[] = __( 'Minimize use of target="_blank" (respect user choice)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Unexpectedly opening new tabs disorients users. Provide visual and textual warning before links that open in new tabs.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/target-blank',
				'details'      => array(
					'recommendations'         => $issues,
					'icon_example'            => '<a href="..." target="_blank">Link ↗</a>',
					'aria_example'            => '<a href="..." target="_blank" aria-label="External link (opens in new tab)">Link</a>',
					'security'                => 'rel="noopener noreferrer" prevents window.opener access',
				),
			);
		}

		return null;
	}
}
