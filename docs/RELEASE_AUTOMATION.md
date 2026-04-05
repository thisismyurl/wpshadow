# Release Automation

Use the release preparation script to build a WordPress.org-ready zip, generate companion release content, and optionally publish the GitHub release.

## Command

```bash
bash scripts/prepare-wordpress-org-release.sh
```

You can also run it through Composer:

```bash
composer release:prepare
```

## What It Does

- reads the version from [../wpshadow.php](../wpshadow.php)
- confirms the version matches the `Stable tag` in [../readme.txt](../readme.txt)
- builds a distributable zip using [../.distignore](../.distignore)
- generates a SHA-256 checksum
- writes release-content templates for:
  - GitHub release notes
  - GitHub discussion announcement
  - GitHub wiki release page
  - WordPress.org submission summary
  - WordPress.org support announcement
- writes a machine-readable manifest for the generated files

## Output

By default the script writes to:

```text
artifacts/releases/<version>/
```

That directory contains:

- `assets/wpshadow-<version>.zip`
- `assets/wpshadow-<version>.zip.sha256`
- `content/*.md`
- `release-manifest.json`

## Optional Flags

Publish the GitHub release after building the assets:

```bash
bash scripts/prepare-wordpress-org-release.sh --publish-github-release
```

Run the existing release-proof checks before packaging:

```bash
bash scripts/prepare-wordpress-org-release.sh --run-release-proof
```

Target a specific repository or ref:

```bash
bash scripts/prepare-wordpress-org-release.sh \
  --repo thisismyurl/wpshadow \
  --target main \
  --publish-github-release
```

## Notes

- GitHub release publishing requires `gh` to be installed and authenticated.
- The script generates discussion and wiki content files, but it does not attempt to post those automatically.
- The zip artifact is suitable for WordPress.org plugin submission review packaging. WordPress.org SVN deployment remains a separate step.