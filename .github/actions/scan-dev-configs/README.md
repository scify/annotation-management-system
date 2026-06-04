# scan-dev-configs

Composite GitHub Action that scans dev tool configuration directories for signs of compromise. Catches malicious payloads and suspicious commands before they reach production.

## What it checks

**Directories scanned:** `.vscode/`, `.claude/`, `.cursor/`, `.idea/`

If none of these directories exist in the repository, the action passes silently.

| Check | What triggers a failure |
|-------|----------------------|
| Executable scripts | Any `.js`, `.mjs`, `.cjs`, `.jsx`, `.ts`, `.tsx`, `.mts`, or `.cts` file exists in a dev tool directory |
| Suspicious commands | Any config file contains execution or network patterns (see list below) |

**Suspicious command patterns:**

`curl`, `wget`, `Invoke-WebRequest`, `iwr`, `bash -c`, `sh -c`, `powershell`, `pwsh`, `certutil`, `base64`, `chmod +x`, `nc`, `ncat`, `socat`, `python -c`, `node -e`, `eval(`, `npx`

These are execution and network patterns that have no legitimate purpose in editor or tool configuration files.

## Why this exists

Supply chain attacks increasingly target dev tool configs as a persistence and execution vector. Malicious payloads are dropped as `.js` files in `.vscode/` or `.claude/` directories, and triggers are injected into `settings.json`, `tasks.json`, or similar config files to execute them. This action catches both the payload and the trigger.

## Setup

### 1. Wire the action into your workflow

**Same-repo usage** (action lives in the consuming repository):

Copy the `.github/actions/scan-dev-configs/` directory into your repo, then reference it as a local action:

```yaml
steps:
  - uses: actions/checkout@34e114876b0b11c390a56381ad16ebd13914f8d5 # v4
  - uses: ./.github/actions/scan-dev-configs
```

**Organisation-wide usage** (action lives in its own repository):

Move the action to a dedicated repo (e.g. `scify/scan-dev-configs`), tag a release, and reference it by version:

```yaml
steps:
  - uses: actions/checkout@34e114876b0b11c390a56381ad16ebd13914f8d5 # v4
  - uses: scify/scan-dev-configs@v1
```

### 2. Placement

Place after `actions/checkout` and before any build or deploy steps. It runs independently of `verify-npm-hardening` and does not require Node.js or npm.

## When the check fails

The error output names the exact file and line that triggered the failure:

- **Executable script found:** remove it from the repository. Dev tool directories should not contain executable code (`.js`, `.mjs`, `.cjs`, `.jsx`, `.ts`, `.tsx`, `.mts`, `.cts`).
- **Suspicious command found:** inspect the config file. Remove entries containing execution or network commands.

If the file is legitimate and expected, reconsider whether it belongs in a dev tool config directory or whether it should live elsewhere in the project.
