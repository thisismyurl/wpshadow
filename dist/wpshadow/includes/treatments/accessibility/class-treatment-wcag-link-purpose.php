<?php
/**
 * WCAG 2.4.4 Link Purpose Treatment
 *
 * Validates that link text is descriptive and meaningful.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCAG Link Purpose Treatment Class
 *
 * Checks for generic or unclear link text (WCAG 2.4.4 Level A).
 *
 * @since 0.6093.1200
 */
class Treatment_WCAG_Link_Purpose extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'wcag-link-purpose';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Link Purpose (WCAG 2.4.4)';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that link text is descriptive and meaningful';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_WCAG_Link_Purpose' );
	}
}
