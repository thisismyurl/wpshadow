<?php
/**
 * Feed Content Length Treatment
 *
 * Checks if the feed content length is within recommended limits.
 *
 * @since   1.6032.1921
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Feed_Content_Length Class
 *
 * Checks if the feed content length is within recommended limits.
 */
class Treatment_Feed_Content_Length extends Treatment_Base {
	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-content-length';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Content Length';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the feed content length is within recommended limits.';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'feed';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6032.1921
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Feed_Content_Length' );
	}
}
