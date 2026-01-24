<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: Permalink Structure Configured
 *
 * Category: Site Configuration
 * Priority: 2
 * Philosophy: 1
 *
 * Test Description:
 * Is a proper permalink structure configured?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 4 implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Permalink_Structure_Configured extends Diagnostic_Base
{
	protected static $slug = 'permalink-structure-configured';
	protected static $title = 'Permalink Structure Configured';
	protected static $description = 'Is a proper permalink structure configured?';
	protected static $category = 'Site Configuration';
	protected static $threat_level = 'low';
	protected static $family = 'general';
	protected static $family_label = 'General';

	/**
	 * Run the diagnostic check
	 *
	 * @return ?array Null if pass, array of findings if fail
	 */
	public function check(): ?array
	{
		// Get permalink structure
		$permalink_structure = get_option('permalink_structure');

		// Empty or default (just ?p=123) is not optimal
		if (empty($permalink_structure) || $permalink_structure === '?p=%post_id%') {
			return Diagnostic_Lean_Checks::build_finding(
				'permalink-structure-configured',
				'Default Permalink Structure',
				'You are using plain URLs or post IDs. Consider using a more SEO-friendly structure like /%postname%/.',
				'Site Configuration',
				'low',
				'low'
			);
		}

		return null;
	}
}
