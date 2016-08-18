<?php
/**
 * A class to category item records found through the API.
 *
 * @version 1.0.0
 */
class CategoryRenderer {

	protected $_model = null;
	protected $_router = null;



	public function __construct($model) {
		$this->_model = $model;
		$this->_router = $model->get('router');

		$this->_wikiparser = Ecl::factory('Ecl_Parser_Wikicode');
	}



	/* ------------------------------------------------------------------------
	 * Public Methods
	 */



	public function csv($string) {
		return str_replace('"', '""', $string);
	}



	public function getCategoryLink($category) {
		return $this->_router->makeAbsoluteUri("/id/category/{$category->idslug}");
	}



	public function getCategoryUri($category) {
		return $this->_router->makeAbsoluteUri("/id/category/{$category->id}");
	}



	public function html($string) {
		return htmlentities($string, ENT_QUOTES, 'UTF-8');
	}



	/**
	 * Get a named value/object from the model.
	 *
	 * Using a null $name parameter will return the model itself.
	 *
	 * @param  string  $name  (default: null.
	 *
	 * @return  mixed  The model item requested. On fail, null.
	 */
	public function model($name = null) {
		return (is_null($name)) ? $this->_model : $this->_model->get($name);
	}// /method



	public function outIfNotEmpty($value, $output) {
		if (!empty($value)) {
			echo $output;
		}
	}



	public function renderAsCsv($category) {
		$fields = array(
			'id' => $this->csv($this->getCategoryUri($category)) ,
			'name' => $this->csv($category->name) ,
			'external_schema_uri' => $this->csv($category->external_schema_uri) ,
			'item_count_public' => $this->csv($category->item_count_public) ,
			'link' => $this->csv($this->getCategoryLink($category)) ,
		);
		echo '"' . implode('","', $fields) .'"';
	}



	public function renderAsHtml($category) {
		?>
		<dl>

			<dt data-key="id">ID</dt>
			<dd data-key="id"><?php echo $this->html($this->getCategoryUri($category)); ?></dd>

			<dt data-key="name">Name</dt>
			<dd data-key="name"><?php echo $this->html($category->name); ?></dd>

			<dt data-key="externalschemauri">External Schema Uri</dt>
			<dd data-key="externalschemauri"><?php echo $this->html($category->external_schema_uri); ?></dd>

			<dt data-key="publicitemcount">Public Item Count</dt>
			<dd data-key="publicitemcount"><?php echo $this->html($category->item_count_public); ?></dd>

			<dt data-key="link">Link</dt>
			<dd data-key="link"><a href="<?php echo $this->getCategoryLink($category) ?>"><?php echo $this->html($this->getCategoryLink($category)); ?></a></dd>

		</dl><?php
	}



	public function renderAsJson($category) {
		$obj = new StdClass();

		$obj->id = $this->getCategoryUri($category);
		$obj->name = $category->name;
		$obj->external_schema_uri = $category->external_schema_uri;
		$obj->item_count_public = $category->item_count_public;
		$obj->link = $this->getCategoryLink($category);

		echo json_encode($obj, JSON_FORCE_OBJECT);
	}



	public function renderAsRdf($category) {
		// @todo : renderAsRdf()
		die('METHOD NOT IMPLEMENTED - renderAsRdf()');
	}



	public function renderAsRSS($category) {
		// @todo : renderAsRSS()
		die('METHOD NOT IMPLEMENTED - renderAsRSS()');
	}



	public function renderAsTurtle($category) {
		// @todo : renderAsTurtle()
		die('METHOD NOT IMPLEMENTED - renderAsTurtle()');
	}



	public function renderAsXml($category) {
		?>
		<category id="<?php echo $this->xml($this->getCategoryUri($category)); ?>">
			<name><?php echo $this->xml($category->name); ?></name>
			<?php
			$this->outIfNotEmpty($category->external_schema_uri, sprintf('<externalschemauri>%1$s</externalschemauri>'."\n", $this->xml($category->external_schema_uri)));
			printf('<itemcountpublic>%1$s</itemcountpublic>'."\n", $this->xml($category->item_count_public));
			printf('<link>%1$s</link>'."\n", $this->html($this->getCategoryLink($category)));
			?>
		</category>
		<?php
	}



	public function xml($string) {
		return str_replace(array('&', '<', '>', '"', "'"), array('&#x26;', '&#x3C;', '&#x3E;', '&#x22;', '&#x27;'), $string);
	}



}


