<?php
/**
 * Job Board Settings
 *
 * Centralized settings for job board configuration and email templates.
 *
 * @package WPShadow
 * @subpackage Content
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\JobPostings;

use WPShadow\Core\Settings_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Job Board Settings Class
 *
 * @since 0.6093.1200
 */
class Job_Board_Settings {

	/**
	 * Register job board settings.
	 *
	 * @since 0.6093.1200
	 */
	public static function register_settings() {
		$settings = array(
			// General Settings
			'job_board_title' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => __( 'Job Board', 'wpshadow' ),
				'label'             => __( 'Job Board Title', 'wpshadow' ),
				'description'       => __( 'Title displayed on the job board page', 'wpshadow' ),
			),
			'job_board_description' => array(
				'type'              => 'string',
				'sanitize_callback' => 'wp_kses_post',
				'default'           => __( 'Browse our open positions and apply today!', 'wpshadow' ),
				'label'             => __( 'Job Board Description', 'wpshadow' ),
				'description'       => __( 'Description shown at the top of job board', 'wpshadow' ),
			),
			'jobs_per_page' => array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 12,
				'label'             => __( 'Jobs Per Page', 'wpshadow' ),
				'description'       => __( 'Number of jobs to display per page in listings', 'wpshadow' ),
			),
			'allow_external_applications' => array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => true,
				'label'             => __( 'Allow External Applications', 'wpshadow' ),
				'description'       => __( 'Redirect to external application URL if provided', 'wpshadow' ),
			),
			'allow_internal_applications' => array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => true,
				'label'             => __( 'Allow Internal Applications', 'wpshadow' ),
				'description'       => __( 'Allow applications through built-in form', 'wpshadow' ),
			),
			'require_resume_upload' => array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => true,
				'label'             => __( 'Require Resume Upload', 'wpshadow' ),
				'description'       => __( 'Make resume upload mandatory in application form', 'wpshadow' ),
			),
			'allowed_file_types' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'pdf,doc,docx',
				'label'             => __( 'Allowed Resume File Types', 'wpshadow' ),
				'description'       => __( 'Comma-separated list of allowed extensions', 'wpshadow' ),
			),
			'max_file_size_mb' => array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 5,
				'label'             => __( 'Max Resume File Size (MB)', 'wpshadow' ),
				'description'       => __( 'Maximum file size for resume uploads', 'wpshadow' ),
			),

			// Email Settings
			'application_notification_email' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_email',
				'default'           => get_option( 'admin_email' ),
				'label'             => __( 'Application Notification Email', 'wpshadow' ),
				'description'       => __( 'Email to receive new application notifications', 'wpshadow' ),
			),
			'send_applicant_confirmation' => array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => true,
				'label'             => __( 'Send Applicant Confirmation Email', 'wpshadow' ),
				'description'       => __( 'Send confirmation when applicant submits application', 'wpshadow' ),
			),
			'send_rejection_emails' => array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => false,
				'label'             => __( 'Send Rejection Emails', 'wpshadow' ),
				'description'       => __( 'Send notification when applicant is rejected', 'wpshadow' ),
			),

			// Job Posting Settings
			'default_job_status' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'draft',
				'label'             => __( 'Default Job Status', 'wpshadow' ),
				'description'       => __( 'Default status for new job postings (draft or publish)', 'wpshadow' ),
			),
			'auto_expire_jobs' => array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => true,
				'label'             => __( 'Auto-Expire Jobs', 'wpshadow' ),
				'description'       => __( 'Automatically expire jobs past their deadline', 'wpshadow' ),
			),
			'auto_close_after_hire' => array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => true,
				'label'             => __( 'Auto-Close After Hire', 'wpshadow' ),
				'description'       => __( 'Automatically close job when someone is hired', 'wpshadow' ),
			),
			'show_salary_in_listings' => array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => true,
				'label'             => __( 'Show Salary in Listings', 'wpshadow' ),
				'description'       => __( 'Display salary range in job listing cards', 'wpshadow' ),
			),
			'featured_jobs_limit' => array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 5,
				'label'             => __( 'Featured Jobs Limit', 'wpshadow' ),
				'description'       => __( 'Maximum number of featured jobs to display', 'wpshadow' ),
			),

			// Search and Filter Settings
			'enable_location_filter' => array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => true,
				'label'             => __( 'Enable Location Filter', 'wpshadow' ),
				'description'       => __( 'Show location filter in job listing', 'wpshadow' ),
			),
			'enable_salary_filter' => array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => true,
				'label'             => __( 'Enable Salary Filter', 'wpshadow' ),
				'description'       => __( 'Show salary range filter in job listing', 'wpshadow' ),
			),
			'enable_experience_filter' => array(
				'type'              => 'boolean',
				'sanitize_callback' => 'rest_sanitize_boolean',
				'default'           => true,
				'label'             => __( 'Enable Experience Level Filter', 'wpshadow' ),
				'description'       => __( 'Show experience level filter in job listing', 'wpshadow' ),
			),
		);

		foreach ( $settings as $key => $args ) {
			register_setting(
				'wpshadow_job_board',
				'wpshadow_' . $key,
				$args
			);
		}
	}

	/**
	 * Get a job board setting.
	 *
	 * @since 0.6093.1200
	 * @param  string $option Option name (without wpshadow_ prefix).
	 * @param  mixed  $default Default value.
	 * @return mixed Setting value.
	 */
	public static function get( $option, $default = '' ) {
		return get_option( 'wpshadow_' . $option, $default );
	}

	/**
	 * Update a job board setting.
	 *
	 * @since 0.6093.1200
	 * @param  string $option Option name (without wpshadow_ prefix).
	 * @param  mixed  $value  Option value.
	 * @return bool Success.
	 */
	public static function update( $option, $value ) {
		return update_option( 'wpshadow_' . $option, $value );
	}

	/**
	 * Get email template.
	 *
	 * @since 0.6093.1200
	 * @param  string $template Template name.
	 * @return string Template content.
	 */
	public static function get_email_template( $template ) {
		$templates = array(
			'applicant_confirmation' => __( 'Thank you for applying! We have received your application and will review it shortly.', 'wpshadow' ),
			'rejection'              => __( 'Thank you for your interest. Unfortunately, we have decided to move forward with other candidates.', 'wpshadow' ),
			'interview_invitation'   => __( 'Congratulations! We would like to invite you for an interview. Please reply with your availability.', 'wpshadow' ),
			'job_offer'              => __( 'We are pleased to offer you the position. Please confirm your acceptance by [DATE].', 'wpshadow' ),
		);

		return $templates[ $template ] ?? '';
	}
}
