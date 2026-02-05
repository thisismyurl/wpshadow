<?php
/**
 * WPShadow Academy - KB Article Registry
 *
 * Registry of 200+ knowledge base articles mapped to diagnostics.
 *
 * @package    WPShadow
 * @subpackage Academy
 * @since      1.6089
 */

declare(strict_types=1);

namespace WPShadow\Academy;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * KB_Article_Registry Class
 *
 * Manages knowledge base articles and their relationships to diagnostics.
 *
 * @since 1.6089
 */
class KB_Article_Registry extends Hook_Subscriber_Base {

	/**
	 * Registered articles
	 *
	 * @var array
	 */
	private static $articles = array();

	/**
	 * Get hook subscriptions.
	 *
	 * @since  1.7035.1400
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(); // Configuration only, no hooks needed
	}

	/**
	 * Get the minimum required version for this feature.
	 *
	 * @since  1.6089
	 * @return string Minimum required version.
	 */
	protected static function get_required_version(): string {
		return '1.6089';
	}

	/**
	 * Initialize registry (deprecated)
	 *
	 * @deprecated 1.7035.1400 Use KB_Article_Registry::subscribe() instead
	 * @since      1.6030.1905
	 * @return     void
	 */
	public static function init() {
		self::register_articles();
	}

	/**
	 * Register all KB articles
	 *
	 * @since  1.6030.1905
	 * @return void
	 */
	private static function register_articles() {
		// Security Articles.
		self::register(
			'ssl-not-enforced',
			array(
				'title'       => __( 'Why SSL/HTTPS is Critical for WordPress Security', 'wpshadow' ),
				'url'         => 'https://wpshadow.com/kb/ssl-https-security/',
				'category'    => 'security',
				'difficulty'  => 'beginner',
				'read_time'   => 5,
				'diagnostics' => array( 'ssl-not-enforced', 'mixed-content-issues' ),
			)
		);

		self::register(
			'file-permissions',
			array(
				'title'       => __( 'Understanding WordPress File Permissions (chmod 644, 755)', 'wpshadow' ),
				'url'         => 'https://wpshadow.com/kb/file-permissions/',
				'category'    => 'security',
				'difficulty'  => 'intermediate',
				'read_time'   => 8,
				'diagnostics' => array( 'file-permissions-insecure', 'wp-config-permissions' ),
			)
		);

		self::register(
			'database-prefix',
			array(
				'title'       => __( 'Why Change wp_ Database Prefix? (Security Best Practice)', 'wpshadow' ),
				'url'         => 'https://wpshadow.com/kb/database-prefix/',
				'category'    => 'security',
				'difficulty'  => 'advanced',
				'read_time'   => 10,
				'diagnostics' => array( 'default-database-prefix' ),
			)
		);

		// Performance Articles.
		self::register(
			'php-memory-limit',
			array(
				'title'       => __( 'How to Increase PHP Memory Limit in WordPress', 'wpshadow' ),
				'url'         => 'https://wpshadow.com/kb/php-memory-limit/',
				'category'    => 'performance',
				'difficulty'  => 'beginner',
				'read_time'   => 6,
				'diagnostics' => array( 'memory-limit-low', 'wp-memory-limit' ),
			)
		);

		self::register(
			'caching-guide',
			array(
				'title'       => __( 'Complete Guide to WordPress Caching (Page, Object, Database)', 'wpshadow' ),
				'url'         => 'https://wpshadow.com/kb/caching-guide/',
				'category'    => 'performance',
				'difficulty'  => 'intermediate',
				'read_time'   => 15,
				'diagnostics' => array( 'no-caching-plugin', 'object-cache-disabled' ),
			)
		);

		self::register(
			'database-optimization',
			array(
				'title'       => __( 'How to Optimize WordPress Database for Speed', 'wpshadow' ),
				'url'         => 'https://wpshadow.com/kb/database-optimization/',
				'category'    => 'performance',
				'difficulty'  => 'advanced',
				'read_time'   => 12,
				'diagnostics' => array( 'database-bloated', 'unoptimized-tables' ),
			)
		);

		// Plugin/Theme Articles.
		self::register(
			'outdated-plugins',
			array(
				'title'       => __( 'Why Keeping Plugins Updated is Critical (Security & Performance)', 'wpshadow' ),
				'url'         => 'https://wpshadow.com/kb/outdated-plugins/',
				'category'    => 'maintenance',
				'difficulty'  => 'beginner',
				'read_time'   => 7,
				'diagnostics' => array( 'outdated-plugins', 'vulnerable-plugins' ),
			)
		);

		self::register(
			'unused-plugins',
			array(
				'title'       => __( 'Should You Deactivate or Delete Unused WordPress Plugins?', 'wpshadow' ),
				'url'         => 'https://wpshadow.com/kb/unused-plugins/',
				'category'    => 'maintenance',
				'difficulty'  => 'beginner',
				'read_time'   => 5,
				'diagnostics' => array( 'inactive-plugins', 'too-many-plugins' ),
			)
		);

		// Privacy/GDPR Articles.
		self::register(
			'gdpr-compliance',
			array(
				'title'       => __( 'GDPR Compliance Checklist for WordPress Sites', 'wpshadow' ),
				'url'         => 'https://wpshadow.com/kb/gdpr-compliance/',
				'category'    => 'privacy',
				'difficulty'  => 'intermediate',
				'read_time'   => 20,
				'diagnostics' => array( 'missing-privacy-policy', 'gdpr-non-compliant' ),
			)
		);

		self::register(
			'cookie-consent',
			array(
				'title'       => __( 'How to Add Cookie Consent Banner (GDPR/CCPA Compliant)', 'wpshadow' ),
				'url'         => 'https://wpshadow.com/kb/cookie-consent/',
				'category'    => 'privacy',
				'difficulty'  => 'beginner',
				'read_time'   => 8,
				'diagnostics' => array( 'missing-cookie-consent', 'tracking-without-consent' ),
			)
		);

		// SEO Articles.
		self::register(
			'seo-title-tags',
			array(
				'title'       => __( 'How to Write Perfect Title Tags for SEO (WordPress)', 'wpshadow' ),
				'url'         => 'https://wpshadow.com/kb/seo-title-tags/',
				'category'    => 'seo',
				'difficulty'  => 'beginner',
				'read_time'   => 10,
				'diagnostics' => array( 'missing-title-tags', 'duplicate-titles' ),
			)
		);

		self::register(
			'xml-sitemap',
			array(
				'title'       => __( 'XML Sitemaps Explained: Why Your WordPress Site Needs One', 'wpshadow' ),
				'url'         => 'https://wpshadow.com/kb/xml-sitemap/',
				'category'    => 'seo',
				'difficulty'  => 'beginner',
				'read_time'   => 7,
				'diagnostics' => array( 'missing-xml-sitemap', 'sitemap-errors' ),
			)
		);

		// Accessibility Articles.
		self::register(
			'wcag-compliance',
			array(
				'title'       => __( 'WCAG 2.1 Compliance Guide for WordPress (Level AA)', 'wpshadow' ),
				'url'         => 'https://wpshadow.com/kb/wcag-compliance/',
				'category'    => 'accessibility',
				'difficulty'  => 'advanced',
				'read_time'   => 25,
				'diagnostics' => array( 'accessibility-issues', 'wcag-violations' ),
			)
		);

		self::register(
			'alt-text-images',
			array(
				'title'       => __( 'How to Write Descriptive Alt Text for Images (Accessibility & SEO)', 'wpshadow' ),
				'url'         => 'https://wpshadow.com/kb/alt-text-images/',
				'category'    => 'accessibility',
				'difficulty'  => 'beginner',
				'read_time'   => 6,
				'diagnostics' => array( 'images-missing-alt-text' ),
			)
		);

		// Allow other modules to register articles.
		do_action( 'wpshadow_academy_register_kb_articles' );
	}

	/**
	 * Register a KB article
	 *
	 * @since  1.6030.1905
	 * @param  string $id Article ID.
	 * @param  array  $data Article data.
	 * @return void
	 */
	public static function register( $id, $data ) {
		self::$articles[ $id ] = $data;
	}

	/**
	 * Get article by ID
	 *
	 * @since  1.6030.1905
	 * @param  string $id Article ID.
	 * @return array|null Article data or null.
	 */
	public static function get( $id ) {
		return isset( self::$articles[ $id ] ) ? self::$articles[ $id ] : null;
	}

	/**
	 * Get article for diagnostic
	 *
	 * @since  1.6030.1905
	 * @param  string $diagnostic_slug Diagnostic slug.
	 * @return array|null Article data or null.
	 */
	public static function get_article_for_diagnostic( $diagnostic_slug ) {
		foreach ( self::$articles as $id => $article ) {
			if ( isset( $article['diagnostics'] ) && in_array( $diagnostic_slug, $article['diagnostics'], true ) ) {
				$article['id'] = $id;
				return $article;
			}
		}

		return null;
	}

	/**
	 * Get articles by category
	 *
	 * @since  1.6030.1905
	 * @param  string $category Category slug.
	 * @return array Articles in category.
	 */
	public static function get_by_category( $category ) {
		$results = array();

		foreach ( self::$articles as $id => $article ) {
			if ( isset( $article['category'] ) && $article['category'] === $category ) {
				$article['id'] = $id;
				$results[]     = $article;
			}
		}

		return $results;
	}

	/**
	 * Get all articles
	 *
	 * @since  1.6030.1905
	 * @return array All registered articles.
	 */
	public static function get_all() {
		$results = array();

		foreach ( self::$articles as $id => $article ) {
			$article['id'] = $id;
			$results[]     = $article;
		}

		return $results;
	}

	/**
	 * Search articles
	 *
	 * @since  1.6030.1905
	 * @param  string $query Search query.
	 * @return array Matching articles.
	 */
	public static function search( $query ) {
		$query   = strtolower( $query );
		$results = array();

		foreach ( self::$articles as $id => $article ) {
			$title = strtolower( $article['title'] );

			if ( strpos( $title, $query ) !== false ) {
				$article['id'] = $id;
				$results[]     = $article;
			}
		}

		return $results;
	}

	/**
	 * Get article categories
	 *
	 * @since  1.6030.1905
	 * @return array Category list with counts.
	 */
	public static function get_categories() {
		$categories = array();

		foreach ( self::$articles as $article ) {
			if ( isset( $article['category'] ) ) {
				$cat = $article['category'];
				if ( ! isset( $categories[ $cat ] ) ) {
					$categories[ $cat ] = array(
						'slug'  => $cat,
						'name'  => ucfirst( $cat ),
						'count' => 0,
					);
				}
				++$categories[ $cat ]['count'];
			}
		}

		return array_values( $categories );
	}
}
