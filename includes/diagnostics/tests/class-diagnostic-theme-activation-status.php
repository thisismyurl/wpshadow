<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

/**
 * Diagnostic: Check theme deactivation/update status.
 *
 * @since 1.0.0
 */
class Diagnostic_Theme_Activation_Status extends Diagnostic_Base {

	/**
	 * Unique diagnostic slug.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected static string $slug = 'theme-activation-status';

	/**
	 * Diagnostic title.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected static string $title = 'Active Theme Status';

	/**
	 * Diagnostic description.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected static string $description = 'Verify that the active theme is properly initialized and not in an error state.';

	/**
	 * Diagnostic category.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected static string $category = 'Maintenance';

	/**
	 * Threat level.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected static string $threat_level = 'medium';

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
	 * Checks if active theme exists and is properly set.
	 *
	 * @since 1.0.0
	 * @return ?array Null if pass, array of findings if fail.
	 */
	public function check(): ?array {
		$theme = wp_get_theme();

		// Check if theme exists
		if ( ! $theme->exists() ) {
			return Diagnostic_Lean_Checks::build_finding(
				self::$slug,
				self::$title,
				'Active theme does not exist. Activate a valid theme immediately.',
				'Maintenance',
				'critical',
				'high'
			);
		}

		// Check if theme has valid name
		if ( empty( $theme->get( 'Name' ) ) ) {
			return Diagnostic_Lean_Checks::build_finding(
				self::$slug,
				self::$title,
				'Active theme has invalid or missing metadata. Try re-activating the theme.',
				'Maintenance',
				'high',
				'high'
			);
		}

		// Check if theme's parent theme exists (if child theme)
		$parent = $theme->get( 'Template' );
		if ( ! empty( $parent ) ) {
			$parent_theme = wp_get_theme( $parent );
			if ( ! $parent_theme->exists() ) {
				return Diagnostic_Lean_Checks::build_finding(
					self::$slug,
					self::$title,
					"Child theme is active but parent theme '$parent' does not exist.",
					'Maintenance',
					'critical',
					'high'
				);
			}
		}

		return null;
	}
}
