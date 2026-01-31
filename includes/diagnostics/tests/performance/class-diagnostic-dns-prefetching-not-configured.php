<?php
/**
 * DNS Prefetching Not Configured Diagnostic
 *
 * Checks if DNS prefetching is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DNS Prefetching Not Configured Diagnostic Class
 *
 * Detects missing DNS prefetch hints.
 *
 * @since 1.2601.2352
 */
class Diagnostic_DNS_Prefetching_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dns-prefetching-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'DNS Prefetching Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if DNS prefetching is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for DNS prefetch link tags
		if ( ! has_filter( 'wp_head', 'add_dns_prefetch_links' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'DNS prefetching is not configured. Add dns-prefetch hints for external domains to improve page load speed.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/dns-prefetching-not-configured',
			);
		}

		return null;
	}
}
