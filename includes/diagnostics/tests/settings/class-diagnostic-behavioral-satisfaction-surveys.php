<?php
/**
 * Diagnostic: Member Satisfaction Surveys
 *
 * Tests whether the site conducts quarterly member satisfaction surveys and
 * acts on feedback to improve retention.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4550
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Behavioral
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Member Satisfaction Surveys Diagnostic
 *
 * Checks for survey/feedback systems. Regular satisfaction surveys identify
 * issues before members cancel. Sites using feedback loops have 22% better retention.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Behavioral_Satisfaction_Surveys extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'surveys-member-satisfaction';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Member Satisfaction Surveys';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site conducts regular member satisfaction surveys';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for survey/feedback implementation.
	 *
	 * Looks for survey plugins and feedback forms.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if missing, null if present.
	 */
	public static function check() {
		// Check for survey plugins.
		$survey_plugins = array(
			'wp-polls/wp-polls.php'                          => 'WP-Polls',
			'quiz-master-next/mlw_quizmaster2.php'           => 'Quiz and Survey Master',
			'formidable/formidable.php'                      => 'Formidable Forms',
		);

		foreach ( $survey_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return null; // Has survey capability.
			}
		}

		// Check major form plugins (can be used for surveys).
		$form_plugins = array(
			'wpforms-lite/wpforms.php',
			'gravityforms/gravityforms.php',
			'contact-form-7/wp-contact-form-7.php',
		);

		$has_forms = false;
		foreach ( $form_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_forms = true;
				break;
			}
		}

		// Check for survey pages.
		$survey_keywords = array( 'survey', 'feedback', 'satisfaction', 'nps' );
		$pages           = get_pages();
		
		foreach ( $pages as $page ) {
			foreach ( $survey_keywords as $keyword ) {
				if ( stripos( $page->post_title, $keyword ) !== false || stripos( $page->post_name, $keyword ) !== false ) {
					if ( $has_forms ) {
						return null; // Has survey page with form plugin.
					}
				}
			}
		}

		// Only applicable for membership sites.
		$is_membership_site = false;
		
		$membership_indicators = array(
			class_exists( 'WC_Subscriptions' ),
			class_exists( 'MeprUser' ),
			function_exists( 'pmpro_hasMembershipLevel' ),
			is_plugin_active( 'restrict-content-pro/restrict-content-pro.php' ),
			get_option( 'users_can_register' ),
		);

		foreach ( $membership_indicators as $indicator ) {
			if ( $indicator ) {
				$is_membership_site = true;
				break;
			}
		}

		if ( ! $is_membership_site ) {
			return null; // Not membership site.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'No member satisfaction survey system detected. Regular feedback (quarterly NPS, satisfaction surveys) identifies problems before members cancel. Sites with feedback loops have 22% better retention - they address issues proactively. Use simple 1-10 rating surveys or Net Promoter Score (NPS) to track member sentiment. Act visibly on feedback.',
				'wpshadow'
			),
			'severity'     => 'low',
			'threat_level' => 37,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/member-satisfaction-surveys',
		);
	}
}
