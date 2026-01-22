#!/bin/bash
#
# WPShadow Local Environment Setup
# Installs code quality tools and creates aliases
# Run: bash scripts/setup-local-env.sh
#

set -e

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║   WPShadow Local Environment Setup                        ║"
echo "║   Installing code quality tools & creating aliases       ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""

cd /workspaces/wpshadow

# 1. Install additional Composer packages
echo "📦 Installing additional code quality tools..."
echo ""

echo "  • Installing Psalm (advanced type checking)..."
composer require --dev vimeo/psalm --quiet

echo "  • Installing PHPCPD (duplicate code detection)..."
composer require --dev sebastian/phpcpd --quiet

echo "  • Installing PHPMetrics (code metrics)..."
composer require --dev phpmetrics/phpmetrics --quiet

echo "  • Installing PHPMD (code smell detection)..."
composer require --dev phpmd/phpmd --quiet

echo "  • Installing PHPCompatibility (version checking)..."
composer require --dev phpcompatibility/php-compatibility --quiet

echo "✅ All tools installed!"
echo ""

# 2. Create configuration files
echo "⚙️  Creating configuration files..."
echo ""

# PHPStan config
cat > phpstan.neon << 'EOF'
parameters:
    level: 8
    paths:
        - includes
        - wpshadow.php
    
    excludePaths:
        - vendor/
        - tests/
    
    ignoreErrors:
        - '#Call to undefined function wp_#'
        - '#Call to undefined function is_#'
        - '#Undefined variable#'
        - '#Access to private property#'

    bootstrapFiles:
        - bootstrap.php
EOF

echo "  ✓ phpstan.neon"

# PHPCS config
cat > .phpcs.xml << 'EOF'
<?xml version="1.0"?>
<ruleset name="WPShadow">
    <description>WordPress coding standards for WPShadow</description>
    
    <arg name="extensions" value="php" />
    <arg name="colors" />
    
    <file>wpshadow.php</file>
    <file>includes</file>
    
    <exclude-pattern>*/vendor/*</exclude-pattern>
    <exclude-pattern>*/tests/*</exclude-pattern>
    
    <rule ref="WordPress-Extra">
        <exclude name="WordPress.Files.FileName.InvalidClassFileName" />
        <exclude name="WordPress.Files.FileName.NotHyphenatedLowercase" />
    </rule>
</ruleset>
EOF

echo "  ✓ .phpcs.xml"

# PHPMD rules
cat > phpmd.xml << 'EOF'
<?xml version="1.0" encoding="UTF-8" ?>
<ruleset name="WPShadow Rules"
    xmlns="http://pmd.sf.net/ruleset/1.0.0"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://pmd.sf.net/ruleset/1.0.0
                        http://pmd.sf.net/ruleset_xml_schema.xsd">
    <rule ref="rulesets/php/codesize.xml" />
    <rule ref="rulesets/php/design.xml" />
    <rule ref="rulesets/php/naming.xml" />
    <rule ref="rulesets/php/unusedcode.xml" />
</ruleset>
EOF

echo "  ✓ phpmd.xml"

# Psalm config
cat > psalm.xml << 'EOF'
<?xml version="1.0"?>
<psalm
    errorLevel="3"
    resolveFromConfigFile="true"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="https://getpsalm.org/schema/psalm"
    xsi:schemaLocation="https://getpsalm.org/schema/psalm vendor/vimeo/psalm/config.xsd"
>
    <projectFiles>
        <directory name="includes" />
        <file name="wpshadow.php" />
    </projectFiles>
    <php>
        <version>8.0</version>
    </php>
</psalm>
EOF

echo "  ✓ psalm.xml"

# WordPress stubs for PHPStan
cat > bootstrap.php << 'EOF'
<?php
/**
 * PHPStan Bootstrap - WordPress stubs
 * Prevents "undefined function" errors for WordPress functions
 */

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('wp_kses_post')) {
    function wp_kses_post($str) {
        return $str;
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        return $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value) {
        return true;
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($cap) {
        return false;
    }
}

if (!function_exists('add_action')) {
    function add_action($hook, $function_to_add, $priority = 10, $accepted_args = 1) {
        return true;
    }
}

if (!function_exists('do_action')) {
    function do_action($hook, ...$args) {
        return null;
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters($hook, $value, ...$args) {
        return $value;
    }
}
EOF

echo "  ✓ bootstrap.php"

echo ""
echo "✅ Configuration files created!"
echo ""

# 3. Create shell aliases
echo "🔧 Creating shell aliases..."
echo ""

# Detect shell
if [ -f ~/.bashrc ]; then
    SHELL_RC="~/.bashrc"
elif [ -f ~/.zshrc ]; then
    SHELL_RC="~/.zshrc"
else
    SHELL_RC="~/.bash_profile"
fi

# Add aliases to shell config
cat >> "$SHELL_RC" << 'EOF'

# WPShadow Code Quality Aliases
# Added by setup-local-env.sh

# Quick quality check (standards + analysis)
alias wq='cd /workspaces/wpshadow && vendor/bin/phpcs --quiet && vendor/bin/phpstan analyse --memory-limit=512M --quiet && echo "✅ Quality checks passed"'

# Auto-fix code standards
alias wqf='cd /workspaces/wpshadow && vendor/bin/phpcbf --standard=WordPress-Extra includes wpshadow.php && echo "✅ Code fixed"'

# Full quality report
alias wqr='cd /workspaces/wpshadow && echo "📊 WPShadow Quality Report" && echo "Coding Standards:" && vendor/bin/phpcs --report=summary includes wpshadow.php && echo "" && echo "Static Analysis:" && vendor/bin/phpstan analyse --memory-limit=512M includes wpshadow.php && echo "" && echo "Duplicates:" && vendor/bin/phpcpd includes/ || echo "No duplicates found"'

# Find duplicate code
alias wqdupe='cd /workspaces/wpshadow && vendor/bin/phpcpd includes/'

# Code metrics
alias wqmetrics='cd /workspaces/wpshadow && vendor/bin/phpmetrics --report-html=metrics includes/ && echo "📊 Open metrics/index.html" && open metrics/index.html 2>/dev/null || echo "Report generated in metrics/index.html"'

# Type checking with Psalm
alias wqtype='cd /workspaces/wpshadow && vendor/bin/psalm'

# Code smell detection
alias wqsmell='cd /workspaces/wpshadow && vendor/bin/phpmd includes xml phpmd.xml'

# Check specific file
wq-file() {
    cd /workspaces/wpshadow
    echo "Checking $1..."
    vendor/bin/phpcs "$1"
    vendor/bin/phpstan analyse "$1"
}

export -f wq-file
EOF

echo "  ✓ Added to $SHELL_RC"

echo ""
echo "╔═══════════════════════════════════════════════════════════╗"
echo "║              ✅ SETUP COMPLETE                           ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""
echo "🚀 Quick Start Commands (reload shell first):"
echo ""
echo "  wq              Check code quality (standards + analysis)"
echo "  wqf             Auto-fix code standards issues"
echo "  wqr             Full quality report"
echo "  wqdupe          Find duplicate code"
echo "  wqmetrics       Generate code metrics (HTML report)"
echo "  wqtype          Run Psalm type checking"
echo "  wqsmell         Detect code smells"
echo "  wq-file file.php    Check specific file"
echo ""
echo "📖 Run: source $SHELL_RC"
echo "   to activate aliases immediately"
echo ""
