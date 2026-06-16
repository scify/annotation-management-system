import { ShortcutHint } from '@/components/annotation-task/shortcut-hint';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import type { AnnotationTaskQuestion as Question } from '@/types';

/** Confidence levels, in Figma display order (High → Low). */
const CONFIDENCE_ORDER = ['high', 'medium', 'low'] as const;
const CONFIDENCE_SET = new Set<string>(CONFIDENCE_ORDER);

interface AnnotationTaskQuestionProps {
    question: Question;
    /** Selected answer from `question.answers`, or null. */
    answer: string | null;
    /** Selected secondary parameter (e.g. confidence level), or null. */
    parameter: string | null;
    onAnswerChange: (value: string) => void;
    onParameterChange: (value: string | null) => void;
    /** Whether keyboard-shortcut hints are visible (driven by "Hide Shortcuts"). */
    showShortcuts?: boolean;
}

/**
 * Renders one schema-defined question: the prompt, an answer dropdown built from
 * `answers`, and the secondary `parameters` control. A confidence triplet
 * (low/medium/high) renders as a labelled radio pill ("Your Confidence:"); any
 * other parameter set falls back to plain toggle chips. This is the piece that
 * makes the page consume a dynamic schema rather than hard-coded controls.
 */
export function AnnotationTaskQuestion({
    question,
    answer,
    parameter,
    onAnswerChange,
    onParameterChange,
    showShortcuts = true,
}: AnnotationTaskQuestionProps) {
    const { t } = useTranslations();

    const isConfidence =
        question.parameters.length > 0 &&
        question.parameters.every((p) => CONFIDENCE_SET.has(p.toLowerCase()));

    const confidenceLevels = CONFIDENCE_ORDER.filter((level) =>
        question.parameters.some((p) => p.toLowerCase() === level)
    );

    const confidenceLabel = (level: string): string =>
        t(`annotation-task.confidence_${level.toLowerCase()}`);

    const toggleParameter = (value: string) => {
        onParameterChange(parameter === value ? null : value);
    };

    return (
        <div className="flex flex-col items-center gap-5">
            <p className="text-brand-blue-700 text-center text-base font-bold">
                {question.question}
            </p>

            {/* Answer */}
            <div className="flex flex-col items-center gap-1">
                <Select
                    value={answer ?? undefined}
                    onValueChange={onAnswerChange}
                    aria-label={question.question}
                >
                    <SelectTrigger className="h-11 w-[280px] rounded-lg bg-white text-base">
                        <SelectValue placeholder={t('annotation-task.select_an_option')} />
                    </SelectTrigger>
                    <SelectContent>
                        {question.answers.map((option) => (
                            <SelectItem key={option} value={option}>
                                {option}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <ShortcutHint show={showShortcuts} keys="A" />
            </div>

            {/* Confidence (radio pill) */}
            {isConfidence && (
                <div className="bg-brand-blue-100 flex flex-wrap items-center justify-center gap-x-8 gap-y-3 rounded-full px-6 py-4">
                    <span className="text-sm font-semibold text-slate-800">
                        {t('annotation-task.your_confidence')}
                    </span>
                    <RadioGroup
                        value={parameter}
                        onValueChange={onParameterChange}
                        orientation="horizontal"
                        aria-label={t('annotation-task.your_confidence')}
                        className="flex-wrap gap-x-8 gap-y-2"
                    >
                        {confidenceLevels.map((level) => (
                            <div key={level} className="flex flex-col items-start gap-1">
                                <RadioGroupItem value={level}>
                                    {confidenceLabel(level)}
                                </RadioGroupItem>
                                <ShortcutHint
                                    show={showShortcuts}
                                    keys={`Ctrl + ${level.charAt(0).toUpperCase()}`}
                                />
                            </div>
                        ))}
                    </RadioGroup>
                </div>
            )}

            {/* Non-confidence parameters — dynamic-schema fallback */}
            {!isConfidence && question.parameters.length > 0 && (
                <div
                    className="flex flex-wrap justify-center gap-2"
                    role="group"
                    aria-label={question.question}
                >
                    {question.parameters.map((value) => {
                        const selected = parameter === value;

                        return (
                            <button
                                key={value}
                                type="button"
                                onClick={() => toggleParameter(value)}
                                aria-pressed={selected}
                                className={cn(
                                    'focus-visible:outline-brand-blue-700 flex h-9 touch-manipulation items-center justify-center rounded-lg px-4 text-sm font-semibold transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2',
                                    selected
                                        ? 'bg-brand-blue-700 text-white'
                                        : 'bg-brand-blue-50 hover:bg-brand-blue-100 text-slate-600'
                                )}
                            >
                                {value}
                            </button>
                        );
                    })}
                </div>
            )}
        </div>
    );
}
