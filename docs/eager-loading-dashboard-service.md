# Eager Loading Analysis: `DashboardService::augmentProjectData`

## Current query count

Every call to `augmentProjectData()` (triggered by `getAllInProgressProjects` or `getMyInProgressProjects`) fires **8 separate queries**:

| # | Where | What |
|---|-------|-------|
| 1 | `getAllInProgressProjects` / `getMyInProgressProjects` | `SELECT` projects (no relationships loaded) |
| 2 | `augmentProjectsWithAnnotationTasks` | `SELECT annotation_tasks WHERE id IN (...)` |
| 3 | `augmentProjectsWithAnnotationTasks` | `SELECT datasets WHERE id IN (...)` |
| 4 | `augmentProjectsWithSubprojects` | `SELECT project_id, COUNT(*) FROM sub_projects WHERE project_id IN (...)` |
| 5 | `augmentProjectsWithAnnotators` | `SELECT project_id, COUNT(*) FROM user_relations WHERE project_id IN (...) AND relation_type = ANNOTATOR_OF_MANAGER` |
| 6 | `augmentProjectsWithManagers` | `SELECT users WHERE id IN (owner_ids)` |
| 7 | `augmentProjectsWithManagers` | `SELECT user_relations WHERE project_id IN (...) AND relation_type = COLLABORATOR_OF_USER` |
| 8 | `augmentProjectsWithManagers` | `SELECT users WHERE id IN (co_manager_user_ids)` |

Queries 2–8 all exist because the Project models are **converted to plain arrays immediately**:

```php
->get()
->map(fn (Project $project) => $project->makeHidden([...])->toArray())
->all();
```

This discards the Eloquent model before any relationships can be used. Each augment method then re-derives IDs from those arrays and fires its own `whereIn`.

---

## Relationships already on `Project`

```php
owner()          BelongsTo<User>          (FK: owner_user_id)
annotationTask() BelongsTo<AnnotationTask> (FK: annotation_task_id)
dataset()        BelongsTo<Dataset>        (FK: dataset_id)
subProjects()    HasMany<SubProject>        (FK: project_id)
```

**Missing — need to be added:**

```php
// app/Models/Project.php
public function annotatorRelations(): HasMany
{
    return $this->hasMany(UserRelation::class, 'project_id')
        ->where('relation_type', UserRelationsEnum::ANNOTATOR_OF_MANAGER);
}

public function coManagerRelations(): HasMany
{
    return $this->hasMany(UserRelation::class, 'project_id')
        ->where('relation_type', UserRelationsEnum::COLLABORATOR_OF_USER);
}

// app/Models/UserRelation.php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class, 'user_id');
}
```

---

## How eager loading eliminates each redundant query

### Queries 2 & 3 — annotation task + dataset

```php
->with([
    'annotationTask:id,title',
    'dataset:id,name',
])
```

Eloquent fires one `SELECT … WHERE id IN (…)` per relationship (two queries total), but these **replace** the identical queries already fired manually. The net effect is zero extra queries. `augmentProjectsWithAnnotationTasks` is reduced to reading from the already-populated array keys `annotation_task` and `dataset` instead of hitting the DB.

### Query 4 — subproject count

```php
->withCount('subProjects')
```

`withCount` does **not** add a new query — it injects a correlated `COUNT(*)` subquery directly into the main `SELECT`, producing a `sub_projects_count` column. `augmentProjectsWithSubprojects` is entirely replaced.

### Query 5 — annotator count

Requires `annotatorRelations()` on Project (see above), then:

```php
->withCount('annotatorRelations as annotators_count')
```

Also a correlated subquery on the main `SELECT` — zero extra queries. `augmentProjectsWithAnnotators` is replaced.

### Queries 6, 7 & 8 — owner + co-managers

Requires `coManagerRelations()` on Project and `user()` on UserRelation (see above), then:

```php
->with([
    'owner:id,username',
    'coManagerRelations.user:id,username',
])
```

This fires:
- 1 query for all owners → replaces query 6
- 1 query for all co-manager `UserRelation` rows → replaces query 7
- 1 query for all co-manager `User` rows (resolved via the nested load) → replaces query 8

Same query count for these three, but the manual `array_column` / `groupBy` / `filter` logic inside `augmentProjectsWithManagers` disappears.

---

## After — target query count

| # | What |
|---|------|
| 1 | `SELECT projects.*, (subquery) AS sub_projects_count, (subquery) AS annotators_count FROM projects …` |
| 2 | `SELECT * FROM annotation_tasks WHERE id IN (…)` |
| 3 | `SELECT * FROM datasets WHERE id IN (…)` |
| 4 | `SELECT * FROM users WHERE id IN (owner_ids)` |
| 5 | `SELECT * FROM user_relations WHERE project_id IN (…) AND relation_type = COLLABORATOR_OF_USER` |
| 6 | `SELECT * FROM users WHERE id IN (co_manager_user_ids)` |

**6 queries** — down from 8. The bigger win is ~60 lines of boilerplate ID-extraction code removed from the augment methods.

---

## Caveat — `select()` column list

The current query uses an explicit `->select([...])` list. Eager loading requires the **foreign key columns** to be included, so Eloquent can match related records. The current list already contains everything needed:

| Relationship | FK needed | Already selected? |
|---|---|---|
| `owner()` | `owner_user_id` | ✓ |
| `annotationTask()` | `annotation_task_id` | ✓ |
| `dataset()` | `dataset_id` | ✓ |
| `subProjects()` / `annotatorRelations()` / `coManagerRelations()` | `id` (project PK) | ✓ |

No changes to the `select()` list are required.

---

## What cannot be eliminated

| Method | Reason |
|---|---|
| `augmentProjectsWithProgress` | No DB call — hardcoded `0.5` placeholder |
| `augmentProjectsWithNotifications` | No DB call — hardcoded `0` placeholder |
| `augmentProjectsWithDateRange` | No DB call — renames `started_at`/`deadline_at` keys |

---

## Files affected

| File | Change |
|---|---|
| `app/Models/Project.php` | Add `annotatorRelations()` and `coManagerRelations()` |
| `app/Models/UserRelation.php` | Add `user()` BelongsTo |
| `app/Services/Dashboard/DashboardService.php` | Add `->with([...])->withCount([...])` to both initial Project queries; refactor `augmentProjectsWithAnnotationTasks`, `augmentProjectsWithSubprojects`, `augmentProjectsWithAnnotators`, `augmentProjectsWithManagers` to read from pre-loaded data |
