<?php
/**
 * No Lead Magnets Diagnostic
 *
 * Tests whether the site offers downloadable resources for list building.
 * Lead magnets are essential for email list growth.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5003.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_No_Lead_Magnets Class
 *
 * Detects when sites lack downloadable resources (PDFs, checklists, guides)
 * for email list building. Lead magnets convert 10-50x better than generic signup.
 *
 * @since 1.5003.1200
 */
class Diagnostic_No_Lead_Magnets extends Diagnostic_Base {

	protected static $slug = 'no-lead-magnets';
	protected static $title = 'No Lead Magnets';
	protected static $description = 'Tests whether downloadable resources are offered for list building';
	protected static $family = 'conversion';

	public static function check() {
		$score          = 0;
		$max_score      = 4;
		$score_details  = array();
		$recommendations = array();

		// Check for downloadable content keywords.
		$lead_magnet_content = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => 20,
				'post_status'    => 'publish',
				's'              => 'download free pdf checklist guide ebook workbook template',
			)
		);

		if ( count( $lead_magnet_content ) >= 5 ) {
			$score += 2;
			$score_details[] = sprintf( __( '✓ %d+ pages mention downloadables', 'wpshadow' ), count( $lead_magnet_content ) );
		} elseif ( count( $lead_magnet_content ) > 0 ) {
			++$score;
			$score_details[]   = sprintf( __( '◐ %d page(s) with download mentions', 'wpshadow' ), count( $lead_magnet_content ) );
			$recommendations[] = __( 'Create more lead magnets (checklists, templates, guides)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No downloadable resources detected', 'wpshadow' );
			$recommendations[] = __( 'Create lead magnets: PDF guides, checklists, templates related to your content', 'wpshadow' );
		}

		// Check for form plugins.
		$form_plugins = array(
			'contact-form-7/wp-contact-form-7.php',
			'wpforms-lite/wpforms.php',
			'ninja-forms/ninja-forms.php',
			'gravityforms/gravityforms.php',
		);

		$has_forms = false;
		foreach ( $form_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_forms = true;
				++$score;
				$score_details[] = __( '✓ Form plugin active (can deliver lead magnets)', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_forms ) {
			$score_details[]   = __( '✗ No form plugin for lead magnet delivery', 'wpshadow' );
			$recommendations[] = __( 'Install WPForms or Contact Form 7 to deliver downloads', 'wpshadow' );
		}

		// Check for email marketing integration.
		if ( is_plugin_active( 'mailchimp-for-wp/mailchimp-for-wp.php' ) || is_plugin_active( 'newsletter/plugin.php' ) ) {
			++$score;
			$score_details[] = __( '✓ Email marketing plugin active', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No email marketing integration', 'wpshadow' );
			$recommendations[] = __( 'Integrate Mailchimp or newsletter plugin to build list', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage >= 70 ) {
			return null;
		}

		$severity = 'medium';
		$threat_level = 25;

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Lead magnet score: %d%%. Lead magnets (free downloads in exchange for email) convert 10-50x better than generic signup forms. Types: PDF guides, checklists, templates, toolkits, cheat sheets. Create content upgrades: post-specific downloads related to each article\'s topic.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/lead-magnets',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Lead magnets provide value exchange, growing email lists that convert 40x better than social media followers.', 'wpshadow' ),
		);
	}
}
