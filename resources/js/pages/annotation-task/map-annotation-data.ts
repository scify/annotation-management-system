import type {
    AnnotationProgressData,
    AnnotationTaskData,
    AnnotationTaskInstance,
    AnnotationTaskItemData,
} from '@/types';

/**
 * Adapts the backend `AnnotationController@show` payload into the view types the
 * annotation-task page renders. Translation-dependent bits (the sidebar
 * description, the question text/answers) are built in the page where the
 * `useTranslations` hook is available — these helpers stay pure.
 */

/** Total instances across all states — the same sum the backend derives its percentages from. */
export function totalInstances(progress: AnnotationProgressData): number {
    return progress.submitted_count + progress.not_annotated_count + (progress.pending_count ?? 0);
}

/** Map the single backend instance payload into the view shape the page renders. */
export function toInstance(task: AnnotationTaskItemData): AnnotationTaskInstance {
    return {
        id: task.annotator_instance_index,
        index: task.annotator_instance_index,
        focusWord: task.word ?? '',
        leftContext: task.first_corpus_sentence ?? '',
        rightContext: task.second_corpus_sentence ?? '',
        // TODO(backend): annotationTaskData carries no per-instance flagged flag yet — defaults to false.
        flagged: false,
    };
}

/** Map progress counts + project chrome into the layout/sidebar payload. */
export function toLayoutData(
    progress: AnnotationProgressData,
    projectName: string,
    subProjectName: string,
    description: string
): AnnotationTaskData {
    return {
        projectName,
        subProjectName,
        description,
        progress: {
            submitted: progress.submitted_count,
            thisSession: progress.session_annotations_count,
            pending: progress.pending_count ?? 0,
            notAnnotated: progress.not_annotated_count,
            totalInstances: totalInstances(progress),
            submittedPct: progress.submitted_pct,
        },
        flagged: {
            total: progress.number_of_flagged_instances,
            replied: progress.number_of_replied_flagged_instances,
        },
    };
}
