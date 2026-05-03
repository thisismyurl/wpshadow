<?php
/**
 * Diagnostic Metadata Registry
 *
 * Central override registry for diagnostic metadata: confidence tiers,
 * Core 50 membership, auto-fix safety, and display notes. Acts as the
 * single source of truth for any metadata that cannot or should not be
 * baked into individual diagnostic class files.
 *
 * Usage: Diagnostic_Base::get_confidence(), is_core(), etc. check here first
 * before falling back to their static class properties.
 *
 * Operators can override any entry via the thisismyurl_shadow_diagnostic_metadata filter.
 *
 * @package ThisIsMyURL\Shadow
 * @since   0.7055
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Static metadata registry for all shipped diagnostics.
 *
 * Keys:  diagnostic slug (must match protected static $slug on the class)
 * Values: associative array with any subset of:
 *   - confidence     string  'high' | 'standard' | 'low'
 *   - is_core        bool    true when part of the Core 50 essential set
 *   - auto_fix_safe  bool    true when the associated treatment is safe to
 *                            apply without manual review
 *   - notes          string  Short human-readable note visible in admin reports
 */
class Diagnostic_Metadata {

	/**
	 * Resolved metadata cache (slug → merged array).
	 *
	 * @var array<string, array<string, mixed>>|null
	 */
	private static ?array $cache = null;

	// -------------------------------------------------------------------------
	// Core 50 slugs: universally applicable, high-signal, battle-tested checks.
	// These are shown by default for new users; remaining 180+ are Advanced.
	// -------------------------------------------------------------------------

	/**
	 * Built-in metadata keyed by diagnostic slug.
	 *
	 * Confidence tiers:
	 *   'high'     – Boolean or near-certain heuristic; safe to auto-fix
	 *   'standard' – Generally reliable; occasional false-positives possible
	 *   'low'      – Context-dependent; manual review strongly recommended
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private static array $built_in = array(

		// ------------------------------------------------------------------
		// SECURITY — Core 50 (12 checks)
		// ------------------------------------------------------------------

		'auth-keys-and-salts-set' => array(
			'confidence'    => 'high',
			'is_core'       => true,
			'auto_fix_safe' => true,
			'notes'         => 'Boolean check; no false-positives in production.',
		),
		'admin-account-count-minimized' => array(
			'confidence'    => 'high',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Lists admin user count; admin decides.',
		),
		'ssl-certificate-valid' => array(
			'confidence'    => 'high',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Certificate validity is a definitive boolean.',
		),
		'core-updated' => array(
			'confidence'    => 'high',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Core version comparison; no false-positives.',
		),
		'wp-debug-display-off' => array(
			'confidence'    => 'high',
			'is_core'       => true,
			'auto_fix_safe' => true,
			'notes'         => 'WP_DEBUG_DISPLAY should always be false in production.',
		),
		'wp-debug-log-private' => array(
			'confidence'    => 'high',
			'is_core'       => true,
			'auto_fix_safe' => true,
			'notes'         => 'Public debug log is a critical security risk.',
		),
		'xmlrpc-policy-intentional' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Intentional state requires owner confirmation.',
		),
		'sensitive-files-protected' => array(
			'confidence'    => 'high',
			'is_core'       => true,
			'auto_fix_safe' => true,
			'notes'         => 'readme.html, license.txt, etc. must not be web-accessible.',
		),
		'uploads-php-execution-blocked' => array(
			'confidence'    => 'high',
			'is_core'       => true,
			'auto_fix_safe' => true,
			'notes'         => 'PHP execution in /uploads is a critical attack vector.',
		),
		'security-headers-present' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Header presence check; value correctness may vary.',
		),
		'two-factor-admin-enabled' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Plugin-agnostic detection; may yield false negatives.',
		),
		'admin-session-expiration-hardened' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => true,
			'notes'         => 'auth_cookie_expiration filter check.',
		),

		// ------------------------------------------------------------------
		// PERFORMANCE — Core 50 (10 checks)
		// ------------------------------------------------------------------

		'browser-caching-headers' => array(
			'confidence'    => 'high',
			'is_core'       => true,
			'auto_fix_safe' => true,
			'notes'         => 'Deterministic header inspection.',
		),
		'compression-enabled' => array(
			'confidence'    => 'high',
			'is_core'       => true,
			'auto_fix_safe' => true,
			'notes'         => 'GZIP/Brotli presence check; clear binary result.',
		),
		'autosave-interval-optimized' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => true,
			'notes'         => 'Compares AUTOSAVE_INTERVAL against recommended range.',
		),
		'block-library-css' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => true,
			'notes'         => 'Global asset removal; theme compatibility should be verified.',
		),
		'adjacent-posts-links' => array(
			'confidence'    => 'high',
			'is_core'       => true,
			'auto_fix_safe' => true,
			'notes'         => 'Unnecessary DB query on every frontend page load.',
		),
		'caching-plugin-active' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Detects common caching plugins; server-side caching not detected.',
		),
		'page-cache-enabled' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Heuristic; server-level caches may not be identifiable.',
		),
		'http2-or-http3-enabled' => array(
			'confidence'    => 'standard',
			'is_core'       => false,
			'auto_fix_safe' => false,
			'notes'         => 'Uses WordPress HTTP transport metadata when available; readiness is beta because some hosts do not expose the negotiated protocol version.',
		),
		'php-memory-limit-optimized' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Compares ini_get memory_limit against recommended minimum.',
		),
		'opcache-enabled' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'OPcache availability; enabling requires server access.',
		),
		'object-cache' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Object caching presence; Memcached/Redis not always detectable.',
		),

		// ------------------------------------------------------------------
		// DATABASE — Core 50 (8 checks)
		// ------------------------------------------------------------------

		'myisam-tables-detected' => array(
			'confidence'    => 'high',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Direct SHOW TABLE STATUS query; deterministic.',
		),
		'tables-without-primary-key' => array(
			'confidence'    => 'high',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Schema inspection; reliable.',
		),
		'auto-draft-accumulation' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => true,
			'notes'         => 'Count threshold may need tuning for high-traffic editorial sites.',
		),
		'stale-sessions-cleared' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => true,
			'notes'         => 'Age threshold is configurable.',
		),
		'wp-options-autoload-size' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Size threshold; some large autoload sets are intentional.',
		),
		'orphaned-user-meta' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => true,
			'notes'         => 'User-ID foreign-key check; safe to clean up.',
		),
		'post-meta-bloat-detected' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Identifies rows without parent post; may need plugin awareness.',
		),
		'transients-cleanup' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => true,
			'notes'         => 'Expired transients are always safe to remove.',
		),

		// ------------------------------------------------------------------
		// WORDPRESS HEALTH — Core 50 (10 checks)
		// ------------------------------------------------------------------

		'plugins-updated' => array(
			'confidence'    => 'high',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Version comparison against wordpress.org API.',
		),
		'themes-updated' => array(
			'confidence'    => 'high',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Version comparison; reliable.',
		),
		'php-version' => array(
			'confidence'    => 'high',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Runtime version check; no false-positives.',
		),
		'wp-cron-reliable' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Scheduled event health; real-cron environments require extra context.',
		),
		'post-revision-limit-set' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => true,
			'notes'         => 'WP_POST_REVISIONS constant check.',
		),
		'trash-auto-empty-configured' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => true,
			'notes'         => 'empty_trash_days option; safe configuration.',
		),
		'unused-plugins-removed' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Inactive plugin detection; admin decides what to keep.',
		),
		'unused-themes-removed' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Identifies themes beyond active + one parent.',
		),
		'site-urls-correctly' => array(
			'confidence'    => 'high',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'siteurl/home mismatch detection; reliable.',
		),
		'wp-config-location' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Checks if wp-config.php is one level above webroot.',
		),

		// ------------------------------------------------------------------
		// MONITORING — Core 50 (5 checks)
		// ------------------------------------------------------------------

		'backups-automated' => array(
			'confidence'    => 'high',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Detects known backup plugins and scheduled events.',
		),
		'spam-protection-enabled' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Detects common anti-spam plugins/settings.',
		),
		'site-health-criticals-addressed' => array(
			'confidence'    => 'high',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Bridges to WP Site Health API critical issues.',
		),
		'php-extensions-required' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Checks commonly required extensions against loaded list.',
		),
		'scheduled-posts-not-stuck' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => true,
			'notes'         => 'Detects missed scheduled posts older than threshold.',
		),

		// ------------------------------------------------------------------
		// CONTENT / SETUP — Core 50 (5 checks)
		// ------------------------------------------------------------------

		'privacy-policy-page-set' => array(
			'confidence'    => 'high',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'wp_get_privacy_policy_url() non-empty check.',
		),
		'search-engine-visibility-intentional' => array(
			'confidence'    => 'high',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'blog_public option; must be intentional.',
		),
		'permalink-structure-meaningful' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Validates non-default permalink structure.',
		),
		'timezone' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Checks timezone is set to a named region, not numeric offset.',
		),
		'smtp' => array(
			'confidence'    => 'standard',
			'is_core'       => true,
			'auto_fix_safe' => false,
			'notes'         => 'Detects SMTP plugin or mail() override; native wp_mail unreliable.',
		),

		// ------------------------------------------------------------------
		// NON-CORE: confidence overrides for remaining shipped diagnostics
		// These override the default 'standard' where we have strong signal.
		// ------------------------------------------------------------------

		// High-confidence non-core items (reliable detection, not universal)
		'wp-config-permissions-hardened'       => array( 'confidence' => 'high' ),
		'backup-files-not-public'              => array( 'confidence' => 'high' ),
		'readme-html-protected'                => array( 'confidence' => 'high' ),
		'user-enumeration-reduced'             => array( 'confidence' => 'standard' ),
		'rest-api-sensitive-routes-protected'  => array( 'confidence' => 'standard' ),
		'comment-moderation-enabled'           => array( 'confidence' => 'high' ),
		'comment-link-limit-set'               => array( 'confidence' => 'high' ),
		'comments-auto-close-old-posts'        => array( 'confidence' => 'high' ),
		'database-prefix-intentional'          => array( 'confidence' => 'standard' ),
		'site-charset-utf8'                    => array( 'confidence' => 'high' ),
		'site-language-intentional'            => array( 'confidence' => 'standard' ),
		'auto-update-policy'                   => array( 'confidence' => 'standard' ),
		'auto-update-policy-reviewed'          => array( 'confidence' => 'standard' ),
		'script-debug-production'              => array( 'confidence' => 'high' ),
		'query-debug-logging-disabled-production' => array( 'confidence' => 'high' ),
		'wp-generator-tag'                     => array( 'confidence' => 'high' ),
		'rss-version-leak'                     => array( 'confidence' => 'high' ),
		'duplicate-post-meta-keys'             => array( 'confidence' => 'high' ),
		'orphaned-term-relationships'          => array( 'confidence' => 'standard' ),
		'user-meta-bloat-detected'             => array( 'confidence' => 'standard' ),
		'user-table-large'                     => array( 'confidence' => 'standard' ),
		'woocommerce-session-table-size'       => array( 'confidence' => 'standard' ),
		'wp-options-row-count-reasonable'      => array( 'confidence' => 'standard' ),
		'orphaned-comments'                    => array( 'confidence' => 'standard' ),
		'responsive-images-enabled'            => array( 'confidence' => 'high' ),
		'webp-support'                         => array( 'confidence' => 'standard' ),
		'noncritical-js-deferred'              => array( 'confidence' => 'standard' ),
		'concatenate-scripts-disabled'         => array( 'confidence' => 'standard' ),
		'system-cron-production'               => array( 'confidence' => 'standard' ),
		'cron-overlap-protection-enabled'      => array( 'confidence' => 'standard' ),
		'fatal-error-handler-enabled'          => array( 'confidence' => 'high' ),
		'error-logging'                        => array( 'confidence' => 'standard' ),

		// Low-confidence items (context-dependent; manual review important)
		'analytics-installed-intentional'      => array( 'confidence' => 'low' ),
		'author-archives-intentional'          => array( 'confidence' => 'low' ),
		'comment-policy-intentional'           => array( 'confidence' => 'low' ),
		'search-enabled-intentional'           => array( 'confidence' => 'low' ),
		'tag-archives-intentional'             => array( 'confidence' => 'low' ),
		'registration-setting-intentional'     => array( 'confidence' => 'low' ),
		'application-passwords-intentional'    => array( 'confidence' => 'low' ),
		'update-services-intentional'          => array( 'confidence' => 'low' ),
		'noindex-policy'                       => array( 'confidence' => 'low' ),
		'robots-policy'                        => array( 'confidence' => 'low' ),
		'category-strategy'                    => array( 'confidence' => 'low' ),
		'critical-css-strategy'                => array( 'confidence' => 'low' ),
		'redirect-management'                  => array( 'confidence' => 'low' ),
		'seo-plugin-config-intentional'        => array( 'confidence' => 'low' ),
		'homepage-displays-intentional'        => array( 'confidence' => 'low' ),
		'rss-feed-summary'                     => array( 'confidence' => 'low' ),
		'pingbacks-trackbacks'                 => array( 'confidence' => 'low' ),
		'cdn-for-static-assets'                => array( 'confidence' => 'low' ),
		'critical-resources-preloaded'         => array( 'confidence' => 'low' ),
		'oembed-discovery-links'               => array( 'confidence' => 'low' ),
		'shortlink-head-tag'                   => array( 'confidence' => 'low' ),
		'rest-api-head-link'                   => array( 'confidence' => 'low' ),
		'rsd-link'                             => array( 'confidence' => 'low' ),
		'wlwmanifest-link'                     => array( 'confidence' => 'low' ),

		// Accessibility — standard (process-oriented, not purely boolean)
		'skip-link-present'                    => array( 'confidence' => 'standard' ),
		'viewport-meta'                        => array( 'confidence' => 'high' ),
		'lang-attribute-correct'               => array( 'confidence' => 'high' ),
		'heading-structure-reviewable'         => array( 'confidence' => 'low' ),
		'image-alt-process'                    => array( 'confidence' => 'low' ),
		'button-text-specific'                 => array( 'confidence' => 'low' ),
		'focus-outline-preserved'              => array( 'confidence' => 'low' ),
		'form-error-messaging'                 => array( 'confidence' => 'low' ),
		'motion-reduction'                     => array( 'confidence' => 'low' ),
		'nav-menu-accessible-name'             => array( 'confidence' => 'standard' ),
		'search-form-accessible-name'          => array( 'confidence' => 'standard' ),
		'underlines-or-link-distinction'       => array( 'confidence' => 'low' ),
	);

	// -------------------------------------------------------------------------
	// Public API
	// -------------------------------------------------------------------------

	/**
	 * Get merged metadata for a diagnostic slug.
	 *
	 * Merges built-in entries with operator-supplied overrides from the
	 * thisismyurl_shadow_diagnostic_metadata filter.  Results are cached per-request.
	 *
	 * @param string $slug Diagnostic slug.
	 * @return array<string, mixed> Metadata array (may be empty for unknown slugs).
	 */
	public static function get( string $slug ): array {
		if ( null === self::$cache ) {
			self::$cache = self::build_cache();
		}

		return self::$cache[ $slug ] ?? array();
	}

	/**
	 * Return all known metadata entries.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function get_all(): array {
		if ( null === self::$cache ) {
			self::$cache = self::build_cache();
		}

		return self::$cache;
	}

	/**
	 * Return slugs for all Core 50 diagnostics.
	 *
	 * @return array<int, string>
	 */
	public static function get_core_slugs(): array {
		$all   = self::get_all();
		$slugs = array();

		foreach ( $all as $slug => $meta ) {
			if ( ! empty( $meta['is_core'] ) ) {
				$slugs[] = $slug;
			}
		}

		return $slugs;
	}

	/**
	 * Return slugs grouped by confidence tier.
	 *
	 * @param string $tier 'high' | 'standard' | 'low'
	 * @return array<int, string>
	 */
	public static function get_slugs_by_confidence( string $tier ): array {
		$tier  = strtolower( trim( $tier ) );
		$all   = self::get_all();
		$slugs = array();

		foreach ( $all as $slug => $meta ) {
			$confidence = $meta['confidence'] ?? 'standard';
			if ( $confidence === $tier ) {
				$slugs[] = $slug;
			}
		}

		return $slugs;
	}

	/**
	 * Invalidate the per-request cache.
	 *
	 * Useful in tests or when the filter changes at runtime.
	 *
	 * @return void
	 */
	public static function flush_cache(): void {
		self::$cache = null;
	}

	// -------------------------------------------------------------------------
	// Internal helpers
	// -------------------------------------------------------------------------

	/**
	 * Build the merged metadata cache.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private static function build_cache(): array {
		$map = self::$built_in;

		/**
		 * Filter the complete diagnostic metadata map.
		 *
		 * Each top-level key is a diagnostic slug.  Child arrays may contain:
		 *   - confidence     string  'high' | 'standard' | 'low'
		 *   - is_core        bool
		 *   - auto_fix_safe  bool
		 *   - notes          string
		 *
		 * @since 0.7055
		 *
		 * @param array<string, array<string, mixed>> $map Metadata map.
		 */
		$filtered = apply_filters( 'thisismyurl_shadow_diagnostic_metadata', $map );
		if ( ! is_array( $filtered ) ) {
			return $map;
		}

		// Deep-merge: operator overrides one specific key at a time.
		foreach ( $filtered as $slug => $overrides ) {
			if ( ! is_string( $slug ) || ! is_array( $overrides ) ) {
				continue;
			}

			$base               = isset( $map[ $slug ] ) ? $map[ $slug ] : array();
			$map[ $slug ]       = array_merge( $base, $overrides );
		}

		// Sanitise confidence values to allowed set.
		$allowed_confidence = array( 'high', 'standard', 'low' );
		foreach ( $map as $slug => &$entry ) {
			if ( isset( $entry['confidence'] ) && ! in_array( $entry['confidence'], $allowed_confidence, true ) ) {
				$entry['confidence'] = 'standard';
			}
			if ( isset( $entry['is_core'] ) ) {
				$entry['is_core'] = (bool) $entry['is_core'];
			}
			if ( isset( $entry['auto_fix_safe'] ) ) {
				$entry['auto_fix_safe'] = (bool) $entry['auto_fix_safe'];
			}
		}
		unset( $entry );

		return $map;
	}
}
