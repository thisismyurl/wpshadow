<?php
/**
 * Diagnostic: PHP Max Input Vars Configuration
 *
 * Checks if PHP max_input_vars allows large form submissions.
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Max_Input_Vars Class
 *
 * Detects if PHP max_input_vars is too restrictive. This setting limits
 * the number of variables PHP will accept in GET, POST, or FILE requests.
 *
 * When exceeded, excess POST data is silently discarded, causing data loss:
 *
 * - Complex custom field pages don't save properly
 * - Bulk editing silently drops changes
 * - ACF field data is lost
 * - Plugin option pages don't save all fields
 * - Import operations fail or lose data
 *
 * This is a hidden data loss vulnerability - the form appears to save,
 * but changes are never recorded.
 *
 * Returns different threat levels based on max_input_vars configuration.
 *
 * @since 1.2601.2200
 */
class Diagnostic_Max_Input_Vars extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @var string
	 */
	protected static $slug = 'max-input-vars';

	/**
	 * Diagnostic title (user-facing)
	 *
	 * @var string
	 */
	protected static $title = 'PHP Max Input Vars';

	/**
	 * Diagnostic description (plain language)
	 *
	 * @var string
	 */
	protected static $description = 'Checks if form submission limits allow complex pages and bulk editing';

	/**
	 * Family grouping for batch operations
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Family label (human-readable)
	 *
	 * @var string
	 */
	protected static $family_label = 'Performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks the current PHP max_input_vars and compares against thresholds:
	 * - Below 1000: Medium priority (common data loss in complex forms)
	 * - 1000-3000: Acceptable but may limit very complex pages
	 * - 3000+: Good (optimal for most sites)
	 *
	 * Note: Default is usually 1000, which is insufficient for complex pages.
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if max_input_vars is too low, null if adequate.
	 */
	public static function check() {
		$max_input_vars = (int) ini_get( 'max_input_vars' );

		// Use PHP 5.3.9+ default of 1000 if not set
		if ( 0 === $max_input_vars ) {
			$max_input_vars = 1000;
		}

		$minimum_recommended = 1000;
		$optimal             = 3000;

		// High: Below 1000 (very restrictive)
		if ( $max_input_vars < $minimum_recommended ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: 1: current max input vars, 2: recommended minimum */
					esc_html__( 'Your PHP max_input_vars is set to %1$d, which is too low. Complex pages with many custom fields may lose data silently. We recommend at least %2$d.', 'wpshadow' ),
					$max_input_vars,
					$minimum_recommended
				),
				'severity'           => 'high',
				'threat_level'       => 60,
				'site_health_status' => 'recommended',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/performance-max-input-vars',
				'family'             => self::$family,
				'details'            => array(
					'current_value'       => $max_input_vars,
					'minimum_recommended' => $minimum_recommended,
					'optimal_value'       => $optimal,
					'risk'                => 'Data loss in complex forms, bulk editing, ACF fields',
					'recommendation'      => 'Contact hosting provider to increase to 3000+',
				),
			);
		}

		// Medium: Between 1000-3000 (acceptable but may limit very complex pages)
		if ( $max_input_vars < $optimal ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: 1: current max input vars, 2: optimal value */
					esc_html__( 'Your PHP max_input_vars is %1$d. While this is acceptable, %2$d or higher is recommended for sites with complex custom fields or bulk editing operations.', 'wpshadow' ),
					$max_input_vars,
					$optimal
				),
				'severity'           => 'medium',
				'threat_level'       => 35,
				'site_health_status' => 'recommended',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/performance-max-input-vars',
				'family'             => self::$family,
				'details'            => array(
					'current_value'       => $max_input_vars,
					'minimum_recommended' => $minimum_recommended,
					'optimal_value'       => $optimal,
					'recommendation'      => 'Consider requesting increase to 3000+ from hosting provider',
				),
			);
		}

		// All good - max input vars is adequate
		return null;
	}
}
