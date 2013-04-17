<?php
class Patch_v2_0_0 extends Ecl_Db_Migration {


	/* --------------------------------------------------------------------------------
	 * Public Methods
	 */



	public function down() {
		return false;
	}



	public function up() {

		$model = $this->getParam('model');
		if (!$model) { return false; }

		$lang = $model->get('lang');



		$this->_schema->patchTable('building', array (
			'url' => array (
				'type'    => 'varchar(250)',
				'default' => '',
				'after'   => 'name',
				),
		));



		$this->_schema->patchTable('item', array (
			'ou_id' => array (
				'type'    => 'int(10) unsigned',
				'default' => 0,
				'after'   => 'portability',
				),
			'embedded_content' => array (
				'type'    => 'text',
				'default' => null,
				),
		));



		$this->_db->execute("
			CREATE TABLE IF NOT EXISTS `item_editor` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `item_id` int(10) unsigned NOT NULL,
			  `username` varchar(50) NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `item_id` (`item_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");



		$this->_db->execute("
			CREATE TABLE IF NOT EXISTS `item_link` (
				`id` INTEGER AUTO_INCREMENT NOT NULL,
				`item_id` INTEGER UNSIGNED NOT NULL,
				`name` VARCHAR(250) NOT NULL,
				`url` VARCHAR(250) NOT NULL,
				`file_type` int(10) unsigned NOT NULL DEFAULT '0',
				PRIMARY KEY (`id`),
				KEY `item_id` (`item_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");



		$this->_db->execute("
			CREATE TABLE IF NOT EXISTS `log_enquiry` (
				`log_enquiry_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`date_enquiry` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				`item_id` int(10) unsigned NOT NULL DEFAULT '0',
				`item_name` varchar(250) NOT NULL DEFAULT '',
				`user_name` varchar(250) NOT NULL DEFAULT '',
				`user_email` varchar(250) NOT NULL DEFAULT '',
				`user_phone` varchar(20) NOT NULL DEFAULT '',
				`user_org` varchar(250) NOT NULL DEFAULT '',
				`user_role` varchar(250) NOT NULL DEFAULT '',
				`user_deadline` varchar(50) NOT NULL DEFAULT '',
				`enquiry_type` varchar(15) NOT NULL DEFAULT '',
				`enquiry_text` MEDIUMTEXT,
				PRIMARY KEY (`log_enquiry_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");



		$this->_db->execute("
			CREATE TABLE IF NOT EXISTS  `ou_tree` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`tree_left` int(10) unsigned NOT NULL,
				`tree_right` int(10) unsigned NOT NULL,
				`tree_level` int(10) unsigned NOT NULL,
				`name` varchar(250) DEFAULT '',
				`ref` int(10) unsigned DEFAULT NULL,
				PRIMARY KEY (`id`),
				KEY `tree_left` (`tree_left`),
				KEY `tree_right` (`tree_right`),
				KEY `tree_level` (`tree_level`),
				KEY `ref` (`ref`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->_db->execute("
			REPLACE INTO `ou_tree` (id, tree_left, tree_right, tree_level, name, ref)
			VALUES (1, 1, 2, 0, 'Catalogue', 1);
		");



		$this->_db->execute("
			CREATE TABLE IF NOT EXISTS `ou_tree_label` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(250) DEFAULT '',
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$sql__org_label = $this->_db->prepareValue($lang['org.label']);
		$sql__dept_label = $this->_db->prepareValue($lang['dept.label']);

		$this->_db->execute("
			REPLACE INTO `ou_tree_label` (id, name) VALUES
			(1, $sql__org_label),
			(2, $sql__dept_label);
		");



		$this->_db->execute("
			CREATE TABLE IF NOT EXISTS  `ou` (
				`ou_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(250) NOT NULL,
				`url` varchar(250) DEFAULT NULL,
				`item_count_internal` int(10) unsigned DEFAULT '0',
				`item_count_public` int(10) unsigned DEFAULT '0',
				PRIMARY KEY (`ou_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->_db->execute("
		REPLACE INTO `ou` (ou_id, name, url)
			VALUES (1, 'Catalogue', '');
		");


		// Copy existing organisations to OUs
		$ou_store = $model->get('organisationalunitstore');
		$ou_tree = $model->get('ou_tree');


		$this->_db->query("
			SELECT *
			FROM `organisation`
			ORDER BY organisation_id
		");

		$default_org = null;
		$org_map = array();

		if (!$this->_db->hasResult()) {
			$ou = $ou_store->newOrganisationalunit();
			$ou->name = $model->get('org.title');
			$new_id = $ou_store->insert($ou, 1);

			$default_org = $new_id;
		} else {
			$org_list = $this->_db->getResult();

			foreach($org_list as $org) {
				$ou = $ou_store->newOrganisationalunit();
				$ou->name = $org['name'];
				$new_id = $ou_store->insert($ou, 1);

				if ($new_id) {
					if (!$default_org) { $default_org = $new_id; }

					$org_map[$org['organisation_id']] = $new_id;

					$sql__org_id = $this->_db->prepareValue($org['organisation_id']);
					$this->_db->update('item', array('ou_id' => $new_id), "organisation=$sql__org_id");
				}
			}
		}


		// Copy existing departments to OUs

		// Work out which department belongs to which organisation
		// Check the item counts for each dept-org combination
		// the most items for a department will be the parent-org we use!
		$this->_db->query("
			SELECT department_id, organisation, count(item_id) as item_count
			FROM item i
			GROUP BY department_id, organisation
			ORDER BY department_id, item_count DESC
		");

		$dept_orgs = array();
		if ($this->_db->hasResult()) {
			$temp = $this->_db->getResult();
			foreach($temp as $row) {
				if (
					(!array_key_exists($row['department_id'], $dept_orgs))
					|| ($row['item_count'] > $dept_orgs[$row['department_id']]['count'])
					) {
					if (!array_key_exists($row['department_id'], $dept_orgs)) {
						$dept_orgs[$row['department_id']] = array ('org_ou' => null, 'count' => null);
					}
					if (array_key_exists($row['organisation'], $org_map)) {
						$dept_orgs[$row['department_id']]['org_ou'] = $org_map[$row['organisation']];
						$dept_orgs[$row['department_id']]['count'] = $row['item_count'];
					} else {
						$dept_orgs[$row['department_id']]['org_ou'] = $default_org;
						$dept_orgs[$row['department_id']]['count'] = $row['item_count'];
					}
				}
			}
		}


		$this->_db->query("
			SELECT *
			FROM `department`
			ORDER BY department_id
		");

		if ($this->_db->hasResult()) {
			$dept_list = $this->_db->getResult();
			foreach($dept_list as $dept) {
				$ou = $ou_store->newOrganisationalunit();
				$ou->name = $dept['name'];

				$parent_org = (array_key_exists($dept['department_id'], $dept_orgs)) ? $dept_orgs[$row['department_id']]['org_ou'] : $default_org ;

				$new_id = $ou_store->insert($ou, $parent_org);

				if ($new_id) {
					$sql__dept_id = $this->_db->prepareValue($dept['department_id']);
					$sql_dept_obj_id = $this->_db->prepareValue("dept_{$dept['department_id']}");
					$sql_ou_obj_id = $this->_db->prepareValue("ou_{$new_id}");

					$this->_db->update('item', array('ou_id' => $new_id), "department_id=$sql__dept_id");
					$this->_db->update('system_authorisation', array('item' => $new_id), "item=$sql_dept_obj_id");
				}
			}
		}

		// Set any remaining items to the default organisation OU.
		$this->_db->update('item', array('ou_id' => $default_org), "ou_id=0 OR ou_id IS NULL OR ou_id=''");

		$ou_store->rebuildItemCounts();



		$this->_schema->patchTable('site', array (
			'url' => array (
				'type'    => 'varchar(250)',
				'default' => '',
				'after'   => 'name',
				),
		));



		$this->_db->replaceMulti('system_info', array (
			array (
				'name'  => 'database_version',
				'value' => '2.0.0',
				),
			array (
				'name'  => 'database_updated',
				'value' => date('c'),
				),
		));



		return true;
	}



}


