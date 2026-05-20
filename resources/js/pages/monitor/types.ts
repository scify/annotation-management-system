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
    started_at: string | null;
    completed_at: string | null;
    scheduled_at: string | null;
    deadline_at: string | null;
    is_delayed_to_start: boolean;
    is_delayed_to_end: boolean;
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

export interface HistoryAnnotatorSubproject {
    project: string;
    subproject: string;
    annotations: number;
    flags: number;
    velocity: number;
    confidence: 'High' | 'Medium' | 'Low';
    dateCompleted: string;
}

export interface HistoryAnnotator {
    id: number;
    username: string;
    initials: string;
    status: 'active' | 'inactive';
    totalProjects: number;
    totalSubprojects: number;
    totalAnnotations: number;
    totalFlags: number;
    averageVelocity: number;
    subprojects: HistoryAnnotatorSubproject[];
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
