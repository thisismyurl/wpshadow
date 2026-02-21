<?php
/**
 * Treatment: Missing Meta Descriptions
 *
 * Detects posts without custom meta descriptions.
 * Missing meta = Google writes bad ones, custom increases CTR 20%.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7030.1516
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Missing Meta Descriptions Treatment Class
 *
 * Checks for posts with missing/poor meta descriptions.
 *
 * Detection methods:
 * - Meta field checking (_yoast_wpseo_metadesc, rank_math_description)
 * - Length validation (120-160 characters)
 * - Quality assessment
 *
 * @since 1.7030.1516
 */
class Treatment_Missing_Meta_Descriptions extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-meta-descriptions';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Meta Descriptions';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Missing meta = Google writes bad ones, custom increases CTR 20%';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'keyword-strategy';

	/**
	 * Run the treatment check.
	 *
	 * Scoring system (3 points):
	 * - 1 point: <10% posts missing meta descriptions
	 * - 1 point: <25% posts missing
	 * - 1 point: <50% posts missing
	 *
	 * @since  1.7030.1516
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Missing_Meta_Descriptions' );
	}
}
