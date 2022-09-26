<?php

use Shared\Services\Db;
use Framework\{Registry, TimeZone, ArrayMethods};

class Assigned extends Shared\Controller {

	/**
	 * @before _secure
	 */
	public function add(){
        $view = $this->getActionView();
        $query = ['user_id' => $this->account->_id];
        $employees = \Models\Employee::cacheAllv2($query, [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
        $assets = \Models\Asset::cacheAllv2($query, [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
		try {
			if ($this->request->isPost()) {
				$data = $this->request->post('data', []);
				if (!$data['handover_date']) {
					unset($data['handover_date']);
				}
				$data = array_merge($data, ['user_id' => $this->account->_id]);
				$asset = new \Models\Assigned($data);
				$asset->save();
				\Shared\Utils::flashMsg(['type' => 'success', 'text' => 'Assigned Added created successfully']);
				$this->redirect('/assigned/manage');
				
			}
		} catch (\Exception $e) {
			\Shared\Utils::flashMsg(['type' => 'error', 'text' => $e->getMessage()]);
		}
        $view->set([
			'assets' => $assets ?? [],
			'employees' => $employees ?? []
		]);
	}

	/**
	 * @before _secure
	 */
	public function manage() {
		$view = $this->getActionView();

		$limit = $this->request->get("limit", 10, ["type" => "numeric", "maxVal" => 500]);
		$page = $this->request->get("page", 1, ["type" => "numeric", "maxVal" => 100]);

		$query = ['user_id' => $this->account->_id];
		$uiQuery = $this->request->get("query", []);
		if ($uiQuery) {
			foreach (['asset_id', 'emp_id'] as $key) {
				if (isset($uiQuery[$key]) && $uiQuery[$key]) {
					$query[$key] = $uiQuery[$key];
				}
			}
		}

        $assigneds = \Models\Assigned::selectAll($query, [], ['maxTimeMS' => 5000, 'page' => $page, 'limit' => $limit, 'direction' => 'desc', 'order' => ['created' => -1]]);
        $empIds = ArrayMethods::arrayKeys($assigneds, 'emp_id');
        if ($empIds) {
            $employees = \Models\Employee::cacheAllv2(['user_id' => $this->account->_id, '_id' => ['$in' => $empIds]], ['_id', 'name'], ['maxTimeMS' => 5000]);
        }
        $assetIds = ArrayMethods::arrayKeys($assigneds, 'asset_id');
        if ($assetIds) {
            $assets = \Models\Asset::selectAll(['user_id' => $this->account->_id, '_id' => ['$in' => $assetIds]], ['_id', 'name', 'asset_type'], ['maxTimeMS' => 5000]);
        }
        $total = $count = \Models\Assigned::count($query);

		$view->set([
			'assigneds' => $assigneds ?? [],
            'assets' => $assets ?? [],
			'employees' => $employees ?? [],
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
			$this->redirect('/assigned/manage');
		}

		$asset = \Models\Assigned::findById($id);
		if (!$asset) {
			return $view->set('message', ['type' => 'error', 'text' => 'No Assigned found!']);
		}
		$msg = "";
		try {
			$asset->delete();
			$msg = ['type' => 'success', 'text' => 'Assigned deleted successfully!'];
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
        $query = ['user_id' => $this->account->_id];
        $employees = \Models\Employee::cacheAllv2($query, [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
        $assets = \Models\Asset::cacheAllv2($query, [], ['maxTimeMS' => 5000, 'limit' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
		$assigned = \Models\Assigned::findById($id);
		if (!$assigned) {
			return $view->set('message', ['type' => 'error', 'text' => 'No Assigned found!']);
		}
		try {
			if ($this->request->isPost()) {
				$data = $this->request->post('data', []);
				foreach(['asset_id', 'emp_id', 'assign_date', 'handover_date'] as $value) {
					if (isset($data[$value])) {
						$assigned->$value = $data[$value];
					}
				}
				$assigned->save();
				$view->set('message', ['type' => 'success', 'text' => 'Assigned Edited successfully']);
				
			}
		} catch (\Exception $e) {
			$view->set('message', ['type' => 'error', 'text' => $e->getMessage()]);
		}
        $view->set([
            'assigned' => $assigned ?? [],
			'assets' => $assets ?? [],
			'employees' => $employees ?? []
		]);
	}

}