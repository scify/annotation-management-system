<?php

declare(strict_types=1);

return [
    'title' => 'Έργα',
    'create_button' => 'Δημιουργία Έργου',
    'filter' => 'Φίλτρο',

    'card' => [
        'overall_progress' => 'Συνολική Πρόοδος',
        'owner' => 'Ιδιοκτήτης:',
        'co_managers' => 'Συν-διαχειριστές:',
        'view_project' => 'Προβολή Έργου',
    ],

    'show' => [
        'overall_progress' => 'Συνολική Πρόοδος',
        'tab_subprojects' => 'Υποέργα',
        'tab_annotators' => 'Επισημειωτές',
        'tab_managers' => 'Διαχειριστές',
        'tab_export' => 'Εξαγωγή',
        'tag_task' => 'Εργασία:',
        'tag_dataset' => 'Σύνολο δεδομένων:',
    ],

    'annotators_tab' => [
        'title' => 'Επισημειωτές',
        'add_annotator' => 'Προσθήκη Επισημειωτή',
        'table_username' => 'Όνομα χρήστη',
        'table_projects' => 'Έργα',
        'table_subprojects' => 'Υποέργα',
        'table_workload' => 'Υπόλ. Φόρτος',
        'table_progress' => 'Πρόοδος',
        'table_action' => 'Ενέργεια',
    ],

    'managers_tab' => [
        'title' => 'Διαχειριστές',
        'invite_placeholder' => 'Κείμενο…',
        'invite_button' => 'Πρόσκληση μέσω email',
        'table_username' => 'Όνομα χρήστη',
        'table_role' => 'Ρόλος',
        'table_ownership' => 'Ιδιοκτησία',
        'table_actions' => 'Ενέργειες',
        'role_owner' => 'Ιδιοκτήτης',
        'role_co_manager' => 'Συν-Διαχειριστής',
        'ownership_request_button' => 'Αίτημα Ιδιοκτησίας',
        'leave_button' => 'Αίτημα αποχώρησης',
        'dialog_ownership_title' => 'Αίτημα Ιδιοκτησίας',
        'dialog_ownership_transferred' => 'Ο/Η :username μεταβίβασε την Ιδιοκτησία αυτού του Έργου σε εσάς.',
        'dialog_ownership_accept' => 'Αποδέχεστε την Ιδιοκτησία;',
        'dialog_ownership_reject' => 'Απόρριψη',
        'dialog_ownership_approve' => 'Αποδοχή',
        'dialog_leave_title' => 'Αίτημα Αποχώρησης',
        'dialog_leave_description' => 'Αυτή η ενέργεια θα υποβάλει το αίτημα αποχώρησής σας για έγκριση από τον Ιδιοκτήτη',
        'dialog_leave_confirm' => 'Αποστολή αιτήματος αποχώρησης;',
        'dialog_leave_warning' => 'Με την αποχώρηση από το Έργο, δεν θα έχετε πλέον πρόσβαση στα δεδομένα του',
        'dialog_leave_send' => 'Αποστολή Αιτήματος',
        'dialog_message_title' => 'Αποστολή μηνύματος',
        'dialog_message_write' => 'Γράψτε το μήνυμά σας στον/ην :username',
        'dialog_message_send' => 'Αποστολή',
        'dialog_message_placeholder' => 'Κείμενο…',
    ],

    'export_tab' => [
        'title' => 'Εξαγωγή',
        'selected' => ':count επιλεγμένα',
        'export_button' => 'Εξαγωγή Αποτελεσμάτων (CSV)',
        'select_all' => 'Επιλογή όλων',
    ],

    'subprojects_tab' => [
        'title' => 'Υποέργα',
        'create_button' => 'Δημιουργία Υποέργου',
    ],

    'dashboard' => [
        'overview_title' => 'Επισκόπηση Πίνακα Ελέγχου',
        'active_projects_heading' => 'Ενεργά Έργα Επισημείωσης',
        'annotators_overview_heading' => 'Επισκόπηση Επισημειωτών',
        'table_username' => 'Όνομα χρήστη',
        'table_active_projects' => 'Ενεργά Έργα',
        'table_remaining_workload' => 'Υπόλοιπος Φόρτος',
        'table_progress' => 'Πρόοδος',
    ],
];
