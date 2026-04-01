<?php
/**
 * Landmark Regions Unlabeled Treatment
 *
 * Checks if ARIA landmark regions have descriptive labels.
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
 * Landmark Regions Treatment Class
 *
 * Validates that landmark regions (<nav>, <aside>) have descriptive labels.
 *
 * @since 0.6093.1200
 */
class Treatment_Landmark_Regions_Unlabeled extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'landmark-regions-unlabeled';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Landmark Regions Not Labeled';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if ARIA landmark regions have descriptive labels';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Landmark_Regions_Unlabeled' );
	}
}
