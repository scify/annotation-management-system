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
        'cannot_respond_cancelled' => 'This notification has been cancelled and can no longer be responded to.',
    ],
    'approve' => 'Accept',
    'reject' => 'Reject',
    'accepted' => 'Accepted',
    'rejected' => 'Rejected',
    'canceled' => 'Cancelled',
    'select_notification_hint' => 'Select a notification to view its details',
    'today_at' => 'Today at',
    'recipient' => 'Recipient:',
    'recipients' => 'Recipients:',
    'recipients_more' => '+:count more',
    'empty_list' => 'You have no notifications',
    'empty_unread' => 'You have no unread notifications',
    'messages' => [
        'profile_edited' => [
            'title' => 'Profile edit',
            'body' => "@:editor just edited @:recipient's profile",
        ],
        'added_to_project' => [
            'title' => 'New Project',
            'body' => '@:editor just added @:recipient to their project',
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
        'project_ownership' => 'wants to transfer the Ownership of :project to @:recipient.',
        'project_ownership_question' => 'Do you accept the Ownership?',
        'project_invitation' => 'invited @:recipient to participate to :project.',
        'project_invitation_question' => 'Do you accept the Invitation?',
        'project_request_to_leave' => 'asked to leave :project.',
        'project_request_to_leave_question' => 'Do you accept the request?',
        'annotators_added_to_project' => [
            'title' => 'New Annotators',
            'body' => 'Annotators :names have been added to :project.',
        ],
        'manager_removed_from_project' => [
            'title' => 'Removed from Project',
            'body' => '@:recipient has been removed from :project.',
        ],
        'ownership_proposal_cancelled' => [
            'title' => 'Ownership Change Proposal Withdrawn',
            'body' => 'The ownership change proposal for :project has been withdrawn.',
        ],
        'leave_request_cancelled' => [
            'title' => 'Leave Request Withdrawn',
            'body' => 'The leave request for :project has been withdrawn.',
        ],
        'leave_request_accepted' => [
            'title' => 'Leave Request Accepted',
            'body' => "@:recipient's leave request for :project has been accepted.",
        ],
        'leave_request_rejected' => [
            'title' => 'Leave Request Rejected',
            'body' => "@:recipient's leave request for :project has been rejected.",
        ],
        'ownership_transfer_accepted' => [
            'title' => 'Ownership Transfer Accepted',
            'body' => ':username accepted the ownership transfer for :project.',
        ],
        'ownership_transfer_rejected' => [
            'title' => 'Ownership Transfer Declined',
            'body' => ':username declined the ownership transfer for :project.',
        ],
    ],
];
