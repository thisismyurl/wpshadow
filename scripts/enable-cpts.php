<?php
/**
 * Enable all managed CPTs on the test server.
 *
 * Usage: wp eval-file scripts/enable-cpts.php
 * Or: php scripts/enable-cpts.php (with WordPress bootstrapped)
 *
 * @package WPShadow
 */

// If WordPress is not loaded, load it.
if ( ! function_exists( 'add_option' ) ) {
	// Find wp-load.php by walking up directory tree.
	$wp_load = null;
	$dir     = dirname( __FILE__ );

	for ( $i = 0; $i < 10; $i++ ) {
		$dir = dirname( $dir );
		if ( file_exists( "$dir/wp-load.php" ) ) {
			$wp_load = "$dir/wp-load.php";
			break;
		}
	}

	if ( ! $wp_load || ! file_exists( $wp_load ) ) {
		die( "Could not find WordPress installation\n" );
	}

	require_once $wp_load;
}

// All managed CPT slugs.
$cpts = array(
	'case_study',
	'portfolio_item',
	'testimonial',
	'service',
	'training_program',
	'training_event',
	'download',
	'tool',
	'faq',
);

// Build activation settings: all CPTs set to 1 (enabled).
$activation_settings = array();
foreach ( $cpts as $cpt ) {
	$activation_settings[ $cpt ] = 1;
}

// Update the option.
$option_key = 'wpshadow_post_type_activation_settings';
update_option( $option_key, $activation_settings );

echo "✓ Enabled " . count( $cpts ) . " CPTs\n";
echo "✓ Option key: $option_key\n";
echo "✓ Enabled CPTs: " . implode( ', ', $cpts ) . "\n";

// Verify the update.
$saved = get_option( $option_key );
if ( $saved === $activation_settings ) {
	echo "✓ Verified: settings saved successfully\n";
} else {
	echo "✗ Warning: verification failed\n";
}
