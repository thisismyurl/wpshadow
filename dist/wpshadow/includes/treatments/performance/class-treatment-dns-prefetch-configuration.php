<?php
/**
 * DNS Prefetch Configuration Treatment
 *
 * Issue #4936: No DNS Prefetch for External Resources
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if DNS prefetch hints are configured.
 * DNS lookups add 20-120ms latency for external resources.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_DNS_Prefetch_Configuration Class
 *
 * @since 0.6093.1200
 */
class Treatment_DNS_Prefetch_Configuration extends Treatment_Base {

	protected static $slug = 'dns-prefetch-configuration';
	protected static $title = 'No DNS Prefetch for External Resources';
	protected static $description = 'Checks if DNS prefetch hints are configured for external domains';
	protected static $family = 'performance';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_DNS_Prefetch_Configuration' );
	}
}
