<?php
define('HASHID_SALT','roster-designations-secret-KANHAI');

define('DB_HOST', $_SERVER['DB_HOST'] ?? 'localhost');
define('DB_USER', $_SERVER['DB_USER'] ?? 'root');
define('DB_PASS', $_SERVER['DB_PASS'] ?? 'MSkrishna@14');
define('DB_DATABASE', $_SERVER['DB_DATABASE'] ?? 'bindunamavali');

define('BASE_URL', $_SERVER['BASE_URL'] ?? 'http://c_office.local/secure/');
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT']);
define('SN_VISHANY_LENGTH', $_SERVER['SN_VISHANY_LENGTH'] ?? 2000);
define('SN_VISHANY_PDF_SIZE', $_SERVER['SN_VISHANY_PDF_SIZE'] ?? 5); // in MB
define('EMPLOYEE_REMARKS_LENGTH', $_SERVER['EMPLOYEE_REMARKS_LENGTH'] ?? 2000);
define('EMPLOYEE_PDF_SIZE', $_SERVER['EMPLOYEE_PDF_SIZE'] ?? 5); // MB
define('GOSHWARA_REMARK_LENGTH', $_SERVER['GOSHWARA_REMARK_LENGTH'] ?? 500);
define('POST_REMARK_LENTH', $_SERVER['POST_REMARK_LENTH'] ?? 500);

define('POST_SERVICE_PDF_SIZE', $_SERVER['POST_SERVICE_PDF_SIZE'] ?? 5);
define('POST_LAYOUT_PDF_SIZE', $_SERVER['POST_LAYOUT_PDF_SIZE'] ?? 5);
define('POST_GOSHWARA_PDF_SIZE', $_SERVER['POST_GOSHWARA_PDF_SIZE'] ?? 5);

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