import { SelectAnnotatorsStep } from '@/components/annotator/select-annotators-step';
import { type ProjectAnnotatorRowData } from '@/components/annotator/annotators-table';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/hooks/use-translations';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

const MOCK_ANNOTATORS: ProjectAnnotatorRowData[] = [
    {
        id: 10,
        name: 'Alexis Papadopoulos',
        active_projects_count: 3,
        active_subprojects_count: 5,
        workload: 0.3,
        annotator_progress: 0.6,
        status: 'active',
    },
    {
        id: 11,
        name: 'Maria Stavridou',
        active_projects_count: 8,
        active_subprojects_count: 12,
        workload: 0.85,
        annotator_progress: 0.5,
        status: 'active',
    },
    {
        id: 12,
        name: 'Kostis Nikolaou',
        active_projects_count: 1,
        active_subprojects_count: 2,
        workload: 0.1,
        annotator_progress: 0.2,
        status: 'pending',
    },
    {
        id: 13,
        name: 'Elena Tzimopoulou',
        active_projects_count: 5,
        active_subprojects_count: 9,
        workload: 0.65,
        annotator_progress: 0.8,
        status: 'active',
    },
    {
        id: 14,
        name: 'Panagiotis Dimos',
        active_projects_count: 12,
        active_subprojects_count: 20,
        workload: 0.92,
        annotator_progress: 0.45,
        status: 'active',
    },
];

interface Props {
    project_id: number;
    project_name: string;
    subproject_id: number;
    subproject_name: string;
}

export default function AddAnnotatorsToSubproject({
    project_id,
    project_name,
    subproject_id,
    subproject_name,
}: Props) {
    const { t, trans } = useTranslations();
    const [selectedIds, setSelectedIds] = useState<Set<number>>(new Set());
    const [processing, setProcessing] = useState(false);

    function handleSelectionChange(id: number, checked: boolean) {
        setSelectedIds((prev) => {
            const next = new Set(prev);
            if (checked) next.add(id);
            else next.delete(id);
            return next;
        });
    }

    function handleSelectAllChange(ids: number[], checked: boolean) {
        setSelectedIds((prev) => {
            const next = new Set(prev);
            if (checked) ids.forEach((id) => next.add(id));
            else ids.forEach((id) => next.delete(id));
            return next;
        });
    }

    function handleAdd() {
        setProcessing(true);
        router.post(
            route('projects.subprojects.annotators.attach', [project_id, subproject_id]),
            { annotator_ids: [...selectedIds] },
            { onFinish: () => setProcessing(false) }
        );
    }

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('projects.title'), href: route('projects.index') },
        { title: project_name, href: route('projects.show', project_id) },
        {
            title: subproject_name,
            href: route('projects.subprojects.edit', [project_id, subproject_id]),
        },
        {
            title: t('projects.add_annotators.heading'),
            href: route('projects.subprojects.annotators.add', [project_id, subproject_id]),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('projects.add_annotators.heading')} />
            <div className="flex flex-col gap-6 px-6 py-6">
                {/* Page header */}
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div className="flex flex-col gap-1">
                        <h1 className="text-slate-800">{t('projects.add_annotators.heading')}</h1>
                        <p className="text-sm font-semibold text-slate-600">
                            {trans('projects.add_annotators.subtitle', { name: subproject_name })}
                        </p>
                    </div>
                    <div className="flex items-center gap-3">
                        <span className="text-sm font-semibold text-slate-700">
                            {trans('projects.add_annotators.selected_count', {
                                count: selectedIds.size,
                            })}
                        </span>
                        <Button
                            variant="secondary"
                            size="lg"
                            onPress={() =>
                                router.visit(
                                    route('projects.subprojects.edit', [project_id, subproject_id])
                                )
                            }
                        >
                            {t('sub-projects.add_annotators.back')}
                        </Button>
                        <Button
                            size="lg"
                            isDisabled={selectedIds.size === 0 || processing}
                            onPress={handleAdd}
                        >
                            {t('projects.add_annotators.add_selected')}
                        </Button>
                    </div>
                </div>

                <SelectAnnotatorsStep
                    annotators={MOCK_ANNOTATORS}
                    selectedIds={selectedIds}
                    onSelectionChange={handleSelectionChange}
                    onSelectAllChange={handleSelectAllChange}
                    translationNamespace="projects"
                />
            </div>
        </AppLayout>
    );
}
