<?php
/**
 * HTML Validate Internal Link Structure On The Page Diagnostic
 *
 * Validates internal link structure and patterns.
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
 * HTML Validate Internal Link Structure On The Page Diagnostic Class
 */
class Diagnostic_Html_Validate_Internal_Link_Structure_On_The_Page extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-validate-internal-link-structure-on-the-page';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Poor Internal Link Structure';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates internal link structure on the page';

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

		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) {
			return null;
		}

		$content = $post->post_content;
		$site_url = home_url();

		// Count total links.
		$all_links = preg_match_all( '/<a[^>]*href=["\']([^"\']+)["\'][^>]*>/i', $content );

		if ( ! $all_links || $all_links < 3 ) {
			return null; // Need at least 3 links for meaningful analysis
		}

		// Count internal vs external.
		$internal_count = 0;
		$external_count = 0;

		if ( preg_match_all( '/<a[^>]*href=["\']([^"\']+)["\'][^>]*/i', $content, $matches ) ) {
			foreach ( $matches[1] as $href ) {
				if ( strpos( $href, $site_url ) === 0 || strpos( $href, '/' ) === 0 ) {
					$internal_count++;
				} else {
					$external_count++;
				}
			}
		}

		// Ideal ratio: ~80% internal, ~20% external.
		$internal_ratio = $all_links > 0 ? $internal_count / $all_links : 0;
		$external_ratio = $all_links > 0 ? $external_count / $all_links : 0;

		// If too many external links relative to internal.
		if ( $internal_ratio < 0.3 && $all_links > 5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: internal count, 2: external count, 3: internal ratio */
					__( 'Poor internal link structure: %1$d internal links vs %2$d external (%.1f%% internal). Pages with mostly external links dilute your site\'s SEO value. Aim for ~80%% internal and ~20%% external links to distribute PageRank internally.', 'wpshadow' ),
					$internal_count,
					$external_count,
					$internal_ratio * 100
				),
				'severity'     => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/html-validate-internal-link-structure-on-the-page',
				'meta'         => array(
					'total_links'      => $all_links,
					'internal_count'   => $internal_count,
					'external_count'   => $external_count,
					'internal_ratio'   => round( $internal_ratio, 2 ),
				),
			);
		}

		return null;
	}
}
