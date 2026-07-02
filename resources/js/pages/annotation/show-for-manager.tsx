import { AnnotationQuestion } from '@/components/annotation/annotation-question';
import { FlagAndContinueDialog } from '@/components/annotation/flag-and-continue-dialog';
import { useTranslations } from '@/hooks/use-translations';
import AnnotationLayout from '@/layouts/annotation-layout';
import { cn } from '@/lib/utils';
import { toInstance, toLayoutData } from '@/pages/annotation/map-annotation-data';
import type {
    AnnotationItemData,
    AnnotationProgressData,
    AnnotationQuestion as Question,
} from '@/types';
import { Head, Link } from '@inertiajs/react';
import { CheckIcon, FlagIcon } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

interface Props {
    subProjectId: number;
    annotationId: number;
    projectName: string;
    subProjectName: string;
    annotationProgressData: AnnotationProgressData;
    annotationTaskData: AnnotationItemData;
}

interface QuestionAnswer {
    answer: string | null;
    parameter: string | null;
}

const EMPTY_ANSWER: QuestionAnswer = { answer: null, parameter: null };

/** Confidence levels offered when the task allows a confidence rating (matches the component). */
const CONFIDENCE_PARAMETERS = ['low', 'medium', 'high'];

/** The lexical-semantic task renders a single question; its id is fixed. */
const SAME_MEANING_QUESTION_ID = 0;

/**
 * Initial answer state for this annotation, derived from the backend `annotations`
 * schema: the selected answer is whichever key is `is_selected`, and the confidence
 * parameter comes from `annotationData.confidence`. A not-started annotation yields
 * no selection; an already-done one pre-fills the annotator's choice. A malformed
 * (array) `annotations` payload is ignored, leaving no selection.
 */
function initialAnswers(task: AnnotationItemData): Record<number, QuestionAnswer> {
    const raw = task.annotationData?.annotations;
    const map = raw && !Array.isArray(raw) ? raw : null;
    if (!map) return {};
    const selectedKey = Object.keys(map).find((key) => map[key].is_selected);
    return {
        [SAME_MEANING_QUESTION_ID]: {
            answer: selectedKey ?? null,
            parameter: task.annotationData?.confidence ?? null,
        },
    };
}

/**
 * Manager view of a single annotation, reached from the sub-project annotations tab
 * ("Go to instance"). A stripped-down `annotation/show`: it keeps the data sidebar,
 * the flag button, and the annotator's pre-selected answer + confidence (editable),
 * but drops navigation/session/to-manager/exit and replaces Submit with **Save**.
 * The Save (and manager flag) POSTs are wired later.
 */
export default function ShowForManager({
    subProjectId,
    projectName,
    subProjectName,
    annotationProgressData,
    annotationTaskData,
}: Props) {
    const { t, trans } = useTranslations();

    const instance = useMemo(() => toInstance(annotationTaskData), [annotationTaskData]);

    // Sidebar description: "Meanings of word X:" followed by the numbered senses.
    const senses = annotationTaskData.senses ?? [];
    const description =
        senses.length === 0
            ? ''
            : [
                  trans('annotation.meanings_of_word', { word: annotationTaskData.word ?? '' }),
                  ...senses.map((sense, i) => `${i + 1}. ${sense}`),
              ].join('\n');

    // The answer options come from the backend `annotations` map. A backend bug can emit
    // it as an array instead of an object; guard against that and hide the question when so.
    const rawAnnotations = annotationTaskData.annotationData?.annotations;
    const annotationsMap = rawAnnotations && !Array.isArray(rawAnnotations) ? rawAnnotations : null;

    const questions: Question[] = [];
    if (annotationTaskData.word && annotationsMap) {
        questions.push({
            id: SAME_MEANING_QUESTION_ID,
            question: trans('annotation.same_meaning_question', { word: annotationTaskData.word }),
            answers: Object.keys(annotationsMap).map((key) => ({
                key,
                label: t(`annotation.answer_${key}`),
            })),
            parameters: annotationTaskData.allow_confidence ? CONFIDENCE_PARAMETERS : [],
        });
    }
    const hasQuestion = questions.length > 0;

    const layoutData = useMemo(
        () => toLayoutData(annotationProgressData, projectName, subProjectName, description),
        [annotationProgressData, projectName, subProjectName, description]
    );

    const [answers, setAnswers] = useState<Record<number, QuestionAnswer>>(() =>
        initialAnswers(annotationTaskData)
    );
    const [isFlagged, setIsFlagged] = useState(instance.flagged);
    const [flagDialogOpen, setFlagDialogOpen] = useState(false);

    // Re-sync local answer/flag state whenever the backend swaps the annotation.
    useEffect(() => {
        setAnswers(initialAnswers(annotationTaskData));
        setIsFlagged(instance.flagged);
    }, [annotationTaskData, instance]);

    const getAnswer = (questionId: number): QuestionAnswer => answers[questionId] ?? EMPTY_ANSWER;

    const updateAnswer = (questionId: number, patch: Partial<QuestionAnswer>) => {
        setAnswers((prev) => {
            const current = prev[questionId] ?? EMPTY_ANSWER;
            return { ...prev, [questionId]: { ...current, ...patch } };
        });
    };

    // Saving requires a selected answer — and, when the task offers a confidence rating,
    // a selected confidence too. Gates the Save button.
    const confidenceRequired = annotationTaskData.allow_confidence ?? false;
    const currentAnswer = getAnswer(SAME_MEANING_QUESTION_ID);
    const canSave =
        hasQuestion &&
        currentAnswer.answer != null &&
        (!confidenceRequired || currentAnswer.parameter != null);

    const save = () => {
        // TODO: POST added later.
    };

    return (
        <AnnotationLayout canNavigate={false} canSubmitAllPending={false} data={layoutData}>
            <Head title={t('annotation.title')} />

            <FlagAndContinueDialog
                open={flagDialogOpen}
                onClose={() => setFlagDialogOpen(false)}
                subProjectId={subProjectId}
                subProjectName={subProjectName}
                instanceIndex={instance.index}
                annotationSessionId={undefined}
                activeFilter="all"
            />

            <div className="mx-auto flex w-full max-w-7xl flex-col gap-6">
                {/* Instance number + focus word, with the flag action floated left */}
                <div className="relative flex flex-col items-center gap-2">
                    <div className="absolute top-0 left-0 flex flex-col items-start gap-2">
                        {isFlagged && (
                            <div className="flex items-center gap-2">
                                <span className="rounded border border-rose-400 bg-rose-100 px-2 py-px text-xs font-semibold text-rose-600">
                                    {t('annotation.flagged')}
                                </span>
                                {instance.isReplied ? (
                                    <span className="flex items-center gap-1.5 text-xs">
                                        {instance.isReplyRead === false && (
                                            <>
                                                <span className="font-bold text-slate-800">
                                                    {t('annotation.replied')}
                                                </span>
                                                <span
                                                    className="size-2 rounded-full bg-rose-500"
                                                    aria-hidden="true"
                                                />
                                            </>
                                        )}
                                        {instance.flagThreadId !== null && (
                                            <Link
                                                href={route('notifications.index', {
                                                    thread: instance.flagThreadId,
                                                })}
                                                className="focus-visible:outline-brand-blue-700 font-medium text-slate-800 underline hover:text-slate-950 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                                            >
                                                {t('annotation.see_reply')}
                                            </Link>
                                        )}
                                    </span>
                                ) : (
                                    <span className="text-xs text-slate-500 italic">
                                        {t('annotation.waiting_for_reply')}
                                    </span>
                                )}
                            </div>
                        )}
                        <button
                            type="button"
                            onClick={() => setFlagDialogOpen(true)}
                            disabled={isFlagged || instance.flagged}
                            aria-pressed={isFlagged}
                            className={cn(
                                'flex h-9 touch-manipulation items-center gap-1.5 rounded-lg border px-3 text-sm font-semibold transition-colors hover:cursor-pointer focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-500 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-white',
                                isFlagged
                                    ? 'border-red-500 bg-red-50 text-red-700'
                                    : 'border-red-200 bg-white text-red-600 hover:bg-red-50'
                            )}
                        >
                            <FlagIcon className="size-4" aria-hidden="true" />
                            {t('annotation.flag_and_continue')}
                        </button>
                    </div>

                    <p className="text-base font-medium text-slate-800">
                        {trans('annotation.instance', { index: instance.index })}
                    </p>
                    <span className="bg-brand-yellow-400 rounded-full px-6 py-2 text-2xl font-bold text-slate-800">
                        {instance.focusWord}
                    </span>
                </div>

                {/* Context (two columns). The corpus sentences arrive with the focus word
                wrapped in <b>…</b>; the backend must emit only safe markup here. */}
                <div className="mb-12 grid gap-4 md:grid-cols-2">
                    <p
                        className="h-[30vh] overflow-y-auto rounded-xl bg-white p-5 text-sm leading-6 text-slate-600"
                        dangerouslySetInnerHTML={{ __html: instance.leftContext }}
                    />
                    <p
                        className="h-[30vh] overflow-y-auto rounded-xl bg-white p-5 text-sm leading-6 text-slate-600"
                        dangerouslySetInnerHTML={{ __html: instance.rightContext }}
                    />
                </div>

                {/* Questions (schema-driven; hidden when the annotations payload is malformed) */}
                {hasQuestion && (
                    <div className="flex flex-col gap-6">
                        {questions.map((question) => {
                            const state = getAnswer(question.id);
                            return (
                                <AnnotationQuestion
                                    key={question.id}
                                    question={question}
                                    answer={state.answer}
                                    parameter={state.parameter}
                                    showShortcuts={false}
                                    onAnswerChange={(value) =>
                                        updateAnswer(question.id, { answer: value })
                                    }
                                    onParameterChange={(value) =>
                                        updateAnswer(question.id, { parameter: value })
                                    }
                                />
                            );
                        })}
                    </div>
                )}

                {/* Save (POST wired later) */}
                <div className="flex justify-center">
                    <button
                        type="button"
                        onClick={save}
                        disabled={!canSave}
                        className="bg-brand-blue-700 hover:bg-brand-blue-600 focus-visible:outline-brand-blue-700 disabled:hover:bg-brand-blue-700 flex h-11 min-w-[160px] touch-manipulation items-center justify-center gap-1.5 rounded-full px-6 text-base font-semibold text-white transition-colors hover:cursor-pointer focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {t('annotation.save')}
                        <CheckIcon className="size-4" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </AnnotationLayout>
    );
}
