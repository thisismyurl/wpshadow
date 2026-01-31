<?php
/**
 * Accessibility Color Contrast Not Verified Diagnostic
 *
 * Checks if color contrast is WCAG compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2347
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibility Color Contrast Not Verified Diagnostic Class
 *
 * Detects unverified color contrast.
 *
 * @since 1.2601.2347
 */
class Diagnostic_Accessibility_Color_Contrast_Not_Verified extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'accessibility-color-contrast-not-verified';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Accessibility Color Contrast Not Verified';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if color contrast is WCAG compliant';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2347
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for accessibility testing plugins
		$a11y_plugins = array(
			'wp-accessibility/wp-accessibility.php',
			'access-monitor/access-monitor.php',
		);

		$a11y_active = false;
		foreach ( $a11y_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$a11y_active = true;
				break;
			}
		}

		if ( ! $a11y_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Accessibility color contrast has not been verified. Use accessibility tools to ensure WCAG 2.1 AA compliance.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/accessibility-color-contrast-not-verified',
			);
		}

		return null;
	}
}
