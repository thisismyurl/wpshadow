<?php
/**
 * Job Posting Block
 *
 * Custom Gutenberg block for displaying job postings with rich metadata.
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
 * Job Posting Block Class
 *
 * Registers and manages the job posting block.
 *
 * @since 1.6093.1200
 */
class Job_Posting_Block extends Hook_Subscriber_Base {

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
	 * Register the job posting block.
	 *
	 * @since 1.6093.1200
	 */
	public static function register_block(): void {
		register_block_type(
			'wpshadow/job-posting',
			array(
				'render_callback' => array( __CLASS__, 'render_block' ),
				'attributes'      => array(
					'jobPostId'      => array(
						'type'    => 'number',
						'default' => 0,
					),
					'displaySalary'  => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'displayLocation' => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'displayDeadline' => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'applyButtonText' => array(
						'type'    => 'string',
						'default' => __( 'Apply Now', 'wpshadow' ),
					),
					'applyButtonStyle' => array(
						'type'    => 'string',
						'default' => 'primary',
					),
				),
				'editor_script'   => 'wpshadow-job-posting-block-editor',
				'editor_style'    => 'wpshadow-job-posting-block-editor-style',
				'style'           => 'wpshadow-job-posting-block-style',
			)
		);
	}

	/**
	 * Render the job posting block.
	 *
	 * @since 1.6093.1200
	 * @param  array $attributes Block attributes.
	 * @return string Rendered block HTML.
	 */
	public static function render_block( $attributes ) {
		$job_id = absint( $attributes['jobPostId'] ?? 0 );

		if ( 0 === $job_id ) {
			return '<p>' . esc_html__( 'Select a job posting to display.', 'wpshadow' ) . '</p>';
		}

		$post = get_post( $job_id );
		if ( ! $post || 'wps_job_posting' !== $post->post_type ) {
			return '<p>' . esc_html__( 'Invalid job posting.', 'wpshadow' ) . '</p>';
		}

		$html = '<div class="wpshadow-job-posting-block">';

		// Job Header
		$html .= '<div class="wpshadow-job-header">';
		$html .= '<h3 class="wpshadow-job-title">' . esc_html( get_the_title( $job_id ) ) . '</h3>';

		$company = get_post_meta( $job_id, '_wps_job_company_name', true );
		if ( $company ) {
			$html .= '<p class="wpshadow-job-company">' . esc_html( $company ) . '</p>';
		}

		$html .= '</div>';

		// Job Meta
		$html .= '<div class="wpshadow-job-meta">';

		// Location
		if ( $attributes['displayLocation'] ?? true ) {
			$location = get_post_meta( $job_id, '_wps_job_location', true );
			if ( $location ) {
				$html .= '<div class="wpshadow-job-location">';
				$html .= '<span class="dashicon">📍</span> ';
				$html .= esc_html( $location );
				$html .= '</div>';
			}
		}

		// Job Type
		$types = wp_get_post_terms( $job_id, 'wps_job_type', array( 'fields' => 'names' ) );
		if ( ! empty( $types ) ) {
			$html .= '<div class="wpshadow-job-type">';
			$html .= implode( ', ', array_map( 'esc_html', $types ) );
			$html .= '</div>';
		}

		// Salary
		if ( $attributes['displaySalary'] ?? true ) {
			$salary_min = get_post_meta( $job_id, '_wps_job_salary_min', true );
			$salary_max = get_post_meta( $job_id, '_wps_job_salary_max', true );

			if ( $salary_min || $salary_max ) {
				$html .= '<div class="wpshadow-job-salary">';
				$currency = get_post_meta( $job_id, '_wps_job_salary_currency', true ) ?? 'USD';

				if ( $salary_min && $salary_max ) {
					$html .= sprintf(
						'%s %s - %s %s',
						esc_html( $currency ),
						esc_html( number_format( (int) $salary_min ) ),
						esc_html( $currency ),
						esc_html( number_format( (int) $salary_max ) )
					);
				} elseif ( $salary_min ) {
					$html .= sprintf(
						'%s %s',
						esc_html( $currency ),
						esc_html( number_format( (int) $salary_min ) )
					);
				} else {
					$html .= sprintf(
						'%s %s',
						esc_html( $currency ),
						esc_html( number_format( (int) $salary_max ) )
					);
				}

				$html .= '</div>';
			}
		}

		// Deadline
		if ( $attributes['displayDeadline'] ?? true ) {
			$deadline = get_post_meta( $job_id, '_wps_job_deadline_date', true );
			if ( $deadline ) {
				$deadline_obj = DateTime::createFromFormat( 'Y-m-d', $deadline );
				if ( $deadline_obj ) {
					$html .= '<div class="wpshadow-job-deadline">';
					$html .= sprintf(
						/* translators: %s: application deadline date */
						esc_html__( 'Apply by: %s', 'wpshadow' ),
						esc_html( $deadline_obj->format( get_option( 'date_format' ) ) )
					);
					$html .= '</div>';
				}
			}
		}

		$html .= '</div>';

		// Job Description
		$html .= '<div class="wpshadow-job-description">';
		$html .= wp_kses_post( get_the_content( '', false, $job_id ) );
		$html .= '</div>';

		// Apply Button
		$apply_url = get_post_meta( $job_id, '_wps_job_application_url', true );
		$apply_email = get_post_meta( $job_id, '_wps_job_application_email', true );

		if ( $apply_url || $apply_email ) {
			$html .= '<div class="wpshadow-job-apply">';

			if ( $apply_url ) {
				$html .= sprintf(
					'<a href="%s" class="wpshadow-job-apply-button wpshadow-job-apply-button-%s" target="_blank" rel="noopener noreferrer">%s</a>',
					esc_url( $apply_url ),
					esc_attr( $attributes['applyButtonStyle'] ?? 'primary' ),
					esc_html( $attributes['applyButtonText'] ?? __( 'Apply Now', 'wpshadow' ) )
				);
			} elseif ( $apply_email ) {
				$html .= sprintf(
					'<a href="mailto:%s" class="wpshadow-job-apply-button wpshadow-job-apply-button-%s">%s</a>',
					esc_attr( $apply_email ),
					esc_attr( $attributes['applyButtonStyle'] ?? 'primary' ),
					esc_html( $attributes['applyButtonText'] ?? __( 'Apply Now', 'wpshadow' ) )
				);
			}

			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}
}
