<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Content_Updated_Date_Missing extends Diagnostic_Base {


	protected static $slug        = 'test-content-updated-date-missing';
	protected static $title       = 'Updated Date Missing Test';
	protected static $description = 'Tests for missing "last updated" date when publish date is shown.';

	public static function check( ?string $url = null, ?string $html = null ): ?array {
		$body = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		if ( $body === false ) {
			return null;
		}

		if ( ! self::is_article( $body ) ) {
			return null;
		}

		$has_updated_date   = self::has_updated_date( $body );
		$has_published_date = self::has_published_date( $body );

		if ( $has_published_date && ! $has_updated_date ) {
			return array(
				'id'            => 'content-no-updated-date',
				'title'         => 'Missing "Last Updated" Date',
				'description'   => 'Published date shown but no "last updated" date. Showing update dates signals fresh content to users and search engines.'
				'kb_link' => 'https://wpshadow.com/kb/last-updated-dates/',
				'training_link' => 'https://wpshadow.com/training/content-freshness/',
				'auto_fixable'  => false,
				'threat_level'  => 25,
				'module'        => 'Content Quality',
				'priority'      => 3,
				'meta'          => array(
					'has_published' => true,
					'has_updated'   => false,
				),
			);
		}

		return null;
	}

	protected static function is_article( string $html ): bool {
		return (bool) ( preg_match( '/<article[^>]*>/i', $html ) ||
			preg_match( '/class=["\'][^"\']*(?:post|entry|article)[^"\']*["\']/i', $html ) );
	}

	protected static function has_updated_date( string $html ): bool {
		return (bool) (
			preg_match( '/(?:last\s*)?updated[:\s]/i', $html ) ||
			preg_match( '/(?:last\s*)?modified[:\s]/i', $html ) ||
			preg_match( '/<time[^>]+class=["\'][^"\']*(?:updated|modified)[^"\']*["\']/i', $html ) ||
			preg_match( '/class=["\'][^"\']*date[_-]?(?:updated|modified)[^"\']*["\']/i', $html )
		);
	}

	protected static function has_published_date( string $html ): bool {
		return (bool) (
			preg_match( '/<time[^>]+datetime=/i', $html ) ||
			preg_match( '/published[:\s]/i', $html ) ||
			preg_match( '/class=["\'][^"\']*(?:published|post-date|entry-date)[^"\']*["\']/i', $html )
		);
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
		return __( 'Updated Date Missing', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks if articles show a "last updated" date when published date is present.', 'wpshadow' );
	}
}
