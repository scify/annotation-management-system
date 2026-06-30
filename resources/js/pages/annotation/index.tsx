import { AnnotationTaskQuestion } from '@/components/annotation-task/annotation-task-question';
import { ShortcutHint } from '@/components/annotation-task/shortcut-hint';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTranslations } from '@/hooks/use-translations';
import AnnotationTaskLayout from '@/layouts/annotation-task-layout';
import { cn } from '@/lib/utils';
import { toInstance, toLayoutData } from '@/pages/annotation/map-annotation-data';
import type { AnnotationShowProps, AnnotationTaskQuestion as Question } from '@/types';
import { Head, router } from '@inertiajs/react';
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

export default function AnnotationTaskPage({
    subProjectId,
    mode,
    projectName,
    subProjectName,
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
                  trans('annotation-task.meanings_of_word', {
                      word: annotationTaskData?.word ?? '',
                  }),
                  ...senses.map((sense, i) => `${i + 1}. ${sense}`),
              ].join('\n');

    // The lexical-semantic task asks a single fixed question; the backend only
    // toggles whether confidence / "cannot decide" are offered.
    const questions: Question[] = [];
    if (annotationTaskData?.word) {
        const answers = [t('annotation-task.answer_yes'), t('annotation-task.answer_no')];
        if (annotationTaskData.allow_cannot_decide) {
            answers.push(t('annotation-task.answer_cannot_decide'));
        }
        questions.push({
            id: SAME_MEANING_QUESTION_ID,
            question: trans('annotation-task.same_meaning_question', {
                word: annotationTaskData.word,
            }),
            answers,
            parameters: annotationTaskData.allow_confidence ? CONFIDENCE_PARAMETERS : [],
        });
    }
    const hasQuestion = questions.length > 0;

    const layoutData = useMemo(
        () => toLayoutData(annotationProgressData, projectName, subProjectName, description),
        [annotationProgressData, projectName, subProjectName, description]
    );

    const [answers, setAnswers] = useState<Record<number, QuestionAnswer>>({});
    const [isFlagged, setIsFlagged] = useState(instance?.flagged ?? false);
    const [showShortcuts, setShowShortcuts] = useState(true);
    const [instanceFilter, setInstanceFilter] = useState('not_annotated');

    const getAnswer = (questionId: number): QuestionAnswer => answers[questionId] ?? EMPTY_ANSWER;

    const updateAnswer = useCallback((questionId: number, patch: Partial<QuestionAnswer>) => {
        setAnswers((prev) => {
            const current = prev[questionId] ?? EMPTY_ANSWER;
            return { ...prev, [questionId]: { ...current, ...patch } };
        });
    }, []);

    // Submit / Next / Previous all round-trip to the server, which owns which
    // instance loads next (and, in a follow-up, persisting the current answer).
    const goToServer = useCallback(
        () => router.visit(route('annotation.show', { subProject: subProjectId, mode })),
        [subProjectId, mode]
    );

    const toggleFlag = useCallback(() => setIsFlagged((flagged) => !flagged), []);

    const flagAction = useCallback(() => {
        toggleFlag();
        if (mode === 'strict') goToServer(); // "Flag & Continue"
    }, [toggleFlag, mode, goToServer]);

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
                    goToServer();
                    break;
                case 'f':
                    flagAction();
                    break;
                case 'e':
                    exit();
                    break;
                case 'n':
                    if (mode === 'flexible') goToServer();
                    break;
                case 'u':
                    if (mode === 'flexible') goToServer();
                    break;
                default:
                    break;
            }
        };

        window.addEventListener('keydown', handler);
        return () => window.removeEventListener('keydown', handler);
    }, [goToServer, flagAction, exit, mode, hasQuestion, updateAnswer]);

    const headerRight = (
        <>
            {mode === 'flexible' && (
                <div className="flex items-center gap-2">
                    <span className="text-sm font-medium text-slate-600">
                        {t('annotation-task.show_instances')}
                    </span>
                    <Select
                        value={instanceFilter}
                        onValueChange={setInstanceFilter}
                        aria-label={t('annotation-task.show_instances')}
                    >
                        <SelectTrigger className="h-9 w-[160px] rounded-lg bg-white text-sm">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="not_annotated">
                                {t('annotation-task.filter_not_annotated')}
                            </SelectItem>
                            <SelectItem value="pending">
                                {t('annotation-task.filter_pending')}
                            </SelectItem>
                            <SelectItem value="submitted">
                                {t('annotation-task.filter_submitted')}
                            </SelectItem>
                            <SelectItem value="all">{t('annotation-task.filter_all')}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            )}
            <div className="flex flex-col items-center gap-1">
                <button
                    type="button"
                    className="bg-brand-blue-700 hover:bg-brand-blue-600 focus-visible:outline-brand-blue-700 flex h-9 touch-manipulation items-center gap-1.5 rounded-lg px-3 text-sm font-semibold text-white transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                >
                    <UserCogIcon className="size-4" aria-hidden="true" />
                    {t('annotation-task.to_manager')}
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
                    {t('annotation-task.exit_annotation')}
                </button>
                <ShortcutHint show={showShortcuts} keys="E" />
            </div>
        </>
    );

    return (
        <AnnotationTaskLayout mode={mode} data={layoutData} headerRight={headerRight}>
            <Head title={t('annotation-task.title')} />

            {instance ? (
                <div className="mx-auto flex w-full max-w-3xl flex-col gap-6">
                    {/* Instance number + focus word, with the flag action floated left */}
                    <div className="relative flex flex-col items-center gap-2">
                        <div className="absolute top-0 left-0 flex flex-col items-start gap-1">
                            <button
                                type="button"
                                onClick={flagAction}
                                aria-pressed={isFlagged}
                                className={cn(
                                    'flex h-9 touch-manipulation items-center gap-1.5 rounded-lg border px-3 text-sm font-semibold transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-500',
                                    isFlagged
                                        ? 'border-red-500 bg-red-50 text-red-700'
                                        : 'border-red-200 bg-white text-red-600 hover:bg-red-50'
                                )}
                            >
                                <FlagIcon className="size-4" aria-hidden="true" />
                                {mode === 'strict'
                                    ? t('annotation-task.flag_and_continue')
                                    : t('annotation-task.flag')}
                            </button>
                            <ShortcutHint show={showShortcuts} keys="F" />
                        </div>

                        <p className="text-base font-medium text-slate-800">
                            {trans('annotation-task.instance', { index: instance.index })}
                        </p>
                        <span className="bg-brand-yellow-400 rounded-full px-6 py-2 text-2xl font-bold text-slate-800">
                            {instance.focusWord}
                        </span>
                    </div>

                    {/* Context (two columns). The corpus sentences arrive with the focus word
                    wrapped in <b>…</b>; the backend must emit only safe markup here. */}
                    <div className="grid gap-4 md:grid-cols-2">
                        <p
                            className="rounded-xl bg-white p-5 text-sm leading-6 text-slate-600"
                            dangerouslySetInnerHTML={{ __html: instance.leftContext }}
                        />
                        <p
                            className="rounded-xl bg-white p-5 text-sm leading-6 text-slate-600"
                            dangerouslySetInnerHTML={{ __html: instance.rightContext }}
                        />
                    </div>

                    {/* Questions (schema-driven) */}
                    <div className="flex flex-col gap-6">
                        {questions.map((question) => {
                            const state = getAnswer(question.id);
                            return (
                                <AnnotationTaskQuestion
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
                            {mode === 'flexible' && (
                                <div className="flex flex-col items-center gap-1">
                                    <button
                                        type="button"
                                        onClick={goToServer}
                                        className="focus-visible:outline-brand-blue-700 flex h-11 touch-manipulation items-center gap-1.5 rounded-full border border-slate-300 bg-white px-5 text-base font-semibold text-slate-600 transition-colors hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                                    >
                                        <ChevronLeftIcon className="size-4" aria-hidden="true" />
                                        {t('annotation-task.previous')}
                                    </button>
                                    <ShortcutHint show={showShortcuts} keys="U" />
                                </div>
                            )}

                            <div className="flex flex-col items-center gap-1">
                                <button
                                    type="button"
                                    onClick={goToServer}
                                    className="bg-brand-blue-700 hover:bg-brand-blue-600 focus-visible:outline-brand-blue-700 flex h-11 min-w-[160px] touch-manipulation items-center justify-center gap-1.5 rounded-full px-6 text-base font-semibold text-white transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                                >
                                    {t('annotation-task.submit')}
                                    <CheckIcon className="size-4" aria-hidden="true" />
                                </button>
                                <ShortcutHint show={showShortcuts} keys="Enter" />
                            </div>

                            {mode === 'flexible' && (
                                <div className="flex flex-col items-center gap-1">
                                    <button
                                        type="button"
                                        onClick={goToServer}
                                        className="focus-visible:outline-brand-blue-700 flex h-11 touch-manipulation items-center gap-1.5 rounded-full border border-slate-300 bg-white px-5 text-base font-semibold text-slate-600 transition-colors hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                                    >
                                        {t('annotation-task.next')}
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
                                ? t('annotation-task.hide_shortcuts')
                                : t('annotation-task.show_shortcuts')}
                        </button>
                    </div>
                </div>
            ) : (
                <div className="flex min-h-[50vh] w-full items-center justify-center">
                    <p className="text-base font-medium text-slate-500">
                        {t('annotation-task.no_instances')}
                    </p>
                </div>
            )}
        </AnnotationTaskLayout>
    );
}
