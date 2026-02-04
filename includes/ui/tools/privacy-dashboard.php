<?php
/**
 * Privacy Dashboard Redirect Utility
 *
 * Redirects to the main Privacy Dashboard page.
 *
 * @package WPShadow
 * @since   1.6030.2300
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Redirect to the Privacy Dashboard page
wp_safe_redirect( admin_url( 'admin.php?page=wpshadow-privacy' ) );
exit;
