<?php
/**
 * CTA Placement Issues Treatment
 *
 * Identifies posts with poor call-to-action placement or visibility.
 * CTAs should be strategically positioned throughout content.
 *
 * @package    WPShadow
 * @subpackage Treatments\Engagement
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CTA Placement Issues Treatment Class
 *
 * Analyzes where CTAs appear in content to ensure optimal placement for
 * maximum visibility and conversion potential.
 *
 * **Why This Matters:**
 * - CTA placement affects conversion rates by up to 300%
 * - Above-the-fold CTAs convert 20% better
 * - Multiple CTAs in long content improve conversions
 * - Poor placement = lost opportunities
 *
 * **Optimal CTA Placement:**
 * - Within first 300 words (above the fold)
 * - Middle of long-form content (800+ words)
 * - End of every post
 * - After key value points
 *
 * @since 1.6093.1200
 */
class Treatment_CTA_Placement_Issues extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'cta-placement-issues';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'CTA Placement Issues';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies posts lacking clear calls-to-action in strategic positions';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'engagement';

	/**
	 * Run the treatment check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if posts have CTA placement issues, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_CTA_Placement_Issues' );
	}
}
