<?php
declare(strict_types=1);
/**
 * Crawl Errors Diagnostic
 *
 * Philosophy: SEO indexation - fix crawl errors for better discovery
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for common crawl error patterns.
 */
class Diagnostic_SEO_Crawl_Errors extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-crawl-errors',
			'title'       => 'Check for Crawl Errors in GSC',
			'description' => 'Review Google Search Console for crawl errors (404s, server errors, redirect errors). Fix crawl errors to ensure complete site indexing. Check Coverage report.',
			'severity'    => 'medium',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/fix-crawl-errors/',
			'training_link' => 'https://wpshadow.com/training/crawl-optimization/',
			'auto_fixable' => false,
			'threat_level' => 60,
		);
	}
}
