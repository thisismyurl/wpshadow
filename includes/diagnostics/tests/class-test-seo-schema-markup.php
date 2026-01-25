<?php

declare(strict_types=1);
/**
 * Test: Schema Markup (JSON-LD) Check
 *
 * Tests if HTML contains proper Schema.org structured data in JSON-LD format.
 *
 * Philosophy: Show value (#9) - Help users get rich snippets in search results
 *
 * @package WPShadow
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Schema_Markup extends Diagnostic_Base {


	protected static $slug        = 'test-seo-schema-markup';
	protected static $title       = 'Schema Markup Test';
	protected static $description = 'Tests for Schema.org structured data (JSON-LD)';

	/**
	 * Common schema types for different page types
	 */
	const COMMON_TYPES = array(
		'Article',
		'BlogPosting',
		'NewsArticle',
		'WebPage',
		'Organization',
		'Person',
		'Product',
		'LocalBusiness',
		'BreadcrumbList',
	);

	/**
	 * Run the diagnostic check
	 *
	 * PASS (returns null): Valid schema markup present
	 * FAIL (returns array): Missing schema or has issues
	 *
	 * @param string|null $url URL to test (defaults to homepage)
	 * @param string|null $html Pre-fetched HTML to analyze
	 * @return array|null Finding data or null if no issue
	 */
	public static function check( ?string $url = null, ?string $html = null ): ?array {
		if ( $html !== null ) {
			return self::analyze_html( $html, $url ?? 'provided-html' );
		}

		$site_url = $url ?? home_url( '/' );

		if ( $url !== null && ! self::is_internal_url( $url ) ) {
			return self::error_result( 'Invalid URL', 'URL must be from this WordPress site' );
		}

		$html = self::fetch_html( $site_url );
		if ( $html === false ) {
			return self::error_result( 'Fetch Failed', 'Could not retrieve page HTML' );
		}

		return self::analyze_html( $html, $site_url );
	}

	/**
	 * Run comprehensive schema markup tests
	 *
	 * @param string|null $url URL to test
	 * @param string|null $html Pre-fetched HTML
	 * @return array Test results
	 */
	public static function run_schema_tests( ?string $url = null, ?string $html = null ): array {
		$html = $html ?? self::fetch_html( $url ?? home_url( '/' ) );

		if ( $html === false ) {
			return array(
				'success' => false,
				'error'   => 'Could not fetch HTML',
				'url'     => $url ?? home_url( '/' ),
			);
		}

		$schemas = self::extract_json_ld( $html );
		$parsed  = self::parse_schemas( $schemas );

		return array(
			'success'       => true,
			'url'           => $url ?? home_url( '/' ),
			'schema_blocks' => count( $schemas ),
			'schema_types'  => $parsed['types'],
			'tests'         => array(
				'has_schema'          => self::test_has_schema( $html ),
				'valid_json'          => self::test_valid_json( $html ),
				'has_context'         => self::test_has_context( $html ),
				'has_type'            => self::test_has_type( $html ),
				'organization_schema' => self::test_organization_schema( $html ),
			),
			'summary'       => array(
				'passed'         => count( $schemas ) > 0 && $parsed['valid_count'] > 0,
				'total_blocks'   => count( $schemas ),
				'valid_blocks'   => $parsed['valid_count'],
				'invalid_blocks' => $parsed['invalid_count'],
				'types_found'    => $parsed['types'],
			),
			'schemas'       => $parsed['parsed'],
		);
	}

	/**
	 * Test if schema markup exists
	 */
	public static function test_has_schema( ?string $url = null, ?string $html = null ): array {
		$html    = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		$schemas = self::extract_json_ld( $html );

		return array(
			'test'    => 'has_schema',
			'passed'  => count( $schemas ) > 0,
			'count'   => count( $schemas ),
			'message' => count( $schemas ) > 0
				? sprintf( '%d schema block(s) found', count( $schemas ) )
				: 'No schema markup found',
			'impact'  => 'Schema markup enables rich snippets in search results',
		);
	}

	/**
	 * Test if JSON is valid
	 */
	public static function test_valid_json( ?string $url = null, ?string $html = null ): array {
		$html    = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		$schemas = self::extract_json_ld( $html );

		if ( empty( $schemas ) ) {
			return array(
				'test'    => 'valid_json',
				'passed'  => false,
				'message' => 'No schema to validate',
				'impact'  => 'Invalid JSON prevents search engines from reading schema',
			);
		}

		$valid_count = 0;
		$invalid     = array();

		foreach ( $schemas as $idx => $schema ) {
			$decoded = json_decode( $schema, true );
			if ( json_last_error() === JSON_ERROR_NONE ) {
				++$valid_count;
			} else {
				$invalid[] = sprintf( 'Block %d: %s', $idx + 1, json_last_error_msg() );
			}
		}

		return array(
			'test'        => 'valid_json',
			'passed'      => empty( $invalid ),
			'valid_count' => $valid_count,
			'total_count' => count( $schemas ),
			'errors'      => $invalid,
			'message'     => empty( $invalid )
				? 'All schema blocks are valid JSON'
				: sprintf( '%d schema block(s) have JSON errors', count( $invalid ) ),
			'impact'      => 'Invalid JSON is ignored by search engines',
		);
	}

	/**
	 * Test if schema has @context
	 */
	public static function test_has_context( ?string $url = null, ?string $html = null ): array {
		$html    = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		$schemas = self::extract_json_ld( $html );
		$parsed  = self::parse_schemas( $schemas );

		$missing_context = 0;
		foreach ( $parsed['parsed'] as $schema ) {
			if ( empty( $schema['@context'] ) ) {
				++$missing_context;
			}
		}

		return array(
			'test'          => 'has_context',
			'passed'        => $missing_context === 0,
			'missing_count' => $missing_context,
			'message'       => $missing_context === 0
				? 'All schemas have @context'
				: sprintf( '%d schema(s) missing @context', $missing_context ),
			'impact'        => '@context defines the vocabulary (should be https://schema.org)',
		);
	}

	/**
	 * Test if schema has @type
	 */
	public static function test_has_type( ?string $url = null, ?string $html = null ): array {
		$html    = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		$schemas = self::extract_json_ld( $html );
		$parsed  = self::parse_schemas( $schemas );

		$missing_type = 0;
		foreach ( $parsed['parsed'] as $schema ) {
			if ( empty( $schema['@type'] ) ) {
				++$missing_type;
			}
		}

		return array(
			'test'          => 'has_type',
			'passed'        => $missing_type === 0,
			'missing_count' => $missing_type,
			'types_found'   => $parsed['types'],
			'message'       => $missing_type === 0
				? 'All schemas have @type'
				: sprintf( '%d schema(s) missing @type', $missing_type ),
			'impact'        => '@type defines what the data represents (Article, Product, etc.)',
		);
	}

	/**
	 * Test for Organization schema (recommended for all sites)
	 */
	public static function test_organization_schema( ?string $url = null, ?string $html = null ): array {
		$html    = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		$schemas = self::extract_json_ld( $html );
		$parsed  = self::parse_schemas( $schemas );

		$has_org = in_array( 'Organization', $parsed['types'], true ) ||
			in_array( 'LocalBusiness', $parsed['types'], true );

		return array(
			'test'        => 'organization_schema',
			'passed'      => $has_org,
			'types_found' => $parsed['types'],
			'message'     => $has_org
				? 'Organization/Business schema present (recommended)'
				: 'No Organization schema (recommended for all sites)',
			'impact'      => 'Organization schema helps search engines understand your brand',
		);
	}

	/**
	 * Analyze HTML for schema markup issues
	 *
	 * @param string $html HTML content
	 * @param string $checked_url URL that was checked
	 * @return array|null Finding or null
	 */
	protected static function analyze_html( string $html, string $checked_url ): ?array {
		$schemas = self::extract_json_ld( $html );

		// Missing schema = FAIL (warning level)
		if ( empty( $schemas ) ) {
			return array(
				'id'            => 'seo-schema-markup',
				'title'         => 'Missing Schema Markup',
				'description'   => 'Your page has no structured data (Schema.org JSON-LD). Adding schema markup can help you get rich snippets in search results, improving click-through rates.'
				'kb_link' => 'https://wpshadow.com/kb/schema-markup/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
				'training_link' => 'https://wpshadow.com/training/structured-data/',
				'auto_fixable'  => false,
				'threat_level'  => 50,
				'module'        => 'SEO',
				'priority'      => 2,
				'meta'          => array(
					'issue'          => 'missing',
					'recommendation' => 'Add JSON-LD schema based on page type (Article, Product, LocalBusiness, etc.)',
					'checked_url'    => $checked_url,
				),
			);
		}

		// Parse schemas and check for issues
		$parsed = self::parse_schemas( $schemas );
		$issues = array();

		// Invalid JSON
		if ( $parsed['invalid_count'] > 0 ) {
			$issues[] = sprintf( '%d schema block(s) have JSON errors', $parsed['invalid_count'] );
		}

		// Missing @context or @type
		foreach ( $parsed['parsed'] as $idx => $schema ) {
			if ( empty( $schema['@context'] ) ) {
				$issues[] = sprintf( 'Block %d missing @context', $idx + 1 );
			}
			if ( empty( $schema['@type'] ) ) {
				$issues[] = sprintf( 'Block %d missing @type', $idx + 1 );
			}
		}

		// Perfect: has valid schemas with no issues
		if ( empty( $issues ) ) {
			return null; // PASS
		}

		// Has schemas but with issues = FAIL
		$threat_level = 40;
		if ( $parsed['invalid_count'] > 0 ) {
			$threat_level = 60; // Invalid JSON is more serious
		}

		return array(
			'id'            => 'seo-schema-markup',
			'title'         => 'Schema Markup Issues',
			'description'   => sprintf(
				'Your page has %d schema block(s) with %d issue(s): %s. These issues prevent search engines from using your structured data.',
				count( $schemas ),
				count( $issues ),
				implode( ', ', array_slice( $issues, 0, 3 ) ) . ( count( $issues ) > 3 ? '...' : '' )
			)
			'kb_link' => 'https://wpshadow.com/kb/schema-markup/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
			'training_link' => 'https://wpshadow.com/training/structured-data/',
			'auto_fixable'  => false,
			'threat_level'  => $threat_level,
			'module'        => 'SEO',
			'priority'      => 2,
			'meta'          => array(
				'schema_blocks'  => count( $schemas ),
				'valid_blocks'   => $parsed['valid_count'],
				'invalid_blocks' => $parsed['invalid_count'],
				'types_found'    => $parsed['types'],
				'issues'         => $issues,
				'checked_url'    => $checked_url,
			),
		);
	}

	/**
	 * Extract JSON-LD script blocks from HTML
	 *
	 * @param string $html HTML content
	 * @return array Array of JSON-LD strings
	 */
	protected static function extract_json_ld( string $html ): array {
		if ( empty( $html ) ) {
			return array();
		}

		preg_match_all( '/<script\s+type=["\']application\/ld\+json["\']\s*>(.*?)<\/script>/is', $html, $matches );
		return array_map( 'trim', $matches[1] ?? array() );
	}

	/**
	 * Parse schema JSON strings
	 *
	 * @param array $schemas Array of JSON strings
	 * @return array Parsed results
	 */
	protected static function parse_schemas( array $schemas ): array {
		$parsed        = array();
		$types         = array();
		$valid_count   = 0;
		$invalid_count = 0;

		foreach ( $schemas as $schema ) {
			$decoded = json_decode( $schema, true );

			if ( json_last_error() === JSON_ERROR_NONE ) {
				++$valid_count;
				$parsed[] = $decoded;

				// Extract type(s)
				if ( isset( $decoded['@type'] ) ) {
					if ( is_array( $decoded['@type'] ) ) {
						$types = array_merge( $types, $decoded['@type'] );
					} else {
						$types[] = $decoded['@type'];
					}
				}

				// Handle @graph (multiple schemas in one block)
				if ( isset( $decoded['@graph'] ) && is_array( $decoded['@graph'] ) ) {
					foreach ( $decoded['@graph'] as $item ) {
						if ( isset( $item['@type'] ) ) {
							if ( is_array( $item['@type'] ) ) {
								$types = array_merge( $types, $item['@type'] );
							} else {
								$types[] = $item['@type'];
							}
						}
					}
				}
			} else {
				++$invalid_count;
			}
		}

		return array(
			'parsed'        => $parsed,
			'types'         => array_unique( $types ),
			'valid_count'   => $valid_count,
			'invalid_count' => $invalid_count,
		);
	}

	/**
	 * Fetch HTML from URL
	 *
	 * @param string $url URL to fetch
	 * @return string|false HTML or false on error
	 */
	protected static function fetch_html( string $url ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 10,
				'user-agent' => 'WPShadow-Diagnostic/1.0 (SEO Checker)',
				'sslverify'  => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		return wp_remote_retrieve_body( $response );
	}

	/**
	 * Check if URL is internal
	 *
	 * @param string $url URL to check
	 * @return bool
	 */
	protected static function is_internal_url( string $url ): bool {
		$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
		$test_host = wp_parse_url( $url, PHP_URL_HOST );
		return $site_host === $test_host;
	}

	/**
	 * Generate error result
	 *
	 * @param string $title Error title
	 * @param string $description Error description
	 * @return array Error result
	 */
	protected static function error_result( string $title, string $description ): array {
		return array(
			'id'            => 'seo-schema-markup',
			'title'         => $title,
			'description'   => $description
			'kb_link' => 'https://wpshadow.com/kb/schema-markup/',
			'training_link' => 'https://wpshadow.com/training/structured-data/',
			'auto_fixable'  => false,
			'threat_level'  => 30,
			'module'        => 'SEO',
			'priority'      => 3,
		);
	}

	/**
	 * Get the name for display
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return __( 'Schema Markup Check', 'wpshadow' );
	}

	/**
	 * Get the description for display
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return __( 'Checks HTML for Schema.org structured data (JSON-LD) for rich snippets.', 'wpshadow' );
	}
}
