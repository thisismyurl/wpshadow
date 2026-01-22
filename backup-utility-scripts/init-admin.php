<?php
/**
 * Initialize WordPress Admin User
 * Run this after WordPress tables are created
 */

// Connect to WordPress
if ( ! file_exists( '/var/www/html/wp-load.php' ) ) {
    echo "WordPress not installed yet\n";
    exit(1);
}

require( '/var/www/html/wp-load.php' );

// Check if database is initialized
if ( ! function_exists( 'get_users' ) ) {
    echo "WordPress database not initialized\n";
    exit(1);
}

// Check if admin user exists
$admin = get_user_by( 'login', 'admin' );

if ( $admin ) {
    // Update existing admin
    wp_update_user( array(
        'ID'        => $admin->ID,
        'user_pass' => 'admin',
    ) );
    echo "Admin password updated to: admin\n";
} else {
    // Create new admin user
    $user_id = wp_create_user( 'admin', 'admin', 'admin@example.com' );
    
    if ( is_wp_error( $user_id ) ) {
        echo "Error creating user: " . $user_id->get_error_message() . "\n";
        exit(1);
    }
    
    $user = new WP_User( $user_id );
    $user->set_role( 'administrator' );
    
    echo "Admin user created successfully\n";
    echo "Username: admin\n";
    echo "Password: admin\n";
}
