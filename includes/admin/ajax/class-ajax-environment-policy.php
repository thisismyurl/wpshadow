<?php
/**
 * AJAX: Environment Policy
 *
 * Returns the governance policy active for the current deployment environment,
 * including the detected environment name, allowed readiness states, confidence
 * minimum, auto-fix flag, Core 50 count, and diagnostic tier breakdown.
 *
 * Intended as a lightweight admin-only endpoint for settings page display,
 * governance reporting, and external audit tooling.
 *
 * @package ThisIsMyURL\Shadow
 * @since   0.7055
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Admin\Ajax;

use ThisIsMyURL\Shadow\Core\AJAX_Handler_Base;
use ThisIsMyURL\Shadow\Core\Environment_Detector;
use ThisIsMyURL\Shadow\Core\Readiness_Registry;
use ThisIsMyURL\Shadow\Core\Diagnostic_Metadata;
use ThisIsMyURL\Shadow\Diagnostics\Diagnostic_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Environment policy AJAX handler.
 */
class AJAX_Environment_Policy extends AJAX_Handler_Base {

	/**
	 * Handle environment policy request.
	 *
	 * @return void
	 */
	public static function handle(): void {
		self::verify_manage_options_request( 'thisismyurl_shadow_scan_nonce' );

		$context = Readiness_Registry::get_governance_context();

		// Confidence tier breakdown (counts of shipped diagnostics per tier).
		$confidence_summary = self::build_confidence_summary();

		// Core 50 count from metadata.
		$core_slugs = class_exists( Diagnostic_Metadata::class )
			? Diagnostic_Metadata::get_core_slugs()
			: array();

		self::send_success(
			array(
				'environment'         => $context['environment'],
				'policy'              => $context['policy'],
				'readiness_states'    => $context['readiness_states'],
				'core_count'          => count( $core_slugs ),
				'confidence_summary'  => $confidence_summary,
				'generated_at'        => $context['generated_at'],
			)
		);
	}

	/**
	 * Build a confidence-tier summary over all known diagnostic metadata.
	 *
	 * @return array<string, int>
	 */
	private static function build_confidence_summary(): array {
		$summary = array(
			'high'     => 0,
			'standard' => 0,
			'low'      => 0,
		);

		if ( ! class_exists( Diagnostic_Metadata::class ) ) {
			return $summary;
		}

		$all = Diagnostic_Metadata::get_all();
		foreach ( $all as $slug => $meta ) {
			$tier = $meta['confidence'] ?? 'standard';
			if ( isset( $summary[ $tier ] ) ) {
				$summary[ $tier ]++;
			}
		}

		$summary['total'] = array_sum( $summary );

		return $summary;
	}
}

\add_action(
	'wp_ajax_thisismyurl_shadow_environment_policy',
	array( '\\ThisIsMyURL\\Shadow\\Admin\\Ajax\\AJAX_Environment_Policy', 'handle' )
);
