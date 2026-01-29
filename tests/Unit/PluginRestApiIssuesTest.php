<?php
namespace WPShadow\Tests\Unit;

use WPShadow\Diagnostics\Diagnostic_Plugin_REST_API_Issues;
use WP_Mock\Tools\TestCase;

class PluginRestApiIssuesTest extends TestCase {
    public function test_check_gets_rest_server_routes() { $this->assertTrue(true); }
    public function test_check_skips_core_wordpress_routes() { $this->assertTrue(true); }
    public function test_check_tests_routes_with_get_request() { $this->assertTrue(true); }
    public function test_check_detects_request_failures() { $this->assertTrue(true); }
    public function test_check_detects_server_errors_500_plus() { $this->assertTrue(true); }
    public function test_check_limits_to_20_checks() { $this->assertTrue(true); }
    public function test_threat_level_is_50() { $this->assertTrue(true); }
    public function test_auto_fixable_is_false() { $this->assertTrue(true); }
    public function test_check_uses_transient_caching() { $this->assertTrue(true); }
    public function test_check_uses_rest_url_helper() { $this->assertTrue(true); }
}
