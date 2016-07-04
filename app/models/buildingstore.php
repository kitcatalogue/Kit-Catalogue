<?php


include_once('building.php');



/**
 * building Store class
 *
 * @version 1.0.0
 */
class Buildingstore {

	// Private Properties
	protected $_db = null;

	protected $_lookup = null;


	/**
	 * Constructor
	 *
	 * @param  object  $database  An Ecl_Db data access object.
	 */
	public function __construct($database) {
		$this->_db = $database;
	}// /->__construct()



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Convert a domain object to a database row
	 *
	 * @param  object  $object
	 *
	 * @return  array
	 */
	public function convertObjectToRow($object) {
		$row = array (
			'building_id'           => $object->id ,
			'code'                  => $object->code ,
			'name'                  => $object->name ,
			'site_id'               => $object->site_id ,
			'latitude'              => $object->latitude ,
			'longitude'             => $object->longitude ,
			'url'                   => $object->url ,
            'item_count_internal'   => $object->item_count_internal ,
            'item_count_public'     => $object->item_count_public ,
		);

		return $row;
	}// /method



	/**
	 * Convert a database row to a domain object
	 *
	 * @param  array  $row
	 *
	 * @return  object
	 */
	public function convertRowToObject($row) {
		$object = $this->newbuilding();

		$object->id = $row['building_id'];
		$object->code = $row['code'];
		$object->name = $row['name'];
		$object->site_id = $row['site_id'];
		$object->latitude = $row['latitude'];
		$object->longitude = $row['longitude'];
		$object->url = $row['url'];
        $object->item_count_internal = $row['item_count_internal'];
        $object->item_count_public = $row['item_count_public'];

		return $object;
	}// /method



	/**
	 * Delete a building.
	 *
	 * @param  integer  $id  The record to delete.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function delete($id) {
		$id = $this->_db->prepareValue($id);
		$affected_count = $this->_db->delete('building', "building_id=$id");

		$binds = array (
			'building_id'  => null ,
		);

		$this->_db->update('item', $binds, "building_id=$id");

		return ($affected_count>0);
	}// /method



	/**
	 * Find the building(s) specified.
	 *
	 * @param  mixed  $id  The ID, or array of IDs, to find.
	 *
	 * @return  mixed  The object, or array of objects, requested.  On fail, null.
	 */
	public function find($id) {
		if (is_array($id)) {
			$id_set = $this->_db->prepareSet($id);

			return $this->_db->newRecordset("
				SELECT *
				FROM building
				WHERE building_id IN $id_set
				ORDER BY name ASC
			", null, array($this, 'convertRowToObject') );
		} else {

			$binds = array (
				'building_id'  => (int) $id ,
			);

			$row_count = $this->_db->query("
				SELECT *
				FROM building
				WHERE building_id=:building_id
			", $binds);
			return $this->_db->getObject(array($this, 'convertRowToObject') );
		}
	}// /method



	/**
	 * Find all buildings.
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findAll() {
		return $this->_db->newRecordset("
			SELECT *
			FROM building
			ORDER BY name ASC
		", null, array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find all buildings used by at least one item of equipment, for the given visibility.
	 *
	 * @param  integer  $visibility  (optional) The item visibility to check
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findAllUsed($visibility = null) {

		if (empty($visibility)) { $visibility = KC__VISIBILITY_INTERNAL; }
		$sql__visibility = $this->_db->escapeString($visibility);

		return $this->_db->newRecordset("
			SELECT b.*
			FROM building b INNER JOIN item i ON b.building_id=i.building_id
			WHERE (i.visibility & $sql__visibility)=$sql__visibility
			ORDER BY b.name ASC
		", null, array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find buildings for OU.
	 *
	 * @param  string  $ou_id  The OU ID to check for.
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findForOU($ou_id) {

		$binds = array (
			'ou_id'  => $ou_id ,
		);

		return $this->_db->newRecordset("
			SELECT DISTINCT s.*
			FROM building s
				INNER JOIN item i ON s.building_id=i.building
			WHERE i.ou_id=:ou_id
			ORDER BY name ASC
		", $binds, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Find a building by its name.
	 *
	 * @param  string  $name
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findForName($name) {

		$binds = array (
			'name'  => $name ,
		);

		$this->_db->query("
			SELECT *
			FROM building
			WHERE name=:name
		", $binds);

		return $this->_db->getObject(array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Lookup the name of a building using its ID.
	 *
	 * Use find() if you only want a single lookup.
	 * This method uses caching to speed up subsequent lookups, so will be faster if you need more than one.
	 *
	 * @param  integer  $building_id
	 * @param  string  $default  (optional) The default name to return.
	 *
	 * @return  string  The building's name.  On fail, $default.
	 */
	public function lookupName($building_id, $default = '') {
		if (null === $this->_lookup) {
			$this->_lookup = $this->findAll()->toAssoc('id', 'name');
		}
		return (isset($this->_lookup[$building_id])) ? $this->_lookup[$building_id] : $default ;
	}// /method



	/**
	 * Find an existing building with the given name, or create a new one with the name.
	 *
	 * If $building_to_create is given, then it will be inserted and returned as the new building.
	 * If it is left empty, then a new building will be created.
	 * Any newly created building will use the given name.
	 *
	 * @param  string  $name
	 * @param  mixed  $building_to_create  (optional)
	 *
	 * @return  object  The building
	 */
	public function findOrCreateForName($name, $building_to_create = null) {
		$building = $this->findForName($name);
		if (!empty($building)) { return $building; }

		if (empty($building_to_create)) {
			$building = $this->newBuilding();
		}
		$building->name = $name;
		$building->id = $this->insert($building);
		return $building;
	}// /method



	/**
	 * Insert a new building.
	 *
	 * @param  object  $object  The building to create.
	 *
	 * @return  mixed  The new id created.  On fail, null.
	 */
	public function insert($object) {

		$binds = $this->convertObjectToRow($object);

		unset($binds['building_id']);   // Don't insert the id, we want a new one

		$new_id = $this->_db->insert('building', $binds);

		return ($new_id>0) ? $new_id : null ;
	}// /method



	/**
	 * Get a new instance of a Building object.
	 *
	 * @return  object  A Building object.
	 */
	public function newBuilding() {
		return new Building();
	}// /method



	/**
	 * Update an existing building.
	 *
	 * @param  object  $object
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function update($object) {
		$binds = $this->convertObjectToRow($object);

		$id = $this->_db->prepareValue($object->id);

		$affected_count = $this->_db->update('building', $binds, "building_id=$id");

		return ($affected_count>0);
	}// /method

    	/**
	 * Find all categories IDs and Names, and their respective counts, for the items specified.
	 *
	 * @param  array  $id  The array of item IDs to find.
	 *
	 * @return  array  The information retrieved.
	 */
	public function findCountsForItemBuildings($id) {


	 // This may not work...
    }// /method
    
    /**
	 * Rebuild all the item counts per building.
	 *
	 * @return  boolean  The operation was successful.
	 */
    public function rebuildItemCounts() {
        // needs to be adapted
		$visibility_types = array (
			'internal' => '
					SELECT building_id, count(item_id) AS count
					FROM item
					GROUP BY building_id 
					ORDER BY building_id
				' ,
			'public' => '
					SELECT building_id, count(item_id) AS count
					FROM item
					WHERE visibility = \''. KC__VISIBILITY_PUBLIC .'\'
					GROUP BY building_id 
                    ORDER BY building_id
				' ,
		);


		// Get all the counts for each category
		$update_info = null;

		foreach($visibility_types as $type => $sql) {
			$row_count = $this->_db->query($sql);

			if ($row_count>0) {
				$counts = $this->_db->getResultAssoc('building_id', 'count');

				if ($counts) {
					foreach($counts as $building_id => $item_count) {
						$building_id = (int) $building_id;
						$update_info[$building_id][$type] = $item_count;
					}
				}
			}
		}


		// Reset all the category item counts to 0
		$binds = array (
			'item_count_internal'  => 0 ,
			'item_count_public'    => 0 ,
		);
		$this->_db->update('building', $binds);

		// If there are counts to update in the database
		if ($update_info) {
			foreach($update_info as $building_id => $counts) {
				$binds = array (
					'item_count_internal'  => (isset($counts['internal'])) ? $counts['internal'] : 0 ,
					'item_count_public'    => (isset($counts['public'])) ? $counts['public'] : 0 ,
				);

				$this->_db->update('building', $binds, "building_id='$building_id'");
			}
		}

		return true;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>
