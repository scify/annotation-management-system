import type { Project } from '@/types';
import { ProjectListItem } from './project-list-item';

interface ProjectListProps {
    projects: Project[];
}

export function ProjectList({ projects }: ProjectListProps) {
    return (
        <div className="flex flex-col gap-6">
            {projects.map((project) => (
                <ProjectListItem key={project.id} project={project} />
            ))}
        </div>
    );
}
