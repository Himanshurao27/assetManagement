<?php

/**
 * @author Himanshu Rao
 */

use Framework\{Registry, TimeZone, ArrayMethods};
use Shared\Services\Db;

class Asset extends Shared\Controller {

	/**
	 * @before _secure
	 */
	public function add(){
		$view = $this->getActionView();
        $venders = \Models\Vender::cacheAllv2(['user_id' => $this->account->_id], [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
		try {
			if ($this->request->isPost()) {
				$data = $this->request->post('data', []);
				$data = array_merge($data, ['user_id' => $this->account->_id]);
				$asset = new Models\Asset($data);
				$asset->save();
				\Shared\Utils::flashMsg(['type' => 'success', 'text' => 'Asset Added created successfully']);
				$this->redirect('/asset/manage');
				
			}
		} catch (\Exception $e) {
			\Shared\Utils::flashMsg(['type' => 'error', 'text' => $e->getMessage()]);
		}
		$view->set([
			'venders' => $venders ?? []
		]);
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

		$assets = \Models\Asset::cacheAllv2($query, [], ['maxTimeMS' => 5000, 'page' => $page, 'limit' => $limit, 'direction' => 'desc', 'order' => ['created' => -1]]);
		$venders = \Models\Vender::cacheAllv2(['user_id' => $this->account->_id], [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);

		$view->set([
			'assets' => $assets ?? [],
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
			$this->redirect('/asset/manage');
		}

		$asset = \Models\Asset::findById($id);
		if (!$asset) {
			return $view->set('message', ['type' => 'error', 'text' => 'No Chargeback found!']);
		}
		$msg = "";
		try {
			$asset->delete();
			var_dump($asset->delete());
			die();
			$msg = ['type' => 'success', 'text' => 'Asset deleted successfully!'];
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
		$asset = Models\asset::findById($id);
		$venders = Models\vender::cacheAllv2(['user_id' => $this->account->_id], [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
		if (!$asset) {
			return $view->set('message', ['type' => 'error', 'text' => 'No Asset found!']);
		}
		try {
			if ($this->request->isPost()) {
				$data = $this->request->post('data', []);
				foreach(['name', 'asset_type', 'status', 'ven_id', 'description', 'pur_date'] as $value) {
					if (isset($data[$value])) {
						$asset->$value = $data[$value];
					}
				}
				$asset->save();
				$view->set('message', ['type' => 'success', 'text' => 'Asset Edited successfully']);
				
			}
		} catch (\Exception $e) {
			$view->set('message', ['type' => 'error', 'text' => $e->getMessage()]);
		}
        $view->set([
            'asset' => $asset ?? [],
			'venders' => $venders ?? []
		]);
	}
}