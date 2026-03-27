<?php
/**
 * Advanced Job Search and Filters Block
 *
 * Provides advanced search and filtering for job listings.
 *
 * @package WPShadow
 * @subpackage Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Blocks;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Job Search and Filters Block Class
 *
 * @since 1.6093.1200
 */
class Advanced_Job_Search_Block extends Hook_Subscriber_Base {

	/**
	 * Get hooks to subscribe to.
	 *
	 * @since 1.6093.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'init' => 'register_block',
		);
	}

	/**
	 * Register the advanced job search block.
	 *
	 * @since 1.6093.1200
	 */
	public static function register_block(): void {
		register_block_type(
			'wpshadow/advanced-job-search',
			array(
				'render_callback' => array( __CLASS__, 'render_block' ),
				'attributes'      => array(
					'enableKeyword'      => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'enableLocation'     => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'enableJobType'      => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'enableCategory'     => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'enableExperience'   => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'enableSalary'       => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'buttonText'         => array(
						'type'    => 'string',
						'default' => __( 'Search Jobs', 'wpshadow' ),
					),
				),
				'style'           => 'wpshadow-advanced-job-search-style',
			)
		);
	}

	/**
	 * Render the advanced job search block.
	 *
	 * @since 1.6093.1200
	 * @param  array $attributes Block attributes.
	 * @return string Rendered block HTML.
	 */
	public static function render_block( $attributes ) {
		$html = '<div class="wpshadow-advanced-job-search">';
		$html .= '<form class="wpshadow-job-search-form" method="get">';

		// Keyword Search
		if ( $attributes['enableKeyword'] ?? true ) {
			$html .= '<div class="wpshadow-search-field wpshadow-search-field-keyword">';
			$html .= '<label for="job-search-keyword">' . esc_html__( 'Keyword', 'wpshadow' ) . '</label>';
			$html .= '<input type="text" id="job-search-keyword" name="keyword" placeholder="' . esc_attr__( 'Job title, skills...', 'wpshadow' ) . '" value="' . esc_attr( $_GET['keyword'] ?? '' ) . '">';
			$html .= '</div>';
		}

		// Location Search
		if ( $attributes['enableLocation'] ?? true ) {
			$html .= '<div class="wpshadow-search-field wpshadow-search-field-location">';
			$html .= '<label for="job-search-location">' . esc_html__( 'Location', 'wpshadow' ) . '</label>';
			$html .= '<input type="text" id="job-search-location" name="location" placeholder="' . esc_attr__( 'City, region...', 'wpshadow' ) . '" value="' . esc_attr( $_GET['location'] ?? '' ) . '">';
			$html .= '</div>';
		}

		// Job Type Filter
		if ( $attributes['enableJobType'] ?? true ) {
			$types = get_terms( array(
				'taxonomy'   => 'wps_job_type',
				'hide_empty' => true,
			) );

			if ( ! empty( $types ) && ! is_wp_error( $types ) ) {
				$html .= '<div class="wpshadow-search-field wpshadow-search-field-type">';
				$html .= '<label for="job-search-type">' . esc_html__( 'Job Type', 'wpshadow' ) . '</label>';
				$html .= '<select id="job-search-type" name="job_type">';
				$html .= '<option value="">' . esc_html__( 'All Types', 'wpshadow' ) . '</option>';

				foreach ( $types as $type ) {
					$selected = ( isset( $_GET['job_type'] ) && absint( $_GET['job_type'] ) === $type->term_id ) ? 'selected' : '';
					$html .= sprintf(
						'<option value="%d" %s>%s</option>',
						absint( $type->term_id ),
						$selected,
						esc_html( $type->name )
					);
				}

				$html .= '</select>';
				$html .= '</div>';
			}
		}

		// Category Filter
		if ( $attributes['enableCategory'] ?? true ) {
			$categories = get_terms( array(
				'taxonomy'   => 'wps_job_category',
				'hide_empty' => true,
			) );

			if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
				$html .= '<div class="wpshadow-search-field wpshadow-search-field-category">';
				$html .= '<label for="job-search-category">' . esc_html__( 'Category', 'wpshadow' ) . '</label>';
				$html .= '<select id="job-search-category" name="category">';
				$html .= '<option value="">' . esc_html__( 'All Categories', 'wpshadow' ) . '</option>';

				foreach ( $categories as $category ) {
					$selected = ( isset( $_GET['category'] ) && absint( $_GET['category'] ) === $category->term_id ) ? 'selected' : '';
					$html .= sprintf(
						'<option value="%d" %s>%s</option>',
						absint( $category->term_id ),
						$selected,
						esc_html( $category->name )
					);
				}

				$html .= '</select>';
				$html .= '</div>';
			}
		}

		// Experience Level Filter
		if ( $attributes['enableExperience'] ?? true ) {
			$experience_levels = array(
				'entry'     => __( 'Entry Level', 'wpshadow' ),
				'mid'       => __( 'Mid Level', 'wpshadow' ),
				'senior'    => __( 'Senior', 'wpshadow' ),
				'executive' => __( 'Executive', 'wpshadow' ),
			);

			$html .= '<div class="wpshadow-search-field wpshadow-search-field-experience">';
			$html .= '<label for="job-search-experience">' . esc_html__( 'Experience', 'wpshadow' ) . '</label>';
			$html .= '<select id="job-search-experience" name="experience">';
			$html .= '<option value="">' . esc_html__( 'Any Level', 'wpshadow' ) . '</option>';

			foreach ( $experience_levels as $key => $label ) {
				$selected = ( isset( $_GET['experience'] ) && sanitize_text_field( $_GET['experience'] ) === $key ) ? 'selected' : '';
				$html .= sprintf(
					'<option value="%s" %s>%s</option>',
					esc_attr( $key ),
					$selected,
					esc_html( $label )
				);
			}

			$html .= '</select>';
			$html .= '</div>';
		}

		// Salary Range Filter
		if ( $attributes['enableSalary'] ?? true ) {
			$html .= '<div class="wpshadow-search-field wpshadow-search-field-salary">';
			$html .= '<label>' . esc_html__( 'Salary Range', 'wpshadow' ) . '</label>';
			$html .= '<div class="wpshadow-salary-range">';
			$html .= '<input type="number" id="salary-min" name="salary_min" placeholder="' . esc_attr__( 'Min', 'wpshadow' ) . '" value="' . esc_attr( $_GET['salary_min'] ?? '' ) . '">';
			$html .= '<span class="wpshadow-salary-separator">-</span>';
			$html .= '<input type="number" id="salary-max" name="salary_max" placeholder="' . esc_attr__( 'Max', 'wpshadow' ) . '" value="' . esc_attr( $_GET['salary_max'] ?? '' ) . '">';
			$html .= '</div>';
			$html .= '</div>';
		}

		// Submit Button
		$html .= '<div class="wpshadow-search-field wpshadow-search-actions">';
		$html .= '<button type="submit" class="wpshadow-btn wpshadow-btn-primary">' . esc_html( $attributes['buttonText'] ?? __( 'Search Jobs', 'wpshadow' ) ) . '</button>';
		$html .= '<a href="' . esc_url( get_post_type_archive_link( 'wps_job_posting' ) ) . '" class="wpshadow-btn wpshadow-btn-secondary">' . esc_html__( 'Clear Filters', 'wpshadow' ) . '</a>';
		$html .= '</div>';

		$html .= '</form>';
		$html .= '</div>';

		return $html;
	}
}
