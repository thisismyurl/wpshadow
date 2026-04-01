<?php
/**
 * Content Reading Level Too High Treatment
 *
 * Detects when content is too complex for general audiences.
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
 * Content Reading Level Too High Treatment Class
 *
 * 54% of US adults read at or below 8th grade level. Complex content (grade 13+)
 * excludes half your potential audience.
 *
 * @since 0.6093.1200
 */
class Treatment_Content_Reading_Level_Too_High extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-reading-level-too-high';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Reading Level Too High';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detect when content is too complex for general audiences (grade 8-10 target)';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Content_Reading_Level_Too_High' );
	}
}
