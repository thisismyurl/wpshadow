<?php
/**
 * Theme Accessibility Standards Not Met Diagnostic
 *
 * Checks if theme meets WCAG accessibility standards.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Accessibility Standards Not Met Diagnostic Class
 *
 * Detects accessibility issues in active theme.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Theme_Accessibility_Standards_Not_Met extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-accessibility-standards-not-met';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Accessibility Standards Not Met';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if theme is accessible';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if theme declares accessibility support
		if ( ! current_theme_supports( 'accessibility-ready' ) ) {
			$theme = wp_get_theme();

			// Check theme tags for accessibility declaration
			$tags = $theme->get( 'Tags' );
			if ( empty( $tags ) || ! in_array( 'accessibility-ready', $tags, true ) ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __( 'Theme does not declare accessibility support. Users with disabilities may have difficulty navigating your site.', 'wpshadow' ),
					'severity'      => 'medium',
					'threat_level'  => 50,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/theme-accessibility-standards-not-met',
				);
			}
		}

		return null;
	}
}
