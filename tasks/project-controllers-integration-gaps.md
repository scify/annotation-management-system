# Project / SubProject controllers — frontend-integration gaps

Tracking notes from the AJAX-response refactor audit (Project + SubProject controllers). Captures
endpoints that are **not fully integrated** with the frontend, to revisit later. Companion to
[`notifications-backend-gaps.md`](./notifications-backend-gaps.md).

## Audit result

Every route of both controllers — 20 on `ProjectController`, 9 on `SubProjectController` — is
referenced somewhere in `resources/js`. **No endpoint is fully unwired.** The gaps below are
endpoints that are *referenced* but not *functionally complete*.

## Functional gap to revisit

### `ProjectController::export()` — backend stub
`app/Http/Controllers/ProjectController.php` `export()` is a placeholder:

```php
return response()->streamDownload(
    static function (): void { echo json_encode((object) []); },
    'export.json',
    ['Content-Type' => 'application/json'],
);
```

- It streams an empty `{}` and **ignores both `$id` and the validated `subproject_ids`**.
- `ProjectExportRequest` *does* validate the input (`subproject_ids` required / array / each must
  exist on the project), so the contract is defined — only the implementation is missing.
- The frontend is already wired: `resources/js/components/project/export-tab.tsx` lets the user pick
  subprojects, builds `?subproject_ids[]=…`, and downloads via `window.location.href`. Today the
  user receives an empty file.
- **Action:** implement the real export — read the selected subprojects' annotation data and stream
  it. Then add feature coverage (no test currently exercises a real payload).

## Minor / related

- `resources/js/components/sub-project/annotator-subproject-card.tsx` carries a `TODO(backend)`:
  per-annotator progress counts are placeholder values pending real annotation-progress data.

## Not gaps — intentionally kept as Inertia redirects (context)

These are correctly integrated; listed so we don't mistake them for gaps in a future pass. They use
Inertia's `useForm`/validation/navigation machinery, which a JSON conversion would break or degrade:

- `ProjectController::store`, `SubProjectController::store` — create forms (field validation +
  `created_*_name` flash).
- `SubProjectController::update` — `useForm().put()` with inline `<InputError>` field validation.
- `ProjectController::attachAnnotators`, `SubProjectController::attachAnnotators` — dedicated
  add-annotators pages that navigate to the show/edit page on success.

## Converted to JSON (done — for reference)

`changeStatus`, `detachAnnotator`, `destroy` (both controllers), plus `ProjectController`'s
`toggleCanFlagOfAnnotator`, now return `jsonSuccess`/`jsonError` and are consumed via
`apiFetchWithFlash`. The already-JSON ownership/leave endpoints on `ProjectController`
(`acceptOwnership`, `proposeOwnership`, …) use `response()->json(['comanagers_data' => …])` and the
`jsonError` helper.
