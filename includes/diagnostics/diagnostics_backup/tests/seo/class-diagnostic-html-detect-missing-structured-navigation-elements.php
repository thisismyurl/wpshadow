<?php
/**
 * HTML Detect Missing Structured Navigation Elements Diagnostic
 *
 * Detects missing semantic navigation elements.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\HTML
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTML Detect Missing Structured Navigation Elements Diagnostic Class
 *
 * Identifies pages missing proper semantic navigation elements like
 * <nav>, <menu>, or other structured navigation markup.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Missing_Structured_Navigation_Elements extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-missing-structured-navigation-elements';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Structured Navigation Elements';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing semantic <nav> or other structured navigation elements';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( is_admin() ) {
			return null;
		}

		$missing_nav = array();
		$has_nav     = false;
		$has_menu    = false;
		$has_ul_menu = false;

		// Check scripts for navigation elements.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Check for <nav> element.
					if ( preg_match( '/<nav[^>]*>/i', $data ) ) {
						$has_nav = true;
					}

					// Check for <menu> element.
					if ( preg_match( '/<menu[^>]*>/i', $data ) ) {
						$has_menu = true;
					}

					// Check for <ul class="menu"> or similar nav menu pattern.
					if ( preg_match( '/<ul[^>]*(?:class="[^"]*menu[^"]*"|role="navigation")[^>]*>/i', $data ) ) {
						$has_ul_menu = true;
					}
				}
			}
		}

		// If site has menus registered, should have navigation markup.
		$menus = get_registered_nav_menus();

		if ( ! empty( $menus ) ) {
			if ( ! $has_nav && ! $has_menu && ! $has_ul_menu ) {
				$missing_nav[] = array(
					'issue'        => __( 'Registered navigation menus exist but no semantic <nav> element found', 'wpshadow' ),
					'menu_count'   => count( $menus ),
					'recommendation' => __( 'Use wp_nav_menu() with semantic markup or ensure <nav> wraps navigation', 'wpshadow' ),
				);
			}
		} else {
			// Check if there's a primary menu but no <nav> to display it.
			if ( has_nav_menu( 'primary' ) && ! $has_nav ) {
				$missing_nav[] = array(
					'issue'         => __( 'Primary menu registered but no semantic <nav> element found', 'wpshadow' ),
					'recommendation' => __( 'Wrap wp_nav_menu( array( \'theme_location\' => \'primary\' ) ) in <nav> element', 'wpshadow' ),
				);
			}
		}

		// Check for breadcrumb navigation.
		if ( ! preg_match( '/<nav[^>]*aria-label=["\']?breadcrumb["\']?[^>]*>/i', '' ) && ! preg_match( '/<nav[^>]*role=["\']?navigation["\']?[^>]*>/i', '' ) ) {
			// If this looks like a post/archive page and no breadcrumbs, that's optional but nice.
			if ( is_singular() || is_archive() ) {
				// This is optional, so we just note it.
			}
		}

		if ( empty( $missing_nav ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $missing_nav, 0, $max_items ) as $item ) {
			$items_list .= sprintf(
				"\n- %s",
				esc_html( $item['issue'] )
			);
		}

		if ( count( $missing_nav ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more navigation issues", 'wpshadow' ),
				count( $missing_nav ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d missing structured navigation issue(s). Pages should use semantic <nav> elements to help screen readers and search engines understand site structure. Proper navigation markup improves accessibility and SEO.%2$s', 'wpshadow' ),
				count( $missing_nav ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-missing-structured-navigation-elements',
			'meta'         => array(
				'issues' => $missing_nav,
			),
		);
	}
}
