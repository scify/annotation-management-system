import { type ProjectAnnotatorRowData } from '@/components/annotator/annotators-table';
import { SelectAnnotatorsStep } from '@/components/annotator/select-annotators-step';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/hooks/use-translations';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Loader2 } from 'lucide-react';
import { useState } from 'react';

interface BackendAnnotatorData {
    id: number;
    username: string;
    status: string;
    active_projects_count: number;
    active_subprojects_count: number;
    annotator_progress: number;
    workload: number;
}

interface Props {
    project_id: number;
    project_name: string;
    all_annotators?: BackendAnnotatorData[];
    my_annotators: BackendAnnotatorData[];
}

function toRowData(a: BackendAnnotatorData): ProjectAnnotatorRowData {
    return {
        id: a.id,
        name: a.username,
        status: a.status as ProjectAnnotatorRowData['status'],
        annotator_progress: a.annotator_progress,
        active_projects_count: a.active_projects_count,
        active_subprojects_count: a.active_subprojects_count,
        workload: a.workload,
    };
}

export default function AddAnnotators({
    project_id,
    project_name,
    all_annotators,
    my_annotators,
}: Props) {
    const { t, trans } = useTranslations();
    const [selectedIds, setSelectedIds] = useState<Set<number>>(new Set());
    const [processing, setProcessing] = useState(false);

    const annotators = (all_annotators ?? my_annotators).map(toRowData);
    const myAnnotators = all_annotators ? my_annotators.map(toRowData) : undefined;

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
            route('projects.annotators.attach', project_id),
            { annotator_ids: [...selectedIds] },
            { onFinish: () => setProcessing(false) }
        );
    }

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('projects.title'), href: route('projects.index') },
        { title: project_name, href: route('projects.show', project_id) },
        {
            title: t('projects.add_annotators.heading'),
            href: route('projects.annotators.add', project_id),
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
                            {trans('projects.add_annotators.subtitle', { name: project_name })}
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
                            onPress={() => router.visit(route('projects.show', project_id))}
                        >
                            {t('projects.add_annotators.back')}
                        </Button>
                        <Button
                            size="lg"
                            isDisabled={selectedIds.size === 0 || processing}
                            onPress={handleAdd}
                        >
                            {processing && <Loader2 className="animate-spin" />}
                            {t('projects.add_annotators.add_selected')}
                        </Button>
                    </div>
                </div>

                <SelectAnnotatorsStep
                    annotators={annotators}
                    myAnnotators={myAnnotators}
                    selectedIds={selectedIds}
                    onSelectionChange={handleSelectionChange}
                    onSelectAllChange={handleSelectAllChange}
                    translationNamespace="projects"
                />
            </div>
        </AppLayout>
    );
}
