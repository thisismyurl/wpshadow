<?php
/**
 * Email Preview Testing Diagnostic
 *
 * Tests whether the site tests email rendering across major email clients before sending.
 *
 * @since   1.6034.0340
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email Preview Testing Diagnostic Class
 *
 * Email rendering varies wildly across clients. Testing prevents broken
 * emails that damage brand perception and reduce engagement.
 *
 * @since 1.6034.0340
 */
class Diagnostic_Email_Preview_Testing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-preview-testing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Preview Testing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site tests email rendering across major email clients before sending';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email-marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6034.0340
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$testing_score = 0;
		$max_score = 5;

		// Check for testing tools.
		$testing_tools = self::check_testing_tools();
		if ( $testing_tools ) {
			$testing_score++;
		} else {
			$issues[] = __( 'No email preview testing tools or services', 'wpshadow' );
		}

		// Check for responsive design.
		$responsive_design = self::check_responsive_design();
		if ( $responsive_design ) {
			$testing_score++;
		} else {
			$issues[] = __( 'Email templates not responsive for mobile', 'wpshadow' );
		}

		// Check for spam testing.
		$spam_testing = self::check_spam_testing();
		if ( $spam_testing ) {
			$testing_score++;
		} else {
			$issues[] = __( 'No spam filter testing before sending', 'wpshadow' );
		}

		// Check for link testing.
		$link_testing = self::check_link_testing();
		if ( $link_testing ) {
			$testing_score++;
		} else {
			$issues[] = __( 'No automated link validation in emails', 'wpshadow' );
		}

		// Check for testing process.
		$testing_process = self::check_testing_process();
		if ( $testing_process ) {
			$testing_score++;
		} else {
			$issues[] = __( 'No documented email testing process', 'wpshadow' );
		}

		// Determine severity based on testing.
		$testing_percentage = ( $testing_score / $max_score ) * 100;

		if ( $testing_percentage < 40 ) {
			$severity = 'low';
			$threat_level = 25;
		} elseif ( $testing_percentage < 70 ) {
			$severity = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Email testing percentage */
				__( 'Email preview testing at %d%%. ', 'wpshadow' ),
				(int) $testing_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Testing prevents broken emails that damage engagement', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-preview-testing',
			);
		}

		return null;
	}

	/**
	 * Check testing tools.
	 *
	 * @since  1.6034.0340
	 * @return bool True if tools exist, false otherwise.
	 */
	private static function check_testing_tools() {
		// Professional platforms include preview tools.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_email_testing_tools', false );
	}

	/**
	 * Check responsive design.
	 *
	 * @since  1.6034.0340
	 * @return bool True if responsive, false otherwise.
	 */
	private static function check_responsive_design() {
		// Modern email platforms use responsive templates.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ||
			 is_plugin_active( 'newsletter/newsletter.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_responsive_email_templates', false );
	}

	/**
	 * Check spam testing.
	 *
	 * @since  1.6034.0340
	 * @return bool True if testing exists, false otherwise.
	 */
	private static function check_spam_testing() {
		// Advanced platforms include spam checkers.
		return apply_filters( 'wpshadow_tests_spam_score', false );
	}

	/**
	 * Check link testing.
	 *
	 * @since  1.6034.0340
	 * @return bool True if testing exists, false otherwise.
	 */
	private static function check_link_testing() {
		// Professional platforms validate links.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_validates_email_links', false );
	}

	/**
	 * Check testing process.
	 *
	 * @since  1.6034.0340
	 * @return bool True if process documented, false otherwise.
	 */
	private static function check_testing_process() {
		// Check for testing documentation.
		$query = new \WP_Query(
			array(
				's'              => 'email testing preview checklist',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}
}
