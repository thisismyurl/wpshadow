<?php
/**
 * Cache Poisoning Not Prevented Diagnostic
 *
 * Checks cache poisoning.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
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
 * @since 1.6093.1200
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
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$has_prevention_hook = has_filter( 'init', 'prevent_cache_poisoning' );

		if ( false === $has_prevention_hook ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Cache poisoning safeguards do not appear to be configured yet. Adding cache key validation and cache key namespacing helps protect cached responses from being tampered with.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cache-poisoning-not-prevented',
			);
		}

		return null;
	}
}
