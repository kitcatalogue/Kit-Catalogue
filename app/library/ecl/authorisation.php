<?php
/**
 * Authorisation handler for managing simple access control lists.
 *
 * Authorisations constitue a triple of the form (agent, item, auth).
 *
 * @package  Ecl
 * @version  6.3.0
 */
class Ecl_Authorisation {

	// Private Properties
	protected $_db = null;      // The database connection object
	protected $_table = null;   // The database table to use



	/**
	 * Constructor
	 */
	public function __construct() {
	}// /->__construct()



	/* --------------------------------------------------------------------------------
	 * Public Methods
	*/



	/**
	 * Delete an auth for the given agent and object.
	 *
	 * @param  string  $agent
	 * @param  string  $item
	 * @param  mixed  $auth  The auth(s) to search for.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function delete($agent, $item, $auth) {
		$bind = array (
			':agent'   => $agent ,
			':item'  => $item ,
		);

		$sql__auth_set = $this->_db->prepareSet($auth);

		$sql = "
			DELETE FROM `{$this->_table}`
			WHERE agent=:agent
				AND item=:item
				AND auth IN $sql__auth_set
		";

		$this->_db->execute($sql, $bind);

		return true;
	}// /method



	/**
	 * Delete all authorisations for the given agent.
	 *
	 * @param  mixed  $agent  The agent(s) to search for.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function deleteForAgent($agent) {

		$agent = (array) $agent;
		$sql__agent_set = $this->_db->prepareSet($agent);

		$sql = "
			DELETE FROM `{$this->_table}`
			WHERE agent IN $sql__agent_set
		";

		$this->_db->execute($sql);

		return true;
	}// /method



	/**
	 * Delete all authorisations for the given agent and auth.
	 *
	 * @param  mixed  $agent  The agent(s) to search for.
	 * @param  mixed  $auth  The auth(s) to search for.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function deleteForAgentAuth($agent, $auth) {

		$agent = (array) $agent;
		$sql__agent_set = $this->_db->prepareSet($agent);

		$auth = (array) $auth;
		$sql__auth_set = $this->_db->prepareSet($auth);

		$sql = "
			DELETE FROM `{$this->_table}`
			WHERE agent IN $sql__agent_set
				AND auth IN $sql__auth_set
		";

		$this->_db->execute($sql, $bind);

		return true;
	}// /method



	/**
	 * Delete all authorisations for an item held a agent.
	 *
	 * @param  string  $item  The item(s) to search for.
	 * @param  string  $agent  The agent(s) to search for.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function deleteForAgentItem($agent, $item) {

		$agent = (array) $agent;
		$sql__agent_set = $this->_db->prepareSet($agent);

		$item = (array) $item;
		$sql__item_set = $this->_db->prepareSet($item);

		$sql = "
			DELETE FROM `{$this->_table}`
			WHERE item IN $sql__item_set
				AND agent IN $sql__agent_set
		";

		$this->_db->execute($sql);

		return true;
	}// /method



	/**
	 * Delete all authorisations with the given auth.
	 *
	 * @param  mixed  $auth  The auth(s) to search for.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function deleteForAuth($auth) {

		$auth = (array) $auth;
		$sql__auth_set = $this->_db->prepareSet($auth);

		$sql = "
			DELETE FROM `{$this->_table}`
			WHERE auth IN $sql__auth_set
		";

		$this->_db->execute($sql);

		return true;
	}// /method



	/**
	 * Delete all authorisations for the given item.
	 *
	 * @param  mixed  $item  The item(s) to search for.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function deleteForItem($item) {

		$item = (array) $item;
		$sql__item_set = $this->_db->prepareSet($item);

		$sql = "
			DELETE FROM `{$this->_table}`
			WHERE item IN $sql__item_set
		";

		$this->_db->execute($sql);

		return true;
	}// /method



	/**
	 * Delete all authorisations for the given item and auth.
	 *
	 * @param  mixed  $item  The item(s) to search for.
	 * @param  mixed  $auth  The auth(s) to search for.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function deleteForItemAuth($item, $auth) {

		$item = (array) $item;
		$sql__item_set = $this->_db->prepareSet($item);

		$auth = (array) $auth;
		$sql__auth_set = $this->_db->prepareSet($auth);

		$sql = "
			DELETE FROM `{$this->_table}`
			WHERE item IN $sql__item_set
				AND auth IN $sql__auth_set
		";

		$this->_db->execute($sql);

		return true;
	}// /method



	/**
	 * Check if the given authorisation exists.
	 *
	 * Note, only single values can be given for each parameter.
	 *
	 * @param  string  $agent
	 * @param  string  $item
	 * @param  string  $auth
	 *
	 * @return  boolean  The requested authorisation record exists.
	 */
	public function exists($agent, $item, $auth) {
		$agent = (string) $agent;
		$item = (string) $item;
		$auth = (string) $auth;

		return $this->_queryAuthorisations($agent, $item, $auth);
	}// /method



	/**
	 * Check if any authorisation exists for the given combination of entities.
	 *
	 * @param  mixed  $agent  The agent(s) to search for.
	 * @param  mixed  $item  The item(s) to search for.
	 * @param  mixed  $auth  The auth(s) to search for.
	 *
	 * @return  boolean  At least one combination of the entities exists.
	 */
	public function existsAny($agent, $item, $auth) {
		return $this->_queryAuthorisations($agent, $item, $auth);
	}// /method



	/**
	 * Get all the agents with the given auth, regardless of item.
	 *
	 * @param  mixed  $auth  The auth(s) to search for.
	 *
	 * @return  mixed  An array of agents.  On fail, null.
	 */
	public function findAgentsForAuth($auth) {
		$result = $this->_queryAuthorisations(null, null, $auth);
		return ($result) ? $this->_db->getColumnDistinct('agent') : null ;
	}// /method



	/**
	 * Get all the agents associated with the given item, regardless of auth.
	 *
	 * @param  mixed  $item  The item(s) to search for.
	 *
	 * @return  mixed  An array of agents.  On fail, null.
	 */
	public function findAgentsForItem($item) {
		$result = $this->_queryAuthorisations(null, $item, null);
		return ($result) ? $this->_db->getColumnDistinct('agent') : null ;
	}// /method



	/**
	 * Get all agents with the given authorisation for the given item.
	 *
	 * @param  string  $item  The item(s) to search for.
	 * @param  mixed  $auth  The auth(s) to search for.
	 *
	 * @return  mixed  An array of agents.  On fail, null.
	 */
	public function findAgentsForItemAuth($item, $auth) {
		$result = $this->_queryAuthorisations(null, $item, $auth);
		return ($result) ? $this->_db->getColumnDistinct('agent') : null ;
	}// /method



	/**
	 * Find all auths for the given item, regardless of which agent.
	 *
	 * @param  mixed  $item  The item(s) to search for.
	 *
	 * @return  mixed  An array of auth-strings.  On fail, null.
	 */
	public function findAuthsForItem($item) {
		$result = $this->_queryAuthorisations(null, $item, null);
		return ($result) ? $this->_db->getColumnDistinct('auth') : null ;
	}// /method



	/**
	 * Find all auths for the given item and agent.
	 *
	 * @param  mixed  $item  The item(s) to search for.
	 * @param  mixed  $agent  The agent(s) to search for.
	 *
	 * @return  mixed  An array of auth-strings.  On fail, null.
	 */
	public function findAuthsForAgentItem($agent, $item) {
		$result = $this->_queryAuthorisations($agent, $item, null);
		return ($result) ? $this->_db->getColumnDistinct('auth') : null ;
	}// /method



	/**
	 * Find all auth-strings for the given agent, regardless of the item.
	 *
	 * @param  mixed  $agent  The agent(s) to search for.
	 *
	 * @return  mixed  An array of auths.  On fail, null.
	 */
	public function findAuthsForAgent($agent) {
		$result = $this->_queryAuthorisations($agent, null, null);
		return ($result) ? $this->_db->getColumnDistinct('auth') : null ;
	}// /method



	/**
	 * Find authorisations for the given agent.
	 *
	 * @param  mixed  $agent  The agent(s) to search for.
	 *
	 * @return  mixed  An array of authorisations.  On fail, null.
	 */
	public function findForAgent($agent) {
		$result = $this->_queryAuthorisations($agent, null, null);
		return ($result) ? $this->_db->getResult() : null ;
	}// /method



	/**
	 * Find authorisations for the given agent and auth.
	 *
	 * @param  mixed  $agent  The agent(s) to search for.
	 * @param  mixed  $auth  The authorisation(s) to search for.
	 *
	 * @return  mixed  An array of authorisations.  On fail, null.
	 */
	public function findForAgentAuth($agent, $auth) {
		$result = $this->_queryAuthorisations($agent, null, $auth);
		return ($result) ? $this->_db->getResult() : null ;
	}// /method



	/**
	 * Find authorisations for the given agent and item.
	 *
	 * @param  mixed  $agent  The agent(s) to search for.
	 * @param  mixed  $item  The item(s) to search for.
	 *
	 * @return  mixed  An array of authorisations.  On fail, null.
	 */
	public function findForAgentItem($item, $agent) {
		$result = $this->_queryAuthorisations($agent, $item, null);
		return ($result) ? $this->_db->getResult() : null ;
	}// /method



	/**
	 * Find authorisations for the given auth.
	 *
	 * @param  mixed  $auth  The auth(s) to search for.
	 *
	 * @return  mixed  An array of authorisation-triples.  On fail, null.
	 */
	public function findForAuth($auth) {
		$result = $this->_queryAuthorisations(null, null, $auth);
		return ($result) ? $this->_db->getResult() : null ;
	}// /method



	/**
	 * Find authorisations for the given item.
	 *
	 * @param  mixed  $item  The item(s) to search for.
	 *
	 * @return  mixed  An array of authorisation-triples.  On fail, null.
	 */
	public function findForItem($item) {
		$result = $this->_queryAuthorisations(null, $item, null);
		return ($result) ? $this->_db->getResult() : null ;
	}// /method



	/**
	 * Find authorisations for the given item and auth.
	 *
	 * @param  mixed  $item  The item(s) to search for.
	 * @param  mixed  $auth  The auth(s) to search for.
	 *
	 * @return  mixed  An array of authorisations.  On fail, null.
	 */
	public function findForItemAuth($item, $auth) {
		$result = $this->_queryAuthorisations(null, $item, $auth);
		return ($result) ? $this->_db->getResult() : null ;
	}// /method



	/**
	 * Find all items associated with the given agent, regardless of auth.
	 *
	 * @param  mixed  $agent  The agent(s) to search for.
	 *
	 * @return  mixed  An array of Item IDs.  On fail, null.
	 */
	public function findItemsForAgent($agent) {
		$result = $this->_queryAuthorisations($agent, null, null);
		return ($result) ? $this->_db->getColumnDistinct('item') : null ;
	}// /method



	/**
	 * Find all items for the given agent and auth.
	 *
	 * @param  mixed  $agent  The agent(s) to search for.
	 * @param  mixed  $auth  The auth(s) to search for.
	 *
	 * @return  mixed  An array of auths.  On fail, null.
	 */
	public function findItemsForAgentAuth($agent, $auth) {
		$result = $this->_queryAuthorisations($agent, null, $auth);
		return ($result) ? $this->_db->getColumnDistinct('item') : null ;
	}// /method



	/**
	 * Find all items associated with the given auth, regardless of the agent.
	 *
	 * @param  mixed  $auth  The auth(s) to search for.
	 *
	 * @return  mixed  An array of Item IDs.  On fail, null.
	 */
	public function findItemsForAuth($auth) {
		$result = $this->_queryAuthorisations(null, null, $auth);
		return ($result) ? $this->_db->getColumnDistinct('item') : null ;
	}// /method



	/**
	 * Create new authorisations for the given item, agent and auth.
	 *
	 * @param  string  $agent  The agent(s) to use.
	 * @param  string  $item  The item(s) to use.
	 * @param  mixed  $auth  The auth(s) to use.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function insert($agent, $item, $auth) {

		$agent = (array) $agent;
		$item = (array) $item;
		$auth = (array) $auth;

		$bind = array();

		foreach($agent as $the_agent) {
			foreach($item as $the_item) {
				foreach($auth as $the_auth) {
					$bind[] = array (
						'agent'  => $the_agent ,
						'item'   => $the_item ,
						'auth'   => $the_auth ,
					);
				}
			}
		}

		$this->_db->replaceMulti("{$this->_table}", $bind);

		return true;
	}// /method



	/**
	 * Create new item-authorisations for the given agent.
	 *
	 * $item_auths is an assoc array ( item => array (auths) )
	 *
	 * @param  string  $agent  The agent to use.
	 * @param  array  $item_auths  Assoc array of item-auths.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function insertForAgent($agent, $item_auths) {

		if (!is_array($item_auths)) { return false; }
		if (empty($item_auths)) { return true; }

		$bind = array();

		foreach($item_auths as $item => $auths) {
			$auths = (array) $auths;
			foreach($auths as $i => $auth) {
				$bind[] = array (
					'agent'  => $agent ,
					'item'   => $item ,
					'auth'   => $auth ,
				);
			}
		}

		$this->_db->replaceMulti("{$this->_table}", $bind);

		return true;
	}// /method



	/**
	 * Install the handler into the given database
	 */
	public function install() {
		$sql = "
			CREATE TABLE IF NOT EXISTS `{$this->_table}` (
				`item` varchar(20) NOT NULL default '',
				`agent` varchar(20) NOT NULL default '',
				`auth` varchar(20) NOT NULL default '',
				PRIMARY KEY  (`item`,`agent`,`auth`),
				KEY `agent` (`agent`),
				KEY `auth` (`auth`)
			)
		";
		$this->_db->execute($sql);
	}// /method



	/**
	 * Replace the agent's existing auths with the given item-auths.
	 *
	 * If you do not provide any item-auths, then you will erase everything from the agent.
	 *
	 * $item_auths is an assoc array ( item => array (auths) )
	 *
	 * @param  string  $agent  The agent to use.
	 * @param  array  $item_auths  Assoc array of items and their auths.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function replaceForAgent($agent, $item_auths = array()) {
		$this->deleteForAgent($agent);

		// If we want to set at least some auths
		if (empty($item_auths)) {
			return true;
		} else {
			return $this->insertForAgent($agent, $item_auths);
		}
	}// /method



	/**
	 * Replace any existing auths for the item/agent with the given auths.
	 *
	 * If you do not provide any auths, then effectively you will
	 * erase all auths for that agent/item combination.
	 *
	 * @param  string  $agent  The agent to use.
	 * @param  string  $item  The item to use.
	 * @param  mixed  $auths  The auth(s) to use.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function replaceForAgentItem($agent, $item, $auths = array()) {
		$this->deleteForAgentItem($agent, $item);

		// If we want to set at least some auths
		if (empty($auths)) {
			return true;
		} else {
			return $this->insert($agent, $item, $auths);
		}
	}// /method



	/**
	 * Set the DB database object to be used by the Handler
	 *
	 * @param  object $db  The Database object to use
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setDatabase($db) {
		$this->_db = $db;
		return true;
	}// /method



	/**
	 * Set the table prefix to be used by the Handler
	 *
	 * @param  string $table_prefix  The prefix to put on every table manipulated by this Handler
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setTable($table) {
		$this->_table = $table;
		return true;
	}// /method



	/* --------------------------------------------------------------------------------
	 * Private Methods
	*/



	/**
	 * Queries the authorisation table, but DOES NOT fetch the results from the database.
	 *
	 * Essentially acts as a query builder for authorisation table requests.
	 * Clients should call getColumn(), getResult(), etc themselves.
	 *
	 * @param  mixed  $agent  The agent(s) to search for.  (Set to null to ignore in query).
	 * @param  mixed  $item  The item(s) to search for.  (Set to null to ignore in query).
	 * @param  mixed  $auth  The auth(s) to search for.  (Set to null to ignore in query).
	 *
	 * @return  boolean  There were results to the query.
	 */
	protected function _queryAuthorisations($agent, $item, $auth) {

		$clauses = null;
		$where_clause = null;

		if ($agent) {
			$set = $this->_db->prepareSet($agent);
			$clauses[] = "(agent IN $set)";
		}

		if ($item) {
			$set = $this->_db->prepareSet($item);
			$clauses[] = "(item IN $set)";
		}

		if ($auth) {
			$set = $this->_db->prepareSet($auth);
			$clauses[] = "(auth IN $set)";
		}

		if (is_array($clauses)) {
			$where_clause = 'WHERE '.implode(' AND ', $clauses);
		}

		if ($where_clause) {
			$sql = "
				SELECT agent, item, auth
				FROM `{$this->_table}`
			$where_clause
				ORDER BY agent, item, auth
			";

			$this->_db->query($sql);

			return $this->_db->hasResult();
		}

		return false;
	}// /method



}// /class
?>