import type {
    AnnotationProgressData,
    AnnotationData,
    AnnotationInstance,
    AnnotationItemData,
} from '@/types';

/**
 * Adapts the backend `AnnotationController@show` payload into the view types the
 * annotation page renders. Translation-dependent bits (the sidebar
 * description, the question text/answers) are built in the page where the
 * `useTranslations` hook is available — these helpers stay pure.
 */

/** Total instances across all states — the same sum the backend derives its percentages from. */
export function totalInstances(progress: AnnotationProgressData): number {
    return progress.submitted_count + progress.not_annotated_count + (progress.pending_count ?? 0);
}

/** Map the single backend instance payload into the view shape the page renders. */
export function toInstance(task: AnnotationItemData): AnnotationInstance {
    return {
        id: task.annotator_instance_index,
        index: task.annotator_instance_index,
        focusWord: task.word ?? '',
        leftContext: task.first_corpus_sentence ?? '',
        rightContext: task.second_corpus_sentence ?? '',
        flagged: task.annotationData?.is_flagged ?? false,
        flagThreadId: task.annotationData?.flag_notification_thread_id ?? null,
        isReplied: task.annotationData?.is_replied ?? null,
        isReplyRead: task.annotationData?.is_reply_read ?? null,
    };
}

/** Map progress counts + project chrome into the layout/sidebar payload. */
export function toLayoutData(
    progress: AnnotationProgressData,
    projectName: string,
    subProjectName: string,
    description: string
): AnnotationData {
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
