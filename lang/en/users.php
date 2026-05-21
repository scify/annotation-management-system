<?php

declare(strict_types=1);

return [
    'title' => 'Users',
    'index_page_title' => 'All Users',
    'tabs' => [
        'admins' => 'Admins',
        'managers' => 'Managers',
        'annotators' => 'Annotators',
    ],
    'actions' => [
        'new' => 'New User',
        'new_big_button' => 'Create User',
        'create' => 'Create User',
        'edit' => 'Edit User',
        'edit_big_button' => 'Edit User',
        'update' => 'Update User',
        'delete' => 'Delete User',
        'show' => 'Show User',
        'restore' => 'Restore User',
        'view_edit' => 'View/Edit',
        'view' => 'View',
        'create_manager' => 'Create New Manager',
        'create_admin' => 'Create New Admin',
        'create_annotator' => 'Create New Annotator',
    ],
    'labels' => [
        'name' => 'Name',
        'username_name' => 'Username/Name',
        'username' => 'Username',
        'email' => 'Email',
        'password' => 'Password',
        'password_confirmation' => 'Confirm Password',
        'actions' => 'Actions',
        'role' => 'Role',
        'status' => 'Status',
        'created_at' => 'Created At',
    ],
    'filters' => [
        'show_active' => 'Show Active',
        'show_all' => 'Show all',
        'show_only_active' => 'Show only active',
        'show_only_inactive' => 'Show only inactive',
        'show_only_mine' => 'Show only Managers I am connected with',
        'show_only_mine_annotators' => 'Show only Annotators I am connected with',
    ],
    'placeholders' => [
        'select_role' => 'Select Role',
        'search' => 'Search by name or email...',
    ],
    'messages' => [
        'created' => 'User created successfully',
        'updated' => 'User updated successfully',
        'deleted' => 'User deleted successfully',
        'restored' => 'User restored successfully',
    ],
    'delete' => [
        'title' => 'Delete User',
        'description' => 'Are you sure you want to delete this user?',
    ],
    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],
    'restore' => [
        'title' => 'Restore User',
        'description' => 'Are you sure you want to restore this user?',
    ],
];
