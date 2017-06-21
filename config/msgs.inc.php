<?php
// All messages and errors

    // Messages
    /*define('MSG_LOGIN_OK',                              'Logged in successfully!');
    define('MSG_UPDATE_SUCCESSFUL',                     'Item(s) has been updated successfully!');
    define('MSG_ADDED_SUCCESSFULLY',                    'Item has been added successfully!');
    define('MSG_DELETED_SUCCESSFULLY',                  'Item has been deleted succesfully.');
    define('MSG_BUG_REP_SENT_SUCCESSFULLY',             'Bug report has been sent succesfully. We will have a look at the reported bug as soon as possible.');
    define('MSG_OLD_BROWSER',                           "<b>Notice:</b><br /> Your browser is not updated to the latest version. Due to this fact, some <b>functionality may not work 100%</b>. Please update your browser (at least to Internet Explorer 9.0), or use other modern browser (Google Chrome, Mozilla FireFox, etc.) for full functional compatibility.");
    define('MSG_TEMPLATE_CREATED',                      "Template created successfully.");
    define('MSG_TEMPLATE_DELETED',                      "Template deleted successfully.");
    define('MSG_TEMPLATE_CHANGED',                      "Template changed successfully.");
    define('MSG_TEMPLATE_BACKUP_NOT_CREATED',           "Unable to create template backup file. The template was not changed. Please contact developer.");
    define('MSG_PWD_CHANGE_OK',                         'Your password has been successfully changed!');
    define('MSG_POP_CREATED',                           "New PoP created successfully.");
    define('MSG_POP_DELETED',                           "PoP deleted successfully.");
    define('MSG_POP_SAVED',                             "PoP saved successfully.");
    define('MSG_MAILER_SENT',                           "E-mail notification has been sent.");
            
    // Errors
    define('ERR_LOGIN_FAILED',                          'Login failed! Check your username/password and try again.');
    define('ERR_LEFT_MENU_URL_PARAM_MISSING',           "Left menu not set, due to 1st URL parameter missing.");
    define('ERR_DB_CONNECT_FAILED',                     "Unable to connect to the database"); // 'db_name'
    define('ERR_SELECT_DB_FAILED',                      "Unable to select the database"); // 'db_name'
    define('ERR_DB_CHARSET_FAILED',                     "Unable to set the cahrset of the database connection.");
    define('ERR_DB_CONNECTION_UNKNOWN',                 "Non-existing database connection name tried to set as active.");
    define('ERR_DB_UNABLE_TO_PREPARE_SQL',              "Error while preparing SQL.");
    define('ERR_DB_BAD_INPUT_PARAMS',                   "Input parameters of the called function are in incorect format.");
    define('ERR_DB_ARGUMENTS_COUNT_MISMATCH',           "Number of variables in SQL doesn't match with the number of passed values.");
    define('ERR_DB_ARGUMENT_TYPE_ERROR',                "One or more variables in SQL query doesn't match its type(s).");
    define('ERR_DB_TABLES_COUNT_MISMATCH',              "Number of tables in SQL doesn't match with the number of passed table names.");
    define('ERR_DB_UNKOWN_DATA_TYPE',                   "Unknown data type encountered in database table.");
    define('ERR_NOT_AUTHORISED_ACCESS',                 'Not authorised access into called module or module functionality.');
    define('ERR_SQL_QUERY_FAILED',                      'SQL query failed!');
    define('ERR_MENU_ITEMS_LIST_EMPTY',                 'No menu items found.');
    define('ERR_TEMPLATE_LIST_EMPTY',                   'No templates found.');
    define('ERR_MODULE_NOT_RECOGNIZED_FROM_URL',        'Unexpected error! Invalid URL.');
    define('ERR_MODULE_NOT_RECOGNIZED',                 'Unexpected error! Unable to find module: ');
    define('ERR_INVALID_URL',                           'Unexpected error! Invalid URL! <br>Possible causes: number of arguments mismatch or called module do not exist.');
    define('ERR_SCREENSHOT_UPLOADER_VARIABLES_MISSING', 'Necessery variables are missing.');
    define('ERR_SCREENSHOT_UPLOADER_WRONG_IMG_TYPE',    "Wrong image type. Only PNG allowed (Max. 500kB).");
    define('ERR_MAILER_MISSING_VAR',                    'Unable to send email. One or more variables missing: ');
    define('ERR_MAILER_SENDING_FAILED',                 'An error occured while sending an email');
    define('ERR_BUG_REP_EMPTY',                         "Unable to send a bug report. No description, nor screenshot provided.");
    define('ERR_BUG_REP_NOT_SENT',                      'Error. Unable to send a bug report.');
    define('ERR_PWD_OLD',                               'Old password did not match current.');
    define('ERR_PWD_NEW',                               'New passwords did not match.');
    define('ERR_UKNOWN',                                "Uknown error. Please contact developer.");
    define('ERR_UKNOWN_TEMPLATE_DIR',                   "Uknown template directory.");
    define('ERR_DB_UNSUPPORTED_SQL_COMMAND',            "Unsupported SQL command. First statement in SQL query checked. Check if the right db method is used for desired SQL command.");
    define('ERR_TEMPLATE_CHANGE_UNSUCCESSFUL',          "Uknown error. Template was not changed.");
    define('ERR_CONF_STORAGE_VARIABLES_MISSING',        'Necessery variables are missing.');
    define('ERR_CONF_STORAGE_FILE_EXISTS',              "Unable to upload one or more files. File(s) with the same name already exist in the selected PoP.");
    define('ERR_NO_ITEM_FOUND',                         "No item with specified id found.");
    define('ERR_POP_SAVE_FAILED',                       "Unable to save PoP.");
    define('ERR_INPUT_DATA_MISSING',                    "Some input data are in wrong format, or missing.");
    define('ERR_TEMPLATE_VARIABLES_MISSING',            "Some template variables are missing in the generated configuration files.");
    define('ERR_NO_SAVED_CONFIG_FOUND',                 "Configuration not found in database. Possible reasons: the time span for reopening passed, or it's not your configuration.");
    define('ERR_CONFIG_LIST_EMPTY',                     'No configuration found.');
    define('ERR_ITEMS_LIST_EMPTY',                      'No item(s) found.');
        
    // Errors/messages used in JScript (will be automaticaly included in header.tpl)
    define('JS_ERR_MISSING_VALUES',                     'Missing or wrong value(s) of some variables.');
    define('JS_ERR_MISSING_FORM_FIELDS',                'Missing or wrong value(s) of some variables. Please check the highlighted inputs - red border.');
    define('JS_QUE_CONFIRM_DELETE',                     "Are you sure you want to DELETE selected item(s)?");
    define('JS_QUE_CONFIRM_ACTION',                     "Are you sure you want to execute this command? It might change/delete lot of data, which could be hard or even impossible to reverse.");
    define('JS_ERR_DELETE_BTN_ONLY_FOR_A',              "Css classes .delete-btn and .btn-confirm-needed is defined only for <A> links.");
    define('JS_MSG_BUG_REP_OLD_BROWSER',                "Unable to send bug report. Incompatible browser. Please upgrade your broser to version 9, or use other modern browsers (Chrome, Firefox, Safari, etc.).");
    define('JS_ERR_BUG_REP_DESC_EMPTY',                 'Briefly describe the bug you want to report.');
    define('JS_ERR_CONF_STORAGE_WRONG_FILE_TYPE',       "Wrong file type. Only TXT allowed (Max. size 5MB).");
    define('JS_ERR_WRONG_DATE_FORMAT',                  "Date range has wrong format. (e.g.: 2014-03-28)");
    define('JS_ERR_NOT_AUTHORISED_ACCESS',              "Not authorised access into called module or module functionality.");
    define('JS_NCG_MULTISAFI_SERVICE_RUNNING_NOTICE',   'One of the services is probably already running. Make sure that you select correct Primary Route Reflector (secondary RR is selected automaticaly). Primary and Secondary RR must match to existing configuration.');
    define('JS_TESTING_USERS_NOT_SELECTED',             'No testing user is selected.');*/
            
    
?>