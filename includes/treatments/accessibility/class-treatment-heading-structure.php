<?php
/**
 * Heading Structure Treatment
 *
 * Checks for proper heading hierarchy (H1-H6) which helps screen reader
 * users navigate page structure and understand content organization.
 *
 * @package    WPShadow
 * @subpackage Treatments\Accessibility
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Heading Structure Treatment Class
 *
 * Verifies proper heading hierarchy and structure.
 * WCAG 2.1 Level A Success Criterion1.0 (Info and Relationships).
 *
 * @since 0.6093.1200
 */
class Treatment_Heading_Structure extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'heading_structure';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Heading Structure';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies proper heading hierarchy (H1-H6)';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Heading_Structure' );
	}
}
