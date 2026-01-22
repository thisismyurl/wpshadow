<?php
declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Performance Impact Classifier
 * 
 * Predicts diagnostic execution impact before running them.
 * Used to determine when diagnostics can be executed:
 * - Anytime (very low impact)
 * - Peak hours (low impact, Guardian-safe)
 * - Off-peak only (medium/high impact)
 * - Manual/scheduled only (very high impact)
 * 
 * Philosophy: Shows value (#9) through intelligent resource management
 */
class Performance_Impact_Classifier {

	/**
	 * Impact levels (execution time estimate)
	 */
	const IMPACT_NONE = 'none';                // 0-5ms, negligible
	const IMPACT_MINIMAL = 'minimal';          // 5-25ms, anytime safe
	const IMPACT_LOW = 'low';                  // 25-100ms, anytime safe
	const IMPACT_MEDIUM = 'medium';            // 100-500ms, batch acceptable
	const IMPACT_HIGH = 'high';                // 500ms-2s, off-peak preferred
	const IMPACT_VERY_HIGH = 'very_high';      // 2s+, off-peak only
	const IMPACT_EXTREME = 'extreme';          // 5s+, manual/scheduled only

	/**
	 * Guardian suitability levels
	 */
	const GUARDIAN_ANYTIME = 'anytime';        // Can run during any request
	const GUARDIAN_BACKGROUND = 'background';  // Should run in background job
	const GUARDIAN_SCHEDULED = 'scheduled';    // Scheduled job only
	const GUARDIAN_MANUAL = 'manual';          // User-triggered or off-peak

	/**
	 * Impact factors with estimated costs (in milliseconds)
	 */
	protected static $impact_factors = [
		// Database operations
		'db_query_simple'          => 5,        // Simple SELECT (indexed)
		'db_query_complex'         => 50,       // JOINs, WHERE conditions
		'db_query_full_scan'       => 200,      // Full table scan
		'db_query_per_post'        => 2,        // Per-post loop (×post_count)
		'db_query_post_meta'       => 5,        // Post meta lookups
		'db_query_option_get'      => 2,        // get_option() call
		'db_query_option_batch'    => 3,        // Batch option gets
		
		// File system
		'fs_scan_uploads_light'    => 50,       // Scan recent uploads
		'fs_scan_uploads_full'     => 200,      // Scan all uploads
		'fs_scan_plugins'          => 100,      // Scan plugin directory
		'fs_scan_themes'           => 100,      // Scan theme directory
		'fs_file_hash'             => 20,       // Hash single file
		'fs_file_hash_per_file'    => 5,        // Per-file in batch
		
		// External calls
		'http_get_external'        => 500,      // HTTP GET request (avg)
		'http_post_external'       => 1000,     // HTTP POST request (avg)
		'ssl_cert_check'           => 300,      // SSL certificate validation
		'dns_lookup'               => 100,      // DNS lookup
		
		// Processing
		'regex_compile_simple'     => 5,        // Simple regex
		'regex_compile_complex'    => 50,       // Complex regex
		'xml_parse'                => 50,       // XML parsing
		'json_parse'               => 10,       // JSON parsing
		'image_metadata'           => 100,      // Image metadata extraction
		
		// WordPress API
		'wp_query_posts'           => 50,       // WP_Query creation
		'wp_query_per_post_meta'   => 3,        // Post meta in query
		'wp_sanitize_per_item'     => 1,        // Per-item sanitization
		'wp_cache_get'             => 0.5,      // Cache hit/miss
		'wp_transient_get'         => 5,        // Transient retrieval
		
		// Memory/CPU
		'serialize_large_array'    => 25,       // Serialize complex data
		'array_operations_1k'      => 5,        // Operations on 1K items
		'array_operations_10k'     => 50,       // Operations on 10K items
		'array_operations_100k'    => 500,      // Operations on 100K items
	];

	/**
	 * Flag to ensure we only attempt to load the external map once
	 */
	protected static $external_map_loaded = false;

	/**
	 * Load external impact map from includes/data/impact-map.json if present
	 * and merge into the pre-classified diagnostics.
	 */
	protected static function ensure_external_map_loaded(): void {
		if ( self::$external_map_loaded ) {
			return;
		}

		self::$external_map_loaded = true; // Prevent re-entry

		$base_dir = dirname(__DIR__); // points to includes/
		$file = $base_dir . '/data/impact-map.json';
		if ( ! file_exists( $file ) ) {
			return;
		}

		$contents = @file_get_contents( $file );
		if ( $contents === false ) {
			return;
		}

		$decoded = json_decode( $contents, true );
		if ( ! is_array( $decoded ) ) {
			return;
		}

		// Expected format: [ slug => [impact, guardian, factors, description] ]
		foreach ( $decoded as $slug => $config ) {
			if ( ! is_array( $config ) ) {
				continue;
			}
			$impact   = $config['impact']   ?? null;
			$guardian = $config['guardian'] ?? null;
			$factors  = $config['factors']  ?? [];
			$desc     = $config['description'] ?? '';

			if ( ! is_string( $slug ) || ! is_string( $impact ) || ! is_string( $guardian ) ) {
				continue;
			}

			// Merge/override into the pre-classified list
			self::$diagnostic_impacts[ $slug ] = [
				'impact'      => $impact,
				'guardian'    => $guardian,
				'factors'     => is_array( $factors ) ? $factors : [],
				'description' => is_string( $desc ) ? $desc : ''
			];
		}
	}

	/**
	 * Pre-classified diagnostic impacts
	 */
	protected static $diagnostic_impacts = [
		// Security diagnostics - mostly fast
		'admin-email'                       => [
			'impact'            => self::IMPACT_MINIMAL,
			'guardian'          => self::GUARDIAN_ANYTIME,
			'factors'           => ['db_query_simple' => 1],
			'description'       => 'Get option, validate email format',
		],
		'admin-username'                    => [
			'impact'            => self::IMPACT_MINIMAL,
			'guardian'          => self::GUARDIAN_ANYTIME,
			'factors'           => ['db_query_simple' => 1],
			'description'       => 'Query users table for default usernames',
		],
		'ssl'                               => [
			'impact'            => self::IMPACT_MEDIUM,
			'guardian'          => self::GUARDIAN_BACKGROUND,
			'factors'           => ['ssl_cert_check' => 1, 'http_get_external' => 1],
			'description'       => 'Remote SSL certificate check',
		],
		'https-everywhere'                  => [
			'impact'            => self::IMPACT_LOW,
			'guardian'          => self::GUARDIAN_ANYTIME,
			'factors'           => ['db_query_simple' => 3],
			'description'       => 'Check HTTPS enforcement options',
		],

		// Plugin diagnostics - medium impact
		'outdated-plugins'                  => [
			'impact'            => self::IMPACT_HIGH,
			'guardian'          => self::GUARDIAN_SCHEDULED,
			'factors'           => [
				'http_get_external'  => 1,  // Remote API call to check versions
				'db_query_simple'    => 2,
				'array_operations_1k' => 1,
			],
			'description'       => 'Queries WordPress.org API for plugin versions',
		],
		'abandoned-plugins'                 => [
			'impact'            => self::IMPACT_VERY_HIGH,
			'guardian'          => self::GUARDIAN_SCHEDULED,
			'factors'           => [
				'http_get_external'  => 5,  // Multiple API calls
				'db_query_simple'    => 2,
				'array_operations_10k' => 1,
			],
			'description'       => 'Multiple WordPress.org API calls, complex analysis',
		],
		'plugin-conflicts-likely'           => [
			'impact'            => self::IMPACT_HIGH,
			'guardian'          => self::GUARDIAN_BACKGROUND,
			'factors'           => [
				'db_query_complex'   => 2,
				'array_operations_10k' => 2,
			],
			'description'       => 'Analyzes plugin interdependencies',
		],

		// Database diagnostics - varies
		'database-health'                   => [
			'impact'            => self::IMPACT_MEDIUM,
			'guardian'          => self::GUARDIAN_BACKGROUND,
			'factors'           => [
				'db_query_simple'    => 5,
				'db_query_complex'   => 1,
			],
			'description'       => 'Multiple health checks on database',
		],
		'database-post-revisions'           => [
			'impact'            => self::IMPACT_LOW,
			'guardian'          => self::GUARDIAN_ANYTIME,
			'factors'           => ['db_query_simple' => 2],
			'description'       => 'Count revisions, simple aggregation',
		],
		'autoloaded-options-size'           => [
			'impact'            => self::IMPACT_LOW,
			'guardian'          => self::GUARDIAN_ANYTIME,
			'factors'           => ['db_query_complex' => 1],
			'description'       => 'Single aggregation query on options table',
		],

		// File system diagnostics - potentially expensive
		'backup'                            => [
			'impact'            => self::IMPACT_EXTREME,
			'guardian'          => self::GUARDIAN_MANUAL,
			'factors'           => [
				'fs_scan_uploads_full' => 1,
				'fs_file_hash_per_file' => 10000,  // Estimate 10K files
			],
			'description'       => 'Full backup creation - use Guardian cloud only',
		],
		'core-backups-recent'               => [
			'impact'            => self::IMPACT_MEDIUM,
			'guardian'          => self::GUARDIAN_BACKGROUND,
			'factors'           => [
				'db_query_simple'    => 2,
				'fs_scan_uploads_light' => 1,
			],
			'description'       => 'Check recent backups exist',
		],

		// Performance diagnostics
		'core-homepage-load-time'           => [
			'impact'            => self::IMPACT_HIGH,
			'guardian'          => self::GUARDIAN_SCHEDULED,
			'factors'           => ['http_get_external' => 1],
			'description'       => 'Makes HTTP request to home page',
		],
		'core-response-time-total'          => [
			'impact'            => self::IMPACT_HIGH,
			'guardian'          => self::GUARDIAN_SCHEDULED,
			'factors'           => ['http_get_external' => 3],
			'description'       => 'Multiple page requests for timing',
		],

		// Content quality diagnostics
		'pub-alt-text-coverage'             => [
			'impact'            => self::IMPACT_VERY_HIGH,
			'guardian'          => self::GUARDIAN_SCHEDULED,
			'factors'           => [
				'wp_query_posts'        => 1,
				'wp_query_per_post_meta' => 5000,  // ~5K posts
				'db_query_simple'       => 5000,   // Per-post query
			],
			'description'       => 'Scans all posts/images for alt text',
		],
		'broken-links'                      => [
			'impact'            => self::IMPACT_EXTREME,
			'guardian'          => self::GUARDIAN_MANUAL,
			'factors'           => [
				'wp_query_posts'    => 1,
				'http_get_external' => 5000,  // One per link found
			],
			'description'       => 'Makes HTTP requests to all links - very expensive',
		],

		// SEO diagnostics - mostly safe
		'seo-missing-meta-description'      => [
			'impact'            => self::IMPACT_MEDIUM,
			'guardian'          => self::GUARDIAN_BACKGROUND,
			'factors'           => [
				'wp_query_posts'        => 1,
				'wp_query_per_post_meta' => 3000,
			],
			'description'       => 'Queries posts missing meta descriptions',
		],
		'seo-missing-h1-tag'                => [
			'impact'            => self::IMPACT_HIGH,
			'guardian'          => self::GUARDIAN_BACKGROUND,
			'factors'           => [
				'wp_query_posts'     => 1,
				'http_get_external'  => 1000,  // One per post
				'regex_compile_simple' => 1000,
			],
			'description'       => 'Fetches post content to analyze H1 tags',
		],

		// Security scanning - expensive
		'database-malware-scanning'         => [
			'impact'            => self::IMPACT_VERY_HIGH,
			'guardian'          => self::GUARDIAN_SCHEDULED,
			'factors'           => [
				'db_query_full_scan'  => 5,
				'regex_compile_complex' => 100,
				'array_operations_100k' => 1,
			],
			'description'       => 'Scans database for malware patterns',
		],

		// Header/RSS diagnostics - fast
		'head-cleanup'                      => [
			'impact'            => self::IMPACT_LOW,
			'guardian'          => self::GUARDIAN_ANYTIME,
			'factors'           => ['db_query_simple' => 3],
			'description'       => 'Checks header output filters',
		],
	];

	/**
	 * Predict impact for a diagnostic
	 * 
	 * Returns impact classification and estimated time
	 */
	public static function predict( string $diagnostic_slug ): array {
		self::ensure_external_map_loaded();
		if ( isset( self::$diagnostic_impacts[ $diagnostic_slug ] ) ) {
			$prediction = self::$diagnostic_impacts[ $diagnostic_slug ];
			$time_ms = self::calculate_time( $prediction['factors'] ?? [] );

			return [
				'slug'              => $diagnostic_slug,
				'impact_level'      => $prediction['impact'],
				'estimated_ms'      => $time_ms,
				'guardian_suitable' => $prediction['guardian'],
				'description'       => $prediction['description'] ?? '',
				'factors'           => $prediction['factors'] ?? [],
			];
		}

		// Unknown diagnostic - categorize by slug patterns
		return self::predict_from_slug( $diagnostic_slug );
	}

	/**
	 * Calculate estimated execution time from factors
	 */
	public static function calculate_time( array $factors ): float {
		$total_ms = 0;

		foreach ( $factors as $factor => $count ) {
			if ( isset( self::$impact_factors[ $factor ] ) ) {
				$total_ms += self::$impact_factors[ $factor ] * $count;
			}
		}

		return round( $total_ms, 1 );
	}

	/**
	 * Predict impact from diagnostic slug
	 */
	protected static function predict_from_slug( string $slug ): array {
		// Categorize by slug patterns
		if ( strpos( $slug, 'malware' ) !== false || strpos( $slug, 'security-scan' ) !== false ) {
			return [
				'slug'              => $slug,
				'impact_level'      => self::IMPACT_VERY_HIGH,
				'estimated_ms'      => 2500,
				'guardian_suitable' => self::GUARDIAN_SCHEDULED,
				'description'       => 'Security scanning operation (predicted)',
				'factors'           => [],
			];
		}

		if ( strpos( $slug, 'link' ) !== false && strpos( $slug, 'broken' ) !== false ) {
			return [
				'slug'              => $slug,
				'impact_level'      => self::IMPACT_EXTREME,
				'estimated_ms'      => 5000,
				'guardian_suitable' => self::GUARDIAN_MANUAL,
				'description'       => 'Link checking operation (predicted)',
				'factors'           => [],
			];
		}

		if ( strpos( $slug, 'backup' ) !== false && strpos( $slug, 'create' ) !== false ) {
			return [
				'slug'              => $slug,
				'impact_level'      => self::IMPACT_EXTREME,
				'estimated_ms'      => 10000,
				'guardian_suitable' => self::GUARDIAN_MANUAL,
				'description'       => 'Backup creation (predicted)',
				'factors'           => [],
			];
		}

		if ( strpos( $slug, 'http' ) !== false || strpos( $slug, 'api' ) !== false ) {
			return [
				'slug'              => $slug,
				'impact_level'      => self::IMPACT_HIGH,
				'estimated_ms'      => 1000,
				'guardian_suitable' => self::GUARDIAN_SCHEDULED,
				'description'       => 'External API/HTTP call (predicted)',
				'factors'           => [],
			];
		}

		if ( strpos( $slug, 'plugin' ) !== false || strpos( $slug, 'theme' ) !== false ) {
			return [
				'slug'              => $slug,
				'impact_level'      => self::IMPACT_MEDIUM,
				'estimated_ms'      => 500,
				'guardian_suitable' => self::GUARDIAN_BACKGROUND,
				'description'       => 'Plugin/theme analysis (predicted)',
				'factors'           => [],
			];
		}

		if ( strpos( $slug, 'post' ) !== false || strpos( $slug, 'content' ) !== false ) {
			return [
				'slug'              => $slug,
				'impact_level'      => self::IMPACT_MEDIUM,
				'estimated_ms'      => 750,
				'guardian_suitable' => self::GUARDIAN_BACKGROUND,
				'description'       => 'Content analysis (predicted)',
				'factors'           => [],
			];
		}

		// Default to low impact
		return [
			'slug'              => $slug,
			'impact_level'      => self::IMPACT_LOW,
			'estimated_ms'      => 50,
			'guardian_suitable' => self::GUARDIAN_ANYTIME,
			'description'       => 'Simple check (predicted)',
			'factors'           => [],
		];
	}

	/**
	 * Get all diagnostics by impact level
	 */
	public static function get_by_impact( string $level = '' ): array {
		self::ensure_external_map_loaded();
		$diagnostics = self::$diagnostic_impacts;

		if ( empty( $level ) ) {
			return $diagnostics;
		}

		return array_filter(
			$diagnostics,
			fn( $config ) => $config['impact'] === $level
		);
	}

	/**
	 * Get all Guardian-suitable diagnostics
	 */
	public static function get_guardian_suitable( string $context = '' ): array {
		self::ensure_external_map_loaded();
		if ( empty( $context ) ) {
			// All Guardian-suitable
			return array_filter(
				self::$diagnostic_impacts,
				fn( $config ) => in_array(
					$config['guardian'],
					[ self::GUARDIAN_ANYTIME, self::GUARDIAN_BACKGROUND ],
					true
				)
			);
		}

		return array_filter(
			self::$diagnostic_impacts,
			fn( $config ) => $config['guardian'] === $context
		);
	}

	/**
	 * Get diagnostics suitable for off-peak execution
	 */
	public static function get_off_peak_suitable(): array {
		self::ensure_external_map_loaded();
		return array_filter(
			self::$diagnostic_impacts,
			fn( $config ) => in_array(
				$config['guardian'],
				[ self::GUARDIAN_SCHEDULED, self::GUARDIAN_MANUAL ],
				true
			)
		);
	}

	/**
	 * Get summary statistics
	 */
	public static function get_stats(): array {
		self::ensure_external_map_loaded();
		$diagnostics = self::$diagnostic_impacts;
		$stats = [
			'total'     => count( $diagnostics ),
			'by_impact' => [],
			'by_guardian' => [],
			'avg_ms'    => 0,
			'total_ms'  => 0,
		];

		$total_ms = 0;
		$count = 0;

		foreach ( $diagnostics as $config ) {
			$impact = $config['impact'];
			$guardian = $config['guardian'];

			// Count by impact
			if ( ! isset( $stats['by_impact'][ $impact ] ) ) {
				$stats['by_impact'][ $impact ] = 0;
			}
			$stats['by_impact'][ $impact ]++;

			// Count by guardian suitability
			if ( ! isset( $stats['by_guardian'][ $guardian ] ) ) {
				$stats['by_guardian'][ $guardian ] = 0;
			}
			$stats['by_guardian'][ $guardian ]++;

			// Calculate average time
			$time = self::calculate_time( $config['factors'] ?? [] );
			$total_ms += $time;
			$count++;
		}

		$stats['total_ms'] = round( $total_ms, 1 );
		$stats['avg_ms'] = round( $total_ms / $count, 1 );

		return $stats;
	}

	/**
	 * Get impact classification for display
	 */
	public static function get_impact_label( string $impact_level ): array {
		$labels = [
			self::IMPACT_NONE => [
				'label'  => 'Negligible',
				'color'  => 'green',
				'emoji'  => '✓',
				'ms_max' => 5,
			],
			self::IMPACT_MINIMAL => [
				'label'  => 'Minimal',
				'color'  => 'green',
				'emoji'  => '✓✓',
				'ms_max' => 25,
			],
			self::IMPACT_LOW => [
				'label'  => 'Low',
				'color'  => 'green',
				'emoji'  => '✓✓✓',
				'ms_max' => 100,
			],
			self::IMPACT_MEDIUM => [
				'label'  => 'Medium',
				'color'  => 'yellow',
				'emoji'  => '⚠',
				'ms_max' => 500,
			],
			self::IMPACT_HIGH => [
				'label'  => 'High',
				'color'  => 'orange',
				'emoji'  => '⚠⚠',
				'ms_max' => 2000,
			],
			self::IMPACT_VERY_HIGH => [
				'label'  => 'Very High',
				'color'  => 'red',
				'emoji'  => '⚠⚠⚠',
				'ms_max' => 5000,
			],
			self::IMPACT_EXTREME => [
				'label'  => 'Extreme',
				'color'  => 'red',
				'emoji'  => '🔴',
				'ms_max' => 99999,
			],
		];

		return $labels[ $impact_level ] ?? $labels[ self::IMPACT_MEDIUM ];
	}

	/**
	 * Get Guardian suitability explanation
	 */
	public static function get_guardian_explanation( string $context ): string {
		$explanations = [
			self::GUARDIAN_ANYTIME => 'Safe for anytime execution, even during user requests',
			self::GUARDIAN_BACKGROUND => 'Best as background job, Guardian can execute safely',
			self::GUARDIAN_SCHEDULED => 'Scheduled job only, run during off-peak hours',
			self::GUARDIAN_MANUAL => 'Manual or one-time execution only, very high impact',
		];

		return $explanations[ $context ] ?? 'Unknown context';
	}
}
