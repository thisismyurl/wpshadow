<?php
/**
 * Media Usage Analytics Treatment
 *
 * Tests tracking which posts/pages use specific media.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Usage Analytics Treatment Class
 *
 * Verifies ability to track and report on which posts/pages use specific media.
 *
 * @since 1.6033.0000
 */
class Treatment_Media_Usage_Analytics extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-usage-analytics';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Usage Analytics';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests tracking which posts/pages use specific media';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Usage_Analytics' );
	}
}
