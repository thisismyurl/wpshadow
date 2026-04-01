<?php
/**
 * Long-Form Content Focus Treatment
 *
 * Verifies site publishes comprehensive long-form content for depth
 * and authority building.
 *
 * @package    WPShadow
 * @subpackage Treatments\Content
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Long-Form Content Focus Treatment Class
 *
 * Analyzes content length distribution to detect strategic use of
 * comprehensive, in-depth articles.
 *
 * **Why This Matters:**
 * - Long-form content (2000+ words) ranks 77% better
 * - Comprehensive posts earn 3.5x more backlinks
 * - Higher engagement and time-on-page
 * - Establishes authority and expertise
 * - Better for featured snippets
 *
 * **Long-Form Benefits:**
 * - More thorough keyword coverage
 * - Higher perceived value
 * - More social shares (75% more)
 * - Better conversion rates
 * - Longer content shelf life
 *
 * @since 0.6093.1200
 */
class Treatment_Publishes_Long_Form_Content extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'publishes-long-form-content';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Long-Form Content Focus';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies site publishes comprehensive long-form content';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the treatment check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if insufficient long-form content, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Publishes_Long_Form_Content' );
	}
}
