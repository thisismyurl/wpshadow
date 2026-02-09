<?php
/**
 * Help card: Getting Started
 *
 * @package WPShadow
 */

use WPShadow\Core\UTM_Link_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'id'          => 'getting-started',
	'order'       => 10,
	'section'     => 'resources',
	'width'       => 'half',
	'title'       => __( 'Getting Started with WPShadow', 'wpshadow' ),
	'description' => __( 'Learn the basics of how WPShadow helps you maintain a healthy WordPress site.', 'wpshadow' ),
	'icon'        => 'dashicons-info',
	'url'         => UTM_Link_Manager::kb_link( 'getting-started', 'help_page' ),
	'video'       => 'https://wpshadow.com/academy/getting-started?utm_source=wpshadow&utm_medium=plugin&utm_campaign=help_page&utm_content=video_getting_started',
);
