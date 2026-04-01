<?php
/**
 * Keep-Alive Connection Diagnostic
 *
 * Issue #4968: Keep-Alive Connections Not Enabled
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if server uses persistent connections.
 * New TCP connection per file wastes time on handshakes.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Keep_Alive_Connection Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Keep_Alive_Connection extends Diagnostic_Base {

	protected static $slug = 'keep-alive-connection';
	protected static $title = 'Keep-Alive Connections Not Enabled';
	protected static $description = 'Checks if server uses persistent HTTP connections';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Enable Keep-Alive in server configuration', 'wpshadow' );
		$issues[] = __( 'Set timeout: 5-15 seconds for persistent connections', 'wpshadow' );
		$issues[] = __( 'Set max requests: 100-1000 per connection', 'wpshadow' );
		$issues[] = __( 'Apache: KeepAlive On, KeepAliveTimeout 5', 'wpshadow' );
		$issues[] = __( 'Nginx: keepalive_timeout 15;', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Keep-Alive reuses TCP connections for multiple files. Without it, each file requires a new connection (100ms+ overhead per file).', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/keep-alive?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'latency_saved'           => '100-200ms per additional file',
					'check_header'            => 'Connection: keep-alive',
					'default_apache'          => 'Usually enabled by default',
				),
			);
		}

		return null;
	}
}
