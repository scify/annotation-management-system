import AppLayout from '@/layouts/app-layout';
import { ProjectCard } from '@/components/project/project-card';
import { UserTableCell } from '@/components/project/user-table-cell';
import { SendMessageDialog } from '@/components/send-message-dialog';
import { WorkloadGauge } from '@/components/workload-gauge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useAuth } from '@/hooks/use-auth';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import type { Annotator, BreadcrumbItem, PlatformStats, Project } from '@/types';
import { Head } from '@inertiajs/react';
import { useState } from 'react';

interface Props {
    my_projects: Project[];
    all_projects?: Project[];
    my_annotators: Annotator[];
    all_annotators?: Annotator[];
    platform_stats?: PlatformStats;
}

function StatCard({ title, value, subtitle }: { title: string; value: string; subtitle: string }) {
    return (
        <div className="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4">
            <p className="text-base font-medium text-slate-800">{title}</p>
            <p className="text-brand-blue-700 text-4xl font-light">{value}</p>
            <p className="text-sm font-medium text-slate-500">{subtitle}</p>
        </div>
    );
}

function SectionToggle({
    checked,
    onChange,
    label,
}: {
    checked: boolean;
    onChange: (checked: boolean) => void;
    label: string;
}) {
    return (
        <div className="flex items-center gap-2">
            <button
                role="switch"
                type="button"
                aria-checked={checked}
                onClick={() => onChange(!checked)}
                className={cn(
                    'focus-visible:ring-brand-blue-700 relative h-6 w-11 shrink-0 cursor-pointer rounded-full transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none',
                    checked ? 'bg-brand-blue-700' : 'bg-slate-300'
                )}
            >
                <span
                    className={cn(
                        'absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow-sm motion-safe:transition-transform motion-safe:duration-200',
                        checked ? 'translate-x-5' : 'translate-x-0'
                    )}
                />
            </button>
            <span className="text-sm font-medium text-slate-800">{label}</span>
        </div>
    );
}

export default function Dashboard({
    my_projects,
    all_projects,
    my_annotators,
    all_annotators,
    platform_stats,
}: Props) {
    const { isAdmin: checkIsAdmin } = useAuth();
    const isAdmin = checkIsAdmin();
    const [showOnlyMineProjects, setShowOnlyMineProjects] = useState(false);
    const [showOnlyMineAnnotators, setShowOnlyMineAnnotators] = useState(false);
    const [messageAnnotator, setMessageAnnotator] = useState<Annotator | null>(null);

    const projects = isAdmin && showOnlyMineProjects ? my_projects : (all_projects ?? my_projects);
    const annotators =
        isAdmin && showOnlyMineAnnotators ? my_annotators : (all_annotators ?? my_annotators);

    const { t } = useTranslations();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('navbar.dashboard'),
            href: '/dashboard',
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex flex-col gap-12 px-6 py-6">
                <h1 className="text-slate-800">{t('projects.dashboard.overview_title')}</h1>

                {isAdmin && (
                    <section aria-label={t('projects.dashboard.stats_heading')}>
                        <div className="grid grid-cols-2 gap-4 xl:grid-cols-4">
                            <StatCard
                                title={t('projects.dashboard.stats_total_projects')}
                                value={String(platform_stats?.all_projects ?? 0)}
                                subtitle={t('projects.dashboard.stats_subtitle_system_wide')}
                            />
                            <StatCard
                                title={t('projects.dashboard.stats_total_annotators')}
                                value={String(platform_stats?.all_annotators ?? 0)}
                                subtitle={t('projects.dashboard.stats_subtitle_active_users')}
                            />
                            <StatCard
                                title={t('projects.dashboard.stats_total_managers')}
                                value={String(platform_stats?.all_managers ?? 0)}
                                subtitle={t('projects.dashboard.stats_subtitle_active_users')}
                            />
                            <StatCard
                                title={t('projects.dashboard.stats_total_admins')}
                                value={String(platform_stats?.all_admins ?? 0)}
                                subtitle={t('projects.dashboard.stats_subtitle_active_users')}
                            />
                        </div>
                    </section>
                )}

                <section aria-labelledby="projects-heading">
                    <div className="mb-5 flex items-center gap-9">
                        <h2 id="projects-heading" className="page-subtitle shrink-0">
                            {t('projects.dashboard.active_projects_heading')}
                        </h2>
                        {isAdmin && (
                            <SectionToggle
                                checked={showOnlyMineProjects}
                                onChange={setShowOnlyMineProjects}
                                label={
                                    showOnlyMineProjects
                                        ? t('projects.dashboard.toggle_show_mine_projects')
                                        : t('projects.dashboard.toggle_show_all_projects')
                                }
                            />
                        )}
                    </div>
                    {projects.length === 0 ? (
                        <p className="py-10 text-center text-sm text-slate-400">
                            {t('projects.dashboard.no_projects')}
                        </p>
                    ) : (
                        <div className="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                            {projects.map((project) => (
                                <ProjectCard key={project.id} project={project} />
                            ))}
                        </div>
                    )}
                </section>

                <section aria-labelledby="annotators-heading">
                    <div className="mb-5 flex items-center gap-9">
                        <h2 id="annotators-heading" className="page-subtitle shrink-0">
                            {t('projects.dashboard.annotators_overview_heading')}
                        </h2>
                        {isAdmin && (
                            <SectionToggle
                                checked={showOnlyMineAnnotators}
                                onChange={setShowOnlyMineAnnotators}
                                label={
                                    showOnlyMineAnnotators
                                        ? t('projects.dashboard.toggle_show_mine_annotators')
                                        : t('projects.dashboard.toggle_show_all_annotators')
                                }
                            />
                        )}
                    </div>
                    <div className="overflow-hidden rounded-xl">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-brand-blue-100 hover:bg-brand-blue-100 border-b border-slate-300">
                                    <TableHead className="pl-4 text-sm font-semibold text-slate-800">
                                        {t('projects.dashboard.table_username')}
                                    </TableHead>
                                    <TableHead className="text-right text-sm font-semibold text-slate-800">
                                        {t('projects.dashboard.table_active_projects')}
                                    </TableHead>
                                    <TableHead className="text-right text-sm font-semibold text-slate-800">
                                        {t('projects.dashboard.table_subprojects')}
                                    </TableHead>
                                    <TableHead className="text-center text-sm font-semibold text-slate-800">
                                        {t('projects.dashboard.table_remaining_workload')}
                                    </TableHead>
                                    <TableHead className="text-center text-sm font-semibold text-slate-800">
                                        {t('projects.dashboard.table_progress')}
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {annotators.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={5}
                                            className="py-10 text-center text-sm text-slate-400"
                                        >
                                            {t('projects.dashboard.no_annotators')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    annotators.map((annotator) => {
                                        const workloadPct = Math.round(annotator.workload * 100);
                                        const progressPct = Math.round(
                                            annotator.annotator_progress * 100
                                        );
                                        const initial = annotator.name.charAt(0).toUpperCase();
                                        return (
                                            <TableRow key={annotator.id}>
                                                <TableCell className="pl-4">
                                                    <UserTableCell
                                                        initials={initial}
                                                        username={annotator.name}
                                                        onMessage={() =>
                                                            setMessageAnnotator(annotator)
                                                        }
                                                    />
                                                </TableCell>
                                                <TableCell className="text-right text-sm text-slate-800">
                                                    {annotator.active_projects_count}
                                                </TableCell>
                                                <TableCell className="text-right text-sm text-slate-800">
                                                    {annotator.active_subprojects_count}
                                                </TableCell>
                                                <TableCell className="px-6">
                                                    <div className="flex justify-center">
                                                        <WorkloadGauge value={workloadPct} />
                                                    </div>
                                                </TableCell>
                                                <TableCell className="px-6">
                                                    <div className="flex flex-col gap-1">
                                                        <span className="text-right text-xs font-semibold text-slate-800 tabular-nums">
                                                            {progressPct}%
                                                        </span>
                                                        <div className="bg-brand-blue-100 h-[5px] w-full overflow-hidden rounded-full">
                                                            <div
                                                                className="bg-brand-blue-800 h-full rounded-full motion-safe:transition-[width] motion-safe:duration-500 motion-safe:ease-out"
                                                                style={{ width: `${progressPct}%` }}
                                                                role="progressbar"
                                                                aria-valuenow={progressPct}
                                                                aria-valuemin={0}
                                                                aria-valuemax={100}
                                                                aria-label={`Progress: ${progressPct}%`}
                                                            />
                                                        </div>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        );
                                    })
                                )}
                            </TableBody>
                        </Table>
                    </div>
                </section>
            </div>

            <SendMessageDialog
                open={messageAnnotator !== null}
                onClose={() => setMessageAnnotator(null)}
                targetName={messageAnnotator?.name ?? ''}
                recipientUserId={messageAnnotator?.id ?? 0}
            />
        </AppLayout>
    );
}
