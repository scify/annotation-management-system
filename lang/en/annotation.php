<?php

declare(strict_types=1);

return [
    'title' => 'Annotation',
    'project' => 'Project',
    'subproject' => 'Subproject',

    // Sidebar
    'description' => 'Description',
    'meanings_of_word' => 'Meanings of word :word:',
    'annotation_progress' => 'Annotation Progress',
    'instances_count' => ':done / :total Instances',
    'submitted' => 'SUBMITTED',
    'this_session' => 'THIS SESSION',
    'pending' => 'PENDING',
    'not_annotated' => 'NOT ANNOTATED',
    'session' => 'SESSION',
    'submitted_progress' => 'Submitted Progress :pct%',
    'progress' => 'Progress :pct%',
    'flagged_instances' => 'Flagged Instances',
    'total_replied' => ':total Total / :replied Replied',
    'submit_all_pending' => 'Submit All Pending',
    'exit_annotation' => 'Exit Annotation',
    'exit_annotation_success' => 'Exited Annotation',
    'to_manager' => 'To Manager',

    // Top bar
    'show_instances' => 'Show Instances:',
    'filter_not_annotated' => 'Not Annotated',
    'filter_pending' => 'Pending',
    'filter_submitted' => 'Submitted',
    'filter_all' => 'All',

    // Main panel
    'no_instances' => 'No instances left to annotate.',
    'instance' => 'Instance: :index',
    'flag' => 'Flag',
    'flag_and_continue' => 'Flag & Continue',
    'flagged' => 'Flagged',
    'replied' => 'Replied!',
    'see_reply' => 'See Reply',
    'waiting_for_reply' => 'Waiting for Reply',
    'select_an_option' => 'Select an Option',
    'same_meaning_question' => 'Does the word :word have the same meaning?',
    'answer_yes' => 'Yes',
    'answer_no' => 'No',
    'answer_cannot_decide' => 'Cannot decide',
    'your_confidence' => 'Your Confidence:',
    'confidence_high' => 'High',
    'confidence_medium' => 'Medium',
    'confidence_low' => 'Low',
    'submit' => 'Submit',
    'save' => 'Save',
    'submit_success' => 'Annotation submitted.',
    'flag_success' => 'Instance flagged.',
    'submitted_button' => 'Submitted',
    'previous' => 'Previous',
    'next' => 'Next',
    'hide_shortcuts' => 'Hide Shortcuts',
    'show_shortcuts' => 'Show Shortcuts',

    // Send to Manager dialog
    'send_to_manager' => [
        'title' => 'To Manager',
        'instance' => 'Instance #:index',
        'description' => 'Send a message to the Manager(s) referring to this instance. This will not interfere with your workflow',
        'label' => 'Message to Manager:',
        'placeholder' => 'Type your message…',
        'send' => 'Send',
        'cancel' => 'Cancel',
        'success' => 'Your message was sent to the manager(s).',
    ],

    // Flag & Continue dialog
    'flag_and_continue_dialog' => [
        'title' => 'Flag & Continue',
        'instance' => 'Instance #:index',
        'description' => 'By sending this message to the manager you mark this instance as “Flagged”. This means that you came across an issue and you need feedback to continue. You will automatically continue with the next instance. You can come back to this one later.',
        'label' => 'Message to Manager:',
        'placeholder' => 'Type your message…',
        'send' => 'Send',
        'cancel' => 'Cancel',
    ],
];
