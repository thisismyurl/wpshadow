<?php
/**
 * Forum or Community Platform Treatment
 *
 * Tests whether the site maintains a dedicated community platform (forum, Discord, Slack) with active users.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Forum or Community Platform Treatment Class
 *
 * Active community platforms increase user retention by 300% and lifetime value
 * by 500%. Dedicated community spaces foster authentic connections.
 *
 * @since 1.6093.1200
 */
class Treatment_Maintains_Community_Platform extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'maintains-community-platform';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Forum or Community Platform';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site maintains a dedicated community platform (forum, Discord, Slack) with active users';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'community-building';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Maintains_Community_Platform' );
	}
}
