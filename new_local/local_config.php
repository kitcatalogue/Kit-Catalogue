<?php
/*
 * Local Application Settings
 *
 * Make changes here, and they will override the default application settings.
 */



/*
 * Installation Lock
 */


// Setting this to false will lockout the installation wizard.
// Even if you have deleted your /install/ folder, you should set this to false as if you
// later upgrade or reinstall Kit-Catalogue a new /install/ folder will be created.
$config['install.enabled'] = true;



/*
 * Your Organisation's Details
 */


// The title to use when showing your organisation's name
$config['org.title'] = 'Kit-Catalogue';

// The full URL of your organisation's main website
// e.g. http://www.example.com
$config['org.www'] = 'http://www.example.com';

// The file name of your organisation's logo
// If a logo is given, Kit-Catalogue will look for /local/images/<logo-filename>
// Leave this blank to ignore
$config['org.logo'] = '';



/*
 * Application Settings
 */


// Enable debugging mode.
// When enabled, verbose error messages and other system features useful for testing are activated.
// WARNING : We advise disabling this setting on a public-facing site.
$config['app.debug'] = false;

// The Full URL that the catalogue will be served from (i.e. the browsable location of  /index.php)
// e.g. http://www.example.com/catalogue
// DO NOT include a trailing slash '/'
// For SSL, use a URL like: https://www.example.com/catalogue
$config['app.www'] = '';

// The title of your Kit-Catalogue installation
// e.g.  $config['app.title'] = "Loughborough University's Equipment Database";
$config['app.title'] = 'Our Kit-Catalogue';

// The introductory text to show on the home page.
// The text will be interpreted as HTML.
$config['app.intro'] = "A catalogue of all the equipment and facilities available at our institution.";

// The contact email address of the catalogue webmaster
$config['app.email'] = 'someone@example.com';

// Allow anonymous access to the catalogue
// Anonymous users who haven't signed on, can only see items marked "public" in the catalogue.
// If this setting is disabled, all users must sign on before they can see anything.
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
 * Note: You can use a local-plugin function to override, or provide fall-back for, the built-in
 * authentication methods below.
 *
 * To override completely, set the "signin.use_ldap" and "signin.use_database" settings to false in local_config.php
 * and provide your own plugin function.
 *
 * To provide a custom fall-back, leave the settings enabled and your plugin function will be called if LDAP and Kit-Catalogue
 * database authentication fail.
 *
 * See docs/plugins.txt for more information.
 */

// Enable LDAP / Active Directory authentication
// Note: Ensure you configure the connection in the "LDAP Settings" section below
$config['signin.use_ldap'] = false;

// Enable Database authentication
// If you're using LDAP, you can probably disable this.
// If you're NOT using LDAP, then you have little choice but to use it unless you supply your own plugin.
$config['signin.use_database'] = true;

// Log all user sign-ins to the catalogue system
$config['signin.log'] = true;



/*
 * LDAP Settings
 */

$config['ldap.host'] = 'LDAPServer';
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
 * Layout Settings
 */

// Layout template file path.
// Override the default layout template by providing your own.
// If a template file is given, Kit-Catalogue will look for /local/layouts/<template-filename>
$config['layout.template_file'] = '';

// Should the system automatically include local/css/local.css
$config['layout.use_local_css'] = true;

// Should the system automatically include local/local_head.html
// Use this setting and the local_head.html file to include scripts (such as Google Analytics)
$config['layout.use_local_head'] = true;

// Show the sign-in prompt to anonymous users
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
$config['search.include_custom_fields'] = true;

// Include items where search terms match the associated department
$config['search.include_departments'] = true;

// Include items where search terms match associated tags
$config['search.include_tags'] = true;



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
// By default, this is the UK Open Government Licence
$config['data.licence_name'] = 'UK Open Government Licence';

// The link URL to the full licence text.
// If blank, only the licence's name will be shown.
// By default, this is the URL of the UK's Open Government Licence:
// http://www.nationalarchives.gov.uk/doc/open-government-licence/
$config['data.licence_link'] = 'http://www.nationalarchives.gov.uk/doc/open-government-licence/';


