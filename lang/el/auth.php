<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */
    'login' => [
        'title' => 'Συνδεθείτε στο Annotation Management System',
        'description' => 'Εισάγετε το email και τον κωδικό σας παρακάτω για να συνδεθείτε',
        'email' => 'Διεύθυνση email',
        'password' => 'Κωδικός',
        'remember' => 'Να με θυμάσαι',
        'forgot_password' => 'Ξεχάσατε τον κωδικό σας;',
        'button' => 'Σύνδεση',
        'captcha' => 'Παρακαλώ συμπληρώστε το CAPTCHA',
        'registration_application' => 'Αίτηση πρόσβασης',
    ],
    'forgot_password' => [
        'title' => 'Ξεχάσατε τον κωδικό;',
        'description' => 'Εισάγετε το email σας για να λάβετε σύνδεσμο επαναφοράς κωδικού',
        'email_label' => 'Διεύθυνση email',
        'submit_button' => 'Αποστολή συνδέσμου επαναφοράς',
        'return_to_login' => 'Ή, επιστρέψτε στη',
        'login_link' => 'σύνδεση',
    ],
    'reset_password' => [
        'title' => 'Επαναφορά κωδικού',
        'description' => 'Παρακαλώ εισάγετε τον νέο σας κωδικό παρακάτω',
        'email_label' => 'Email',
        'password_label' => 'Κωδικός',
        'password_placeholder' => 'Κωδικός',
        'confirm_label' => 'Επιβεβαίωση κωδικού',
        'confirm_placeholder' => 'Επιβεβαίωση κωδικού',
        'submit_button' => 'Επαναφορά κωδικού',
    ],
    'verify_email' => [
        'title' => 'Επαλήθευση email',
        'description' => 'Παρακαλώ επαληθεύστε τη διεύθυνση email σας κάνοντας κλικ στον σύνδεσμο που σας στείλαμε.',
        'verification_sent' => 'Ένας νέος σύνδεσμος επαλήθευσης εστάλη στη διεύθυνση email που δηλώσατε κατά την εγγραφή.',
        'resend_button' => 'Εκ νέου αποστολή email επαλήθευσης',
        'logout_link' => 'Αποσύνδεση',
    ],
    'confirm_password' => [
        'title' => 'Επιβεβαίωση κωδικού',
        'description' => 'Αυτή είναι μια ασφαλής περιοχή. Παρακαλώ επιβεβαιώστε τον κωδικό σας πριν συνεχίσετε.',
        'password_label' => 'Κωδικός',
        'password_placeholder' => 'Κωδικός',
        'submit_button' => 'Επιβεβαίωση κωδικού',
    ],

    'failed' => 'Τα στοιχεία σύνδεσης δεν ταιριάζουν με τα αρχεία μας.',
    'password' => 'Ο κωδικός που εισάγατε είναι λανθασμένος.',
    'throttle' => 'Πολλές προσπάθειες σύνδεσης. Παρακαλώ δοκιμάστε ξανά σε :seconds δευτερόλεπτα.',
];
