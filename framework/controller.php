<?php

/**
 * Loading libraries and views or integrating with business logic in the model.
 *
 * @author Faizan Ayubi
 */

namespace Framework {

	use Framework\Base as Base;
	use Framework\View as View;
	use Framework\Events as Events;
	use Framework\Registry as Registry;
	use Framework\Template as Template;
	use Framework\Controller\Exception as Exception;

	class Controller extends Base {

		/**
		 * Every project needs a basic logged in User so this has been moved from
		 * Shared\Controller to Framework\Controller
		 * @readwrite
		 */
		protected $_user;

		/**
		 * @read
		 */
		protected $_name;

		/**
		 * @readwrite
		 */
		protected $_parameters;

		/**
		 * @readwrite
		 */
		protected $_layoutView;

		/**
		 * @readwrite
		 */
		protected $_actionView;

		/**
		 * @readwrite
		 */
		protected $_willRenderLayoutView = true;

		/**
		 * @readwrite
		 */
		protected $_willRenderActionView = true;

		/**
		 * @readwrite
		 */
		protected $_defaultPath = "application/views";

		/**
		 * @readwrite
		 */
		protected $_defaultLayout = "layouts/standard";

		/**
		 * @readwrite
		 */
		protected $_defaultExtension = "html";

		/**
		 * @readwrite
		 */
		protected $_defaultContentType = "text/html";

		/**
		 * @readwrite
		 * @var Container\Request Store the current request state
		 */
		protected $_request = null;

		/**
		 * @readwrite
		 */
		protected $_byPassJsonHeader = false;

		/**
		 * It defines the location of the layout template, which is passed to the new View instance, which is then passed into the setLayoutView() setter method.
		 * It gets the controller/action names from the router. It gets the router instance from the registry, and uses getters for the names.
		 * It then builds a path from the controller/action names, to a template it can render.
		 * @param type $options
		 */
		public function __construct($options = array()) {
			parent::__construct($options);
			Events::fire("framework.controller.construct.before", array($this->name));

			$router = Registry::get("router");

			Registry::get("cache")->connect();

			switch ($router->getExtension()) {
				case "json":
					$this->defaultContentType = "application/json";
					$this->defaultExtension = $router->getExtension();
					break;

				case "csv":
					$this->defaultContentType = "text/csv";
					$this->defaultExtension = $router->getExtension();
					break;
			}

			$this->setLayout();

			if (!$this->request) {
				$this->request = new Container\Request();
			}

			Events::fire("framework.controller.construct.after", array($this->name));
		}

		/**
		 * To make Registry::getCurrentUserId work we have added this dummy function
		 * @return boolean
		 */
		public function isSuperUser() {
			return false;
		}

		protected function setView($viewFile = null) {
			$router = Registry::get("router");
			$controller = strtolower($this->name);
			if ($viewFile) {
				$action = $viewFile;
			} else {
				$action = $router->action;
			}

			$view = new View(array(
				"file" => APP_PATH . "/{$this->defaultPath}/{$controller}/{$action}.{$this->defaultExtension}"
			));
			$data = $this->actionView->getData();
			$view->data = $data;
			$this->actionView = $view;
		}

		protected function setLayout($layout = "layouts/standard") {
			$this->defaultLayout = $layout;
			$defaultPath = $this->defaultPath;
			$defaultExtension = $this->defaultExtension;
			if ($this->willRenderLayoutView) {
				$defaultLayout = $this->defaultLayout;

				$view = new View(array(
					"file" => APP_PATH . "/{$defaultPath}/{$defaultLayout}.{$defaultExtension}"
				));

				$this->layoutView = $view;
			}

			if ($this->willRenderActionView) {
				$router = Registry::get("router");
				$controller = $router->controller;
				$action = $router->action;

				$view = new View(array(
					"file" => APP_PATH . "/{$defaultPath}/{$controller}/{$action}.{$defaultExtension}"
				));

				$this->actionView = $view;
			}
		}

		protected function getName() {
			if (empty($this->_name)) {
				$this->_name = get_class($this);
			}
			return $this->_name;
		}

		protected function _getExceptionForImplementation($method) {
			return new Exception\Implementation("{$method} method not implemented");
		}

		/**
		 * @protected
		 */
		public function setLoggerConfig() {
			$logger = Registry::getLogger();
			if (! $logger) {
				return false;
			}
			$logger->pushProcessor(function ($record) {
			    $record['context']['requestId'] = $this->request->id;
			    if ($this->user) {
			        $record['context']['user'] = $this->user->jsonSerialize();
			    }
			    return $record;
			});
		}

		protected function getBreadcrumbs() {
			$key = "BREADCRUMBS";
			$json = Utils::getCache($key);
			if (is_null($json)) {
				$filePath = APP_PATH . "/breadcrumbs.json";
				$json = json_decode(file_get_contents($filePath), true);
				Utils::setCache($key, $json, 60);
			}
			return $json;
		}

		protected function setBreadcrumbs($seo) {
			$breadcrumbs = $this->getBreadcrumbs();
			$router = Registry::get('router');
			$controller = Registry::get('controller');
			if (!$this->layoutView) {
				return;
			}
			$fallbackBrc = [['title' => $seo ? $seo->getTitle() : 'Dashboard', 'active' => true]];
			$this->layoutView->set("breadcrumbs", $fallbackBrc);
			if ($router && $controller) {
				$key = sprintf('%s.%s', strtolower(get_class($controller)), $router->action);
				$bdc = $breadcrumbs['pages'][$key]['breadcrumbs'] ?? $fallbackBrc;
				$navLinks = $breadcrumbs['pages'][$key]['nav_links'] ?? [];
				$this->layoutView->set("breadcrumbs", $bdc)->set('_navLinks', $navLinks);
			}
			if ($this->actionView) {
				$this->layoutView->set('brcMacroMap', array_merge([
					'{title}' => $seo ? $seo->getTitle() : 'Dashboard'
				], $this->actionView->get('_brcMacroMap', [])));
			}
		}

		public function seo($params = []) {
			$seo = Registry::get("seo");
			foreach ($params as $key => $value) {
				$property = "set" . ucfirst($key);
				$seo->$property($value);
			}
			$this->layoutView->set("seo", $seo);
		}

		public function _503($msg = "Invalid Request") {
			$this->noview();
			throw new \Framework\Router\Exception\Maintenance($msg);
		}

		public function _404($msg = "Invalid Request") {
			$this->noview();
			throw new \Framework\Router\Exception\Controller($msg);
		}

		public function _401($msg = "Invalid Request") {
			$this->noview();
			throw new \Framework\Router\Exception\Inactive($msg);
		}

		protected function renderJSONFields($data) {
			$obj = array();
			foreach ($data as $key => $value) {
				switch (gettype($value)) {
					case 'object':
						if (get_class($value) === "stdClass") {
							$obj[$key] = $value;
						} else if (is_a($value, 'Framework\Model')) {
							$obj[$key] = $value->toArray(['id']);
						} else {
							$obj[$key] = $value;
						}
						break;

					case 'array':
						$obj[$key] = $this->renderJSONFields($value);
						break;

					case 'string':
					case 'integer':
					case 'boolean':
					case 'float':
					default:
						$obj[$key] = $value;
						break;

				}
			}
			return $obj;
		}

		public function render() {
			Events::fire("framework.controller.render.before", array($this->name));

			$defaultContentType = $this->defaultContentType;
			$results = null;

			$doAction = $this->willRenderActionView && $this->actionView;
			$doLayout = $this->willRenderLayoutView && $this->layoutView;

			$this->setBreadcrumbs($doLayout ? $this->layoutView->get('seo') : null);

			if ($doAction) {
				$view = $this->actionView;
				$data = $view->data;

				$api = $this->request->header('x-json-api', false);
				if ($this->defaultExtension == "json" && (strtolower($api) == 'swiftmvc' || $this->byPassJsonHeader)) {
					if ($data) {
						$obj = $this->renderJSONFields($data);
					} else {
						$obj = array();
					}
					echo json_encode($obj);
				} else if ($this->defaultExtension == "json") {
					$parsed = parse_url(URL);
					$path = explode(".", $parsed['path'] ?? '/')[0];
					header("Location: $path");
					exit();
				} else if ($this->defaultExtension == "csv") {
					// parse the data
					$csv = new Writer\Csv($data);
					$csv->write();
				}
				$results = $view->render();
				$this->actionView->template->implementation->set("action", $results);
			}

			if ($doLayout) {
				$view = $this->layoutView;
				$results = $view->render();

				header("Content-type: {$defaultContentType}");
				echo $results;
			} else if ($doAction) {
				header("Content-type: {$defaultContentType}");
				echo $results;
			}

			$this->willRenderLayoutView = false;
			$this->willRenderActionView = false;

			Events::fire("framework.controller.render.after", array($this->name));
		}

		public function __destruct() {
			Events::fire("framework.controller.destruct.before", array($this->name));

			// $this->render();

			Events::fire("framework.controller.destruct.after", array($this->name));
		}

	}

}
