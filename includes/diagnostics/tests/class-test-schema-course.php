<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Schema_Course extends Diagnostic_Base {


	protected static $slug        = 'test-schema-course';
	protected static $title       = 'Course Schema Test';
	protected static $description = 'Tests for Course structured data';

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
		// Check for course indicators
		$has_course_keywords = preg_match( '/\b(course|training|certification|curriculum|syllabus|lesson|module|learn|enroll)\b/i', $html );
		$has_duration        = preg_match( '/\b(hours?|weeks?|months?|duration)\b/i', $html );
		$has_instructor      = preg_match( '/\b(instructor|teacher|taught by|facilitator)\b/i', $html );

		// Check for Course schema
		$has_course_schema = preg_match( '/"@type"\s*:\s*"Course"/i', $html );

		// If looks like course but no schema
		if ( $has_course_keywords && ( $has_duration || $has_instructor ) && ! $has_course_schema ) {
			return array(
				'id'            => 'schema-course-missing',
				'title'         => 'Course Schema Missing',
				'description'   => 'Educational course content detected but no Course structured data found. Course schema enables rich results with duration, instructor, and provider information.'
				'kb_link' => 'https://wpshadow.com/kb/course-schema/',
				'training_link' => 'https://wpshadow.com/training/education-seo/',
				'auto_fixable'  => false,
				'threat_level'  => 35,
				'module'        => 'SEO',
				'priority'      => 3,
				'meta'          => array(
					'has_course_keywords' => $has_course_keywords,
					'has_duration'        => $has_duration,
					'has_instructor'      => $has_instructor,
					'has_schema'          => $has_course_schema,
					'checked_url'         => $checked_url,
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
		return __( 'Course Schema', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for Course structured data (education sites).', 'wpshadow' );
	}
}
