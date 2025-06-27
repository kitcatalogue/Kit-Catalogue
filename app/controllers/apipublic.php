<?php
class Controller_Apipublic extends Ecl_Mvc_Controller {


	protected $_default_format = 'json';
	protected $_valid_formats = array('csv', 'html', 'json', 'rdf', 'xml');



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	public function beforeAction() {
		if (true !== $this->model('api.enabled')) {
			$this->abort();
			?>
			<pre>
			Kit-Catalogue API disabled.

			The setting  <em>$config['api.enabled'] = false;</em>  in  <em>local/local_config.php</em>
			To enable the API, change the configuration to  <em>$config['api.enabled'] = true;</em>

			For more about the API, see  <em>docs/api.txt</em>
			</pre>
			<?php
			return;
		}

		if (true !== $this->model('api.public.enabled')) {
			$this->abort();
			?>
			<pre>
			Kit-Catalogue Public API disabled.

			The setting  <em>$config['api.public.enabled'] = false;</em>  in  <em>local/local_config.php</em>
			To enable the API, change the configuration to  <em>$config['api.public.enabled'] = true;</em>

			For more about the API, see  <em>docs/api.txt</em>
			</pre>
			<?php
			return;
		}

		$this->view()->api_root = $this->router()->makeAbsoluteUri('/api/public');

		$dot_pos = strpos($this->_action, '.');
		if ($dot_pos !== false) {
			$new_action = substr($this->_action, 0, $dot_pos);
			if (!empty($new_action)) {
				$this->_action = $new_action;
			}
		}


		$this->layout()->addStylesheet($this->router()->makeAbsoluteUri('/css/api.css'));
	}// /method



	public function actionIndex() {
		$this->view()->available_calls = array (
			"/items.json"                        => 'List all available items.' ,
			"/items.json?search=[keywords]"      => 'Search the available items for a given query. Use the querystring parameter to define the terms to search for.' ,
			"/items.json?category=[category-id]" => 'Search for items with the given category id. Use the querystring parameter to define the category to search for.' ,
			"/items.json?ou=[ou-id]"             => 'Search for items with the given Organisational Unit id. Use the querystring parameter to define the OU to search for.' ,
			"/items.json?ou-name=[ou-name]"      => 'Search for items with the given Organisational Unit name. Use the querystring parameter to define the OU.' ,
			"/items.json?tag=[tag-name]"         => 'Search for items with the given tag. Use the querystring parameter to define the tag to search for.' ,
			"/item/[id-number]"                  => 'Show an individual item. Use the item ID number path parameter to define which item to return.' ,
			"/categories.json"                   => 'List all available categories.' ,
		);

		$this->view()->render("api_public_index");
	}



	public function actionItem() {
		include($this->model('app.include_root').'/classes/itemrenderer.php');
		$public_fields = array();
		$this->view()->renderer = new Itemrenderer($public_fields, $this->model());

		$format = $this->_getFormat();

		$item = $this->model('itemstore')->find($this->param('id'));
		if (!$item) { echo '{}'; die(); }

		$this->view()->item = $item;
		$this->view()->render("api_public_item.{$format}");
	}// /method




	public function actionItems() {
		include($this->model('app.include_root').'/classes/itemrenderer.php');
		$public_fields = array();
		$this->view()->renderer = new Itemrenderer($public_fields, $this->model());

		$format = $this->_getFormat();

		$this->view()->items = array();

		if ($this->request()->get('search')) {
			$this->view()->items = $this->model('itemstore')->searchItems($this->request()->get('search'), KC__VISIBILITY_PUBLIC);
		} elseif ($this->request()->get('tag')) {
			$this->view()->items = $this->model('itemstore')->findForTag($this->request()->get('tag'), KC__VISIBILITY_PUBLIC);
		} elseif ($this->request()->get('category')) {
			$this->view()->items = $this->model('itemstore')->findForCategory($this->request()->get('category'), KC__VISIBILITY_PUBLIC);
		} elseif ($this->request()->get('ou')) {
			$this->view()->items = $this->model('itemstore')->findForOU($this->request()->get('ou'), true, KC__VISIBILITY_PUBLIC);
		} elseif ($this->request()->get('ou-name')) {
			$ou = $this->model('organisationalunitstore')->findForName($this->request()->get('ou-name'));
			if ($ou) {
				$this->view()->items = $this->model('itemstore')->findForOU($ou->id, true, KC__VISIBILITY_PUBLIC);
			}
		} else {
			$this->view()->items = $this->model('itemstore')->findAll(KC__VISIBILITY_PUBLIC);
		}

		$this->view()->render("api_public_items.{$format}");
	}// /method



	public function actionCategories() {
		include($this->model('app.include_root').'/classes/categoryrenderer.php');

		$this->view()->renderer = new Categoryrenderer($this->model());

		$format = $this->_getFormat();

		$this->view()->categories = $this->model('categorystore')->findAllUsed(KC__VISIBILITY_PUBLIC);

		$this->view()->render("api_public_category_categories.{$format}");
	}



/* --------------------------------------------------------------------------------
 * Private Methods
 */



	public function _getFormat() {
		$format = $this->request()->extension() ?? $this->_default_format ;
		$format = strtolower($format);
		if (!in_array($format, $this->_valid_formats)) { $format = $this->_default_format; }
		return $format;
	}



}// /class


