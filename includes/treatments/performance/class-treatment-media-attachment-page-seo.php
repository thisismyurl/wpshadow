<?php
/**
 * Media Attachment Page SEO Treatment
 *
 * Tests SEO optimization of media attachment pages by
 * checking indexing controls and title handling.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1625
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Attachment_Page_SEO Class
 *
 * Validates SEO controls for attachment pages.
 *
 * @since 1.6033.1625
 */
class Treatment_Media_Attachment_Page_SEO extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-attachment-page-seo';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Attachment Page SEO';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests SEO optimization of media attachment pages';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1625
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$seo_plugins = array(
			'wordpress-seo/wp-seo.php',
			'rank-math/rank-math.php',
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'autodescription/autodescription.php',
			'seopress/seopress.php',
		);

		$seo_active = false;
		foreach ( $seo_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$seo_active = true;
				break;
			}
		}

		if ( ! $seo_active && ! has_filter( 'wp_robots' ) ) {
			$issues[] = __( 'No SEO controls detected for attachment pages; consider adding noindex or redirects', 'wpshadow' );
		}

		$attachment_post_type = get_post_type_object( 'attachment' );
		if ( $attachment_post_type && $attachment_post_type->publicly_queryable ) {
			$issues[] = __( 'Attachment pages are publicly queryable; ensure they are optimized or redirected to avoid thin content', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-attachment-page-seo',
			);
		}

		return null;
	}
}
