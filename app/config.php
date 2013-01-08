<?php
/**
 * Kit-Catalogue Default Application Config.
 *
 * Future updates to Kit-Catalogue may overwrite this config file.
 *
 * Do not edit the settings in here, instead edit and override the settings in local/local_config.php
 */



/*
 * Installation Settings
 */


// Setting this to false will lockout the installation wizard.
// Even if you have deleted your /install/ folder, you should set this to false as if you
// later upgrade or reinstall it will recreate a new /install/ folder.
$config['install.enabled'] = false;



/*
 * Application Paths
 */


$config['app.root'] = realpath(dirname(__FILE__) . '/../');   // The basis from which we work out the other paths
$config['app.include_root'] = dirname(__FILE__);

$config['app.local_root'] = $config['app.root'] . '/local';

$config['app.writable_root'] = $config['app.root'] . '/writable';
$config['app.upload_root'] = $config['app.writable_root'] . '/uploads';


// Relative Browsable Paths

$config['app.api_www'] = '/data';

$config['app.items_www'] = '/writable/uploads/items';



/*
 * Your Organisation's Details
 */


// The title to use when showing your organisation's name
$config['org.title'] = 'Our organisation';

// The full URL of your organisation's main website
// e.g. http://www.example.com
$config['org.www'] = 'http://www.example.com/';

// The file name of your organisation's logo
// If a logo is given, Kit-Catalogue will look for /local/images/<logo-filename>
// Leave this blank to ignore
$config['org.logo'] = '';



/*
 * Application Settings
 */


// Enable debugging mode.
// When enabled, verbose error messages and other system features useful for testing are activated.
// WARNING : Do not leave this setting enabled on a public-facing site.
$config['app.debug'] = false;


$config['app.version'] = '1.1.0';


// The Full URL that the catalogue will be served from (i.e. the browsable location of  /index.php)
// e.g. http://www.example.com/catalogue
// DO NOT include a trailing slash '/'
$config['app.www'] = 'http://www.example.com/catalogue';

// The title of your Kit-Catalogue installation
$config['app.title'] = 'Kit-Catalogue';

// The introductory text to show on the home page.
// The text will be interpreted as HTML.
$config['app.intro'] = "A catalogue of all the equipment and facilities available at our institution.";

// The contact email address of the catalogue owner
$config['app.email.owner'] = '';

// The contact email address offering support for catalogue users
$config['app.email.support'] = '';

// Allow anonymous (non-signed in) access to the catalogue.
// If enabled, anonymous users can still only see items marked "public"
// If this is disabled, all users must sign on before they can see anything in the catalogue.
$config['app.allow_anonymous'] = false;

// Use this setting to change the PHP session-key under which authentication user data is stored.
// You only really need to change this if you are hosting two separate kit-catalogue installations
// on the same server, and wish to keep the sign-ins entirely separate.
$config['app.user_session_var'] = '_user_data';



/*
 * Database connection settings for MySQL
 */

$config['db.host'] = 'localhost';
$config['db.port'] = 3308;
$config['db.username'] = 'kc-username';
$config['db.password'] = 'kc-password';
$config['db.database'] = 'kc-database';



/*
 * Sign-In Settings
 *
 * Note: You can use a local-plugin function to override, or provide fall-back for,  the built-in
 * authentication methods below.
 *
 * To override, set the "signin.use_ldap" and "signin.use_database" settings to false in local_config.php
 * and provide your own plugin function.
 *
 * To provide fall-back, leave the settings enabled and your plugin function will be called if LDAP and Kit-Catalogue
 * database authentication fail.
 */

// Enable LDAP / Active Directory authentication
// Note: Ensure you configure your LDAP connection using the "LDAP Settings" section below
$config['signin.use_ldap'] = true;

// Enable Database authentication
$config['signin.use_database'] = false;

// Log all user sign-ins
$config['signin.log'] = true;



/*
 * LDAP Settings
 */

$config['ldap.host'] = 'ldap.example.com';
$config['ldap.port'] = '389';
$config['ldap.dn'] = 'dc=example, dc=com';
$config['ldap.username_suffix'] = '@example.com';
$config['ldap.options'] = array (
	LDAP_OPT_PROTOCOL_VERSION  => 3 ,   // Set the version of LDAP that we will be using
	LDAP_OPT_REFERRALS         => 0 ,   // Set this option to 0 to cope with Windows Server 2003 Active Directories
);

// Set to true to secure the LDAP connection (using start-TLS).
// Note, to have secure LDAP work correctly, you will need to configure your server's LDAP extension to either:
// (a) Use the correct certificate.
// (b) Disable certificate checking altogether (use "TLS_REQCERT never" in ldap.conf).
// For more info, see:  http://www.php.net/manual/en/function.ldap-start-tls.php
$config['ldap.use_secure'] = false;



/*
 * Layout and Styling Settings
 */

// The direct layout template file-path
// The Kit-Catalogue default layout is '/app/layouts/default.phtml'
// Provide a path to a template file to override the default layout.
// e.g. '/local/layouts/mytemplate.phtml' would cause Kit-Catalogue to use your local 'mytemplate.phtml' file.
$config['layout.template_file'] = null;

// The named layout template to use - by default, looks for templates in /app/layouts/
// Local configs should override 'layout.template_file' rather than change this value.
$config['layout.default'] = 'default';

// Should the system automatically include local/css/local.css
$config['layout.use_local_css'] = true;

// Should the system automatically include local/local_head.html
// Use this setting and the local_head.html file to include scripts (such as Google Analytics)
$config['layout.use_local_head'] = true;

// The formatting options to use when outputting dates (e.g. Last updated)
$config['layout.date_format'] = 'j\<\s\u\p\>S\<\/\s\u\p\> F, Y';

// Show the sign-in prompt to anonymous users on every page
$config['layout.signin_prompt_enabled'] = true;



/*
 * Data Logging
 */

// Log views of individual item detail pages
$config['log.item_view'] = true;



/*
 * Item Enquiries
 */

// Enable the enquiry form
// Regardless of this setting, users can always read the custodians' contact
// information from the item details page.
// By default, enquiry form submissions are emailed to the custodians' email addresses.
$config['enquiry.enabled'] = true;

// An email address which will overrides the custodian addresses,
// and will be the only address that receives enquiry form submissions.
// Use this setting if you wish all enquiries to go to a central point email address.
// We suggest this setting uses an email address accessible to numerous staff, so
// enquiries do not go unanswered.
$config['enquiry.send_to'] = '';

// An email address to automatically BCC enquiry form submissions to.
// Use this if you wish to monitor enquiries from users to item custodians.
// This setting will also work with the $config['enquiry.send_to'] setting.
$config['enquiry.bcc'] = '';



/*
 * Search Settings
 */

// Include items where search terms match associated categories
$config['search.include_categories'] = true;

// Include items where search terms match the custodian name or email addresses
$config['search.include_custodians'] = true;

// Include items where search terms match custom field values
// You can't limit which custom fields are used, it's all or nothing.
$config['search.include_custom_fields'] = false;

// Include items where search terms match the associated department
$config['search.include_departments'] = false;

// Include items where search terms match associated tags
$config['search.include_tags'] = true;



/*
 * Social Networking Settings
 */

// Enable the Google Plus button
$config['socialnetwork.allow_googleplus'] = false;

// Enable the Tweet this button
$config['socialnetwork.allow_twitter'] = false;



/*
 * API Settings
 *
 * see docs/api.txt for more information.
 */

// Enables the API.
// If false, all API calls are disabled.
$config['api.enabled'] = false;

// Controls access to information about those items you have made public in the catalogue.
// The public API is not protected by any API key.
$config['api.public.enabled'] = true;

// NOTE : At the moment this setting has no effect (item collections are not implemented).
// Controls access to private collections of items (regardless of public/internal visibility).
// Access to each collection is controlled by its own API key (essentially a password).
// If using the private API, you should enforce a HTTPS connection to encrypt access.
$config['api.private.enabled'] = false;

// NOTE : At the moment this setting has no effect.
// Forces all API calls to go via HTTPS, and therefore be encrypted.
$config['api.use_https_only'] = false;



/*
 * Data Licensing and Settings
 */

// Show the data licence information.
// If true, the site/API will show "This data is licensed under the <licence name>"
// Where <license name> is the text and link given below.
// If false, no licensing name or link will be shown.
$config['data.licence_enabled'] = false;

// The name of the data licence being used.
// If blank, no licensing name or link will be shown.
// By default, this is the UK's Open Government Licence
$config['data.licence_name'] = 'Open Government Licence';

// The link URL to the full licence text.
// If blank, only the licence's name will be shown.
// By default, this is the URL of the UK's Open Government Licence:
// http://www.nationalarchives.gov.uk/doc/open-government-licence/
$config['data.licence_link'] = 'http://www.nationalarchives.gov.uk/doc/open-government-licence/';



?>