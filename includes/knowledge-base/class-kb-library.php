<?php
/**
 * KB Library
 *
 * Manages KB article storage, retrieval, and caching.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * KB Library Manager
 */
class KB_Library {
	/**
	 * Option key for KB articles cache.
	 *
	 * @var string
	 */
	private static $cache_key = 'wpshadow_kb_articles_v1';

	/**
	 * Get an article by ID.
	 *
	 * @param string $article_id Article ID.
	 * @return array|null Article data or null if not found.
	 */
	public static function get_article( $article_id ) {
		$articles = self::get_all_articles();
		return isset( $articles[ $article_id ] ) ? $articles[ $article_id ] : null;
	}

	/**
	 * Get all KB articles.
	 *
	 * @return array Articles indexed by ID.
	 */
	public static function get_all_articles() {
		$cache = get_option( self::$cache_key, array() );

		if ( ! empty( $cache ) ) {
			return $cache;
		}

		// Generate articles from registry
		$articles = self::generate_articles_from_registry();

		// Cache for 24 hours
		update_option( self::$cache_key, $articles );

		return $articles;
	}

	/**
	 * Get articles by category.
	 *
	 * @param string $category Category name.
	 * @return array Articles in category.
	 */
	public static function get_by_category( $category ) {
		$articles = self::get_all_articles();
		$result   = array();

		foreach ( $articles as $id => $article ) {
			if ( isset( $article['category'] ) && $article['category'] === $category ) {
				$result[ $id ] = $article;
			}
		}

		return $result;
	}

	/**
	 * Get articles by type.
	 *
	 * @param string $type Type: 'diagnostic' or 'treatment'.
	 * @return array Articles of type.
	 */
	public static function get_by_type( $type ) {
		$articles = self::get_all_articles();
		$result   = array();

		foreach ( $articles as $id => $article ) {
			if ( isset( $article['type'] ) && $article['type'] === $type ) {
				$result[ $id ] = $article;
			}
		}

		return $result;
	}

	/**
	 * Search articles by keyword.
	 *
	 * @param string $keyword Search keyword.
	 * @return array Matching articles.
	 */
	public static function search( $keyword ) {
		$articles = self::get_all_articles();
		$keyword  = strtolower( sanitize_text_field( $keyword ) );
		$results  = array();

		foreach ( $articles as $id => $article ) {
			$searchable = strtolower(
				$article['title'] . ' ' .
				( isset( $article['description'] ) ? $article['description'] : '' ) . ' ' .
				( isset( $article['category'] ) ? $article['category'] : '' )
			);

			if ( strpos( $searchable, $keyword ) !== false ) {
				$results[ $id ] = $article;
			}
		}

		return $results;
	}

	/**
	 * Clear KB cache.
	 *
	 * @return void
	 */
	public static function clear_cache() {
		delete_option( self::$cache_key );
	}

	/**
	 * Generate articles from diagnostic and treatment registries.
	 *
	 * @return array Articles.
	 */
	private static function generate_articles_from_registry() {
		$articles = array();

		// Load diagnostic registry and generate articles
		if ( class_exists( '\\WPShadow\\Diagnostics\\Diagnostic_Registry' ) ) {
			$diagnostics = \WPShadow\Diagnostics\Diagnostic_Registry::get_all();

			foreach ( $diagnostics as $id => $diagnostic ) {
				$article = KB_Article_Generator::generate_diagnostic_article( $id, $diagnostic );
				if ( $article ) {
					$articles[ $article['id'] ] = $article;
				}
			}
		}

		// Load treatment registry and generate articles
		if ( class_exists( '\\WPShadow\\Treatments\\Treatment_Registry' ) ) {
			$treatments = \WPShadow\Treatments\Treatment_Registry::get_all();

			foreach ( $treatments as $id => $treatment ) {
				$article = KB_Article_Generator::generate_treatment_article( $id, $treatment );
				if ( $article ) {
					$articles[ $article['id'] ] = $article;
				}
			}
		}

		return $articles;
	}

	/**
	 * Get related articles for an article.
	 *
	 * @param string $article_id Article ID.
	 * @param int    $limit      Max number of related articles.
	 * @return array Related articles.
	 */
	public static function get_related_articles( $article_id, $limit = 3 ) {
		$article = self::get_article( $article_id );

		if ( ! $article ) {
			return array();
		}

		// Get articles in same category
		$related  = array();
		$category = isset( $article['category'] ) ? $article['category'] : '';

		if ( ! empty( $category ) ) {
			$category_articles = self::get_by_category( $category );

			foreach ( $category_articles as $id => $related_article ) {
				if ( $id !== $article_id ) {
					$related[ $id ] = $related_article;
				}

				if ( count( $related ) >= $limit ) {
					break;
				}
			}
		}

		return $related;
	}

	/**
	 * Get statistics about KB library.
	 *
	 * @return array Statistics.
	 */
	public static function get_stats() {
		$articles = self::get_all_articles();

		$stats = array(
			'total_articles'      => count( $articles ),
			'diagnostic_articles' => count( self::get_by_type( 'diagnostic' ) ),
			'treatment_articles'  => count( self::get_by_type( 'treatment' ) ),
			'categories'          => array(),
		);

		// Count by category
		foreach ( $articles as $article ) {
			if ( isset( $article['category'] ) ) {
				if ( ! isset( $stats['categories'][ $article['category'] ] ) ) {
					$stats['categories'][ $article['category'] ] = 0;
				}
				++$stats['categories'][ $article['category'] ];
			}
		}

		return $stats;
	}
}
