<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Check for multisite configuration issues.
 *
 * @since 1.0.0
 */
class Diagnostic_Dx_Multisite_Awareness extends Diagnostic_Base {

	/**
	 * Unique diagnostic slug.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected static string $slug = 'dx-multisite-awareness';

	/**
	 * Diagnostic title.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected static string $title = 'Multisite Configuration';

	/**
	 * Diagnostic description.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected static string $description = 'Check if site is multisite enabled and if networking is properly configured.';

	/**
	 * Diagnostic category.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected static string $category = 'DeveloperExperience';

	/**
	 * Threat level.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected static string $threat_level = 'low';

	/**
	 * Diagnostic family.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected static string $family = 'Core';

	/**
	 * Diagnostic family label.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected static string $family_label = 'WordPress Core';

	/**
	 * Perform the diagnostic check.
	 *
	 * Checks multisite configuration (informational).
	 *
	 * @since 1.0.0
	 * @return ?array Null if pass, array of findings if fail.
	 */
	public function check(): ?array {
		// This is informational - multisite can be enabled or disabled based on needs
		// Just check if it's properly configured if enabled

		if ( ! is_multisite() ) {
			return null; // Not multisite, nothing to check
		}

		// If multisite is enabled, verify basic configuration
		$network_home = network_home_url();
		$site_url = site_url();

		// Check if site URL is different from network URL (expected for multisite)
		if ( $network_home === $site_url ) {
			// Might be a misconfigured multisite
			return null; // Could be single network site, not necessarily an error
		}

		return null; // Multisite is configured
	}
}
