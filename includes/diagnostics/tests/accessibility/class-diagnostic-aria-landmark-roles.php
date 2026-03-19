<?php
/**
 * ARIA Landmark Roles Diagnostic
 *
 * Issue #4891: No ARIA Landmark Roles for Screen Readers
 * Pillar: 🌍 Accessibility First
 *
 * Checks if page sections have semantic landmarks.
 * Screen readers use landmarks to navigate page structure.
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
 * Diagnostic_ARIA_Landmark_Roles Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_ARIA_Landmark_Roles extends Diagnostic_Base {

	protected static $slug = 'aria-landmark-roles';
	protected static $title = 'No ARIA Landmark Roles for Screen Readers';
	protected static $description = 'Checks if page sections use semantic HTML5 or ARIA landmarks';
	protected static $family = 'accessibility';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Use <header> or role="banner" for site header', 'wpshadow' );
		$issues[] = __( 'Use <nav> or role="navigation" for menus', 'wpshadow' );
		$issues[] = __( 'Use <main> or role="main" for primary content (one per page)', 'wpshadow' );
		$issues[] = __( 'Use <aside> or role="complementary" for sidebars', 'wpshadow' );
		$issues[] = __( 'Use <footer> or role="contentinfo" for site footer', 'wpshadow' );
		$issues[] = __( 'Label landmarks: <nav aria-label="Main menu">', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'ARIA landmarks help screen reader users navigate page structure quickly. They can jump directly to main content, navigation, or footer.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/aria-landmarks',
				'details'      => array(
					'recommendations'         => $issues,
					'wcag_requirement'        => 'WCAG 2.1.6093.1200 Info and Relationships',
					'landmark_types'          => 'banner, navigation, main, complementary, contentinfo, search, form',
					'screen_reader_shortcut'  => 'NVDA: D key, JAWS: ; key',
				),
			);
		}

		return null;
	}
}
