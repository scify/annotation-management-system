// ── Raw backend types (what MonitorController returns) ────────────────────────

export interface BackendCoManager {
    id: number;
    username: string;
}

export interface BackendSubproject {
    id: number;
    name: string;
    status: string;
    workload: number;
    progress: number;
    started_at: string | null;
    completed_at: string | null;
    scheduled_at: string | null;
    deadline_at: string | null;
}

export interface BackendProject {
    id: number;
    name: string;
    status: string;
    annotation_task_title: string;
    dataset_name: string;
    owner_name: string;
    co_managers: BackendCoManager[];
    project_progress: number;
    notifications_count: number;
    started_at: string | null;
    completed_at: string | null;
    scheduled_at: string | null;
    deadline_at: string | null;
    is_delayed_to_start: boolean;
    is_delayed_to_end: boolean;
    subprojects: BackendSubproject[];
}

export interface BackendHiddenProject {
    owner_name: string;
    active_subprojects_count: number;
    // TODO(backend): add assigned_to (string) — needed to render "X subprojects assigned to @user"
}

export interface BackendAnnotator {
    id: number;
    username: string;
    status: boolean;
    active_subprojects: number;
    active_projects: number;
    workload: number;
    progress: number;
    projects: BackendProject[];
    hidden_projects: BackendHiddenProject[];
}

export interface BackendActiveWorkData {
    all_annotators: BackendAnnotator[];
    my_annotators: BackendAnnotator[];
}

// ── Raw backend types — history tab ───────────────────────────────────────────

export interface BackendHistorySubproject {
    project_name: string;
    subproject_name: string;
    completed_at: string | null;
    annotations: number;
    flags: number;
    avg_confidence: 'high' | 'medium' | 'low' | null;
    // TODO(backend): add velocity once available
}

export interface BackendHistoryAnnotator {
    id: number;
    username: string;
    is_active: boolean;
    total_projects: number;
    total_subprojects: number;
    total_annotations: number;
    total_flags: number;
    subprojects: BackendHistorySubproject[];
    // TODO(backend): add average_velocity once available
}

export interface BackendHistoryData {
    all_annotators: BackendHistoryAnnotator[];
    my_annotators: BackendHistoryAnnotator[];
}

// ── Normalized UI types (what components consume) ──────────────────────────────

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
    annotation_task_title: string;
    dataset_name: string;
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
    notifications_count: number;
    subprojects: SubProject[];
}

export interface HiddenProject {
    restricted: true;
    owner: string;
    assignedCount: number;
}

export interface HistoryAnnotatorSubproject {
    project: string;
    subproject: string;
    annotations: number;
    flags: number;
    /** null until backend adds velocity to subproject history response */
    velocity: number | null;
    /** null when backend avg_confidence is null */
    confidence: 'High' | 'Medium' | 'Low' | null;
    /** empty string when backend completed_at is null */
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
    /** null until backend adds average_velocity to annotator history response */
    averageVelocity: number | null;
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
