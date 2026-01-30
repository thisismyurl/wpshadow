<?php
/**
 * AJAX: Bulk Find and Replace
 *
 * @since   1.2601.2200
 * @package WPShadow\Admin
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Activity_Logger;
use WPShadow\Core\Error_Handler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bulk Find Replace Handler
 */
class AJAX_Bulk_Find_Replace extends AJAX_Handler_Base {
	/**
	 * Handle the AJAX request.
	 *
	 * @since 1.2601.2200
	 * @return void
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_bulk_find_replace', 'manage_options' );

		$find_text       = self::get_post_param( 'find_text', 'text', '', true );
		$replace_text    = self::get_post_param( 'replace_text', 'text', '', true );
		$search_scope    = self::get_post_param( 'search_scope', 'array', array() );
		$post_types      = self::get_post_param( 'post_types', 'array', array( 'post', 'page' ) );
		$case_sensitive  = rest_sanitize_boolean( self::get_post_param( 'case_sensitive', 'bool', false ) );
		$whole_word      = rest_sanitize_boolean( self::get_post_param( 'whole_word', 'bool', false ) );
		$dry_run         = rest_sanitize_boolean( self::get_post_param( 'dry_run', 'bool', true ) );

		if ( empty( $find_text ) ) {
			self::send_error( __( 'Find text is required', 'wpshadow' ) );
			return;
		}

		if ( empty( $search_scope ) ) {
			self::send_error( __( 'Please select at least one search scope', 'wpshadow' ) );
			return;
		}

		try {
			$results = array(
				'total_matches' => 0,
				'replacements'  => 0,
				'tables'        => array(),
			);

			global $wpdb;

			// Search in post content
			if ( in_array( 'content', $search_scope, true ) ) {
				$content_result = self::find_replace_in_posts(
					$find_text,
					$replace_text,
					$post_types,
					'post_content',
					$case_sensitive,
					$whole_word,
					$dry_run
				);
				$results['tables']['post_content'] = $content_result;
				$results['total_matches']         += $content_result['matches'];
				$results['replacements']          += $content_result['replaced'];
			}

			// Search in post excerpts
			if ( in_array( 'excerpt', $search_scope, true ) ) {
				$excerpt_result = self::find_replace_in_posts(
					$find_text,
					$replace_text,
					$post_types,
					'post_excerpt',
					$case_sensitive,
					$whole_word,
					$dry_run
				);
				$results['tables']['post_excerpt'] = $excerpt_result;
				$results['total_matches']         += $excerpt_result['matches'];
				$results['replacements']          += $excerpt_result['replaced'];
			}

			// Search in post meta
			if ( in_array( 'meta', $search_scope, true ) ) {
				$meta_result = self::find_replace_in_postmeta(
					$find_text,
					$replace_text,
					$case_sensitive,
					$whole_word,
					$dry_run
				);
				$results['tables']['postmeta'] = $meta_result;
				$results['total_matches']     += $meta_result['matches'];
				$results['replacements']      += $meta_result['replaced'];
			}

			// Search in options
			if ( in_array( 'options', $search_scope, true ) ) {
				$options_result = self::find_replace_in_options(
					$find_text,
					$replace_text,
					$case_sensitive,
					$whole_word,
					$dry_run
				);
				$results['tables']['options'] = $options_result;
				$results['total_matches']    += $options_result['matches'];
				$results['replacements']     += $options_result['replaced'];
			}

			// Search in comments
			if ( in_array( 'comments', $search_scope, true ) ) {
				$comments_result = self::find_replace_in_comments(
					$find_text,
					$replace_text,
					$case_sensitive,
					$whole_word,
					$dry_run
				);
				$results['tables']['comments'] = $comments_result;
				$results['total_matches']     += $comments_result['matches'];
				$results['replacements']      += $comments_result['replaced'];
			}

			// Log activity if not dry run
			if ( ! $dry_run ) {
				Activity_Logger::log(
					'bulk_find_replace_executed',
					array(
						'find_text'     => $find_text,
						'replace_text'  => $replace_text,
						'replacements'  => $results['replacements'],
						'search_scope'  => $search_scope,
					)
				);
			}

			self::send_success(
				array(
					'message'  => $dry_run ? __( 'Preview completed', 'wpshadow' ) : __( 'Replacement completed successfully', 'wpshadow' ),
					'dry_run'  => $dry_run,
					'results'  => $results,
				)
			);

		} catch ( \Exception $e ) {
			Error_Handler::log_error( $e->getMessage(), $e );
			self::send_error( $e->getMessage() );
		}
	}

	/**
	 * Find and replace in posts.
	 *
	 * @since  1.2601.2200
	 * @param  string $find           Text to find.
	 * @param  string $replace        Replacement text.
	 * @param  array  $post_types     Post types to search.
	 * @param  string $field          Field to search (post_content/post_excerpt).
	 * @param  bool   $case_sensitive Case sensitive search.
	 * @param  bool   $whole_word     Whole word only.
	 * @param  bool   $dry_run        Dry run mode.
	 * @return array Results.
	 */
	private static function find_replace_in_posts( $find, $replace, $post_types, $field, $case_sensitive, $whole_word, $dry_run ) {
		global $wpdb;

		// Build LIKE pattern
		$like_pattern = '%' . $wpdb->esc_like( $find ) . '%';

		// Build post types IN clause
		$post_types_placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );

		// Count matches
		$count_query = $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE {$field} LIKE %s 
			AND post_type IN ({$post_types_placeholders})", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			array_merge( array( $like_pattern ), $post_types )
		);
		$matches = (int) $wpdb->get_var( $count_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( $dry_run || 0 === $matches ) {
			return array(
				'matches'  => $matches,
				'replaced' => 0,
			);
		}

		// Perform replacement
		if ( $case_sensitive ) {
			$replaced = $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->posts} 
					SET {$field} = REPLACE(BINARY {$field}, %s, %s) 
					WHERE {$field} LIKE %s 
					AND post_type IN ({$post_types_placeholders})", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					array_merge( array( $find, $replace, $like_pattern ), $post_types )
				)
			);
		} else {
			$replaced = $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->posts} 
					SET {$field} = REPLACE({$field}, %s, %s) 
					WHERE {$field} LIKE %s 
					AND post_type IN ({$post_types_placeholders})", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					array_merge( array( $find, $replace, $like_pattern ), $post_types )
				)
			);
		}

		return array(
			'matches'  => $matches,
			'replaced' => $replaced,
		);
	}

	/**
	 * Find and replace in postmeta.
	 *
	 * @since  1.2601.2200
	 * @param  string $find           Text to find.
	 * @param  string $replace        Replacement text.
	 * @param  bool   $case_sensitive Case sensitive search.
	 * @param  bool   $whole_word     Whole word only.
	 * @param  bool   $dry_run        Dry run mode.
	 * @return array Results.
	 */
	private static function find_replace_in_postmeta( $find, $replace, $case_sensitive, $whole_word, $dry_run ) {
		global $wpdb;

		$like_pattern = '%' . $wpdb->esc_like( $find ) . '%';

		// Count matches
		$matches = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_value LIKE %s",
				$like_pattern
			)
		);

		if ( $dry_run || 0 === $matches ) {
			return array(
				'matches'  => $matches,
				'replaced' => 0,
			);
		}

		// Perform replacement
		if ( $case_sensitive ) {
			$replaced = $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->postmeta} 
					SET meta_value = REPLACE(BINARY meta_value, %s, %s) 
					WHERE meta_value LIKE %s",
					$find,
					$replace,
					$like_pattern
				)
			);
		} else {
			$replaced = $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->postmeta} 
					SET meta_value = REPLACE(meta_value, %s, %s) 
					WHERE meta_value LIKE %s",
					$find,
					$replace,
					$like_pattern
				)
			);
		}

		return array(
			'matches'  => $matches,
			'replaced' => $replaced,
		);
	}

	/**
	 * Find and replace in options.
	 *
	 * @since  1.2601.2200
	 * @param  string $find           Text to find.
	 * @param  string $replace        Replacement text.
	 * @param  bool   $case_sensitive Case sensitive search.
	 * @param  bool   $whole_word     Whole word only.
	 * @param  bool   $dry_run        Dry run mode.
	 * @return array Results.
	 */
	private static function find_replace_in_options( $find, $replace, $case_sensitive, $whole_word, $dry_run ) {
		global $wpdb;

		$like_pattern = '%' . $wpdb->esc_like( $find ) . '%';

		// Count matches
		$matches = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_value LIKE %s",
				$like_pattern
			)
		);

		if ( $dry_run || 0 === $matches ) {
			return array(
				'matches'  => $matches,
				'replaced' => 0,
			);
		}

		// Perform replacement
		if ( $case_sensitive ) {
			$replaced = $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->options} 
					SET option_value = REPLACE(BINARY option_value, %s, %s) 
					WHERE option_value LIKE %s",
					$find,
					$replace,
					$like_pattern
				)
			);
		} else {
			$replaced = $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->options} 
					SET option_value = REPLACE(option_value, %s, %s) 
					WHERE option_value LIKE %s",
					$find,
					$replace,
					$like_pattern
				)
			);
		}

		return array(
			'matches'  => $matches,
			'replaced' => $replaced,
		);
	}

	/**
	 * Find and replace in comments.
	 *
	 * @since  1.2601.2200
	 * @param  string $find           Text to find.
	 * @param  string $replace        Replacement text.
	 * @param  bool   $case_sensitive Case sensitive search.
	 * @param  bool   $whole_word     Whole word only.
	 * @param  bool   $dry_run        Dry run mode.
	 * @return array Results.
	 */
	private static function find_replace_in_comments( $find, $replace, $case_sensitive, $whole_word, $dry_run ) {
		global $wpdb;

		$like_pattern = '%' . $wpdb->esc_like( $find ) . '%';

		// Count matches
		$matches = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_content LIKE %s",
				$like_pattern
			)
		);

		if ( $dry_run || 0 === $matches ) {
			return array(
				'matches'  => $matches,
				'replaced' => 0,
			);
		}

		// Perform replacement
		if ( $case_sensitive ) {
			$replaced = $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->comments} 
					SET comment_content = REPLACE(BINARY comment_content, %s, %s) 
					WHERE comment_content LIKE %s",
					$find,
					$replace,
					$like_pattern
				)
			);
		} else {
			$replaced = $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->comments} 
					SET comment_content = REPLACE(comment_content, %s, %s) 
					WHERE comment_content LIKE %s",
					$find,
					$replace,
					$like_pattern
				)
			);
		}

		return array(
			'matches'  => $matches,
			'replaced' => $replaced,
		);
	}
}

// Register AJAX action
\add_action( 'wp_ajax_wpshadow_bulk_find_replace', array( '\WPShadow\\Admin\\AJAX_Bulk_Find_Replace', 'handle' ) );
