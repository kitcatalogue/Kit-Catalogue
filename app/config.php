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



// IMPORTANT
// When updating to v2.0.0, your existing departments and organisations will be copied to your
// catalogue's new organisational tree structure.
//
// This initial structure will be pretty flat in nature, so if you want to delay using the
// organisational tree while your catalogue admins build your full tree, change this setting
// to false.
//
// We plan to remove the separate department and organisation records entirely in a future update,
// so you should switch to using the organisational tree soon.
$config['app.use_ou_tree'] = true;



/*
 * Application Paths
 */

$config['app.root'] = realpath(dirname(__FILE__) . '/../');   // The basis from which we work out the other paths
$config['app.include_root'] = dirname(__FILE__);

$config['app.local_root'] = $config['app.root'] . '/local';

$config['app.writable_root'] = $config['app.root'] . '/writable';
$config['app.upload_root'] = $config['app.writable_root'] . '/uploads';



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

// The current Kit-Catalogue software version.
// Do not override this setting as you could break future updates.
$config['app.version'] = '2.0.6';

// The Full URL that the catalogue will be served from (i.e. the browsable location of  /index.php)
// e.g. http://www.example.com/catalogue
// DO NOT include a trailing slash '/'
$config['app.www'] = 'http://www.example.com/catalogue';

// Should users signing in, or browsing internally, be switched to browsing over HTTPS?
// Essentially, once a user authenticates, app.www will be changed to using 'https://' instead of 'http://'.
// You must ensure you have the appropriate SSL certificates, etc, setup on your server.
// If you wish to use HTTPS for all access, set 'app.www' to use a HTTPS URL.
$config['app.use_https'] = false;

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

// Enabled use of the more modern MySQLi extension for database access.
// If disabled the old, and soon to be deprecated, PHP MySQL extension will be used.
// We recommend you ensure MySQLi is installed on your server, and leave this enabled.
$config['db.use_mysqli'] = true;

// Database connection settings
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
 * To override completely, set the "signin.use_ldap" and "signin.use_database" settings to false in local_config.php
 * and provide your own plugin function.
 *
 * To provide a custom fall-back, leave the settings enabled and your plugin function will be called if LDAP and Kit-Catalogue
 * database authentication fail.  See docs/plugins.txt for more information.
 */

// Enable LDAP / Active Directory authentication
// Note: Ensure you configure the connection in the "LDAP Settings" section below
$config['signin.use_ldap'] = true;

// Enable Database authentication
// If you're using LDAP, you can probably disable this.
// If you're NOT using LDAP, then you have little choice but to use it unless you supply your own plugin.
$config['signin.use_database'] = true;

// Enable Shibboleth SSO authentication
// Note: Ensure you configure the attribute mapping in the "Shibboleth Settings" section below
$config['signin.use_shibboleth'] = false;

// Only permit Shibboleth access
// If enabled the sign-in form will be hidden and no one can enter a username and password into the system.
// Access will only be possible via Shibboleth SSO.
$config['signin.use_shibboleth_only'] = false;

// Log all user sign-ins to the catalogue system
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
 * Shibboleth Settings
*
* As well as setting up shibboleth here, you may need to edit the /local/sso/.htaccess file that triggers the
* single sign-on process and supply your local handler URL.
*
* Please see /docs/shibboleth.txt for more information on setting up Shibboleth, and how the particulars of your
* SSO setup could limit how Kit-Catalogue works for your users.  Your local IT staff will need to setup your
* server for Shibboleth (e.g. Apache + mod_shib) and add your catalogue to your local SSO service provider.
*
* You must supply the config settings to map the fields in Kit-Catalogue's user information to the user attributes
* available in PHP's $_SERVER array.  The $_SERVER array is typically populated automatically by your server's
* Shibboleth setup when a user successfully logs in.
*
* The "shib.<field-name>.attr" setting should be the name of the attribute as returned in the $_SERVER array.
*
* If "shib.<field-name>.regex" is supplied, it will be used as a regular expression to extract the actual value
* from the raw attribute. If it is left blank, the raw value of the attribute will be used.
*/

// Required attributes
// At least one of these must be supplied by your SSO setup.

$config['shib.username.attr'] = 'eppn';
$config['shib.username.regex'] = '/^(.*)@/';

$config['shib.email.attr'] = 'mail';
$config['shib.email.regex'] = '';

// Optional attributes
// Depending on your SSO setup not all of these may be available but their use is not required.

$config['shib.id.attr'] = 'employeeNumber';
$config['shib.id.regex'] = '';

$config['shib.forename.attr'] = 'givenName';
$config['shib.forename.regex'] = '';

$config['shib.surname.attr'] = 'sn';
$config['shib.surname.regex'] = '';



/*
 * Item Settings
 */

// If enabled, the "embedded_content" field is available to item editors and
// things like youtube videos can be embedded directly in an item's details page.
// You can also use this field for any custom HTML content.
$config['item.allow_embedded_content'] = true;

// Use the lightbox functionality when showing item images.
// If disabled, images will open in a new window instead.
$config['item.allow_lightbox'] = true;

// Enter the maximum permitted dimensions of uploaded images in pixels.
// On upload, images exceeding one or more of these dimensions will be resized accordingly.
// Aspect ratios will be respected during resize.
// Use null for a dimension if there should be no limit.
// Although these settings default to off (null), using 600 pixels for each dimension would be a sensible limit.
$config['item.image.max_width'] = null;
$config['item.image.max_height'] = null;



/*
 * Import Settings
 */

// Should imported dates be parsed in US-style M/D/Y format.
// Any setting other than true will cause the importer to use D/M/Y format.
// To change the date format used for output, see 'layout.date_format'.
$config['import.date_format_mdy'] = false;



/*
 * Layout and Styling Settings
 */

// The direct layout template file-path
// The Kit-Catalogue default layout is '/app/layouts/kitcatalogue.phtml'
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
// By default, this is 'j\<\s\u\p\>S\<\/\s\u\p\> F, Y' which gives dates like 25th December, 2012.
// The ordinal suffix, e.g. 'th', will be in superscript.
$config['layout.date_format'] = 'j\<\s\u\p\>S\<\/\s\u\p\> F, Y';

// The formatting options to use when outputting dates in reports.
// By default, this is 'd-M-Y' which gives dates like 25 DEC 2012.
$config['layout.date_format_report'] = 'd M Y';

// Show the sign-in prompt to anonymous users on every page
$config['layout.signin_prompt_enabled'] = true;

// HTML message to show for the sign-in prompt.
// If you include the text  [[SIGNIN_URL]]  it will be replaced with the signin URL automatically.
// E.g.  '<a href="[[SIGNIN_URL]]">click here to login</a>' will give a working link to the signin page.
$config['layout.signin_prompt'] = 'You are currently viewing only those items made visible to the public. <a href="[[SIGNIN_URL]]" style="font-weight: bold;">Click here to sign in</a> and view the full catalogue.';



/*
 * Menu Settings
 */

// Order to display menu items
// Each item is identified by its default name
// Any items missing from the order list will be appended to the menu
// Whether an item is shown or not is controlled by the menu.___.enabled settings
$config['menu.order'] = array('home', 'category', 'department', 'ou', 'manufacturer', 'facility', 'tag');

// Home menu option
$config['menu.home.enabled'] = true;
$config['menu.home.label'] = 'Home';

// Categories menu option
$config['menu.category.enabled'] = true;
$config['menu.category.label'] = 'Category';

// Department menu option
$config['menu.department.enabled'] = true;
$config['menu.department.label'] = 'Departments';

// OU menu option
$config['menu.ou.enabled'] = true;
$config['menu.ou.label'] = 'Departments';

// Manufacturer menu option
$config['menu.manufacturer.enabled'] = true;
$config['menu.manufacturer.label'] = 'Manufacturer A-Z';

// Facility menu option
$config['menu.facility.enabled'] = false;
$config['menu.facility.label'] = 'Facilities';

// Tag menu option
$config['menu.tag.enabled'] = false;
$config['menu.tag.label'] = 'Tags';



/*
 * Administration Area Settings
 */

// Editors are users able to edit items with which they are associated.
// They're intended as a supplement to the staff contacts who can usually edit
// their own items.  (See the admin.item.edit options below).
// If you need a user to edit all items in an organisational unit, give them
// the administrator permission for that OU in the user area.
$config['admin.item.editors.enabled'] = false;

// If enabled, only system and OU administrators can assign editors to items.
// If disabled, anyone who can edit an item can add/remove other editors too.
$config['admin.item.editors.adminonly'] = true;

// These settings control whether the staff contacts for an item have editing rights
// If disabled (false) then custodians can still view the administrative item
// information, but not change it.
// By default, the editing is enabled (true).
$config['admin.item.edit.contact_1'] = true;
$config['admin.item.edit.contact_2'] = true;

// Defines where the administration area's user manual link goes.
// If left blank, no link will be shown.
// Defaults to the Kit-Catalogue user manual, http://kit-catalogue.lboro.ac.uk/project/software/docs/usermanual/
$config['admin.help.manual'] = 'http://kit-catalogue.lboro.ac.uk/project/software/docs/usermanual/';

// Define the text of the user manual link.
$config['admin.help.manual_title'] = 'Kit-Catalogue User Manual';



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

// Activate the recaptcha spam protection on enquiry forms
// If enabled, you must provide your recaptcha information below.
$config['enquiry.use_recaptcha'] = false;

// Log any enquiries made through the enquiry form.
$config['enquiry.log'] = true;



/*
 * reCAPTCHA Settings
 *
 * If you're using reCAPTCHA with your enquiry forms, you must sign up for a reCAPTCHA API key
 * for your local installation.
 *
 * Visit: http://recaptcha.net to sign up, and for more information.
 */

// The public key you were given as part of the reCAPTCHA registration.
// It will be used to identify your visitors to the reCAPTCHA API.
$config['recaptcha.public_key'] = '';

// The private key you were given as part of the reCAPTCHA registration.
// It will be used to identify your Kit-Catalogue system to the reCAPTCHA API.
$config['recaptcha.private_key'] = '';



/*
 * Browse Settings
 */

// Prioritise facility records
// If true, Facility/Parent items will be returned first.
$config['browse.prioritise_facilities'] = false;



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

// Include items where search terms match the associated organisational unit
$config['search.include_ou'] = true;

// If 'search.include_ou' is true, this setting controls whether searches should return
// all items from descendent OUs.
// e.g. a search for "science" could return all items in the OUs science/chemistry, science/physics, science/...
$config['search.include_ou_descendents'] = true;

// Include items where search terms match associated tags
$config['search.include_tags'] = true;

// Prioritise facility records
// If true, Facility/Parent items will be returned first.
$config['search.prioritise_facilities'] = false;



/*
 * Social Media and Networking Settings
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