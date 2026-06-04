# verify-npm-hardening

Composite GitHub Action that verifies supply chain security settings before `npm ci` runs. Catches missing or misconfigured settings before they reach production.

## What it checks

**Required files:**

| File                | Why                                                        |
|---------------------|------------------------------------------------------------|
| `.npmrc`            | Contains the hardening settings below                      |
| `package-lock.json` | Locks exact dependency versions for deterministic installs |

**Required `.npmrc` settings:**

| Setting               | Purpose                                                                       |
|-----------------------|-------------------------------------------------------------------------------|
| `ignore-scripts=true` | Blocks postinstall hooks (the main vector for npm supply chain attacks)       |
| `engine-strict=true`  | Refuses to install on unsupported Node/npm versions                           |
| `min-release-age=N`   | Quarantines packages published less than N days ago (requires npm >= 11.10.0) |

**Non-registry dependency checks:**

| Check | Purpose |
|-------|---------|
| Git/URL deps in `package.json` | Detects dependencies that bypass `min-release-age` (git, GitHub, HTTP, file sources) |
| Non-registry URLs in `package-lock.json` | Detects tampered lockfiles resolving packages outside the npm registry |

**Recommended (not enforced):**

| Setting           | Purpose                                                       |
|-------------------|---------------------------------------------------------------|
| `save-exact=true` | Pins new dependencies to exact versions instead of `^` ranges |

## Setup

### 1. Add `.npmrc` to your repository

```ini
engine-strict=true
ignore-scripts=true
min-release-age=7
```

Commit this file. Do not `.gitignore` it.

You may also want to add `save-exact=true` to pin new dependencies to exact versions instead of `^` ranges. The action does not enforce this, but it is recommended.

**Common mistake:** `min-release-age` takes a plain integer (days), not a suffixed value. `min-release-age=7` is correct. `min-release-age=7d` silently resolves to `null` and disables the protection entirely.

### 2. Commit your lockfile

`package-lock.json` must be committed to the repository. Without it, `npm ci` cannot perform a deterministic installation and the action will fail.

### 3. Wire the action into your workflow

**Same-repo usage** (action lives in the consuming repository):

Copy the `.github/actions/verify-npm-hardening/` directory into your repo, then reference it as a local action:

```yaml
steps:
  - uses: actions/checkout@34e114876b0b11c390a56381ad16ebd13914f8d5 # v4
  - uses: ./.github/actions/verify-npm-hardening
  - run: npm ci
```

**Organisation-wide usage** (action lives in its own repository):

Move the action to a dedicated repo (e.g. `scify/verify-npm-hardening`), tag a release, and reference it by version:

```yaml
steps:
  - uses: actions/checkout@34e114876b0b11c390a56381ad16ebd13914f8d5 # v4
  - uses: scify/verify-npm-hardening@v1
  - run: npm ci
```

This is the recommended route for adopting the action across multiple repositories. One repo, one source of truth, version-tagged so updates don't break existing workflows.

## Inputs

| Name      | Default | Description                                                       |
|-----------|---------|-------------------------------------------------------------------|
| `min-age` | `7`     | Minimum acceptable `min-release-age` value in days. Must be >= 7. |

```yaml
- uses: ./.github/actions/verify-npm-hardening
  with:
    min-age: 14  # stricter quarantine
```

## When the check fails

The error output names the exact file or setting that is missing or invalid:

- **`.npmrc` missing:** create it with the settings listed above and commit it.
- **`package-lock.json` missing:** run `npm install` locally, commit the lockfile, and push.
- **A setting is missing or invalid:** add or correct it in `.npmrc` and push.

Do not remove or weaken these settings to make the build pass.
