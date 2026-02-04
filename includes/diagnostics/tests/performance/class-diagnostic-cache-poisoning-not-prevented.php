<?php
/**
 * Cache Poisoning Not Prevented Diagnostic
 *
 * Checks cache poisoning.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Cache_Poisoning_Not_Prevented Class
 *
 * Performs diagnostic check for Cache Poisoning Not Prevented.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Cache_Poisoning_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cache-poisoning-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cache Poisoning Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks cache poisoning';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
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
