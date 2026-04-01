<?php
/**
 * Elementor Pro Theme Builder Performance Impact Diagnostic
 *
 * Checks if Elementor Pro Theme Builder templates are causing performance issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Pro Theme Builder Performance Diagnostic Class
 *
 * Verifies Theme Builder templates are not causing site-wide performance issues.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Elementor_Pro_Theme_Builder_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'elementor-pro-theme-builder-performance';

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
	protected static $description = 'Verifies Theme Builder templates not causing performance issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if Elementor Pro is active and Theme Builder is available.
		if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) || ! class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' ) ) {
			return null; // Plugin not active or Theme Builder not available.
		}

		$issues = array();

		// Get Theme Builder templates.
		$templates = get_posts(
			array(
				'post_type'      => 'elementor_library',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'meta_query'     => array(
					array(
						'key'     => '_elementor_template_type',
						'value'   => array( 'header', 'footer', 'single', 'archive', 'popup' ),
						'compare' => 'IN',
					),
				),
			)
		);

		if ( empty( $templates ) ) {
			return null; // No Theme Builder templates active.
		}

		// Analyze each template.
		foreach ( $templates as $template ) {
			$template_type = get_post_meta( $template->ID, '_elementor_template_type', true );
			$elementor_data = get_post_meta( $template->ID, '_elementor_data', true );

			if ( empty( $elementor_data ) ) {
				continue;
			}

			$data = json_decode( $elementor_data, true );
			if ( ! is_array( $data ) ) {
				continue;
			}

			// Count widgets.
			$widget_count = self::count_widgets_recursively( $data );

			// Check for excessive widgets.
			$max_widgets = array(
				'header' => 30,
				'footer' => 30,
				'single' => 50,
				'archive' => 40,
				'popup'  => 20,
			);

			$limit = isset( $max_widgets[ $template_type ] ) ? $max_widgets[ $template_type ] : 50;
			if ( $widget_count > $limit ) {
				$issues[] = array(
					'template_id'   => $template->ID,
					'template_name' => $template->post_title,
					'template_type' => $template_type,
					'issue_type'    => 'excessive_widgets',
					'widget_count'  => $widget_count,
					'recommended'   => $limit,
					'description'   => sprintf(
						/* translators: 1: template type, 2: widget count, 3: recommended limit */
						__( '%1$s template has %2$d widgets (recommended: %3$d)', 'wpshadow' ),
						ucfirst( $template_type ),
						$widget_count,
						$limit
					),
					'severity'      => 'high',
				);
			}

			// Check for conditional logic (display conditions).
			$conditions = get_post_meta( $template->ID, '_elementor_conditions', true );
			if ( ! empty( $conditions ) && is_array( $conditions ) && count( $conditions ) > 5 ) {
				$issues[] = array(
					'template_id'    => $template->ID,
					'template_name'  => $template->post_title,
					'template_type'  => $template_type,
					'issue_type'     => 'excessive_conditions',
					'condition_count' => count( $conditions ),
					'description'    => sprintf(
						/* translators: %d: number of display conditions */
						__( 'Template has %d display conditions, may cause performance overhead', 'wpshadow' ),
						count( $conditions )
					),
					'severity'       => 'medium',
				);
			}

			// Check for loop widgets (Posts, Portfolio, etc.).
			$has_loop_widget = self::has_widget_type( $data, array( 'posts', 'portfolio', 'woocommerce-products', 'loop-grid' ) );
			if ( $has_loop_widget ) {
				// Check if query is optimized (not loading too many items).
				$posts_per_page = self::get_loop_posts_per_page( $data );
				if ( $posts_per_page > 12 ) {
					$issues[] = array(
						'template_id'    => $template->ID,
						'template_name'  => $template->post_title,
						'template_type'  => $template_type,
						'issue_type'     => 'large_loop_query',
						'posts_per_page' => $posts_per_page,
						'description'    => sprintf(
							/* translators: %d: posts per page */
							__( 'Loop widget loading %d items per page (recommended: 12 or fewer)', 'wpshadow' ),
							$posts_per_page
						),
						'severity'       => 'medium',
					);
				}
			}

			// Check popup frequency (if popup template).
			if ( 'popup' === $template_type ) {
				$popup_settings = get_post_meta( $template->ID, '_elementor_page_settings', true );
				if ( is_array( $popup_settings ) ) {
					$triggers = isset( $popup_settings['triggers'] ) ? $popup_settings['triggers'] : array();
					if ( in_array( 'page_load', $triggers, true ) ) {
						$issues[] = array(
							'template_id'   => $template->ID,
							'template_name' => $template->post_title,
							'template_type' => 'popup',
							'issue_type'    => 'aggressive_popup',
							'description'   => __( 'Popup set to trigger on page load, may annoy users and slow initial load', 'wpshadow' ),
							'severity'      => 'medium',
						);
					}
				}
			}
		}

		if ( empty( $issues ) ) {
			return null; // No issues found.
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of performance issues */
				__( 'Found %d Theme Builder performance issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 75,
			'auto_fixable' => false,
			'details'      => $issues,
			'kb_link'      => 'https://wpshadow.com/kb/elementor-pro-theme-builder-performance?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}

	/**
	 * Count widgets recursively in Elementor data.
	 *
	 * @since 0.6093.1200
	 * @param  array $elements Elementor elements array.
	 * @return int Widget count.
	 */
	private static function count_widgets_recursively( $elements ) {
		$count = 0;
		foreach ( $elements as $element ) {
			if ( isset( $element['elType'] ) && 'widget' === $element['elType'] ) {
				++$count;
			}
			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$count += self::count_widgets_recursively( $element['elements'] );
			}
		}
		return $count;
	}

	/**
	 * Check if data contains specific widget types.
	 *
	 * @since 0.6093.1200
	 * @param  array $elements    Elementor elements array.
	 * @param  array $widget_types Widget types to search for.
	 * @return bool True if found, false otherwise.
	 */
	private static function has_widget_type( $elements, $widget_types ) {
		foreach ( $elements as $element ) {
			if ( isset( $element['widgetType'] ) && in_array( $element['widgetType'], $widget_types, true ) ) {
				return true;
			}
			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				if ( self::has_widget_type( $element['elements'], $widget_types ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Get posts_per_page setting from loop widgets.
	 *
	 * @since 0.6093.1200
	 * @param  array $elements Elementor elements array.
	 * @return int Maximum posts_per_page found.
	 */
	private static function get_loop_posts_per_page( $elements ) {
		$max = 0;
		foreach ( $elements as $element ) {
			if ( isset( $element['settings']['posts_per_page'] ) ) {
				$max = max( $max, (int) $element['settings']['posts_per_page'] );
			}
			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$max = max( $max, self::get_loop_posts_per_page( $element['elements'] ) );
			}
		}
		return $max;
	}
}
