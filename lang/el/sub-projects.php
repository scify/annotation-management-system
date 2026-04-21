<?php

declare(strict_types=1);

return [
    'create' => [
        'page_title' => 'Δημιουργία Υποέργου',
        'heading' => 'Δημιουργία Υποέργου',
        'cancel' => 'Ακύρωση',
        'back' => 'Πίσω',
        'next' => 'Επόμενο',
        'step_configurations' => 'Ρυθμίσεις',
        'config_coming_soon' => 'Οι επιλογές ρυθμίσεων έρχονται σύντομα…',
        'create_action' => 'Δημιουργία',
        'dialog_description' => 'Εισάγετε ένα όνομα για το υποέργο',
        'dialog_name_placeholder' => 'Όνομα Νέου Υποέργου…',
    ],

    'list_item' => [
        'instances' => 'Παραδείγματα:',
        'progress' => 'Πρόοδος',
        'actions_label' => 'Ενέργειες υποέργου',
        'action_view_edit' => 'Προβολή / Επεξεργασία',
        'action_test' => 'Δοκιμή',
        'action_clone' => 'Αντιγραφή',
        'action_set_in_progress' => 'Ορισμός ως Σε Εξέλιξη',
    ],

    'select_annotators' => [
        'heading' => 'Επιλογή Επισημειωτών',
        'selected_count' => ':count επιλεγμένοι',
        'sort_by_name' => 'Ταξινόμηση κατά Όνομα',
        'sort_by_workload' => 'Ταξινόμηση κατά Φόρτο',
        'sort_asc_name' => 'Α → Ω',
        'sort_desc_name' => 'Ω → Α',
        'sort_asc_workload' => 'Χαμηλός → Υψηλός',
        'sort_desc_workload' => 'Υψηλός → Χαμηλός',
        'search_placeholder' => 'Αναζήτηση Επισημειωτών…',
        'select_all' => 'Επιλογή όλων',
        'min_one_required' => 'Επιλέξτε τουλάχιστον 1 επισημειωτή για να συνεχίσετε.',
    ],

    'configuration' => [
        'heading' => 'Ρυθμίσεις',
        'priority_label' => 'Προτεραιότητα',
        'priority_placeholder' => 'Ορισμός Προτεραιότητας',
        'priority_low' => 'Χαμηλή',
        'priority_medium' => 'Μεσαία',
        'priority_high' => 'Υψηλή',
        'timeframe_label' => 'Χρονικό Πλαίσιο',
        'timeframe_placeholder' => 'Ορισμός χρονικού πλαισίου',
        'requirements_label' => 'Απαιτήσεις',
        'min_annotations_label' => 'Ελάχιστες Επισημειώσεις',
        'min_annotations_description' => 'Ορισμός ελάχιστων επισημειώσεων ανά παράδειγμα',
        'priority_and_timeframe_required' => 'Επιλέξτε προτεραιότητα και χρονικό πλαίσιο για να συνεχίσετε.',
        'min_annotations_inactive' => 'Ανενεργό',
        'min_annotations_placeholder' => 'Ορίστε αριθμό από 1 έως :max',
        'browsing_label' => 'Περιήγηση και Υποβολή',
        'flexible_browsing_label' => 'Ευέλικτη Περιήγηση',
        'flexible_browsing_description' => 'Επιτρέπει στον επισημειωτή να πλοηγείται μπρος-πίσω στα παραδείγματα',
        'submission_auto' => 'Αυτόματα ανά παράδειγμα',
        'submission_manual' => 'Με επιβεβαίωση του Επισημειωτή',
    ],

    'select_dataset' => [
        'project_dataset_heading' => 'Σύνολο Δεδομένων Έργου',
        'dataset_label' => 'Σύνολο δεδομένων:',
        'total_instances' => 'Σύνολο Παραδειγμάτων:',
        'shuffle_on' => 'Τυχαία σειρά',
        'select_subset_heading' => 'Επιλογή Υποσυνόλου',
        'previous_ended_at' => 'Το προηγούμενο υποέργο στο σύνολο ":name" τελείωσε στο Παράδειγμα #:instance',
        'start_from' => 'Έναρξη από #:instance',
        'from_instance' => 'Από παράδειγμα#',
        'to_instance' => 'Έως παράδειγμα#',
        'instances_selected' => ':count παραδείγματα επιλεγμένα',
    ],
];
