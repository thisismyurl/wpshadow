<?php
/**
 * Session Persistence Diagnostic
 *
 * Checks whether user sessions survive server restarts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Reliability
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Session Persistence Diagnostic Class
 *
 * Verifies that sessions are stored in a persistent location.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Session_Persistence extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'session-persistence';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Sessions Lost on Server Restart';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if sessions are stored persistently';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$session_handler = (string) ini_get( 'session.save_handler' );
		$stats['session_handler'] = $session_handler ? $session_handler : 'unknown';

		$has_persistent_sessions = false;

		if ( 'files' !== $session_handler ) {
			$has_persistent_sessions = true;
		}

		if ( class_exists( 'WooCommerce' ) ) {
			$has_persistent_sessions = true;
			$stats['woocommerce_sessions'] = 'enabled';
		}

		$session_plugins = array(
			'redis-cache/redis-cache.php',
			'wp-redis/wp-redis.php',
			'object-cache.php',
			'memcached/memcached.php',
		);

		foreach ( $session_plugins as $plugin_file ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$has_persistent_sessions = true;
				$stats['session_plugin']  = $plugin_file;
				break;
			}
		}

		if ( ! $has_persistent_sessions ) {
			$issues[] = __( 'Sessions appear to be stored in server memory or temporary files', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Server restarts can log visitors out or clear carts if sessions are stored only in memory. Persistent sessions keep shoppers logged in and reduce frustration.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/session-persistence',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
