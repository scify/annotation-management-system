<?php

declare(strict_types=1);

return [
    'title' => 'Ειδοποιήσεις',
    'index_page_title' => 'Ειδοποιήσεις',
    'show_unread' => 'Εμφανίζονται τα μη αναγνωσμένα',
    'show_all' => 'Εμφανίζονται όλα',
    'mark_as_unread' => 'Σήμανση ως μη αναγνωσμένο',
    'mark_all_as_read' => 'Σήμανση όλων ως αναγνωσμένα',
    'unread' => 'Μη αναγνωσμένη',
    'quick_links' => 'Γρήγοροι σύνδεσμοι:',
    'reply' => 'Απάντηση',
    'reply_placeholder' => 'Γράψτε την απάντησή σας…',
    'send_reply' => 'Αποστολή απάντησης',
    'reply_sent' => 'Η απάντηση εστάλη με επιτυχία.',
    'action_approved' => 'Η ενέργεια εγκρίθηκε.',
    'action_rejected' => 'Η ενέργεια απορρίφθηκε.',
    'errors' => [
        'response_not_found' => 'Δεν βρέθηκε εγγραφή απάντησης για αυτήν την ειδοποίηση.',
        'cannot_reject_accepted' => 'Η πρόταση έχει ήδη γίνει αποδεκτή και δεν μπορεί να απορριφθεί.',
        'cannot_approve_rejected' => 'Η πρόταση έχει ήδη απορριφθεί και δεν μπορεί να εγκριθεί.',
        'cannot_respond_cancelled' => 'Η πρόταση έχει ακυρωθεί και δεν μπορεί πλέον να απαντηθεί.',
    ],
    'approve' => 'Έγκριση',
    'reject' => 'Απόρριψη',
    'accepted' => 'Αποδεκτό',
    'rejected' => 'Απορρίφθηκε',
    'select_notification_hint' => 'Επιλέξτε μια ειδοποίηση για να δείτε τις λεπτομέρειές της',
    'today_at' => 'Σήμερα στις',
    'recipient' => 'Παραλήπτης:',
    'recipients' => 'Παραλήπτες:',
    'recipients_more' => '+:count ακόμη',
    'empty_list' => 'Δεν έχετε ειδοποιήσεις',
    'empty_unread' => 'Δεν έχετε μη αναγνωσμένες ειδοποιήσεις',
    'messages' => [
        'profile_edited' => [
            'title' => 'Επεξεργασία προφίλ',
            'body' => 'Ο/Η @:editor επεξεργάστηκε το προφίλ σας',
        ],
        'added_to_project' => [
            'title' => 'Νέο έργο',
            'body' => 'Ο/Η @:editor σας πρόσθεσε στο έργο του/της',
        ],
        'overdue_approaching' => [
            'title' => 'Πλησιάζει η προθεσμία',
            'body' => 'Το υποέργο :subproject θα ξεπεράσει την προθεσμία σε :days ημέρες',
        ],
        'subproject_overdue' => [
            'title' => 'Εκπρόθεσμο υποέργο',
            'body' => 'Το υποέργο :subproject ξεπέρασε την προθεσμία σήμερα',
        ],
        'announcement' => 'Ο/Η :username έκανε μια ανακοίνωση:',
        'project_ownership' => 'θέλει να σας μεταβιβάσει την κυριότητα του :project.',
        'project_ownership_question' => 'Αποδέχεστε την κυριότητα;',
        'project_invitation' => 'σας προσκάλεσε να συμμετάσχετε στο :project.',
        'project_invitation_question' => 'Αποδέχεστε την πρόσκληση;',
        'project_request_to_leave' => 'ζήτησε να αποχωρήσει από το :project.',
        'project_request_to_leave_question' => 'Αποδέχεστε το αίτημα;',
        'annotators_added_to_project' => [
            'title' => 'Νέοι Επισημειωτές',
            'body' => 'Οι επισημειωτές :names προστέθηκαν στο :project.',
        ],
        'manager_removed_from_project' => [
            'title' => 'Εξαίρεση από Έργο',
            'body' => 'Εξαιρεθήκατε από το :project.',
        ],
        'ownership_proposal_cancelled' => [
            'title' => 'Αναίρεση Πρότασης για Αλλαγή Ιδιοκτησίας',
            'body' => 'Η πρόταση αλλαγής ιδιοκτησίας για το έργο :project αναιρέθηκε.',
        ],
        'leave_request_cancelled' => [
            'title' => 'Αναίρεση Αίτησης Αποχώρησης',
            'body' => 'Η αίτηση αποχώρησης από το :project αναιρέθηκε.',
        ],
        'leave_request_accepted' => [
            'title' => 'Αποδεχτή η αίτηση αποχώρησης',
            'body' => 'Η αίτηση αποχώρησης από το έργο :project έγινε αποδεκτή.',
        ],
        'leave_request_rejected' => [
            'title' => 'Απόρριψη αίτησης αποχώρησης',
            'body' => 'Η αίτηση αποχώρησης από το έργο :project απορρίφθηκε.',
        ],
        'ownership_transfer_accepted' => [
            'title' => 'Αποδοχή Μεταβίβασης Ιδιοκτησίας',
            'body' => 'Ο χρήστης :username αποδέχτηκε να γίνει ιδιοκτήτης του έργου :project.',
        ],
        'ownership_transfer_rejected' => [
            'title' => 'Απόρριψη Μεταβίβασης Ιδιοκτησίας',
            'body' => 'Ο χρήστης :username αρνήθηκε να γίνει ιδιοκτήτης του έργου :project.',
        ],
    ],
];
