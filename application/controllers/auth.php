<?php

/**
 * @author Himanshu Rao
 */

use Framework\{Registry, Security};
use Shared\Services\Db;
use Cloudstuff\ApiUtil\Mailing\{Config, Factory, Params};

class Auth extends Shared\Controller {

	/**
	 * @before _session
	 * @after _csrfToken
	 */
	public function login() {
		$token = $this->request->post("token", '');
		$session = Registry::get("session");
		$view = $this->getActionView();

		$appConf = Framework\Utils::getConfig("app");
		Castle::setApiKey($appConf->app->castle_api_key);
		
		if ($this->request->get('error')) {
			$view->set("message", [ "type" => "error", "text" => "No account found with that email address." ]);
		}

		if ($this->request->isPost()) {
			if (!$this->verifyToken($token)) {
				return $view->set('message', 'Invalid token');
			}
			$email = $this->request->post('email');
			$pass = sha1($this->request->post('password'));
			$account = \Models\Account::first(['email' => $email]);
			if ($account && $account->password != $pass) {
				return $view->set('message', [ "type" => "warning", "text" => "'Wrong Password'" ]);
			}
			if ($account && $account->password == $pass) {
				$this->setAccount($account);
				$beforeLogin = $session->get('$beforeLogin');
				if ($beforeLogin) {
					$session->erase('$beforeLogin');
					$beforeLogin = str_replace('&amp;', '&', $beforeLogin);	// fix the URL
					$this->redirect($beforeLogin);
				}
				$this->redirect('/admin/index');
			} else {
				$view->set('message', [ "type" => "warning", "text" => "email and password do not match" ]);
			}
		}
	}

	/**
	 * @protected
	 */
	public function _csrfToken() {
		$session = $this->getSession();
		$csrf_token = Framework\StringMethods::uniqRandString(44);
		$session->set('Auth\Request:$token', $csrf_token);

		if ($this->actionView) {
			$this->actionView->set('__token', $csrf_token);
		}
	}

	public function verifyToken($token = null) {
		$session = $this->getSession();
		$csrf = $session->get('Auth\Request:$token');

		if ($csrf && $csrf === $token) {
			return true;
		}
		return false;
	}

	public function logout() {
		$this->setAccount(false);
		$this->redirect('/auth/login');
	}

	/**
	 * @before _session
	 * @after _csrfToken
	 */
	public function register() {
		$token = $this->request->post("token", '');
		$session = Registry::get("session");
		$view = $this->getActionView();
		try {
			if ($this->request->isPost()) {
				if (!$this->verifyToken($token)) {
					return $view->set('message', ['type' => 'error', 'text' => 'Invalid token']);
				}
				$data = $this->request->post('data', []);
				$data['password'] = sha1($data['password']);
				$account = new \Models\Account($data);
				$account->save();
				\Shared\Utils::flashMsg(['type' => 'success', 'text' => 'Account created successfully']);
			}
		} catch (\Exception $e) {
			\Shared\Utils::flashMsg(['type' => 'error', 'text' => $e->getMessage()]);
		}
	}

}

