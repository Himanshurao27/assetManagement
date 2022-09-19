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
		$totalAssets = \Models\Asset::count();
		$totalEmployees = \Models\Employee::count();
		$totalAssigneds = \Models\Assigned::count();
		$totalVenders = \Models\Vender::count();

		$view->set('totalAssets', $totalAssets)
			->set('totalEmployees', $totalEmployees)
			->set('totalAssigneds', $totalAssigneds)
			->set('totalVenders', $totalVenders);
	}




}
