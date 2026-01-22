<?php
/**
 * KB Search
 *
 * Full-text search across KB articles with indexing.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * KB Search Engine
 */
class KB_Search {
	/**
	 * Index articles for full-text search.
	 *
	 * @return int Number of articles indexed.
	 */
	public static function build_index() {
		$articles = KB_Library::get_all_articles();
		$index    = array();

		foreach ( $articles as $id => $article ) {
			$searchable   = self::extract_searchable_text( $article );
			$index[ $id ] = array(
				'title'      => isset( $article['title'] ) ? $article['title'] : '',
				'searchable' => $searchable,
				'category'   => isset( $article['category'] ) ? $article['category'] : '',
				'type'       => isset( $article['type'] ) ? $article['type'] : '',
			);
		}

		update_option( 'wpshadow_kb_search_index_v1', $index );

		return count( $index );
	}

	/**
	 * Search the KB.
	 *
	 * @param string $query Search query.
	 * @param array  $filters Optional filters (category, type).
	 * @param int    $limit Max results to return.
	 * @return array Search results.
	 */
	public static function search( $query, $filters = array(), $limit = 10 ) {
		$query = sanitize_text_field( trim( wp_unslash( $query ) ) );
		$index = get_option( 'wpshadow_kb_search_index_v1', array() );

		if ( empty( $index ) ) {
			self::build_index();
			$index = get_option( 'wpshadow_kb_search_index_v1', array() );
		}

		if ( empty( $query ) || empty( $index ) ) {
			return array();
		}

		$results  = array();
		$keywords = self::tokenize_query( $query );

		foreach ( $index as $article_id => $entry ) {
			// Apply filters
			if ( isset( $filters['category'] ) && $entry['category'] !== $filters['category'] ) {
				continue;
			}
			if ( isset( $filters['type'] ) && $entry['type'] !== $filters['type'] ) {
				continue;
			}

			// Calculate relevance score
			$score = self::calculate_relevance( $keywords, $entry );

			if ( $score > 0 ) {
				$article          = KB_Library::get_article( $article_id );
				$article['score'] = $score;
				$results[]        = $article;
			}
		}

		// Sort by relevance score
		usort(
			$results,
			function ( $a, $b ) {
				return $b['score'] <=> $a['score'];
			}
		);

		return array_slice( $results, 0, $limit );
	}

	/**
	 * Extract searchable text from article.
	 *
	 * @param array $article Article data.
	 * @return string Searchable text.
	 */
	private static function extract_searchable_text( $article ) {
		$text = '';

		if ( isset( $article['title'] ) ) {
			$text .= $article['title'] . ' ';
		}

		if ( isset( $article['description'] ) ) {
			$text .= $article['description'] . ' ';
		}

		if ( isset( $article['content'] ) ) {
			$text .= wp_strip_all_tags( $article['content'] ) . ' ';
		}

		if ( isset( $article['category'] ) ) {
			$text .= $article['category'] . ' ';
		}

		return strtolower( $text );
	}

	/**
	 * Tokenize search query.
	 *
	 * @param string $query Search query.
	 * @return array Keywords.
	 */
	private static function tokenize_query( $query ) {
		$tokens   = explode( ' ', $query );
		$keywords = array();

		foreach ( $tokens as $token ) {
			$token = trim( $token );
			if ( strlen( $token ) > 2 ) {
				$keywords[] = $token;
			}
		}

		return $keywords;
	}

	/**
	 * Calculate relevance score for a search result.
	 *
	 * @param array $keywords Keywords from query.
	 * @param array $entry    Index entry.
	 * @return float Relevance score (0-100).
	 */
	private static function calculate_relevance( $keywords, $entry ) {
		$score      = 0;
		$searchable = $entry['searchable'];
		$title      = strtolower( $entry['title'] );

		foreach ( $keywords as $keyword ) {
			// Title matches are worth more
			if ( strpos( $title, $keyword ) !== false ) {
				$score += 30;
			}

			// Content matches
			if ( strpos( $searchable, $keyword ) !== false ) {
				$score += 10;

				// Count occurrences
				$count  = substr_count( $searchable, $keyword );
				$score += min( $count * 2, 20 ); // Max 20 bonus for multiple occurrences
			}
		}

		return min( $score, 100 );
	}

	/**
	 * Get search suggestions as user types.
	 *
	 * @param string $partial Partial query.
	 * @param int    $limit Max suggestions.
	 * @return array Suggestions.
	 */
	public static function get_suggestions( $partial, $limit = 5 ) {
		$partial = sanitize_text_field( trim( wp_unslash( $partial ) ) );

		if ( strlen( $partial ) < 2 ) {
			return array();
		}

		$articles    = KB_Library::get_all_articles();
		$suggestions = array();

		foreach ( $articles as $article ) {
			$title = $article['title'] ?? '';

			if ( stripos( $title, $partial ) === 0 ) {
				// Matches start of title
				$suggestions[] = array(
					'text' => $title,
					'type' => 'title',
				);
			}
		}

		return array_slice( $suggestions, 0, $limit );
	}

	/**
	 * Get popular searches.
	 *
	 * @param int $limit Max items.
	 * @return array Popular searches.
	 */
	public static function get_popular_searches( $limit = 5 ) {
		$stats = get_option( 'wpshadow_kb_search_stats', array() );

		if ( empty( $stats ) ) {
			return array();
		}

		// Sort by count
		arsort( $stats );

		return array_slice( array_keys( $stats ), 0, $limit );
	}

	/**
	 * Track a search for analytics.
	 *
	 * @param string $query Search query.
	 * @return void
	 */
	public static function track_search( $query ) {
		$query = sanitize_text_field( $query );
		$stats = get_option( 'wpshadow_kb_search_stats', array() );

		if ( ! isset( $stats[ $query ] ) ) {
			$stats[ $query ] = 0;
		}

		++$stats[ $query ];

		// Keep history to 100 top searches
		if ( count( $stats ) > 100 ) {
			arsort( $stats );
			$stats = array_slice( $stats, 0, 100 );
		}

		update_option( 'wpshadow_kb_search_stats', $stats );
	}
}
