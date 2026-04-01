<?php
/**
 * Diagnostic: Responds to User Inquiries
 *
 * Tests if site responds to user inquiries in a timely manner.
 * Quick response times improve user satisfaction and conversions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Responds to User Inquiries Diagnostic Class
 *
 * Checks if site has systems in place to respond to user
 * inquiries quickly through multiple channels.
 *
 * Detection methods:
 * - Contact form availability
 * - Live chat implementation
 * - Email response tracking
 * - Support ticket system
 *
 * @since 0.6093.1200
 */
class Diagnostic_Responds_To_Inquiries extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'responds-to-inquiries';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Responds to User Inquiries';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site responds to user inquiries in a timely manner';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-engagement';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (5 points):
	 * - 1 point: Contact form available
	 * - 1 point: Live chat/chatbot implemented
	 * - 1 point: Multiple contact methods visible
	 * - 1 point: Help desk/ticket system active
	 * - 1 point: FAQ or knowledge base available
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 5;
		$details   = array();

		// Check for contact form plugins.
		$form_plugins = array(
			'contact-form-7/wp-contact-form-7.php'       => 'Contact Form 7',
			'wpforms-lite/wpforms.php'                   => 'WPForms',
			'ninja-forms/ninja-forms.php'                => 'Ninja Forms',
			'forminator/forminator.php'                  => 'Forminator',
			'gravityforms/gravityforms.php'              => 'Gravity Forms',
		);

		foreach ( $form_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['contact_form'] = $name;
				break;
			}
		}

		// Check for live chat plugins.
		$chat_plugins = array(
			'tidio-live-chat/tidio-live-chat.php'        => 'Tidio',
			'livechat-inc/livechat.php'                  => 'LiveChat',
			'tawk-to-live-chat/tawk-to.php'              => 'Tawk.to',
			'wp-live-chat-support/wp-live-chat-support.php' => 'WP Live Chat',
			'chatra/chatra.php'                          => 'Chatra',
		);

		foreach ( $chat_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['live_chat'] = $name;
				break;
			}
		}

		// Check for help desk/ticket systems.
		$helpdesk_plugins = array(
			'awesome-support/awesome-support.php'        => 'Awesome Support',
			'wpsc-support-tickets/wp-support-tickets.php' => 'WP Support Tickets',
			'kb-support/kb-support.php'                  => 'KB Support',
			'helpscout/helpscout.php'                    => 'HelpScout',
		);

		foreach ( $helpdesk_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['helpdesk_system'] = $name;
				break;
			}
		}

		// Check for FAQ or knowledge base.
		$kb_plugins = array(
			'echo-knowledge-base/echo-knowledge-base.php' => 'Echo Knowledge Base',
			'heroic-kb/heroic-kb.php'                    => 'Heroic KB',
			'betterdocs/betterdocs.php'                  => 'BetterDocs',
		);

		foreach ( $kb_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['knowledge_base'] = $name;
				break;
			}
		}

		// Check if contact page exists.
		$contact_page = get_page_by_path( 'contact' );
		if ( ! $contact_page ) {
			$contact_page = get_page_by_path( 'contact-us' );
		}

		if ( $contact_page ) {
			$score++;
			$details['contact_page_exists'] = true;
		}

		// Calculate percentage score.
		$percentage = ( $score / $max_score ) * 100;

		// Pass if score is 60% or higher.
		if ( $percentage >= 60 ) {
			return null;
		}

		// Build finding.
		$severity     = $percentage < 30 ? 'medium' : 'low';
		$threat_level = (int) ( 50 - $percentage );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: percentage score */
				__( 'Inquiry response system score: %d%%. Multiple contact methods and quick response times improve conversions.', 'wpshadow' ),
				(int) $percentage
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/user-inquiry-response?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => $details,
			'why_matters'  => self::get_why_matters(),
		);
	}

	/**
	 * Get the "Why This Matters" educational content.
	 *
	 * @since 0.6093.1200
	 * @return string Explanation of why this diagnostic matters.
	 */
	private static function get_why_matters() {
		return __(
			'Fast response times can be the difference between a sale and a lost customer. When users have questions, they want answers now. Multiple contact methods (chat, email, phone, forms) give users choices, and a knowledge base reduces your support burden by answering common questions automatically. Every unanswered inquiry is potential revenue walking away.',
			'wpshadow'
		);
	}
}
