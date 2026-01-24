<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

/**
 * Diagnostic: REST API Enabled
 *
 * Category: Environment & Infrastructure
 * Priority: 2
 * Philosophy: 1
 *
 * Test Description:
 * Is the WordPress REST API enabled?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @verified 2026-01-24 - Batch 4 implementation
 * @guardian-integrated Pending
 */
class Diagnostic_Rest_Api_Enabled extends Diagnostic_Base
{
	protected static $slug = 'rest-api-enabled';
	protected static $title = 'REST API Enabled';
	protected static $description = 'Is the WordPress REST API enabled?';
	protected static $category = 'Environment & Infrastructure';
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
		// Check if REST API is disabled globally
		if (defined('REST_API_ENABLED') && ! REST_API_ENABLED) {
			return Diagnostic_Lean_Checks::build_finding(
				'rest-api-enabled',
				'REST API Disabled',
				'The WordPress REST API is disabled. This may impact modern applications and integrations.',
				'Environment & Infrastructure',
				'low',
				'informational'
			);
		}

		// Try to get REST endpoints - if accessible, it's enabled
		$rest_url = rest_url();
		if (! $rest_url) {
			return Diagnostic_Lean_Checks::build_finding(
				'rest-api-enabled',
				'REST API Not Accessible',
				'The WordPress REST API is not accessible. Check your site configuration.',
				'Environment & Infrastructure',
				'low',
				'low'
			);
		}

		return null;
	}
}
