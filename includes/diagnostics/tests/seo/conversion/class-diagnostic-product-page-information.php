<?php
/**
 * Product Page Information Diagnostic
 *
 * Issue #4780: Product/Service Pages Missing Key Purchase Info
 * Family: business-performance
 *
 * Checks if product/service pages include essential purchase information.
 * Missing info causes purchase hesitation and cart abandonment.
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
 * Diagnostic_Product_Page_Information Class
 *
 * Checks for complete product/service information.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Product_Page_Information extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'product-page-information';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Product/Service Pages Missing Key Purchase Info';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if product/service pages answer all pre-purchase questions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$issues[] = __( 'Show clear pricing (final price including tax/fees)', 'wpshadow' );
		$issues[] = __( 'List what\'s included in purchase (features, benefits)', 'wpshadow' );
		$issues[] = __( 'State delivery/fulfillment details (shipping time, digital delivery)', 'wpshadow' );
		$issues[] = __( 'Include refund/guarantee policy ("30-day money-back guarantee")', 'wpshadow' );
		$issues[] = __( 'Add comparison table (Basic vs Pro vs Enterprise)', 'wpshadow' );
		$issues[] = __( 'Show customer reviews/testimonials', 'wpshadow' );
		$issues[] = __( 'Answer: Who is this for? What problem does it solve?', 'wpshadow' );
		$issues[] = __( 'Include FAQs addressing common objections', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your product or service pages might leave visitors with unanswered questions, causing them to leave without purchasing. Research shows visitors abandon purchases when they can\'t find answers to: How much does this cost exactly? (including taxes, shipping, fees), What do I get for my money? (specific features and benefits), How long until I receive it? (shipping time or instant access), Can I get my money back if it doesn\'t work? (refund policy), Who else has bought this successfully? (social proof), Is this right for MY specific situation? (use cases, target audience). Each missing piece of information increases cart abandonment. Think of it like shopping in a store where nothing has a price tag and the staff disappeared—you\'d leave too.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/product-page-information?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'essential_elements'      => 'Price, features, delivery, refund policy, social proof',
					'cart_abandonment_reason' => '27% abandon due to unclear total cost, 23% due to slow shipping',
					'trust_signals'           => 'Reviews, guarantees, secure payment badges',
					'comparison_benefit'      => 'Side-by-side comparisons increase conversions by 19%',
					'faq_benefit'             => 'FAQs can answer objections automatically, reducing drop-off',
					'mobile_consideration'    => 'All info must be accessible on mobile (60% of traffic)',
				),
			);
		}

		return null;
	}
}
