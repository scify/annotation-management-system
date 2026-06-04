import { FilterableProjectList } from '@/components/project/filterable-project-list';
import { ProjectListItem } from '@/components/project/project-list-item';
import { Button } from '@/components/ui/button';
import { useAuth } from '@/hooks/use-auth';
import { useTranslations } from '@/hooks/use-translations';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Project } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';

interface Props {
    all_projects?: Project[];
    my_projects: Project[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projects',
        href: '/projects',
    },
];

export default function ProjectsIndex({ all_projects, my_projects }: Props) {
    const { t, trans } = useTranslations();
    const { isAnnotationManager: checkIsAnnotationManager } = useAuth();
    const isAnnotationManager = checkIsAnnotationManager();

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('projects.title')} />
            <div className="flex flex-col gap-6 px-6 py-6">
                {/* Page header */}
                <div className="flex items-center justify-between">
                    <h1 className="text-slate-800">{t('projects.title')}</h1>
                    <Button
                        className="hover:bg-brand-blue-800 bg-brand-blue-700 h-10 font-semibold text-white"
                        onClick={() => router.visit(route('projects.create'))}
                    >
                        <Plus className="size-4" aria-hidden="true" />
                        {t('projects.create_button')}
                    </Button>
                </div>

                <FilterableProjectList
                    projects={all_projects ?? []}
                    myProjects={my_projects}
                    showMineToggle={!isAnnotationManager}
                    mineToggleLabel={(showOnlyMine) =>
                        showOnlyMine
                            ? t('projects.show_only_mine')
                            : t('projects.show_all_projects')
                    }
                    searchPlaceholder={t('projects.search_placeholder')}
                    emptyLabel={t('projects.no_projects')}
                    renderAfterToolbar={(displayedProjects) => (
                        <p className="text-base font-medium text-slate-800">
                            {trans('projects.projects_count', {
                                count: displayedProjects.length,
                            })}
                        </p>
                    )}
                    renderItem={(project) => <ProjectListItem project={project} />}
                />
            </div>
        </AppLayout>
    );
}
