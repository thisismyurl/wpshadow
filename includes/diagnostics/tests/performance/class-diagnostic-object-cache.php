<?php
/**
 * Object Cache Diagnostic
 *
 * Checks whether a persistent object cache (Redis, Memcached) is active
 * to reduce repetitive database queries on every page request.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_Server_Environment_Helper as Server_Env;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Object_Cache Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Object_Cache extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'object-cache';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Object Cache';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether a persistent object cache is active. Without one, WordPress must re-query the database on every request for data that could be served from a fast in-memory cache.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Whether this diagnostic is part of the core trusted set.
	 *
	 * @var bool
	 */
	protected static $is_core = true;

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'high';

	/**
	 * Run the diagnostic check.
	 *
	 * Uses Server_Env to verify whether wp_using_ext_object_cache() returns
	 * true or a persistent object-cache drop-in file is present.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when no persistent cache is detected, null when healthy.
	 */
	public static function check() {
		if ( Server_Env::is_object_cache_enabled() ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No persistent object cache is active. WordPress currently caches objects in memory only for the duration of a single request and must re-query the database on the next request. A persistent cache (Redis, Memcached) avoids redundant database queries and can dramatically reduce response times on high-traffic sites.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'kb_link'      => 'https://wpshadow.com/kb/object-cache?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'persistent_cache_active' => false,
				'note'                    => __( 'Consider Redis Object Cache (plugin) with a Redis server, or Memcached if available from your host.', 'wpshadow' ),
			),
		);
	}
}
