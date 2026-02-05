<?php
/**
 * Feed Content Length Treatment
 *
 * Checks if the feed content length is within recommended limits.
 *
 * @since   1.6032.1921
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Feed_Content_Length Class
 *
 * Checks if the feed content length is within recommended limits.
 */
class Treatment_Feed_Content_Length extends Treatment_Base {
	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-content-length';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Content Length';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the feed content length is within recommended limits.';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'feed';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6032.1921
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$feed_url = get_feed_link();
		$response = wp_remote_get( $feed_url, array( 'timeout' => 5 ) );
		if ( is_wp_error( $response ) ) {
			return null;
		}
		$body = wp_remote_retrieve_body( $response );
		if ( strlen( $body ) > 1048576 ) { // 1MB
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Feed content length exceeds 1MB, which may cause issues with some feed readers.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level'=> 50,
				'auto_fixable'=> false,
				'kb_link'     => 'https://wpshadow.com/kb/feed-content-length',
			);
		}
		return null;
	}
}
