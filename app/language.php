<?php
/**
 * Kit-Catalogue Default Language File.
 *
 * Future updates to Kit-Catalogue may overwrite this config file.
 *
 * Do not edit the settings in here, instead edit and override the settings in local/local_language.php
 */



/*
 * Access Levels
 */

$lang['access.label'] = 'Access Level';
$lang['access.label.plural'] = 'Access Levels';



/*
 * Building
 */

$lang['building.label'] = 'Building';
$lang['building.label.plural'] = 'Buildings';



/*
 * Category
 */

// The route MUST be a single, lowercase word suitable for inclusion in a URL
// The route MUST NOT clash with the name of another entity in kit-catalogue, such as 'category' or 'item'.
// The route MUST contain only alphanumeric characters, i.e. Only a-z or 0-9.
// For example, you could use "department" (the default) or "school".
$lang['cat.route'] = 'category';

$lang['cat.label'] = 'Category';
$lang['cat.label.plural'] = 'Categories';



/*
 * Department
 */

// The route MUST be a single, lowercase word suitable for inclusion in a URL
// The route MUST NOT clash with the name of another entity in kit-catalogue, such as 'category' or 'item'.
// The route MUST contain only alphanumeric characters, i.e. Only a-z or 0-9.
// For example, you could use "department" (the default) or "school".
$lang['dept.route'] = 'department';

$lang['dept.label'] = 'Department';
$lang['dept.label.plural'] = 'Departments';



/*
 * Item Form Fields (as used in the item editor)
 * Some fields use the labels defined elsewhere, e.g. 'cat.label' or 'dept.label'.
 */

$lang['item.formsection.main'] = 'Main Details';
$lang['item.formsection.parent'] = 'Parent Facility';
$lang['item.formsection.categorisation'] = 'Categorisation';
$lang['item.formsection.access'] = 'Access & Usage';
$lang['item.formsection.contact'] = 'Contact Information';
$lang['item.formsection.location'] = 'Location';
$lang['item.formsection.asset'] = 'Asset & Finance Information';
$lang['item.formsection.custom'] = 'Custom Fields';
$lang['item.formsection.files'] = 'Images & Files';



$lang['item.form.title'] = 'Title';
$lang['item.form.manufacturer'] = 'Manufacturer';
$lang['item.form.model'] = 'Model';
$lang['item.form.short_description'] = 'Short Description';
$lang['item.form.full_description'] = 'Full Description';
$lang['item.form.specification'] = 'Specification';

$lang['item.form.upgrades'] = 'Upgrades';
$lang['item.form.future_upgrades'] = 'Future Upgrades';

$lang['item.form.manufacturer_website'] = 'Manufacturer\'s Website';
$lang['item.form.technique'] = 'Technique';
$lang['item.form.keywords'] = 'Keywords';
$lang['item.form.acronym'] = 'Acronym';

$lang['item.form.is_parent'] = "Use this item as a parent facility";
$lang['item.form.selectparent'] = 'Associate this item with one or more parent facilities';
$lang['item.form.showchildren'] = 'Associated child items';

$lang['item.form.visibility'] = 'Visibility';
$lang['item.form.usergroup'] = 'User Group';
$lang['item.form.availability'] = 'Availability';
$lang['item.form.restrictions'] = 'Restrictions';

$lang['item.form.portability'] = 'Portability';

$lang['item.form.trainingrequired'] = 'Training Required';
$lang['item.form.trainingprovided'] = 'Training Provided';

$lang['item.form.calibrated'] = 'Calibrated';
$lang['item.form.last_calibration_date'] = 'Last Calibration Date';
$lang['item.form.next_calibration_date'] = 'Next Calibration Date';

$lang['item.form.quantity'] = 'Quantity';
$lang['item.form.quantity_detail'] = 'Quantity Detail';


$lang['item.form.contact_1'] = 'First Staff Contact';
$lang['item.form.contact_2'] = 'Second Staff Contact';


$lang['item.form.room'] = 'Room';


$lang['item.form.asset_no'] = 'Asset Number';
$lang['item.form.finance_id'] = 'Finance ID / Order Reference';
$lang['item.form.serial_no'] = 'Serial Number';
$lang['item.form.year_of_manufacture'] = 'Year of Manufacture';
$lang['item.form.supplier'] = 'Supplier';
$lang['item.form.date_of_purchase'] = 'Date of Purchase';
$lang['item.form.PAT'] = 'PAT Expiry Date';

$lang['item.form.cost'] = 'Purchase Cost';
$lang['item.form.replacement_cost'] = 'Replacement Cost';
$lang['item.form.end_of_life'] = 'Expected End Of Life';
$lang['item.form.maintenance'] = 'Maintenance';

$lang['item.form.is_disposed_of'] = 'Has Been Disposed Of';
$lang['item.form.date_disposed_of'] = 'Date Disposed Of';

$lang['item.form.archived'] = 'Archived';

$lang['item.form.comments'] = 'Comments';

$lang['item.form.files'] = 'Additional Files';

$lang['item.form.copyright_notice'] = 'Copyright Notice';



/*
 * Item Property Labels (as used on detail pages)
 * Some fields use the labels defined elsewhere, e.g. 'cat.label' or 'dept.label'.
 */

$lang['item.label.title'] = '';
$lang['item.label.manufacturer'] = 'Manufacturer';
$lang['item.label.model'] = 'Model';
$lang['item.label.short_description'] = '';
$lang['item.label.full_description'] = 'Description';
$lang['item.label.specification'] = 'Specification';

$lang['item.label.upgrades'] = 'Upgrades';
$lang['item.label.future_upgrades'] = 'Future Upgrades';

$lang['item.label.manufacturer_website'] = '';
$lang['item.label.technique'] = 'Technique';
$lang['item.label.acronym'] = 'Acronym';
$lang['item.label.keywords'] = 'Keywords';

$lang['item.label.showchildren'] = 'Associated Items';
$lang['item.label.showparents'] = 'Associated Facilities';

$lang['item.label.usergroup'] = 'User Group';
$lang['item.label.availability'] = 'Availability';
$lang['item.label.restrictions'] = 'Restrictions';

$lang['item.label.portability'] = 'Portability';

$lang['item.label.training'] = 'Training';

$lang['item.label.calibrated'] = 'Calibrated';
$lang['item.label.last_calibration_date'] = 'Last';
$lang['item.label.last_calibration_date'] = 'Next';

$lang['item.label.quantity'] = 'Quantity';
$lang['item.label.quantity_detail'] = '';


$lang['item.label.contact_1'] = 'Contact 1';
$lang['item.label.contact_2'] = 'Contact 2';


$lang['item.label.room'] = 'Room';


$lang['item.label.asset_no'] = 'Asset No.';
$lang['item.label.finance_id'] = 'Purchase Order No.';
$lang['item.label.serial_no'] = 'Serial No.';
$lang['item.label.year_of_manufacture'] = 'Year of Manufacture';
$lang['item.label.supplier'] = 'Supplier';
$lang['item.label.date_of_purchase'] = 'Purchase Date';
$lang['item.label.PAT'] = 'PAT Expiry';


$lang['item.label.cost'] = 'Cost';
$lang['item.label.replacement_cost'] = 'Replacement Cost';
$lang['item.label.end_of_life'] = 'Expected End of Life';
$lang['item.label.maintenance'] = 'Maintenance';

$lang['item.label.is_disposed_of'] = 'Disposed of';
$lang['item.label.date_disposed_of'] = 'Date Disposed of';


$lang['item.label.files'] = 'Additional Files';

$lang['item.label.copyright_notice'] = '';

$lang['item.form.archived'] = 'Archived';
$lang['item.form.date_archived'] = 'Date Archived';

$lang['item.label.date_updated'] = 'Last Updated';





/*
 * Organisations
 */

$lang['org.label'] = 'Organisation';
$lang['org.label.plural'] = 'Organisations';



/*
 * Site
 */

$lang['site.label'] = 'Site';
$lang['site.label.plural'] = 'Sites';



/*
 * Tag
 */

$lang['tag.label'] = 'Tag';
$lang['tag.label.plural'] = 'Tags';



?>