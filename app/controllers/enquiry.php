<?php
/*
 *
 */
class Controller_Enquiry extends Ecl_Mvc_Controller {



	public function beforeAction() {
		if ( (false == $this->model('app.allow_anonymous')) && ($this->model('user')->isAnonymous()) ) {
			$this->abort();
			$this->router()->action('index', 'signin');
			return;
		}

		if (!$this->model('enquiry.enabled')) {
			$this->abort();
			$this->router()->action('404', 'error');
			return;
		}
	}// /method



	public function actionIndex() {
		$user = $this->model('user');

		$item = $this->model('itemstore')->find($this->param('itemid'));
		$backlink = base64_decode($this->request()->get('backlink'));

		if (empty($item)) {
			$this->router()->action('error', '404');
			return true;
		}

		if ( (KC__VISIBILITY_PUBLIC != $item->visibility) && ($this->model('user')->isAnonymous()) ) {
			$this->router()->action('404', 'error');
			return true;
		}


		$form = array(
			'name'   => (!$user->isAnonymous()) ? $user->name : '' ,
			'email'  => (!$user->isAnonymous()) ? $user->email : '' ,
			'phone'  => '' ,

			'org'       => '' ,
			'role'      => '' ,
			'deadline'  => '' ,

			'type'  => 'general' ,
			'body'  => '' ,
		);


		if ($this->request()->isPost()) {

			if ($this->request()->post('submitcancel')) {
				$this->response()->setRedirect($this->router()->makeAbsoluteUri($backlink));
				return;
			}

			$errors = array();

			$form['name'] = $this->request()->post('name');
			$form['email'] = $this->request()->post('email');
			$form['phone'] = $this->request()->post('phone');

			$form['org'] = $this->request()->post('org');
			$form['role'] = $this->request()->post('role');
			$form['deadline'] = $this->request()->post('deadline');

			$form['type'] = $this->request()->post('type');
			$form['body'] = $this->request()->post('body');

			if (empty($form['name'])) { $errors[] = 'You must enter your name.'; }
			if (empty($form['email'])) { $errors[] = 'You must enter your email address.'; }

			if (empty($form['type'])) { $errors[] = 'You must select the type of enquiry you\'re making.'; }
			if (empty($form['body'])) { $errors[] = 'You must enter some text for your enquiry.'; }


			$mail = Ecl::factory('Ecl_Mail');

			$to_emails = array();
			if (!Ecl::isEmpty($this->model('enquiry.send_to'))) {
				$to_emails[] = $this->model('enquiry.send_to');
			} else {
				if (!empty($item->contact_1_email)) { $to_emails[] = $item->contact_1_email; }
				if (!empty($item->contact_2_email)) { $to_emails[] = $item->contact_2_email; }
			}

			if (empty($to_emails)) {
				$errors[] = 'No contact information exists for this item.';
			} else {
				$to = implode(', ', $to_emails);
				$subject = "Equipment Enquiry : {$item->name}";

				/*
				 * Outlook ignores new-lines where the line is >40 characters long and doesn't
				 * end with either punctuation or a tab.  Hence, there are lots of full-stops
				 * and where necessary, tabs, in the following email...
				 */

				$body = '';
				$body .= "Sent by : {$form['name']}.\r\n";
				$body .= "Email : {$form['email']}\t\r\n";
				$body .= "Phone : {$form['phone']}.\r\n\r\n";

				if (!empty($form['org'])) { $body .= "Organisation : {$form['org']}.\r\n"; }
				if (!empty($form['role'])) { $body .= "Role : {$form['role']}.\r\n"; }
				if (!empty($form['deadline'])) { $body .= "Deadline : {$form['deadline']}.\r\n"; }

				$body .= 'Time : '. date('d-m-Y H:i:s') .".\r\n\r\n";

				$body .= "Enquiring about : {$item->name}.\r\n";
				$body .= "Make : {$item->manufacturer}.\r\n";
				$body .= "Model : {$item->model}.\r\n";
				$body .= 'Item Link : '. $this->router()->makeAbsoluteUri("/id/item/{$item->id}")."\r\n\r\n";

				$body .= "Enquiry type: {$form['type']}.\r\n\r\n";
				$body .= "Enquiry: \r\n{$form['body']}\r\n";

				$headers = '';
				$headers .= "From: {$form['email']}\r\n";
				$headers .= "Cc: {$form['email']}\r\n";
				$headers .= "Reply-To: {$form['email']}\r\n";

				if (!Ecl::isEmpty($this->model('enquiry.bcc'))) {
					$headers .= 'Bcc: '. $this->model('enquiry.bcc') ."\r\n";
				}
			}

			if (!empty($errors)) {
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found with your enquiry.', '', $errors);
			} else {
				//mail($to, $subject, $body, $headers);

				$info = array (
					'to'      => $to ,
					'subject' => $subject ,
					'body'    => $body ,
					'headers' => $headers ,
				);

				$payload = base64_encode(serialize($info));

				$req = Ecl_Http::newHttpRequest();
				$req->setUrl('http://kit-catalogue.lboro.ac.uk/northumbria-emailer/index.php?q=ABDF4E1A');
				$req->setMethod('POST');
				$req->addFormField('payload', $payload);

				$resp = Ecl_Http::sendRequest($req);

				$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'Your enquiry has been sent.', '<p>You should shortly receive a confirmation of your enquiry via email.</p><p>You can now <a href="'. $this->router()->makeAbsoluteUri($backlink).'">return to the catalogue</a>.</p>');
			}

		}


		$this->view()->backlink = $backlink;
		$this->view()->item = $item;
		$this->view()->form = $form;

		$this->view()->render('enquiry_index');
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class



?>