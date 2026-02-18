<?php
/**
 * Incomplete Personal Data Deletion Diagnostic
 *
 * Tests whether Personal Data Erasure removes all user data from WordPress core tables.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Privacy
 * @since      1.2034.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Incomplete_Personal_Data_Deletion Class
 *
 * Verifies that personal data erasure is comprehensive.
 *
 * @since 1.2034.1445
 */
class Diagnostic_Incomplete_Personal_Data_Deletion extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'incomplete-personal-data-deletion';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Personal Data Deletion Completeness';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies that GDPR data erasure removes all required user information';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2034.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check if core erasure functionality exists.
		if ( ! function_exists( 'wp_privacy_anonymize_data' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WordPress personal data erasure functionality is not available', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/gdpr-personal-data-deletion',
			);
		}

		// 2. Check if data erasers are registered.
		$erasers = apply_filters( 'wp_privacy_personal_data_erasers', array() );
		
		if ( empty( $erasers ) ) {
			$issues[] = __( 'No personal data erasers are registered', 'wpshadow' );
		} else {
			// 3. Verify core erasers exist.
			$core_erasers = array(
				'wordpress-user',
				'wordpress-comments',
			);

			$missing_core = array();
			foreach ( $core_erasers as $eraser_id ) {
				$found = false;
				foreach ( $erasers as $eraser ) {
					if ( isset( $eraser['eraser_friendly_name'] ) && 
					     false !== strpos( strtolower( $eraser['eraser_friendly_name'] ), str_replace( 'wordpress-', '', $eraser_id ) ) ) {
						$found = true;
						break;
					}
				}
				if ( ! $found ) {
					$missing_core[] = $eraser_id;
				}
			}

			if ( ! empty( $missing_core ) ) {
				$issues[] = sprintf(
					/* translators: %s: comma-separated list of missing erasers */
					__( 'Missing core data erasers: %s', 'wpshadow' ),
					implode( ', ', $missing_core )
				);
			}
		}

		// 4. Check for empty eraser callbacks.
		foreach ( $erasers as $eraser_id => $eraser ) {
			if ( ! isset( $eraser['callback'] ) || ! is_callable( $eraser['callback'] ) ) {
				$issues[] = sprintf(
					/* translators: %s: eraser ID */
					__( 'Eraser "%s" has invalid or missing callback', 'wpshadow' ),
					esc_html( $eraser_id )
				);
			}
		}

		// 5. Check if user meta erasure is configured.
		$test_user_id = get_current_user_id();
		if ( $test_user_id ) {
			$user_meta = get_user_meta( $test_user_id );
			if ( ! empty( $user_meta ) ) {
				// Check if any eraser handles user meta.
				$has_meta_eraser = false;
				foreach ( $erasers as $eraser ) {
					if ( isset( $eraser['callback'] ) && is_callable( $eraser['callback'] ) ) {
						// Core WordPress user eraser handles meta.
						if ( isset( $eraser['eraser_friendly_name'] ) && 
						     false !== strpos( strtolower( $eraser['eraser_friendly_name'] ), 'user' ) ) {
							$has_meta_eraser = true;
							break;
						}
					}
				}

				if ( ! $has_meta_eraser ) {
					$issues[] = __( 'User meta data may not be deleted during erasure', 'wpshadow' );
				}
			}
		}

		// 6. Check comment anonymization configuration.
		global $wpdb;
		$comment_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments}" );
		
		if ( (int) $comment_count > 0 ) {
			// Verify comment eraser exists.
			$has_comment_eraser = false;
			foreach ( $erasers as $eraser ) {
				if ( isset( $eraser['eraser_friendly_name'] ) &&
				     false !== strpos( strtolower( $eraser['eraser_friendly_name'] ), 'comment' ) ) {
					$has_comment_eraser = true;
					break;
				}
			}

			if ( ! $has_comment_eraser ) {
				$issues[] = __( 'Comments may not be anonymized during erasure', 'wpshadow' );
			}
		}

		// 7. Check for post author reassignment.
		$post_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_author = %d",
				$test_user_id
			)
		);

		if ( (int) $post_count > 0 ) {
			// WordPress doesn't automatically reassign posts during erasure.
			$issues[] = __( 'Post author reassignment is not configured - posts may remain attributed to deleted users', 'wpshadow' );
		}

		// 8. Check if erasure requests are being tracked.
		$erasure_request_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status IN (%s, %s)",
				'user_request',
				'request-completed',
				'request-confirmed'
			)
		);

		if ( (int) $erasure_request_count > 0 ) {
			// Check if there's an audit trail.
			$action_log_plugins = array(
				'simple-history/index.php',
				'wp-security-audit-log/wp-security-audit-log.php',
				'stream/stream.php',
			);

			$has_audit = false;
			foreach ( $action_log_plugins as $plugin ) {
				if ( is_plugin_active( $plugin ) ) {
					$has_audit = true;
					break;
				}
			}

			if ( ! $has_audit ) {
				$issues[] = __( 'No audit logging for erasure requests - cannot prove GDPR compliance', 'wpshadow' );
			}
		}

		// 9. Check for orphaned user meta after deletions.
		$orphaned_meta = $wpdb->get_var(
			"SELECT COUNT(DISTINCT user_id) 
			FROM {$wpdb->usermeta} um
			LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID
			WHERE u.ID IS NULL"
		);

		if ( (int) $orphaned_meta > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned records */
				_n(
					'%d orphaned user meta record found - incomplete deletion',
					'%d orphaned user meta records found - incomplete deletion',
					$orphaned_meta,
					'wpshadow'
				),
				$orphaned_meta
			);
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Personal data deletion may be incomplete: %s', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'critical',
			'threat_level' => 95,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/gdpr-personal-data-deletion',
			'details'      => array(
				'issues'          => $issues,
				'registered_count' => count( $erasers ),
				'orphaned_meta'   => $orphaned_meta,
			),
		);
	}
}
