import { type SubProjectListItemData } from '@/components/sub-project/sub-project-list-item';
import { type SubprojectPriority } from '@/components/sub-project/configuration-step';
import { Tag } from '@/components/ui/tag';
import { useTranslations } from '@/hooks/use-translations';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { AnnotatorsTab } from '@/components/project/annotators-tab';
import { ExportTab } from '@/components/project/export-tab';
import { ManagersTab } from '@/components/project/managers-tab';
import { type ProjectManagerRowData } from '@/components/project/managers-tab';
import { SubprojectsTab } from '@/components/project/subprojects-tab';
import { STATUS_VARIANT, toInitials } from '@/components/project/project-card';
import { type ProjectAnnotatorRowData } from '@/components/annotator/annotators-table';
import { apiFetch } from '@/lib/api';
import { type BreadcrumbItem } from '@/types';
import { formatDate } from '@/utils/format';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

interface BackendProjectData {
    id: number;
    name: string;
    annotation_task_title: string;
    dataset_name: string;
    project_progress: number;
    status: 'pending' | 'in_progress' | 'completed';
}

interface BackendSubprojectData {
    id: number;
    name: string;
    status: 'in_progress' | 'pending' | 'completed';
    priority?: SubprojectPriority;
    scheduled_at: string | null;
    deadline_at: string | null;
    started_at: string | null;
    completed_at: string | null;
    progress: number;
    annotators_count: number;
    first_instance_index: number;
    last_instance_index: number;
    notification_count: number;
}

interface BackendAnnotatorData {
    id: number;
    username: string;
    status: string;
    active_subprojects_of_project_count: number;
    annotator_progress: number;
    workload: number;
    can_flag: boolean;
    can_be_removed: boolean;
}

interface BackendManagerData {
    id: number;
    username: string;
    email: string;
    status: string;
    owner: boolean;
    accepted: boolean;
    request_to_leave: boolean;
    proposed_to_become_owner: boolean;
    can_request_to_leave: boolean;
    can_remove: boolean;
    can_transfer_ownership: boolean;
    can_accept_to_become_owner: boolean;
    can_accept_request_to_leave: boolean;
}

interface Props {
    project_data: BackendProjectData;
    subprojects_data: BackendSubprojectData[];
    annotators_data: BackendAnnotatorData[];
    comanagers_data: BackendManagerData[];
}

type TabKey = 'subprojects' | 'annotators' | 'managers' | 'export';

const DATE_FORMAT: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'short', day: 'numeric' };

export default function ProjectShow({
    project_data,
    subprojects_data,
    annotators_data,
    comanagers_data,
}: Props) {
    const { t } = useTranslations();
    const [activeTab, setActiveTab] = useState<TabKey>('subprojects');
    const [comanagers, setComanagers] = useState(comanagers_data);

    const handleTransferOwnership = async (managerId: number) => {
        const { comanagers_data: updated } = await apiFetch<{
            comanagers_data: BackendManagerData[];
        }>(route('projects.propose-ownership', project_data.id), {
            method: 'POST',
            body: JSON.stringify({ user_id: managerId }),
        });
        setComanagers(updated);
    };

    const handleAcceptOwnership = async () => {
        const { comanagers_data: updated } = await apiFetch<{
            comanagers_data: BackendManagerData[];
        }>(route('projects.accept-ownership', project_data.id), { method: 'POST' });
        setComanagers(updated);
    };

    const handleRejectOwnership = async () => {
        const { comanagers_data: updated } = await apiFetch<{
            comanagers_data: BackendManagerData[];
        }>(route('projects.reject-ownership', project_data.id), { method: 'POST' });
        setComanagers(updated);
    };

    const handleCancelOwnership = async (managerId: number) => {
        const { comanagers_data: updated } = await apiFetch<{
            comanagers_data: BackendManagerData[];
        }>(route('projects.cancel-ownership', project_data.id), {
            method: 'POST',
            body: JSON.stringify({ user_id: managerId }),
        });
        setComanagers(updated);
    };

    const subProjects: SubProjectListItemData[] = subprojects_data.map((sp) => ({
        id: sp.id,
        name: sp.name,
        instancesRange: `${sp.first_instance_index}–${sp.last_instance_index}`,
        dateRange: [
            formatDate(sp.scheduled_at, DATE_FORMAT),
            formatDate(sp.deadline_at, DATE_FORMAT),
        ]
            .filter(Boolean)
            .join(' – '),
        status: STATUS_VARIANT[sp.status],
        statusLabel: t(`projects.status.${sp.status}`),
        subprojectStatus: sp.status,
        priority: sp.priority,
        progress: Math.round(sp.progress * 100),
        annotators: sp.annotators_count,
        notifications: sp.notification_count,
    }));

    const annotators: ProjectAnnotatorRowData[] = annotators_data.map((a) => ({
        id: a.id,
        name: a.username,
        annotator_progress: a.annotator_progress,
        active_subprojects_count: a.active_subprojects_of_project_count,
        workload: a.workload,
        allow_flagging: a.can_flag,
        can_be_removed: a.can_be_removed,
    }));

    const managers: ProjectManagerRowData[] = comanagers.map((m) => ({
        id: m.id,
        initials: toInitials(m.username),
        username: m.username,
        email: m.email,
        role: m.owner ? 'owner' : 'co-manager',
        isActive: m.status === 'active',
        accepted: m.accepted,
        requestToLeave: m.request_to_leave,
        proposedToBecomeOwner: m.proposed_to_become_owner,
        canRequestToLeave: m.can_request_to_leave,
        canRemove: m.can_remove,
        canTransferOwnership: m.can_transfer_ownership,
        canAcceptToBecomeOwner: m.can_accept_to_become_owner,
        canAcceptRequestToLeave: m.can_accept_request_to_leave,
    }));

    const progress = Math.round(project_data.project_progress * 100);

    const tabs: { key: TabKey; label: string; count?: number }[] = [
        {
            key: 'subprojects',
            label: t('projects.show.tab_subprojects'),
            count: subprojects_data.length,
        },
        {
            key: 'annotators',
            label: t('projects.show.tab_annotators'),
            count: annotators_data.length,
        },
        { key: 'managers', label: t('projects.show.tab_managers'), count: comanagers.length },
        { key: 'export', label: t('projects.show.tab_export') },
    ];

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Projects', href: route('projects.index') },
        { title: project_data.name, href: route('projects.show', project_data.id) },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={project_data.name} />
            <div className="flex flex-col gap-4 px-6 py-6">
                {/* Project title */}
                <h1 className="text-slate-800">{project_data.name}</h1>

                {/* Tags */}
                <div className="flex flex-wrap gap-3">
                    <Tag>
                        <strong className="font-bold">{t('projects.show.tag_task')}</strong>
                        <span className="ml-1">{project_data.annotation_task_title}</span>
                    </Tag>
                    <Tag>
                        <strong className="font-bold">{t('projects.show.tag_dataset')}</strong>
                        <span className="ml-1">{project_data.dataset_name}</span>
                    </Tag>
                </div>

                {/* Overall progress bar */}
                <div className="flex flex-col gap-2">
                    <span className="text-sm font-semibold text-slate-800">
                        {t('projects.show.overall_progress')} {progress}%
                    </span>
                    <div className="bg-brand-blue-100 h-3 w-full overflow-hidden rounded-full">
                        <div
                            className="bg-brand-blue-800 h-full rounded-full motion-safe:transition-[width] motion-safe:duration-500 motion-safe:ease-out"
                            style={{ width: `${progress}%` }}
                            role="progressbar"
                            aria-valuenow={progress}
                            aria-valuemin={0}
                            aria-valuemax={100}
                            aria-label={`Overall project progress: ${progress}%`}
                        />
                    </div>
                </div>

                {/* Tab strip */}
                <div
                    className="flex h-[50px] overflow-hidden rounded-lg border border-slate-200 bg-white px-1.5 py-1"
                    role="tablist"
                    aria-label="Project sections"
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
                                    ? 'bg-slate-100 font-semibold text-slate-800'
                                    : 'bg-white font-medium text-slate-500 hover:bg-slate-50'
                            )}
                        >
                            {tab.label}
                            {tab.count !== undefined ? ` (${tab.count})` : ''}
                        </button>
                    ))}
                </div>

                {/* Tab panels */}
                {activeTab === 'subprojects' && (
                    <SubprojectsTab
                        subProjects={subProjects}
                        projectId={project_data.id}
                        onSubprojectCreated={() =>
                            router.visit(route('projects.subprojects.create', project_data.id))
                        }
                    />
                )}
                {activeTab === 'annotators' && (
                    <AnnotatorsTab
                        annotators={annotators}
                        projectId={project_data.id}
                        projectStatus={project_data.status}
                    />
                )}
                {activeTab === 'managers' && (
                    <ManagersTab
                        managers={managers}
                        onTransferOwnership={handleTransferOwnership}
                        onAcceptOwnership={handleAcceptOwnership}
                        onRejectOwnership={handleRejectOwnership}
                        onCancelOwnership={handleCancelOwnership}
                    />
                )}
                {activeTab === 'export' && (
                    <ExportTab projectId={project_data.id} subProjects={subProjects} />
                )}
            </div>
        </AppLayout>
    );
}
