<?php
/**
 * Kit-Catalogue Default Item Field View Settings.
 *
 * Future updates to Kit-Catalogue may overwrite this config file.
 *
 * Do not edit the settings in here, instead edit and override the settings in local/local_field_view.php
 *
 *
 * This file controls which fields are visible on the front-facing catalogue pages, with some limits:
 *
 * - Some fields cannot be altered and are always visible:
 *   title, manufacturer, organisational unit, descriptions,
 *   technique, images, categories, and parent/child items.
 *
 * - The "Asset" fields are never visible.
 *
 * - Some fields are also affected by the relevant config settings, such as the enquiry
 *   form options which affect how custodian contact details are displayed.
 *
 * - Currently, the public API ignores these settings. Settings for the API will be introduced in a
 *   future release.
 *
 *
 * Fields can be set to either:
 *
 *   '*'       = visible to anyone.
 *   'user'    = visible only to authenticated users.
 *   'hide'    = hide from view.
 *
 *   Any misspelt settings will default to 'hide'.
 */


$field_view['item.model'] = '*';
$field_view['item.acronym'] = '*';
$field_view['item.specification'] = '*';
$field_view['item.upgrades'] = '*';
$field_view['item.future_upgrades'] = '*';
$field_view['item.manufacturer_website'] = '*';

$field_view['item.availability'] = '*';
$field_view['item.restrictions'] = '*';
$field_view['item.portability'] = '*';
$field_view['item.accesslevel'] = 'user';
$field_view['item.usergroup'] = 'user';

$field_view['item.training'] = '*';
$field_view['item.calibrated'] = '*';
$field_view['item.quantity'] = '*';
$field_view['item.PAT'] = 'user';

$field_view['item.contact_1'] = '*';   // Name and email address!
$field_view['item.contact_2'] = '*';   // Name and email address!

$field_View['item.ou'] = '*';

$field_view['item.site'] = '*';
$field_view['item.building'] = 'user';
$field_view['item.room'] = 'user';

$field_view['item.tags'] = '*';

$field_view['item.embedded_content'] = '*';

$field_view['item.resources'] = 'user';   // Links, uploaded files, etc

$field_view['item.custom'] = 'user';


?>