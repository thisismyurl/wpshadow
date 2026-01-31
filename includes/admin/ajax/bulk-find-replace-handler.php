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
		// Get all posts of the specified types using WordPress API
		$posts = get_posts(
			array(
				'post_type'      => $post_types,
				'posts_per_page' => -1,
				'post_status'    => 'any',
				'fields'         => 'ids',
			)
		);

		$matches  = 0;
		$replaced = 0;

		// Search for matches using WordPress functions
		foreach ( $posts as $post_id ) {
			$post_data = get_post( $post_id );
			$field_value = $post_data->$field ?? '';

			// Check if find text is in the field
			if ( empty( $field_value ) ) {
				continue;
			}

			$found_count = 0;
			if ( $case_sensitive ) {
				// Case-sensitive search
				$found_count = substr_count( $field_value, $find );
			} else {
				// Case-insensitive search
				$found_count = substr_count( strtolower( $field_value ), strtolower( $find ) );
			}

			if ( $found_count > 0 ) {
				$matches += $found_count;

				if ( ! $dry_run ) {
					// Perform replacement
					if ( $case_sensitive ) {
						$new_value = str_replace( $find, $replace, $field_value );
					} else {
						// Case-insensitive replace
						$new_value = preg_replace(
							'/' . preg_quote( $find, '/' ) . '/i',
							$replace,
							$field_value
						);
					}

					// Update post using WordPress API
					wp_update_post(
						array(
							'ID'       => $post_id,
							$field     => $new_value,
						)
					);
					$replaced += $found_count;
				}
			}
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
		// Get all post IDs using WordPress API
		$post_ids = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => -1,
				'post_status'    => 'any',
				'fields'         => 'ids',
			)
		);

		$matches  = 0;
		$replaced = 0;

		// Search through post meta using WordPress functions
		foreach ( $post_ids as $post_id ) {
			$meta_data = get_post_meta( $post_id );

			foreach ( $meta_data as $meta_key => $meta_values ) {
				// get_post_meta returns single value as string, multiple as array
				$values = is_array( $meta_values ) ? $meta_values : array( $meta_values );

				foreach ( $values as $meta_value ) {
					// Skip if not a string
					if ( ! is_string( $meta_value ) ) {
						continue;
					}

					$found_count = 0;
					if ( $case_sensitive ) {
						$found_count = substr_count( $meta_value, $find );
					} else {
						$found_count = substr_count( strtolower( $meta_value ), strtolower( $find ) );
					}

					if ( $found_count > 0 ) {
						$matches += $found_count;

						if ( ! $dry_run ) {
							// Perform replacement
							if ( $case_sensitive ) {
								$new_value = str_replace( $find, $replace, $meta_value );
							} else {
								$new_value = preg_replace(
									'/' . preg_quote( $find, '/' ) . '/i',
									$replace,
									$meta_value
								);
							}

							// Update post meta using WordPress API
							update_post_meta( $post_id, $meta_key, $new_value, $meta_value );
							$replaced += $found_count;
						}
					}
				}
			}
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
		// Get all options using WordPress API
		$all_options = wp_load_alloptions();

		$matches  = 0;
		$replaced = 0;

		// Search through options using WordPress functions
		foreach ( $all_options as $option_name => $option_value ) {
			// Skip if not a string
			if ( ! is_string( $option_value ) ) {
				continue;
			}

			// Count occurrences
			$found_count = 0;
			if ( $case_sensitive ) {
				$found_count = substr_count( $option_value, $find );
			} else {
				$found_count = substr_count( strtolower( $option_value ), strtolower( $find ) );
			}

			if ( $found_count > 0 ) {
				$matches += $found_count;

				if ( ! $dry_run ) {
					// Perform replacement
					if ( $case_sensitive ) {
						$new_value = str_replace( $find, $replace, $option_value );
					} else {
						$new_value = preg_replace(
							'/' . preg_quote( $find, '/' ) . '/i',
							$replace,
							$option_value
						);
					}

					// Update option using WordPress API
					update_option( $option_name, $new_value );
					$replaced += $found_count;
				}
			}
	 * @param  string $find           Text to find.
	 * @param  string $replace        Replacement text.
	 * @param  bool   $case_sensitive Case sensitive search.
	 * @param  bool   $whole_word     Whole word only.
	 * @param  bool   $dry_run        Dry run mode.
	 * @return array Results.
	 */
	private static function find_replace_in_comments( $find, $replace, $case_sensitive, $whole_word, $dry_run ) {
		// Get all comments using WordPress API
		$comments = get_comments(
			array(
				'number'       => 0,
				'status'       => 'any',
				'type'         => 'comment',
				'fields'       => 'ids',
			)
		);

		$matches  = 0;
		$replaced = 0;

		// Search through comments using WordPress functions
		foreach ( $comments as $comment_id ) {
			$comment = get_comment( $comment_id );

			if ( ! $comment || empty( $comment->comment_content ) ) {
				continue;
			}

			// Count occurrences
			$found_count = 0;
			if ( $case_sensitive ) {
				$found_count = substr_count( $comment->comment_content, $find );
			} else {
				$found_count = substr_count( strtolower( $comment->comment_content ), strtolower( $find ) );
			}

			if ( $found_count > 0 ) {
				$matches += $found_count;

				if ( ! $dry_run ) {
					// Perform replacement
					if ( $case_sensitive ) {
						$new_content = str_replace( $find, $replace, $comment->comment_content );
					} else {
						$new_content = preg_replace(
							'/' . preg_quote( $find, '/' ) . '/i',
							$replace,
							$comment->comment_content
						);
					}

					// Update comment using WordPress API
					wp_update_comment(
						array(
							'comment_ID'      => $comment_id,
							'comment_content' => $new_content,
						)
					);
					$replaced += $found_count;
				}
			}
		}

		return array(
			'matches'  => $matches,
			'replaced' => $replaced,
		);
	}
}

// Register AJAX action
\add_action( 'wp_ajax_wpshadow_bulk_find_replace', array( '\WPShadow\\Admin\\AJAX_Bulk_Find_Replace', 'handle' ) );
