<?php
/**
 * Job Application Form Block
 *
 * Renders an application form for job postings.
 *
 * @package WPShadow
 * @subpackage Content
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Blocks;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Job Application Form Block Class
 *
 * @since 1.6050.0000
 */
class Job_Application_Form_Block extends Hook_Subscriber_Base {

	/**
	 * Get hooks to subscribe to.
	 *
	 * @since  1.6050.0000
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'init' => 'register_block',
		);
	}

	/**
	 * Register the job application form block.
	 *
	 * @since 1.6050.0000
	 */
	public static function register_block(): void {
		register_block_type(
			'wpshadow/job-application-form',
			array(
				'render_callback' => array( __CLASS__, 'render_block' ),
				'attributes'      => array(
					'jobPostId'         => array(
						'type'    => 'number',
						'default' => 0,
					),
					'allowCoverLetter'  => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'allowResumeUpload' => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'requirePhone'      => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'buttonText'        => array(
						'type'    => 'string',
						'default' => __( 'Submit Application', 'wpshadow' ),
					),
				),
				'style'           => 'wpshadow-job-application-form-style',
			)
		);
	}

	/**
	 * Render the job application form block.
	 *
	 * @since  1.6050.0000
	 * @param  array $attributes Block attributes.
	 * @return string Rendered form HTML.
	 */
	public static function render_block( $attributes ) {
		$job_id = absint( $attributes['jobPostId'] ?? 0 );

		if ( 0 === $job_id ) {
			return '<p>' . esc_html__( 'No job selected for this application form.', 'wpshadow' ) . '</p>';
		}

		$post = get_post( $job_id );
		if ( ! $post || 'wps_job_posting' !== $post->post_type ) {
			return '<p>' . esc_html__( 'Invalid job posting.', 'wpshadow' ) . '</p>';
		}

		$nonce = wp_create_nonce( 'wpshadow_job_application_nonce' );

		$html = '<div class="wpshadow-job-application-form-wrapper">';
		$html .= '<form class="wpshadow-job-application-form" id="wpshadow-job-application-form-' . absint( $job_id ) . '">';

		// Nonce and job ID
		$html .= '<input type="hidden" name="action" value="submit_job_application">';
		$html .= '<input type="hidden" name="nonce" value="' . esc_attr( $nonce ) . '">';
		$html .= '<input type="hidden" name="job_id" value="' . absint( $job_id ) . '">';

		// Full Name
		$html .= '<div class="wpshadow-form-group">';
		$html .= '<label for="applicant-name-' . absint( $job_id ) . '">' . esc_html__( 'Full Name', 'wpshadow' ) . ' <span class="required">*</span></label>';
		$html .= '<input type="text" id="applicant-name-' . absint( $job_id ) . '" name="applicant_name" required class="wpshadow-form-control">';
		$html .= '</div>';

		// Email
		$html .= '<div class="wpshadow-form-group">';
		$html .= '<label for="applicant-email-' . absint( $job_id ) . '">' . esc_html__( 'Email Address', 'wpshadow' ) . ' <span class="required">*</span></label>';
		$html .= '<input type="email" id="applicant-email-' . absint( $job_id ) . '" name="applicant_email" required class="wpshadow-form-control">';
		$html .= '</div>';

		// Phone (optional or required based on settings)
		$phone_required = $attributes['requirePhone'] ?? true;
		$phone_label = $phone_required ? __( 'Phone Number', 'wpshadow' ) . ' <span class="required">*</span>' : __( 'Phone Number', 'wpshadow' );
		$phone_attr = $phone_required ? 'required' : '';

		$html .= '<div class="wpshadow-form-group">';
		$html .= '<label for="applicant-phone-' . absint( $job_id ) . '">' . $phone_label . '</label>';
		$html .= '<input type="tel" id="applicant-phone-' . absint( $job_id ) . '" name="applicant_phone" ' . $phone_attr . ' class="wpshadow-form-control">';
		$html .= '</div>';

		// Resume Upload
		if ( $attributes['allowResumeUpload'] ?? true ) {
			$html .= '<div class="wpshadow-form-group">';
			$html .= '<label for="resume-' . absint( $job_id ) . '">' . esc_html__( 'Resume/CV', 'wpshadow' ) . '</label>';
			$html .= '<input type="file" id="resume-' . absint( $job_id ) . '" name="resume" accept=".pdf,.doc,.docx" class="wpshadow-form-control">';
			$html .= '<small>' . esc_html__( 'Accepted formats: PDF, DOC, DOCX (Max 5MB)', 'wpshadow' ) . '</small>';
			$html .= '</div>';
		}

		// Cover Letter
		if ( $attributes['allowCoverLetter'] ?? true ) {
			$html .= '<div class="wpshadow-form-group">';
			$html .= '<label for="cover-letter-' . absint( $job_id ) . '">' . esc_html__( 'Cover Letter', 'wpshadow' ) . '</label>';
			$html .= '<textarea id="cover-letter-' . absint( $job_id ) . '" name="cover_letter" rows="5" class="wpshadow-form-control"></textarea>';
			$html .= '</div>';
		}

		// Submit Button
		$html .= '<div class="wpshadow-form-group">';
		$html .= '<button type="submit" class="wpshadow-btn wpshadow-btn-primary">';
		$html .= esc_html( $attributes['buttonText'] ?? __( 'Submit Application', 'wpshadow' ) );
		$html .= '</button>';
		$html .= '</div>';

		// Success message (hidden)
		$html .= '<div class="wpshadow-form-success" style="display:none;">';
		$html .= '<p class="wpshadow-alert wpshadow-alert-success">' . esc_html__( 'Thank you! Your application has been submitted successfully.', 'wpshadow' ) . '</p>';
		$html .= '</div>';

		// Error message (hidden)
		$html .= '<div class="wpshadow-form-error" style="display:none;">';
		$html .= '<p class="wpshadow-alert wpshadow-alert-danger"></p>';
		$html .= '</div>';

		$html .= '</form>';
		$html .= '</div>';

		// Inline script for form handling
		$html .= '<script type="text/javascript">';
		$html .= 'document.getElementById("wpshadow-job-application-form-' . absint( $job_id ) . '").addEventListener("submit", function(e) {
			e.preventDefault();
			var form = this;
			var formData = new FormData(form);
			
			fetch(ajaxurl, {
				method: "POST",
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					form.style.display = "none";
					form.nextElementSibling.style.display = "block";
				} else {
					form.querySelector(".wpshadow-form-error p").textContent = data.data.message;
					form.querySelector(".wpshadow-form-error").style.display = "block";
				}
			});
		});';
		$html .= '</script>';

		return $html;
	}
}
