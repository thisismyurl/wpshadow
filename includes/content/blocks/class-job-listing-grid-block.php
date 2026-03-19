<?php
/**
 * Job Listing Grid Block
 *
 * Displays a grid of job postings with filtering and search.
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
 * Job Listing Grid Block Class
 *
 * Registers and manages the job listing grid block.
 *
 * @since 1.6093.1200
 */
class Job_Listing_Grid_Block extends Hook_Subscriber_Base {

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
	 * Register the job listing grid block.
	 *
	 * @since 1.6093.1200
	 */
	public static function register_block(): void {
		register_block_type(
			'wpshadow/job-listing-grid',
			array(
				'render_callback' => array( __CLASS__, 'render_block' ),
				'attributes'      => array(
					'columns'       => array(
						'type'    => 'number',
						'default' => 3,
					),
					'postsPerPage'  => array(
						'type'    => 'number',
						'default' => 12,
					),
					'orderBy'       => array(
						'type'    => 'string',
						'default' => 'date',
					),
					'order'         => array(
						'type'    => 'string',
						'default' => 'DESC',
					),
					'categories'    => array(
						'type'    => 'array',
						'default' => array(),
					),
					'jobTypes'      => array(
						'type'    => 'array',
						'default' => array(),
					),
					'featured'      => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'showPagination' => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showSearch'    => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showFilters'   => array(
						'type'    => 'boolean',
						'default' => true,
					),
				),
				'editor_script'   => 'wpshadow-job-listing-grid-editor',
				'editor_style'    => 'wpshadow-job-listing-grid-editor-style',
				'style'           => 'wpshadow-job-listing-grid-style',
			)
		);
	}

	/**
	 * Render the job listing grid block.
	 *
	 * @since 1.6093.1200
	 * @param  array $attributes Block attributes.
	 * @return string Rendered block HTML.
	 */
	public static function render_block( $attributes ) {
		$columns = absint( $attributes['columns'] ?? 3 );
		$per_page = absint( $attributes['postsPerPage'] ?? 12 );
		$order_by = sanitize_text_field( $attributes['orderBy'] ?? 'date' );
		$order = sanitize_text_field( $attributes['order'] ?? 'DESC' );
		$featured = (bool) ( $attributes['featured'] ?? false );

		$args = array(
			'post_type'      => 'wps_job_posting',
			'posts_per_page' => $per_page,
			'orderby'        => $order_by,
			'order'          => strtoupper( $order ),
			'paged'          => max( 1, get_query_var( 'paged' ) ),
		);

		// Filter by category
		if ( ! empty( $attributes['categories'] ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'wps_job_category',
					'field'    => 'term_id',
					'terms'    => $attributes['categories'],
				),
			);
		}

		// Filter by job type
		if ( ! empty( $attributes['jobTypes'] ) ) {
			if ( isset( $args['tax_query'] ) ) {
				$args['tax_query'][] = array(
					'taxonomy' => 'wps_job_type',
					'field'    => 'term_id',
					'terms'    => $attributes['jobTypes'],
				);
			} else {
				$args['tax_query'] = array(
					array(
						'taxonomy' => 'wps_job_type',
						'field'    => 'term_id',
						'terms'    => $attributes['jobTypes'],
					),
				);
			}
		}

		// Filter by featured only
		if ( $featured ) {
			$args['meta_query'] = array(
				array(
					'key'   => '_wps_job_featured',
					'value' => '1',
				),
			);
		}

		$jobs = new \WP_Query( $args );

		$html = '<div class="wpshadow-job-listing-grid">';

		// Search form
		if ( $attributes['showSearch'] ?? true ) {
			$html .= '<div class="wpshadow-job-search">';
			$html .= '<input type="text" class="wpshadow-job-search-input" placeholder="' . esc_attr__( 'Search jobs...', 'wpshadow' ) . '">';
			$html .= '</div>';
		}

		// Filters
		if ( $attributes['showFilters'] ?? true ) {
			$html .= '<div class="wpshadow-job-filters">';

			// Category filter
			$categories = get_terms( array(
				'taxonomy' => 'wps_job_category',
				'hide_empty' => true,
			) );
			if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
				$html .= '<div class="wpshadow-job-filter-group">';
				$html .= '<label>' . esc_html__( 'Category', 'wpshadow' ) . '</label>';
				$html .= '<select class="wpshadow-job-filter-category">';
				$html .= '<option value="">' . esc_html__( 'All Categories', 'wpshadow' ) . '</option>';
				foreach ( $categories as $category ) {
					$html .= sprintf(
						'<option value="%s">%s</option>',
						esc_attr( $category->term_id ),
						esc_html( $category->name )
					);
				}
				$html .= '</select>';
				$html .= '</div>';
			}

			// Job type filter
			$types = get_terms( array(
				'taxonomy' => 'wps_job_type',
				'hide_empty' => true,
			) );
			if ( ! empty( $types ) && ! is_wp_error( $types ) ) {
				$html .= '<div class="wpshadow-job-filter-group">';
				$html .= '<label>' . esc_html__( 'Job Type', 'wpshadow' ) . '</label>';
				$html .= '<select class="wpshadow-job-filter-type">';
				$html .= '<option value="">' . esc_html__( 'All Types', 'wpshadow' ) . '</option>';
				foreach ( $types as $type ) {
					$html .= sprintf(
						'<option value="%s">%s</option>',
						esc_attr( $type->term_id ),
						esc_html( $type->name )
					);
				}
				$html .= '</select>';
				$html .= '</div>';
			}

			$html .= '</div>';
		}

		// Job grid
		$html .= sprintf(
			'<div class="wpshadow-job-grid wpshadow-job-grid-columns-%d">',
			absint( $columns )
		);

		if ( $jobs->have_posts() ) {
			while ( $jobs->have_posts() ) {
				$jobs->the_post();
				$html .= self::render_job_card();
			}
			wp_reset_postdata();
		} else {
			$html .= '<p>' . esc_html__( 'No job postings found.', 'wpshadow' ) . '</p>';
		}

		$html .= '</div>';

		// Pagination
		if ( $attributes['showPagination'] ?? true ) {
			$html .= self::render_pagination( $jobs );
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Render a single job card.
	 *
	 * @since 1.6093.1200
	 * @return string Rendered job card HTML.
	 */
	private static function render_job_card() {
		$job_id = get_the_ID();

		$html = '<div class="wpshadow-job-card">';

		// Job title
		$html .= '<h3 class="wpshadow-job-card-title">';
		$html .= '<a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a>';
		$html .= '</h3>';

		// Company
		$company = get_post_meta( $job_id, '_wps_job_company_name', true );
		if ( $company ) {
			$html .= '<p class="wpshadow-job-card-company">' . esc_html( $company ) . '</p>';
		}

		// Excerpt
		$html .= '<p class="wpshadow-job-card-excerpt">' . esc_html( wp_trim_words( get_the_excerpt(), 15 ) ) . '</p>';

		// Meta info
		$html .= '<div class="wpshadow-job-card-meta">';

		// Location
		$location = get_post_meta( $job_id, '_wps_job_location', true );
		if ( $location ) {
			$html .= '<span class="wpshadow-job-card-meta-item">📍 ' . esc_html( $location ) . '</span>';
		}

		// Job type
		$types = wp_get_post_terms( $job_id, 'wps_job_type', array( 'fields' => 'names' ) );
		if ( ! empty( $types ) ) {
			$html .= '<span class="wpshadow-job-card-meta-item">' . esc_html( implode( ', ', $types ) ) . '</span>';
		}

		$html .= '</div>';

		// Apply button
		$apply_url = get_post_meta( $job_id, '_wps_job_application_url', true );
		if ( $apply_url ) {
			$html .= sprintf(
				'<a href="%s" class="wpshadow-job-card-apply-button" target="_blank" rel="noopener noreferrer">%s</a>',
				esc_url( $apply_url ),
				esc_html__( 'Apply Now', 'wpshadow' )
			);
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Render pagination.
	 *
	 * @since 1.6093.1200
	 * @param  \WP_Query $query WP_Query object.
	 * @return string Pagination HTML.
	 */
	private static function render_pagination( $query ) {
		$total_pages = $query->max_num_pages;

		if ( $total_pages <= 1 ) {
			return '';
		}

		return wp_kses_post(
			paginate_links( array(
				'total'   => $total_pages,
				'current' => max( 1, get_query_var( 'paged' ) ),
				'type'    => 'list',
			) )
		);
	}
}
