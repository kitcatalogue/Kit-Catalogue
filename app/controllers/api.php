<?php
class Controller_Api extends Ecl_Mvc_Controller {


	protected $_valid_formats = array('html', 'json');



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


		if ('actionIndex' != $this->_action) {
			$public_mode = $this->param('public_mode');
			if ($public_mode) {
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
			} else {
				if (true !== $this->model('api.private.enabled')) {
					$this->abort();
					?>
					<pre>
					Kit-Catalogue Private API disabled.

					The setting  <em>$config['api.private.enabled'] = false;</em>  in  <em>local/local_config.php</em>
					To enable the API, change the configuration to  <em>$config['api.private.enabled'] = true;</em>

					For more about the API, see  <em>docs/api.txt</em>
					</pre>
					<?php
					return;
				}
			}
		}

		$this->layout()->addStylesheet($this->router()->makeAbsoluteUri('/css/api.css'));
	}// /method



	public function actionIndex() {
		$this->view()->render("api_index");
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class


