import { Input } from '@/components/ui/input';
import { type TaskTypeCardData } from '@/components/project/select-task-type-step';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import { BookSearch, Check, Container, Search } from 'lucide-react';
import { useState } from 'react';

export const MOCK_TASK_TYPES: TaskTypeCardData[] = [
    {
        id: 1,
        title: 'Text Annotation – English Poetry',
        short_description: 'Identify and label poetic devices in English poetry excerpts.',
        description:
            'Annotators read short passages of English poetry and mark instances of metaphor, simile, alliteration, and other rhetorical figures.',
        guidelines_url: null,
        tags: [
            { id: 1, name: 'text' },
            { id: 2, name: 'poetry' },
            { id: 3, name: 'english' },
        ],
        datasets: [
            {
                id: 1,
                name: 'Poetry Corpus',
                description: 'A curated collection of English poetry from 1800–2000.',
                instances_count: 1200,
            },
        ],
        customization_options: [],
    },
    {
        id: 2,
        title: 'Sentiment Analysis – Product Reviews',
        short_description: 'Classify the sentiment of customer product reviews.',
        description:
            'Annotators assign a positive, negative, or neutral label to each product review, and optionally highlight the key sentiment-bearing phrase.',
        guidelines_url: null,
        tags: [
            { id: 4, name: 'sentiment' },
            { id: 5, name: 'reviews' },
            { id: 6, name: 'classification' },
        ],
        datasets: [
            {
                id: 2,
                name: 'E-Commerce Reviews',
                description: 'Product reviews from multiple e-commerce platforms.',
                instances_count: 4500,
            },
        ],
        customization_options: [],
    },
    {
        id: 3,
        title: 'Read Text – Recognise Meaning Changes',
        short_description: 'Detect shifts in word meaning across historical text samples.',
        description:
            'Annotators compare pairs of sentences from different time periods and mark whether the target word has changed in meaning.',
        guidelines_url: null,
        tags: [
            { id: 7, name: 'semantics' },
            { id: 8, name: 'historical' },
            { id: 9, name: 'comparison' },
        ],
        datasets: [
            {
                id: 3,
                name: 'Historical Corpus',
                description: 'Text samples spanning 200 years of written English.',
                instances_count: 800,
            },
        ],
        customization_options: [],
    },
    {
        id: 4,
        title: 'Named Entity Recognition – News',
        short_description: 'Tag persons, organisations, and locations in news articles.',
        description:
            'Annotators highlight spans in news text and assign entity type labels (PERSON, ORG, LOC, MISC) following the CoNLL-2003 scheme.',
        guidelines_url: null,
        tags: [
            { id: 10, name: 'NER' },
            { id: 11, name: 'news' },
            { id: 12, name: 'entities' },
        ],
        datasets: [
            {
                id: 4,
                name: 'News Wire Dataset',
                description: 'English news articles from major wire services.',
                instances_count: 3200,
            },
        ],
        customization_options: [],
    },
    {
        id: 5,
        title: 'Image Classification – Wildlife',
        short_description: 'Assign wildlife species labels to photographs.',
        description:
            'Annotators view wildlife photographs and select the correct species from a predefined taxonomy. Multiple subjects per image are supported.',
        guidelines_url: null,
        tags: [
            { id: 13, name: 'image' },
            { id: 14, name: 'wildlife' },
            { id: 15, name: 'classification' },
        ],
        datasets: [
            {
                id: 5,
                name: 'Wildlife Photo Bank',
                description: 'Camera-trap images from national parks.',
                instances_count: 6700,
            },
        ],
        customization_options: [],
    },
    {
        id: 6,
        title: 'Relation Extraction – Biomedical',
        short_description: 'Mark relationships between biomedical entities in research abstracts.',
        description:
            'Annotators identify pairs of biomedical entities in PubMed abstracts and label the semantic relation between them (e.g. treats, causes, inhibits).',
        guidelines_url: null,
        tags: [
            { id: 16, name: 'biomedical' },
            { id: 17, name: 'relations' },
            { id: 18, name: 'NLP' },
        ],
        datasets: [
            {
                id: 6,
                name: 'PubMed Abstracts',
                description: 'Biomedical research abstracts from PubMed.',
                instances_count: 2100,
            },
        ],
        customization_options: [],
    },
];

interface TaskTypeCardProps {
    taskType: TaskTypeCardData;
    isSelected: boolean;
    onToggle: () => void;
}

function TaskTypeCard({ taskType, isSelected, onToggle }: Readonly<TaskTypeCardProps>) {
    const { t } = useTranslations();
    const [tab, setTab] = useState(0);

    return (
        <div
            role="checkbox"
            aria-checked={isSelected}
            tabIndex={0}
            onClick={onToggle}
            onKeyDown={(e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    onToggle();
                }
            }}
            className={cn(
                'focus-visible:ring-brand-blue-700 relative flex h-[323px] w-full cursor-pointer flex-col rounded-2xl border border-slate-200 px-5 pt-5 pb-3 transition-colors outline-none focus-visible:ring-2',
                isSelected ? 'bg-brand-blue-50' : 'bg-white hover:bg-slate-50'
            )}
        >
            {/* Checkbox indicator */}
            <div className="flex w-full items-start">
                <span
                    aria-hidden="true"
                    className={cn(
                        'flex size-[18px] shrink-0 items-center justify-center rounded border-2',
                        isSelected
                            ? 'border-brand-blue-700 bg-brand-blue-700'
                            : 'border-slate-300 bg-white'
                    )}
                >
                    {isSelected && <Check className="size-3 text-white" strokeWidth={3} />}
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

            {/* Tab 2 — scrollable long description */}
            {tab === 1 && (
                <div className="min-h-0 flex-1 pt-3">
                    <p className="h-full [scrollbar-width:thin] [scrollbar-color:theme(colors.slate.300)_transparent] overflow-y-auto text-sm leading-5 text-slate-600">
                        {taskType.description ?? '—'}
                    </p>
                </div>
            )}

            {/* Footer — tags + dot switchers */}
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

interface TasksAccessStepProps {
    selectedIds: number[];
    onSelectionChange: (ids: number[]) => void;
}

export function TasksAccessStep({ selectedIds, onSelectionChange }: TasksAccessStepProps) {
    const { t } = useTranslations();
    const [searchQuery, setSearchQuery] = useState('');

    const filtered = searchQuery
        ? MOCK_TASK_TYPES.filter(
              (tt) =>
                  tt.tags.some((tag) =>
                      tag.name.toLowerCase().includes(searchQuery.toLowerCase())
                  ) || tt.title.toLowerCase().includes(searchQuery.toLowerCase())
          )
        : MOCK_TASK_TYPES;

    function handleToggle(id: number) {
        onSelectionChange(
            selectedIds.includes(id) ? selectedIds.filter((x) => x !== id) : [...selectedIds, id]
        );
    }

    return (
        <div className="flex flex-col gap-6">
            <div className="flex items-center justify-between">
                <h2 className="text-xl font-medium text-slate-800">
                    {t('users.tasks_access.heading')}
                </h2>
                <div className="relative w-72">
                    <Search
                        className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
                        aria-hidden="true"
                    />
                    <Input
                        type="search"
                        name="task-search"
                        placeholder={t('projects.select_task_type.search_placeholder')}
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        className="pl-9"
                        aria-label={t('projects.select_task_type.search_placeholder')}
                    />
                </div>
            </div>

            <div
                role="group"
                aria-label={t('users.tasks_access.heading')}
                className="grid grid-cols-3 gap-6"
            >
                {filtered.map((tt) => (
                    <TaskTypeCard
                        key={tt.id}
                        taskType={tt}
                        isSelected={selectedIds.includes(tt.id)}
                        onToggle={() => handleToggle(tt.id)}
                    />
                ))}
            </div>
        </div>
    );
}
