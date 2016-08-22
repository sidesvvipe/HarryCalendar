<?php

namespace App\Presenters;

use Nette;
use App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	/** @var \Nette\Database\Context */
	public $database;
	protected $log;
	private $todayDayOfTheYear;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
		$this->log = new Model\Log($database);
		//TODO dont forget to remove this l8r too
		$cache = new \Nette\Caching\Cache(new Nette\Caching\Storages\DevNullStorage());
		$cache->clean(array($cache::ALL => TRUE));
	}

	protected function startup()
	{
		parent::startup();
		if ($this->getUser()->isLoggedIn()) {
			//TODO add something like "extend my session time by two weeks
			$this->log->add("userLogIn", array("userId" => $this->user->id));
		} else {
			if ($this->logCheck())
				$this->log->add("");
		}
	}

	public function beforeRender()
	{
		parent::beforeRender(); // TODO: Change the autogenerated stub. UPDATE #1 WTF is stub?
		$this->template->currentYearTable = Model\BackgroundTable::createTable("currentYear", array("random" => true, "showToday" => true));
//		$this->template->previousYearsTable = Model\BackgroundTable::createTable("previousYears", array("year" => "1997"));
		$this->template->previousYearsTable = Model\BackgroundTable::createTable("previousYears", "1997");
	}

	/**
	 * @return mixed
	 */
	public function getTodayDayOfTheYear()
	{
		return date("z")+1;
	}

	protected function logCheck ($event, $params) {
		if ($this->log->check($event, $params) == 0)
			$this->log->add($event, $params);
		else {
			echo "This action has been registered earlier";
			die();
			//TODO Either move whole function in Log class or remove before Production...
			// Update #1 Got an idea, we can do something like Check log for logins from curent IP address to be able
			// to show tutorial or hints for new users only. Alternatively we can fill in user name before user even click on form...
			// Update #2 This proved to be useful in cases like IPcheck
		}
	}
}
