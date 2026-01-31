<?php
/**
 * Accessibility Testing Automation Not Configured Diagnostic
 *
 * Checks if accessibility testing is automated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibility Testing Automation Not Configured Diagnostic Class
 *
 * Detects missing accessibility testing automation.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Accessibility_Testing_Automation_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'accessibility-testing-automation-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Accessibility Testing Automation Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if accessibility testing is automated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for accessibility monitoring
		if ( ! has_filter( 'wp_head', 'wp_accessibility_check' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Accessibility testing is not automated. Implement automated accessibility checks to ensure WCAG compliance across your site.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/accessibility-testing-automation-not-configured',
			);
		}

		return null;
	}
}
