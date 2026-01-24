<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Check if version control is used.
 *
 * @since 1.0.0
 */
class Diagnostic_Dx_Version_Control_Used extends Diagnostic_Base {

	/**
	 * Unique diagnostic slug.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected static string $slug = 'dx-version-control-used';

	/**
	 * Diagnostic title.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected static string $title = 'Version Control System';

	/**
	 * Diagnostic description.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected static string $description = 'Check if the site uses a version control system like Git.';

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
	 * Checks for .git directory or other VCS indicators.
	 *
	 * @since 1.0.0
	 * @return ?array Null if pass, array of findings if fail.
	 */
	public function check(): ?array {
		// Check for .git directory
		$wp_root = dirname( dirname( dirname( __DIR__ ) ) );
		$git_dir = $wp_root . '/.git';

		if ( is_dir( $git_dir ) ) {
			return null; // Git is being used
		}

		// Check for other VCS
		$svn_dir = $wp_root . '/.svn';
		$hg_dir = $wp_root . '/.hg';

		if ( is_dir( $svn_dir ) || is_dir( $hg_dir ) ) {
			return null; // Version control is being used
		}

		// If we get here, no VCS detected
		return Diagnostic_Lean_Checks::build_finding(
			self::$slug,
			self::$title,
			'No version control system detected. Use Git to track changes and enable collaboration.',
			'DeveloperExperience',
			'low',
			'low'
		);
	}
}
