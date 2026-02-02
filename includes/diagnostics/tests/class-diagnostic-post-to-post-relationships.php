<?php
/**
 * Post-to-Post Relationships Diagnostic
 *
 * Checks if post-to-post connections work correctly (ACF Relationship fields, Pods, etc).
 * Tests relationship integrity and detects broken connections.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post-to-Post Relationships Diagnostic Class
 *
 * Checks for issues in post relationship connections.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Post_To_Post_Relationships extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-to-post-relationships';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post-to-Post Relationships';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates post-to-post relationship connections and data integrity';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check for ACF relationship fields.
		$acf_relationships = $wpdb->get_results(
			"SELECT DISTINCT meta_key
			FROM {$wpdb->postmeta}
			WHERE meta_key LIKE '%\\_relationship%'
			OR meta_key LIKE '%\\_post\\_object%'
			OR meta_value LIKE 'field\\_%'
			LIMIT 50",
			ARRAY_A
		);

		$has_relationships = ! empty( $acf_relationships );

		// Check for serialized relationship data.
		$serialized_relationships = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->postmeta}
			WHERE (meta_key LIKE '%relationship%' OR meta_key LIKE '%related%')
			AND meta_value LIKE 'a:%'"
		);

		if ( $serialized_relationships > 0 ) {
			$has_relationships = true;

			// Check for broken serialized relationships (invalid post IDs).
			$broken_relationships = 0;
			$sample_relationships = $wpdb->get_results(
				"SELECT meta_id, post_id, meta_key, meta_value
				FROM {$wpdb->postmeta}
				WHERE (meta_key LIKE '%relationship%' OR meta_key LIKE '%related%')
				AND meta_value LIKE 'a:%'
				LIMIT 100",
				ARRAY_A
			);

			foreach ( $sample_relationships as $relationship ) {
				$unserialized = @unserialize( $relationship['meta_value'] ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

				if ( is_array( $unserialized ) ) {
					foreach ( $unserialized as $related_id ) {
						if ( is_numeric( $related_id ) && $related_id > 0 ) {
							$exists = $wpdb->get_var(
								$wpdb->prepare(
									"SELECT ID FROM {$wpdb->posts} WHERE ID = %d",
									$related_id
								)
							);

							if ( ! $exists ) {
								++$broken_relationships;
								break;
							}
						}
					}
				}
			}

			if ( $broken_relationships > 10 ) {
				$issues[] = sprintf(
					/* translators: %d: number of broken relationships */
					__( '%d posts have relationship data pointing to deleted posts', 'wpshadow' ),
					$broken_relationships
				);
			}
		}

		// Check for Posts 2 Posts plugin relationships.
		$p2p_exists = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM information_schema.tables
			WHERE table_schema = '" . DB_NAME . "'
			AND table_name = '{$wpdb->prefix}p2p'"
		);

		if ( $p2p_exists > 0 ) {
			$has_relationships = true;

			// Check for broken P2P connections.
			$broken_p2p = $wpdb->get_var(
				"SELECT COUNT(p2p.p2p_id)
				FROM {$wpdb->prefix}p2p p2p
				LEFT JOIN {$wpdb->posts} p1 ON p2p.p2p_from = p1.ID
				LEFT JOIN {$wpdb->posts} p2 ON p2p.p2p_to = p2.ID
				WHERE p1.ID IS NULL OR p2.ID IS NULL"
			);

			if ( $broken_p2p > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of broken P2P connections */
					__( '%d Posts 2 Posts connections reference deleted posts', 'wpshadow' ),
					$broken_p2p
				);
			}
		}

		// Check for meta keys that suggest relationships.
		$relationship_meta_keys = $wpdb->get_results(
			"SELECT DISTINCT meta_key, COUNT(*) as usage
			FROM {$wpdb->postmeta}
			WHERE meta_key LIKE '%related%'
			OR meta_key LIKE '%connection%'
			OR meta_key LIKE '%link%'
			GROUP BY meta_key
			HAVING usage > 10
			ORDER BY usage DESC
			LIMIT 20",
			ARRAY_A
		);

		if ( ! empty( $relationship_meta_keys ) ) {
			$has_relationships = true;

			// Check for numeric relationship values pointing to non-existent posts.
			foreach ( $relationship_meta_keys as $key_data ) {
				$key = $key_data['meta_key'];

				// Sample values for this key.
				$sample_values = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT meta_value
						FROM {$wpdb->postmeta}
						WHERE meta_key = %s
						AND meta_value REGEXP '^[0-9]+$'
						LIMIT 50",
						$key
					)
				);

				$broken_count = 0;
				foreach ( $sample_values as $value ) {
					$exists = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT ID FROM {$wpdb->posts} WHERE ID = %d",
							(int) $value
						)
					);

					if ( ! $exists ) {
						++$broken_count;
					}
				}

				if ( $broken_count > 5 ) {
					$issues[] = sprintf(
						/* translators: 1: meta key, 2: number of broken relationships */
						__( 'Meta key "%1$s" has %2$d broken post references', 'wpshadow' ),
						esc_html( $key ),
						$broken_count
					);
					break; // Only report once.
				}
			}
		}

		// If no relationship data found, return early.
		if ( ! $has_relationships && empty( $issues ) ) {
			return null;
		}

		// Check for bidirectional relationship consistency.
		if ( ! empty( $relationship_meta_keys ) ) {
			$bidirectional_keys = array();
			foreach ( $relationship_meta_keys as $key_data ) {
				// Look for paired keys (e.g., "related_posts" and "related_to").
				if ( strpos( $key_data['meta_key'], 'related' ) !== false ) {
					$bidirectional_keys[] = $key_data['meta_key'];
				}
			}

			if ( count( $bidirectional_keys ) > 1 ) {
				// Sample check for consistency.
				$inconsistent = 0;
				foreach ( array_slice( $bidirectional_keys, 0, 5 ) as $key ) {
					$sample = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT post_id, meta_value
							FROM {$wpdb->postmeta}
							WHERE meta_key = %s
							AND meta_value REGEXP '^[0-9]+$'
							LIMIT 20",
							$key
						),
						ARRAY_A
					);

					foreach ( $sample as $relationship ) {
						$post_id = (int) $relationship['post_id'];
						$related_id = (int) $relationship['meta_value'];

						// Check if reverse relationship exists.
						$reverse_exists = $wpdb->get_var(
							$wpdb->prepare(
								"SELECT COUNT(*)
								FROM {$wpdb->postmeta}
								WHERE post_id = %d
								AND meta_value = %s
								AND meta_key LIKE '%related%'",
								$related_id,
								(string) $post_id
							)
						);

						if ( 0 === (int) $reverse_exists ) {
							++$inconsistent;
						}
					}
				}

				if ( $inconsistent > 10 ) {
					$issues[] = sprintf(
						/* translators: %d: number of inconsistent relationships */
						__( '%d one-way relationships detected (missing reverse connections)', 'wpshadow' ),
						$inconsistent
					);
				}
			}
		}

		// Check for self-referential relationships.
		$self_relationships = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->postmeta}
			WHERE meta_key LIKE '%related%'
			AND CAST(meta_value AS UNSIGNED) = post_id
			AND meta_value REGEXP '^[0-9]+$'"
		);

		if ( $self_relationships > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of self-referential relationships */
				__( '%d posts related to themselves (likely configuration error)', 'wpshadow' ),
				$self_relationships
			);
		}

		// Check for excessive relationships per post.
		$excessive_relationships = $wpdb->get_results(
			"SELECT post_id, COUNT(*) as rel_count
			FROM {$wpdb->postmeta}
			WHERE meta_key LIKE '%related%'
			OR meta_key LIKE '%relationship%'
			GROUP BY post_id
			HAVING rel_count > 50
			LIMIT 10",
			ARRAY_A
		);

		if ( ! empty( $excessive_relationships ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with excessive relationships */
				__( '%d posts have 50+ relationship connections (may impact performance)', 'wpshadow' ),
				count( $excessive_relationships )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/post-to-post-relationships',
			);
		}

		return null;
	}
}
