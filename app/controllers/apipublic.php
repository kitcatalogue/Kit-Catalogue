<?php
class Controller_Apipublic extends Ecl_Mvc_Controller {


	protected $_default_format = 'json';
	protected $_valid_formats = array('csv', 'html', 'json', 'xml');



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
			"/items.json"                   => 'List all available items.' ,
			"/items.json?search=[keywords]" => 'Search the available items for a given query. Use the querystring parameter to define the terms to search for.' ,
		);

		$this->view()->render("api_public_index");
	}



	public function actionItems() {
		include($this->model('app.include_root').'/classes/itemrenderer.php');
		$public_fields = array();
		$this->view()->renderer = new Itemrenderer($public_fields, $this->model());

		$format = $this->_getFormat();

		if ($this->request()->get('search')) {
			$this->view()->items = $this->model('itemstore')->searchItems($this->request()->get('search'), KC__VISIBILITY_PUBLIC);
		} else {
			$this->view()->items = $this->model('itemstore')->findAll(KC__VISIBILITY_PUBLIC);
		}

		$this->view()->render("api_public_items.{$format}");
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



	public function _getFormat() {
		$format = strtolower($this->request()->extension());
		if (!in_array($format, $this->_valid_formats)) { $format = $this->_default_format; }

		return $format;
	}



}// /class


