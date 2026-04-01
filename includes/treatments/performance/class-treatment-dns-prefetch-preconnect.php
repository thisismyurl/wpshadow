<?php
/**
 * DNS Prefetch/Preconnect Headers Treatment
 *
 * Checks if DNS prefetch and preconnect headers are configured to optimize
 * connection establishment with third-party domains.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DNS Prefetch/Preconnect Headers Treatment Class
 *
 * Verifies resource hints configuration:
 * - dns-prefetch for external domains
 * - preconnect for critical domains
 * - Implementation via wp_resource_hints filter
 *
 * @since 0.6093.1200
 */
class Treatment_Dns_Prefetch_Preconnect extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'dns-prefetch-preconnect';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'DNS Prefetch/Preconnect Headers';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for DNS prefetch and preconnect optimization headers';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Dns_Prefetch_Preconnect' );
	}
}
