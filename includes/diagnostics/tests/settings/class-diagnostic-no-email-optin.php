<?php
/**
 * Diagnostic: No Email Opt-in Forms
 *
 * Detects missing email list-building opportunities. Email lists typically
 * convert at 3-5% signup rate and provide highest ROI marketing channel.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Email Opt-in Diagnostic Class
 *
 * Checks for email list building forms and integration.
 *
 * Detection methods:
 * - Email marketing plugin detection
 * - Form plugin detection
 * - Opt-in form presence in content
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Email_Optin extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-email-optin';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Email Opt-in Forms';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Missing list-building opportunity - 3-5% typical signup rate';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-engagement';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (4 points):
	 * - 2 points: Email marketing plugin active
	 * - 1 point: Form plugin active
	 * - 1 point: Opt-in forms found in content
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score               = 0;
		$max_score           = 4;
		$has_email_plugin    = false;
		$has_form_plugin     = false;
		$has_optin_forms     = false;
		$active_plugins      = array();

		// Check for email marketing plugins.
		$email_plugins = array(
			'mailchimp-for-wp/mailchimp-for-wp.php'     => 'Mailchimp for WordPress',
			'newsletter/plugin.php'                     => 'Newsletter',
			'mailpoet/mailpoet.php'                     => 'MailPoet',
			'convertkit/convertkit.php'                 => 'ConvertKit',
			'constant-contact-forms/constant-contact-forms.php' => 'Constant Contact',
			'email-subscribers/email-subscribers.php'   => 'Email Subscribers',
		);

		foreach ( $email_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score             += 2;
				$has_email_plugin   = true;
				$active_plugins[]   = $name;
				break;
			}
		}

		// Check for opt-in specific plugins.
		$optin_plugins = array(
			'thrive-leads/thrive-leads.php'             => 'Thrive Leads',
			'bloom/bloom.php'                           => 'Bloom',
			'optinmonster/optin-monster-wp-api.php'     => 'OptinMonster',
			'elementor-pro/elementor-pro.php'           => 'Elementor Pro Forms',
		);

		foreach ( $optin_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$has_form_plugin  = true;
				$active_plugins[] = $name;
				break;
			}
		}

		// Check for opt-in forms in content.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 30,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$posts_with_optin = 0;
		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Check for common opt-in patterns.
			$optin_patterns = array(
				'subscribe',
				'newsletter',
				'email',
				'opt-in',
				'opt in',
				'join our',
				'mailing list',
				'[mc4wp',
				'[newsletter',
				'[convertkit',
			);

			foreach ( $optin_patterns as $pattern ) {
				if ( stripos( $content, $pattern ) !== false ) {
					$posts_with_optin++;
					$has_optin_forms = true;
					break;
				}
			}
		}

		if ( $has_optin_forms ) {
			$score++;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.7 ) ) {
			return null;
		}

		// Build message.
		$issues = array();
		if ( ! $has_email_plugin ) {
			$issues[] = __( 'No email marketing plugin detected', 'wpshadow' );
		}
		if ( ! $has_form_plugin ) {
			$issues[] = __( 'No dedicated opt-in plugin detected', 'wpshadow' );
		}
		if ( ! $has_optin_forms ) {
			$issues[] = __( 'No opt-in forms found in recent content', 'wpshadow' );
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of issues */
				__( '%s. Email lists are your highest-ROI marketing channel (4200%% ROI per DMA). You own the list (vs. social media algorithms). Average signup rate: 3-5%% with good offer. Benefits: Direct communication, repeat traffic, product launches, relationship building. Every 1,000 subscribers = ~$10-50/month revenue potential. Setup: Choose email service (Mailchimp, ConvertKit, MailPoet), create compelling offer (free PDF, checklist, course), add forms (sidebar, popup, inline, footer), optimize with A/B testing.', 'wpshadow' ),
				implode( '. ', $issues )
			),
			'severity'    => 'medium',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/no-email-optin',
			'stats'       => array(
				'has_email_plugin' => $has_email_plugin,
				'has_form_plugin'  => $has_form_plugin,
				'posts_checked'    => count( $posts ),
				'posts_with_optin' => $posts_with_optin,
				'active_plugins'   => $active_plugins,
			),
			'recommendation' => __( 'Install email marketing plugin (Mailchimp, MailPoet). Create compelling lead magnet. Add opt-in forms in multiple locations (popup, inline, sidebar, footer). Test different offers and copy. Aim for 3-5% conversion rate.', 'wpshadow' ),
		);
	}
}
