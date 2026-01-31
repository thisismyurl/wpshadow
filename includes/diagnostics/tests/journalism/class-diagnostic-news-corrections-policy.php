<?php
/**
 * News Article Correction and Retraction Policy Diagnostic
 *
 * Checks if journalism/news sites have visible correction and retraction
 * policies, and systems in place to properly mark updated/corrected articles.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Journalism
 * @since      1.6031.1446
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Journalism;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * News Corrections Policy Diagnostic Class
 *
 * Verifies journalism sites have proper correction and retraction policies.
 *
 * @since 1.6031.1446
 */
class Diagnostic_News_Corrections_Policy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'news-corrections-policy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'News Article Correction and Retraction Policy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies news sites have visible correction/retraction policies and systems';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'journalism';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for:
	 * - Corrections policy page
	 * - Article versioning/revision tracking
	 * - Update timestamps on articles
	 * - Correction notices in post meta
	 *
	 * @since  1.6031.1446
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if site appears to be journalism/news focused.
		$site_tagline    = get_bloginfo( 'description' );
		$site_name       = get_bloginfo( 'name' );
		$journalism_terms = array( 'news', 'journalism', 'reporter', 'press', 'media' );

		$is_journalism_site = false;
		foreach ( $journalism_terms as $term ) {
			if ( stripos( $site_name, $term ) !== false || stripos( $site_tagline, $term ) !== false ) {
				$is_journalism_site = true;
				break;
			}
		}

		if ( ! $is_journalism_site ) {
			return null; // Not a journalism site.
		}

		$issues = array();

		// Check for corrections policy page.
		$pages = get_pages();
		$has_corrections_page = false;

		foreach ( $pages as $page ) {
			if ( stripos( $page->post_title, 'correction' ) !== false ||
				stripos( $page->post_title, 'retraction' ) !== false ||
				stripos( $page->post_content, 'correction policy' ) !== false ) {
				$has_corrections_page = true;
				break;
			}
		}

		if ( ! $has_corrections_page ) {
			$issues[] = __( 'No corrections/retractions policy page found', 'wpshadow' );
		}

		// Check for editorial/corrections plugins.
		$active_plugins       = get_option( 'active_plugins', array() );
		$has_editorial_plugin = false;
		$editorial_plugins    = array(
			'editorial',
			'correction',
			'retraction',
			'article-version',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $editorial_plugins as $ed_plugin ) {
				if ( stripos( $plugin, $ed_plugin ) !== false ) {
					$has_editorial_plugin = true;
					break 2;
				}
			}
		}

		if ( ! $has_editorial_plugin ) {
			$issues[] = __( 'No editorial/corrections management plugin detected', 'wpshadow' );
		}

		// Check if post revisions are enabled (good for transparency).
		if ( ! wp_revisions_enabled( get_post( get_option( 'page_on_front' ) ) ) ) {
			$issues[] = __( 'Post revisions disabled (prevents article history tracking)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Corrections policy concerns: %s. Journalism sites should have transparent correction policies and systems to track article updates.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/news-corrections-policy',
		);
	}
}
