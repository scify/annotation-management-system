<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Το πεδίο :attribute πρέπει να γίνει αποδεκτό.',
    'accepted_if' => 'Το πεδίο :attribute πρέπει να γίνει αποδεκτό όταν το :other είναι :value.',
    'active_url' => 'Το πεδίο :attribute πρέπει να είναι έγκυρο URL.',
    'after' => 'Το πεδίο :attribute πρέπει να είναι ημερομηνία μετά από :date.',
    'after_or_equal' => 'Το πεδίο :attribute πρέπει να είναι ημερομηνία ίση ή μετά από :date.',
    'alpha' => 'Το πεδίο :attribute πρέπει να περιέχει μόνο γράμματα.',
    'alpha_dash' => 'Το πεδίο :attribute πρέπει να περιέχει μόνο γράμματα, αριθμούς, παύλες και κάτω παύλες.',
    'alpha_num' => 'Το πεδίο :attribute πρέπει να περιέχει μόνο γράμματα και αριθμούς.',
    'array' => 'Το πεδίο :attribute πρέπει να είναι πίνακας.',
    'ascii' => 'Το πεδίο :attribute πρέπει να περιέχει μόνο μονο-byte αλφαριθμητικούς χαρακτήρες και σύμβολα.',
    'before' => 'Το πεδίο :attribute πρέπει να είναι ημερομηνία πριν από :date.',
    'before_or_equal' => 'Το πεδίο :attribute πρέπει να είναι ημερομηνία ίση ή πριν από :date.',
    'between' => [
        'array' => 'Το πεδίο :attribute πρέπει να έχει μεταξύ :min και :max στοιχεία.',
        'file' => 'Το πεδίο :attribute πρέπει να είναι μεταξύ :min και :max kilobytes.',
        'numeric' => 'Το πεδίο :attribute πρέπει να είναι μεταξύ :min και :max.',
        'string' => 'Το πεδίο :attribute πρέπει να είναι μεταξύ :min και :max χαρακτήρων.',
    ],
    'boolean' => 'Το πεδίο :attribute πρέπει να είναι αληθές ή ψευδές.',
    'can' => 'Το πεδίο :attribute περιέχει μη εξουσιοδοτημένη τιμή.',
    'confirmed' => 'Η επιβεβαίωση του πεδίου :attribute δεν ταιριάζει.',
    'contains' => 'Το πεδίο :attribute δεν περιέχει μια απαιτούμενη τιμή.',
    'current_password' => 'Ο κωδικός πρόσβασης είναι λανθασμένος.',
    'date' => 'Το πεδίο :attribute πρέπει να είναι έγκυρη ημερομηνία.',
    'date_equals' => 'Το πεδίο :attribute πρέπει να είναι ημερομηνία ίση με :date.',
    'date_format' => 'Το πεδίο :attribute πρέπει να ταιριάζει με τη μορφή :format.',
    'decimal' => 'Το πεδίο :attribute πρέπει να έχει :decimal δεκαδικά ψηφία.',
    'declined' => 'Το πεδίο :attribute πρέπει να απορριφθεί.',
    'declined_if' => 'Το πεδίο :attribute πρέπει να απορριφθεί όταν το :other είναι :value.',
    'different' => 'Το πεδίο :attribute και το :other πρέπει να είναι διαφορετικά.',
    'digits' => 'Το πεδίο :attribute πρέπει να είναι :digits ψηφία.',
    'digits_between' => 'Το πεδίο :attribute πρέπει να είναι μεταξύ :min και :max ψηφίων.',
    'dimensions' => 'Το πεδίο :attribute έχει μη έγκυρες διαστάσεις εικόνας.',
    'distinct' => 'Το πεδίο :attribute έχει διπλότυπη τιμή.',
    'doesnt_end_with' => 'Το πεδίο :attribute δεν πρέπει να τελειώνει με ένα από τα εξής: :values.',
    'doesnt_start_with' => 'Το πεδίο :attribute δεν πρέπει να αρχίζει με ένα από τα εξής: :values.',
    'email' => 'Το πεδίο :attribute πρέπει να είναι έγκυρη διεύθυνση email.',
    'ends_with' => 'Το πεδίο :attribute πρέπει να τελειώνει με ένα από τα εξής: :values.',
    'enum' => 'Η επιλεγμένη τιμή για το :attribute δεν είναι έγκυρη.',
    'exists' => 'Η επιλεγμένη τιμή για το :attribute δεν είναι έγκυρη.',
    'extensions' => 'Το πεδίο :attribute πρέπει να έχει μία από τις εξής επεκτάσεις: :values.',
    'file' => 'Το πεδίο :attribute πρέπει να είναι αρχείο.',
    'filled' => 'Το πεδίο :attribute πρέπει να έχει τιμή.',
    'gt' => [
        'array' => 'Το πεδίο :attribute πρέπει να έχει περισσότερα από :value στοιχεία.',
        'file' => 'Το πεδίο :attribute πρέπει να είναι μεγαλύτερο από :value kilobytes.',
        'numeric' => 'Το πεδίο :attribute πρέπει να είναι μεγαλύτερο από :value.',
        'string' => 'Το πεδίο :attribute πρέπει να είναι μεγαλύτερο από :value χαρακτήρες.',
    ],
    'gte' => [
        'array' => 'Το πεδίο :attribute πρέπει να έχει :value ή περισσότερα στοιχεία.',
        'file' => 'Το πεδίο :attribute πρέπει να είναι μεγαλύτερο ή ίσο με :value kilobytes.',
        'numeric' => 'Το πεδίο :attribute πρέπει να είναι μεγαλύτερο ή ίσο με :value.',
        'string' => 'Το πεδίο :attribute πρέπει να είναι μεγαλύτερο ή ίσο με :value χαρακτήρες.',
    ],
    'hex_color' => 'Το πεδίο :attribute πρέπει να είναι έγκυρο δεκαεξαδικό χρώμα.',
    'image' => 'Το πεδίο :attribute πρέπει να είναι εικόνα.',
    'in' => 'Η επιλεγμένη τιμή για το :attribute δεν είναι έγκυρη.',
    'in_array' => 'Το πεδίο :attribute πρέπει να υπάρχει στο :other.',
    'integer' => 'Το πεδίο :attribute πρέπει να είναι ακέραιος αριθμός.',
    'ip' => 'Το πεδίο :attribute πρέπει να είναι έγκυρη διεύθυνση IP.',
    'ipv4' => 'Το πεδίο :attribute πρέπει να είναι έγκυρη διεύθυνση IPv4.',
    'ipv6' => 'Το πεδίο :attribute πρέπει να είναι έγκυρη διεύθυνση IPv6.',
    'json' => 'Το πεδίο :attribute πρέπει να είναι έγκυρη συμβολοσειρά JSON.',
    'list' => 'Το πεδίο :attribute πρέπει να είναι λίστα.',
    'lowercase' => 'Το πεδίο :attribute πρέπει να είναι πεζά γράμματα.',
    'lt' => [
        'array' => 'Το πεδίο :attribute πρέπει να έχει λιγότερα από :value στοιχεία.',
        'file' => 'Το πεδίο :attribute πρέπει να είναι μικρότερο από :value kilobytes.',
        'numeric' => 'Το πεδίο :attribute πρέπει να είναι μικρότερο από :value.',
        'string' => 'Το πεδίο :attribute πρέπει να είναι μικρότερο από :value χαρακτήρες.',
    ],
    'lte' => [
        'array' => 'Το πεδίο :attribute δεν πρέπει να έχει περισσότερα από :value στοιχεία.',
        'file' => 'Το πεδίο :attribute πρέπει να είναι μικρότερο ή ίσο με :value kilobytes.',
        'numeric' => 'Το πεδίο :attribute πρέπει να είναι μικρότερο ή ίσο με :value.',
        'string' => 'Το πεδίο :attribute πρέπει να είναι μικρότερο ή ίσο με :value χαρακτήρες.',
    ],
    'mac_address' => 'Το πεδίο :attribute πρέπει να είναι έγκυρη διεύθυνση MAC.',
    'max' => [
        'array' => 'Το πεδίο :attribute δεν πρέπει να έχει περισσότερα από :max στοιχεία.',
        'file' => 'Το πεδίο :attribute δεν πρέπει να είναι μεγαλύτερο από :max kilobytes.',
        'numeric' => 'Το πεδίο :attribute δεν πρέπει να είναι μεγαλύτερο από :max.',
        'string' => 'Το πεδίο :attribute δεν πρέπει να είναι μεγαλύτερο από :max χαρακτήρες.',
    ],
    'max_digits' => 'Το πεδίο :attribute δεν πρέπει να έχει περισσότερα από :max ψηφία.',
    'mimes' => 'Το πεδίο :attribute πρέπει να είναι αρχείο τύπου: :values.',
    'mimetypes' => 'Το πεδίο :attribute πρέπει να είναι αρχείο τύπου: :values.',
    'min' => [
        'array' => 'Το πεδίο :attribute πρέπει να έχει τουλάχιστον :min στοιχεία.',
        'file' => 'Το πεδίο :attribute πρέπει να είναι τουλάχιστον :min kilobytes.',
        'numeric' => 'Το πεδίο :attribute πρέπει να είναι τουλάχιστον :min.',
        'string' => 'Το πεδίο :attribute πρέπει να είναι τουλάχιστον :min χαρακτήρες.',
    ],
    'min_digits' => 'Το πεδίο :attribute πρέπει να έχει τουλάχιστον :min ψηφία.',
    'missing' => 'Το πεδίο :attribute δεν πρέπει να υπάρχει.',
    'missing_if' => 'Το πεδίο :attribute δεν πρέπει να υπάρχει όταν το :other είναι :value.',
    'missing_unless' => 'Το πεδίο :attribute δεν πρέπει να υπάρχει εκτός αν το :other είναι :value.',
    'missing_with' => 'Το πεδίο :attribute δεν πρέπει να υπάρχει όταν υπάρχει το :values.',
    'missing_with_all' => 'Το πεδίο :attribute δεν πρέπει να υπάρχει όταν υπάρχουν τα :values.',
    'multiple_of' => 'Το πεδίο :attribute πρέπει να είναι πολλαπλάσιο του :value.',
    'not_in' => 'Η επιλεγμένη τιμή για το :attribute δεν είναι έγκυρη.',
    'not_regex' => 'Η μορφή του πεδίου :attribute δεν είναι έγκυρη.',
    'numeric' => 'Το πεδίο :attribute πρέπει να είναι αριθμός.',
    'password' => [
        'letters' => 'Το πεδίο :attribute πρέπει να περιέχει τουλάχιστον ένα γράμμα.',
        'mixed' => 'Το πεδίο :attribute πρέπει να περιέχει τουλάχιστον ένα κεφαλαίο και ένα πεζό γράμμα.',
        'numbers' => 'Το πεδίο :attribute πρέπει να περιέχει τουλάχιστον έναν αριθμό.',
        'symbols' => 'Το πεδίο :attribute πρέπει να περιέχει τουλάχιστον ένα σύμβολο.',
        'uncompromised' => 'Το :attribute εμφανίστηκε σε διαρροή δεδομένων. Παρακαλώ επιλέξτε διαφορετικό :attribute.',
    ],
    'present' => 'Το πεδίο :attribute πρέπει να υπάρχει.',
    'present_if' => 'Το πεδίο :attribute πρέπει να υπάρχει όταν το :other είναι :value.',
    'present_unless' => 'Το πεδίο :attribute πρέπει να υπάρχει εκτός αν το :other είναι :value.',
    'present_with' => 'Το πεδίο :attribute πρέπει να υπάρχει όταν υπάρχει το :values.',
    'present_with_all' => 'Το πεδίο :attribute πρέπει να υπάρχει όταν υπάρχουν τα :values.',
    'prohibited' => 'Το πεδίο :attribute δεν επιτρέπεται.',
    'prohibited_if' => 'Το πεδίο :attribute δεν επιτρέπεται όταν το :other είναι :value.',
    'prohibited_if_accepted' => 'Το πεδίο :attribute δεν επιτρέπεται όταν το :other γίνεται αποδεκτό.',
    'prohibited_if_declined' => 'Το πεδίο :attribute δεν επιτρέπεται όταν το :other απορρίπτεται.',
    'prohibited_unless' => 'Το πεδίο :attribute δεν επιτρέπεται εκτός αν το :other βρίσκεται στα :values.',
    'prohibits' => 'Το πεδίο :attribute απαγορεύει την παρουσία του :other.',
    'regex' => 'Η μορφή του πεδίου :attribute δεν είναι έγκυρη.',
    'required' => 'Το πεδίο :attribute είναι υποχρεωτικό.',
    'required_array_keys' => 'Το πεδίο :attribute πρέπει να περιέχει καταχωρίσεις για: :values.',
    'required_if' => 'Το πεδίο :attribute είναι υποχρεωτικό όταν το :other είναι :value.',
    'required_if_accepted' => 'Το πεδίο :attribute είναι υποχρεωτικό όταν το :other γίνεται αποδεκτό.',
    'required_if_declined' => 'Το πεδίο :attribute είναι υποχρεωτικό όταν το :other απορρίπτεται.',
    'required_unless' => 'Το πεδίο :attribute είναι υποχρεωτικό εκτός αν το :other βρίσκεται στα :values.',
    'required_with' => 'Το πεδίο :attribute είναι υποχρεωτικό όταν υπάρχει το :values.',
    'required_with_all' => 'Το πεδίο :attribute είναι υποχρεωτικό όταν υπάρχουν τα :values.',
    'required_without' => 'Το πεδίο :attribute είναι υποχρεωτικό όταν δεν υπάρχει το :values.',
    'required_without_all' => 'Το πεδίο :attribute είναι υποχρεωτικό όταν δεν υπάρχει κανένα από τα :values.',
    'same' => 'Το πεδίο :attribute πρέπει να ταιριάζει με το :other.',
    'size' => [
        'array' => 'Το πεδίο :attribute πρέπει να περιέχει :size στοιχεία.',
        'file' => 'Το πεδίο :attribute πρέπει να είναι :size kilobytes.',
        'numeric' => 'Το πεδίο :attribute πρέπει να είναι :size.',
        'string' => 'Το πεδίο :attribute πρέπει να είναι :size χαρακτήρες.',
    ],
    'starts_with' => 'Το πεδίο :attribute πρέπει να αρχίζει με ένα από τα εξής: :values.',
    'string' => 'Το πεδίο :attribute πρέπει να είναι συμβολοσειρά.',
    'timezone' => 'Το πεδίο :attribute πρέπει να είναι έγκυρη ζώνη ώρας.',
    'unique' => 'Το :attribute έχει ήδη χρησιμοποιηθεί.',
    'uploaded' => 'Το :attribute απέτυχε να ανεβεί.',
    'uppercase' => 'Το πεδίο :attribute πρέπει να είναι κεφαλαία γράμματα.',
    'url' => 'Το πεδίο :attribute πρέπει να είναι έγκυρο URL.',
    'ulid' => 'Το πεδίο :attribute πρέπει να είναι έγκυρο ULID.',
    'uuid' => 'Το πεδίο :attribute πρέπει να είναι έγκυρο UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'captcha' => [
            'required' => 'Παρακαλώ επιβεβαιώστε ότι δεν είστε ρομπότ.',
            'captcha' => 'Η επαλήθευση CAPTCHA απέτυχε. Παρακαλώ δοκιμάστε ξανά.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
