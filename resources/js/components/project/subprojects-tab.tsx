import { SubProjectList } from '@/components/sub-project/sub-project-list';
import { type SubProjectListItemData } from '@/components/sub-project/sub-project-list-item';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/hooks/use-translations';
import { Plus } from 'lucide-react';

interface SubprojectsTabProps {
	subProjects: SubProjectListItemData[];
	projectId?: number;
	/** Called after a new subproject is successfully created */
	onSubprojectCreated?: () => void;
}

export function SubprojectsTab({
	subProjects,
	projectId,
	onSubprojectCreated,
}: SubprojectsTabProps) {
	const { t } = useTranslations();
	return (
		<div
			id="tabpanel-subprojects"
			role="tabpanel"
			aria-labelledby="tab-subprojects"
			className="flex flex-col gap-6"
		>
			<div className="flex items-center justify-between">
				<h2 className="page-subtitle">{t('projects.subprojects_tab.title')}</h2>
				<Button
					className="hover:bg-brand-blue-800 h-10 font-semibold text-white"
					onClick={onSubprojectCreated}
				>
					<Plus className="size-4" aria-hidden="true" />
					{t('projects.subprojects_tab.create_button')}
				</Button>
			</div>
			<SubProjectList subProjects={subProjects} projectId={projectId} />
		</div>
	);
}
