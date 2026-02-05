<?php
/**
 * Lead Magnet Strategy Diagnostic
 *
 * Issue #4778: No Lead Magnet or Incentive to Subscribe
 * Family: business-performance
 *
 * Checks if site offers incentives for email signups.
 * Generic "subscribe to our newsletter" converts at <1%. Lead magnets convert at 10-20%.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6036.1515
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Lead_Magnet_Strategy Class
 *
 * Checks for lead magnet offers to incentivize subscriptions.
 *
 * @since 1.6036.1515
 */
class Diagnostic_Lead_Magnet_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'lead-magnet-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Lead Magnet or Incentive to Subscribe';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if email signup forms offer valuable incentives (lead magnets)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6036.1515
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$issues[] = __( 'Create a valuable free resource (PDF guide, checklist, template)', 'wpshadow' );
		$issues[] = __( 'Offer exclusive content not available on the blog', 'wpshadow' );
		$issues[] = __( 'Provide discount code for first purchase (e-commerce)', 'wpshadow' );
		$issues[] = __( 'Give access to free training or webinar recording', 'wpshadow' );
		$issues[] = __( 'Make lead magnet specific to visitor\'s interests', 'wpshadow' );
		$issues[] = __( 'Test headline: "Get [benefit]" not "Subscribe to newsletter"', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your email signup forms might ask people to subscribe without offering anything in return. Compare results: "Subscribe to our newsletter" converts <1% of visitors (like asking strangers for their phone number), versus "Get our free 10-page SEO checklist" converting 10-20% (offering something valuable first). A lead magnet is a free resource you offer in exchange for an email address. Best lead magnets solve one specific problem immediately: checklists, templates, PDF guides, discount codes, exclusive videos, free tools, or first-chapter downloads. The key: must be instantly accessible (auto-delivered), solve a real problem, and be relevant to your business so subscribers become customers later.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/lead-magnets',
				'details'      => array(
					'recommendations'       => $issues,
					'conversion_rates'      => 'Generic subscribe: <1%. Lead magnet: 10-20%',
					'best_lead_magnets'     => 'Checklist, template, PDF guide, discount code, free course',
					'bad_example'           => '"Subscribe to our newsletter for updates"',
					'good_example'          => '"Download our free 10-page WordPress security checklist"',
					'instant_delivery'      => 'Auto-send lead magnet via email immediately after signup',
					'relevance_test'        => 'Lead magnet should attract ideal customers, not just anyone',
				),
			);
		}

		return null;
	}
}
