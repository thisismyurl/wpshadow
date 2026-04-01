<?php
/**
 * Diagnostic: Email List Building Strategy
 *
 * Tests if site has an active email list building strategy in place.
 * Email lists provide direct access to your audience and drive conversions.
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
 * Email List Building Strategy Diagnostic Class
 *
 * Checks if site has email opt-in forms, lead magnets, and
 * strategies to grow an email subscriber list.
 *
 * Detection methods:
 * - Email marketing service integration
 * - Opt-in forms presence
 * - Lead magnet offerings
 * - Popup/slide-in forms
 *
 * @since 0.6093.1200
 */
class Diagnostic_Builds_Email_List extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'builds-email-list';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email List Building Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site has an active email list building strategy in place';

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
	 * - 1 point: Email marketing service connected
	 * - 1 point: Opt-in forms visible on site
	 * - 1 point: Lead magnet or content upgrade offered
	 * - 1 point: Popup or slide-in form implemented
	 * - 1 point: Multiple opt-in opportunities
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 5;
		$details   = array();

		// Check for email marketing plugins.
		$email_plugins = array(
			'mailchimp-for-wp/mailchimp-for-wp.php'      => 'Mailchimp',
			'newsletter/plugin.php'                      => 'Newsletter',
			'mailpoet/mailpoet.php'                      => 'MailPoet',
			'convertkit/convertkit.php'                  => 'ConvertKit',
			'klaviyo/klaviyo.php'                        => 'Klaviyo',
		);

		$email_service_found = false;
		foreach ( $email_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['email_service'] = $name;
				$email_service_found = true;
				break;
			}
		}

		// Check for form builder plugins with email capabilities.
		$form_plugins = array(
			'wpforms-lite/wpforms.php'                   => 'WPForms',
			'ninja-forms/ninja-forms.php'                => 'Ninja Forms',
			'forminator/forminator.php'                  => 'Forminator',
			'gravityforms/gravityforms.php'              => 'Gravity Forms',
		);

		foreach ( $form_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['form_builder'] = $name;
				break;
			}
		}

		// Check for popup/opt-in plugins.
		$popup_plugins = array(
			'optinmonster/optin-monster-wp-api.php'      => 'OptinMonster',
			'popup-maker/popup-maker.php'                => 'Popup Maker',
			'convertpro/convertpro.php'                  => 'Convert Pro',
			'hustle/opt-in.php'                          => 'Hustle',
			'bloom/bloom.php'                            => 'Bloom',
		);

		foreach ( $popup_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['popup_tool'] = $name;
				break;
			}
		}

		// Check for lead magnet indicators (downloads, freebies).
		$recent_posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => 50,
			)
		);

		$lead_magnet_count = 0;
		foreach ( $recent_posts as $post ) {
			$content = strtolower( $post->post_content );
			if ( strpos( $content, 'download' ) !== false &&
			     ( strpos( $content, 'free' ) !== false ||
			       strpos( $content, 'subscribe' ) !== false ||
			       strpos( $content, 'email' ) !== false ) ) {
				$lead_magnet_count++;
			}
		}

		if ( $lead_magnet_count > 0 ) {
			$score++;
			$details['lead_magnet_mentions'] = $lead_magnet_count;
		}

		// Check for sidebar widgets with email forms.
		if ( is_active_sidebar( 'sidebar-1' ) || is_active_sidebar( 'primary' ) ) {
			$sidebars = wp_get_sidebars_widgets();
			$has_email_widget = false;

			foreach ( $sidebars as $sidebar => $widgets ) {
				if ( is_array( $widgets ) ) {
					foreach ( $widgets as $widget ) {
						if ( strpos( $widget, 'mail' ) !== false ||
						     strpos( $widget, 'newsletter' ) !== false ||
						     strpos( $widget, 'subscribe' ) !== false ) {
							$has_email_widget = true;
							break 2;
						}
					}
				}
			}

			if ( $has_email_widget ) {
				$score++;
				$details['sidebar_email_widget'] = true;
			}
		}

		// Calculate percentage score.
		$percentage = ( $score / $max_score ) * 100;

		// Pass if score is 60% or higher.
		if ( $percentage >= 60 ) {
			return null;
		}

		// Build finding.
		$severity     = $percentage < 30 ? 'medium' : 'low';
		$threat_level = (int) ( 55 - $percentage );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: percentage score */
				__( 'Email list building score: %d%%. Growing an email list gives you direct access to your audience.', 'wpshadow' ),
				(int) $percentage
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/email-list-building?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
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
			'Your email list is your most valuable marketing asset. Unlike social media followers (controlled by algorithms), email subscribers are yours. Email marketing has the highest ROI of any digital channel: $42 for every $1 spent. A growing email list means you can reach your audience anytime, drive traffic on demand, and build lasting relationships.',
			'wpshadow'
		);
	}
}
