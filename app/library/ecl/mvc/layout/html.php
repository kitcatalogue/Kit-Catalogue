<?php



Ecl::load('Ecl_Mvc_Layout');



class Ecl_Mvc_Layout_Html_Exception extends Ecl_Mvc_Layout_Exception {}



/**
 * A class for handling HTML layouts.
 *
 * In addition to content sections, this class provides specific methods for adding common content to web pages,
 * and corresponding rendering functions to use in layout templates.
 *
 *
 * @package  Ecl
 * @version  1.0.0
 */
class Ecl_Mvc_Layout_Html extends Ecl_Mvc_Layout {

	// Public Properties

	// Private Properties
	protected $_title = null;

	protected $_breadcrumbs = array();
	protected $_feedback = array();
	protected $_javascripts = array();
	protected $_stylesheets = array();



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Add a link to the breadcrumbs trail.
	 *
	 * @param  string  $title
	 * @param  string  $href
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function addBreadcrumb($title, $href = null) {
        $title = $this->escape($title);
		$this->_breadcrumbs[] = array (
			'title'  => $title ,
			'href'   => $href ,
		);
		return true;
	}// /method



	/**
	 * Add a feedback box to the output.
	 *
	 * @param  string  $type  The feedback-type.
	 * @param  string  $title
	 * @param  string  $body  (optional)
	 * @param  array  $bullets  (optional)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function addFeedback($type, $title, $body = null, $bullets = null) {
		$this->_feedback[] = array (
			'type'     => $type ,
			'title'    => $title ,
			'body'     => $body ,
			'bullets'  => $bullets ,
		);
		return true;
	}// /method



	/**
	 * Add a javascript include.
	 *
	 * @param  string  $href
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function addJavascript($href) {
		$this->_javascripts[] = $href;
		return true;
	}// /method



	/**
	 * Add a stylesheet to the layout.
	 *
	 * @param  string  $href
	 * @param  string  $media
	 *
	 * @return  boolean  The operation was succesful.
	 */
	public function addStylesheet($href, $media = 'all') {
		$this->_stylesheets[] = array (
			'href'   => $href ,
			'media'  => $media ,
		);
		return true;
	}// /method



	public function clear() {
		parent::clear();
		$this->clearBreadcrumbs();
		$this->clearFeedback();
		$this->clearJavascripts();
		$this->clearStylesheets();
	}// /method



	public function clearBreadcrumbs($num = null) {
		if (is_null($num)) {
			$this->_breadcrumbs = array();
		} else {
			for($i=1; $i<=$num; $i++) {
				array_pop($this->_breadcrumbs);
			}
		}
	}// /method



	public function clearFeedback() {
		$this->_feedback = array();
	}// /method



	public function clearJavascripts() {
		$this->_javascripts = array();
	}// /method



	public function clearStylesheets() {
		$this->_stylesheets = array();
	}// /method



	/**
	 * Escape the given string for output.
	 *
	 * @param  string  $string
	 * @param  string  $charset  (default: 'UTF-8')
	 *
	 * @return  string
	 */
	public function escape($string, $charset = 'UTF-8') {
		return htmlspecialchars($string, ENT_COMPAT | ENT_IGNORE, $charset, false);
	}// /method



	public function hasBreadcrumbs() {
		return (!empty($this->_breadcrumbs));
	}// /method



	public function hasFeedback() {
		return (!empty($this->_feedback));
	}// /method



	public function hasJavascripts() {
		return (!empty($this->_javascripts));
	}// /method



	public function hasStylesheets() {
		return (!empty($this->_stylesheets));
	}// /method



	/**
	 * Render any defined breadcrumb links.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function renderBreadcrumbs() {
		if ($this->hasBreadcrumbs()) {
			echo('<ul>');
			foreach($this->_breadcrumbs as $i => $breadcrumb) {
				if (isset($breadcrumb['href'])) {
					printf('<li>&gt; <a href="%2$s">%1$s</a></li>', $breadcrumb['title'], $breadcrumb['href']);
				} else {
					printf('<li>&gt; %1$s</li>', $breadcrumb['title']);
				}
			}
			echo('</ul>');
		}
		return true;
	}// /method



	/**
	 * Render any defined feedback boxes.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function renderFeedback() {
		if ($this->hasFeedback()) {
			foreach($this->_feedback as $i => $feedback) {
				?>
				<div class="feedback <?php echo($feedback['type']); ?>">
					<?php
					if (isset($feedback['title'])) {
						?>
						<p class="title"><?php $this->out($feedback['title']); ?></p>
						<?php
					}

					if (isset($feedback['body'])) {
						?>
						<p><?php echo $feedback['body']; ?></p>
						<?php
					}

					if (isset($feedback['bullets'])) {
						?>
						<ul class="feedback_bullets">
						<?php
						foreach($feedback['bullets'] as $i => $item) {
							?>
							<li><?php $this->out($item); ?></li>
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
			echo('</ul>');
		}
		return true;
	}// /method



	/**
	 * Render the HTML elements for any defined javascript includes.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function renderJavascripts() {
		if ($this->hasJavascripts()) {
			foreach($this->_javascripts as $i => $href) {
				printf("\t".'<script type="text/javascript" src="%s"></script>%s', $href, "\n");
			}
		}
		return true;
	}// /method



	/**
	 * Render the HTML elements to include any defined stylesheets.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function renderStylesheets() {
		if ($this->hasStylesheets()) {
			foreach($this->_stylesheets as $i => $stylesheet) {
				printf("\t".'<link href="%s" media="%s" rel="stylesheet" type="text/css" />%s', $stylesheet['href'], $stylesheet['media'], "\n");
			}
		}
		return true;
	}// /method



	public function setTitle($title) {
		$this->_title = $title;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>
