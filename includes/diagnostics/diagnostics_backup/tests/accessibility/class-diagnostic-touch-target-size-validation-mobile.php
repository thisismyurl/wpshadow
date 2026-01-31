<?php
/**
 * Touch Target Size Validation (Mobile) Diagnostic
 *
 * Validates buttons/links meet 44x44px minimum touch target size.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Touch Target Size Validation (Mobile) Class
 *
 * Tests touch target sizes.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Touch_Target_Size_Validation_Mobile extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'touch-target-size-validation-mobile';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Touch Target Size Validation (Mobile)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates buttons/links meet 44x44px minimum touch target size';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$touch_check = self::check_touch_targets();
		
		if ( $touch_check['small_targets_found'] > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of small touch targets */
					__( '%d potential small touch targets detected (WCAG 2.1 requires 44x44px minimum)', 'wpshadow' ),
					$touch_check['small_targets_found']
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/touch-target-size-validation-mobile',
				'meta'         => array(
					'small_targets_found'   => $touch_check['small_targets_found'],
					'buttons_checked'       => $touch_check['buttons_checked'],
					'links_checked'         => $touch_check['links_checked'],
					'common_issues'         => $touch_check['common_issues'],
				),
			);
		}

		return null;
	}

	/**
	 * Check touch target sizes.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_touch_targets() {
		global $wpdb;

		$check = array(
			'small_targets_found' => 0,
			'buttons_checked'     => 0,
			'links_checked'       => 0,
			'common_issues'       => array(),
		);

		// Get homepage HTML.
		$response = wp_remote_get( get_home_url(), array( 'timeout' => 10 ) );
		
		if ( is_wp_error( $response ) ) {
			return $check;
		}

		$html = wp_remote_retrieve_body( $response );

		// Check for problematic patterns indicating small touch targets.
		$problematic_patterns = array(
			// Small text links.
			'/<a[^>]*style=["\'][^"\']*font-size\s*:\s*([0-9]+)px[^"\']*["\'][^>]*>/i' => array(
				'threshold' => 12,
				'issue'     => __( 'Small text links (likely <44px touch targets)', 'wpshadow' ),
			),
			// Close-together navigation items.
			'/<nav[^>]*>.*?<a[^>]*>.*?<\/a>\s*<a[^>]*>/is' => array(
				'threshold' => 0,
				'issue'     => __( 'Adjacent navigation links (may lack spacing)', 'wpshadow' ),
			),
			// Small buttons.
			'/<button[^>]*style=["\'][^"\']*padding\s*:\s*([0-9]+)px[^"\']*["\'][^>]*>/i' => array(
				'threshold' => 8,
				'issue'     => __( 'Small button padding (likely <44px)', 'wpshadow' ),
			),
		);

		foreach ( $problematic_patterns as $pattern => $config ) {
			preg_match_all( $pattern, $html, $matches );
			
			if ( ! empty( $matches[0] ) ) {
				$count = 0;
				
				foreach ( $matches[0] as $index => $match ) {
					if ( isset( $config['threshold'] ) && isset( $matches[1][ $index ] ) ) {
						$value = (int) $matches[1][ $index ];
						if ( $value < $config['threshold'] ) {
							++$count;
						}
					} else {
						++$count;
					}
				}

				if ( $count > 0 ) {
					$check['small_targets_found'] += $count;
					
					if ( ! in_array( $config['issue'], $check['common_issues'], true ) ) {
						$check['common_issues'][] = $config['issue'];
					}
				}
			}
		}

		return $check;
	}
}
