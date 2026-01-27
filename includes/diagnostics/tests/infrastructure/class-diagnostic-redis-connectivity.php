<?php
/**
 * Diagnostic: Redis Connectivity
 *
 * Checks if Redis extension is available and a basic connection can be made.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Infrastructure
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Redis_Connectivity
 *
 * Tests Redis extension availability and simple connectivity.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Redis_Connectivity extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'redis-connectivity';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Redis Connectivity';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks Redis extension availability and connectivity';

	/**
	 * Check Redis connectivity.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! class_exists( '\\Redis' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Redis PHP extension is not available. Object cache backends relying on Redis will not work.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/redis_connectivity',
				'meta'        => array(
					'extension_loaded' => false,
				),
			);
		}

		$host = defined( 'WP_REDIS_HOST' ) ? WP_REDIS_HOST : '127.0.0.1';
		$port = defined( 'WP_REDIS_PORT' ) ? WP_REDIS_PORT : 6379;

		$redis = new \Redis();
		try {
			$connected = $redis->connect( (string) $host, (int) $port, 0.5 );
			if ( ! $connected || 'PONG' !== $redis->ping() ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => __( 'Redis extension is loaded but connection test failed. Verify host/port and Redis server status.', 'wpshadow' ),
					'severity'    => 'medium',
					'threat_level' => 40,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/redis_connectivity',
					'meta'        => array(
						'host'       => $host,
						'port'       => $port,
						'connected'  => $connected,
					),
				);
			}
		} catch ( \Exception $e ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Redis connection threw an exception. Verify server availability and credentials.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/redis_connectivity',
				'meta'        => array(
					'host'       => $host,
					'port'       => $port,
					'exception'  => $e->getMessage(),
				),
			);
		}

		return null;
	}
}
