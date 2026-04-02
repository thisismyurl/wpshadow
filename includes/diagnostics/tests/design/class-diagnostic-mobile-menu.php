<?php
/**
 * Mobile Menu Existence Check
 *
 * Verifies a mobile-friendly navigation menu exists at <768px viewport.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Navigation
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Menu Existence Check
 *
 * Validates that a mobile-friendly navigation menu exists and is properly
 * implemented at mobile viewport sizes (<768px).
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mobile_Menu extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-menu-existence';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Menu Existence Check';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies mobile menu exists and is functional';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'navigation';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = self::find_menu_issues();

		if ( empty( $issues['all'] ) ) {
			return null; // Mobile menu exists and is functional
		}

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %d: number of menu issues */
				__( 'Found %d mobile menu issues', 'wpshadow' ),
				count( $issues['all'] )
			),
			'severity'        => 'high',
			'threat_level'    => 75,
			'issues'          => $issues['all'],
			'has_mobile_menu' => $issues['has_mobile_menu'] ?? false,
			'has_hamburger'   => $issues['has_hamburger'] ?? false,
			'has_toggle'      => $issues['has_toggle'] ?? false,
			'wcag_violation'  => '2.1.1 Keyboard (Level A)',
			'user_impact'     => __( 'Mobile users cannot access navigation', 'wpshadow' ),
			'auto_fixable'    => false,
			'kb_link'         => 'https://wpshadow.com/kb/mobile-menu',
		);
	}

	/**
	 * Find mobile menu issues.
	 *
	 * @since 1.6093.1200
	 * @return array Issues found.
	 */
	private static function find_menu_issues(): array {
		$html = self::get_page_html();
		if ( ! $html ) {
			return array( 'all' => array() );
		}

		$issues = array(
			'all'              => array(),
			'has_mobile_menu'  => false,
			'has_hamburger'    => false,
			'has_toggle'       => false,
		);

		// Check for mobile menu
		$has_mobile_menu = self::check_mobile_menu( $html );
		$issues['has_mobile_menu'] = $has_mobile_menu;

		if ( ! $has_mobile_menu ) {
			$issues['all'][] = array(
				'type'    => 'no-mobile-menu',
				'issue'   => 'No responsive mobile menu detected',
				'element' => 'Navigation',
			);
		}

		// Check for hamburger menu button
		$has_hamburger = self::check_hamburger_button( $html );
		$issues['has_hamburger'] = $has_hamburger;

		if ( ! $has_hamburger ) {
			$issues['all'][] = array(
				'type'    => 'no-hamburger',
				'issue'   => 'No hamburger menu button found',
				'element' => 'Navigation toggle',
			);
		}

		// Check for menu toggle functionality
		$has_toggle = self::check_menu_toggle( $html );
		$issues['has_toggle'] = $has_toggle;

		if ( ! $has_toggle ) {
			$issues['all'][] = array(
				'type'    => 'no-toggle',
				'issue'   => 'Menu toggle functionality not detected',
				'element' => 'JavaScript handler',
			);
		}

		// Check for media query
		$has_media_query = self::check_responsive_media_query( $html );

		if ( ! $has_media_query ) {
			$issues['all'][] = array(
				'type'       => 'no-media-query',
				'issue'      => 'No mobile breakpoint media query detected',
				'breakpoint' => '<768px',
			);
		}

		// Check CSS for mobile display properties
		$css_issues = self::check_mobile_css( $html );
		$issues['all'] = array_merge( $issues['all'], $css_issues );

		return $issues;
	}

	/**
	 * Check for mobile menu element.
	 *
	 * @since 1.6093.1200
	 * @param  string $html HTML content.
	 * @return bool Has mobile menu.
	 */
	private static function check_mobile_menu( string $html ): bool {
		// Look for common mobile menu patterns
		$patterns = array(
			'/class\s*=\s*["\'][^"\']*mobile[^"\']*["\']/',
			'/id\s*=\s*["\'][^"\']*mobile[^"\']*["\']/',
			'/class\s*=\s*["\'][^"\']*hamburger[^"\']*["\']/',
			'/class\s*=\s*["\'][^"\']*menu-toggle[^"\']*["\']/',
			'/<nav[^>]*class\s*=\s*["\'][^"\']*mobile[^"\']*["\']/',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $html ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for hamburger menu button.
	 *
	 * @since 1.6093.1200
	 * @param  string $html HTML content.
	 * @return bool Has hamburger button.
	 */
	private static function check_hamburger_button( string $html ): bool {
		$patterns = array(
			'/class\s*=\s*["\'][^"\']*(?:hamburger|menu-toggle|nav-toggle)[^"\']*["\']/',
			'/aria-label\s*=\s*["\'](?:Menu|Toggle|Hamburger)[^"\']*["\']/',
			'/<button[^>]*(?:menu|hamburger|toggle)[^>]*>/i',
			'/data-toggle\s*=\s*["\']menu["\']/i',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $html ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for menu toggle JavaScript.
	 *
	 * @since 1.6093.1200
	 * @param  string $html HTML content.
	 * @return bool Has toggle handler.
	 */
	private static function check_menu_toggle( string $html ): bool {
		$patterns = array(
			'/toggle|onclick|addEventListener/i',
			'/\.classList\.(?:add|remove|toggle)\(["\']active["\']/',
			'/slideToggle|slideDown|slideUp|fadeToggle/',
			'/data-toggle=["\'](.*?)["\']/i',
			'/aria-expanded=["\'](true|false)["\']/i',
		);

		$js_count = preg_match_all( '/toggle|onclick|addEventListener/i', $html, $matches );
		$aria_expanded = preg_match_all( '/aria-expanded/i', $html, $matches );

		return ( $js_count > 0 ) || ( $aria_expanded > 0 );
	}

	/**
	 * Check for responsive media query.
	 *
	 * @since 1.6093.1200
	 * @param  string $html HTML content.
	 * @return bool Has media query.
	 */
	private static function check_responsive_media_query( string $html ): bool {
		// Look for media queries targeting mobile
		$patterns = array(
			'/@media\s*\(?.*?(?:max-width|max-device-width)\s*:\s*(?:768px|767px|600px|640px|480px)/',
			'/@media\s*\(?.*?screen.*?and.*?max-width/',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $html ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check CSS for mobile display rules.
	 *
	 * @since 1.6093.1200
	 * @param  string $html HTML content.
	 * @return array CSS issues.
	 */
	private static function check_mobile_css( string $html ): array {
		$issues = array();

		// Extract CSS
		preg_match_all( '/<style[^>]*>(.*?)<\/style>/is', $html, $style_matches );
		$css = implode( "\n", $style_matches[1] ?? array() );

		// Check for display: none on nav (never hidden completely)
		if ( preg_match( '/nav\s*{[^}]*display\s*:\s*none/', $css ) ) {
			$issues[] = array(
				'type'  => 'nav-hidden',
				'issue' => 'Navigation completely hidden (no :not(:not-mobile) rule)',
			);
		}

		// Check for max-width on nav (might be hidden)
		if ( preg_match( '/nav\s*{[^}]*max-width\s*:\s*0/', $css ) ) {
			$issues[] = array(
				'type'  => 'nav-max-width-zero',
				'issue' => 'Navigation max-width: 0 might hide it',
			);
		}

		return $issues;
	}

	/**
	 * Get page HTML for analysis.
	 *
	 * @since 1.6093.1200
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
