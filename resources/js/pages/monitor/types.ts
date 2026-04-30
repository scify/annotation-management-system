export interface SubProject {
	id: number;
	name: string;
	dateRange: string;
	remainingWorkload: number;
	progress: number;
	state: 'in_progress' | 'completed' | 'pending';
}

export interface MonitorProject {
	id: number;
	name: string;
	dateRange: string;
	status: 'in_progress' | 'completed' | 'pending';
	owner: string;
	coManagers: string[];
	overallProgress: number;
	subprojects: SubProject[];
}

export interface HiddenProject {
	restricted: true;
	owner: string;
	assignedCount: number;
	assignedTo: string;
}

export interface MonitorAnnotator {
	id: number;
	username: string;
	initials: string;
	status: 'active' | 'inactive';
	activeSubprojects: number;
	activeProjects: number;
	remainingWorkload: number;
	progress: number;
	projects: (MonitorProject | HiddenProject)[];
}
