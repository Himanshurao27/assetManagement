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
				\Shared\Utils::flashMsg(['type' => 'success', 'text' => 'Vender Added successfully']);
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

		$query = ['user_id' => $this->account->_id];
		$searchKeyType = strtolower($this->request->get('type'));
		$searchValue = $this->request->get('search');
		switch ($searchKeyType) {
			case 'name':
				$query = array_merge($query, ['name' => Db::convertType($searchValue, 'regex')]);
				break;

			case 'comapnyName':
				$query = array_merge($query, ['company_name' => Db::convertType($searchValue, 'regex')]);
				break;

			case 'emp_id':
				$query = array_merge($query, ['emp_id' => $searchValue]);
				break;

			case 'phone':
				$query = array_merge($query, ['phone' => Db::convertType($searchValue, 'regex')]);
				break;
		
			case 'email':
				$query = array_merge($query, ['email' => Db::convertType($searchValue, 'regex')]);
				break;

			case 'address':
				$query = array_merge($query, ['address' => Db::convertType($searchValue, 'regex')]);
				break;
			
			case 'state':
				$query = array_merge($query, ['state' => Db::convertType($searchValue, 'regex')]);
				break;

			case 'country':
				$query = array_merge($query, ['country' => Db::convertType($searchValue, 'regex')]);
				break;
		}

        $venders = \Models\vender::selectAll($query, [], ['maxTimeMS' => 5000, 'direction' => 'desc', 'order' => ['created' => -1]]);
		$count = \Models\vender::count($query);

		$view->set([
			'venders' => $venders ?? [],
			'search' => $this->request->get('search', ''),
			'type' => $this->request->get('type', '')
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
			$msg = 'Vender deleted successfully!';
		} catch (\Exception $e) {
			$msg = ['type' => 'error', 'text' => 'Something went wrong. Please Try Again'];
		}
		$view->set('message', $msg);
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