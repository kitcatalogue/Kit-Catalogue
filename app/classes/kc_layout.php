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
			echo('<ul>');
			foreach($this->_breadcrumbs as $i => $breadcrumb) {
				if (isset($breadcrumb['href'])) {
					printf('<li><a href="%2$s">%1$s</a></li>', $breadcrumb['title'], $breadcrumb['href']);
				} else {
					printf('<li>%1$s</li>', $breadcrumb['title']);
				}
			}
			echo('</ul>');
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

		if (empty($item->image)) {
			$image_alt = 'No image available';
			$image = $this->router()->makeAbsoluteUri('/images/system/no_image.jpg');
		} else {
			$image_alt = $item->manufacturer .' '. $item->model;
			$image = $this->router()->makeAbsoluteUri($this->model('app.items_www') . $item->getFilePath() .'/'. $item->image);
		}
		?>

		<div class="item">
			<table>
				<tr>
					<td><div class="image"><a href="<?php echo $item_url; ?>"><img src="<?php echo $image; ?>" width="100px" alt="<?php $this->out($image_alt); ?>" /></a></div></td>
					<td>
						<?php
						if ($this->model('security')->checkItemPermission($item, 'site.item.edit')) {
							$edit_url = $this->router()->makeAbsoluteUri('/admin/items/edit/'. $item->id);
							$back_url = base64_encode($this->request()->relativeUri());
							printf('<div class="editlink"><a style="float: right;" class="admin_link" href="%1$s?backlink=%2$s">edit</a></div>', $edit_url, $back_url);
						}
						?>

						<p class="name"><a href="<?php echo $item_url; ?>"><?php $this->out($item->manufacturer .' '. $item->model); ?></a></p>
						<div class="maininfo">
							<?php
							if (!empty($item->short_description)) { printf('<p class="short_description">%s</p>', $this->escape($item->short_description)); }
							?>
						</div>
						<div class="extrainfo">
							<?php
							if (!empty($item->technique)) { printf('<p class="technique"><span class="label">Technique :</span> %s</p>', $this->escape($item->technique)); }
							if (!empty($item->department)) { printf('<p class="department"><span class="label">Department :</span> %s</p>', $this->model('departmentstore')->lookupName($item->department)); }
							?>
						</div>
						<div class="morelink"><a href="<?php echo $item_url; ?>">more details...</a></div>
					</td>
				</tr>
			</table>

		</div>
		<?php
		return true;
	}



	public function renderItemInFull($item, $goto_url) {
		$user = $this->model('user');

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
			printf('<div class="editlink"><a style="float: right;" class="admin_link" href="%1$s?backlink=%2$s">edit</a></div>', $edit_url, $back_url );
		}
		?>

		<div class="item_full">
			<div class="item">

				<h1><?php $this->out($item->manufacturer .' '. $item->model); ?></h1>

				<div class="grid_row">


					<div class="grid_9col">

						<table class="basics">
						<?php
						if (!empty($item->manufacturer)) {
							?>
							<tr>
								<th>Manufacturer</th>
								<td><?php $this->out($item->manufacturer); ?></td>
							</tr>
							<?php
						}
						if (!empty($item->model)) {
							?>
							<tr>
								<th>Model</th>
								<td><?php $this->out($item->model); ?></td>
							</tr>
							<?php
						}
						if (!empty($item->acronym)) {
							?>
							<tr>
								<th>Acronym</th>
								<td><?php $this->out($item->acronym); ?></td>
							</tr>
							<?php
						}
						if (!empty($item->technique)) {
							?>
							<tr>
								<th>Technique</th>
								<td><?php $this->out($item->technique); ?></td>
							</tr>
							<?php
						}
						?>
						</table>

						<?php
						if (!empty($item->full_description)) {
							?>
							<div class="description">
								<div class="label">Description</div>
								<?php
								printf('<div class="detail">%s</div>', $wiki_parser->parse($item->full_description));
								?>
							</div>
							<?php
						}

						if (!empty($item->specification)) {
							?>
							<div class="specification">
								<div class="label">Specification</div>
								<?php printf('<div class="detail">%s</div>', $wiki_parser->parse($this->escape($item->specification))); ?>
							</div>
							<?php
						}

						if (!empty($item->manufacturer_website)) {
							?>
							<div class="specification">
								<div class="label">Manufacturer's Website</div>
								<?php printf('<div class="detail"><a href="%1$s" target="_blank">%1$s</a><br /><p style="font-size: 0.8125em;">(opens in a new window)</p></div>', $item->manufacturer_website); ?>
							</div>
							<?php
						}


						// Custom fields
						if ($this->model('security')->checkItemPermission($item, 'item.customfield.view')) {
							$custom_fields = $this->model('itemstore')->getItemCustomFields($item->id);
							if (!empty($custom_fields)) {
								$field_names = $this->model('itemstore')->getCustomFields();
								if (!empty($field_names)) {
									?>
									<div class="customfields">
										<div class="label">Extra Information</div>
										<table class="grid" style="margin-left: 1em; font-size: 0.875em;">
										<?php
										foreach($field_names as $field_id => $field_name) {
											if ( (isset($custom_fields[$field_id])) && (!empty($custom_fields[$field_id])) ) {
												?>
												<tr>
													<th><?php echo($this->out($field_name)); ?></th>
													<td><?php echo($this->out($custom_fields[$field_id])); ?></td>
												</tr>
												<?php
											}
										}
										?>
										</table>
									</div>
									<?php
								}
							}
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
							?>
							<h2>Additional Files</h2>
							<div class="additional_files">
								<?php
								foreach($grouped_files as $file_type => $file_group) {
									$type_name = (isset($types[$file_type])) ? $types[$file_type] : 'Other Files' ;
									?>
									<h3><?php $this->out($type_name); ?></h3>
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
							printf('<div class="copyright_notice"><div class="detail">%s</div></div>', $item->copyright_notice);
						}
						?>

					</div>


					<div class="grid_3col grid_lastcol">
					<div class="image">
							<?php
							if ($no_image) {
								?>
								<img src="<?php echo $image; ?>" width="150" alt="<?php $this->out($image_alt); ?>" />
								<?php
							} else {
								?>
								<a href="<?php echo $image; ?>" target="_blank"><img src="<?php echo $image; ?>" width="150" alt="<?php $this->out($image_alt); ?>" /></a>
								<?php
							}

							foreach($image_files as $i => $file) {
								if ($file->filename != $item->image) {
									$extra_image = $this->router()->makeAbsoluteUri($this->model('app.items_www') . $item->getFilePath() .'/'. $file->filename);
									?>
									<div class="extra_image">
										<a href="<?php echo $extra_image; ?>" target="_blank"><img src="<?php echo $extra_image; ?>" width="150" alt="" /></a>
									</div>
									<?php
								}
							}
							?>
						</div>
						<div class="boxed" style="font-size: 0.875em;">

						<?php
						if (!empty($item->department)) {
							?>
							<div class="department">
								<div class="label">Department</div>
								<?php printf('<div class="detail">%s</div>', $this->escape($this->model('departmentstore')->lookupName($item->department))); ?>
							</div>
							<?php
						}

						if ($this->model('security')->checkItemPermission($item, 'item.location.view')) {
							if ( (!empty($item->site)) || (!empty($item->building)) || (!empty($item->room)) ) {
								?>
								<div class="location">
									<div class="label">Location</div>
									<div class="detail">
										<?php
										if (!empty($item->site)) {
											$site = $this->model('sitestore')->find($item->site);
											if ($site) {
												$this->out($site->name); echo('<br />');
											}
										}

										if (!empty($item->building)) {
											$building = $this->model('buildingstore')->find($item->building);
											if ($building) {
												$this->out($building->name); echo('<br />');
											}
										}
										if (!empty($item->room)) { $this->out($item->room); }
										?>
									</div>
								</div>
								<?php
							}
						}

						if (!empty($item->contact_email)) {
							?>
							<div class="accesslevel">
								<div class="label">Contact</div>
								<?php printf('<div class="detail"><a href="mailto:%1$s">%1$s</a></div>', $item->contact_email); ?>
							</div>
							<?php
						}

						if ($this->model('security')->checkItemPermission($item, 'item.accesslevel.view')) {
							if (!empty($item->access)) {
								?>
								<div class="access">
									<div class="label">Access Level</div>
									<?php printf('<div class="detail">%1$s</div>', $this->escape($this->model('accesslevelstore')->lookupName($item->access))); ?>
								</div>
								<?php
							}

							if (!empty($item->usergroup)) {
								?>
								<div class="usergroup">
									<div class="label">Usergroup</div>
									<?php printf('<div class="detail">%1$s</div>', $this->escape($item->usergroup)); ?>
								</div>
								<?php
							}
						}

						if (!empty($item->availability)) {
							?>
							<div class="availability">
								<div class="label">Availability</div>
								<?php printf('<div class="detail">%1$s</div>', $this->escape($item->availability)); ?>
							</div>
							<?php
						}
						?>
						</div>
					</div>


				</div>
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