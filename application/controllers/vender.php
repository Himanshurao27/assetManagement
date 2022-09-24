<?php

/**
 * @author Himanshu Rao
 */


use Framework\{Registry, TimeZone, ArrayMethods};
use Shared\Services\Db;

class Vender extends Shared\Controller {

	/**
	 * @before _secure
	 */
	public function add(){
		try {
			if ($this->request->isPost()) {
				$data = $this->request->post('data', []);
				$data = array_merge($data, ['user_id' => $this->account->_id]);
				$asset = new \Models\vender($data);
				$asset->save();
				\Shared\Utils::flashMsg(['type' => 'success', 'text' => 'Vender Added created successfully']);
				$this->redirect('/vender/manage');
			}
		} catch (\Exception $e) {
			\Shared\Utils::flashMsg(['type' => 'error', 'text' => $e->getMessage()]);
		}
	}

	/**
	 * @before _secure
	 */
	public function manage() {
		$view = $this->getActionView();

		$limit = $this->request->get("limit", 10, ["type" => "numeric", "maxVal" => 500]);
		$page = $this->request->get("page", 1, ["type" => "numeric", "maxVal" => 100]);

		$uiQuery = $this->request->get("query", []);
		$query = ['user_id' => $this->account->_id];

        $venders = \Models\vender::cacheAllv2($query, [], ['maxTimeMS' => 5000, 'page' => $page, 'limit' => $limit, 'direction' => 'desc', 'order' => ['created' => -1]]);
		$count = \Models\vender::count($query);

		$view->set([
			'venders' => $venders ?? [],
			'limit' => $limit, 'page' => $page,
			'query' => $uiQuery
		]);
	}

	/**
	 * @before _secure
	 */
	public function delete($id = null) {
		$view = $this->getActionView();
		if (!$id || !$this->request->isDelete()) {
			\Shared\Utils::flashMsg(['type' => 'error', 'text' => 'Invalid Request']);
			$this->redirect('/vendor/manage');
		}

		$vender = \Models\vender::findById($id);
		if (!$vender) {
			return $view->set('message', ['type' => 'error', 'text' => 'No Vender found!']);
		}
		$msg = "";
		try {
			$vender->delete();
			$msg = ['type' => 'success', 'text' => 'Vender deleted successfully!'];
		} catch (\Exception $e) {
			$msg = ['type' => 'error', 'text' => 'Something went wrong. Please Try Again'];
		}
	}

    	/**
	 * @before _secure
	 */
	public function edit($id = null) {
		$view = $this->getActionView();
		if (!$id) {
			$this->_404();
		}
		$vender = \Models\vender::findById($id);
		if (!$vender) {
			return $view->set('message', ['type' => 'error', 'text' => 'No Vender found!']);
		}
		try {
			if ($this->request->isPost()) {
				$data = $this->request->post('data', []);
				foreach(['name', 'email', 'address', 'state', 'country', 'company_name', 'phone'] as $value) {
					if (isset($data[$value])) {
						$vender->$value = $data[$value];
					}
				}
				$vender->save();
				$view->set('message', ['type' => 'success', 'text' => 'Vender Edited successfully']);
				
			}
		} catch (\Exception $e) {
			$view->set('message', ['type' => 'error', 'text' => $e->getMessage()]);
		}
		$view->set('vender', $vender);
	}
}