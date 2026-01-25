<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Schema_Review extends Diagnostic_Base {


	protected static $slug        = 'test-schema-review';
	protected static $title       = 'Review/Rating Schema Test';
	protected static $description = 'Tests for Review and AggregateRating structured data';

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
		// Check for rating/review HTML
		$has_rating_html = preg_match( '/class=["\'][^"\']*rating|class=["\'][^"\']*stars|⭐|★/iu', $html );
		$has_review_html = preg_match( '/class=["\'][^"\']*review|class=["\'][^"\']*testimonial/i', $html );

		// Check for Review/AggregateRating schema
		$has_review_schema    = preg_match( '/"@type"\s*:\s*"Review"/i', $html );
		$has_aggregate_schema = preg_match( '/"@type"\s*:\s*"AggregateRating"/i', $html );

		// If ratings/reviews visible but no schema
		if ( ( $has_rating_html || $has_review_html ) && ! $has_review_schema && ! $has_aggregate_schema ) {
			return array(
				'id'            => 'schema-review-missing',
				'title'         => 'Review/Rating Schema Missing',
				'description'   => 'Ratings or reviews detected but no Review or AggregateRating structured data found. Adding schema can enable star ratings in search results.'
				'kb_link' => 'https://wpshadow.com/kb/review-schema/',
				'training_link' => 'https://wpshadow.com/training/rich-results/',
				'auto_fixable'  => false,
				'threat_level'  => 35,
				'module'        => 'SEO',
				'priority'      => 2,
				'meta'          => array(
					'has_rating_html' => $has_rating_html,
					'has_review_html' => $has_review_html,
					'has_schema'      => ( $has_review_schema || $has_aggregate_schema ),
					'checked_url'     => $checked_url,
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
		return __( 'Review/Rating Schema', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for Review and AggregateRating structured data.', 'wpshadow' );
	}
}
