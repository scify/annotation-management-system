import { Input } from '@/components/ui/input';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import { BookSearch, Container, Search } from 'lucide-react';
import { useState } from 'react';

export interface TaskTypeCardData {
    id: number;
    title: string;
    short_description: string;
    description: string | null;
    guidelines_url: string | null;
    tags: Array<{ id: number; name: string }>;
    datasets: Array<{ id: number; name: string; description: string; instances_count: number }>;
    customization_options: Array<{ id: number; question: string; answers: string[] }>;
}

interface TaskTypeCardProps {
    taskType: TaskTypeCardData;
    isSelected: boolean;
    onSelect: () => void;
}

function TaskTypeCard({ taskType, isSelected, onSelect }: Readonly<TaskTypeCardProps>) {
    const { t } = useTranslations();
    const [tab, setTab] = useState(0);

    return (
        // <div role="radio"> instead of <button> so that the dot <button>s inside are valid HTML
        <div
            role="radio"
            aria-checked={isSelected}
            tabIndex={0}
            onClick={onSelect}
            onKeyDown={(e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    onSelect();
                }
            }}
            className={cn(
                'focus-visible:ring-brand-blue-700 relative flex h-[343px] w-full cursor-pointer flex-col rounded-2xl border border-slate-200 px-5 pt-5 pb-3 transition-colors outline-none focus-visible:ring-2',
                isSelected ? 'bg-brand-blue-50' : 'bg-white hover:bg-slate-50'
            )}
        >
            {/* Radio indicator */}
            <div className="flex w-full items-start">
                <span
                    aria-hidden="true"
                    className={cn(
                        'flex size-4 shrink-0 items-center justify-center rounded-full border-2',
                        isSelected ? 'border-brand-blue-700' : 'border-slate-300'
                    )}
                >
                    {isSelected && <span className="bg-brand-blue-700 size-2 rounded-full" />}
                </span>
            </div>

            {/* Guidelines icon button — top-right corner */}
            <a
                href={taskType.guidelines_url ?? '#'}
                target="_blank"
                rel="noopener noreferrer"
                onClick={(e) => e.stopPropagation()}
                aria-label={t('projects.select_task_type.view_guidelines')}
                className="bg-brand-blue-700 hover:bg-brand-blue-800 absolute top-5 right-5 flex size-10 items-center justify-center rounded-lg transition-colors"
            >
                <BookSearch className="size-6 text-white" aria-hidden="true" />
            </a>

            {/* Tab 1 — icon + title + short description */}
            {tab === 0 && (
                <div className="flex min-h-0 flex-1 flex-col gap-3 pt-3">
                    <Container className="size-10 shrink-0 text-slate-400" aria-hidden="true" />
                    <div className="flex flex-col gap-4">
                        <p className="text-base leading-5 font-bold text-slate-800">
                            {taskType.title}
                        </p>
                        <p className="line-clamp-3 text-sm leading-5 text-slate-500">
                            {taskType.short_description}
                        </p>
                    </div>
                </div>
            )}

            {/* Tab 2 — scrollable long description, fills the same space as tab 1 */}
            {tab === 1 && (
                <div className="min-h-0 flex-1 pt-3">
                    <p className="h-full [scrollbar-width:thin] [scrollbar-color:theme(colors.slate.300)_transparent] overflow-y-auto text-sm leading-5 text-slate-600">
                        {taskType.description ?? '—'}
                    </p>
                </div>
            )}

            {/* Footer — tags (always) + dot switchers */}
            <div className="mt-3 flex shrink-0 flex-col gap-1.5">
                <p className="text-right text-xs font-medium text-slate-500">
                    {taskType.tags
                        .slice(0, 3)
                        .map((tag) => `#${tag.name}`)
                        .join(' ')}
                    {taskType.tags.length > 3 && ` +${taskType.tags.length - 3}`}
                </p>
                <div className="flex items-center justify-between">
                    <div
                        role="tablist"
                        aria-label={t('projects.select_task_type.card_pages_label')}
                        className="flex gap-2"
                    >
                        {[0, 1].map((i) => (
                            <button
                                key={i}
                                type="button"
                                role="tab"
                                aria-selected={tab === i}
                                aria-label={`${t('projects.select_task_type.page_label')} ${i + 1}`}
                                onClick={(e) => {
                                    e.stopPropagation();
                                    setTab(i);
                                }}
                                className={cn(
                                    'focus-visible:outline-brand-blue-700 size-3 rounded-full transition-colors hover:cursor-pointer focus-visible:outline-2',
                                    tab === i
                                        ? 'bg-brand-blue-700'
                                        : 'border border-slate-300 bg-transparent'
                                )}
                            />
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
}

interface SelectTaskTypeStepProps {
    taskTypes: TaskTypeCardData[];
    selectedId: number | null;
    onSelectionChange: (id: number) => void;
}

export function SelectTaskTypeStep({
    taskTypes,
    selectedId,
    onSelectionChange,
}: SelectTaskTypeStepProps) {
    const { t } = useTranslations();
    const [searchQuery, setSearchQuery] = useState('');

    const filtered = searchQuery
        ? taskTypes.filter(
              (tt) =>
                  tt.tags.some((tag) =>
                      tag.name.toLowerCase().includes(searchQuery.toLowerCase())
                  ) || tt.title.toLowerCase().includes(searchQuery.toLowerCase())
          )
        : taskTypes;

    return (
        <div className="flex flex-col gap-6">
            <div className="flex items-center justify-between">
                <h2 className="text-xl font-medium text-slate-800">
                    {t('projects.select_task_type.heading')}
                </h2>
                <div className="relative w-72">
                    <Search
                        className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                        aria-hidden="true"
                    />
                    <Input
                        type="search"
                        name="search"
                        placeholder={t('projects.select_task_type.search_placeholder')}
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        className="pl-9"
                        aria-label={t('projects.select_task_type.search_placeholder')}
                    />
                </div>
            </div>

            <div
                role="radiogroup"
                aria-label={t('projects.select_task_type.heading')}
                className="grid grid-cols-3 gap-6"
            >
                {filtered.map((tt) => (
                    <TaskTypeCard
                        key={tt.id}
                        taskType={tt}
                        isSelected={selectedId === tt.id}
                        onSelect={() => onSelectionChange(tt.id)}
                    />
                ))}
            </div>
        </div>
    );
}
