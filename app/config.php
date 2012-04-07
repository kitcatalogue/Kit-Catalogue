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

$config['app.root'] = realpath(__DIR__ . '/../');   // The basis from which we work out the other paths
$config['app.include_root'] = __DIR__;

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


$config['app.version'] = '0.9.7';


// The Full URL that the catalogue will be served from (i.e. the browsable location of  /index.php)
// e.g. http://www.example.com/catalogue
// DO NOT include a trailing slash '/'
$config['app.www'] = '/catalogue';

// The title of your Kit-Catalogue installation
$config['app.title'] = 'Kit-Catalogue';

// The introductory text to show on the home page.
$config['app.intro'] = "A catalogue of all the equipment and facilities available at our institution.";

// The contact email address of the catalogue webmaster
$config['app.email'] = '';

// Allow anonymous (non-signed in) access to the catalogue.
// If enabled, anonymous users can still only see items marked "public"
// If this is disabled, all users must sign on before they can see anything in the catalogue.
$config['app.allow_anonymous'] = false;



/*
 * Database connection settings for MySQL
 */

$config['db.host'] = 'localhost';
$config['db.port'] = 3308;
$config['db.username'] = 'kitcatalogue';
$config['db.password'] = 'kc1234';
$config['db.database'] = 'kitcatalogue';



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



/*
 * Data Logging
 */

// Log views of individual item detail pages
$config['log.item_view'] = true;



/*
 * Linked Data and API Settings
 */

// Enables the API and makes it available to the public (see docs/api.txt for more info)
// At the moment there is no API, so this is disabled by default.
$config['api.enabled'] = false;



?>