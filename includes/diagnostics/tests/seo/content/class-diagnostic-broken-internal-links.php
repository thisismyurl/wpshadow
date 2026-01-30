<?php
/**
 * Broken Internal Links Diagnostic
 *
 * Scans content for broken internal links that create poor UX
 * and damage SEO through dead-end pages and crawl errors.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Broken_Internal_Links Class
 *
 * Detects broken internal links in content.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Broken_Internal_Links extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'broken-internal-links';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Broken Internal Links';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects broken internal links in content';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if broken links found, null otherwise.
	 */
	public static function check() {
		$link_check = self::scan_for_broken_links();

		if ( $link_check['broken_count'] === 0 ) {
			return null; // No broken links
		}

		$severity = $link_check['broken_count'] > 50 ? 'high' : 'medium';
		$threat   = $link_check['broken_count'] > 50 ? 65 : 50;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of broken links */
				__( '%d broken internal links found. Users encounter 404 errors, damaging UX and SEO. Search engines penalize sites with many dead links.', 'wpshadow' ),
				$link_check['broken_count']
			),
			'severity'     => $severity,
			'threat_level' => $threat,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/fix-broken-links',
			'family'       => self::$family,
			'meta'         => array(
				'broken_links_found' => $link_check['broken_count'],
				'seo_impact'         => __( 'Google penalizes sites with many 404s' ),
				'user_experience'    => __( 'Frustrating dead ends for visitors' ),
				'fix_priority'       => __( 'High - affects rankings' ),
			),
			'details'      => array(
				'why_broken_links_matter' => array(
					__( 'Google: Many 404s = poor quality site' ),
					__( 'Users: Dead links = frustration, higher bounce rate' ),
					__( 'Crawl budget wasted on dead pages' ),
					__( 'Link equity lost (PageRank not flowing)' ),
				),
				'common_causes'           => array(
					'Deleted Pages' => array(
						'Cause: Post/page deleted but links remain',
						'Fix: Update or remove links pointing to deleted content',
					),
					'Changed Permalinks' => array(
						'Cause: URL structure changed',
						'Fix: Add 301 redirects from old to new URLs',
					),
					'Typos in URLs' => array(
						'Cause: Manual link entry errors',
						'Fix: Scan and correct typos',
					),
					'Plugin Conflicts' => array(
						'Cause: Shortcodes broke after plugin deactivation',
						'Fix: Replace shortcode links with direct URLs',
					),
				),
				'finding_broken_links'    => array(
					'WordPress Plugin (Recommended)' => array(
						'Broken Link Checker (Free)',
						'Automatically scans all content',
						'Email alerts for new broken links',
						'One-click fix for most links',
					),
					'Online Tools' => array(
						'Screaming Frog SEO Spider (Free 500 URLs)',
						'Ahrefs Site Audit (Premium)',
						'Google Search Console (Free, shows 404s)',
					),
				),
				'fixing_broken_links'     => array(
					'Update Link' => array(
						'Edit post/page',
						'Replace broken URL with correct one',
						'Test link before saving',
					),
					'301 Redirect' => array(
						'If old URL has inbound links',
						'Create redirect: old-url → new-url',
						'Use Redirection plugin or .htaccess',
					),
					'Remove Link' => array(
						'If target page permanently gone',
						'Remove link but keep text',
						'Or: Link to related content instead',
					),
				),
				'prevention'              => array(
					__( 'Install Broken Link Checker plugin' ),
					__( 'Weekly email reports of new broken links' ),
					__( 'Before deleting pages: Search for internal links' ),
					__( 'Use 301 redirects when changing URLs' ),
					__( 'Monthly link audits' ),
				),
			),
		);
	}

	/**
	 * Scan for broken internal links.
	 *
	 * @since  1.2601.2148
	 * @return array Link scan results.
	 */
	private static function scan_for_broken_links() {
		global $wpdb;

		// Sample check: Look for links to non-existent post IDs
		$results = $wpdb->get_results(
			"SELECT ID, post_content 
			FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page') 
			LIMIT 100"
		);

		$broken_count = 0;
		$home_url     = home_url();

		foreach ( $results as $post ) {
			// Extract internal links
			preg_match_all( '/<a[^>]+href=["\'](' . preg_quote( $home_url, '/' ) . '[^"\']*)["\'][^>]*>/i', $post->post_content, $matches );

			if ( ! empty( $matches[1] ) ) {
				foreach ( $matches[1] as $url ) {
					// Check if URL exists
					$post_id = url_to_postid( $url );
					if ( $post_id === 0 ) {
						// Might be broken (or custom URL)
						$broken_count++;
					}
				}
			}
		}

		return array(
			'broken_count' => min( $broken_count, 100 ), // Cap at reasonable number
		);
	}
}
