<?php



Ecl::load('Ecl_Mvc_Layout_Html');



/**
 * Kit-Catalogue specific layout class.
 *
 * @package  Kit-Catalogue
 * @version  1.0.0
 */
class Kc_Layout extends Ecl_Mvc_Layout_Html {

	// Public Properties


	// Private Properties



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Add the global javascripts required for other scripting.
	 *
	 * Loads JQuery and the Kit-Catalogue javascript config.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function addGlobalJavascript() {
		$this->addJavascript($this->router()->makeAbsoluteUri('/js/jquery-min.js'));
		$this->addJavascript($this->router()->makeAbsoluteUri('/js/jquery.require.js'));
		$this->addJavascript($this->router()->makeAbsoluteUri('/js/kc_config.php'));
		return true;
	}// /method



	/**
	 * Render any defined breadcrumb links.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function renderBreadcrumbs() {
		if (!empty($this->_breadcrumbs)) {
			foreach($this->_breadcrumbs as $i => $breadcrumb) {
				if (isset($breadcrumb['href'])) {
					printf('<li><a href="%2$s">%1$s</a></li>', $breadcrumb['title'], $breadcrumb['href']);
				} else {
					printf('<li>%1$s</li>', $breadcrumb['title']);
				}
			}
		}
		return true;
	}// /method



	/**
	 * Render an item's list view representation.
	 *
	 * @param  object  $item  The Item object to render.
	 * @param  string  $item_url  The URL to use for clicking through to the item details.
<<<<<<< HEAD
	 * @param  mixed  $hide_properties  The property name, or array of property names, to hide in the display.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function renderItemInList($item, $item_url, $hide_properties = array() ) {
		$user = $this->model('user');
		$lang = $this->model('lang');

		if (!is_array($hide_properties)) {
			if (empty($hide_properties)) {
				$hide_properties = array();
			} else {
				$hide_properties = array( $hide_properties );
			}
		}

=======
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function renderItemInList($item, $item_url) {
		$user = $this->model('user');
		$lang = $this->model('lang');

>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
		if (empty($item->image)) {
			$image_alt = 'No image available';
			$image = $this->router()->makeAbsoluteUri('/images/system/no_image.jpg');
		} else {
			$image_alt = $item->name;
<<<<<<< HEAD
			$image = $this->router()->makeAbsoluteUri("/item/{$item->url_suffix}/image/{$item->image}");
=======
			$image = $this->router()->makeAbsoluteUri($this->model('app.items_www') . $item->getFilePath() .'/'. $item->image);
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
		}
		?>
		<li class="item" id="item-<?php $this->out($item->id); ?>">
			<?php
			if ($this->model('security')->checkItemPermission($item, 'site.item.edit')) {
				$edit_url = $this->router()->makeAbsoluteUri('/admin/items/edit/'. $item->id);
				$back_url = base64_encode($this->request()->relativeUri());
				printf('<a class="admin_link" href="%1$s?backlink=%2$s">edit</a>', $edit_url, $back_url);
			}
			?>

			<a href="<?php echo $item_url; ?>"><img class="item-thumb" src="<?php $this->out($image); ?>" alt="<?php $this->out($image_alt); ?>" /></a>

			<div class="item-content">
				<h2 class="item-title">
<<<<<<< HEAD
					<a href="<?php echo $item_url; ?>" title="<?php $this->out($item->name); ?>"><?php $this->out($item->name); ?></a>
=======
					<?php
						$name = $item->name;
						if (strlen($name)>29) {
							$name = $this->escape(substr($name, 0, 28)) . '&hellip;';
						} else {
							$name = $this->escape($name);
						}
					?>
					<a href="<?php echo $item_url; ?>" title="<?php $this->out($item->name); ?>"><?php echo($name); ?></a>
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
				</h2>
				<p class="item-desc"><?php
					if (!empty($item->short_description)) {
						$this->out($item->short_description);
					}
					?><br /><a href="<?php echo $item_url; ?>">more details &raquo;</a>
				</p>

				<ul class="item-meta">
					<?php
<<<<<<< HEAD
					if ( (!in_array('manufacturer', $hide_properties)) && (!empty($item->manufacturer)) ) {
						printf('<li><strong>%1$s:</strong> %2$s</li>', $lang['item.label.manufacturer'], $this->escape($item->manufacturer));
					}
					if ( (!in_array('technique', $hide_properties)) && (!empty($item->technique)) ) {
						printf('<li><strong>%1$s:</strong> %2$s</li>', $lang['item.label.technique'], $this->escape($item->technique));
					}
					if ( (!in_array('ou', $hide_properties)) && (!empty($item->ou)) ) {
						printf('<li><strong>%1$s:</strong> %2$s</li>', $lang['ou.label'], $this->escape($this->model('organisationalunitstore')->lookupName($item->ou)));
=======
					if (!empty($item->technique)) {
						printf('<li><strong>%1$s</strong> %2$s</li>', $lang['item.label.technique'], $this->escape($item->technique));
					}
					if (!empty($item->department)) {
						printf('<li><strong>%1$s:</strong> %2$s</li>', $lang['dept.label'], $this->escape($this->model('departmentstore')->lookupName($item->department)));
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
					}
					?>
				</ul>
			</div>
		</li>
<<<<<<< HEAD
=======

>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
		<?php
		return true;
	}



<<<<<<< HEAD
	public function renderItemInFull($item, $goto_url = '') {
		$this->layout()->addStylesheet($this->router()->makeAbsoluteUri('/css/print.css'),'print');
		$this->layout()->addGlobalJavascript();

		if ($this->model('item.allow_lightbox')) {
			$this->layout()->addJavascript($this->router()->makeAbsoluteUri('/js/kc_lightbox.js'));
		}

		$user = $this->model('user');
		$lang = $this->model('lang');

		$back_url = base64_encode($this->request()->relativeUri());
=======
	public function renderItemInFull($item, $goto_url) {
		$this->layout()->addStylesheet($this->router()->makeAbsoluteUri('/css/print.css'),'print');
		$user = $this->model('user');
		$lang = $this->model('lang');

		// These are being a pain - set them to null here
		$latude = null;
		$ldtude = null;
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd

		if (empty($item->image)) {
			$no_image = true;
			$image_alt = 'No image available';
			$image = $this->router()->makeAbsoluteUri('/images/system/no_image.jpg');
		} else {
			$no_image = false;
			$image_alt = $item->manufacturer .' '. $item->model;
<<<<<<< HEAD
			$image = $this->router()->makeAbsoluteUri("/item/{$item->url_suffix}/image/{$item->image}");
=======
			$image = $this->router()->makeAbsoluteUri($this->model('app.items_www') . $item->getFilePath() .'/'. $item->image);
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
		}

		$files = $this->model('itemstore')->findFilesForItem($item);

		$image_files = array();
		$other_files = array();

		if (!empty($files)) {
			$image_ext = array ('jpg', 'jpeg', 'gif', 'png');
			foreach($files as $file) {
				$extension = strtolower(Ecl_Helper_Filesystem::getFileExtension($file->filename));
				if (in_array($extension, $image_ext)) {
					$image_files[] = $file;
				} else {
					$other_files[] = $file;
				}
			}
		}


<<<<<<< HEAD
		$links = $this->model('itemlinkstore')->findForItem($item->id);

=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd

		if ($this->model('log.item_view')) {
			$this->model('db')->insert('log_view', array (
				'date_view'  => $this->model('db')->formatDate(time()) ,
				'user_id'    => $this->model('user')->id ,
				'username'   => $this->model('user')->username ,
				'item_id'    => $item->id ,
			));
		}

		$wiki_parser = Ecl::factory('Ecl_Parser_Wikicode');
<<<<<<< HEAD
=======
		?>

		<?php
		if ($this->model('security')->checkItemPermission($item, 'site.item.edit')) {
			$edit_url = $this->router()->makeAbsoluteUri('/admin/items/edit/'. $item->id);
			$back_url = base64_encode($this->request()->relativeUri());
			printf('<a class="admin_link" href="%1$s?backlink=%2$s">edit item</a>', $edit_url, $back_url );
		}
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd


		function drawField($header, $detail) {
			if (!empty($detail)) {
				$header = strtoupper($header);
				?>
				<tr>
					<th><?php echo $header; ?></th>
					<td><?php echo $detail; ?></td>
				</tr>
<<<<<<< HEAD
				<?php
=======
				<?
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
			}
		}


		function isSensibleDate($date) {
			return (!empty($date)) && ($date > strtotime('1970-01-02 00:00:00'));
		}
		?>

		<div class="item">

<<<<<<< HEAD
			<div class="item-header">

				<?php
				if (
					($this->model('enquiry.enabled'))
					&& (
						(!empty($item->contact_1_email))
						|| (!empty($item->contact_2_email))
						|| (!Ecl::isEmpty($this->model('enquiry.send_to')))
						)
					) {
					?>
					<a class="enquire-link" href="<?php echo $this->router()->makeAbsoluteUri("/enquiry/{$item->id}?backlink={$back_url}"); ?>"><img src="<?php echo $this->router()->makeAbsoluteUri('/images/system/enquirebutton.gif'); ?>" alt="Enquire Now" /></a>
					<?php
				}
				?>

				<h1><?php $this->out($item->name); ?></h1>

				<table class="fields">
				<?php
				$manufacturer_website = '';

				if (!empty($item->manufacturer_website)) {
					$start_url = '';
					if ( (substr($item->manufacturer_website, 0, 7)!='http://')
						&& (substr($item->manufacturer_website, 0, 8)!='https://') ) {
							$start_url = 'http://';
					}
					$manufacturer_website = sprintf('&nbsp;&nbsp;&nbsp;(<a href="%1$s" target="_blank">manufacturer\'s website</a>)', htmlentities("{$start_url}{$item->manufacturer_website}"));
				}
				drawField($lang['item.label.manufacturer'], $this->escape($item->manufacturer) . $manufacturer_website);

				drawField($lang['item.label.model'], $this->escape($item->model));
				drawField($lang['item.label.acronym'], $this->escape($item->acronym));

				?>
				</table>

			</div>

			<div class="item-detail-right">

				<?php
				if ($this->model('security')->checkItemPermission($item, 'site.item.edit')) {
					$edit_url = $this->router()->makeAbsoluteUri("/admin/items/edit/{$item->id}");
					printf('<a class="admin_link" href="%1$s?backlink=%2$s">edit item</a>', $edit_url, $back_url );
				}
				?>

				<div class="item-body">

					<div class="usage">
						<?php
						if (!empty($item->ou)) {
							echo '<h2>'. $this->model('organisationalunitstore')->lookupName($item->ou). '</h2>';
						}
						?>
						<table class="layout fields">
						<?php

						drawField($lang['item.label.availability'], $this->escape($item->availability));
						drawField($lang['item.label.restrictions'], $this->escape($item->restrictions));
						drawField($lang['item.label.portability'], $this->escape($item->portability));

						if ($this->model('security')->checkItemPermission($item, 'item.accesslevel.view')) {
							$access = $this->escape($this->model('accesslevelstore')->lookupName($item->access));
							drawField($lang['access.label'], $this->escape($access));

							drawField($lang['item.label.usergroup'], $this->escape($item->usergroup));
						}


						$details = '';
						if (true === $item->training_required) {
							if (true === $item->training_provided) {
								$details = 'Training is required to use this item and we can arrange this if needed.';
							} elseif (false === $item->training_provided) {
								$details = 'Although training is required to use this item, we cannot arrange it for you.';
							} else {
								$details = 'Training is required to use this item.';
							}
						} elseif(false === $item->training_required) {
							$details = 'No special training required.';
						}
						drawField($lang['item.label.training'], $details);


						switch($item->calibrated) {
							case Item::CALIB_YES:
								$details = 'Yes, this item is calibrated.';
								if (isSensibleDate($item->last_calibration_date)) {
									$details .= '<br />Last Calibration: '. date($this->model('layout.date_format'), $item->last_calibration_date);
								}
								if (isSensibleDate($item->next_calibration_date)) {
									$details .= '<br />Next Calibration: '. date($this->model('layout.date_format'), $item->next_calibration_date);
								}
								break;
							case Item::CALIB_NO:
								$details = 'No, this item is not calibrated.';
							case Item::CALIB_AUTO:
								$details = 'This item is automatically calibrated.';
							default:
								$details = '';
								break;
						}
						drawField($lang['item.label.calibrated'], $details);

						if (1 < $item->quantity) {
							drawField($lang['item.label.quantity'], $item->quantity .'.<br />'. $this->escape($item->quantity_detail));
						}

						if (isSensibleDate($item->PAT)) {
							drawField($lang['item.label.PAT'], date($this->model('layout.date_format'), $item->PAT));
						}
						?>
						</table>
					</div>

					<div class="contact">

						<table class="layout fields">
						<?php
						if (!$user->isAnonymous()) {
							// Contact 1
							$contact_email = (!empty($item->contact_1_email)) ? $item->contact_1_email : '' ;
							$contact_name = (!empty($item->contact_1_name)) ? $item->contact_1_name : $contact_email ;
							$contact_link = '';

							if (empty($contact_email)) {
								$contact_link = sprintf('%1$s', $contact_name);
							} else {
								$contact_link = sprintf('<a href="mailto:%1$s">%2$s</a>', $contact_email, $contact_name);
							}
							drawField($lang['item.label.contact_1'], $contact_link);

							// Contact 2
							$contact_email = (!empty($item->contact_2_email)) ? $item->contact_2_email : '' ;
							$contact_name = (!empty($item->contact_2_name)) ? $item->contact_2_name : $contact_email ;
							$contact_link = '';

							if (empty($contact_email)) {
								$contact_link = sprintf('%1$s', $contact_name);
							} else {
								$contact_link = sprintf('<a href="mailto:%1$s">%2$s</a>', $contact_email, $contact_name);
							}
							drawField($lang['item.label.contact_2'], $contact_link);
						} else {
							if ($this->model('enquiry.enabled')) {
								$enquiry_form_link = $this->router()->makeAbsoluteUri("/enquiry/{$item->id}?backlink={$back_url}");

								if (Ecl::isEmpty($this->model('enquiry.send_to'))) {
									$contact_email = (!empty($item->contact_1_email)) ? $item->contact_1_email : '' ;
									$contact_name = (!empty($item->contact_1_name)) ? $item->contact_1_name : $contact_email ;
									if (!empty($contact_name)) {
										drawField($lang['item.label.contact_1'], $contact_name);
									}

									$contact_email = (!empty($item->contact_2_email)) ? $item->contact_2_email : '' ;
									$contact_name = (!empty($item->contact_2_name)) ? $item->contact_2_name : $contact_email ;
									if (!empty($contact_name)) {
										drawField($lang['item.label.contact_2'], $contact_name);
									}
								}
								drawField('', sprintf('<a href="%1$s">%2$s</a>', $enquiry_form_link, 'Enquire about this item'));
							} else {
								// Contact 1
								$contact_email = (!empty($item->contact_1_email)) ? $item->contact_1_email : '' ;
								$contact_name = (!empty($item->contact_1_name)) ? $item->contact_1_name : $contact_email ;
								$contact_link = '';

								if (empty($contact_email)) {
									$contact_link = sprintf('%1$s', $contact_name);
								} else {
									$contact_link = sprintf('<a href="mailto:%1$s">%2$s</a>', $contact_email, $contact_name);
								}
								drawField($lang['item.label.contact_1'], $contact_link);

								// Contact 2
								$contact_email = (!empty($item->contact_2_email)) ? $item->contact_2_email : '' ;
								$contact_name = (!empty($item->contact_2_name)) ? $item->contact_2_name : $contact_email ;
								$contact_link = '';

								if (empty($contact_email)) {
									$contact_link = sprintf('%1$s', $contact_name);
								} else {
									$contact_link = sprintf('<a href="mailto:%1$s">%2$s</a>', $contact_email, $contact_name);
								}
								drawField($lang['item.label.contact_2'], $contact_link);
							}
						}


						// @todo : Deprecated, remove organisation 'else' below
						if ($this->model('app.use_ou_tree')) {
							$ou = $this->model('organisationalunitstore')->find($item->ou);
							if ($ou) {
								if (empty($ou->url)) {
									drawField($lang['ou.label'], $this->model('organisationalunitstore')->lookupName($item->ou));
								} else {
									drawField($lang['ou.label'], sprintf('<a href="%1$s">%2$s</a>', $ou->url, $this->escape($ou->name)));
								}
							}
						} else {
							if (!empty($item->organisation)) {
								$org = $this->model('organisationstore')->find($item->organisation);
								if ($org) {
									drawField($lang['org.label'], $this->escape($org->name));
								}
							}
						}


						if ($this->model('security')->checkItemPermission($item, 'item.location.view')) {
							if (!empty($item->site)) {
								$site = $this->model('sitestore')->find($item->site);
								if ($site) {
									if (empty($site->url)) {
										drawField($lang['site.label'], $this->escape($site->name));
									} else {
										drawField($lang['site.label'], sprintf('<a href="%1$s">%2$s</a>', $site->url, $this->escape($site->name)));
									}
								}
							}

							if (!empty($item->building)) {
								$building = $this->model('buildingstore')->find($item->building);
								if ($building) {
									if (empty($building->url)) {
										drawField($lang['building.label'], $this->escape($building->name));
									} else {
										drawField($lang['building.label'], sprintf('<a href="%1$s">%2$s</a>', $building->url, $this->escape($building->name)));
									}
								}
							}

							drawField($lang['item.label.room'], $this->escape($item->room));
						}

						?>
						</table>

					</div>

					<?php
					if (!empty($item->full_description)) {
						$this->outf($lang['item.label.full_description'], '<h2>%s</h2>');
						?>
						<div class="item-description">
							<?php
							$text = $wiki_parser->parse($item->full_description);
							$text = str_replace(
								array('<h1>', '</h1>', '<h2>', '</h2>'),
								array('<h3>', '</h3>', '<h3>', '</h3>'),
								$text
								);
							echo $text;
							?>
=======
			<h1><?php $this->out($item->name); ?></h1>


			<div style="float: right; width: 690px;">

				<div class="white-box">

					<table class="layout">
					<tr>
						<td width="50%" style="width: 50%">
							<table class="fields">
							<?php
							$manufacturer_website = '';

							if (!empty($item->manufacturer_website)) {
								$start_url = '';
								if ( (substr($item->manufacturer_website, 0, 7)!='http://')
									&& (substr($item->manufacturer_website, 0, 8)!='https://') ) {
										$start_url = 'http://';
								}
								$manufacturer_website = sprintf('<br />(<a href="%1$s" target="_blank">manufacturer\'s website</a>)', "{$start_url}{$item->manufacturer_website}");
							}
							drawField($lang['item.label.manufacturer'], $this->escape($item->manufacturer) . $manufacturer_website);

							drawField($lang['item.label.model'], $this->escape($item->model));
							drawField($lang['item.label.acronym'], $this->escape($item->acronym));

							if (!empty($item->department)) {
								drawField($lang['dept.label'], $this->model('departmentstore')->lookupName($item->department));
							}
							?>
							</table>

							<br />

							<table class="fields">
							<?php
							if (!empty($item->contact_1_email)) {
								if (!empty($item->contact_1_name)) {
									$contact_link = sprintf('<a href="mailto:%1$s">%2$s</a>', $item->contact_1_email, $item->contact_1_name);
								} else {
									$contact_link = sprintf('<a href="mailto:%1$s">%1$s</a>', $item->contact_1_email);
								}
								drawField($lang['item.label.contact_1'], $contact_link);
							}

							if (!empty($item->contact_2_email)) {
								if (!empty($item->contact_2_name)) {
									$contact_link = sprintf('<a href="mailto:%1$s">%2$s</a>', $item->contact_2_email, $item->contact_2_name);
								} else {
									$contact_link = sprintf('<a href="mailto:%1$s">%1$s</a>', $item->contact_2_email);
								}
								drawField($lang['item.label.contact_2'], $contact_link);
							}
							?>
							</table>
						</td>
						<td width="50%" style="width: 50%; padding-left: 1em;">
							<table class="fields">
							<?php
							if ($this->model('security')->checkItemPermission($item, 'item.location.view')) {
								if (!empty($item->site)) {
									$site = $this->model('sitestore')->find($item->site);
									if ($site) {
										drawField($lang['site.label'], $this->escape($site->name));
									}
								}

								if (!empty($item->building)) {
									$building = $this->model('buildingstore')->find($item->building);
									if ($building) {
										drawField($lang['building.label'], $this->escape($building->name));
									}
								}

								drawField($lang['item.label.room'], $this->escape($item->room));
							}

							drawField($lang['item.label.availability'], $this->escape($item->availability));

							if ($this->model('security')->checkItemPermission($item, 'item.accesslevel.view')) {
								$access = $this->escape($this->model('accesslevelstore')->lookupName($item->access));
								drawField($lang['access.label'], $this->escape($access));

								drawField($lang['item.label.usergroup'], $this->escape($item->usergroup));
							}
							?>
							</table>

							<br />

							<table class="fields">
							<?php
							$details = '';
							if (true === $item->training_required) {
								if (true === $item->training_provided) {
									$details = 'Training is required to use this item and we can arrange this if needed.';
								} elseif (false === $item->training_provided) {
									$details = 'Although training is required to use this item, we cannot arrange it for you.';
								} else {
									$details = 'Training is required to use this item.';
								}
							} elseif(false === $item->training_required) {
								$details = 'No special training required.';
							}
							drawField($lang['item.label.training'], $details);


							switch($item->calibrated) {
								case Item::CALIB_YES:
									$details = 'Yes, this item is calibrated.';
									if (isSensibleDate($item->last_calibration_date)) {
										$details .= '<br />Last Calibration: '. date('d-m-Y', $item->last_calibration_date);
									}
									if (isSensibleDate($item->next_calibration_date)) {
										$details .= '<br />Next Calibration: '. date('d-m-Y', $item->next_calibration_date);
									}
									break;
								case Item::CALIB_NO:
									$details = 'No, this item is not calibrated.';
								case Item::CALIB_AUTO:
									$details = 'This item is automatically calibrated.';
								default:
									$details = '';
									break;
							}
							drawField($lang['item.label.calibrated'], $details);
							?>
							</table>
						</td>
					</tr>
					</table>

				</div>


				<div class="item-body">

					<?php
					if (!empty($item->full_description)) {
						$this->outf($lang['item.label.full_description'], '<h3>%s</h3>');
						?>
						<div class="item-description">
							<?php echo $wiki_parser->parse($item->full_description); ?>
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
						</div>
						<?php
					}

					if (!empty($item->specification)) {
<<<<<<< HEAD
						$this->outf($lang['item.label.specification'], '<h2>%s</h2>');
						?>
						<div class="item-specification">
							<?php
							$text = $wiki_parser->parse($this->escape($item->specification));
							$text = str_replace(
								array('<h1>', '</h1>', '<h2>', '</h2>'),
								array('<h3>', '</h3>', '<h3>', '</h3>'),
								$text
								);
							echo $text;
							?>
						</div>
						<?php
					}


					if ( ($this->model('item.allow_embedded_content')) && (!empty($item->embedded_content)) ) {
						$this->outf($lang['item.label.embedded_content'], '<h2>%s</h2>');
						?>
						<div class="item-embedded-content">
							<?php echo $item->embedded_content; ?>
=======
						$this->outf($lang['item.label.specification'], '<h3>%s</h3>');
						?>
						<div class="item-specification">
							<?php echo $wiki_parser->parse($this->escape($item->specification)); ?>
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
						</div>
						<?php
					}



<<<<<<< HEAD

					/*
					 * Show Available Resources
					 */
					if ( ($this->model('security')->checkItemPermission($item, 'item.files.view'))
						&& ( (!empty($other_files)) || (!empty($links)) )
						) {

						$this->outf($lang['item.label.resources'], '<h2>%s</h2>');

						$grouped_files = array();

						foreach($links as $link) {
							$grouped_files[$link->type][] = $link;
						}
=======
					/*
					 * Show Available Files
					 */
					if ( ($this->model('security')->checkItemPermission($item, 'item.files.view')) && (!empty($other_files)) ) {
						$grouped_files = null;
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd

						foreach($other_files as $file) {
							$grouped_files[$file->type][] = $file;
						}

						$types = $this->model('itemstore')->findAllFileTypes();
<<<<<<< HEAD
						?>
						<div class="item-resources">
							<?php
							foreach($grouped_files as $file_type => $file_group) {
								$type_name = (isset($types[$file_type])) ? $types[$file_type] : 'Other Resources' ;
=======

						$this->outf($lang['item.label.files'], '<h3>%s</h3>');
						?>
						<div class="item-files">
							<?php
							foreach($grouped_files as $file_type => $file_group) {
								$type_name = (isset($types[$file_type])) ? $types[$file_type] : 'Other Files' ;
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
								?>
								<h4><?php $this->out($type_name); ?></h4>
								<ul>
									<?php
<<<<<<< HEAD
									foreach($file_group as $resource) {
										if ($resource instanceof Itemfile) {
											$file_url = $this->router()->makeAbsoluteUri("/item/{$item->url_suffix}/file/{$resource->filename}");
											$display_name = (empty($resource->name)) ? $resource->filename : $resource->name ;
											?>
											<li class="file"><a href="<?php echo $file_url; ?>"><?php $this->out($display_name); ?></a></li>
											<?php
										} else {
											$start_url = '';
											if ( (substr($resource->url, 0, 7)!='http://')
												&& (substr($resource->url, 0, 8)!='https://') ) {
													$start_url = 'http://';
											}
											?>
											<li class="link"><a href="<?php echo $start_url.$resource->url; ?>" target="_blank"><?php $this->out($resource->name); ?></a></li>
											<?php
										}
=======
									foreach($file_group as $file) {
										$file_url = $this->router()->makeAbsoluteUri("/item/{$item->url_suffix}/file/{$file->filename}");
										$display_name = (empty($file->name)) ? $file->filename : $file->name ;
										?>
										<li><a href="<?php echo $file_url; ?>"><?php $this->out($display_name); ?></a></li>
										<?php
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
									}
									?>
								</ul>
								<?php
							}
							?>
						</div>
						<?php
<<<<<<< HEAD
						if (!empty($item->copyright_notice)) {
							$this->outf($lang['item.label.copyright_notice'], '<h4>%s</h4>');
							printf('<p>%s</p>', $item->copyright_notice);
						}
					}


					// Custom fields
					if ($this->model('security')->checkItemPermission($item, 'item.customfield.view')) {
						$custom_fields = $this->model('itemstore')->getItemCustomFields($item->id);
						if (!empty($custom_fields)) {
							$fields = $this->model('customfieldstore')->findAll();

							$drawn_field = false;
							if (!empty($fields)) {
								foreach($fields as $field) {
									if ( (isset($custom_fields[$field->id])) && (!empty($custom_fields[$field->id])) ) {
										if (!$drawn_field) {
											?>
											<h2>Additional Fields</h2>
											<table class="fields">
											<?php
										}
										$drawn_field = true;
										drawField($field->name, $this->escape($custom_fields[$field->id]));
									}
								}
							}

							if ($drawn_field) {
								?>
								</table>
								<?php
							}
						}
					}


					// Show associated child items
					$children = $this->model('itemstore')->findChildren($item->id);
					if (count($children)>0) {
						?>
						<div class="item-children">
							<h2><?php $this->out($lang['item.label.showchildren']); ?></h2>
							<ul class="item-list">
								<?php
								$url_stub = $this->router()->makeAbsoluteUri('/item/');
								foreach($children as $child) {
									$this->layout()->renderItemInList($child, "{$url_stub}{$child->slug}");
								}
								?>
							</ul>
						</div>
						<?php
					}
					$children = null;



					if (isSensibleDate($item->date_updated)) {
						printf('<p class="note item-date-updated">%s: %s</p>', $lang['item.label.date_updated'], date($this->model('layout.date_format'), $item->date_updated));
					}
					?>
=======
					}

					if (!empty($item->copyright_notice)) {
						$this->outf($lang['item.label.copyright_notice'], '<h4>%s</h4>');
						printf('<p>%s</p>', $item->copyright_notice);
					}

					if (isSensibleDate($item->date_updated)) {
						printf('<p class="note" style="margin-top: 2em;">%s: %s</p>', $lang['item.label.date_updated'], date('d-m-Y', $item->date_updated));
					}
					?>

>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
				</div>


			</div>



<<<<<<< HEAD
			<div class="item-detail-left">

				<div class="images cf">
					<?php
					if ($no_image) {
						?>
						<img src="<?php echo $image; ?>" width="100" alt="<?php $this->out($image_alt); ?>" />
						<?php
					} else {
						?>
						<a href="<?php echo $image; ?>" target="_blank"><img id="main-image" src="<?php echo $image; ?>" width="252" alt="<?php $this->out($image_alt); ?>" /></a>
						<?php
					}

					foreach($image_files as $i => $file) {
						if ($file->filename != $item->image) {
							$extra_image = $this->router()->makeAbsoluteUri("/item/{$item->url_suffix}/image/{$file->filename}");
							?>
							<div class="extra-image">
								<a href="<?php echo $extra_image; ?>" target="_blank"><img src="<?php echo $extra_image; ?>" width="68" height="68" alt="" /></a>
							</div>
							<?php
						}
					}
					?>
				</div>

				<?php
=======
			<div style="float: left; width: 250px;">

				<?php
				if ($no_image) {
					?>
					<img src="<?php echo $image; ?>" width="100" alt="<?php $this->out($image_alt); ?>" />
					<?php
				} else {
					?>
					<a href="<?php echo $image; ?>" target="_blank"><img src="<?php echo $image; ?>" width="250" alt="<?php $this->out($image_alt); ?>" /></a>
					<?php
				}

				foreach($image_files as $i => $file) {
					if ($file->filename != $item->image) {
						$extra_image = $this->router()->makeAbsoluteUri($this->model('app.items_www') . $item->getFilePath() .'/'. $file->filename);
						?>
						<div class="extra_image" style="border: 2px solid #fff;">
							<a href="<?php echo $extra_image; ?>" target="_blank"><img src="<?php echo $extra_image; ?>" width="80" alt="" /></a>
						</div>
						<?php
					}
				}


>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
				$categories = $this->model('categorystore')->findForItem($item->id);
				if ($categories->count()==0) {
					if ($this->model('user')->isAnonymous()) {
						?>
						<p class="note">There are no publically available categories listed at present. You may have to <a href="<?php echo $this->router()->makeAbsoluteUri('/'); ?>">sign in</a> to browse this catalogue.</p>
						<?php
					}
				} else {
					?>
<<<<<<< HEAD
					<div class="side-bar">
						<h4><?php $this->out($lang['cat.label.plural']); ?></h4>
						<ul>
						<?php
						foreach($categories as $i => $category) {
							?>
							<li>
								<a href="<?php echo $this->router()->makeAbsoluteUri("/{$lang['cat.route']}/{$category->url_suffix}"); ?>">
									<?php $this->out($category->name); ?>
									<span class="count">(<?php $this->out($category->getItemCount($user->param('visibility'))); ?>)</span>
								</a>
							</li>
							<?php
						}
						?>
						</ul>
					</div>
					<?php
				}
				$categories = null;


				$facilities = $this->model('itemstore')->findParents($item->id);
				if (count($facilities)>0) {
					?>
					<div class="side-bar tags">
						<h4><?php $this->out($lang['item.label.showparents']); ?></h4>
						<ul>
							<?php
							foreach($facilities as $i => $facility) {
								?>
								<li><a href="<?php echo $this->router()->makeAbsoluteUri("/item/{$facility->slug}"); ?>"><?php $this->out($facility->name); ?></a></li>
								<?php
							}
							?>
						</ul>
					</div>
					<?php
				}
				$facilities = null;


				$tags = $this->model('itemstore')->getItemTags($item->id);
				if (count($tags)>0) {
					?>
					<div class="side-bar tags">
						<h4>Tags</h4>
						<ul>
							<?php
							foreach($tags as $i => $tag) {
								?>
								<li class="tags"><a href="<?php echo $this->router()->makeAbsoluteUri("/tag/{$tag}"); ?>">#<?php $this->out($tag); ?></a></li>
								<?php
							}
							?>
						</ul>
					</div>
					<?php
				}
				$tags = null;
				?>

				<div class="side-bar">
					<h4>Permanent Link</h4>
					<ul>
						<li><a href="<?php echo $this->router()->makeAbsoluteUri("/id/item/{$item->idslug}"); ?>"># Permalink</a></li>
					</ul>
				</div>

				<?php
				if ( (!$user->isAnonymous()) && (KC__VISIBILITY_PUBLIC == $item->visibility) ) {
					?>
					<div class="side-bar">
						<h4>Visibility</h4>
						<div style="padding: 3px;">This item is publically visible.</div>
					</div>
					<?php
				}


				if (
					(KC__VISIBILITY_PUBLIC == $item->visibility)
					|| ($this->model('socialnetwork.allow_googleplus'))
					|| ($this->model('socialnetwork.allow_twitter'))
					) {
					?>
					<div class="socialnetworkbuttons">
						<?php
						if ($this->model('socialnetwork.allow_googleplus')) {
							?>
							<div id="plusone-div" class="plusone"></div>
							<script type="text/javascript" src="https://apis.google.com/js/plusone.js">
								{lang:"en", parsetags:"explicit"}
							</script>
							<script type="text/javascript">
								gapi.plusone.render("plusone-div", {annotation:"none"});
							</script>
							<?php
						}


						if ($this->model('socialnetwork.allow_twitter')) {
							?>
							<a href="https://twitter.com/share" class="twitter-share-button" data-size="large" data-count="none">Tweet</a>
							<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
							<?php
						}
						?>
					</div>
					<?php
				}
				?>
=======
					<h6>Related Categories</h6>
					<ul style="margin-left: 1em;">
					<?php
					foreach($categories as $i => $category) {
						?>
						<li>
							<a href="<?php echo $this->router()->makeAbsoluteUri("/category/{$category->url_suffix}"); ?>">
								<?php $this->out($category->name); ?>
								<span class="count">(<?php $this->out($category->getItemCount($user->param('visibility'))); ?>)</span>
							</a>
						</li>
						<?php
					}
					?>
					</ul><?php
				}


				$tags = $this->model('itemstore')->getItemTags($item->id);

				if (0 == count($tags)) {
					if ($this->model('user')->isAnonymous()) {
						?>
						<p class="note">There are no publically available tags listed at present. You may have to <a href="<?php echo $this->router()->makeAbsoluteUri('/'); ?>">sign in</a> to browse this catalogue.</p>
						<?php
					}
				} else {
					?>
					<h6>Tags</h6>
					<ul>
						<?php
						foreach($tags as $i => $tag) {
							?>
							<li class="tags"><a href="<?php echo $this->router()->makeAbsoluteUri("/tags/{$tag}"); ?>">#<?php $this->out($tag); ?></a></li>
							<?php
						}
						?>
					</ul>
					<?php
				}
				?>

				<br />

				<table class="fields">
				<?php
				if ($this->model('security')->checkItemPermission($item, 'item.accesslevel.view')) {
					if (isSensibleDate($item->PAT)) {
						drawField($lang['item.label.PAT'], date('d-m-Y', $item->PAT));
					}
				}

				// Custom fields
				if ($this->model('security')->checkItemPermission($item, 'item.customfield.view')) {
					$custom_fields = $this->model('itemstore')->getItemCustomFields($item->id);
					if (!empty($custom_fields)) {
						$fields = $this->model('customfieldstore')->findAll();
						if (!empty($fields)) {
							foreach($fields as $field) {
								if ( (isset($custom_fields[$field->id])) && (!empty($custom_fields[$field->id])) ) {
									drawField($field->name, $this->escape($custom_fields[$field->id]));
								}
							}
						}
					}
				}
				?>
				</table>
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd

			</div>

		</div>

		<?php
<<<<<<< HEAD
		if ($this->model('item.allow_lightbox')) {
			?>
			<div id="lightbox" class="window">
				<div id="lightbox_top"><a class="modalwindow_close" href="#" title="close window">close</a></div>
				<div id="lightbox_imagecontainer"><a id="lightbox_link" href="#" target="_blank"><img id="lightbox_image" src="ajax-loader.gif"></a></div>
				<div id="lightbox_list"></div>
			</div>
			<?php
		}
		?>

		<?php
		return true;
	}



	public function renderBuildingSelect($html_id, $selected_building = null) {
		$all_buildings = $this->model('buildingstore')->findAllUsed()->toAssoc('id', 'name');
		$all_buildings = array('' => '') + (array) $all_buildings;
		Ecl_Helper_Html::formSelect($html_id, $all_buildings, $selected_building);
		return true;
	}



	public function renderCategorySelect($html_id, $selected_category = null) {
		$all_categories = $this->model('categorystore')->findAllUsed()->toAssoc('id', 'name');
		$all_categories = array('' => '') + (array) $all_categories;
		Ecl_Helper_Html::formSelect($html_id, $all_categories, $selected_category);
		return true;
	}



	public function renderContactSelect($html_id, $selected_contact = null) {
		$all_contacts = $this->model('itemstore')->findAllContacts(KC__VISIBILITY_INTERNAL);
		array_unshift($all_contacts, '');
		$all_contacts = Ecl_Helper_Array::duplicateValueAsKey($all_contacts);
		Ecl_Helper_Html::formSelect($html_id, $all_contacts, $selected_contact);
		return true;
	}



	public function renderManufacturerSelect($html_id, $selected_manufacturer = null) {
		$all_manufacturers = $this->model('itemstore')->findAllManufacturers(KC__VISIBILITY_INTERNAL);
		$all_manufacturers = Ecl_Helper_Array::duplicateValueAsKey($all_manufacturers);
		$all_manufacturers = array('' => '') + (array) $all_manufacturers;
		Ecl_Helper_Html::formSelect($html_id, $all_manufacturers, $selected_manufacturer);
		return true;
	}



	public function renderOUSelect($html_id, $selected_ou = null) {
		$allow_select_ou = false;
		$allow_all_ou = false;
		$valid_ou = array();

		if ($this->model('security')->checkAuth(KC__AUTH_CANADMIN)) {
			$allow_select_ou = true;
			$allow_all_ou = true;
		} elseif ($this->model('security')->checkAuth(KC__AUTH_CANOUADMIN)) {
			$allow_select_ou = true;
			$allow_all_ou = false;
			$valid_ou = $this->model('security')->findOUsForAuth(KC__AUTH_CANOUADMIN);
		}

		if (!$allow_select_ou) {
			return false;
		} else {
			?>
			<select name="<?php echo $html_id; ?>" id="<?php echo $html_id; ?>">
				<?php
				$ou_list = $this->model('organisationalunitstore')->findTree();

				foreach($ou_list as $ou) {
					printf('<option value="%1$s" %4$s %5$s> %3$s %2$s </option>',
						$ou->id,
						$this->escape($ou->name),
						str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $ou->tree_level),
						( ($ou->id==$selected_ou) ? 'selected="selected"' : null),
						( ($allow_all_ou || (in_array($ou->id, $valid_ou))) ? null : 'disabled="disabled"')
					);
				}
				?>
			</select>
			<?php
			return true;
		}
	}



	public function renderTree($tree, $callback = null, $current_level = null) {
		if (null === $current_level) {
			$current_level = -1;
		} else {
			$current_level = (int) $current_level;
		}

		$node = array_shift($tree);

		$indent = str_repeat("\t", $node->tree_level);

		if ($node->tree_level > $current_level) {
			echo("\n{$indent}<ul>");
		} elseif ($node->tree_level < $current_level) {
			$closing_indent = str_repeat("\t", $current_level);
			echo str_repeat("\n{$closing_indent}</ul>", $current_level - $node->tree_level);
		}

		echo "\n{$indent}";
		if (is_callable($callback)) {
			$callback($node);
		} else {
			$name = (!empty($node->name)) ? $node->name : '<unknown>' ;
			echo "<li>{$name}";
		}

		if (!empty($tree)) {
			$this->renderTree($tree, $callback, $node->tree_level);

			if ($node->tree_level > $current_level) {
				echo str_repeat("\n</ul>\n", $node->tree_level);
			}
		}

		return true;
	}



	public function renderVisibilitySelect($html_id, $selected_visibility = null) {
		$options = array (
			''                      => 'Any Visibility' ,
			KC__VISIBILITY_PUBLIC   => 'Public only' ,
			KC__VISIBILITY_INTERNAL => 'Internal only' ,
		);

		Ecl_Helper_Html::formSelect($html_id, $options);
		return true;
	}



	/* --------------------------------------------------------------------------------
	 * Private Methods
	 */



}
=======
		return true;
   }


/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
?>