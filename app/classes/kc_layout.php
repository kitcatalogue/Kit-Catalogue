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
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function renderItemInList($item, $item_url) {
		$user = $this->model('user');
		$lang = $this->model('lang');

		if (empty($item->image)) {
			$image_alt = 'No image available';
			$image = $this->router()->makeAbsoluteUri('/images/system/no_image.jpg');
		} else {
			$image_alt = $item->name;
			$image = $this->router()->makeAbsoluteUri($this->model('app.items_www') . $item->getFilePath() .'/'. $item->image);
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
					<?php
						$name = $item->name;
						if (strlen($name)>29) {
							$name = $this->escape(substr($name, 0, 28)) . '&hellip;';
						} else {
							$name = $this->escape($name);
						}
					?>
					<a href="<?php echo $item_url; ?>" title="<?php $this->out($item->name); ?>"><?php echo($name); ?></a>
				</h2>
				<p class="item-desc"><?php
					if (!empty($item->short_description)) {
						$this->out($item->short_description);
					}
					?><br /><a href="<?php echo $item_url; ?>">more details &raquo;</a>
				</p>

				<ul class="item-meta">
					<?php
					if (!empty($item->technique)) {
						printf('<li><strong>%1$s</strong> %2$s</li>', $lang['item.label.technique'], $this->escape($item->technique));
					}
					if (!empty($item->department)) {
						printf('<li><strong>%1$s:</strong> %2$s</li>', $lang['dept.label'], $this->escape($this->model('departmentstore')->lookupName($item->department)));
					}
					?>
				</ul>
			</div>
		</li>

		<?php
		return true;
	}



	public function renderItemInFull($item, $goto_url) {
		$this->layout()->addStylesheet($this->router()->makeAbsoluteUri('/css/print.css'),'print');
		$user = $this->model('user');
		$lang = $this->model('lang');

		// These are being a pain - set them to null here
		$latude = null;
		$ldtude = null;

		if (empty($item->image)) {
			$no_image = true;
			$image_alt = 'No image available';
			$image = $this->router()->makeAbsoluteUri('/images/system/no_image.jpg');
		} else {
			$no_image = false;
			$image_alt = $item->manufacturer .' '. $item->model;
			$image = $this->router()->makeAbsoluteUri($this->model('app.items_www') . $item->getFilePath() .'/'. $item->image);
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



		if ($this->model('log.item_view')) {
			$this->model('db')->insert('log_view', array (
				'date_view'  => $this->model('db')->formatDate(time()) ,
				'user_id'    => $this->model('user')->id ,
				'username'   => $this->model('user')->username ,
				'item_id'    => $item->id ,
			));
		}

		$wiki_parser = Ecl::factory('Ecl_Parser_Wikicode');
		?>

		<?php
		if ($this->model('security')->checkItemPermission($item, 'site.item.edit')) {
			$edit_url = $this->router()->makeAbsoluteUri('/admin/items/edit/'. $item->id);
			$back_url = base64_encode($this->request()->relativeUri());
			printf('<a class="admin_link" href="%1$s?backlink=%2$s">edit item</a>', $edit_url, $back_url );
		}


		function drawField($header, $detail) {
			if (!empty($detail)) {
				$header = strtoupper($header);
				?>
				<tr>
					<th><?php echo $header; ?></th>
					<td><?php echo $detail; ?></td>
				</tr>
				<?
			}
		}


		function isSensibleDate($date) {
			return (!empty($date)) && ($date > strtotime('1970-01-02 00:00:00'));
		}
		?>

		<div class="item">

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
						</div>
						<?php
					}

					if (!empty($item->specification)) {
						$this->outf($lang['item.label.specification'], '<h3>%s</h3>');
						?>
						<div class="item-specification">
							<?php echo $wiki_parser->parse($this->escape($item->specification)); ?>
						</div>
						<?php
					}



					/*
					 * Show Available Files
					 */
					if ( ($this->model('security')->checkItemPermission($item, 'item.files.view')) && (!empty($other_files)) ) {
						$grouped_files = null;

						foreach($other_files as $file) {
							$grouped_files[$file->type][] = $file;
						}

						$types = $this->model('itemstore')->findAllFileTypes();

						$this->outf($lang['item.label.files'], '<h3>%s</h3>');
						?>
						<div class="item-files">
							<?php
							foreach($grouped_files as $file_type => $file_group) {
								$type_name = (isset($types[$file_type])) ? $types[$file_type] : 'Other Files' ;
								?>
								<h4><?php $this->out($type_name); ?></h4>
								<ul>
									<?php
									foreach($file_group as $file) {
										$file_url = $this->router()->makeAbsoluteUri("/item/{$item->url_suffix}/file/{$file->filename}");
										$display_name = (empty($file->name)) ? $file->filename : $file->name ;
										?>
										<li><a href="<?php echo $file_url; ?>"><?php $this->out($display_name); ?></a></li>
										<?php
									}
									?>
								</ul>
								<?php
							}
							?>
						</div>
						<?php
					}

					if (!empty($item->copyright_notice)) {
						$this->outf($lang['item.label.copyright_notice'], '<h4>%s</h4>');
						printf('<p>%s</p>', $item->copyright_notice);
					}

					if (isSensibleDate($item->date_updated)) {
						printf('<p class="note" style="margin-top: 2em;">%s: %s</p>', $lang['item.label.date_updated'], date('d-m-Y', $item->date_updated));
					}
					?>

				</div>


			</div>



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


				$categories = $this->model('categorystore')->findForItem($item->id);
				if ($categories->count()==0) {
					if ($this->model('user')->isAnonymous()) {
						?>
						<p class="note">There are no publically available categories listed at present. You may have to <a href="<?php echo $this->router()->makeAbsoluteUri('/'); ?>">sign in</a> to browse this catalogue.</p>
						<?php
					}
				} else {
					?>
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

			</div>

		</div>

		<?php
		return true;
   }


/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>