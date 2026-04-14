import { SubProjectList } from '@/components/sub-project/sub-project-list';
import { type SubProjectListItemData } from '@/components/sub-project/sub-project-list-item';
import { Button } from '@/components/ui/button';
import { Plus } from 'lucide-react';

interface SubprojectsTabProps {
	subProjects: SubProjectListItemData[];
	/** Called after a new subproject is successfully created */
	onSubprojectCreated?: () => void;
}

export function SubprojectsTab({ subProjects, onSubprojectCreated }: SubprojectsTabProps) {
	return (
		<div
			id="tabpanel-subprojects"
			role="tabpanel"
			aria-labelledby="tab-subprojects"
			className="flex flex-col gap-6"
		>
			<div className="flex items-center justify-between">
				<h2 className="page-subtitle">Subprojects</h2>
				<Button
					className="hover:bg-brand-blue-800 h-10 font-semibold text-white"
					onClick={onSubprojectCreated}
				>
					<Plus className="size-4" aria-hidden="true" />
					Create Subproject
				</Button>
			</div>
			<SubProjectList subProjects={subProjects} />
		</div>
	);
}
