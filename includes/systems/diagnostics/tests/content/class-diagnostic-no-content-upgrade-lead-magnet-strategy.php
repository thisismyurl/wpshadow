<?php
/**
 * No Content Upgrade Lead Magnet Strategy Diagnostic
 *
 * Detects when content upgrades are not offered,
 * missing high-conversion email capture opportunities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Content Upgrade Lead Magnet Strategy
 *
 * Checks whether content upgrades are offered
 * to convert blog readers into email subscribers.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Content_Upgrade_Lead_Magnet_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-content-upgrade-lead-magnet';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Upgrade Lead Magnets';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether content upgrades are offered';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for content upgrade strategy
		$posts = get_posts( array(
			'post_type'      => 'post',
			'posts_per_page' => 20,
			'post_status'    => 'publish',
		) );

		$has_content_upgrades = false;
		foreach ( $posts as $post ) {
			$content = $post->post_content;
			// Look for common content upgrade patterns
			if ( strpos( $content, 'download' ) !== false ||
				strpos( $content, 'checklist' ) !== false ||
				strpos( $content, 'template' ) !== false ||
				strpos( $content, 'guide' ) !== false ) {
				$has_content_upgrades = true;
				break;
			}
		}

		if ( ! $has_content_upgrades ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not offering content upgrades, which convert 5-10x better than generic signup forms. Content upgrades are article-specific bonuses: if someone reads "10 Email Tips," offer "Email Template Pack" for their email. This converts better because: it\'s directly relevant, they\'re already interested in the topic, the value is clear. Generic "subscribe to newsletter" converts 1-2%, content upgrades convert 10-30%.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Email Conversion Rate',
					'potential_gain' => '+5-10x email signup rate',
					'roi_explanation' => 'Content upgrades convert 10-30% vs 1-2% for generic forms because they\'re topic-specific and immediately valuable.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/content-upgrade-lead-magnets?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
