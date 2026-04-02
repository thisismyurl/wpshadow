<?php
/**
 * GDPR Erasure Compliance Gaps Diagnostic
 *
 * Comprehensive test for legal compliance of erasure implementation vs GDPR requirements.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Privacy
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_GDPR_Erasure_Compliance_Gaps Class
 *
 * Verifies erasure implementation meets GDPR Article 17 requirements.
 *
 * @since 1.6093.1200
 */
class Diagnostic_GDPR_Erasure_Compliance_Gaps extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-erasure-compliance-gaps';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Article 17 Compliance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies that data erasure meets GDPR Right to be Forgotten requirements';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check privacy policy exists (GDPR Art. 13-14).
		$privacy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );
		
		if ( empty( $privacy_page_id ) ) {
			$issues[] = __( 'No privacy policy page configured - required for GDPR compliance', 'wpshadow' );
		} else {
			$privacy_page = get_post( $privacy_page_id );
			if ( ! $privacy_page || 'publish' !== $privacy_page->post_status ) {
				$issues[] = __( 'Privacy policy page is not published', 'wpshadow' );
			}
		}

		// 2. Verify data retention policy is documented.
		if ( $privacy_page_id ) {
			$privacy_content = get_post_field( 'post_content', $privacy_page_id );
			
			// Check for key GDPR concepts.
			$required_terms = array(
				'retention'    => false,
				'data subject' => false,
				'right'        => false,
				'delete'       => false,
			);

			foreach ( $required_terms as $term => $found ) {
				if ( false !== stripos( $privacy_content, $term ) ) {
					$required_terms[ $term ] = true;
				}
			}

			$missing_terms = array_keys( array_filter( $required_terms, function( $found ) {
				return false === $found;
			} ) );

			if ( ! empty( $missing_terms ) ) {
				$issues[] = sprintf(
					/* translators: %s: comma-separated list of terms */
					__( 'Privacy policy missing GDPR concepts: %s', 'wpshadow' ),
					implode( ', ', $missing_terms )
				);
			}
		}

		// 3. Check for legal basis documentation (Art. 6).
		$legal_basis_option = get_option( 'wp_gdpr_legal_basis', false );
		
		if ( false === $legal_basis_option ) {
			$issues[] = __( 'Legal basis for data processing not documented', 'wpshadow' );
		}

		// 4. Verify erasure exceptions are handled (Art. 17(3)).
		$erasers = apply_filters( 'wp_privacy_personal_data_erasers', array() );
		
		// Check if there's handling for legal obligations.
		$has_exception_handling = false;
		foreach ( $erasers as $eraser ) {
			// WordPress doesn't have built-in exception handling.
			// This needs custom implementation.
		}

		if ( ! $has_exception_handling ) {
			$issues[] = __( 'No erasure exception handling for legal obligations (e.g., tax records)', 'wpshadow' );
		}

		// 5. Check data controller identification (Art. 4(7)).
		$admin_email = get_option( 'admin_email' );
		$site_title  = get_option( 'blogname' );
		
		if ( empty( $admin_email ) || empty( $site_title ) ) {
			$issues[] = __( 'Data controller identification incomplete', 'wpshadow' );
		}

		// 6. Verify one-month response time tracking (Art. 12(3)).
		global $wpdb;
		$request_table = $wpdb->prefix . 'posts';
		
		$old_pending = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$request_table} 
				WHERE post_type = %s 
				AND post_status = %s
				AND post_date < DATE_SUB(NOW(), INTERVAL 1 MONTH)",
				'user_request',
				'request-pending'
			)
		);

		if ( (int) $old_pending > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of overdue requests */
				_n(
					'%d erasure request exceeds GDPR 1-month deadline',
					'%d erasure requests exceed GDPR 1-month deadline',
					$old_pending,
					'wpshadow'
				),
				$old_pending
			);
		}

		// 7. Check for data processor agreements (Art. 28).
		$active_plugins = get_option( 'active_plugins', array() );
		
		if ( ! empty( $active_plugins ) ) {
			// Plugins that process data act as processors.
			$data_processing_plugins = array(
				'woocommerce',
				'mailchimp',
				'google-analytics',
				'contact-form',
				'jetpack',
			);

			$processors_found = 0;
			foreach ( $active_plugins as $plugin ) {
				foreach ( $data_processing_plugins as $processor ) {
					if ( false !== strpos( $plugin, $processor ) ) {
						$processors_found++;
						break;
					}
				}
			}

			if ( $processors_found > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of processors */
					_n(
						'%d data processor detected - verify processor agreements exist',
						'%d data processors detected - verify processor agreements exist',
						$processors_found,
						'wpshadow'
					),
					$processors_found
				);
			}
		}

		// 8. Verify right to be forgotten is documented.
		$erasure_request_page = get_option( 'wp_page_for_erasure_request', false );
		
		if ( false === $erasure_request_page ) {
			$issues[] = __( 'No dedicated erasure request page - users may not know how to exercise rights', 'wpshadow' );
		}

		// 9. Check for cross-border data transfer documentation (Art. 44-50).
		$site_lang = get_locale();
		$is_eu     = in_array( substr( $site_lang, -2 ), array( 'DE', 'FR', 'IT', 'ES', 'NL', 'BE', 'AT', 'PL', 'RO', 'SE' ), true );
		
		if ( $is_eu ) {
			// EU site - check for international service usage.
			$intl_services = array(
				'jetpack',
				'akismet',
				'google-analytics',
				'mailchimp',
			);

			$uses_intl = false;
			foreach ( $active_plugins as $plugin ) {
				foreach ( $intl_services as $service ) {
					if ( false !== strpos( $plugin, $service ) ) {
						$uses_intl = true;
						break 2;
					}
				}
			}

			if ( $uses_intl ) {
				$issues[] = __( 'International data transfer detected - verify Standard Contractual Clauses', 'wpshadow' );
			}
		}

		// 10. Check for DPO (Data Protection Officer) if required (Art. 37).
		$user_count = count_users();
		$total_users = $user_count['total_users'];
		
		if ( $total_users > 250 ) {
			// Large-scale processing may require DPO.
			$dpo_email = get_option( 'wp_gdpr_dpo_email', false );
			
			if ( false === $dpo_email ) {
				$issues[] = __( 'Site has 250+ users - consider designating a Data Protection Officer', 'wpshadow' );
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'GDPR compliance gaps: %s', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'critical',
			'threat_level' => 95,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/gdpr-article-17-compliance',
			'details'      => array(
				'issues'       => $issues,
				'privacy_page' => $privacy_page_id,
				'user_count'   => $total_users,
			),
		);
	}
}
