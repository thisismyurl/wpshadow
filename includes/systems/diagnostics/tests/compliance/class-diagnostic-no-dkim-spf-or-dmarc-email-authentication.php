<?php
/**
 * No DKIM, SPF, or DMARC Email Authentication Diagnostic
 *
 * Detects when email authentication is not configured,
 * causing emails to be marked as spam and reducing deliverability.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Compliance
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No DKIM, SPF, or DMARC Email Authentication
 *
 * Checks whether email authentication protocols are
 * configured for improved deliverability.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_DKIM_SPF_Or_DMARC_Email_Authentication extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-email-authentication-dkim-spf-dmarc';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Authentication (SPF, DKIM, DMARC)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether email authentication is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for email authentication in DNS records
		// This is a basic check - full validation requires DNS record inspection

		// Get domain name
		$home_url = home_url();
		$domain = wp_parse_url( $home_url, PHP_URL_HOST );

		// Attempt to check SPF record (simplified)
		if ( function_exists( 'dns_get_record' ) ) {
			$dns_records = @dns_get_record( $domain, DNS_TXT );
			$has_spf = false;
			$has_dkim = false;
			$has_dmarc = false;

			if ( $dns_records ) {
				foreach ( $dns_records as $record ) {
					if ( isset( $record['txt'] ) ) {
						if ( strpos( $record['txt'], 'v=spf1' ) !== false ) {
							$has_spf = true;
						}
						if ( strpos( $record['txt'], 'v=DKIM' ) !== false ) {
							$has_dkim = true;
						}
					}
				}
				
				// Check DMARC
				$dmarc_records = @dns_get_record( '_dmarc.' . $domain, DNS_TXT );
				if ( $dmarc_records ) {
					$has_dmarc = true;
				}
			}

			if ( ! $has_spf || ! $has_dkim || ! $has_dmarc ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __(
						'Email authentication (SPF, DKIM, DMARC) is not configured, which means: email providers don\'t verify you own the domain, so emails get marked as spam more often, you have no protection against spoofing (criminals sending emails pretending to be you). These three protocols take 30 minutes to set up and directly improve email deliverability by 10-30%. SPF says "mail from this domain comes from these servers", DKIM cryptographically signs emails, DMARC enforces the policy.',
						'wpshadow'
					),
					'severity'      => 'high',
					'threat_level'  => 70,
					'auto_fixable'  => false,
					'business_impact' => array(
						'metric'         => 'Email Deliverability',
						'potential_gain' => '+10-30% better deliverability',
						'roi_explanation' => 'Email authentication improves deliverability and prevents email spoofing. Setup takes 30 minutes. Direct impact on email marketing ROI.',
					),
					'kb_link'       => 'https://wpshadow.com/kb/email-authentication-spf-dkim-dmarc',
				);
			}
		}

		return null;
	}
}
