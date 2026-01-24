<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Search Engine Visibility
 *
 * Category: Compliance & Legal Risk
 * Priority: 2
 * Philosophy: 1
 *
 * Test Description:
 * Is the site visible to search engines?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 4 implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Search_Engine_Visibility extends Diagnostic_Base
{
	protected static $slug = 'search-engine-visibility';
	protected static $title = 'Search Engine Visibility';
	protected static $description = 'Is the site visible to search engines?';
	protected static $category = 'Compliance & Legal Risk';
	protected static $threat_level = 'high';
	protected static $family = 'general';
	protected static $family_label = 'General';

	/**
	 * Run the diagnostic check
	 *
	 * @return ?array Null if pass, array of findings if fail
	 */
	public function check(): ?array
	{
		// Check if site is blocking search engines
		$blog_public = get_option('blog_public');

		if (0 === (int) $blog_public) {
			return Diagnostic_Lean_Checks::build_finding(
				'search-engine-visibility',
				'Site Hidden from Search Engines',
				'Your site is set to block search engine crawlers. This will prevent organic discovery. To fix: Settings → Reading → Search Engine Visibility',
				'Compliance & Legal Risk',
				'high',
				'high'
			);
		}

		return null;
	}
}
