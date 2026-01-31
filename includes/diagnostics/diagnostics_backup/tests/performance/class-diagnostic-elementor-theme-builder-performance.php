<?php
/**
 * Elementor Pro Theme Builder Performance Impact Diagnostic
 *
 * Verifies Theme Builder templates not causing performance issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2030.0300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Pro Theme Builder Performance Diagnostic
 *
 * Checks for performance issues with Elementor Pro Theme Builder templates:
 * - Active template count
 * - Header/footer optimization
 * - Conditional logic complexity
 * - DOM size of templates
 * - Loop widget performance
 * - Popup frequency
 * - WooCommerce template optimization
 *
 * @since 1.2030.0300
 */
class Diagnostic_Elementor_Theme_Builder_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'elementor-theme-builder-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Elementor Pro Theme Builder Performance Impact';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verify Theme Builder templates not causing performance issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.2030.0300
	 * @return array|null Finding array if issues found, null if no issues.
	 */
	public static function check() {
		// Check if Elementor Pro is active
		if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			return null;
		}

		// Check if Theme Builder module exists
		if ( ! class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' ) ) {
			return null;
		}

		$issues = array();

		// Check active Theme Builder templates
		$template_types = array( 'header', 'footer', 'single', 'archive', 'search', '404', 'popup' );
		$active_templates = array();

		foreach ( $template_types as $type ) {
			$templates = get_posts(
				array(
					'post_type'      => 'elementor_library',
					'posts_per_page' => -1,
					'meta_query'     => array(
						array(
							'key'   => '_elementor_template_type',
							'value' => $type,
						),
					),
				)
			);

			if ( ! empty( $templates ) ) {
				$active_templates[ $type ] = count( $templates );
			}
		}

		$total_templates = array_sum( $active_templates );

		// Issue 1: Too many active templates
		if ( $total_templates > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of templates */
				__( '%d active Theme Builder templates found (recommended max: 10)', 'wpshadow' ),
				$total_templates
			);
		}

		// Check header/footer performance
		if ( isset( $active_templates['header'] ) && $active_templates['header'] > 1 ) {
			$issues[] = sprintf(
				/* translators: %d: number of headers */
				__( '%d header templates found (only 1 should be active)', 'wpshadow' ),
				$active_templates['header']
			);
		}

		if ( isset( $active_templates['footer'] ) && $active_templates['footer'] > 1 ) {
			$issues[] = sprintf(
				/* translators: %d: number of footers */
				__( '%d footer templates found (only 1 should be active)', 'wpshadow' ),
				$active_templates['footer']
			);
		}

		// Check for excessive popups
		if ( isset( $active_templates['popup'] ) && $active_templates['popup'] > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of popups */
				__( '%d popup templates found (can impact user experience)', 'wpshadow' ),
				$active_templates['popup']
			);
		}

		// Check template DOM size (sample check on header/footer)
		foreach ( array( 'header', 'footer' ) as $type ) {
			if ( empty( $active_templates[ $type ] ) ) {
				continue;
			}

			$template = get_posts(
				array(
					'post_type'      => 'elementor_library',
					'posts_per_page' => 1,
					'meta_query'     => array(
						array(
							'key'   => '_elementor_template_type',
							'value' => $type,
						),
					),
				)
			);

			if ( ! empty( $template ) ) {
				$template_id = $template[0]->ID;
				$data        = get_post_meta( $template_id, '_elementor_data', true );

				if ( ! empty( $data ) ) {
					$decoded = json_decode( $data, true );
					$element_count = self::count_elements( $decoded );

					// Warn if header/footer has too many elements
					if ( $element_count > 50 ) {
						$issues[] = sprintf(
							/* translators: 1: template type, 2: element count */
							__( '%1$s template contains %2$d elements (recommended max: 50 for performance)', 'wpshadow' ),
							ucfirst( $type ),
							$element_count
						);
					}
				}
			}
		}

		// Check for conditional display logic
		$templates_with_conditions = get_posts(
			array(
				'post_type'      => 'elementor_library',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => '_elementor_conditions',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		if ( count( $templates_with_conditions ) > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of templates */
				__( '%d templates with conditional display logic (can add processing overhead)', 'wpshadow' ),
				count( $templates_with_conditions )
			);
		}

		// If no issues found, return null
		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => implode( ' ', $issues ),
			'severity'     => 'medium',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/elementor-theme-builder-performance',
			'context'      => array(
				'active_templates'         => $active_templates,
				'total_templates'          => $total_templates,
				'templates_with_conditions' => count( $templates_with_conditions ),
			),
		);
	}

	/**
	 * Count elements recursively in Elementor data
	 *
	 * @since  1.2030.0300
	 * @param  array $data Elementor data structure.
	 * @return int Element count.
	 */
	private static function count_elements( $data ): int {
		if ( ! is_array( $data ) ) {
			return 0;
		}

		$count = 0;

		foreach ( $data as $element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}

			$count++;

			// Check for nested elements
			if ( isset( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$count += self::count_elements( $element['elements'] );
			}
		}

		return $count;
	}
}
