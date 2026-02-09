<?php
/**
 * Help card: Privacy
 *
 * @package WPShadow
 */

use WPShadow\Core\UTM_Link_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'id'          => 'privacy',
	'order'       => 60,
	'section'     => 'resources',
	'width'       => 'half',
	'title'       => __( 'Privacy & Security', 'wpshadow' ),
	'description' => __( 'Learn about our privacy-first approach and how your data is protected.', 'wpshadow' ),
	'icon'        => 'dashicons-lock',
	'url'         => UTM_Link_Manager::kb_link( 'privacy', 'help_page' ),
	'video'       => 'https://wpshadow.com/academy/privacy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=help_page&utm_content=video_privacy',
);
