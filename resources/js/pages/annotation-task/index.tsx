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
import { getMockAnnotationTask } from '@/pages/annotation-task/mock-data';
import type { AnnotationTaskMode } from '@/types';
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

interface Props {
    subProjectId: number;
    mode: AnnotationTaskMode;
}

interface QuestionAnswer {
    answer: string | null;
    parameter: string | null;
}

type AnswersByInstance = Record<number, Record<number, QuestionAnswer>>;

const EMPTY_ANSWER: QuestionAnswer = { answer: null, parameter: null };

export default function AnnotationTaskPage({ mode }: Props) {
    const { t, trans } = useTranslations();
    const data = useMemo(() => getMockAnnotationTask(mode), [mode]);

    const [currentIndex, setCurrentIndex] = useState(0);
    const [answers, setAnswers] = useState<AnswersByInstance>({});
    const [flaggedById, setFlaggedById] = useState<Record<number, boolean>>({});
    const [showShortcuts, setShowShortcuts] = useState(true);
    const [instanceFilter, setInstanceFilter] = useState('not_annotated');

    const instance = data.instances[currentIndex];
    const isFlagged = flaggedById[instance.id] ?? instance.flagged;

    const goPrev = useCallback(() => setCurrentIndex((i) => Math.max(i - 1, 0)), []);
    const goNext = useCallback(
        () => setCurrentIndex((i) => Math.min(i + 1, data.instances.length - 1)),
        [data.instances.length]
    );

    const getAnswer = (questionId: number): QuestionAnswer =>
        answers[instance.id]?.[questionId] ?? EMPTY_ANSWER;

    const updateAnswer = (questionId: number, patch: Partial<QuestionAnswer>) => {
        setAnswers((prev) => {
            const forInstance = prev[instance.id] ?? {};
            const current = forInstance[questionId] ?? EMPTY_ANSWER;
            return {
                ...prev,
                [instance.id]: { ...forInstance, [questionId]: { ...current, ...patch } },
            };
        });
    };

    const toggleFlag = useCallback(() => {
        setFlaggedById((prev) => ({
            ...prev,
            [instance.id]: !(prev[instance.id] ?? instance.flagged),
        }));
    }, [instance.id, instance.flagged]);

    const submit = useCallback(() => {
        // Mock: nothing persists; Submit advances to the next instance.
        goNext();
    }, [goNext]);

    const flagAction = useCallback(() => {
        toggleFlag();
        if (mode === 'strict') goNext(); // "Flag & Continue"
    }, [toggleFlag, mode, goNext]);

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
                if (level && data.questions.length > 0) {
                    event.preventDefault();
                    updateAnswer(data.questions[0].id, { parameter: level });
                }
                return;
            }

            switch (key) {
                case 'enter':
                    event.preventDefault();
                    submit();
                    break;
                case 'f':
                    flagAction();
                    break;
                case 'e':
                    exit();
                    break;
                case 'n':
                    if (mode === 'flexible') goNext();
                    break;
                case 'u':
                    if (mode === 'flexible') goPrev();
                    break;
                default:
                    break;
            }
        };

        window.addEventListener('keydown', handler);
        return () => window.removeEventListener('keydown', handler);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [submit, flagAction, exit, goNext, goPrev, mode, data.questions]);

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
        <AnnotationTaskLayout mode={mode} data={data} headerRight={headerRight}>
            <Head title={t('annotation-task.title')} />

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

                {/* Context (two columns) */}
                <div className="grid gap-4 md:grid-cols-2">
                    <p className="rounded-xl bg-white p-5 text-sm leading-6 text-slate-600">
                        {instance.leftContext}
                    </p>
                    <p className="rounded-xl bg-white p-5 text-sm leading-6 text-slate-600">
                        {instance.rightContext}
                    </p>
                </div>

                {/* Questions (schema-driven) */}
                <div className="flex flex-col gap-6">
                    {data.questions.map((question) => {
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
                                    onClick={goPrev}
                                    disabled={currentIndex === 0}
                                    className="focus-visible:outline-brand-blue-700 flex h-11 touch-manipulation items-center gap-1.5 rounded-full border border-slate-300 bg-white px-5 text-base font-semibold text-slate-600 transition-colors hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
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
                                onClick={submit}
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
                                    onClick={goNext}
                                    disabled={currentIndex === data.instances.length - 1}
                                    className="focus-visible:outline-brand-blue-700 flex h-11 touch-manipulation items-center gap-1.5 rounded-full border border-slate-300 bg-white px-5 text-base font-semibold text-slate-600 transition-colors hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
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
        </AnnotationTaskLayout>
    );
}
