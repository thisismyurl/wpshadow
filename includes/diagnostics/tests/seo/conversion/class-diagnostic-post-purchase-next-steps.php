<?php
/**
 * Post Purchase Next Steps Diagnostic
 *
 * Checks whether customers get clear next steps after a purchase or signup.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Conversion
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Purchase Next Steps Diagnostic Class
 *
 * Verifies that confirmation or onboarding content exists.
 *
 * @since 1.6035.1400
 */
class Diagnostic_Post_Purchase_Next_Steps extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'post-purchase-next-steps';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Clear Next Step After Purchase or Signup';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks for clear onboarding or next-step guidance';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$onboarding_pages = self::find_pages_by_keywords(
			array(
				'thank you',
				'next steps',
				'getting started',
				'welcome',
			)
		);

		$stats['onboarding_pages'] = ! empty( $onboarding_pages ) ? implode( ', ', $onboarding_pages ) : 'none';

		$onboarding_plugins = array(
			'fluentcrm/fluentcrm.php' => 'FluentCRM',
			'activecampaign/activecampaign.php' => 'ActiveCampaign',
			'mailchimp-for-wp/mailchimp-for-wp.php' => 'Mailchimp for WP',
			'hubspot-all-in-one-marketing-forms-analytics/hubspot.php' => 'HubSpot',
		);

		$active_onboarding = array();
		foreach ( $onboarding_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_onboarding[] = $plugin_name;
			}
		}

		$stats['onboarding_tools'] = ! empty( $active_onboarding ) ? implode( ', ', $active_onboarding ) : 'none';

		if ( empty( $onboarding_pages ) && empty( $active_onboarding ) ) {
			$issues[] = __( 'No clear onboarding page or email flow detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Clear next steps help new customers feel confident and reduce buyer\'s remorse. A short welcome message and simple instructions can improve retention.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-purchase-next-steps',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}

	/**
	 * Find pages or posts by keyword search.
	 *
	 * @since  1.6035.1400
	 * @param  array $keywords Keywords to search for.
	 * @return array List of matching page titles.
	 */
	private static function find_pages_by_keywords( array $keywords ): array {
		$matches = array();

		foreach ( $keywords as $keyword ) {
			$results = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'post' ),
					'post_status'    => 'publish',
					'posts_per_page' => 5,
				)
			);

			foreach ( $results as $post ) {
				$matches[ $post->ID ] = get_the_title( $post );
			}
		}

		return array_values( $matches );
	}
}
