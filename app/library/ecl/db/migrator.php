<?php
/**
 * Database Migrator Class
 *
 * @package  Ecl
 * @version  1.0.1
 */
class Ecl_Db_Migrator {

	protected $_db = null;
	protected $_schema = null;

	protected $_config = array (
		'path'           => null ,
		'params'         => array() ,
		'version_length' => 3 ,
	);



	public function __construct($db, $schema, $config = array()) {
		$this->_db = $db;
		$this->_schema = $schema;

		$this->_config = array_merge($this->_config, $config);
		$this->_config['path'] = rtrim(realpath($this->_config['path']), '/');
		$this->_config['params'] = (array) $this->_config['params'];

		if (!$this->_schema->tableExists('db_migration')) {
			$this->_schema->createTable('db_migration', array (
				'version' => array (
					'type'    => 'int(10) unsigned' ,
					'default' => 0 ,
				) ,
				'date_updated' => array (
					'type'    => 'datetime' ,
				) ,
			));

			$this->_db->insert('db_migration', array(
				'version'      => 0 ,
				'date_updated' => null ,
			));
		}
	}



	/* --------------------------------------------------------------------------------
	 * Public Methods
	 */



	public function getCurrentVersion() {
		$this->_db->query("
			SELECT version
			FROM `db_migration`
		");
		return (int) ($this->_db->hasResult()) ? $this->_db->getValue() : 0 ;
	}



	public function getLatestVersion() {
		$migs = $this->listMigrations();

		if (empty($migs)) { return 0; }

		$last_filename = end($migs);
		return $this->_getVersionForFilename($last_filename);
	}



	public function listMigrations() {
		$pattern = '#^\d{'. $this->_config['version_length'] .'}_.*\.php$#';
		return Ecl_Helper_Filesystem::getFiles($this->_config['path'], $pattern);
	}



	public function listLatestMigrations() {
		$start = $this->getCurrentVersion();
		$end = $this->getLatestVersion();

		if ($start >= $end) { array(); }

		$migrations = array();
		for($i=$start + 1; $i<=$end; $i++) {
			$migrations[] = $this->_getFilenameForVersion($i);
		}

		return $migrations;
	}



	public function toLatest() {
		return $this->toVersion($this->getLatestVersion());
	}



	/**
	 * Migrate the database to the given version.
	 *
	 * @param  integer  $version
	 *
	 * @throws \RuntimeException
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function toVersion($version) {
		$start = $this->getCurrentVersion();


		if ($start == $version) { return true; }

		if ($start < $version) {
			$method = 'up';
			$start++;
			$end = $version + 1;
			$step = 1;
		} else {
			$method = 'down';
			$end = $version;
			$step = -1;
		}


		for($i=$start; $i!=$end; $i += $step) {

			$filename = $this->_getFilenameForVersion($i);

			if (empty($filename)) {
				throw new \RuntimeException("Migration $method to $version failed. Unable to find $i.", 0);
			}

			$filepath = "{$this->_config['path']}/{$filename}";

			if (!file_exists($filepath)) {
				throw new \RuntimeException("Migration $method to $version failed. Unable to find file for $i.", 0);
			}

			include($filepath);
			$class = $this->_getClassForFilename($filename);

			if (!class_exists($class, false)) {
				throw new \RuntimeException("Migration $method to $version failed. Unable to find class for $i.", 0);
			}

			$migration = new $class($this->_db, $this->_schema, $this->_config['params']);

			$result = false;
			if ($migration) {
 				$result = $migration->$method();
			}
 			if (!$result) {
				throw new \RuntimeException("Migration $method to $version failed on version $i.", 0);
 			}

			$this->_updateVersion($version);
		}

		return true;
	}



	/* --------------------------------------------------------------------------------
	 * Private Methods
	 */



	protected function _getClassForFilename($filename) {
		if (!preg_match('#\d{'. $this->_config['version_length'] .'}_(.*)\.php#', $filename, $matches)){
			return '';
		}
		return ucfirst(preg_replace('/[^0-9A-Za-z_]/', '_', $matches[1]));
	}



	protected function _getFilenameForVersion($version) {
		$version_padded = str_pad($version, $this->_config['version_length'], '0', STR_PAD_LEFT);

		$files = glob($this->_config['path'] .'/'. $version_padded .'_*.php');
		return (1 == count($files)) ? basename($files[0]) : '' ;
	}



	protected function _getVersionForFilename($filename) {
		return (int) substr($filename, 0, $this->_config['version_length']);
	}



	protected function _updateVersion($version) {
		return $this->_db->update('db_migration', array(
			'version'      => $version,
			'date_updated' => $this->_db->formatDate(time()),
		));
	}


}


