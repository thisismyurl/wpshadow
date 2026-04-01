<?php
/**
 * Mobile Hover-Dependent Functionality Detection
 *
 * Detects interactive features that require hover and are inaccessible on touch.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Touch
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Hover-Dependent Functionality Detection
 *
 * Identifies interactive features that depend on hover states and are
 * inaccessible on touch devices. Should provide touch alternatives.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Mobile_Hover_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-hover-functionality';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Hover-Dependent Functionality Detection';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects hover-only interactions on touch devices';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'touch-interaction';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = self::find_hover_issues();

		if ( empty( $issues['all'] ) ) {
			return null; // No issues found
		}

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %d: number of hover-dependent elements */
				__( 'Found %d hover-only interactive elements', 'wpshadow' ),
				count( $issues['all'] )
			),
			'severity'        => count( $issues['all'] ) > 5 ? 'high' : 'medium',
			'threat_level'    => 65,
			'issues'          => array_slice( $issues['all'], 0, 5 ),
			'total_issues'    => count( $issues['all'] ),
			'categories'      => $issues['categories'] ?? array(),
			'wcag_violation'  => 'WCAG 2.5.5 Target Size',
			'user_impact'     => __( 'Touch users cannot access hidden hover elements', 'wpshadow' ),
			'auto_fixable'    => false,
			'kb_link'         => 'https://wpshadow.com/kb/hover-functionality?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}

	/**
	 * Find hover-dependent functionality issues.
	 *
	 * @since 0.6093.1200
	 * @return array Issues found.
	 */
	private static function find_hover_issues(): array {
		$html = self::get_page_html();
		if ( ! $html ) {
			return array( 'all' => array() );
		}

		$issues = array();
		$categories = array();

		// Extract CSS
		preg_match_all( '/<style[^>]*>(.*?)<\/style>/is', $html, $style_matches );
		$css = implode( "\n", $style_matches[1] ?? array() );

		// Check for :hover pseudo-class without :focus or :active
		$hover_issues = self::check_hover_without_focus( $css );
		$issues = array_merge( $issues, $hover_issues );
		if ( ! empty( $hover_issues ) ) {
			$categories[] = 'CSS :hover without :focus';
		}

		// Check for hidden menus revealed on hover
		$menu_issues = self::check_hover_menus( $html, $css );
		$issues = array_merge( $issues, $menu_issues );
		if ( ! empty( $menu_issues ) ) {
			$categories[] = 'Hidden dropdown menus';
		}

		// Check for tooltips only on hover
		$tooltip_issues = self::check_hover_tooltips( $html, $css );
		$issues = array_merge( $issues, $tooltip_issues );
		if ( ! empty( $tooltip_issues ) ) {
			$categories[] = 'Hover-triggered tooltips';
		}

		// Check for JavaScript mouse-only handlers
		$js_issues = self::check_js_mouse_handlers( $html );
		$issues = array_merge( $issues, $js_issues );
		if ( ! empty( $js_issues ) ) {
			$categories[] = 'JavaScript mouse events';
		}

		return array(
			'all'        => array_unique( $issues ),
			'categories' => array_unique( $categories ),
		);
	}

	/**
	 * Check for :hover without :focus or :active.
	 *
	 * @since 0.6093.1200
	 * @param  string $css CSS content.
	 * @return array Issues found.
	 */
	private static function check_hover_without_focus( string $css ): array {
		$issues = array();

		// Find :hover selectors
		preg_match_all( '/([^{]+):hover\s*{([^}]+)}/', $css, $hover_matches );

		foreach ( $hover_matches[1] ?? array() as $index => $selector ) {
			// Check if there's a corresponding :focus or :active
			$selector_clean = trim( $selector );
			$css_rule = $hover_matches[2][ $index ] ?? '';

			// Check if :focus exists for this selector
			if ( ! preg_match( '/' . preg_quote( $selector_clean, '/' ) . ':focus/', $css ) &&
				 ! preg_match( '/' . preg_quote( $selector_clean, '/' ) . ':active/', $css ) ) {
				$issues[] = array(
					'type'     => 'hover-only',
					'selector' => $selector_clean,
					'css_rule' => substr( $css_rule, 0, 50 ),
					'severity' => 'high',
				);
			}
		}

		return $issues;
	}

	/**
	 * Check for dropdown menus revealed on hover.
	 *
	 * @since 0.6093.1200
	 * @param  string $html HTML content.
	 * @param  string $css CSS content.
	 * @return array Dropdown menu issues.
	 */
	private static function check_hover_menus( string $html, string $css ): array {
		$issues = array();

		// Check for display: none on submenus
		if ( preg_match( '/\.submenu\s*{[^}]*display\s*:\s*none/i', $css ) ) {
			// Check if revealed on parent hover
			if ( preg_match( '/li:hover\s*>\s*\.submenu|\.submenu:hover|\.menu-item:hover\s*\.submenu/i', $css ) ) {
				$issues[] = array(
					'type'     => 'hover-menu',
					'element'  => 'Dropdown submenu',
					'trigger'  => ':hover',
					'severity' => 'high',
				);
			}
		}

		// Check for transform translateX/Y on hidden menus
		if ( preg_match( '/transform\s*:\s*(?:translateX|translateY)\(-[0-9]+|opacity\s*:\s*0/', $css ) ) {
			if ( preg_match( '/:hover\s*{[^}]*(?:translate|opacity)/i', $css ) ) {
				$issues[] = array(
					'type'     => 'hover-transform',
					'element'  => 'Hidden element revealed via transform',
					'trigger'  => ':hover',
					'severity' => 'high',
				);
			}
		}

		return $issues;
	}

	/**
	 * Check for tooltips only on hover.
	 *
	 * @since 0.6093.1200
	 * @param  string $html HTML content.
	 * @param  string $css CSS content.
	 * @return array Tooltip issues.
	 */
	private static function check_hover_tooltips( string $html, string $css ): array {
		$issues = array();

		// Check for title attribute with hover-based display
		$tooltip_count = preg_match_all( '/title\s*=\s*["\']/', $html, $matches );

		// Check for ::before or ::after with hover display
		if ( preg_match( '/::before|::after.*?content/i', $css ) &&
			 preg_match( '/:hover\s*::(?:before|after)/i', $css ) ) {
			$issues[] = array(
				'type'     => 'hover-tooltip',
				'element'  => 'Pseudo-element tooltip',
				'trigger'  => ':hover ::before/::after',
				'severity' => 'medium',
			);
		}

		// Check for hidden tooltips in data attributes
		if ( preg_match( '/data-tooltip|data-hint|aria-tooltip/i', $html ) &&
			 preg_match( '/:hover.*?opacity|:hover.*?display:block/i', $css ) ) {
			$issues[] = array(
				'type'     => 'hover-data-tooltip',
				'element'  => 'Data attribute tooltip',
				'trigger'  => ':hover',
				'severity' => 'medium',
			);
		}

		return $issues;
	}

	/**
	 * Check for JavaScript mouse-only event handlers.
	 *
	 * @since 0.6093.1200
	 * @param  string $html HTML content.
	 * @return array JavaScript handler issues.
	 */
	private static function check_js_mouse_handlers( string $html ): array {
		$issues = array();

		// Count onmouseover without ontouchstart
		$mouseover_count = preg_match_all( '/onmouseover\s*=/', $html, $matches );
		if ( $mouseover_count > 0 ) {
			// Check if there are corresponding touch handlers
			if ( ! preg_match( '/ontouchstart|ontouchend|addEventListener.*?touch/i', $html ) ) {
				$issues[] = array(
					'type'     => 'js-mouse-only',
					'event'    => 'onmouseover',
					'count'    => $mouseover_count,
					'severity' => 'high',
				);
			}
		}

		// Check for mouseenter without touch equivalents
		$mouseenter_count = preg_match_all( '/\.on\(["\']mouseenter/', $html, $matches );
		if ( $mouseenter_count > 0 ) {
			$issues[] = array(
				'type'     => 'js-mouse-only',
				'event'    => 'mouseenter',
				'count'    => $mouseenter_count,
				'severity' => 'high',
			);
		}

		return $issues;
	}

	/**
	 * Get page HTML for analysis.
	 *
	 * @since 0.6093.1200
	 * @return string|null HTML content.
	 */
	private static function get_page_html(): ?string {
		return Diagnostic_HTML_Helper::fetch_homepage_html(
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);
	}
}
