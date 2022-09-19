<?php

/**
 * Subclass the Controller class within our application.
 *
 * @author Himanshu Rao
 */

namespace Shared {
	use Shared\Services\Db;
	use Framework\{Registry, Router, Events, RequestMethods};

	class Controller extends \Framework\Controller {

		/**
		 * @readwrite
		 */
		protected $_account;


		public function setAccount($account) {
			$session = Registry::get("session");
			if ($account) {
				$session->set("account", $account->id);
			} else {
				$session->erase("account");
			}
			$this->_account = $account;
			return $this;
		}
		
		public function seo($params = array()) {
			$seo = Registry::get("seo");
			foreach ($params as $key => $value) {
				$property = "set" . ucfirst($key);
				$seo->$property($value);
			}
			$this->layoutView->set("seo", $seo);
		}

		/**
		 * @protected
		 */
		public function _secure() {
			$account = $this->getaccount();		
			if (!$account) {
				Registry::get("session")->set('$beforeLogin', RequestMethods::server('REQUEST_URI', '/'));
				$this->redirect("/login");
			} else {
				if ($account->type == "admin") {
					$this->setLayout("layouts/admin");
				} else {
					$this->_404();
				}
			}
		}

		/**
		 * @protected
		 */
		public function _session() {
			$account = $this->getaccount();
			if ($account) {
				$this->redirect('/admin/index');
			}
		}

		public function redirect($url) {
			$this->noview();
			header("Location: {$url}");
			exit();
		}

		public function _404($msg = "Invalid Request") {
			$this->noview();
			throw new \Framework\Router\Exception\Controller($msg);
		}
		
		public function logout() {
			$this->setAccount(false);
			session_destroy();
			self::redirect("/index.html");
		}
		
		public function noview() {
			$this->willRenderLayoutView = false;
			$this->willRenderActionView = false;
		}

		public function JSONview() {
			$this->willRenderLayoutView = false;
			$this->defaultExtension = "json";
		}

		public function getSession() {
			return Registry::get("session");
		}

		public function __construct($options = array()) {
			parent::__construct($options);

			Db::connect();
			// schedule: load account from session
			Events::add("framework.router.beforehooks.before", function($name, $parameters) {
				$session = Registry::get("session");
				$controller = Registry::get("controller");
				$account = $session->get("account");
				if ($account) {
					$controller->account = \Models\Account::first(array("id = ?" => $account));
				}
			});

			// schedule: save account to session
			Events::add("framework.router.afterhooks.after", function($name, $parameters) {
				$session = Registry::get("session");
				$controller = Registry::get("controller");
				if ($controller->account) {
					$session->set("account", $controller->account->id);
				}

				// Set Flash Message to the Action View
				$flashMessage =  $session->get('$flashMessage', null);
				if ($flashMessage) {
					$session->erase('$flashMessage');
					$controller->actionView->set('message', $flashMessage);
				}
			});
		}

	}

}
