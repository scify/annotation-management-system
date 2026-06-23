import { type ProjectAnnotatorRowData } from '@/components/annotator/annotators-table';
import { SubprojectAnnotatorsPanel } from '@/components/sub-project/subproject-annotators-panel';
import { STATUS_VARIANT } from '@/components/project/project-card';
import {
    PriorityBadge,
    type SubprojectPriority,
    type SubmissionMode,
} from '@/components/sub-project/configuration-step';
import { ToggleSwitch } from '@/components/ui/toggle-switch';
import { type DatasetInfo } from '@/components/sub-project/select-dataset-subset-step';
import {
    AnnotationsTab,
    type InstanceAnnotationRow,
    type UserRole,
} from '@/components/sub-project/annotations-tab';
import {
    DateRangePickerButton,
    formatDateRange,
    type DateRangeValue,
} from '@/components/ui/date-range-picker-button';
import { MakeAnnouncementDialog } from '@/components/make-announcement-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Tag } from '@/components/ui/tag';
import { useTranslations } from '@/hooks/use-translations';
import AppLayout from '@/layouts/app-layout';
import { formatDate } from '@/utils/format';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { CalendarDate } from '@internationalized/date';
import InputError from '@/components/input-error';
import { Head, useForm } from '@inertiajs/react';
import { CircleAlert, LoaderCircle, Megaphone } from 'lucide-react';
import { useState } from 'react';

// ── Backend data shapes ───────────────────────────────────────────────────────

type BackendStatus = 'pending' | 'in_progress' | 'completed';

interface BackendSubprojectData {
    id: number;
    project_id: number;
    name: string;
    status: BackendStatus;
    priority: SubprojectPriority;
    flexible: boolean;
    auto_submission: boolean;
    minimum_annotators: number;
    first_instance_index: number;
    last_instance_index: number;
    scheduled_at: string | null;
    deadline_at: string | null;
    started_at: string | null;
    completed_at: string | null;
    dataset_id: number;
    dataset_name: string;
    progress: number; // 0.0–1.0 fraction
    is_instance_shuffled_per_annotator: boolean;
}

interface BackendAnnotatorData {
    id: number;
    username: string;
    status: 'active' | 'inactive' | 'pending';
    annotator_progress: number;
    workload: number;
    can_flag: boolean;
    flag_count: number;
    can_be_removed: boolean;
}

interface BackendAnnotationEntry {
    id: number;
    annotator_data: { user_id: number; username: string; role: string | null };
    last_edited_by_data: { user_id: number; username: string | null; role: string | null } | null;
    updated_at: string | null;
    confidence: 'high' | 'medium' | 'low' | null;
    status: 'submitted' | 'pending' | 'not_annotated';
}

interface BackendAnnotationRow {
    dataset_instance_id: number;
    annotated: number;
    planned_annotations: number;
    agreement: 'high' | 'medium' | 'low' | 'undefined';
    annotations: BackendAnnotationEntry[];
}

interface Props {
    project_name: string;
    subproject_data: BackendSubprojectData;
    annotators_data: BackendAnnotatorData[];
    annotations_data: BackendAnnotationRow[];
}

// ── Helpers ───────────────────────────────────────────────────────────────────

function parseCalendarDate(s: string | null): CalendarDate | null {
    if (!s) return null;
    // Slice to "YYYY-MM-DD" before parsing — Laravel serializes dates as ISO 8601
    // ("YYYY-MM-DDTHH:mm:ss+00:00") so naive split('-') produces NaN for the day.
    const [y, m, d] = s.slice(0, 10).split('-').map(Number);
    return new CalendarDate(y, m, d);
}

function formatCalendarDate(d: CalendarDate): string {
    return `${d.year}-${String(d.month).padStart(2, '0')}-${String(d.day).padStart(2, '0')}`;
}

// ── Types ─────────────────────────────────────────────────────────────────────

type TabKey = 'overview' | 'annotators' | 'annotations';

interface SubprojectFormData {
    name: string;
    priority: SubprojectPriority;
    is_flexible: boolean;
    requires_confirmation: boolean | null;
    minimum_annotations: number | null;
    scheduled_at: string | null;
    deadline_at: string | null;
    from_instance: number;
    to_instance: number;
    is_instance_shuffled_per_annotator: boolean;
}

// ── Page ──────────────────────────────────────────────────────────────────────

export default function EditSubproject({
    project_name,
    subproject_data,
    annotators_data,
    annotations_data,
}: Props) {
    const { t } = useTranslations();

    // ── Derived display values ────────────────────────────────────────────────
    const statusVariant = STATUS_VARIANT[subproject_data.status];
    const statusLabel = t(`projects.status.${subproject_data.status}`);
    const progressPercent = Math.round(subproject_data.progress * 100);

    const displayDataset: DatasetInfo = {
        name: subproject_data.dataset_name,
        totalInstances: subproject_data.last_instance_index,
    };

    const displayAnnotators: ProjectAnnotatorRowData[] = annotators_data.map((a) => ({
        id: a.id,
        name: a.username,
        status: a.status,
        workload: a.workload,
        annotator_progress: a.annotator_progress,
        annotator_flags: a.flag_count,
        allow_flagging: a.can_flag,
        can_be_removed: subproject_data.status === 'pending' && (a.can_be_removed ?? false),
    }));

    const displayAnnotationRows: InstanceAnnotationRow[] = annotations_data.map((row) => ({
        instanceId: row.dataset_instance_id,
        annotationProgress: { completed: row.annotated, total: row.planned_annotations },
        agreement: row.agreement === 'undefined' ? 'low' : row.agreement,
        annotations: row.annotations.map((ann) => ({
            id: ann.id,
            annotation: '', // ⚠️ not returned by backend yet
            assignedTo: {
                username: ann.annotator_data.username,
                role: (ann.annotator_data.role ?? 'annotator') as UserRole,
            },
            annotatedBy: {
                username: ann.last_edited_by_data?.username ?? ann.annotator_data.username,
                role: (ann.last_edited_by_data?.role ??
                    ann.annotator_data.role ??
                    'annotator') as UserRole,
            },
            timestamp: ann.updated_at ?? '',
            confidence: ann.confidence,
            status: ann.status,
        })),
    }));

    // ── Form state ───────────────────────────────────────────────────────────
    const { data, setData, put, processing, errors } = useForm<SubprojectFormData>({
        name: subproject_data.name,
        priority: subproject_data.priority,
        is_flexible: subproject_data.flexible,
        requires_confirmation: subproject_data.flexible ? !subproject_data.auto_submission : null,
        minimum_annotations: null,
        scheduled_at: subproject_data.scheduled_at?.slice(0, 10) ?? null,
        deadline_at: subproject_data.deadline_at?.slice(0, 10) ?? null,
        from_instance: subproject_data.first_instance_index,
        to_instance: subproject_data.last_instance_index,
        is_instance_shuffled_per_annotator: subproject_data.is_instance_shuffled_per_annotator,
    });

    // UI-only: DateRangePicker needs CalendarDate objects, not strings
    const [dateRange, setDateRange] = useState<DateRangeValue | null>(() => {
        const start = parseCalendarDate(subproject_data.scheduled_at);
        const end = parseCalendarDate(subproject_data.deadline_at);
        return start && end ? { start, end } : null;
    });

    // Derived from requires_confirmation — drives radio button selection
    const submissionMode: SubmissionMode = data.requires_confirmation === true ? 'manual' : 'auto';

    function handleDateRangeChange(value: DateRangeValue | null) {
        setDateRange(value);
        setData((prev) => ({
            ...prev,
            scheduled_at: value?.start ? formatCalendarDate(value.start) : null,
            deadline_at: value?.end ? formatCalendarDate(value.end) : null,
        }));
    }

    function handleFlexibleChange(checked: boolean) {
        setData((prev) => ({
            ...prev,
            is_flexible: checked,
            requires_confirmation: checked ? prev.requires_confirmation : null,
        }));
    }

    // ── Permissions ───────────────────────────────────────────────────────────
    const canManageAnnotators = subproject_data.status === 'pending';
    const canEditSettings = subproject_data.status === 'pending';

    // ── Tabs ──────────────────────────────────────────────────────────────────
    const [activeTab, setActiveTab] = useState<TabKey>('annotations');

    // ── Announcement dialog ─────────────────────────────────────────────────────
    const [announcementOpen, setAnnouncementOpen] = useState(false);

    const tabs: { key: TabKey; label: string }[] = [
        { key: 'annotations', label: t('sub-projects.edit.tab_annotations') },
        { key: 'annotators', label: t('sub-projects.edit.tab_annotators') },
        { key: 'overview', label: t('sub-projects.edit.tab_overview_settings') },
    ];

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('projects.title'), href: route('projects.index') },
        { title: project_name, href: route('projects.show', subproject_data.project_id) },
        { title: subproject_data.name, href: '#' },
    ];

    const scheduledFor = formatDateRange(dateRange);

    function handleSave() {
        put(
            route('projects.subprojects.update', {
                projectId: subproject_data.project_id,
                subprojectId: subproject_data.id,
            }),
            { preserveScroll: true }
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('sub-projects.edit.page_title')} />

            <div className="flex flex-col gap-4 px-6 py-6">
                {/* ── Subproject header ─────────────────────────────────── */}
                <div className="flex flex-col gap-3">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <div className="flex flex-wrap items-center gap-3">
                            <h1 className="text-3xl font-light text-slate-800">{data.name}</h1>
                            <Badge variant={statusVariant}>{statusLabel}</Badge>
                        </div>
                        <Button
                            className="bg-brand-blue-700 hover:bg-brand-blue-800 font-semibold text-white"
                            onClick={() => setAnnouncementOpen(true)}
                        >
                            <Megaphone className="size-4" aria-hidden="true" />
                            {t('sub-projects.annotators_panel.make_announcement')}
                        </Button>
                    </div>

                    {/* Metadata tags */}
                    <div className="flex flex-wrap gap-3">
                        <Tag>
                            <strong className="font-bold">
                                {t('sub-projects.edit.scheduled_for')}
                            </strong>
                            <span className="ml-1">{scheduledFor ?? '—'}</span>
                        </Tag>
                        <Tag>
                            <strong className="font-bold">
                                {t('sub-projects.edit.date_started')}
                            </strong>
                            <span className="ml-1">
                                {subproject_data.started_at
                                    ? formatDate(subproject_data.started_at, {
                                          day: 'numeric',
                                          month: 'short',
                                          year: 'numeric',
                                      })
                                    : t('sub-projects.edit.not_started')}
                            </span>
                        </Tag>
                        <Tag>
                            <strong className="font-bold">
                                {t('sub-projects.edit.date_completed')}
                            </strong>
                            <span className="ml-1">
                                {subproject_data.completed_at
                                    ? formatDate(subproject_data.completed_at, {
                                          day: 'numeric',
                                          month: 'short',
                                          year: 'numeric',
                                      })
                                    : t('sub-projects.edit.not_completed')}
                            </span>
                        </Tag>
                    </div>

                    {/* Overall progress bar */}
                    <div className="flex flex-col gap-2">
                        <span className="text-sm font-semibold text-slate-800">
                            {t('sub-projects.edit.overall_progress')} {progressPercent}%
                        </span>
                        <div className="bg-brand-blue-100 h-3 w-full overflow-hidden rounded-full">
                            <div
                                className="bg-brand-blue-800 h-full rounded-full motion-safe:transition-[width] motion-safe:duration-500 motion-safe:ease-out"
                                style={{ width: `${progressPercent}%` }}
                                role="progressbar"
                                aria-valuenow={progressPercent}
                                aria-valuemin={0}
                                aria-valuemax={100}
                                aria-label={`${t('sub-projects.edit.overall_progress')} ${progressPercent}%`}
                            />
                        </div>
                    </div>
                </div>

                {/* ── Tab strip ─────────────────────────────────────────── */}
                <div
                    className="flex h-[50px] overflow-hidden rounded-lg border border-slate-200 bg-white px-1.5 py-1"
                    role="tablist"
                    aria-label={t('sub-projects.edit.tab_aria_label')}
                >
                    {tabs.map((tab) => (
                        <button
                            key={tab.key}
                            type="button"
                            role="tab"
                            aria-selected={activeTab === tab.key}
                            aria-controls={`tabpanel-${tab.key}`}
                            id={`tab-${tab.key}`}
                            onClick={() => setActiveTab(tab.key)}
                            className={cn(
                                'flex flex-1 cursor-pointer items-center justify-center border-x border-slate-200 px-3 text-sm transition-colors',
                                activeTab === tab.key
                                    ? 'bg-white font-semibold text-slate-800'
                                    : 'bg-slate-50 font-medium text-slate-500 hover:bg-slate-100'
                            )}
                        >
                            {tab.label}
                        </button>
                    ))}
                </div>

                {/* ── Overview & Settings panel ─────────────────────────── */}
                {activeTab === 'overview' && (
                    <section
                        id="tabpanel-overview"
                        role="tabpanel"
                        aria-labelledby="tab-overview"
                        className="flex flex-col gap-5"
                    >
                        {/* Section header */}
                        <div className="flex items-center justify-between">
                            <h2 className="text-xl font-medium text-slate-800">
                                {t('sub-projects.edit.section_overview')}
                            </h2>
                            <Button
                                className="bg-brand-blue-700 hover:bg-brand-blue-800 text-white"
                                onClick={handleSave}
                                disabled={!canEditSettings || processing}
                            >
                                {processing && (
                                    <LoaderCircle
                                        className="size-4 animate-spin"
                                        aria-hidden="true"
                                    />
                                )}
                                {t('sub-projects.edit.save_changes')}
                            </Button>
                        </div>

                        {/* Two-column card layout */}
                        <div className={cn(!canEditSettings && 'pointer-events-none opacity-50')}>
                            <div className="grid grid-cols-2 gap-7">
                                {/* ── Left card ──────────────────────────────── */}
                                <div className="flex flex-col gap-8 rounded-2xl border border-slate-200 bg-white px-11 py-5">
                                    {/* Subproject name */}
                                    <div className="flex flex-col gap-2">
                                        <h3 className="text-lg font-semibold text-slate-800">
                                            {t('sub-projects.edit.name_label')}
                                        </h3>
                                        <Input
                                            type="text"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            aria-label={t('sub-projects.edit.name_label')}
                                            className="h-10 bg-white px-3"
                                        />
                                        {errors.name && <InputError message={errors.name} />}
                                    </div>

                                    {/* Dataset */}
                                    <div className="flex flex-col gap-2">
                                        <h3 className="text-lg font-semibold text-slate-800">
                                            {t('sub-projects.edit.dataset_label')}
                                        </h3>
                                        <div className="flex flex-wrap items-center gap-2">
                                            <Tag>{displayDataset.name}</Tag>
                                            <label
                                                className={cn(
                                                    'flex items-center gap-2',
                                                    canEditSettings
                                                        ? 'cursor-pointer'
                                                        : 'cursor-not-allowed'
                                                )}
                                            >
                                                <Checkbox
                                                    checked={
                                                        data.is_instance_shuffled_per_annotator
                                                    }
                                                    disabled={!canEditSettings}
                                                    onCheckedChange={(v) =>
                                                        setData(
                                                            'is_instance_shuffled_per_annotator',
                                                            v
                                                        )
                                                    }
                                                    aria-label={t(
                                                        'sub-projects.select_dataset.shuffle_on'
                                                    )}
                                                />
                                                <span className="text-sm font-medium text-slate-900">
                                                    {t('sub-projects.select_dataset.shuffle_on')}
                                                </span>
                                            </label>
                                        </div>
                                        <div className="flex gap-5">
                                            <div className="flex flex-1 flex-col gap-1.5">
                                                <label
                                                    htmlFor="edit-from-instance"
                                                    className="px-2.5 text-sm font-semibold text-slate-800"
                                                >
                                                    {t('sub-projects.select_dataset.from_instance')}
                                                </label>
                                                <Input
                                                    id="edit-from-instance"
                                                    type="number"
                                                    inputMode="numeric"
                                                    min={1}
                                                    value={data.from_instance}
                                                    onChange={(e) =>
                                                        setData(
                                                            'from_instance',
                                                            Number(e.target.value)
                                                        )
                                                    }
                                                    className="h-10 bg-white px-2.5"
                                                />
                                                {errors.from_instance && (
                                                    <InputError message={errors.from_instance} />
                                                )}
                                            </div>
                                            <div className="flex flex-1 flex-col gap-1.5">
                                                <label
                                                    htmlFor="edit-to-instance"
                                                    className="px-2.5 text-sm font-semibold text-slate-800"
                                                >
                                                    {t('sub-projects.select_dataset.to_instance')}
                                                </label>
                                                <Input
                                                    id="edit-to-instance"
                                                    type="number"
                                                    inputMode="numeric"
                                                    min={data.from_instance + 1}
                                                    max={displayDataset.totalInstances}
                                                    value={data.to_instance}
                                                    onChange={(e) =>
                                                        setData(
                                                            'to_instance',
                                                            Number(e.target.value)
                                                        )
                                                    }
                                                    className="h-10 bg-white px-2.5"
                                                />
                                                {errors.to_instance && (
                                                    <InputError message={errors.to_instance} />
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    {/* Date range */}
                                    <div className="flex flex-col gap-2">
                                        <h3 className="text-lg font-semibold text-slate-800">
                                            {t('sub-projects.configuration.timeframe_label')}
                                        </h3>
                                        <DateRangePickerButton
                                            value={dateRange}
                                            onChange={handleDateRangeChange}
                                            placeholder={t(
                                                'sub-projects.configuration.timeframe_placeholder'
                                            )}
                                            aria-label={t(
                                                'sub-projects.configuration.timeframe_label'
                                            )}
                                        />
                                        {(errors.scheduled_at || errors.deadline_at) && (
                                            <InputError
                                                message={errors.scheduled_at ?? errors.deadline_at}
                                            />
                                        )}
                                    </div>

                                    {/* Priority */}
                                    <div className="flex flex-col gap-2">
                                        <h3 className="text-lg font-semibold text-slate-800">
                                            {t('sub-projects.configuration.priority_label')}
                                        </h3>
                                        <Select
                                            aria-label={t(
                                                'sub-projects.configuration.priority_label'
                                            )}
                                            value={data.priority}
                                            onValueChange={(v) =>
                                                setData('priority', v as SubprojectPriority)
                                            }
                                        >
                                            <SelectTrigger
                                                aria-label={t(
                                                    'sub-projects.configuration.priority_label'
                                                )}
                                                className="h-10 w-full gap-2 border-slate-200 px-3 hover:cursor-pointer [&>span]:!flex [&>span]:!overflow-visible"
                                            >
                                                {data.priority ? (
                                                    <span className="flex flex-1 items-center gap-2">
                                                        <PriorityBadge
                                                            priority={data.priority}
                                                            size="sm"
                                                        />
                                                        <span className="text-sm font-medium text-slate-800">
                                                            {t(
                                                                `sub-projects.configuration.priority_${data.priority}`
                                                            )}
                                                        </span>
                                                    </span>
                                                ) : (
                                                    <span className="flex flex-1 items-center gap-2">
                                                        <CircleAlert
                                                            className="size-4 text-slate-800"
                                                            aria-hidden="true"
                                                        />
                                                        <SelectValue
                                                            placeholder={t(
                                                                'sub-projects.configuration.priority_placeholder'
                                                            )}
                                                            className="text-sm"
                                                        />
                                                    </span>
                                                )}
                                            </SelectTrigger>
                                            <SelectContent className="w-72">
                                                {(
                                                    [
                                                        'low',
                                                        'medium',
                                                        'high',
                                                    ] as SubprojectPriority[]
                                                ).map((p) => (
                                                    <SelectItem
                                                        key={p}
                                                        value={p}
                                                        textValue={t(
                                                            `sub-projects.configuration.priority_${p}`
                                                        )}
                                                        className="py-2.5 pr-8 pl-3 hover:cursor-pointer"
                                                    >
                                                        <span className="flex items-center gap-3">
                                                            <PriorityBadge priority={p} />
                                                            <span className="text-sm font-medium text-slate-800">
                                                                {t(
                                                                    `sub-projects.configuration.priority_${p}`
                                                                )}
                                                            </span>
                                                        </span>
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>

                                {/* ── Right card ─────────────────────────────── */}
                                <div className="flex flex-col gap-8 rounded-2xl border border-slate-200 bg-white px-11 py-5">
                                    {/* Requirements */}
                                    <div className="flex flex-col gap-2">
                                        <h3 className="text-lg font-semibold text-slate-800">
                                            {t('sub-projects.configuration.requirements_label')}
                                        </h3>

                                        <div
                                            className={cn(
                                                'pointer-events-none',
                                                canEditSettings && 'opacity-50'
                                            )}
                                        >
                                            <ToggleSwitch
                                                id="edit-min-annotations"
                                                checked={false}
                                                onChange={() => {}}
                                                label={t(
                                                    'sub-projects.configuration.min_annotations_label'
                                                )}
                                                description={t(
                                                    'sub-projects.configuration.min_annotations_description'
                                                )}
                                            />
                                        </div>

                                        <p className="text-xs text-slate-400 italic">
                                            {t(
                                                'sub-projects.configuration.min_annotations_coming_soon'
                                            )}
                                        </p>
                                    </div>

                                    {/* Browsing and Submission */}
                                    <div className="flex flex-col gap-5">
                                        <h3 className="text-lg font-semibold text-slate-800">
                                            {t('sub-projects.configuration.browsing_label')}
                                        </h3>
                                        <div>
                                            <ToggleSwitch
                                                id="edit-flexible-browsing"
                                                checked={data.is_flexible}
                                                onChange={handleFlexibleChange}
                                                label={t(
                                                    'sub-projects.configuration.flexible_browsing_label'
                                                )}
                                                description={t(
                                                    'sub-projects.configuration.flexible_browsing_description'
                                                )}
                                            />
                                            <fieldset
                                                className={cn(
                                                    'flex flex-col gap-3 transition-opacity',
                                                    !data.is_flexible &&
                                                        'pointer-events-none opacity-50'
                                                )}
                                                aria-disabled={!data.is_flexible}
                                                disabled={!data.is_flexible}
                                            >
                                                <legend className="sr-only">
                                                    {t('sub-projects.configuration.browsing_label')}
                                                </legend>
                                                {(['auto', 'manual'] as SubmissionMode[]).map(
                                                    (mode) => (
                                                        <label
                                                            key={mode}
                                                            className={cn(
                                                                'flex cursor-pointer items-start gap-3 rounded-xl border p-5 transition-colors',
                                                                submissionMode === mode
                                                                    ? 'border-brand-blue-700 bg-brand-blue-50'
                                                                    : 'border-brand-blue-200 hover:bg-brand-blue-50/50 bg-white'
                                                            )}
                                                        >
                                                            <input
                                                                type="radio"
                                                                name="edit-submission-mode"
                                                                value={mode}
                                                                checked={submissionMode === mode}
                                                                onChange={() =>
                                                                    setData(
                                                                        'requires_confirmation',
                                                                        mode === 'manual'
                                                                    )
                                                                }
                                                                disabled={!data.is_flexible}
                                                                className="accent-brand-blue-700 mt-0.5 size-4 shrink-0"
                                                            />
                                                            <span className="flex flex-col gap-1">
                                                                <span className="text-sm font-medium text-slate-800">
                                                                    {t(
                                                                        `sub-projects.configuration.submission_${mode}`
                                                                    )}
                                                                </span>
                                                                <span className="text-sm text-slate-500">
                                                                    {t(
                                                                        `sub-projects.configuration.submission_${mode}_description`
                                                                    )}
                                                                </span>
                                                            </span>
                                                        </label>
                                                    )
                                                )}
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                )}

                {/* ── Annotators panel ──────────────────────────────────── */}
                {activeTab === 'annotators' && (
                    <section
                        id="tabpanel-annotators"
                        role="tabpanel"
                        aria-labelledby="tab-annotators"
                    >
                        <SubprojectAnnotatorsPanel
                            annotators={displayAnnotators}
                            canManageAnnotators={canManageAnnotators}
                            projectId={subproject_data.project_id}
                            subprojectId={subproject_data.id}
                        />
                    </section>
                )}

                {/* ── Annotations panel ────────────────────────────────── */}
                {activeTab === 'annotations' && (
                    <section
                        id="tabpanel-annotations"
                        role="tabpanel"
                        aria-labelledby="tab-annotations"
                    >
                        <AnnotationsTab annotations={displayAnnotationRows} />
                    </section>
                )}
            </div>

            <MakeAnnouncementDialog
                open={announcementOpen}
                onClose={() => setAnnouncementOpen(false)}
                targetName={data.name}
                projectId={subproject_data.project_id}
                subProjectId={subproject_data.id}
            />
        </AppLayout>
    );
}
