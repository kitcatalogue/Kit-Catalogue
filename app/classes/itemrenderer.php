<?php
/**
 * A class to render item records found through the API.
 *
 * @version 1.0.0
 */
class ItemRenderer {

	protected $_fields = null;
	protected $_model = null;
	protected $_router = null;



	public function __construct($allowed_fields, $model) {
		$this->_fields = (array) $allowed_fields;
		$this->_model = $model;
		$this->_router = $model->get('router');

		$this->_wikiparser = Ecl::factory('Ecl_Parser_Wikicode');
	}



	/* ------------------------------------------------------------------------
	 * Public Methods
	 */



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



	public function renderAsCsv($item) {
		// @todo : renderAsCsv()
		die('METHOD NOT IMPLEMENTED - renderAsCsv()');
	}



	public function renderAsHtml($item) {
		$app_www = $this->_router->makeAbsoluteUri('/');
		$link = $this->_router->makeAbsoluteUri("/id/item/{$item->idslug}");
		?>
		<dl>

			<dt data-key="id">ID</dt>
			<dd data-key="id"><?php echo $this->html($link); ?></dd>

			<dt data-key="name">Name</dt>
			<dd data-key="name"><?php echo $this->html($item->name); ?></dd>

			<dt data-key="manufacturer">Manufacturer</dt>
			<dd data-key="manufacturer"><?php echo $this->html($item->manufacturer); ?></dd>

			<dt data-key="model">Model</dt>
			<dd data-key="model"><?php echo $this->html($item->model); ?></dd>

			<dt data-key="description">Description</dt>
			<dd data-key="description"><?php echo $this->_wikiparser->parse($item->full_description); ?></dd>

			<dt data-key="contact1">Contact 1</dt>
			<dd data-key="contact1"><?php echo $this->html($item->contact_1_email); ?></dd>

			<dt data-key="contact2">Contact 2</dt>
			<dd data-key="contact2"><?php echo $this->html($item->contact_2_email); ?></dd>

			<?php
			$image = '';
			if (!empty($item->image)) {
				$image = $this->_router->makeAbsoluteUri($this->model('app.items_www') . $item->getFilePath() .'/'. $item->image);
			}
			?>
			<dt data-key="image">Image</dt>
			<dd data-key="image"><a href="<?php echo $image; ?>"><?php echo $image; ?></a></dd>

			<dt data-key="link">Link</dt>
			<dd data-key="link"><a href="<?php echo $link; ?>"><?php echo $this->html($link); ?></a></dd>

		</dl><?php
	}



	public function renderAsJson($item) {
		$link = $this->_router->makeAbsoluteUri("/id/item/{$item->idslug}");

		$obj = new StdClass();

		$obj->id = $link;
		$obj->name = $item->name;
		$obj->manufacturer = $item->manufacturer;
		$obj->model = $item->model;
		$obj->description = $this->_wikiparser->parse($item->full_description);

		$image = '';
		if (!empty($item->image)) {
			$image = $this->_router->makeAbsoluteUri($this->model('app.items_www') . $item->getFilePath() .'/'. $item->image);
		}

		$obj->contact1 = $item->contact_1_email;
		$obj->contact2 = $item->contact_2_email;

		$obj->image = $image;
		$obj->link = $link;

		echo json_encode($obj, JSON_FORCE_OBJECT);
	}



	public function renderAsRdf($item) {
		// @todo : renderAsRdf()
		die('METHOD NOT IMPLEMENTED - renderAsRdf()');
	}



	public function renderAsTurtle($item) {
		// @todo : renderAsTurtle()
		die('METHOD NOT IMPLEMENTED - renderAsTurtle()');
	}



	public function renderAsXml($item) {
		// @todo : renderAsXml()
		die('METHOD NOT IMPLEMENTED - renderAsXml()');
	}



}


