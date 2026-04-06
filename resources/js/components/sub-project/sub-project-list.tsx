import { type SubProjectListItemData, SubProjectListItem } from './sub-project-list-item';

interface SubProjectListProps {
	subProjects: SubProjectListItemData[];
}

export function SubProjectList({ subProjects }: SubProjectListProps) {
	return (
		<div className="flex flex-col gap-2">
			{subProjects.map((subProject) => (
				<SubProjectListItem key={subProject.id} subProject={subProject} />
			))}
		</div>
	);
}
