<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Schema_JobPosting extends Diagnostic_Base {


	protected static $slug        = 'test-schema-jobposting';
	protected static $title       = 'JobPosting Schema Test';
	protected static $description = 'Tests for JobPosting structured data';

	public static function check( ?string $url = null, ?string $html = null ): ?array {
		if ( $html !== null ) {
			return self::analyze_html( $html, $url ?? 'provided-html' );
		}

		$html = self::fetch_html( $url ?? home_url( '/' ) );
		if ( $html === false ) {
			return null;
		}

		return self::analyze_html( $html, $url ?? home_url( '/' ) );
	}

	protected static function analyze_html( string $html, string $checked_url ): ?array {
		// Check for job posting indicators
		$has_job_keywords = preg_match( '/\b(position|job opening|now hiring|career|employment|apply now|job description|qualifications|responsibilities)\b/i', $html );
		$has_salary       = preg_match( '/\$[0-9,]+|salary|compensation|pay rate/i', $html );
		$has_location     = preg_match( '/\b(location|remote|office|city|state)\b/i', $html );

		// Check for JobPosting schema
		$has_job_schema = preg_match( '/"@type"\s*:\s*"JobPosting"/i', $html );

		// If looks like job posting but no schema
		if ( $has_job_keywords && $has_location && ! $has_job_schema ) {
			return array(
				'id'            => 'schema-jobposting-missing',
				'title'         => 'JobPosting Schema Missing',
				'description'   => 'Job posting content detected but no JobPosting structured data found. JobPosting schema enables rich job listings in Google for Jobs.'
				'kb_link' => 'https://wpshadow.com/kb/jobposting-schema/',
				'training_link' => 'https://wpshadow.com/training/recruitment-seo/',
				'auto_fixable'  => false,
				'threat_level'  => 40,
				'module'        => 'SEO',
				'priority'      => 2,
				'meta'          => array(
					'has_job_keywords' => $has_job_keywords,
					'has_salary'       => $has_salary,
					'has_location'     => $has_location,
					'has_schema'       => $has_job_schema,
					'checked_url'      => $checked_url,
				),
			);
		}

		return null;
	}

	protected static function fetch_html( string $url ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'   => 10,
				'sslverify' => false,
			)
		);
		return is_wp_error( $response ) ? false : wp_remote_retrieve_body( $response );
	}

	public static function get_name(): string {
		return __( 'JobPosting Schema', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for JobPosting structured data (careers pages).', 'wpshadow' );
	}
}
