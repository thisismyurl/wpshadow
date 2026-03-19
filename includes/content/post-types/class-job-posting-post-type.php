<?php
/**
 * Job Posting custom post type.
 *
 * Manages job postings with comprehensive meta fields, taxonomies, and schema markup.
 *
 * @package WPShadow
 * @subpackage Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\JobPostings;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Job Posting Post Type Class
 *
 * Registers job posting CPT with meta fields, taxonomies, and REST API support.
 *
 * @since 1.6093.1200
 */
class Job_Posting_Post_Type extends Hook_Subscriber_Base {

	const POST_TYPE = 'wps_job_posting';

	/**
	 * Get hooks to subscribe to.
	 *
	 * @since 1.6093.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'init'          => array(
				array( 'register_post_type', 10 ),
				array( 'register_taxonomies', 10 ),
				array( 'register_meta_fields', 10 ),
			),
			'rest_api_init' => array(
				array( 'register_rest_fields', 10 ),
			),
			'the_content'   => 'add_schema_markup',
		);
	}

	protected static function get_required_version(): string {
		return '1.6089';
	}

	/**
	 * Register the job posting custom post type.
	 *
	 * @since 1.6093.1200
	 */
	public static function register_post_type(): void {
		$labels = array(
			'name'                  => __( 'Job Postings', 'wpshadow' ),
			'singular_name'         => __( 'Job Posting', 'wpshadow' ),
			'menu_name'             => __( 'Job Board', 'wpshadow' ),
			'add_new'               => __( 'Add Job', 'wpshadow' ),
			'add_new_item'          => __( 'Add New Job Posting', 'wpshadow' ),
			'edit_item'             => __( 'Edit Job Posting', 'wpshadow' ),
			'new_item'              => __( 'New Job Posting', 'wpshadow' ),
			'view_item'             => __( 'View Job Posting', 'wpshadow' ),
			'view_items'            => __( 'View Job Postings', 'wpshadow' ),
			'search_items'          => __( 'Search Job Postings', 'wpshadow' ),
			'not_found'             => __( 'No job postings found', 'wpshadow' ),
			'not_found_in_trash'    => __( 'No job postings found in trash', 'wpshadow' ),
			'all_items'             => __( 'All Job Postings', 'wpshadow' ),
			'archives'              => __( 'Job Archives', 'wpshadow' ),
			'attributes'            => __( 'Job Attributes', 'wpshadow' ),
			'insert_into_item'      => __( 'Insert into job posting', 'wpshadow' ),
			'uploaded_to_this_item' => __( 'Uploaded to this job posting', 'wpshadow' ),
		);

		$args = array(
			'labels'                => $labels,
			'description'           => __( 'Job postings and career opportunities', 'wpshadow' ),
			'public'                => true,
			'publicly_queryable'    => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'query_var'             => true,
			'rewrite'               => array(
				'slug'       => 'jobs',
				'with_front' => false,
			),
			'capability_type'       => 'post',
			'has_archive'           => 'jobs',
			'hierarchical'          => false,
			'menu_position'         => 24,
			'menu_icon'             => 'dashicons-briefcase',
			'show_in_rest'          => true,
			'rest_base'             => 'jobs',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'supports'              => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields', 'author' ),
			'taxonomies'            => array( 'wps_job_category', 'wps_job_type' ),
		);

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Register job posting taxonomies.
	 *
	 * @since 1.6093.1200
	 */
	public static function register_taxonomies(): void {
		// Job Category taxonomy
		$category_labels = array(
			'name'              => __( 'Job Categories', 'wpshadow' ),
			'singular_name'     => __( 'Job Category', 'wpshadow' ),
			'search_items'      => __( 'Search Job Categories', 'wpshadow' ),
			'all_items'         => __( 'All Job Categories', 'wpshadow' ),
			'parent_item'       => __( 'Parent Job Category', 'wpshadow' ),
			'parent_item_colon' => __( 'Parent Job Category:', 'wpshadow' ),
			'edit_item'         => __( 'Edit Job Category', 'wpshadow' ),
			'update_item'       => __( 'Update Job Category', 'wpshadow' ),
			'add_new_item'      => __( 'Add New Job Category', 'wpshadow' ),
			'new_item_name'     => __( 'New Job Category Name', 'wpshadow' ),
			'menu_name'         => __( 'Job Categories', 'wpshadow' ),
		);

		register_taxonomy(
			'wps_job_category',
			self::POST_TYPE,
			array(
				'hierarchical'      => true,
				'labels'            => $category_labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'job-category' ),
				'show_in_rest'      => true,
			)
		);

		// Job Type taxonomy (Full-time, Part-time, Contract, etc.)
		$type_labels = array(
			'name'              => __( 'Job Types', 'wpshadow' ),
			'singular_name'     => __( 'Job Type', 'wpshadow' ),
			'search_items'      => __( 'Search Job Types', 'wpshadow' ),
			'all_items'         => __( 'All Job Types', 'wpshadow' ),
			'parent_item'       => __( 'Parent Job Type', 'wpshadow' ),
			'parent_item_colon' => __( 'Parent Job Type:', 'wpshadow' ),
			'edit_item'         => __( 'Edit Job Type', 'wpshadow' ),
			'update_item'       => __( 'Update Job Type', 'wpshadow' ),
			'add_new_item'      => __( 'Add New Job Type', 'wpshadow' ),
			'new_item_name'     => __( 'New Job Type Name', 'wpshadow' ),
			'menu_name'         => __( 'Job Types', 'wpshadow' ),
		);

		register_taxonomy(
			'wps_job_type',
			self::POST_TYPE,
			array(
				'hierarchical'      => false,
				'labels'            => $type_labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'job-type' ),
				'show_in_rest'      => true,
			)
		);
	}

	/**
	 * Register job posting meta fields.
	 *
	 * @since 1.6093.1200
	 */
	public static function register_meta_fields(): void {
		$meta_fields = array(
			'salary_min'          => __( 'Minimum Salary', 'wpshadow' ),
			'salary_max'          => __( 'Maximum Salary', 'wpshadow' ),
			'salary_currency'     => __( 'Salary Currency', 'wpshadow' ),
			'salary_period'       => __( 'Salary Period', 'wpshadow' ), // annual, monthly, hourly
			'job_location'        => __( 'Job Location', 'wpshadow' ),
			'location_type'       => __( 'Location Type', 'wpshadow' ), // remote, on-site, hybrid
			'experience_level'    => __( 'Experience Level', 'wpshadow' ), // entry, mid, senior, executive
			'application_url'     => __( 'Application URL', 'wpshadow' ),
			'application_email'   => __( 'Application Email', 'wpshadow' ),
			'deadline_date'       => __( 'Application Deadline', 'wpshadow' ),
			'company_name'        => __( 'Company Name', 'wpshadow' ),
			'company_website'     => __( 'Company Website', 'wpshadow' ),
			'company_logo'        => __( 'Company Logo URL', 'wpshadow' ),
			'department'          => __( 'Department', 'wpshadow' ),
			'reports_to'          => __( 'Reports To', 'wpshadow' ),
			'positions_available' => __( 'Positions Available', 'wpshadow' ),
			'featured'            => __( 'Featured Job', 'wpshadow' ),
		);

		foreach ( $meta_fields as $meta_key => $meta_label ) {
			register_meta(
				'post',
				'_wps_job_' . $meta_key,
				array(
					'object_subtype' => self::POST_TYPE,
					'type'           => 'string',
					'single'         => true,
					'show_in_rest'   => true,
				)
			);
		}
	}

	/**
	 * Register REST API fields for job postings.
	 *
	 * @since 1.6093.1200
	 */
	public static function register_rest_fields(): void {
		$meta_fields = array(
			'salary_min',
			'salary_max',
			'salary_currency',
			'salary_period',
			'job_location',
			'location_type',
			'experience_level',
			'application_url',
			'application_email',
			'deadline_date',
			'company_name',
			'company_website',
			'company_logo',
			'department',
			'reports_to',
			'positions_available',
			'featured',
		);

		foreach ( $meta_fields as $field ) {
			register_rest_field(
				self::POST_TYPE,
				$field,
				array(
					'get_callback'    => function ( $post ) use ( $field ) {
						return get_post_meta( $post['id'], '_wps_job_' . $field, true );
					},
					'update_callback' => function ( $value, $post ) use ( $field ) {
						return update_post_meta( $post->ID, '_wps_job_' . $field, $value );
					},
					'schema'          => array(
						'type'        => 'string',
						'description' => ucwords( str_replace( '_', ' ', $field ) ),
					),
				)
			);
		}
	}

	/**
	 * Add Schema.org JobPosting markup to job postings.
	 *
	 * @since 1.6093.1200
	 * @param  string $content Post content.
	 * @return string Post content with schema markup.
	 */
	public static function add_schema_markup( $content ) {
		if ( ! is_singular( self::POST_TYPE ) ) {
			return $content;
		}

		$post_id = get_the_ID();

		$schema = array(
			'@context'      => 'https://schema.org',
			'@type'         => 'JobPosting',
			'title'         => get_the_title( $post_id ),
			'description'   => wp_strip_all_tags( get_the_excerpt( $post_id ) ),
			'datePosted'    => get_the_date( 'c', $post_id ),
			'hiringOrganization' => array(
				'@type' => 'Organization',
				'name'  => get_post_meta( $post_id, '_wps_job_company_name', true ) || get_bloginfo( 'name' ),
				'url'   => get_post_meta( $post_id, '_wps_job_company_website', true ) || get_home_url(),
			),
		);

		// Add logo if available
		$company_logo = get_post_meta( $post_id, '_wps_job_company_logo', true );
		if ( $company_logo ) {
			$schema['hiringOrganization']['logo'] = $company_logo;
		}

		// Add job location
		$location = get_post_meta( $post_id, '_wps_job_location', true );
		if ( $location ) {
			$schema['jobLocation'] = array(
				'@type'   => 'Place',
				'address' => array(
					'@type' => 'PostalAddress',
					'text'  => $location,
				),
			);
		}

		// Add location type (remote, on-site, hybrid)
		$location_type = get_post_meta( $post_id, '_wps_job_location_type', true );
		if ( $location_type ) {
			$location_type_map = array(
				'remote'   => 'TELECOMMUTE',
				'on-site'  => 'PHYSICAL',
				'hybrid'   => 'WORK_FROM_HOME',
			);
			$schema['jobLocationType'] = $location_type_map[ $location_type ] ?? 'PHYSICAL';
		}

		// Add salary
		$salary_min = get_post_meta( $post_id, '_wps_job_salary_min', true );
		$salary_max = get_post_meta( $post_id, '_wps_job_salary_max', true );
		$currency   = get_post_meta( $post_id, '_wps_job_salary_currency', true ) ?? 'USD';
		$period     = get_post_meta( $post_id, '_wps_job_salary_period', true ) ?? 'YEAR';

		if ( $salary_min || $salary_max ) {
			$period_map = array(
				'annual'  => 'YEAR',
				'monthly' => 'MONTH',
				'hourly'  => 'HOUR',
			);

			$schema['baseSalary'] = array(
				'@type'       => 'PriceSpecification',
				'priceCurrency' => strtoupper( $currency ),
				'price'       => $salary_min && $salary_max ? ( $salary_min + $salary_max ) / 2 : ( $salary_min ?? $salary_max ),
			);

			if ( isset( $period_map[ $period ] ) ) {
				$schema['baseSalary']['priceValidUntil'] = $period_map[ $period ];
			}
		}

		// Add application deadline
		$deadline = get_post_meta( $post_id, '_wps_job_deadline_date', true );
		if ( $deadline ) {
			$schema['validThrough'] = $deadline;
		}

		// Add employment type
		$job_type_terms = wp_get_post_terms( $post_id, 'wps_job_type', array( 'fields' => 'names' ) );
		if ( ! empty( $job_type_terms ) ) {
			$employment_map = array(
				'Full-time'  => 'FULL_TIME',
				'Part-time'  => 'PART_TIME',
				'Contract'   => 'CONTRACTOR',
				'Temporary'  => 'TEMPORARY',
				'Internship' => 'INTERN',
			);

			$employment_types = array();
			foreach ( $job_type_terms as $term ) {
				$employment_types[] = $employment_map[ $term ] ?? 'OTHER';
			}

			if ( ! empty( $employment_types ) ) {
				$schema['employmentType'] = count( $employment_types ) === 1 ? $employment_types[0] : $employment_types;
			}
		}

		$schema_json = wp_json_encode( $schema );

		return $content . '<script type="application/ld+json">' . $schema_json . '</script>';
	}
}
