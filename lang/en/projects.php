<?php

declare(strict_types=1);

return [
    'title' => 'Projects',
    'create_button' => 'Create Project',
    'filter' => 'Filter',
    'filter_clear' => 'Clear all',
    'filter_tag_is' => 'is',
    'filter_task_section' => 'Task',
    'filter_dataset_section' => 'Dataset',
    'filter_state_section' => 'State',
    'filter_search' => 'Search',
    'sort_button' => 'Sorting',
    'sort_progress_section' => 'Progress',
    'sort_date_created_section' => 'Date Created',
    'sort_due_date_section' => 'Due Date',
    'sort_ascending' => 'Ascending',
    'sort_descending' => 'Descending',
    'sort_recent_first' => 'Recent first',
    'sort_older_first' => 'Older first',
    'sort_not_selected' => 'None',
    'projects_count' => ':count Projects',
    'search_placeholder' => 'Search Projects…',

    'card' => [
        'overall_progress' => 'Overall Progress',
        'owner' => 'Owner:',
        'co_managers' => 'Co-managers:',
        'view_project' => 'View Project',
    ],

    'show' => [
        'overall_progress' => 'Overall Progress',
        'tab_subprojects' => 'Subprojects',
        'tab_annotators' => 'Annotators',
        'tab_managers' => 'Managers',
        'tab_export' => 'Export',
        'tag_task' => 'Task:',
        'tag_dataset' => 'Dataset:',
    ],

    'annotators_tab' => [
        'title' => 'Annotators',
        'add_annotator' => 'Add Annotator',
        'table_username' => 'Username',
        'table_projects' => 'Projects',
        'table_subprojects' => 'Subprojects',
        'table_workload' => 'Remain. Workload',
        'table_progress' => 'Progress',
        'table_action' => 'Action',
    ],

    'managers_tab' => [
        'title' => 'Managers',
        'invite_placeholder' => 'Placeholder text',
        'invite_button' => 'Invite by email',
        'table_username' => 'Username',
        'table_role' => 'Role',
        'table_ownership' => 'Ownership',
        'table_actions' => 'Actions',
        'role_owner' => 'Owner',
        'role_co_manager' => 'Co-Manager',
        'ownership_request_button' => 'Ownership Request',
        'leave_button' => 'Request to leave',
        'dialog_ownership_title' => 'Ownership Request',
        'dialog_ownership_transferred' => ':username transferred the Ownership of this Project to you.',
        'dialog_ownership_accept' => 'Do you accept the Ownership?',
        'dialog_ownership_reject' => 'Reject',
        'dialog_ownership_approve' => 'Approve',
        'dialog_leave_title' => 'Leave Request',
        'dialog_leave_description' => 'This action will submit your Leave request for approval from the Owner',
        'dialog_leave_confirm' => 'Send Leave Request?',
        'dialog_leave_warning' => 'By Leaving this Project, you will no longer have access to its data',
        'dialog_leave_send' => 'Send Request',
        'dialog_message_title' => 'Send message',
        'dialog_message_write' => 'Write your message to :username',
        'dialog_message_send' => 'Send',
        'dialog_message_placeholder' => 'Placeholder text',
    ],

    'export_tab' => [
        'title' => 'Export',
        'selected' => ':count selected',
        'export_button' => 'Export Results (CSV)',
        'select_all' => 'Select all',
    ],

    'subprojects_tab' => [
        'title' => 'Subprojects',
        'create_button' => 'Create Subproject',
    ],

    'dashboard' => [
        'overview_title' => 'Dashboard Overview',
        'active_projects_heading' => 'Active Annotation Projects',
        'annotators_overview_heading' => 'Annotators Overview',
        'table_username' => 'Username',
        'table_active_projects' => 'Active Projects',
        'table_remaining_workload' => 'Remaining Workload',
        'table_progress' => 'Progress',
    ],
];
