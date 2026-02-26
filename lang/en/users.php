<?php

declare(strict_types=1);

return [
    'title' => 'Users',
    'index_page_title' => 'All Users',
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
    ],
    'labels' => [
        'name' => 'Name',
        'email' => 'Email',
        'password' => 'Password',
        'password_confirmation' => 'Confirm Password',
        'actions' => 'Actions',
        'role' => 'Role',
        'status' => 'Status',
        'created_at' => 'Created At',
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
