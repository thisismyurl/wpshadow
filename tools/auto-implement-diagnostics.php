<?php
declare(strict_types=1);

// Auto-implement lean diagnostics: add static properties and a minimal check() if missing.

$root = dirname(__DIR__);
$diagDir = $root . '/includes/diagnostics';

require_once $root . '/includes/core/class-diagnostic-lean-checks.php';

function title_from_slug(string $slug): string {
    $parts = preg_split('/[-_]+/', $slug);
    $parts = array_map(static fn($w) => ucfirst(strtolower($w)), $parts);
    return trim(implode(' ', $parts));
}

function family_from_slug(string $slug): string {
    $first = strtolower(strtok($slug, '-_'));
    $map = [
        'seo' => 'seo',
        'security' => 'security',
        'design' => 'design',
        'monitor' => 'monitor',
        'monitoring' => 'monitor',
        'code' => 'code',
        'performance' => 'performance',
        'config' => 'config',
        'system' => 'system',
    ];
    return $map[$first] ?? 'general';
}

function severity_for_family(string $family): array {
    // Return [severity, threat_level] defaults per family when issue detected.
    return match ($family) {
        'security' => ['high', 80],
        'performance' => ['medium', 60],
        'seo' => ['medium', 55],
        'design' => ['low', 40],
        'code' => ['medium', 50],
        'config' => ['low', 35],
        'system' => ['medium', 50],
        default => ['low', 30],
    };
}

function ensure_strict_types_once(string $content): string {
    // Remove duplicate declare lines, add a single one after opening <?php
    $content = preg_replace('/\R?declare\(\s*strict_types\s*=\s*1\s*\)\s*;\R?/i', "\n", $content ?? '');
    $content = preg_replace('/<\?php\s*/', "<?php\ndeclare(strict_types=1);\n", $content, 1);
    return $content;
}

function insert_props_and_check(string $content, string $slug, string $title, string $description, string $family): string {
    // Skip if check() already present
    if (preg_match('/public\s+static\s+function\s+check\s*\(/', $content)) {
        return $content;
    }

    // Find class opening brace
    if (!preg_match('/class\s+[^\{]+\{/', $content, $m, PREG_OFFSET_CAPTURE)) {
        return $content; // can't confidently patch
    }
    $classOpenPos = $m[0][1] + strlen($m[0][0]);

    // Prepare static properties block (only if not defined)
    $props = [];
    if (!preg_match('/protected\s+static\s+\$slug\s*=/', $content)) {
        $props[] = "\n\tprotected static \$slug = '" . addslashes($slug) . "';";
    }
    if (!preg_match('/protected\s+static\s+\$title\s*=/', $content)) {
        $props[] = "\n\tprotected static \$title = '" . addslashes($title) . "';";
    }
    if (!preg_match('/protected\s+static\s+\$description\s*=/', $content)) {
        $props[] = "\n\tprotected static \$description = '" . addslashes($description) . "';";
    }
    if (!preg_match('/protected\s+static\s+\$family\s*=/', $content)) {
        $props[] = "\n\tprotected static \$family = '" . addslashes($family) . "';";
    }
    if (!preg_match('/protected\s+static\s+\$family_label\s*=/', $content)) {
        $familyLabel = match ($family) {
            'security' => 'Security',
            'performance' => 'Performance',
            'seo' => 'SEO',
            'design' => 'Design',
            'monitor' => 'Monitoring',
            'code' => 'Code Quality',
            'config' => 'Configuration',
            'system' => 'System',
            default => 'General',
        };
        $props[] = "\n\tprotected static \$family_label = '" . addslashes($familyLabel) . "';";
    }

    $propsBlock = implode("\n", $props) . (empty($props) ? '' : "\n");

    // Build check() method using lean checks per family or wrapping existing run()
    [$severity, $threat] = severity_for_family($family);
    $helperCall = match ($family) {
        'security'    => '\\WPShadow\\Core\\Diagnostic_Lean_Checks::security_basics_issue()',
        'performance' => '\\WPShadow\\Core\\Diagnostic_Lean_Checks::performance_basics_issue()',
        'seo'         => '\\WPShadow\\Core\\Diagnostic_Lean_Checks::seo_basics_issue()',
        'design'      => 'false', // default: informational only; avoid heavy DOM/theme ops here
        'monitor'     => 'false',
        'code'        => '\\WPShadow\\Core\\Diagnostic_Lean_Checks::code_basics_issue()',
        'config'      => '\\WPShadow\\Core\\Diagnostic_Lean_Checks::config_basics_issue()',
        'system'      => 'false',
        default       => 'false',
    };

    $kbSlug = $slug;
    $wrapper = '';
    if (preg_match('/public\s+static\s+function\s+run\s*\(/', $content)) {
        // Provide a check() that wraps existing run() implementation.
        $wrapper = "\n\tpublic static function check(): ?array {\n\t\t$\u0072 = static::run();\n\t\tif (!is_array($\u0072) || empty($\u0072)) {\n\t\t\treturn null;\n\t\t}\n\t\t// If run() did not provide required keys, synthesize a minimal finding.\n\t\tif (!isset($\u0072['id'])) {\n\t\t\treturn \\WPShadow\\Core\\Diagnostic_Lean_Checks::build_finding(\n\t\t\t\t'{$slug}',\n\t\t\t\t'" . addslashes($title) . "',\n\t\t\t\t'" . addslashes($description) . "',\n\t\t\t\t'{$family}',\n\t\t\t\t'{$severity}',\n\t\t\t\t{$threat},\n\t\t\t\t'{$kbSlug}'\n\t\t\t);\n\t\t}\n\t\treturn $\u0072;\n\t}\n";
    }

    $leanCheck = "\n\tpublic static function check(): ?array {\n\t\tif (!({$helperCall})) {\n\t\t\treturn null;\n\t\t}\n\n\t\treturn \\WPShadow\\Core\\Diagnostic_Lean_Checks::build_finding(\n\t\t\t'{$slug}',\n\t\t\t'" . addslashes($title) . "',\n\t\t\t'" . addslashes($description) . "',\n\t\t\t'{$family}',\n\t\t\t'{$severity}',\n\t\t\t{$threat},\n\t\t\t'{$kbSlug}'\n\t\t);\n\t}\n";

    $checkMethod = $wrapper !== '' ? $wrapper : $leanCheck;

    // Insert props at class start and append check() before closing brace
    $before = substr($content, 0, $classOpenPos);
    $after = substr($content, $classOpenPos);

    // Insert props after class opening
    $content = $before . $propsBlock . $after;

    // Append check() before final closing brace of class
    $lastBrace = strrpos($content, '}');
    if ($lastBrace !== false) {
        $content = substr($content, 0, $lastBrace) . $checkMethod . "}\n";
    }

    return $content;
}

function process_file(string $path): ?string {
    $basename = basename($path);
    if ($basename === 'class-diagnostic-registry.php' || $basename === 'README.md') {
        return null;
    }
    $contents = file_get_contents($path);
    if ($contents === false) return null;

    // Derive slug from filename
    $name = preg_replace('/^class-diagnostic-/', '', $basename);
    $name = preg_replace('/\.php$/', '', $name);
    $slug = strtolower($name);

    $title = title_from_slug($slug);
    $family = family_from_slug($slug);
    $desc = sprintf('Automatically initialized lean diagnostic for %s. Optimized for minimal overhead while surfacing high-value signals.', $title);

    // Normalize strict types
    $updated = ensure_strict_types_once($contents);

    // Ensure namespace exists (loose check)
    if (strpos($updated, 'namespace WPShadow\\Diagnostics;') === false) {
        // Insert after declare(strict_types=1);
        $updated = preg_replace('/declare\(strict_types=1\);\n?/', "declare(strict_types=1);\n\nnamespace WPShadow\\Diagnostics;\n\n", $updated, 1);
    }

    // Ensure class extends Diagnostic_Base
    if (!preg_match('/extends\\s+\\\\?WPShadow\\\\Core\\\\Diagnostic_Base|extends\\s+Diagnostic_Base/', $updated)) {
        $updated = preg_replace('/class(\s+[^\s]+\s+)/', 'class$1extends Diagnostic_Base ', $updated, 1);
    }

    // Insert props + check if missing
    $updated = insert_props_and_check($updated, $slug, $title, $desc, $family);

    return ($updated !== $contents) ? $updated : null;
}

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($diagDir));
$changed = 0;
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    if (!preg_match('/class-diagnostic-.*\.php$/', $path)) continue;

    $newContent = process_file($path);
    if ($newContent !== null) {
        file_put_contents($path, $newContent);
        $changed++;
        fwrite(STDOUT, "Updated: {$path}\n");
    }
}

fwrite(STDOUT, "Done. Files updated: {$changed}\n");
