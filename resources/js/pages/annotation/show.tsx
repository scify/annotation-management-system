import { AnnotationQuestion } from '@/components/annotation/annotation-question';
import { SendToManagerDialog } from '@/components/annotation/send-to-manager-dialog';
import { ShortcutHint } from '@/components/annotation/shortcut-hint';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTranslations } from '@/hooks/use-translations';
import AnnotationLayout from '@/layouts/annotation-layout';
import { cn } from '@/lib/utils';
import { toInstance, toLayoutData } from '@/pages/annotation/map-annotation-data';
import type {
    AnnotationItemData,
    AnnotationShowProps,
    AnnotationQuestion as Question,
} from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import {
    CheckIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    FlagIcon,
    LogOutIcon,
    UserCogIcon,
} from 'lucide-react';
import { useCallback, useEffect, useMemo, useState } from 'react';

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
 * Initial answer state for the current instance, derived from the backend
 * `annotations` schema: the selected answer is whichever key is `is_selected`,
 * and the confidence parameter comes from `annotationData.confidence`.
 */
function initialAnswers(task?: AnnotationItemData): Record<number, QuestionAnswer> {
    const map = task?.annotationData?.annotations;
    if (!map) return {};
    const selectedKey = Object.keys(map).find((key) => map[key].is_selected) ?? null;
    return {
        [SAME_MEANING_QUESTION_ID]: {
            answer: selectedKey,
            parameter: task?.annotationData?.confidence ?? null,
        },
    };
}

export default function AnnotationPage({
    subProjectId,
    can_navigate,
    can_submit_all_pending,
    projectName,
    subProjectName,
    can_flag,
    nextAnnotationId,
    annotationSessionId,
    annotationProgressData,
    annotationTaskData,
}: AnnotationShowProps) {
    const { t, trans } = useTranslations();

    // Absent when the annotator has nothing left to annotate (nextAnnotationId === null).
    const instance = useMemo(
        () => (annotationTaskData ? toInstance(annotationTaskData) : null),
        [annotationTaskData]
    );

    // Sidebar description: "Meanings of word X:" followed by the numbered senses.
    // Computed inline (cheap) rather than memoised — `trans` is a fresh closure each
    // render, so memoising on it would never hit. The page only re-renders when the
    // backend swaps the instance anyway.
    const senses = annotationTaskData?.senses ?? [];
    const description =
        senses.length === 0
            ? ''
            : [
                  trans('annotation.meanings_of_word', {
                      word: annotationTaskData?.word ?? '',
                  }),
                  ...senses.map((sense, i) => `${i + 1}. ${sense}`),
              ].join('\n');

    // The lexical-semantic task asks a single fixed question. The answer options
    // are driven by the backend `annotations` schema: one option per key, labelled
    // via `annotation.answer_<key>`. Key presence (e.g. `cannot_decide`) is what
    // decides whether an option is offered.
    const annotationsMap = annotationTaskData?.annotationData?.annotations ?? null;
    const questions: Question[] = [];
    if (annotationTaskData?.word && annotationsMap) {
        questions.push({
            id: SAME_MEANING_QUESTION_ID,
            question: trans('annotation.same_meaning_question', {
                word: annotationTaskData.word,
            }),
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
    const [isFlagged, setIsFlagged] = useState(instance?.flagged ?? false);
    const [showShortcuts, setShowShortcuts] = useState(true);
    const [instanceFilter, setInstanceFilter] = useState('not_annotated');
    const [managerDialogOpen, setManagerDialogOpen] = useState(false);

    // Submitting advances to the next instance via an Inertia re-render of this
    // same component, so local answer/flag state would otherwise persist across
    // instances. Re-sync from the incoming backend data whenever it swaps.
    useEffect(() => {
        setAnswers(initialAnswers(annotationTaskData));
        setIsFlagged(instance?.flagged ?? false);
    }, [annotationTaskData, instance]);

    // The "To Manager" dialog is instance-specific: it needs both a loaded
    // instance and the active session id to post.
    const canSendToManager = instance !== null && annotationSessionId != null;

    const getAnswer = (questionId: number): QuestionAnswer => answers[questionId] ?? EMPTY_ANSWER;

    // Submitting requires a selected answer; gates the Submit button and Enter shortcut.
    const hasAnswer = getAnswer(SAME_MEANING_QUESTION_ID).answer != null;

    const updateAnswer = useCallback((questionId: number, patch: Partial<QuestionAnswer>) => {
        setAnswers((prev) => {
            const current = prev[questionId] ?? EMPTY_ANSWER;
            return { ...prev, [questionId]: { ...current, ...patch } };
        });
    }, []);

    // Submit / Next / Previous all round-trip to the server, which owns which
    // instance loads next (and, in a follow-up, persisting the current answer).
    const goToServer = useCallback(
        () => router.visit(route('annotation.show', { subProject: subProjectId })),
        [subProjectId]
    );

    // Persist the current answer + confidence, then let the server hand back the
    // next instance. Builds the `annotations` payload from the schema keys, marking
    // the selected key `is_selected: true`.
    const submitAnnotation = useCallback(() => {
        if (nextAnnotationId == null || annotationSessionId == null || !annotationsMap) return;
        const { answer: selectedKey, parameter: confidence } =
            answers[SAME_MEANING_QUESTION_ID] ?? EMPTY_ANSWER;
        if (selectedKey == null) return; // an answer must be selected to submit
        const annotations = Object.keys(annotationsMap).map((key) => ({
            key,
            is_selected: key === selectedKey,
        }));
        router.post(route('annotation.submit-annotation', { subProject: subProjectId }), {
            annotation_id: nextAnnotationId,
            annotation_session_id: annotationSessionId,
            annotations,
            pending: can_submit_all_pending,
            confidence,
        });
    }, [
        nextAnnotationId,
        annotationSessionId,
        annotationsMap,
        subProjectId,
        answers,
        can_submit_all_pending,
    ]);

    const toggleFlag = useCallback(() => setIsFlagged((flagged) => !flagged), []);

    const flagAction = useCallback(() => {
        toggleFlag();
        if (!can_navigate) goToServer(); // "Flag & Continue"
    }, [toggleFlag, can_navigate, goToServer]);

    const exit = useCallback(() => router.visit(route('dashboard')), []);

    // Keyboard shortcuts (mirrors the hints shown in the panel).
    useEffect(() => {
        const handler = (event: KeyboardEvent) => {
            const target = event.target as HTMLElement | null;
            if (target && ['INPUT', 'TEXTAREA'].includes(target.tagName)) return;

            const key = event.key.toLowerCase();
            if (event.ctrlKey) {
                const level =
                    key === 'h' ? 'high' : key === 'm' ? 'medium' : key === 'l' ? 'low' : null;
                if (level && hasQuestion) {
                    event.preventDefault();
                    updateAnswer(SAME_MEANING_QUESTION_ID, { parameter: level });
                }
                return;
            }

            switch (key) {
                case 'enter':
                    event.preventDefault();
                    submitAnnotation();
                    break;
                case 'f':
                    if (can_flag) flagAction();
                    break;
                case 'e':
                    exit();
                    break;
                case 'n':
                    if (can_navigate) goToServer();
                    break;
                case 'u':
                    if (can_navigate) goToServer();
                    break;
                case 'm':
                    if (canSendToManager) setManagerDialogOpen(true);
                    break;
                default:
                    break;
            }
        };

        window.addEventListener('keydown', handler);
        return () => window.removeEventListener('keydown', handler);
    }, [
        goToServer,
        submitAnnotation,
        flagAction,
        exit,
        can_navigate,
        hasQuestion,
        updateAnswer,
        can_flag,
        canSendToManager,
    ]);

    // Right-aligned "Show Instances" filter, shown in the content's instance row
    // (matches Figma: it sits below the To Manager / Exit header controls).
    const instanceFilterControl = can_navigate && (
        <div className="flex flex-col items-end gap-1">
            <span className="text-sm font-medium text-slate-600">
                {t('annotation.show_instances')}
            </span>
            <Select
                value={instanceFilter}
                onValueChange={setInstanceFilter}
                aria-label={t('annotation.show_instances')}
            >
                <SelectTrigger className="h-9 w-[200px] rounded-lg bg-white text-sm">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="not_annotated">
                        {t('annotation.filter_not_annotated')}
                    </SelectItem>
                    <SelectItem value="pending">{t('annotation.filter_pending')}</SelectItem>
                    <SelectItem value="submitted">{t('annotation.filter_submitted')}</SelectItem>
                    <SelectItem value="all">{t('annotation.filter_all')}</SelectItem>
                </SelectContent>
            </Select>
        </div>
    );

    const headerRight = (
        <>
            <div className="flex flex-col items-center gap-1">
                <button
                    type="button"
                    onClick={() => setManagerDialogOpen(true)}
                    disabled={!canSendToManager}
                    className="bg-brand-blue-700 hover:bg-brand-blue-600 focus-visible:outline-brand-blue-700 flex h-9 cursor-pointer touch-manipulation items-center gap-1.5 rounded-lg px-3 text-sm font-semibold text-white transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <UserCogIcon className="size-4" aria-hidden="true" />
                    {t('annotation.to_manager')}
                </button>
                <ShortcutHint show={showShortcuts} keys="M" />
            </div>
            <div className="flex flex-col items-center gap-1">
                <button
                    type="button"
                    onClick={exit}
                    className="focus-visible:outline-brand-blue-700 flex h-9 touch-manipulation items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                >
                    <LogOutIcon className="size-4" aria-hidden="true" />
                    {t('annotation.exit_annotation')}
                </button>
                <ShortcutHint show={showShortcuts} keys="E" />
            </div>
        </>
    );

    return (
        <AnnotationLayout
            canNavigate={can_navigate}
            canSubmitAllPending={can_submit_all_pending}
            data={layoutData}
            headerRight={headerRight}
        >
            <Head title={t('annotation.title')} />

            {instance && (
                <SendToManagerDialog
                    open={managerDialogOpen}
                    onClose={() => setManagerDialogOpen(false)}
                    subProjectId={subProjectId}
                    subProjectName={subProjectName}
                    instanceIndex={instance.index}
                    annotationSessionId={annotationSessionId}
                />
            )}

            {instance ? (
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
                            <div className="flex flex-col items-start gap-1">
                                <button
                                    type="button"
                                    onClick={flagAction}
                                    disabled={!can_flag || isFlagged || instance.flagged}
                                    aria-pressed={isFlagged}
                                    className={cn(
                                        'flex h-9 touch-manipulation items-center gap-1.5 rounded-lg border px-3 text-sm font-semibold transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-500 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-white',
                                        isFlagged
                                            ? 'border-red-500 bg-red-50 text-red-700'
                                            : 'border-red-200 bg-white text-red-600 hover:bg-red-50'
                                    )}
                                >
                                    <FlagIcon className="size-4" aria-hidden="true" />
                                    {!can_navigate
                                        ? t('annotation.flag_and_continue')
                                        : t('annotation.flag')}
                                </button>
                                <ShortcutHint show={showShortcuts && can_flag} keys="F" />
                            </div>
                        </div>

                        {instanceFilterControl && (
                            <div className="absolute top-0 right-0">{instanceFilterControl}</div>
                        )}

                        <p className="text-base font-medium text-slate-800">
                            {trans('annotation.instance', { index: instance.index })}
                        </p>
                        <span className="bg-brand-yellow-400 rounded-full px-6 py-2 text-2xl font-bold text-slate-800">
                            {instance.focusWord}
                        </span>
                    </div>

                    {/* Context (two columns). The corpus sentences arrive with the focus word
                    wrapped in <b>…</b>; the backend must emit only safe markup here. */}
                    <div className="mb-8 grid gap-4 md:grid-cols-2">
                        <p
                            className="h-[40vh] overflow-y-auto rounded-xl bg-white p-5 text-sm leading-6 text-slate-600"
                            dangerouslySetInnerHTML={{ __html: instance.leftContext }}
                        />
                        <p
                            className="h-[40vh] overflow-y-auto rounded-xl bg-white p-5 text-sm leading-6 text-slate-600"
                            dangerouslySetInnerHTML={{ __html: instance.rightContext }}
                        />
                    </div>

                    {/* Questions (schema-driven) */}
                    <div className="flex flex-col gap-6">
                        {questions.map((question) => {
                            const state = getAnswer(question.id);
                            return (
                                <AnnotationQuestion
                                    key={question.id}
                                    question={question}
                                    answer={state.answer}
                                    parameter={state.parameter}
                                    showShortcuts={showShortcuts}
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

                    {/* Footer navigation */}
                    <div className="flex flex-col gap-3">
                        <div className="flex items-start justify-center gap-3">
                            {can_navigate && (
                                <div className="flex flex-col items-center gap-1">
                                    <button
                                        type="button"
                                        onClick={goToServer}
                                        className="focus-visible:outline-brand-blue-700 flex h-11 touch-manipulation items-center gap-1.5 rounded-full border border-slate-300 bg-white px-5 text-base font-semibold text-slate-600 transition-colors hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                                    >
                                        <ChevronLeftIcon className="size-4" aria-hidden="true" />
                                        {t('annotation.previous')}
                                    </button>
                                    <ShortcutHint show={showShortcuts} keys="U" />
                                </div>
                            )}

                            <div className="flex flex-col items-center gap-1">
                                {instance.submitted ? (
                                    <button
                                        type="button"
                                        disabled
                                        className="flex h-11 min-w-[160px] cursor-not-allowed touch-manipulation items-center justify-center gap-1.5 rounded-full bg-green-600 px-6 text-base font-semibold text-white"
                                    >
                                        {t('annotation.submitted_button')}
                                        <CheckIcon className="size-4" aria-hidden="true" />
                                    </button>
                                ) : (
                                    <button
                                        type="button"
                                        onClick={submitAnnotation}
                                        disabled={!hasAnswer}
                                        className="bg-brand-blue-700 hover:bg-brand-blue-600 focus-visible:outline-brand-blue-700 disabled:hover:bg-brand-blue-700 flex h-11 min-w-[160px] touch-manipulation items-center justify-center gap-1.5 rounded-full px-6 text-base font-semibold text-white transition-colors hover:cursor-pointer focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        {t('annotation.submit')}
                                        <CheckIcon className="size-4" aria-hidden="true" />
                                    </button>
                                )}
                                <ShortcutHint
                                    show={showShortcuts && !instance.submitted && hasAnswer}
                                    keys="Enter"
                                />
                            </div>

                            {can_navigate && (
                                <div className="flex flex-col items-center gap-1">
                                    <button
                                        type="button"
                                        onClick={goToServer}
                                        className="focus-visible:outline-brand-blue-700 flex h-11 touch-manipulation items-center gap-1.5 rounded-full border border-slate-300 bg-white px-5 text-base font-semibold text-slate-600 transition-colors hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                                    >
                                        {t('annotation.next')}
                                        <ChevronRightIcon className="size-4" aria-hidden="true" />
                                    </button>
                                    <ShortcutHint show={showShortcuts} keys="N" />
                                </div>
                            )}
                        </div>

                        <button
                            type="button"
                            onClick={() => setShowShortcuts((prev) => !prev)}
                            className="focus-visible:outline-brand-blue-700 mx-auto rounded-lg px-2 py-1 text-sm font-medium text-slate-500 transition-colors hover:text-slate-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                        >
                            {showShortcuts
                                ? t('annotation.hide_shortcuts')
                                : t('annotation.show_shortcuts')}
                        </button>
                    </div>
                </div>
            ) : (
                <div className="flex min-h-[50vh] w-full items-center justify-center">
                    <p className="text-base font-medium text-slate-500">
                        {t('annotation.no_instances')}
                    </p>
                </div>
            )}
        </AnnotationLayout>
    );
}
