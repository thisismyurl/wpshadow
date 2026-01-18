<?php
// Handle GitHub Codespaces port forwarding
// The proxy sends HTTPS traffic to port 80, so we need to preserve the original port
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $_SERVER['HTTPS'] = ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'on' : 'off';
}

// Preserve the original port from the forwarded host header
if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
}

// Set the correct site URL for GitHub Codespaces (with explicit port)
define('WP_HOME', 'https://stunning-fishstick-j69p5j559jqcpw79-9000.app.github.dev');
define('WP_SITEURL', 'https://stunning-fishstick-j69p5j559jqcpw79-9000.app.github.dev');




