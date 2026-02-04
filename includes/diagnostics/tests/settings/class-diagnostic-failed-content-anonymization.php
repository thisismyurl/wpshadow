<?php
/**
 * Failed Content Anonymization Diagnostic
 *
 * Tests whether user-generated content (comments, posts) is properly anonymized vs deleted.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Privacy
 * @since      1.2034.1450
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Failed_Content_Anonymization Class
 *
 * Verifies that user content is properly anonymized during GDPR erasure.
 *
 * @since 1.2034.1450
 */
class Diagnostic_Failed_Content_Anonymization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'failed-content-anonymization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Anonymization Process';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies that user content is properly anonymized during data erasure';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2034.1450
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// 1. Check if comment anonymization function exists.
		if ( ! function_exists( 'wp_privacy_anonymize_data' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WordPress anonymization functionality is not available', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-anonymization',
			);
		}

		// 2. Check comment erasure configuration.
		$erasers = apply_filters( 'wp_privacy_personal_data_erasers', array() );
		
		$has_comment_eraser = false;
		foreach ( $erasers as $eraser ) {
			if ( isset( $eraser['eraser_friendly_name'] ) &&
			     false !== strpos( strtolower( $eraser['eraser_friendly_name'] ), 'comment' ) ) {
				$has_comment_eraser = true;
				break;
			}
		}

		if ( ! $has_comment_eraser ) {
			$issues[] = __( 'No comment eraser registered - comments may not be anonymized', 'wpshadow' );
		}

		// 3. Check for comments with identifiable information.
		$comment_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments}" );
		
		if ( (int) $comment_count > 0 ) {
			// Sample check: Look for comments with email addresses or URLs.
			$identifiable_comments = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->comments} 
				WHERE comment_author_email != '' 
				OR comment_author_url != ''"
			);

			if ( (int) $identifiable_comments > 0 ) {
				// This is normal - but verify anonymization process.
				$anon_string = wp_privacy_anonymize_data( 'text', 'Test User' );
				
				if ( 'Test User' === $anon_string ) {
					$issues[] = __( 'Comment anonymization function not working correctly', 'wpshadow' );
				}
			}
		}

		// 4. Check post author handling.
		$post_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_author > 0 AND post_type IN (%s, %s, %s)",
				'post',
				'page',
				'attachment'
			)
		);

		if ( (int) $post_count > 0 ) {
			// Check if there's a strategy for post author reassignment.
			$has_post_eraser = false;
			foreach ( $erasers as $eraser ) {
				if ( isset( $eraser['eraser_friendly_name'] ) &&
				     ( false !== strpos( strtolower( $eraser['eraser_friendly_name'] ), 'post' ) ||
				       false !== strpos( strtolower( $eraser['eraser_friendly_name'] ), 'content' ) ) ) {
					$has_post_eraser = true;
					break;
				}
			}

			if ( ! $has_post_eraser ) {
				$issues[] = __( 'No post author reassignment strategy - posts may remain attributed to deleted users', 'wpshadow' );
			}
		}

		// 5. Check for content preservation settings.
		$remove_policy = get_option( 'wp_remove_post_personal_data', false );
		
		if ( false === $remove_policy ) {
			// Not configured - using defaults.
			$issues[] = __( 'Content removal policy not explicitly configured - behavior unclear', 'wpshadow' );
		}

		// 6. Test anonymization for different data types.
		$test_cases = array(
			'text'   => 'John Doe',
			'email'  => 'john@example.com',
			'url'    => 'https://example.com',
			'ip'     => '192.168.1.1',
			'date'   => '2024-01-15',
		);

		$failed_anonymization = array();
		foreach ( $test_cases as $type => $value ) {
			$anonymized = wp_privacy_anonymize_data( $type, $value );
			
			// Check if anonymization actually changed the value.
			if ( $anonymized === $value ) {
				$failed_anonymization[] = $type;
			}
		}

		if ( ! empty( $failed_anonymization ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of data types */
				__( 'Anonymization failing for types: %s', 'wpshadow' ),
				implode( ', ', $failed_anonymization )
			);
		}

		// 7. Check for custom fields with personal data.
		$meta_keys_with_pii = array(
			'billing_first_name',
			'billing_last_name',
			'billing_email',
			'billing_phone',
			'billing_address',
			'shipping_first_name',
			'shipping_last_name',
			'shipping_address',
		);

		$custom_field_count = 0;
		foreach ( $meta_keys_with_pii as $meta_key ) {
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
					$meta_key
				)
			);
			$custom_field_count += (int) $count;
		}

		if ( $custom_field_count > 0 ) {
			// Check if there's a custom field eraser.
			$has_meta_eraser = false;
			foreach ( $erasers as $eraser ) {
				if ( isset( $eraser['eraser_friendly_name'] ) &&
				     ( false !== strpos( strtolower( $eraser['eraser_friendly_name'] ), 'meta' ) ||
				       false !== strpos( strtolower( $eraser['eraser_friendly_name'] ), 'field' ) ) ) {
					$has_meta_eraser = true;
					break;
				}
			}

			if ( ! $has_meta_eraser ) {
				$issues[] = sprintf(
					/* translators: %d: number of fields */
					_n(
						'%d custom field with personal data found - no eraser registered',
						'%d custom fields with personal data found - no eraser registered',
						$custom_field_count,
						'wpshadow'
					),
					$custom_field_count
				);
			}
		}

		// 8. Check revision handling.
		$revision_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'revision'
			)
		);

		if ( (int) $revision_count > 0 ) {
			// Revisions can contain personal data.
			$issues[] = __( 'Post revisions may contain personal data - verify erasure coverage', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Content anonymization issues: %s', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'critical',
			'threat_level' => 85,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/content-anonymization',
			'details'      => array(
				'issues'         => $issues,
				'comment_count'  => $comment_count,
				'post_count'     => $post_count,
				'eraser_count'   => count( $erasers ),
			),
		);
	}
}
