import { type ProjectAnnotatorRowData } from '@/components/annotator/annotators-table';
import { SelectAnnotatorsStep } from '@/components/annotator/select-annotators-step';

const MOCK_ANNOTATORS: ProjectAnnotatorRowData[] = [
    {
        id: 1,
        name: 'ggiannakopoulou',
        annotator_progress: 0.75,
        active_projects_count: 23,
        active_subprojects_count: 23,
        workload: 0.7,
    },
    {
        id: 2,
        name: 'nellisavrani',
        annotator_progress: 0.75,
        active_projects_count: 12,
        active_subprojects_count: 4,
        workload: 0.4,
    },
    {
        id: 3,
        name: 'alexpapadopoulos',
        annotator_progress: 0.75,
        active_projects_count: 23,
        active_subprojects_count: 23,
        workload: 0.7,
    },
    {
        id: 4,
        name: 'mariakonstantinou',
        annotator_progress: 0.5,
        active_projects_count: 8,
        active_subprojects_count: 12,
        workload: 0.5,
    },
    {
        id: 5,
        name: 'dimitrispappas',
        annotator_progress: 0.3,
        active_projects_count: 5,
        active_subprojects_count: 7,
        workload: 0.3,
    },
];

const MOCK_MY_ANNOTATORS = MOCK_ANNOTATORS.slice(0, 2);

interface ConnectAnnotatorsStepProps {
    selectedAnnotatorIds: number[];
    onSelectionChange: (ids: number[]) => void;
}

export function ConnectAnnotatorsStep({
    selectedAnnotatorIds,
    onSelectionChange,
}: ConnectAnnotatorsStepProps) {
    const selectedIds = new Set(selectedAnnotatorIds);

    function handleSingleChange(id: number, checked: boolean) {
        const next = new Set(selectedAnnotatorIds);
        if (checked) next.add(id);
        else next.delete(id);
        onSelectionChange([...next]);
    }

    function handleSelectAll(ids: number[], checked: boolean) {
        if (checked) {
            onSelectionChange([...new Set([...selectedAnnotatorIds, ...ids])]);
        } else {
            const toRemove = new Set(ids);
            onSelectionChange(selectedAnnotatorIds.filter((id) => !toRemove.has(id)));
        }
    }

    return (
        <SelectAnnotatorsStep
            annotators={MOCK_ANNOTATORS}
            myAnnotators={MOCK_MY_ANNOTATORS}
            selectedIds={selectedIds}
            onSelectionChange={handleSingleChange}
            onSelectAllChange={handleSelectAll}
            translationNamespace="users"
        />
    );
}
