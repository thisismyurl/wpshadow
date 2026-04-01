<?php
/**
 * Accessibility Audit Not Performed Diagnostic
 *
 * Checks if accessibility audit is performed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibility Audit Not Performed Diagnostic Class
 *
 * Detects missing accessibility audit.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Accessibility_Audit_Not_Performed extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'accessibility-audit-not-performed';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Accessibility Audit Not Performed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if accessibility audit is performed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for accessibility audit date
		if ( ! get_option( 'last_accessibility_audit_date' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Accessibility audit is not performed. Conduct WCAG 2.1 AA compliance audit to ensure site is usable by people with disabilities (vision, hearing, mobility, cognitive).', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/accessibility-audit-not-performed?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
