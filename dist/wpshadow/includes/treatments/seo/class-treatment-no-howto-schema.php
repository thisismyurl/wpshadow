<?php
/**
 * Treatment: No HowTo Schema
 *
 * Detects tutorial posts without HowTo schema markup.
 * HowTo schema enables rich results with step-by-step display.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No HowTo Schema Treatment Class
 *
 * Checks tutorial content for HowTo schema.
 *
 * Detection methods:
 * - Tutorial keyword identification
 * - Numbered steps detection
 * - Schema markup checking
 *
 * @since 1.6093.1200
 */
class Treatment_No_HowTo_Schema extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-howto-schema';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No HowTo Schema';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tutorial posts without HowTo schema miss rich results';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'keyword-strategy';

	/**
	 * Run the treatment check.
	 *
	 * Scoring system (4 points):
	 * - 2 points: Schema plugin with HowTo support
	 * - 1 point: Tutorial posts use HowTo schema
	 * - 1 point: <20% tutorial posts missing schema
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_No_HowTo_Schema' );
	}
}
