<?php
/**
 * Reading Settings Performance Impact Treatment
 *
 * Analyzes reading settings (posts per page, feed items) for their impact
 * on site performance and SEO.
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
 * Reading Settings Performance Impact Treatment Class
 *
 * Checks reading settings for performance optimization.
 *
 * @since 1.6093.1200
 */
class Treatment_Reading_Settings_Performance_Impact extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'reading-settings-performance-impact';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Reading Settings Performance Impact';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes reading settings for performance';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the treatment check.
	 *
	 * Checks:
	 * - Posts per page is reasonable (not loading too many)
	 * - RSS feed posts count is appropriate
	 * - Feed excerpt settings are configured
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Reading_Settings_Performance_Impact' );
	}
}
