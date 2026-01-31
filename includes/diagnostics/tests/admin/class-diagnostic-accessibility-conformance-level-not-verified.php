<?php
/**
 * Accessibility Conformance Level Not Verified Diagnostic
 *
 * Checks if accessibility is WCAG compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2335
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibility Conformance Level Not Verified Diagnostic Class
 *
 * Detects missing accessibility verification.
 *
 * @since 1.2601.2335
 */
class Diagnostic_Accessibility_Conformance_Level_Not_Verified extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'accessibility-conformance-level-not-verified';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Accessibility Conformance Level Not Verified';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if accessibility is verified';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2335
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for accessibility plugins
		$a11y_plugins = array(
			'accessible-archives/accessible-archives.php',
			'a11y/a11y.php',
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
				'description'   => __( 'Accessibility compliance has not been verified. Test for WCAG 2.1 AA conformance to ensure inclusive access.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 25,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/accessibility-conformance-level-not-verified',
			);
		}

		return null;
	}
}
