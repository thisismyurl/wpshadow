<?php
/**
 * No CTAs in Content Treatment
 *
 * Detects posts that lack calls-to-action, missing opportunities for conversion.
 * CTAs are essential for guiding users toward desired actions.
 *
 * @package    WPShadow
 * @subpackage Treatments\Engagement
 * @since      1.6034.2156
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No CTAs in Content Treatment Class
 *
 * Analyzes published content for the presence of calls-to-action. Posts without
 * CTAs represent lost conversion opportunities and reduced engagement.
 *
 * **Why This Matters:**
 * - CTAs increase conversion rates by 83%
 * - No CTA = no direction for readers
 * - Lost leads, subscribers, sales
 * - Reduced engagement metrics
 *
 * **Common CTAs:**
 * - "Subscribe to newsletter"
 * - "Download free guide"
 * - "Start free trial"
 * - "Contact us today"
 * - "Learn more"
 *
 * @since 1.6034.2156
 */
class Treatment_No_CTAs_In_Content extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-ctas-in-content';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No CTAs in Content';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies posts without calls-to-action that could drive conversions';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'engagement';

	/**
	 * Run the treatment check
	 *
	 * @since  1.6034.2156
	 * @return array|null Finding array if posts lack CTAs, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_No_CTAs_In_Content' );
	}
}
