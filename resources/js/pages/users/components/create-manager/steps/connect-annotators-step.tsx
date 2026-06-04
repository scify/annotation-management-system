import { FilterableAnnotatorList } from '@/components/annotator/filterable-annotator-list';
import { useTranslations } from '@/hooks/use-translations';
import { type AnnotatorSelectOption } from '@/types';

interface ConnectAnnotatorsStepProps {
    annotators: AnnotatorSelectOption[];
    myAnnotators: AnnotatorSelectOption[];
    selectedAnnotatorIds: number[];
    onSelectionChange: (ids: number[]) => void;
    lockedAnnotatorIds?: number[];
    showMineToggle?: boolean;
}

export function ConnectAnnotatorsStep({
    annotators,
    myAnnotators,
    selectedAnnotatorIds,
    onSelectionChange,
    lockedAnnotatorIds,
    showMineToggle = true,
}: ConnectAnnotatorsStepProps) {
    const { t, trans } = useTranslations();
    const totalSelectedCount = new Set([...selectedAnnotatorIds, ...(lockedAnnotatorIds ?? [])])
        .size;

    return (
        <section aria-labelledby="step-heading" className="flex flex-col gap-5">
            <hgroup>
                <h2 id="step-heading" className="page-subtitle">
                    {t('users.select_annotators.heading')}
                </h2>
                <p className="text-sm font-semibold text-slate-800">
                    {trans('users.select_annotators.selected_count', {
                        count: totalSelectedCount,
                    })}
                </p>
            </hgroup>
            <FilterableAnnotatorList
                annotators={annotators}
                myAnnotators={myAnnotators}
                selectedAnnotatorIds={selectedAnnotatorIds}
                onSelectionChange={onSelectionChange}
                lockedAnnotatorIds={lockedAnnotatorIds}
                showMineToggle={showMineToggle}
            />
        </section>
    );
}
