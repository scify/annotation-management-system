<?php

declare(strict_types=1);

return [
    'title' => 'Notifications',
    'index_page_title' => 'Notifications',
    'show_unread' => 'Showing Unread',
    'show_all' => 'Showing all',
    'mark_as_unread' => 'Mark as Unread',
    'mark_all_as_read' => 'Mark all as Read',
    'unread' => 'Unread',
    'quick_links' => 'Quick Links:',
    'reply' => 'Reply',
    'reply_placeholder' => 'Write your reply…',
    'send_reply' => 'Send Reply',
    'reply_sent' => 'Reply sent successfully.',
    'action_approved' => 'Action approved successfully.',
    'action_rejected' => 'Action rejected successfully.',
    'errors' => [
        'response_not_found' => 'No response record found for this notification.',
        'cannot_reject_accepted' => 'This notification has already been accepted and cannot be rejected.',
        'cannot_approve_rejected' => 'This notification has already been rejected and cannot be approved.',
    ],
    'approve' => 'Approve',
    'reject' => 'Reject',
    'accepted' => 'Accepted',
    'rejected' => 'Rejected',
    'select_notification_hint' => 'Select a notification to view its details',
    'today_at' => 'Today at',
    'roles' => [
        'annotator' => 'Annotator',
        'manager' => 'Manager',
        'owner' => 'Owner',
    ],
    'empty_list' => 'You have no notifications',
    'empty_unread' => 'You have no unread notifications',
    'messages' => [
        'profile_edited' => [
            'title' => 'Profile edit',
            'body' => '@:editor just edited your profile',
        ],
        'added_to_project' => [
            'title' => 'New Project',
            'body' => '@:editor just added you to their project',
        ],
        'overdue_approaching' => [
            'title' => 'Overdue Date Approaching',
            'body' => 'Subproject :subproject will surpass due date in :days days',
        ],
        'subproject_overdue' => [
            'title' => 'Subproject Overdue',
            'body' => 'Subproject :subproject surpassed due date today',
        ],
        'announcement' => ':username made an announcement:',
        'project_ownership' => 'wants to transfer the Ownership of :project to you.',
        'project_ownership_question' => 'Do you accept the Ownership?',
        'project_invitation' => 'invited you to participate to :project.',
        'project_invitation_question' => 'Do you accept the Invitation?',
    ],
];
