<?php
/**
 * Cache Poisoning Not Prevented Treatment
 *
 * Checks cache poisoning.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Cache_Poisoning_Not_Prevented Class
 *
 * Performs treatment check for Cache Poisoning Not Prevented.
 *
 * @since 1.6033.2033
 */
class Treatment_Cache_Poisoning_Not_Prevented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'cache-poisoning-not-prevented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Cache Poisoning Not Prevented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks cache poisoning';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'prevent_cache_poisoning' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Cache poisoning not prevented. Validate cache keys and use cache key namespacing to prevent collision attacks.',
						'severity'   =>   'high',
						'threat_level'   =>   65,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/cache-poisoning-not-prevented'
						);
						);,
						);
						}
						return null;
						}
						return null;
						}
						return null;
	}
}
