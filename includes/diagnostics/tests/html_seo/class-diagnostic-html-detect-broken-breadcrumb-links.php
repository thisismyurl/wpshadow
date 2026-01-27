<?php
/**
 * HTML Detect Broken Breadcrumb Links Diagnostic
 *
 * Detects broken links in breadcrumb navigation.
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
 * HTML Detect Broken Breadcrumb Links Diagnostic Class
 *
 * Identifies breadcrumb navigation with broken or invalid links that
 * hurt user navigation and SEO.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Broken_Breadcrumb_Links extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-broken-breadcrumb-links';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Broken Breadcrumb Links';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects broken or missing links in breadcrumb navigation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

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

		$broken_breadcrumbs = array();

		// Check scripts for breadcrumb patterns.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Look for breadcrumb nav elements.
					if ( preg_match( '/<nav[^>]*(?:class="[^"]*breadcrumb[^"]*"|aria-label=["\']breadcrumb["\'])[^>]*>/i', $data ) ) {
						// Check for links without href.
						if ( preg_match_all( '/<a[^>]*(?!href=["\']([^"\']+)["\'])[^>]*>([^<]+)<\/a>/i', $data, $matches ) ) {
							foreach ( $matches[2] as $link_text ) {
								$broken_breadcrumbs[] = array(
									'handle'  => $handle,
									'text'    => trim( $link_text ),
									'issue'   => __( 'Breadcrumb link missing href attribute', 'wpshadow' ),
									'impact'  => __( 'Users and search engines cannot follow the link', 'wpshadow' ),
								);
							}
						}

						// Check for broken/empty hrefs.
						if ( preg_match_all( '/<a[^>]*href=["\']([^"\']*)["\'][^>]*>([^<]+)<\/a>/i', $data, $matches ) ) {
							foreach ( $matches[1] as $idx => $href ) {
								if ( empty( $href ) || $href === '#' || $href === 'javascript:' ) {
									$broken_breadcrumbs[] = array(
										'handle' => $handle,
										'href'   => $href,
										'text'   => trim( $matches[2][ $idx ] ),
										'issue'  => __( 'Breadcrumb link with empty or invalid href', 'wpshadow' ),
										'impact' => __( 'Link does not navigate anywhere', 'wpshadow' ),
									);
								}
							}
						}
					}
				}
			}
		}

		if ( empty( $broken_breadcrumbs ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $broken_breadcrumbs, 0, $max_items ) as $item ) {
			$items_list .= sprintf(
				"\n- \"%s\": %s",
				esc_html( $item['text'] ),
				esc_html( $item['issue'] )
			);
		}

		if ( count( $broken_breadcrumbs ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more breadcrumb issues", 'wpshadow' ),
				count( $broken_breadcrumbs ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d broken breadcrumb link(s). Breadcrumbs help users navigate your site hierarchy and provide SEO value through structured markup. All breadcrumb links must have valid href attributes that point to real pages.%2$s', 'wpshadow' ),
				count( $broken_breadcrumbs ),
				$items_list
			),
			'severity'     => 'medium',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-broken-breadcrumb-links',
			'meta'         => array(
				'breadcrumbs' => $broken_breadcrumbs,
			),
		);
	}
}
