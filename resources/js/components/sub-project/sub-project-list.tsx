import { type SubProjectListItemData, SubProjectListItem } from './sub-project-list-item';

interface SubProjectListProps {
	subProjects: SubProjectListItemData[];
	projectId?: number;
}

export function SubProjectList({ subProjects, projectId }: SubProjectListProps) {
	return (
		<div className="flex flex-col gap-2">
			{subProjects.map((subProject) => (
				<SubProjectListItem
					key={subProject.id}
					subProject={subProject}
					projectId={projectId}
				/>
			))}
		</div>
	);
}
