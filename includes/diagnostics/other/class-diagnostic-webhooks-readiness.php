<?php
declare(strict_types=1);
/**
 * Webhooks Readiness Diagnostic
 *
 * Philosophy: Encourage automation and integrations; points to Pro workflows.
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if REST API and permalinks are ready for webhook-based workflows.
 */
class Diagnostic_Webhooks_Readiness extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$issues = array();

		// Check REST API availability
		$response = wp_remote_get(
			rest_url(),
			array(
				'timeout'   => 6,
				'sslverify' => false,
			)
		);
		if ( is_wp_error( $response ) || (int) wp_remote_retrieve_response_code( $response ) >= 400 ) {
			$issues[] = 'REST API not reachable; webhooks and integrations may fail';
		}

		// Check permalinks (pretty permalinks recommended for REST)
		$permalink_structure = get_option( 'permalink_structure' );
		if ( empty( $permalink_structure ) ) {
			$issues[] = 'Pretty permalinks are disabled; enable them for reliable webhook endpoints';
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'title'        => 'Webhooks Not Ready',
			'description'  => implode( '. ', $issues ) . '.',
			'severity'     => 'medium',
			'category'     => 'workflows',
			'kb_link'      => 'https://wpshadow.com/kb/enable-wordpress-rest-api/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=webhooks',
			'auto_fixable' => false,
			'threat_level' => 40,
		);
	}
}
