<?php
/**
 * HTML Verify Breadcrumbs Markup Exists Diagnostic
 *
 * Verifies breadcrumbs are present in page markup.
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
 * HTML Verify Breadcrumbs Markup Exists Diagnostic Class
 *
 * Identifies pages that should have breadcrumbs but don't, which can
 * impact user navigation and SEO.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Verify_Breadcrumbs_Markup_Exists extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-verify-breadcrumbs-markup-exists';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Breadcrumb Navigation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing breadcrumb navigation markup';

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

		global $post;

		// Only check on hierarchical content (not homepage, not single posts usually).
		if ( empty( $post ) ) {
			return null;
		}

		// Pages and custom post types that are hierarchical benefit from breadcrumbs.
		$hierarchical_types = array( 'page' );

		if ( ! in_array( $post->post_type, $hierarchical_types, true ) ) {
			return null;
		}

		// Check if page has parent (nested structure).
		if ( empty( $post->post_parent ) ) {
			return null;
		}

		$breadcrumb_found = false;

		// Check scripts for breadcrumb patterns.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Look for breadcrumb nav elements.
					if ( preg_match( '/<nav[^>]*(?:class="[^"]*breadcrumb[^"]*"|aria-label=["\']breadcrumb["\'])[^>]*>/i', $data ) ) {
						$breadcrumb_found = true;
						break;
					}

					// Check for breadcrumb schema.org markup.
					if ( preg_match( '/BreadcrumbList|itemtype=.*Breadcrumb/i', $data ) ) {
						$breadcrumb_found = true;
						break;
					}
				}
			}
		}

		if ( ! $breadcrumb_found ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: page title */
					__( 'Nested page "%s" missing breadcrumb navigation. Breadcrumbs help users understand page hierarchy and improve navigation experience. They also enable rich search results snippets. Add breadcrumb navigation using your theme or a breadcrumb plugin.', 'wpshadow' ),
					esc_html( get_the_title( $post ) )
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/html-verify-breadcrumbs-markup-exists',
				'meta'         => array(
					'post_id'       => $post->ID,
					'post_title'    => $post->post_title,
					'post_parent'   => $post->post_parent,
					'is_hierarchical' => true,
				),
			);
		}

		return null;
	}
}
