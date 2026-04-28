import { type ProjectAnnotatorRowData } from '@/components/annotator/annotators-table';
import { AnnotatorsTable } from '@/components/annotator/annotators-table';
import { ProjectDialog } from '@/components/project/project-dialog';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/hooks/use-translations';
import { Plus, UserMinus } from 'lucide-react';
import { useState } from 'react';

interface SubprojectAnnotatorsPanelProps {
	annotators: ProjectAnnotatorRowData[];
	onAnnotatorRemoved: (id: number) => void;
}

export function SubprojectAnnotatorsPanel({
	annotators,
	onAnnotatorRemoved,
}: SubprojectAnnotatorsPanelProps) {
	const { t, trans } = useTranslations();
	const [annotatorToRemove, setAnnotatorToRemove] = useState<ProjectAnnotatorRowData | null>(
		null
	);

	function handleRemoveRequest(id: number) {
		const annotator = annotators.find((a) => a.id === id);
		if (annotator) setAnnotatorToRemove(annotator);
	}

	function handleConfirmRemove() {
		if (annotatorToRemove) {
			onAnnotatorRemoved(annotatorToRemove.id);
			setAnnotatorToRemove(null);
		}
	}

	return (
		<div className="flex flex-col gap-6">
			<div className="flex items-center justify-between">
				<h2 className="page-subtitle">{t('projects.annotators_tab.title')}</h2>
				<Button className="bg-brand-blue-700 hover:bg-brand-blue-800 h-10 font-semibold text-white">
					<Plus className="size-4" aria-hidden="true" />
					{t('projects.annotators_tab.add_annotator')}
				</Button>
			</div>

			<AnnotatorsTable
				mode="remove"
				annotators={annotators}
				onAnnotatorRemoved={handleRemoveRequest}
			/>

			<ProjectDialog
				open={annotatorToRemove !== null}
				onClose={() => setAnnotatorToRemove(null)}
				icon={<UserMinus />}
				title={t('sub-projects.annotators_panel.remove_dialog_title')}
				description={trans('sub-projects.annotators_panel.remove_dialog_description', {
					username: annotatorToRemove?.username ?? '',
				})}
				cancelLabel={t('sub-projects.create.cancel')}
				actionLabel={t('sub-projects.annotators_panel.remove_confirm')}
				onAction={handleConfirmRemove}
			/>
		</div>
	);
}
