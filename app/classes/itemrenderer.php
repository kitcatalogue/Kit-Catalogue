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



	public function csv($string) {
		return str_replace('"', '""', $string);
	}



	public function getItemImage($item) {
		return (!empty($item->image)) ? $this->_router->makeAbsoluteUri("/id/item/{$item->imageslug}") : '' ;
	}



	public function getItemLink($item) {
		return $this->_router->makeAbsoluteUri("/id/item/{$item->idslug}");
	}



	public function getItemUri($item) {
		return $this->_router->makeAbsoluteUri("/id/item/{$item->id}");
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



	public function renderAsCsv($item) {
		$fields = array(
			'id' => $this->csv($this->getItemUri($item)) ,
			'name' => $this->csv($item->name) ,
			'manufacturer' => $this->csv($item->manufacturer) ,
			'model' => $this->csv($item->model) ,
			'description' => $this->csv($item->full_description) ,
			'contact1' => $this->csv($item->contact_1_email) ,
			'contact2' => $this->csv($item->contact_2_email) ,
			'image' => $this->csv($this->getItemImage($item)) ,
			'link' => $this->csv($this->getItemLink($item)) ,
		);
		echo '"' . implode('","', $fields) .'"';
	}



	public function renderAsHtml($item) {
		?>
		<dl>

			<dt data-key="id">ID</dt>
			<dd data-key="id"><?php echo $this->html($this->getItemUri($item)); ?></dd>

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

			<dt data-key="image">Image</dt>
			<dd data-key="image"><a href="<?php echo $this->getItemImage($item) ?>"><?php echo $this->html($this->getItemImage($item)) ?></a></dd>

			<dt data-key="link">Link</dt>
			<dd data-key="link"><a href="<?php echo $this->getItemLink($item) ?>"><?php echo $this->html($this->getItemLink($item)); ?></a></dd>

		</dl><?php
	}



	public function renderAsJson($item) {
		$obj = new StdClass();

		$obj->id = $this->getItemUri($item);
		$obj->name = $item->name;
		$obj->manufacturer = $item->manufacturer;
		$obj->model = $item->model;
		$obj->description = $this->_wikiparser->parse($item->full_description);
		$obj->contact1 = $item->contact_1_email;
		$obj->contact2 = $item->contact_2_email;
		$obj->image = $this->getItemImage($item);
		$obj->link = $this->getItemLink($item);

		echo json_encode($obj, JSON_FORCE_OBJECT);
	}



	public function renderAsRdf($item) {
		// @todo : renderAsRdf()
		die('METHOD NOT IMPLEMENTED - renderAsRdf()');
	}



	public function renderAsRSS($item) {
		// @todo : renderAsRSS()
		die('METHOD NOT IMPLEMENTED - renderAsRSS()');
	}



	public function renderAsTurtle($item) {
		// @todo : renderAsTurtle()
		die('METHOD NOT IMPLEMENTED - renderAsTurtle()');
	}



	public function renderAsXml($item) {
		?>
		<item id="<?php echo $this->xml($this->getItemUri($item)); ?>">
			<name><?php echo $this->xml($item->name); ?></name>
			<?php
			$this->outIfNotEmpty($item->manufacturer, sprintf('<manufacturer>%1$s</manufacturer>'."\n", $this->xml($item->manufacturer)));
			$this->outIfNotEmpty($item->manufacturer, sprintf('<model>%1$s</model>'."\n", $this->xml($item->model)));
			$this->outIfNotEmpty($item->full_description, sprintf('<description><![CDATA[%1$s]]></description>'."\n", $this->_wikiparser->parse($item->full_description)));

			if (!empty($item->contact_1_email)) {
				?>
				<contact1>
					<email><?php echo $this->xml($item->contact_1_email); ?></email>
				</contact1>
				<?php
			}
			if (!empty($item->contact_2_email)) {
				?>
				<contact2>
					<email><?php echo $this->xml($item->contact_2_email); ?></email>
				</contact2>
				<?php
			}
			$this->outIfNotEmpty($this->getItemImage($item), sprintf('<image>%1$s</image>'."\n", $this->xml($this->getItemImage($item))));
			printf('<link>%1$s</link>'."\n", $this->html($this->getItemLink($item)));
			?>
		</item>
		<?php
	}



	public function xml($string) {
		return str_replace(array('&', '<', '>', '"', "'"), array('&#x26;', '&#x3C;', '&#x3E;', '&#x22;', '&#x27;'), $string);
	}



}


