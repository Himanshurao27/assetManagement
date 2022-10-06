<?php

/**
 * @author Himanshu Rao
 */

use Shared\Controller as Controller;
use Framework\{TimeZone, ArrayMethods};
use Framework\Registry as Registry;

class Admin extends Controller {

	/**
	 * @before _secure
	 */
	public function index() {
		$view = $this->getActionView();
		$assets = \Models\Asset::selectAll(['user_id' => $this->account->_id], ['_id', 'type', 'status'], ['maxTimeMS' => 5000]);
		$vendors = \Models\vendor::selectAll(['user_id' => $this->account->_id], [], ['maxTimeMS' => 5000]);
		$employees = \Models\Employee::selectAll(['user_id' => $this->account->_id], [], ['maxTimeMS' => 5000]);
		$assigneds = \Models\Assigned::selectAll(['user_id' => $this->account->_id], [], ['maxTimeMS' => 5000]);
		foreach ($assets as $asset) {
			switch ($asset->status) {
				case 'available':
					$assetAva[] = $asset;
					break;

				case 'discarded':
					$assetDis[] = $asset;
					break;
				case 'assigned':
					$assetAss[] = $asset;
					break;

			}
		}

		$view->set('Assets', $assets ?? [])
			->set('Employees', $employees ?? [])
			->set('Assigneds', $assigneds ?? [])
			->set('AssetAva', $assetAva ?? [])
			->set('AssetAss', $assetAss ?? [])
			->set('AssetDis', $assetDis ?? [])
			->set('vendors', $vendors ?? []);
	}




}
