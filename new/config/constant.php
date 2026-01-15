<?php
define('HASHID_SALT','roster-designations-secret-KANHAI');
define('BASE_URL', 'http://c_office.local/new/');
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT']);
define('ALLOWED_PAGES', [
    'login',
    'logout',
    'registration',
    'profile',
    'change-password',
    'dashboard',
    'access_logs',
    'login_history',
    'create_post',
    'users_list',
    'designations',
    'designations-add',
    'designations-edit',
    'employees',
    'employees-add',
    'employees-edit',
    'organisations-post',
    'organisations-post-add',
    'organisations-post-view',
    'goshwara',
    'goshwara-add',
    'goshwara-edit',
    'all-designations',
    'users-designations',
    'users-employees',
    'shasan-nirnay',
    'shasan-nirnay-add',
    'shasan-nirnay-edit',
]);
define('SN_VISHANY_LENGTH', 2000);
define('SN_VISHANY_PDF_SIZE', 5); // in MB