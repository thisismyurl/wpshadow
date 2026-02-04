<?php
/**
 * User Profile Data and Information Disclosure
 *
 * Validates user profile data and information disclosure risks.
 *
 * @since   1.2034.1615
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_User_Profile_Data Class
 *
 * Checks user profile data and information disclosure.
 *
 * @since 1.2034.1615
 */
class Diagnostic_User_Profile_Data extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-profile-data';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Profile Data';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates user profile data and information disclosure risks';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-management';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2034.1615
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Pattern 1: User email addresses exposed in public profile
		$users_with_public_email = $wpdb->get_results(
			"SELECT ID, user_login, user_email FROM {$wpdb->users} LIMIT 10"
		);

		// Check if email is exposed in profile URLs
		$first_user = $users_with_public_email[0] ?? null;

		if ( $first_user ) {
			$profile_url = get_author_posts_url( $first_user->ID );
			$response = wp_remote_get( $profile_url, array( 'sslverify' => false ) );

			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
				$body = wp_remote_retrieve_body( $response );

				if ( strpos( $body, $first_user->user_email ) !== false ) {
					return array(
						'id'           => self::$slug,
						'title'        => self::$title,
						'description'  => __( 'User email addresses exposed in public profiles', 'wpshadow' ),
						'severity'     => 'medium',
						'threat_level' => 55,
						'auto_fixable' => false,
						'kb_link'      => 'https://wpshadow.com/kb/user-profile-data',
						'details'      => array(
							'issue' => 'email_exposed_in_profile',
							'message' => __( 'User email addresses are publicly visible', 'wpshadow' ),
							'privacy_concern' => __( 'Email addresses used for phishing, spam, doxxing', 'wpshadow' ),
							'where_exposed' => array(
								'Author profile pages' => '/author/username/',
								'Author archives' => 'Email in HTML',
								'RSS feeds' => 'Author email in feed',
								'Comments' => 'Commenter email visible',
								'REST API' => '/wp-json/wp/v2/users endpoint',
							),
							'uses' => array(
								'Spam campaigns',
								'Phishing emails',
								'Targeted attacks',
								'Privacy violations',
								'GDPR violations',
							),
							'removing_email' => "// Hide email from profile
add_filter('the_author_meta', function(\$value, \$user_id, \$field) {
	if (\$field === 'user_email' && !is_user_logged_in()) {
		return ''; // Hide for public
	}
	return \$value;
}, 10, 3);

// Disable author archives to hide profile entirely
add_action('template_redirect', function() {
	if (is_author()) {
		wp_redirect(home_url(), 301);
		exit;
	}
});",
							'hiding_from_rest' => "add_filter('rest_prepare_user', function(\$response, \$user) {
	// Don't expose email to public
	if (!current_user_can('list_users')) {
		\$response->data = array(
			'id' => \$user->ID,
			'name' => \$user->display_name,
		);
	}
	return \$response;
}, 10, 2);",
							'user_display_name' => __( 'Use display_name instead of email', 'wpshadow' ),
							'privacy_settings' => array(
								'Go to Settings > Privacy',
								'Configure author visibility',
								'Hide emails from public',
							),
							'gdpr_compliance' => __( 'Email exposure may violate GDPR privacy rights', 'wpshadow' ),
							'recommendation' => __( 'Remove email addresses from public-facing areas', 'wpshadow' ),
						),
					);
				}
			}
		}

		// Pattern 2: User phone numbers or personal details in profiles
		$user_meta_with_sensitive = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id, meta_key, meta_value 
				FROM {$wpdb->usermeta} 
				WHERE meta_key IN (%s, %s, %s, %s)
				AND meta_value NOT LIKE ''
				LIMIT 10",
				'phone',
				'user_phone',
				'cellular',
				'mobile'
			)
		);

		if ( ! empty( $user_meta_with_sensitive ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Sensitive personal data stored in user profiles', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-profile-data',
				'details'      => array(
					'issue' => 'sensitive_data_in_profile',
					'count' => count( $user_meta_with_sensitive ),
					'sample' => array_slice( $user_meta_with_sensitive, 0, 10 ),
					'message' => sprintf(
						/* translators: %d: count */
						__( '%d user profiles contain sensitive personal data', 'wpshadow' ),
						count( $user_meta_with_sensitive )
					),
					'sensitive_fields' => array(
						'Phone numbers' => 'PII, privacy risk',
						'Social Security' => 'Critical PII',
						'Date of birth' => 'Identity theft risk',
						'Government IDs' => 'Sensitive info',
						'Bank accounts' => 'Financial data',
					),
					'risks' => array(
						'Privacy violation',
						'Identity theft',
						'Social engineering attacks',
						'GDPR violations',
						'Data breach exposure',
					),
						'why_concerning' => __( 'WordPress database backups could be accessed by attackers', 'wpshadow' ),
					'secure_storage' => array(
						'Encrypt sensitive data',
						'Store in separate database',
						'Use external service',
						'Tokenization',
					),
					'removing_sensitive' => "// Remove sensitive meta
delete_user_meta(123, 'phone');
delete_user_meta(123, 'user_phone');

// Encrypt if needed
\$encrypted = encrypt_data(\$phone);
update_user_meta(123, 'encrypted_phone', \$encrypted);",
					'encryption_example' => "// Encrypt before storing
function encrypt_sensitive_data(\$data) {
	\$key = wp_salt('auth');
	return base64_encode(openssl_encrypt(\$data, 'AES-256-CBC', \$key, true));
}

// Use filtered meta
add_filter('get_user_metadata', function(\$value, \$object_id, \$meta_key) {
	if (\$meta_key === 'phone' && !current_user_can('manage_users')) {
		return ''; // Hide unless admin
	}
	return \$value;
}, 10, 3);",
					'where_not_to_store' => array(
						'WordPress database - accessible',
						'User profiles - could be public',
						'Comments - publicly visible',
						'User descriptions - visible',
					),
					'audit_data' => array(
						'1. Find all user meta fields',
						'2. Identify sensitive fields',
						'3. Evaluate necessity',
						'4. Remove or encrypt',
						'5. Document retention policy',
					),
					'gdpr_right_to_be_forgotten' => __( 'GDPR requires ability to delete personal data', 'wpshadow' ),
					'recommendation' => __( 'Remove or encrypt sensitive personal data from user profiles', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: User profile information easily accessible
		return null;
	}
}
