<?php
/**
 * Email Marketing Integration Diagnostic
 *
 * Verifies email marketing service integration for building
 * subscriber lists and automating email campaigns.
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
 * Diagnostic_Email_Marketing_Integration Class
 *
 * Verifies email marketing setup.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Email_Marketing_Integration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-marketing-integration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Marketing Integration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies email marketing service integration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if no integration, null otherwise.
	 */
	public static function check() {
		$email_status = self::check_email_marketing();

		if ( $email_status['is_integrated'] ) {
			return null; // Email marketing configured
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No email marketing integration. Not building email list = leaving money on table. Email ROI: $36 per $1 spent (highest of any channel). 1000 subscribers = $3600/month potential.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/email-marketing',
			'family'       => self::$family,
			'meta'         => array(
				'integration_detected' => $email_status['method'],
			),
			'details'      => array(
				'why_email_marketing'          => array(
					__( 'Highest ROI: $36 per $1 spent (DMA 2019)' ),
					__( 'You own the list (not rented like social media)' ),
					__( 'Direct communication (inbox, not algorithm)' ),
					__( '81% prefer email for brand communication' ),
					__( 'Automated: Welcome series, abandoned cart, re-engagement' ),
				),
				'popular_email_services'       => array(
					'Mailchimp' => array(
						'Free: Up to 500 subscribers',
						'Paid: $13+/month',
						'Features: Automation, templates, analytics',
					),
					'ConvertKit' => array(
						'Free: Up to 1000 subscribers',
						'Paid: $15+/month',
						'Best for: Creators, bloggers',
					),
					'ActiveCampaign' => array(
						'Price: $15+/month',
						'Features: Advanced automation, CRM',
						'Best for: E-commerce, SaaS',
					),
					'Mailerlite' => array(
						'Free: Up to 1000 subscribers',
						'Paid: $10+/month',
						'Budget-friendly',
					),
				),
				'wordpress_integration'        => array(
					'Mailchimp for WordPress' => array(
						'Plugin: MC4WP (4M+ installs)',
						'Setup: Connect API key',
						'Features: Forms, auto-subscribe after purchase',
					),
					'OptinMonster' => array(
						'Purpose: Popups, slide-ins, inline forms',
						'Integrates: All major email services',
						'Price: $9+/month',
					),
					'Thrive Leads' => array(
						'Purpose: Advanced opt-in forms',
						'Features: A/B testing, targeting',
						'Price: $299/year',
					),
				),
				'building_email_list'          => array(
					'Opt-in Forms' => array(
						'Homepage: Prominent signup form',
						'Sidebar: Newsletter widget',
						'Footer: Email capture',
					),
					'Lead Magnets' => array(
						'Free PDF guide',
						'Checklist or template',
						'Discount code (e-commerce)',
						'Exclusive content',
					),
					'Content Upgrades' => array(
						'Blog post: Related bonus content',
						'Requires: Email to download',
						'Conversion: 20-30% vs 2-3% sidebar',
					),
					'Popups (Use Wisely)' => array(
						'Exit-intent: When leaving page',
						'Timed: After 30 seconds',
						'Scroll-triggered: After 50% page',
						'Don\'t: Immediate aggressive popup',
					),
				),
				'email_automation_workflows'   => array(
					'Welcome Series' => array(
						'Email 1: Thank you, set expectations',
						'Email 2: Best content, valuable resource',
						'Email 3: Ask question, encourage reply',
						'Timing: Day 0, 3, 7',
					),
					'Abandoned Cart (E-commerce)' => array(
						'Email 1: "Forgot something?" + cart contents',
						'Email 2: Customer testimonials',
						'Email 3: 10% discount to complete',
						'Timing: 1 hour, 24 hours, 3 days',
					),
					'Re-engagement' => array(
						'Target: Inactive 90+ days',
						'Subject: "We miss you" or "Still interested?"',
						'Offer: Special deal or new content',
					),
				),
				'email_list_best_practices'    => array(
					__( 'Double opt-in: Confirm email address (reduces spam)' ),
					__( 'Segment lists: Send relevant content to each group' ),
					__( 'Personalization: Use first name, purchase history' ),
					__( 'Mobile-friendly: 60% open emails on mobile' ),
					__( 'Clear unsubscribe: Required by law, builds trust' ),
				),
			),
		);
	}

	/**
	 * Check email marketing integration.
	 *
	 * @since  1.2601.2148
	 * @return array Email marketing status.
	 */
	private static function check_email_marketing() {
		// Check for popular email marketing plugins
		if ( is_plugin_active( 'mailchimp-for-wp/mailchimp-for-wp.php' ) ) {
			return array(
				'is_integrated' => true,
				'method'        => 'Mailchimp for WordPress',
			);
		}

		if ( is_plugin_active( 'convertkit/convertkit.php' ) ) {
			return array(
				'is_integrated' => true,
				'method'        => 'ConvertKit',
			);
		}

		if ( is_plugin_active( 'activecampaign/activecampaign.php' ) ) {
			return array(
				'is_integrated' => true,
				'method'        => 'ActiveCampaign',
			);
		}

		if ( is_plugin_active( 'optinmonster/optin-monster-wp-api.php' ) ) {
			return array(
				'is_integrated' => true,
				'method'        => 'OptinMonster',
			);
		}

		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ) {
			return array(
				'is_integrated' => true,
				'method'        => 'MailPoet',
			);
		}

		return array(
			'is_integrated' => false,
			'method'        => 'Not detected',
		);
	}
}
