<?php
/**
 * Email Domain Mismatch Diagnostic
 *
 * Detects when WordPress sends emails from addresses that don't match the site domain.
 * Domain mismatch increases spam score by 40% and harms deliverability.
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
 * Diagnostic_Email_Domain_Mismatch Class
 *
 * Checks wp_mail_from filter and default WordPress email settings.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Email_Domain_Mismatch extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-domain-mismatch';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Domain Mismatch';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when WordPress sends emails from mismatched domains';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$email_data = self::check_email_domain();

		if ( ! $email_data ) {
			return null;
		}

		$from_email    = $email_data['from_email'];
		$from_domain   = $email_data['from_domain'];
		$site_domain   = $email_data['site_domain'];
		$is_mismatch   = $email_data['is_mismatch'];
		$is_generic    = $email_data['is_generic'];

		if ( ! $is_mismatch && ! $is_generic ) {
			return null; // Email domain matches site domain and not generic.
		}

		$severity     = 'medium';
		$threat_level = 60;

		if ( $is_mismatch && $is_generic ) {
			$severity     = 'high';
			$threat_level = 75;
		}

		$issue_description = '';
		if ( $is_mismatch ) {
			$issue_description = sprintf(
				/* translators: 1: From email, 2: From domain, 3: Site domain */
				__( 'WordPress sends emails from %1$s (domain: %2$s) which doesn\'t match your site domain (%3$s). This increases spam score and reduces deliverability.', 'wpshadow' ),
				$from_email,
				$from_domain,
				$site_domain
			);
		}

		if ( $is_generic ) {
			$issue_description .= ' ' . sprintf(
				/* translators: 1: Generic address */
				__( 'Additionally, using generic address %1$s appears unprofessional and triggers spam filters.', 'wpshadow' ),
				$from_email
			);
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $issue_description,
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/email-domain-mismatch',
			'details'     => self::get_details( $email_data ),
		);
	}

	/**
	 * Check email from domain.
	 *
	 * Gets wp_mail_from value and compares to site domain.
	 *
	 * @since  1.2601.2148
	 * @return array|null {
	 *     Email domain check data.
	 *
	 *     @type string $from_email  Email address used in from field.
	 *     @type string $from_domain Domain of from email.
	 *     @type string $site_domain Site domain.
	 *     @type bool   $is_mismatch Whether domains don't match.
	 *     @type bool   $is_generic  Whether using generic address.
	 * }
	 */
	private static function check_email_domain() {
		$site_domain = wp_parse_url( home_url(), PHP_URL_HOST );
		$site_domain = str_replace( 'www.', '', $site_domain );

		// Get the from email (apply wp_mail_from filter).
		$from_email = apply_filters( 'wp_mail_from', 'wordpress@' . $site_domain );

		// Extract domain from email.
		$from_domain = '';
		if ( preg_match( '/@(.+)$/', $from_email, $matches ) ) {
			$from_domain = $matches[1];
		}

		// Check if domains match.
		$is_mismatch = ( $from_domain !== $site_domain );

		// Check for generic addresses.
		$generic_prefixes = array( 'wordpress', 'admin', 'no-reply', 'noreply', 'root' );
		$email_prefix     = '';
		if ( preg_match( '/^([^@]+)@/', $from_email, $matches ) ) {
			$email_prefix = strtolower( $matches[1] );
		}

		$is_generic = in_array( $email_prefix, $generic_prefixes, true );

		return array(
			'from_email'  => $from_email,
			'from_domain' => $from_domain,
			'site_domain' => $site_domain,
			'is_mismatch' => $is_mismatch,
			'is_generic'  => $is_generic,
		);
	}

	/**
	 * Get detailed information about the finding.
	 *
	 * @since  1.2601.2148
	 * @param  array $email_data Email domain check data.
	 * @return array Details array with explanation and solutions.
	 */
	private static function get_details( $email_data ) {
		$from_email  = $email_data['from_email'];
		$from_domain = $email_data['from_domain'];
		$site_domain = $email_data['site_domain'];
		$is_mismatch = $email_data['is_mismatch'];
		$is_generic  = $email_data['is_generic'];

		$issues = array();
		if ( $is_mismatch ) {
			$issues[] = sprintf(
				/* translators: 1: From domain, 2: Site domain */
				__( 'Domain mismatch: Sending from %1$s instead of %2$s', 'wpshadow' ),
				$from_domain,
				$site_domain
			);
		}
		if ( $is_generic ) {
			$issues[] = sprintf(
				/* translators: 1: Generic email */
				__( 'Generic address: Using %1$s instead of business email', 'wpshadow' ),
				$from_email
			);
		}

		$explanation = sprintf(
			/* translators: 1: From email, 2: Issues list */
			__( 'Your WordPress site sends emails from %1$s with the following issues: %2$s. Domain mismatch increases spam score by ~40%% because email authentication (SPF/DKIM) validates the sending domain. When WordPress sends from wordpress@hostname.com but your site is example.com, authentication fails. Generic addresses like wordpress@ or no-reply@ appear unprofessional and trigger spam filters.', 'wpshadow' ),
			$from_email,
			implode( '; ', $issues )
		);

		$solutions = array(
			'free' => array(
				__( 'Use wp_mail_from filter: Add code to set proper from address', 'wpshadow' ),
				sprintf(
					/* translators: 1: Recommended email format */
					__( 'Set business email: Use %1$s instead of generic wordpress@', 'wpshadow' ),
					'hello@' . $site_domain
				),
				__( 'Update WordPress settings: Change admin email to match domain', 'wpshadow' ),
			),
			'premium' => array(
				__( 'Use SMTP plugin: WP Mail SMTP or Post SMTP to configure proper from address', 'wpshadow' ),
				__( 'Configure email authentication: Set up SPF/DKIM/DMARC records', 'wpshadow' ),
				__( 'Use email service: SendGrid, Mailgun, or Amazon SES with domain authentication', 'wpshadow' ),
			),
			'advanced' => array(
				__( 'Custom SMTP configuration: Full phpmailer_init customization', 'wpshadow' ),
				__( 'Subdomain for email: Use mail.example.com for transactional emails', 'wpshadow' ),
				__( 'Implement DMARC policy: Enforce sender authentication', 'wpshadow' ),
			),
		);

		$additional_info = sprintf(
			/* translators: 1: Recommended format */
			__( 'Recommended: Use business email like hello@%1$s or support@%1$s that matches your domain. Configure SPF and DKIM records to authenticate emails. Domain-matched emails with proper authentication have 90%% better deliverability than generic mismatched addresses.', 'wpshadow' ),
			$site_domain
		);

		return array(
			'explanation'     => $explanation,
			'solutions'       => $solutions,
			'additional_info' => $additional_info,
			'technical_data'  => array(
				'current_from_email' => $from_email,
				'current_from_domain' => $from_domain,
				'site_domain'        => $site_domain,
				'is_mismatch'        => $is_mismatch ? 'Yes' : 'No',
				'is_generic'         => $is_generic ? 'Yes' : 'No',
				'recommended_email'  => 'hello@' . $site_domain,
				'spam_score_impact'  => '~40% increase',
			),
			'resources'       => array(
				array(
					'label' => __( 'WP Mail SMTP Plugin', 'wpshadow' ),
					'url'   => 'https://wordpress.org/plugins/wp-mail-smtp/',
				),
				array(
					'label' => __( 'Email Authentication (SPF/DKIM)', 'wpshadow' ),
					'url'   => 'https://www.dmarcanalyzer.com/how-to-create-an-spf-record-for-your-domain/',
				),
			),
		);
	}
}
