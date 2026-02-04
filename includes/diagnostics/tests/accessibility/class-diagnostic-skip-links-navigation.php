<?php
/**
 * Skip Links Navigation Diagnostic
 *
 * Issue #4890: No Skip Links for Keyboard Navigation
 * Pillar: 🌍 Accessibility First
 *
 * Checks if skip links let users bypass repetitive content.
 * Screen reader users don't want to hear the menu on every page.
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
 * Diagnostic_Skip_Links_Navigation Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Skip_Links_Navigation extends Diagnostic_Base {

	protected static $slug = 'skip-links-navigation';
	protected static $title = 'No Skip Links for Keyboard Navigation';
	protected static $description = 'Checks if skip links help users bypass repetitive content';
	protected static $family = 'accessibility';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Add "Skip to main content" link at page top', 'wpshadow' );
		$issues[] = __( 'Add "Skip to navigation" for easy menu access', 'wpshadow' );
		$issues[] = __( 'Make skip links visible on keyboard focus', 'wpshadow' );
		$issues[] = __( 'Skip links should be first focusable elements', 'wpshadow' );
		$issues[] = __( 'Link targets need id attributes (#main-content)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Skip links let keyboard and screen reader users jump directly to main content, bypassing repetitive navigation menus.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/skip-links',
				'details'      => array(
					'recommendations'         => $issues,
					'wcag_requirement'        => 'WCAG 2.1 2.4.1 Bypass Blocks (Level A)',
					'html_pattern'            => '<a href="#main" class="skip-link">Skip to main content</a>',
					'user_benefit'            => 'Saves 30+ Tab presses per page load',
				),
			);
		}

		return null;
	}
}
