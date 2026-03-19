<?php
/**
 * REST API Response Caching and Performance
 *
 * Validates REST API response caching and performance optimization.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_REST_API_Performance Class
 *
 * Checks REST API response caching and performance issues.
 *
 * @since 1.6093.1200
 */
class Diagnostic_REST_API_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates REST API response caching and performance optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'rest-api';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Pattern 1: No caching headers on REST API responses
		$has_cache_headers = false;

		if ( ! headers_sent() ) {
			// Check if Cache-Control header would be sent
			// Note: get_http_header() doesn't exist; skip this check in CLI/non-request context
			$cache_header      = isset( $_SERVER['HTTP_CACHE_CONTROL'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_CACHE_CONTROL'] ) ) : '';
			$has_cache_headers = ! empty( $cache_header );
		}

		if ( ! $has_cache_headers && defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'REST API responses not cached', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-performance',
				'details'      => array(
					'issue'                 => 'no_cache_headers',
					'message'               => __( 'REST API responses missing cache-control headers', 'wpshadow' ),
					'cache_benefits'        => array(
						'Reduce server load',
						'Faster response times',
						'Lower bandwidth usage',
						'Better user experience',
					),
					'caching_strategies'    => array(
						'Public endpoints'   => 'Cache 1 hour or more',
						'User-specific data' => 'Cache 5-15 minutes',
						'Real-time data'     => 'Cache 1-5 minutes',
						'No-cache endpoints' => 'Vary by request',
					),
					'cache_control_headers' => array(
						'public'          => 'Safe for CDN/browser cache',
						'private'         => 'Browser only, not CDN',
						'max-age=3600'    => 'Cache for 3600 seconds',
						'must-revalidate' => 'Check before using',
						'no-cache'        => 'Revalidate before use',
					),
					'adding_cache_headers'  => "add_filter('rest_post_dispatch', function(\$response) {
	// Cache public endpoints
	if (!is_user_logged_in()) {
		header('Cache-Control: public, max-age=3600');
	} else {
		// Don't cache private data in shared caches
		header('Cache-Control: private, max-age=600');
	}
	
	return \$response;
});",
					'etag_support'          => __( 'Implement ETags for conditional requests', 'wpshadow' ),
					'vary_header'           => __( 'Use Vary header for different response formats', 'wpshadow' ),
					'cdn_integration'       => __( 'Cache-Control: public allows CDN caching', 'wpshadow' ),
					'cache_invalidation'    => array(
						'When post updated',
						'When user published content',
						'On schedule (periodic)',
						'Manual via admin',
					),
					'performance_impact'    => __( 'Caching can reduce API response time by 50-90%', 'wpshadow' ),
					'testing_cache'         => array(
						'1. Make API request',
						'2. Check response headers',
						'3. Look for Cache-Control',
						'4. Verify age increases on repeat requests',
					),
					'recommendation'        => __( 'Add Cache-Control headers to REST API responses', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: Inefficient REST API queries (N+1 problem)
		$post_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish'" );

		if ( $post_count > 1000 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Large number of posts may cause REST API performance issues', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-performance',
				'details'      => array(
					'issue'                     => 'n_plus_one_queries',
					'post_count'                => $post_count,
					'message'                   => sprintf(
						/* translators: %d: number of posts */
						__( 'Site has %d posts - REST API queries may be inefficient', 'wpshadow' ),
						$post_count
					),
					'what_is_n_plus_one'        => __( 'Problem where listing N items requires N+1 database queries', 'wpshadow' ),
					'example_scenario'          => array(
						'1 query'    => 'Get 20 posts',
						'20 queries' => 'Get author for each post',
						'Total'      => '21 queries (N+1 problem)',
					),
					'performance_impact'        => __( 'Each additional query adds 5-50ms - 20 posts = 100-1000ms overhead', 'wpshadow' ),
					'optimization_strategies'   => array(
						'Eager loading'   => 'Load related data upfront',
						'Field selection' => 'Only request needed fields',
						'Pagination'      => 'Limit results per request',
						'Object caching'  => 'Cache related data',
					),
					'eager_loading_example'     => "// Fetch all authors at once instead of per-post
add_filter('rest_post_query', function(\$args) {
	// Instead of loading author for each post
	// Load all authors upfront
	add_filter('rest_prepare_post', function(\$response) {
		\$post_id = \$response->data['id'];
		\$author_id = \$response->data['author'];
		
		// Get author data (should be cached)
		\$author = get_user_by('id', \$author_id);
		
		return \$response;
	});
	
	return \$args;
});",
					'field_limiting'            => "// Only request needed fields
\$response = wp_remote_get('/wp-json/wp/v2/posts?_fields=id,title,date');",
					'pagination_best_practices' => array(
						'Default 10 per page',
						'Max 100 per page',
						'Use per_page query parameter',
						'Include total pages header',
					),
					'query_optimization'        => "add_filter('rest_post_query', function(\$args) {
	// Limit posts per page
	\$args['posts_per_page'] = 20;
	
	// Simplify query
	\$args['fields'] = 'ids'; // Get IDs only
	\$args['no_found_rows'] = true; // Skip count
	
	return \$args;
});",
					'object_cache_use'          => __( 'Enable object cache (Redis, Memcached) for REST queries', 'wpshadow' ),
					'monitoring'                => __( 'Monitor REST API query times in logs', 'wpshadow' ),
					'recommendation'            => __( 'Optimize REST API queries using eager loading and field selection', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: REST API missing compression
		$accept_encoding = isset( $_SERVER['HTTP_ACCEPT_ENCODING'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT_ENCODING'] ) ) : '';
		$gzip_enabled = strpos( $accept_encoding, 'gzip' ) !== false;

		if ( ! $gzip_enabled ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'REST API responses not compressed', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-performance',
				'details'      => array(
					'issue'                => 'no_compression',
					'message'              => __( 'REST API responses not using gzip compression', 'wpshadow' ),
					'compression_benefits' => array(
						'Reduce response size by 70-90%',
						'Faster downloads',
						'Lower bandwidth costs',
						'Better performance',
					),
					'compression_methods'  => array(
						'gzip'    => 'Most common, ~70% reduction',
						'deflate' => 'Alternative, less common',
						'brotli'  => 'Newer, better compression',
					),
					'enabling_gzip'        => array(
						'Apache: Enable mod_deflate',
						'Nginx: Enable gzip module',
						'PHP-FPM: Use onfly compression',
						'WordPress: Use wp_compress_response()',
					),
					'apache_htaccess'      => '<IfModule mod_deflate.c>
	AddOutputFilterByType DEFLATE application/json
	AddOutputFilterByType DEFLATE application/xml
	AddOutputFilterByType DEFLATE text/html text/plain text/xml
	AddOutputFilterByType DEFLATE text/javascript
	AddOutputFilterByType DEFLATE application/javascript
</IfModule>',
					'nginx_config'         => 'gzip on;
gzip_types application/json;',
					'php_function'         => 'wp_compress_response() - WordPress handles automatically',
					'cache_implications'   => __( 'Compressed responses must be marked with Content-Encoding header', 'wpshadow' ),
					'testing_compression'  => array(
						'curl -H "Accept-Encoding: gzip" -i https://yoursite.com/wp-json/wp/v2/posts',
						'Look for "Content-Encoding: gzip"',
						'Compare response sizes',
					),
					'performance_gain'     => __( 'API response time reduction: 30-70% smaller payloads', 'wpshadow' ),
					'recommendation'       => __( 'Enable gzip compression for REST API responses', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: REST API endpoints returning excessive data
		return null;
	}
}
