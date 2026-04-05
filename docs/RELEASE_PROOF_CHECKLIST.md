# Release Proof Checklist

Use this checklist to generate repeatable, runtime-backed release evidence before tagging a build.

## 1. Run the proof script

```bash
./scripts/release-proof.sh
```

Optional container override:

```bash
./scripts/release-proof.sh wpshadow-wordpress
```

## 2. Confirm required checks pass

The script validates:

- PHP lint for core runtime files (`settings-page.php`, `class-settings-registry.php`, `class-hooks-initializer.php`, `dashboard-page-v2.php`)
- Backup vault hardening markers (`index.php`, `.htaccess`, `web.config`)
- Backup index parity (`indexed_count` matches `disk_count`)
- Public docs count alignment between `README.md` and `docs/FEATURES.md` for:
  - shipped diagnostics
  - automated treatments
  - guidance-only treatments

## 3. Save evidence artifact

The script writes an artifact file to:

`artifacts/release-proof/release-proof-<timestamp>.txt`

Attach that file to your release notes, PR, or QA evidence bundle.

## 4. Resolve any failures before release

If the script exits non-zero:

- fix runtime or docs mismatches
- rerun `./scripts/release-proof.sh`
- only ship once all checks are green
