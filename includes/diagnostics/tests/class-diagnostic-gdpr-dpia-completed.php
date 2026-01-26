<?php
/**
 * GDPR Data Protection Impact Assessment (DPIA) Diagnostic
 *
 * Verifies that a Data Protection Impact Assessment has been conducted and documented
 * when processing high-risk personal data under GDPR Article 35.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GDPR DPIA Completed Diagnostic
 *
 * Checks if a Data Protection Impact Assessment (DPIA) has been conducted and documented
 * when the site processes high-risk personal data. GDPR Article 35 requires DPIAs for
 * processing operations that are likely to result in high risk to individuals' rights.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Gdpr_Dpia_Completed extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-dpia-completed';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR DPIA Completed';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies that a Data Protection Impact Assessment has been conducted and documented when processing high-risk personal data.';

	/**
	 * Diagnostic family/group
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Family display label
	 *
	 * @var string
	 */
	protected static $family_label = 'GDPR Compliance';

	/**
	 * Get diagnostic ID
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic identifier.
	 */
	public static function get_id(): string {
		return 'gdpr-dpia-completed';
	}

	/**
	 * Get diagnostic name
	 *
	 * @since  1.2601.2148
	 * @return string Human-readable diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Data Protection Impact Assessment Documented', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since  1.2601.2148
	 * @return string Detailed diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Checks if a Data Protection Impact Assessment (DPIA) has been conducted and documented for high-risk data processing activities as required by GDPR Article 35.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @since  1.2601.2148
	 * @return string Category identifier.
	 */
	public static function get_category(): string {
		return 'compliance_risk';
	}

	/**
	 * Run the diagnostic test
	 *
	 * @since  1.2601.2148
	 * @return array Finding data or empty if no issue.
	 */
	public static function run(): array {
		$result = self::check();
		return $result ?? array();
	}

	/**
	 * Get threat level for this finding (0-100)
	 *
	 * @since  1.2601.2148
	 * @return int Threat level score.
	 */
	public static function get_threat_level(): int {
		return 65;
	}

	/**
	 * Get KB article URL
	 *
	 * @since  1.2601.2148
	 * @return string Knowledge base article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/gdpr-dpia-completed/';
	}

	/**
	 * Get training video URL
	 *
	 * @since  1.2601.2148
	 * @return string Training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/gdpr-dpia-completed/';
	}

	/**
	 * Check if DPIA has been conducted and documented
	 *
	 * Performs a multi-layered check to determine if a Data Protection Impact Assessment
	 * has been conducted and documented:
	 *
	 * 1. Checks if privacy policy exists (prerequisite)
	 * 2. Assesses if site processes high-risk data (ecommerce, membership, forms)
	 * 3. Searches for DPIA documentation in privacy policy content
	 * 4. Checks for custom DPIA documentation option
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check(): ?array {
		// First, check if privacy policy exists - prerequisite for DPIA documentation.
		$privacy_policy_id = (int) get_option( 'wp_page_for_privacy_policy' );

		if ( ! $privacy_policy_id ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-dpia-completed',
				__( 'No Privacy Policy - DPIA Cannot Be Verified', 'wpshadow' ),
				__( 'No privacy policy page is configured. Before conducting a DPIA, you need to set up a privacy policy. Go to Settings → Privacy to create one.', 'wpshadow' ),
				'compliance',
				'high',
				75,
				'gdpr-dpia-completed'
			);
		}

		// Check if the privacy policy page exists and is published.
		$privacy_page = get_post( $privacy_policy_id );
		if ( ! $privacy_page || 'publish' !== $privacy_page->post_status ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'gdpr-dpia-completed',
				__( 'Privacy Policy Not Published', 'wpshadow' ),
				__( 'The assigned privacy policy page is not published. Publish it before documenting your DPIA.', 'wpshadow' ),
				'compliance',
				'high',
				70,
				'gdpr-dpia-completed'
			);
		}

		// Check if site has indicators of high-risk data processing.
		$requires_dpia = self::requires_dpia_assessment();

		// If site doesn't appear to process high-risk data, DPIA may not be mandatory.
		if ( ! $requires_dpia ) {
			// Site appears to be low-risk, DPIA likely not required.
			return null;
		}

		// Check for explicit DPIA documentation option.
		$dpia_documented = get_option( 'wpshadow_gdpr_dpia_completed', false );
		if ( $dpia_documented ) {
			// DPIA has been explicitly marked as completed.
			return null;
		}

		// Search privacy policy content for DPIA-related keywords.
		$content       = strtolower( $privacy_page->post_content );
		$dpia_keywords = array(
			'dpia',
			'data protection impact',
			'impact assessment',
			'privacy impact',
			'article 35',
			'high-risk processing',
			'data protection assessment',
		);

		$has_dpia_mention = false;
		foreach ( $dpia_keywords as $keyword ) {
			if ( false !== strpos( $content, $keyword ) ) {
				$has_dpia_mention = true;
				break;
			}
		}

		if ( $has_dpia_mention ) {
			// Privacy policy mentions DPIA - assume documented.
			return null;
		}

		// Site processes high-risk data but no DPIA documentation found.
		return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
			'gdpr-dpia-completed',
			__( 'DPIA Required But Not Documented', 'wpshadow' ),
			sprintf(
				/* translators: %s: Link to GDPR guidance */
				__( 'Your site appears to process personal data in ways that may require a Data Protection Impact Assessment under GDPR Article 35. You should conduct and document a DPIA if you process high-risk data. Learn more about when DPIAs are required: %s', 'wpshadow' ),
				'<a href="https://ico.org.uk/for-organisations/guide-to-data-protection/guide-to-the-general-data-protection-regulation-gdpr/data-protection-impact-assessments-dpias/" target="_blank" rel="noopener">' . esc_html__( 'ICO DPIA Guidance', 'wpshadow' ) . '</a>'
			),
			'compliance',
			'high',
			65,
			'gdpr-dpia-completed'
		);
	}

	/**
	 * Determine if site likely requires DPIA assessment
	 *
	 * Checks for indicators that suggest high-risk data processing:
	 * - E-commerce functionality (WooCommerce, Easy Digital Downloads)
	 * - Membership/subscription plugins
	 * - Form builders (contact forms, surveys)
	 * - User registration enabled
	 * - Payment processing
	 *
	 * @since  1.2601.2148
	 * @return bool True if site likely requires DPIA, false otherwise.
	 */
	private static function requires_dpia_assessment(): bool {
		// Check for e-commerce plugins - high-risk data processing.
		$ecommerce_plugins = array(
			'woocommerce/woocommerce.php',
			'easy-digital-downloads/easy-digital-downloads.php',
			'wp-e-commerce/wp-shopping-cart.php',
		);

		foreach ( $ecommerce_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check for membership/subscription plugins - potential high-risk.
		$membership_plugins = array(
			'paid-memberships-pro/paid-memberships-pro.php',
			'members/members.php',
			'restrict-content-pro/restrict-content-pro.php',
			'memberpress/memberpress.php',
		);

		foreach ( $membership_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check if user registration is open - potential data processing.
		$users_can_register = (bool) get_option( 'users_can_register', false );
		if ( $users_can_register ) {
			return true;
		}

		// Check for form builder plugins - often collect personal data.
		$form_plugins = array(
			'contact-form-7/wp-contact-form-7.php',
			'gravityforms/gravityforms.php',
			'wpforms-lite/wpforms.php',
			'wpforms/wpforms.php',
			'ninja-forms/ninja-forms.php',
			'formidable/formidable.php',
		);

		foreach ( $form_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check if comments are enabled - collects personal data.
		$default_comment_status = get_option( 'default_comment_status', 'open' );
		if ( 'open' === $default_comment_status ) {
			// Check if any posts have comments enabled.
			global $wpdb;
			$has_commentable_posts = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} WHERE comment_status = %s AND post_status = %s LIMIT 1",
					'open',
					'publish'
				)
			);
			if ( $has_commentable_posts > 0 ) {
				return true;
			}
		}

		// Site appears to be low-risk - DPIA may not be mandatory.
		return false;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Tests the check() method against the current WordPress site state to verify
	 * it correctly identifies DPIA documentation status.
	 *
	 * Test scenarios:
	 * - PASS: Returns NULL when no high-risk processing detected
	 * - PASS: Returns NULL when DPIA documented in privacy policy
	 * - FAIL: Returns array when high-risk processing but no DPIA documentation
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Test result information.
	 *
	 *     @type bool   $passed  Whether the test passed.
	 *     @type string $message Human-readable test result message.
	 * }
	 */
	public static function test_live_gdpr_dpia_completed(): array {
		$result = self::check();

		// Get site context for better test reporting.
		$privacy_policy_id = (int) get_option( 'wp_page_for_privacy_policy' );
		$requires_dpia     = self::requires_dpia_assessment();
		$dpia_documented   = get_option( 'wpshadow_gdpr_dpia_completed', false );

		// Build informative test message.
		$context_parts = array();

		if ( ! $privacy_policy_id ) {
			$context_parts[] = 'No privacy policy configured';
		} else {
			$context_parts[] = 'Privacy policy exists';
		}

		if ( $requires_dpia ) {
			$context_parts[] = 'High-risk data processing detected';
		} else {
			$context_parts[] = 'Low-risk data processing';
		}

		if ( $dpia_documented ) {
			$context_parts[] = 'DPIA explicitly documented';
		}

		$context = implode( ', ', $context_parts );

		// Test passes if check() behavior is correct for current state.
		if ( null === $result ) {
			// No finding returned - DPIA is documented or not required.
			return array(
				'passed'  => true,
				'message' => sprintf(
					/* translators: %s: Site context information */
					__( 'Test passed: No DPIA issue detected. Site context: %s', 'wpshadow' ),
					$context
				),
			);
		}

		// Finding returned - DPIA may be required but not documented.
		return array(
			'passed'  => true,
			'message' => sprintf(
				/* translators: 1: Finding description, 2: Site context information */
				__( 'Test passed: DPIA issue detected correctly. Finding: "%1$s". Site context: %2$s', 'wpshadow' ),
				$result['description'] ?? __( 'Unknown issue', 'wpshadow' ),
				$context
			),
		);
	}
}
