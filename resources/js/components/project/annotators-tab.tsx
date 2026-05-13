import { AnnotatorsTable } from '@/components/annotator/annotators-table';
import { type ProjectAnnotatorRowData } from '@/components/annotator/annotators-table';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/hooks/use-translations';
import { Plus } from 'lucide-react';

const MOCK_ANNOTATORS: ProjectAnnotatorRowData[] = [
    {
        id: 1,
        name: 'George Giannakopoulos',
        active_projects_count: 23,
        active_subprojects_count: 23,
        workload: 0.85,
        annotator_progress: 0.75,
    },
    {
        id: 2,
        name: 'Fotini Papastergiou',
        active_projects_count: 23,
        active_subprojects_count: 2,
        workload: 0.2,
        annotator_progress: 0.75,
    },
    {
        id: 3,
        name: 'Nelly Savrani',
        active_projects_count: 23,
        active_subprojects_count: 7,
        workload: 0.92,
        annotator_progress: 0.75,
    },
];

interface AnnotatorsTabProps {
    annotators?: ProjectAnnotatorRowData[];
    /** Called after an annotator is successfully removed */
    onAnnotatorRemoved?: (id: number) => void;
}

export function AnnotatorsTab({
    annotators = MOCK_ANNOTATORS,
    onAnnotatorRemoved,
}: AnnotatorsTabProps) {
    const { t } = useTranslations();
    return (
        <div
            id="tabpanel-annotators"
            role="tabpanel"
            aria-labelledby="tab-annotators"
            className="flex flex-col gap-6"
        >
            <div className="flex items-center justify-between">
                <h2 className="page-subtitle">{t('projects.annotators_tab.title')}</h2>
                <Button className="hover:bg-brand-blue-800 h-10 font-semibold text-white">
                    <Plus className="size-4" aria-hidden="true" />
                    {t('projects.annotators_tab.add_annotator')}
                </Button>
            </div>
            <AnnotatorsTable
                mode="remove"
                annotators={annotators}
                onAnnotatorRemoved={onAnnotatorRemoved}
            />
        </div>
    );
}
