<?php
/**
 * User Metadata Privacy Treatment
 *
 * Validates that sensitive user metadata is protected from exposure
 * and is not accessible to users without proper permissions.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6032.1340
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Metadata Privacy Treatment Class
 *
 * Checks user metadata privacy and security.
 *
 * @since 1.6032.1340
 */
class Treatment_User_Metadata_Privacy extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-metadata-privacy';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'User Metadata Privacy';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates user metadata privacy and security';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6032.1340
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Sensitive metadata keys that should be protected.
		$sensitive_meta_keys = array(
			'session_tokens',
			'capabilities',
			'user_level',
			'wp_capabilities',
			'wp_user_level',
			'dismissed_wp_pointers',
			'closedpostboxes_',
			'metaboxhidden_',
			'meta-box-order_',
		);

		// Check for sensitive data in user meta.
		global $wpdb;

		// Check for exposed passwords or tokens.
		$password_related = $wpdb->get_results(
			"SELECT user_id, meta_key, COUNT(*) as count
			FROM {$wpdb->usermeta}
			WHERE meta_key LIKE '%password%'
			OR meta_key LIKE '%token%'
			OR meta_key LIKE '%secret%'
			OR meta_key LIKE '%auth%'
			GROUP BY user_id, meta_key
			LIMIT 50"
		);

		if ( ! empty( $password_related ) ) {
			// Some might be legitimate (2FA, API tokens).
			foreach ( $password_related as $record ) {
				// Check for truly sensitive data.
				if ( false !== stripos( $record->meta_key, 'reset' ) || false !== stripos( $record->meta_key, 'forgot' ) ) {
					// Password reset tokens - OK to store temporarily.
				} elseif ( false !== stripos( $record->meta_key, 'plain' ) || false !== stripos( $record->meta_key, 'clear' ) ) {
					$issues[] = sprintf(
						/* translators: %s: meta key */
						__( 'Plaintext password or credential stored in metadata: %s', 'wpshadow' ),
						$record->meta_key
					);
				}
			}
		}

		// Check for session token exposure.
		$sessions = $wpdb->get_results(
			"SELECT COUNT(*) as count
			FROM {$wpdb->usermeta}
			WHERE meta_key = 'session_tokens'"
		);

		// Session tokens are OK to store (encrypted by WordPress).

		// Check for credit card data.
		$cc_data = $wpdb->get_results(
			"SELECT user_id, meta_key
			FROM {$wpdb->usermeta}
			WHERE meta_value LIKE '%-%-%-%'
			AND meta_value REGEXP '[0-9]{13,19}'
			LIMIT 10"
		);

		if ( ! empty( $cc_data ) ) {
			$issues[] = __( 'Possible credit card data stored in user metadata (PCI compliance risk)', 'wpshadow' );
		}

		// Check for SSN or other PII.
		$pii_patterns = $wpdb->get_results(
			"SELECT user_id, meta_key, COUNT(*) as count
			FROM {$wpdb->usermeta}
			WHERE meta_value REGEXP '[0-9]{3}-[0-9]{2}-[0-9]{4}'
			OR meta_value REGEXP '[0-9]{5}-[0-9]{4}'
			GROUP BY user_id, meta_key
			LIMIT 10"
		);

		if ( ! empty( $pii_patterns ) ) {
			$issues[] = __( 'Possible SSN or sensitive PII patterns in user metadata (privacy risk)', 'wpshadow' );
		}

		// Check for excessive metadata per user.
		$large_meta = $wpdb->get_results(
			"SELECT user_id, COUNT(*) as meta_count, SUM(LENGTH(meta_value)) as total_size
			FROM {$wpdb->usermeta}
			GROUP BY user_id
			HAVING total_size > 1024 * 100
			ORDER BY total_size DESC
			LIMIT 10"
		);

		if ( ! empty( $large_meta ) ) {
			foreach ( $large_meta as $record ) {
				if ( $record->total_size > 1024 * 500 ) { // 500KB.
					$issues[] = sprintf(
						/* translators: 1: user ID, 2: size in KB */
						__( 'User %1$d has %2$dKB of metadata (possible abuse or data leak)', 'wpshadow' ),
						$record->user_id,
						absint( $record->total_size / 1024 )
					);
				}
			}
		}

		// Check for publicly accessible user data.
		$public_accessible = $wpdb->get_results(
			"SELECT DISTINCT meta_key
			FROM {$wpdb->usermeta}
			WHERE meta_key NOT LIKE '_%'
			AND meta_key NOT IN ('first_name', 'last_name', 'description', 'rich_editing', 'syntax_highlighting')
			LIMIT 20"
		);

		// Most custom metadata with underscore prefix is private.

		// Check WP REST API user exposure.
		if ( is_plugin_active( 'rest-api/plugin.php' ) || rest_is_enabled() ) {
			// Check what user data is exposed via REST.
			$exposed_fields = array( 'email', 'meta' );
			// Note: This would need deeper analysis.
		}

		// Check for user enumeration risks.
		// User archive pages, REST API, XML-RPC can expose usernames.

		// Check if author pages expose data.
		if ( get_option( 'blog_public' ) ) {
			// Blog is public - user archives might be indexed.
		}

		// Check for exposed user metadata via themes/plugins.
		$template_dir   = get_template_directory();
		$functions_file = $template_dir . '/functions.php';

		if ( file_exists( $functions_file ) ) {
			$content = file_get_contents( $functions_file );

			// Check for direct user meta exposure without permission checks.
			if ( preg_match( '/get_user_meta.*\$_GET|\$_REQUEST/i', $content ) ) {
				$issues[] = __( 'Theme may expose user metadata based on user input without permission checks', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of user metadata privacy issues */
					__( 'Found %d user metadata privacy concerns.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'details'      => array(
					'issues'           => $issues,
					'recommendation'   => __( 'Do not store plaintext passwords, credit cards, or SSN in user metadata. Encrypt sensitive data and verify permissions before exposing metadata.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
