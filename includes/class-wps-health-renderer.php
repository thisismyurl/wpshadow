<?php
/**
 * Health item renderer for standardized health check output.
 *
 * @package WPSHADOW_wpshadow_THISISMYURL
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Health item renderer to eliminate duplication in health check results.
 */
class WPSHADOW_Health_Renderer {
	/**
	 * Build a standardized health check result array.
	 *
	 * @param string $label       Check label.
	 * @param string $status      Status: 'good', 'recommended', 'critical'.
	 * @param string $description Description HTML.
	 * @param string $test        Test identifier.
	 * @param string $actions     Action buttons HTML (optional).
	 * @param string $badge_color Badge color: 'blue', 'orange', 'red', 'gray' (optional, default 'blue').
	 * @return array Formatted health check result.
	 */
	public static function build_result(
		string $label,
		string $status,
		string $description,
		string $test,
		string $actions = '',
		string $badge_color = 'blue'
	): array {
		return array(
			'label'       => $label,
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'WPS Suite', 'plugin-wpshadow' ),
				'color' => $badge_color,
			),
			'description' => sprintf( '<p>%s</p>', $description ),
			'actions'     => $actions,
			'test'        => $test,
		);
	}

	/**
	 * Map status to badge color.
	 *
	 * @param string $status Status string.
	 * @return string Badge color.
	 */
	public static function status_to_color( string $status ): string {
		$map = array(
			'good'        => 'blue',
			'recommended' => 'orange',
			'critical'    => 'red',
		);
		return $map[ $status ] ?? 'gray';
	}
}

/* @changelog Introduce WPSHADOW_Health_Renderer to standardize health check result formatting. */
