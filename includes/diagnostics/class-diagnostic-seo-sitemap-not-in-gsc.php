<?php declare(strict_types=1);
/**
 * Sitemap Not in GSC Diagnostic
 *
 * Philosophy: SEO indexation - submit sitemap to help discovery
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if sitemap is submitted to GSC.
 */
class Diagnostic_SEO_Sitemap_Not_In_GSC {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		return array(
			'id'          => 'seo-sitemap-not-in-gsc',
			'title'       => 'Submit Sitemap to Search Console',
			'description' => 'Ensure XML sitemap is submitted to Google Search Console. Sitemaps help Google discover and index pages faster. Submit at: Search Console > Sitemaps.',
			'severity'    => 'medium',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/submit-sitemap-gsc/',
			'training_link' => 'https://wpshadow.com/training/sitemap-submission/',
			'auto_fixable' => false,
			'threat_level' => 60,
		);
	}
}
