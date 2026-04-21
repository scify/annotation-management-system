<?php

declare(strict_types=1);

return [
    'create' => [
        'page_title' => 'Create Subproject',
        'heading' => 'Create Subproject',
        'cancel' => 'Cancel',
        'back' => 'Back',
        'next' => 'Next',
        'step_configurations' => 'Configurations',
        'config_coming_soon' => 'Configuration options coming soon…',
        'create_action' => 'Create',
        'dialog_description' => 'Enter a subproject name',
        'dialog_name_placeholder' => 'New Subproject Name…',
    ],

    'list_item' => [
        'instances' => 'Instances:',
        'progress' => 'Progress',
        'actions_label' => 'Subproject actions',
        'action_view_edit' => 'View / Edit',
        'action_test' => 'Test',
        'action_clone' => 'Clone',
        'action_set_in_progress' => 'Set as In Progress',
    ],

    'select_annotators' => [
        'heading' => 'Select Annotators',
        'selected_count' => ':count selected',
        'sort_by_name' => 'Sort by Name',
        'sort_by_workload' => 'Sort by Workload',
        'sort_asc_name' => 'A → Z',
        'sort_desc_name' => 'Z → A',
        'sort_asc_workload' => 'Low → High',
        'sort_desc_workload' => 'High → Low',
        'search_placeholder' => 'Search Annotators…',
        'select_all' => 'Select all',
        'min_one_required' => 'Select at least 1 annotator to continue.',
    ],

    'configuration' => [
        'heading' => 'Configurations',
        'priority_label' => 'Priority',
        'priority_placeholder' => 'Set Priority',
        'priority_low' => 'Low',
        'priority_medium' => 'Medium',
        'priority_high' => 'High',
        'timeframe_label' => 'Timeframe',
        'timeframe_placeholder' => 'Set timeframe',
        'requirements_label' => 'Requirements',
        'min_annotations_label' => 'Minimum Annotations',
        'min_annotations_description' => 'Set minimum annotations per instance',
        'priority_and_timeframe_required' => 'Select a priority and a timeframe to continue.',
        'min_annotations_inactive' => 'Not active',
        'min_annotations_placeholder' => 'Set a number from 1 to :max',
        'browsing_label' => 'Browsing and Submission',
        'flexible_browsing_label' => 'Flexible Browsing',
        'flexible_browsing_description' => 'Allows annotator to go back and forth through instances',
        'submission_auto' => 'Automatically per instance',
        'submission_manual' => 'By Annotator\'s Confirmation',
    ],

    'select_dataset' => [
        'project_dataset_heading' => 'Project Dataset',
        'dataset_label' => 'Dataset:',
        'total_instances' => 'Total Instances:',
        'shuffle_on' => 'Shuffle on',
        'select_subset_heading' => 'Select Subset',
        'previous_ended_at' => 'Previous subproject on dataset ":name" ended at Instance #:instance',
        'start_from' => 'Start from #:instance',
        'from_instance' => 'From instance#',
        'to_instance' => 'To instance#',
        'instances_selected' => ':count instances selected',
    ],
];
