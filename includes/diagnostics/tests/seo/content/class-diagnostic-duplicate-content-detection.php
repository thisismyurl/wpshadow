<?php
/**
 * Duplicate Content Detection Diagnostic
 *
 * Identifies duplicate or near-duplicate content that can trigger
 * SEO penalties and confuse search engines about which page to rank.
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
 * Diagnostic_Duplicate_Content_Detection Class
 *
 * Detects duplicate content issues.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Duplicate_Content_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'duplicate-content-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Duplicate Content Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies duplicate or near-duplicate content';

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
	 * @return array|null Finding array if duplicates found, null otherwise.
	 */
	public static function check() {
		$duplicate_check = self::check_for_duplicates();

		if ( $duplicate_check['duplicate_count'] === 0 ) {
			return null; // No duplicates found
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of duplicate content issues */
				__( '%d pages have duplicate content. Google may penalize site or choose wrong page to rank, diluting SEO value.', 'wpshadow' ),
				$duplicate_check['duplicate_count']
			),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/duplicate-content',
			'family'       => self::$family,
			'meta'         => array(
				'duplicate_pages'    => $duplicate_check['duplicate_count'],
				'seo_penalty_risk'   => __( 'Google may filter or penalize duplicate content' ),
				'ranking_dilution'   => __( 'Link equity split across duplicates' ),
			),
			'details'      => array(
				'why_duplicates_bad'      => array(
					__( 'Google shows only 1 version, hiding others' ),
					__( 'Link equity divided instead of concentrated' ),
					__( 'Penguin penalty if excessive duplicates' ),
					__( 'Wasted crawl budget on duplicate pages' ),
				),
				'common_duplicate_sources' => array(
					'WWW vs Non-WWW' => array(
						'Problem: Site accessible at www.site.com AND site.com',
						'Fix: 301 redirect one to the other',
						'Choose: www OR non-www (consistency)',
					),
					'HTTP vs HTTPS' => array(
						'Problem: Both http:// and https:// versions exist',
						'Fix: 301 redirect all HTTP to HTTPS',
						'Update canonical URLs',
					),
					'Pagination' => array(
						'Problem: Category page /category/page/2/ duplicates content',
						'Fix: Use rel="prev/next" or canonical to page 1',
					),
					'Print Versions' => array(
						'Problem: /article/?print=1 is duplicate',
						'Fix: Add noindex meta tag to print pages',
					),
					'Tag/Category Archives' => array(
						'Problem: Same post appears on multiple tag pages',
						'Fix: Canonical tag pointing to main post',
					),
				),
				'detecting_duplicates'    => array(
					'Google Search Console' => array(
						'Coverage report → "Duplicate without canonical"',
						'Shows Google-detected duplicates',
						'Free, authoritative source',
					),
					'Copyscape Premium' => array(
						'Scans for plagiarized content',
						'Also detects internal duplicates',
						'Cost: $0.05/search',
					),
					'Screaming Frog' => array(
						'Desktop tool for crawling site',
						'Finds duplicate titles, content',
						'Free for 500 URLs',
					),
				),
				'fixing_duplicates'       => array(
					'301 Redirect (Best)' => array(
						'Redirect duplicate to canonical version',
						'Passes link equity to canonical',
						'Use: Redirection plugin or .htaccess',
					),
					'Canonical Tag' => array(
						'<link rel="canonical" href="main-url">',
						'Tells Google which version is master',
						'Yoast SEO adds automatically',
					),
					'Noindex Tag' => array(
						'Add to duplicate: <meta name="robots" content="noindex">',
						'Removes from search results',
						'Keep internal links working',
					),
				),
				'prevention'              => array(
					__( 'Choose www vs non-www, redirect other' ),
					__( 'Force HTTPS with 301 redirects' ),
					__( 'Use canonical tags on all pages' ),
					__( 'Noindex tag/category archives if thin content' ),
					__( 'Consolidate similar pages into one comprehensive page' ),
				),
			),
		);
	}

	/**
	 * Check for duplicate content.
	 *
	 * @since  1.2601.2148
	 * @return array Duplicate content results.
	 */
	private static function check_for_duplicates() {
		global $wpdb;

		// Check for duplicate titles (simple indicator)
		$duplicates = $wpdb->get_var(
			"SELECT COUNT(*) FROM (
				SELECT post_title, COUNT(*) as cnt 
				FROM {$wpdb->posts} 
				WHERE post_status = 'publish' 
				AND post_type IN ('post', 'page')
				GROUP BY post_title 
				HAVING cnt > 1
			) as dupes"
		);

		return array(
			'duplicate_count' => (int) $duplicates,
		);
	}
}
