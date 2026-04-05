# Public Beta GitHub Checklist

This checklist is tailored to the current WP Shadow repository state as of April 5, 2026.

Use it before making the repository public and again before cutting each beta release.

## Already In Place

- [x] Project overview in [README.md](../README.md)
- [x] Contributor guidance in [../CONTRIBUTING.md](../CONTRIBUTING.md)
- [x] Code of conduct in [../CODE_OF_CONDUCT.md](../CODE_OF_CONDUCT.md)
- [x] Security policy in [../SECURITY.md](../SECURITY.md)
- [x] Support routing in [../SUPPORT.md](../SUPPORT.md)
- [x] Issue templates and PR template in [.github](../.github)
- [x] Release packaging ignore rules in [../.distignore](../.distignore)
- [x] Root license file in [../LICENSE](../LICENSE)
- [x] GitHub Actions CI workflow in [../.github/workflows/ci.yml](../.github/workflows/ci.yml)
- [x] GitHub release packaging workflow in [../.github/workflows/release.yml](../.github/workflows/release.yml)
- [x] Dependabot config in [../.github/dependabot.yml](../.github/dependabot.yml)

## Manual Repo Settings To Finish

- [ ] Enable GitHub private vulnerability reporting in repository settings.
- [ ] Protect the `main` branch.
- [ ] Confirm issue labels match your intended triage workflow.
- [ ] Decide whether blank issues should remain enabled in [.github/ISSUE_TEMPLATE/config.yml](../.github/ISSUE_TEMPLATE/config.yml).
- [ ] Decide whether GitHub Discussions should be enabled or kept off.

## Public-Facing Repo Quality Checks

- [ ] Add at least 2 to 4 screenshots or short demo assets for the dashboard and treatment flows.
- [ ] Pin one issue for beta feedback, scope, and known rough edges.
- [ ] Publish the first tagged GitHub Release with the generated plugin zip attached.
- [ ] Verify README links only point to files that actually exist in the repository.
- [ ] Verify the README beta claims still match [FEATURES.md](FEATURES.md) and the live plugin UI.

## Release-Day Checks

- [ ] Run `bash scripts/prepare-wordpress-org-release.sh` to generate the submission zip and release-content bundle.
- [ ] Tag the release using the `v*` pattern so the packaging workflow runs.
- [ ] Download the generated zip from the GitHub Release and test it in a clean WordPress install.
- [ ] Confirm the plugin header version, [../readme.txt](../readme.txt), and [../CHANGELOG.md](../CHANGELOG.md) are aligned.
- [ ] Confirm the release artifact contains only distributable files.
- [ ] Confirm support, privacy, and security links still resolve.

## First Week After Going Public

- [ ] Triage new issues within a predictable cadence.
- [ ] Capture recurring feedback themes in [NEXT_STEPS.md](NEXT_STEPS.md) or [MILESTONES.md](MILESTONES.md).
- [ ] Tighten the README again based on what outside contributors actually ask.
- [ ] Decide whether more CI checks should be added once PHPCS or PHPStan are part of the committed toolchain.

## Notes For This Repo

- The strongest remaining non-file improvements are repository settings, screenshots, and the first real tagged release.
- The current CI workflow intentionally runs only checks that are supported by the committed Composer toolchain.
- If PHPCS or PHPStan become mandatory gates, add them to Composer first, then extend CI.