<?php
/**
 * Focus Indicators Missing Treatment
 *
 * Checks if keyboard focus indicators are visible.
 *
 * @since   1.6035.1400
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Focus Indicators Treatment Class
 *
 * Validates that keyboard focus is visible (not hidden with outline:none).
 *
 * @since 1.6035.1400
 */
class Treatment_Focus_Indicators_Missing extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'focus-indicators-missing';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Focus Indicators Not Visible';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if keyboard focus indicators are visible';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check theme CSS files.
		$css_files = array(
			get_template_directory() . '/style.css',
			get_template_directory() . '/assets/css/main.css',
			get_template_directory() . '/css/style.css',
		);

		$outline_none_count    = 0;
		$has_custom_focus      = false;
		$has_focus_styles      = false;

		foreach ( $css_files as $css_file ) {
			if ( ! file_exists( $css_file ) ) {
				continue;
			}

			$content = file_get_contents( $css_file );

			// Check for outline:none on focus.
			if ( preg_match_all( '/:focus[^{]*\{[^}]*outline\s*:\s*none/i', $content, $matches ) ) {
				$outline_none_count += count( $matches[0] );

				// Check if there's a custom focus style (border, box-shadow, background).
				if ( preg_match( '/:focus[^{]*\{[^}]*(?:border|box-shadow|background-color|background)[^:]*:[^;}]+/i', $content ) ) {
					$has_custom_focus = true;
				}
			}

			// Check for any :focus styles.
			if ( preg_match( '/:focus(?:-visible)?[^{]*\{/', $content ) ) {
				$has_focus_styles = true;
			}

			// Check for opacity:0 or visibility:hidden on focus.
			if ( preg_match( '/:focus[^{]*\{[^}]*(?:opacity\s*:\s*0|visibility\s*:\s*hidden)/i', $content ) ) {
				$issues[] = __( 'Theme CSS hides focus with opacity:0 or visibility:hidden', 'wpshadow' );
			}
		}

		if ( $outline_none_count > 0 && ! $has_custom_focus ) {
			$issues[] = sprintf(
				/* translators: %d: number of outline:none instances */
				__( 'Found %d instances of outline:none without replacement focus styles', 'wpshadow' ),
				$outline_none_count
			);
		}

		if ( ! $has_focus_styles ) {
			$issues[] = __( 'No :focus styles found in theme CSS', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your site hides keyboard focus indicators—like walking through a dark room with no flashlight. About 16% of users have motor disabilities that make using a mouse difficult, so they navigate by pressing Tab to move between links and buttons. Without visible focus indicators, they can\'t tell where they are on the page. It\'s like trying to fill out a form you can\'t see—impossible and frustrating.', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/focus-indicators',
			);
		}

		return null;
	}
}
