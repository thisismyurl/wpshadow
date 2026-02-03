<?php
/**
 * Mobile Submenu Interaction
 *
 * Ensures multi-level submenus use tap/click (not hover-only) on touch devices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Navigation
 * @since      1.2602.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Submenu Interaction
 *
 * Validates that multi-level submenus are accessible on touch devices
 * by using click/tap handlers instead of hover-only interactions.
 *
 * @since 1.2602.1430
 */
class Diagnostic_Mobile_Submenu extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-submenu-interaction';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Submenu Interaction';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Ensures submenus use tap/click on touch devices';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'navigation';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2602.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = self::find_submenu_issues();

		if ( empty( $issues['all'] ) ) {
			return null; // No submenu issues detected
		}

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %d: number of submenu issues */
				__( 'Found %d submenu accessibility issues', 'wpshadow' ),
				count( $issues['all'] )
			),
			'severity'        => 'high',
			'threat_level'    => 70,
			'issues'          => $issues['all'],
			'has_submenus'    => $issues['has_submenus'] ?? false,
			'submenus_hidden' => $issues['hidden_count'] ?? 0,
			'hover_only'      => $issues['hover_only_count'] ?? 0,
			'wcag_violation'  => '2.1.1 Keyboard (Level A)',
			'user_impact'     => __( 'Touch users cannot access multi-level menus', 'wpshadow' ),
			'auto_fixable'    => false,
			'kb_link'         => 'https://wpshadow.com/kb/submenu-interaction',
		);
	}

	/**
	 * Find submenu interaction issues.
	 *
	 * @since  1.2602.1430
	 * @return array Issues found.
	 */
	private static function find_submenu_issues(): array {
		$html = self::get_page_html();
		if ( ! $html ) {
			return array( 'all' => array() );
		}

		$issues = array(
			'all'              => array(),
			'has_submenus'     => false,
			'hidden_count'     => 0,
			'hover_only_count' => 0,
		);

		// Check for submenu elements
		$has_submenus = self::check_for_submenus( $html );
		$issues['has_submenus'] = $has_submenus;

		if ( ! $has_submenus ) {
			return $issues; // No submenus to check
		}

		// Check for hover-only submenus
		$hover_issues = self::check_hover_submenus( $html );
		$issues['all'] = array_merge( $issues['all'], $hover_issues );
		$issues['hover_only_count'] = count( $hover_issues );

		// Check for hidden submenus without indicators
		$hidden_issues = self::check_submenu_indicators( $html );
		$issues['all'] = array_merge( $issues['all'], $hidden_issues );
		$issues['hidden_count'] = count( $hidden_issues );

		// Check for click handlers on submenus
		$click_issues = self::check_click_handlers( $html );
		$issues['all'] = array_merge( $issues['all'], $click_issues );

		return $issues;
	}

	/**
	 * Check if page has submenu structure.
	 *
	 * @since  1.2602.1430
	 * @param  string $html HTML content.
	 * @return bool Has submenus.
	 */
	private static function check_for_submenus( string $html ): bool {
		$patterns = array(
			'/<ul\s+class\s*=\s*["\'][^"\']*sub-menu[^"\']*["\']/',
			'/<ul\s+class\s*=\s*["\'][^"\']*submenu[^"\']*["\']/',
			'/<ul\s+class\s*=\s*["\'][^"\']*dropdown[^"\']*["\']/',
			'/class\s*=\s*["\'][^"\']*dropdown-menu[^"\']*["\']/',
			'/<li[^>]*><a[^>]*>.*?<ul/',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $html ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for hover-only submenu reveal.
	 *
	 * @since  1.2602.1430
	 * @param  string $html HTML content.
	 * @return array Hover issues.
	 */
	private static function check_hover_submenus( string $html ): array {
		$issues = array();

		// Extract CSS
		preg_match_all( '/<style[^>]*>(.*?)<\/style>/is', $html, $style_matches );
		$css = implode( "\n", $style_matches[1] ?? array() );

		// Look for :hover reveal patterns
		$patterns = array(
			'/li:hover\s*>\s*(?:ul|\.sub-menu)/',
			'/li:hover\s+ul/',
			'/\.menu-item:hover\s*\.sub-menu/',
			'/li:hover\s*{[^}]*(?:display\s*:\s*block|opacity\s*:\s*1)/',
		);

		$hover_count = 0;
		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $css ) ) {
				$hover_count++;
			}
		}

		if ( $hover_count > 0 ) {
			// Check if there's also a click/tap handler
			if ( ! self::has_click_handler( $html ) ) {
				$issues[] = array(
					'type'    => 'hover-only-submenu',
					'issue'   => 'Submenus revealed only on :hover, no touch alternative',
					'element' => 'Dropdown menu',
				);
			}
		}

		return $issues;
	}

	/**
	 * Check for submenu visual indicators (chevrons, arrows).
	 *
	 * @since  1.2602.1430
	 * @param  string $html HTML content.
	 * @return array Missing indicator issues.
	 */
	private static function check_submenu_indicators( string $html ): array {
		$issues = array();

		// Check for visual indicators
		$has_chevron = preg_match( '/class\s*=\s*["\'][^"\']*(?:chevron|arrow|caret|expand)[^"\']*["\']/', $html );
		$has_icon = preg_match( '/class\s*=\s*["\'][^"\']*(?:icon|fa-[a-z-]+)[^"\']*["\']/', $html );

		if ( ! $has_chevron && ! $has_icon ) {
			$issues[] = array(
				'type'    => 'no-submenu-indicator',
				'issue'   => 'No visual indicators (chevron/arrow) for menu items with submenus',
				'element' => 'Menu indicators',
			);
		}

		// Check for aria-expanded attribute
		$has_aria_expanded = preg_match( '/aria-expanded\s*=\s*["\'](?:true|false)["\']/i', $html );

		if ( ! $has_aria_expanded ) {
			$issues[] = array(
				'type'    => 'no-aria-expanded',
				'issue'   => 'Missing aria-expanded attribute on menu toggle buttons',
				'element' => 'Submenu toggles',
			);
		}

		return $issues;
	}

	/**
	 * Check for click event handlers on submenus.
	 *
	 * @since  1.2602.1430
	 * @param  string $html HTML content.
	 * @return array Click handler issues.
	 */
	private static function check_click_handlers( string $html ): array {
		$issues = array();

		// Check for click handlers
		$has_click = preg_match( '/onclick\s*=|\.on\(["\']click["\']|addEventListener.*?click/i', $html );
		$has_touch = preg_match( '/ontouchend|\.on\(["\'](?:touch|tap)/i', $html );
		$has_keydown = preg_match( '/onkeydown\s*=|addEventListener.*?keydown/i', $html );

		if ( ! $has_click && ! $has_touch ) {
			$issues[] = array(
				'type'    => 'no-click-handler',
				'issue'   => 'No click/tap handler detected for submenu expansion',
				'element' => 'Event handlers',
			);
		}

		// Check for specific menu toggle patterns
		if ( ! preg_match( '/\.toggle\(|classList\.toggle|toggleClass/i', $html ) ) {
			$issues[] = array(
				'type'    => 'no-class-toggle',
				'issue'   => 'No class toggle pattern for menu state management',
				'element' => 'Menu state',
			);
		}

		// Check for enter key support
		if ( $has_keydown ) {
			if ( ! preg_match( '/key\s*===?\s*["\']Enter["\']/i', $html ) ) {
				$issues[] = array(
					'type'    => 'no-enter-key',
					'issue'   => 'No Enter key support for submenu expansion',
					'element' => 'Keyboard support',
				);
			}
		} else {
			$issues[] = array(
				'type'    => 'no-keyboard-support',
				'issue'   => 'No keyboard event handler for submenu navigation',
				'element' => 'Keyboard support',
			);
		}

		return $issues;
	}

	/**
	 * Check if page has click handler.
	 *
	 * @since  1.2602.1430
	 * @param  string $html HTML content.
	 * @return bool Has click handler.
	 */
	private static function has_click_handler( string $html ): bool {
		$patterns = array(
			'/onclick\s*=/',
			'/\.on\(["\']click["\']/',
			'/addEventListener.*?click/i',
			'/\.click\(/',
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $html ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get page HTML for analysis.
	 *
	 * @since  1.2602.1430
	 * @return string|null HTML content.
	 */
	private static function get_page_html(): ?string {
		$response = wp_remote_get(
			home_url( '/' ),
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		return wp_remote_retrieve_body( $response );
	}
}
