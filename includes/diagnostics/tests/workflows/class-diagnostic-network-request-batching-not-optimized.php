<?php
/**
 * Network Request Batching Not Optimized Diagnostic
 *
 * Checks if network requests are batched.
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
 * Diagnostic_Network_Request_Batching_Not_Optimized Class
 *
 * Performs diagnostic check for Network Request Batching Not Optimized.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Network_Request_Batching_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'network-request-batching-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Network Request Batching Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if network requests are batched';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if network request batching is implemented
		if ( ! has_filter( 'wp_head', 'batch_network_requests' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Network request batching not optimized. Combine multiple API calls into single batched requests to reduce round trips and improve performance.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/network-request-batching-not-optimized',
			);
		}

		return null;
	}
}
