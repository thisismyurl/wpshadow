<?php
/**
 * Help card: Monitoring
 *
 * @package WPShadow
 */

use WPShadow\Core\UTM_Link_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'id'          => 'monitoring',
	'order'       => 50,
	'section'     => 'resources',
	'width'       => 'half',
	'title'       => __( 'Monitoring & Alerts', 'wpshadow' ),
	'description' => __( 'Stay informed with real-time monitoring and custom alert notifications.', 'wpshadow' ),
	'icon'        => 'dashicons-bell',
	'url'         => UTM_Link_Manager::kb_link( 'monitoring', 'help_page' ),
	'video'       => 'https://wpshadow.com/academy/monitoring?utm_source=wpshadow&utm_medium=plugin&utm_campaign=help_page&utm_content=video_monitoring',
);
