<?php
/**
 * Featured Jobs Carousel Block
 *
 * Displays featured job postings in an attractive carousel.
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
 * Featured Jobs Carousel Block Class
 *
 * @since 1.6093.1200
 */
class Featured_Jobs_Carousel_Block extends Hook_Subscriber_Base {

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
	 * Register the featured jobs carousel block.
	 *
	 * @since 1.6093.1200
	 */
	public static function register_block(): void {
		register_block_type(
			'wpshadow/featured-jobs-carousel',
			array(
				'render_callback' => array( __CLASS__, 'render_block' ),
				'attributes'      => array(
					'jobsToShow' => array(
						'type'    => 'number',
						'default' => 3,
					),
					'showSalary' => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showLocation' => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showCategory' => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'autoPlay' => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'autoPlayInterval' => array(
						'type'    => 'number',
						'default' => 5000,
					),
				),
				'style'           => 'wpshadow-featured-jobs-carousel-style',
			)
		);
	}

	/**
	 * Render the featured jobs carousel block.
	 *
	 * @since 1.6093.1200
	 * @param  array $attributes Block attributes.
	 * @return string Rendered carousel HTML.
	 */
	public static function render_block( $attributes ) {
		$jobs = get_posts( array(
			'post_type'      => 'wps_job_posting',
			'posts_per_page' => absint( $attributes['jobsToShow'] ?? 3 ),
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'     => 'wps_job_featured',
					'value'   => '1',
					'compare' => '=',
				),
			),
		) );

		if ( empty( $jobs ) ) {
			return '<p>' . esc_html__( 'No featured jobs available at this time.', 'wpshadow' ) . '</p>';
		}

		$carousel_id = 'featured-jobs-carousel-' . wp_rand( 1000, 9999 );

		$html = '<div class="wpshadow-featured-jobs-carousel" id="' . esc_attr( $carousel_id ) . '">';
		$html .= '<div class="wpshadow-carousel-viewport">';
		$html .= '<div class="wpshadow-carousel-track">';

		foreach ( $jobs as $job ) {
			$html .= self::render_job_card( $job, $attributes );
		}

		$html .= '</div>';
		$html .= '</div>';

		// Navigation buttons
		$html .= '<button class="wpshadow-carousel-nav wpshadow-carousel-nav-prev" aria-label="' . esc_attr__( 'Previous job', 'wpshadow' ) . '">';
		$html .= '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>';
		$html .= '</button>';

		$html .= '<button class="wpshadow-carousel-nav wpshadow-carousel-nav-next" aria-label="' . esc_attr__( 'Next job', 'wpshadow' ) . '">';
		$html .= '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>';
		$html .= '</button>';

		// Indicators
		$html .= '<div class="wpshadow-carousel-indicators">';
		for ( $i = 0; $i < count( $jobs ); $i++ ) {
			$html .= '<button class="wpshadow-carousel-indicator' . ( 0 === $i ? ' active' : '' ) . '" aria-label="' . esc_attr( sprintf( __( 'Go to job %d', 'wpshadow' ), $i + 1 ) ) . '"></button>';
		}
		$html .= '</div>';

		$html .= '</div>';

		// Inline script for carousel functionality
		$auto_play = $attributes['autoPlay'] ?? true ? 'true' : 'false';
		$interval = absint( $attributes['autoPlayInterval'] ?? 5000 );

		$html .= '<script type="text/javascript">';
		$html .= "
		(function() {
			const carousel = document.getElementById('" . esc_js( $carousel_id ) . "');
			const track = carousel.querySelector('.wpshadow-carousel-track');
			const cards = track.querySelectorAll('.wpshadow-job-card');
			const prevBtn = carousel.querySelector('.wpshadow-carousel-nav-prev');
			const nextBtn = carousel.querySelector('.wpshadow-carousel-nav-next');
			const indicators = carousel.querySelectorAll('.wpshadow-carousel-indicator');
			
			let currentIndex = 0;
			let autoPlayTimer = null;
			
			function showCard(index) {
				currentIndex = (index + cards.length) % cards.length;
				const offset = -currentIndex * 100;
				track.style.transform = 'translateX(' + offset + '%)';
				
				indicators.forEach((ind, i) => {
					ind.classList.toggle('active', i === currentIndex);
				});
			}
			
			function nextCard() {
				showCard(currentIndex + 1);
			}
			
			function prevCard() {
				showCard(currentIndex - 1);
			}
			
			function startAutoPlay() {
				if (" . $auto_play . ") {
					autoPlayTimer = setInterval(nextCard, " . $interval . ");
				}
			}
			
			function stopAutoPlay() {
				if (autoPlayTimer) {
					clearInterval(autoPlayTimer);
				}
			}
			
			nextBtn.addEventListener('click', nextCard);
			prevBtn.addEventListener('click', prevCard);
			
			indicators.forEach((ind, i) => {
				ind.addEventListener('click', () => showCard(i));
			});
			
			carousel.addEventListener('mouseenter', stopAutoPlay);
			carousel.addEventListener('mouseleave', startAutoPlay);
			
			startAutoPlay();
		})();
		";
		$html .= '</script>';

		return $html;
	}

	/**
	 * Render a single job card.
	 *
	 * @since 1.6093.1200
	 * @param  \WP_Post $job        Job post object.
	 * @param  array    $attributes Block attributes.
	 * @return string Rendered job card HTML.
	 */
	private static function render_job_card( $job, $attributes ) {
		$job_type = wp_get_post_terms( $job->ID, 'wps_job_type' );
		$job_type_label = ! empty( $job_type ) ? $job_type[0]->name : '';

		$location = get_post_meta( $job->ID, 'wps_job_location', true );
		$salary_min = get_post_meta( $job->ID, 'wps_job_salary_min', true );
		$salary_max = get_post_meta( $job->ID, 'wps_job_salary_max', true );

		$salary_text = '';
		if ( ( $attributes['showSalary'] ?? true ) && $salary_min && $salary_max ) {
			$salary_text = sprintf(
				'$%s - $%s',
				number_format( absint( $salary_min ) ),
				number_format( absint( $salary_max ) )
			);
		}

		$html = '<div class="wpshadow-job-card">';
		$html .= '<div class="wpshadow-job-card-header">';
		$html .= '<h3 class="wpshadow-job-card-title"><a href="' . esc_url( get_permalink( $job->ID ) ) . '">' . esc_html( $job->post_title ) . '</a></h3>';

		if ( $job_type_label ) {
			$html .= '<span class="wpshadow-job-type-badge">' . esc_html( $job_type_label ) . '</span>';
		}

		$html .= '</div>';

		$html .= '<div class="wpshadow-job-card-body">';

		if ( ( $attributes['showLocation'] ?? true ) && $location ) {
			$html .= '<p class="wpshadow-job-location"><i class="icon-location"></i> ' . esc_html( $location ) . '</p>';
		}

		if ( $salary_text ) {
			$html .= '<p class="wpshadow-job-salary"><i class="icon-salary"></i> ' . esc_html( $salary_text ) . '</p>';
		}

		if ( ( $attributes['showCategory'] ?? true ) ) {
			$categories = wp_get_post_terms( $job->ID, 'wps_job_category' );
			if ( ! empty( $categories ) ) {
				$html .= '<div class="wpshadow-job-categories">';
				foreach ( $categories as $cat ) {
					$html .= '<span class="wpshadow-category-tag">' . esc_html( $cat->name ) . '</span>';
				}
				$html .= '</div>';
			}
		}

		$html .= '</div>';

		$html .= '<div class="wpshadow-job-card-footer">';
		$html .= '<a href="' . esc_url( get_permalink( $job->ID ) ) . '" class="wpshadow-btn wpshadow-btn-small wpshadow-btn-primary">' . esc_html__( 'View Job', 'wpshadow' ) . '</a>';
		$html .= '</div>';

		$html .= '</div>';

		return $html;
	}
}
