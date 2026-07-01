<?php

declare(strict_types=1);

use App\Enums\AnnotationInstanceFilterEnum;
use App\Enums\AnnotationTaskTypeEnum;
use App\Models\Annotation;
use App\Models\AnnotationAssignment;
use App\Models\AnnotationTask;
use App\Models\Project;
use App\Models\SubProject;
use App\Models\User;
use App\Services\Annotation\AnnotationService;

/*
 * Tests for the instance_filters payload returned by AnnotationService::getAnnotationViewData.
 *
 * instance_filters is included in the response only when can_navigate is true (flexible subproject).
 * Each filter entry carries:
 *   - is_selected:    whether this filter is the active one for the current page render
 *   - can_be_selected: whether the filter has matching annotations (false = greyed-out in the UI)
 *
 * The active filter auto-resolves: if the requested filter has can_be_selected=false
 * (e.g. the user was on "pending" but all pending were submitted), it falls back to "all".
 *
 * The "pending" filter is absent entirely when can_submit_all_pending is false
 * (i.e. subproject uses auto_submission).
 *
 * Base fixture (shared across all groups):
 *   - flexible=true  → can_navigate=true   → instance_filters is present
 *   - auto_submission=false → can_submit_all_pending=true → "pending" filter is included
 *   - DUMMY annotation task → getTaskRelatedData returns [] — no content setup required
 */
describe('AnnotationService — instance_filters', function (): void {
    beforeEach(function (): void {
        $annotationTask = AnnotationTask::factory()->create(['task_type' => AnnotationTaskTypeEnum::DUMMY]);
        $project = Project::factory()->create(['annotation_task_id' => $annotationTask->id]);

        $this->subProject = SubProject::factory()->create([
            'project_id' => $project->id,
            'flexible' => true,        // can_navigate = true
            'auto_submission' => false, // can_submit_all_pending = true
        ]);

        $this->user = User::factory()->create();
        $this->assignment = AnnotationAssignment::factory()->create([
            'user_id' => $this->user->id,
            'sub_project_id' => $this->subProject->id,
        ]);

        $this->service = resolve(AnnotationService::class);
    });

    /*
     * All 10 annotations are submitted (pending=false, annotations!=null).
     * GetNextAnnotationIdQuery returns null → service takes the "no next annotation" fallback path.
     * Counts: submitted=10, pending=0, not_annotated=0.
     *
     * This group tests the basic structure of instance_filters and verifies
     * that filters with zero matching annotations have can_be_selected=false,
     * and that an invalid active_filter auto-falls-back to "all".
     */
    describe('when all 10 annotations are submitted', function (): void {
        beforeEach(function (): void {
            Annotation::factory()->count(10)->create([
                'annotation_assignment_id' => $this->assignment->id,
                'annotations' => ['answer' => 'yes'],
                'pending' => false,
            ]);
        });

        // instance_filters must be present in the response when can_navigate=true.
        it('includes instance_filters in the response', function (): void {
            // Arrange / Act
            $result = $this->service->getAnnotationViewData(
                $this->subProject->id,
                $this->user->id,
                AnnotationInstanceFilterEnum::All,
            );

            // Assert
            expect($result)->toHaveKey('instance_filters');
        });

        // "all" is a catch-all that always has data, so it is always selectable regardless of counts.
        it('all is always can_be_selected', function (): void {
            // Arrange / Act
            $result = $this->service->getAnnotationViewData(
                $this->subProject->id,
                $this->user->id,
                AnnotationInstanceFilterEnum::All,
            );

            // Assert
            expect($result['instance_filters']['all']['can_be_selected'])->toBeTrue();
        });

        // Passing All as the active filter should reflect it as is_selected=true.
        it('selects all when active_filter is all', function (): void {
            // Arrange / Act
            $result = $this->service->getAnnotationViewData(
                $this->subProject->id,
                $this->user->id,
                AnnotationInstanceFilterEnum::All,
            );

            // Assert
            expect($result['instance_filters']['all']['is_selected'])->toBeTrue();
        });

        // pending_count=0 → the "pending" tab would be empty, so can_be_selected must be false.
        it('marks pending as not selectable when no pending annotations exist', function (): void {
            // Arrange / Act
            $result = $this->service->getAnnotationViewData(
                $this->subProject->id,
                $this->user->id,
                AnnotationInstanceFilterEnum::All,
            );

            // Assert
            expect($result['instance_filters']['pending']['can_be_selected'])->toBeFalse();
        });

        // not_annotated_count=0 → same reasoning as pending above.
        it('marks not_annotated as not selectable when no not-annotated annotations exist', function (): void {
            // Arrange / Act
            $result = $this->service->getAnnotationViewData(
                $this->subProject->id,
                $this->user->id,
                AnnotationInstanceFilterEnum::All,
            );

            // Assert
            expect($result['instance_filters']['not_annotated']['can_be_selected'])->toBeFalse();
        });

        // submitted_count=10 → there is data, so the "submitted" tab should be selectable.
        it('marks submitted as selectable when submitted annotations exist', function (): void {
            // Arrange / Act
            $result = $this->service->getAnnotationViewData(
                $this->subProject->id,
                $this->user->id,
                AnnotationInstanceFilterEnum::All,
            );

            // Assert
            expect($result['instance_filters']['submitted']['can_be_selected'])->toBeTrue();
        });

        // The frontend may send active_filter=pending from a previous page state
        // where pending annotations existed. Once all are submitted, pending has
        // can_be_selected=false, so the service must silently fall back to "all".
        it('falls back to all when active_filter is pending but no pending annotations exist', function (): void {
            // Arrange / Act
            $result = $this->service->getAnnotationViewData(
                $this->subProject->id,
                $this->user->id,
                AnnotationInstanceFilterEnum::Pending,
            );

            // Assert
            expect($result['instance_filters']['all']['is_selected'])->toBeTrue()
                ->and($result['instance_filters']['pending']['is_selected'])->toBeFalse();
        });

        // Same fallback logic for not_annotated when not_annotated_count=0.
        it('falls back to all when active_filter is not_annotated but none exist', function (): void {
            // Arrange / Act
            $result = $this->service->getAnnotationViewData(
                $this->subProject->id,
                $this->user->id,
                AnnotationInstanceFilterEnum::NotAnnotated,
            );

            // Assert
            expect($result['instance_filters']['all']['is_selected'])->toBeTrue()
                ->and($result['instance_filters']['not_annotated']['is_selected'])->toBeFalse();
        });

        // submitted_count=10 → Submitted has can_be_selected=true, so it should not fall back.
        it('keeps submitted as selected when active_filter is submitted and submitted annotations exist', function (): void {
            // Arrange / Act
            $result = $this->service->getAnnotationViewData(
                $this->subProject->id,
                $this->user->id,
                AnnotationInstanceFilterEnum::Submitted,
            );

            // Assert
            expect($result['instance_filters']['submitted']['is_selected'])->toBeTrue();
        });
    });

    /*
     * Annotations are spread across all three states: 3 not-annotated, 4 pending, 3 submitted.
     * GetNextAnnotationIdQuery finds the first not-annotated row → service takes the main path
     * (starts a session and returns getDataForShowAnnotation).
     * Counts: not_annotated=3, pending=4, submitted=3 — every filter has data.
     *
     * This group tests that:
     *   - all four filters report can_be_selected=true when counts are non-zero
     *   - a valid active_filter is preserved as-is (no unwanted fallback)
     */
    describe('when 10 annotations are in mixed states (3 not-annotated, 4 pending, 3 submitted)', function (): void {
        beforeEach(function (): void {
            Annotation::factory()->count(3)->sequence(
                fn ($seq): array => ['annotator_instance_index' => $seq->index + 1],
            )->create([
                'annotation_assignment_id' => $this->assignment->id,
                'annotations' => null,
                'pending' => false,
            ]);

            Annotation::factory()->count(4)->sequence(
                fn ($seq): array => ['annotator_instance_index' => $seq->index + 4],
            )->create([
                'annotation_assignment_id' => $this->assignment->id,
                'annotations' => ['answer' => 'yes'],
                'pending' => true,
            ]);

            Annotation::factory()->count(3)->sequence(
                fn ($seq): array => ['annotator_instance_index' => $seq->index + 8],
            )->create([
                'annotation_assignment_id' => $this->assignment->id,
                'annotations' => ['answer' => 'no'],
                'pending' => false,
            ]);
        });

        // Every filter has at least one matching annotation, so all four must report can_be_selected=true.
        it('all filters have can_be_selected=true when all states have at least one annotation', function (): void {
            // Arrange / Act
            $result = $this->service->getAnnotationViewData(
                $this->subProject->id,
                $this->user->id,
                AnnotationInstanceFilterEnum::All,
            );

            // Assert
            expect($result['instance_filters']['all']['can_be_selected'])->toBeTrue()
                ->and($result['instance_filters']['not_annotated']['can_be_selected'])->toBeTrue()
                ->and($result['instance_filters']['pending']['can_be_selected'])->toBeTrue()
                ->and($result['instance_filters']['submitted']['can_be_selected'])->toBeTrue();
        });

        // pending_count=4 → can_be_selected=true → no fallback; active filter must stay as pending.
        it('keeps pending as selected when active_filter is pending and pending annotations exist', function (): void {
            // Arrange / Act
            $result = $this->service->getAnnotationViewData(
                $this->subProject->id,
                $this->user->id,
                AnnotationInstanceFilterEnum::Pending,
            );

            // Assert
            expect($result['instance_filters']['pending']['is_selected'])->toBeTrue()
                ->and($result['instance_filters']['pending']['can_be_selected'])->toBeTrue();
        });

        // not_annotated_count=3 → can_be_selected=true → active filter must stay as not_annotated.
        it('keeps not_annotated as selected when active_filter is not_annotated and not-annotated annotations exist', function (): void {
            // Arrange / Act
            $result = $this->service->getAnnotationViewData(
                $this->subProject->id,
                $this->user->id,
                AnnotationInstanceFilterEnum::NotAnnotated,
            );

            // Assert
            expect($result['instance_filters']['not_annotated']['is_selected'])->toBeTrue()
                ->and($result['instance_filters']['not_annotated']['can_be_selected'])->toBeTrue();
        });
    });

    /*
     * auto_submission=true means the subproject auto-submits annotations on save,
     * so there is never a meaningful "pending" state for the annotator to navigate to.
     * can_submit_all_pending=false → the "pending" filter must be omitted from instance_filters entirely.
     */
    describe('when can_submit_all_pending is false (auto_submission = true)', function (): void {
        beforeEach(function (): void {
            $this->subProject->update(['auto_submission' => true]);

            Annotation::factory()->count(10)->create([
                'annotation_assignment_id' => $this->assignment->id,
                'annotations' => ['answer' => 'yes'],
                'pending' => false,
            ]);
        });

        // The "pending" key must not exist in instance_filters when auto_submission is on.
        it('does not include the pending filter in instance_filters', function (): void {
            // Arrange / Act
            $result = $this->service->getAnnotationViewData(
                $this->subProject->id,
                $this->user->id,
                AnnotationInstanceFilterEnum::All,
            );

            // Assert
            expect($result['instance_filters'])->not->toHaveKey('pending');
        });
    });

    /*
     * flexible=false means the subproject does not support instance navigation.
     * can_navigate=false → instance_filters must be absent from the response entirely,
     * since the UI has no filter controls to render.
     */
    describe('when can_navigate is false (flexible = false)', function (): void {
        beforeEach(function (): void {
            $this->subProject->update(['flexible' => false]);
        });

        // instance_filters must not be present at all when navigation is disabled.
        it('does not include instance_filters in the response', function (): void {
            // Arrange / Act
            $result = $this->service->getAnnotationViewData(
                $this->subProject->id,
                $this->user->id,
                AnnotationInstanceFilterEnum::All,
            );

            // Assert
            expect($result)->not->toHaveKey('instance_filters');
        });
    });
});
