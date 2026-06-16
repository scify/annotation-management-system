import type { AnnotationTaskData, AnnotationTaskInstance, AnnotationTaskMode } from '@/types';

/**
 * Mocked annotation-task payload.
 *
 * TODO(backend): replace `getMockAnnotationTask` with a real Inertia prop once
 * the annotation API is ready. The shape here matches `AnnotationTaskData`, so
 * the page renders identically whether the data is mocked or live — only this
 * module goes away.
 */

const CONTEXT_PARAGRAPH =
    'Lorem ipsum dolor sit amet, consectetur adipiscing elite purus. Nulla volutpat ' +
    'at ac justo lobortis, eu convallis elit congue. Quisque tincidunt aliquet ' +
    'volutpat. Curabitur ornare vulputate diam, at dictum dui luctus in. Ut velit ' +
    'libero, volutpat at lorem eu, condimentum ultrices velit. Cras et libero a lectus ' +
    'consequat varius. In commodo eu lacus at tristique. Integer quis mauris ac magna ' +
    'vulputate malesuada. Nulla sed elit convallis, mattis tellus a, vulputate quam.';

const FOCUS_WORDS = ['ability', 'capacity', 'faculty', 'aptitude', 'competence'];

function buildInstances(): AnnotationTaskInstance[] {
    return FOCUS_WORDS.map((focusWord, i) => ({
        id: i + 1,
        index: 23 + i,
        focusWord,
        leftContext: CONTEXT_PARAGRAPH,
        rightContext: CONTEXT_PARAGRAPH,
        flagged: false,
    }));
}

export function getMockAnnotationTask(mode: AnnotationTaskMode): AnnotationTaskData {
    const totalInstances = 10_360;
    const submitted = 45;
    const pending = mode === 'flexible' ? 100 : 4;

    return {
        projectName: 'Text classification Batch 34',
        subProjectName: 'New_text annotation_March26',
        description:
            'Meanings of word ability:\n' +
            '1. the quality of being able to perform; a quality that permits or facilitates achievement or accomplishment\n' +
            '2. possession of the qualities (especially mental qualities) required to do something or get something done',
        questions: [
            {
                id: 0,
                question: 'Does the word ability have the same meaning?',
                answers: ['Yes', 'No'],
                parameters: ['low', 'medium', 'high'],
            },
        ],
        instances: buildInstances(),
        progress: {
            submitted,
            thisSession: 4,
            pending,
            notAnnotated: 10_000,
            totalInstances,
            submittedPct: Math.round((submitted / totalInstances) * 100),
        },
        flagged: { total: 23, replied: 12 },
    };
}
