<?php

namespace Kdyby\SessionPanel\Diagnostics;

use Nette;
use Nette\Http\IRequest;
use Nette\Iterators\Mapper;
use Tracy;



/**
 * Nette Debug Session Panel
 *
 * @author Pavel Železný <info@pavelzelezny.cz>
 * @author Filip Procházka <email@filip-prochazka.cz>
 */
class SessionPanel implements Tracy\IBarPanel
{

	use Nette\SmartObject;

	const SIGNAL = 'nette-session-panel-delete-session';
	const SECTION_TYPE = 'section-type';
	const NETTE_SESSION = 'nette-session';
	const PHP_SESSION = 'php-session';

	/** @var \Nette\Http\Session */
	private $session;

	/** @var \Nette\Http\UrlScript */
	private $url;



	/**
	 * @param \Nette\Http\Session $session
	 * @param \Nette\Http\IRequest $httpRequest
	 */
	public function __construct(Nette\Http\Session $session, IRequest $httpRequest)
	{
		$this->session = $session;
		$this->url = clone $httpRequest->getUrl();
		$this->processSignal($httpRequest);
	}



	/**
	 * @param \Nette\Http\IRequest $httpRequest
	 */
	private function processSignal(IRequest $httpRequest)
	{
		if ($httpRequest->getQuery('do') !== self::SIGNAL) {
			return;
		}

		if (!$this->session->isStarted()) {
			$this->session->start();
		}

		if ($section = $httpRequest->getQuery(self::SIGNAL)) {
			if ($httpRequest->getQuery(self::SECTION_TYPE) == self::PHP_SESSION) {
				unset($_SESSION[$section]);
			} elseif ($httpRequest->getQuery(self::SECTION_TYPE) == self::NETTE_SESSION) {
				$this->session->getSection($section)->remove();
			}
		} else {
			$this->session->destroy();
		}

		$query = $httpRequest->getQuery();
		unset($query['do'], $query[self::SIGNAL], $query[self::SECTION_TYPE]);
		$this->url->setQuery($query);

		$response = new Nette\Http\Response();
		$response->redirect($this->url);
		exit(0);
	}



	/**
	 * Html code for DebuggerBar Tab
	 * @return string
	 */
	public function getTab()
	{
		return self::render(__DIR__ . '/templates/tab.phtml', array(
			'src' => function ($file) {
				return \Latte\Runtime\Filters::dataStream(file_get_contents($file));
			},
			'esc' => Nette\Utils\Callback::closure(function($string) {
				return htmlspecialchars($string, ENT_QUOTES);
			}),
		));
	}



	/**
	 * Html code for DebuggerBar Panel
	 * @return string
	 */
	public function getPanel()
	{
		$url = $this->url;
		return self::render(__DIR__ . '/templates/panel.phtml', array(
			'time' => Nette\Utils\Callback::closure(get_called_class() . '::time'),
			'esc' => Nette\Utils\Callback::closure(function($string) {
				return htmlspecialchars($string, ENT_QUOTES);
			}),
			'click' => Nette\Utils\Callback::closure(function ($variable) {
				if (class_exists('Tracy\Dumper')) {
					return Tracy\Dumper::toHtml($variable, array(Tracy\Dumper::COLLAPSE => TRUE));
				} else {
					return Nette\Diagnostics\Helpers::clickableDump($variable, TRUE);
				}
			}),
			'del' => function ($section = NULL, $sectionType = NULL) use ($url) {
				$url = clone $url;
				$url->appendQuery(array(
					'do' => SessionPanel::SIGNAL,
					SessionPanel::SIGNAL => $section,
					SessionPanel::SECTION_TYPE => $sectionType,
				));
				return (string) $url;
			},
			'sections' => $this->createSessionIterator(),
			'sessionMaxTime' => $this->session->getOptions()['gc_maxlifetime'],
		));
	}


	/**
	 * @return \AppendIterator
	 */
	protected function createSessionIterator(){
		$iterator = new \AppendIterator();
		$iterator->append($this->createNetteSessionIterator());
		$iterator->append($this->createPhpSessionIterator());
		return $iterator;
	}


	/**
	 * @return \Iterator
	 */
	protected function createPhpSessionIterator()
	{
		$sections = array();

		if ($this->session->exists()) {
			$this->session->start();

			foreach ($_SESSION as $sectionName => $data) {
				if ($sectionName === '__NF') continue;

				$sections[] = (object) array(
					'title' => $sectionName,
					'data' => $data,
					'expiration' => 'inherited',
					'sectionType' => SessionPanel::PHP_SESSION,
				);
			};
		}

		return new \ArrayIterator($sections);
	}


	/**
	 * @return \Iterator
	 */
	protected function createNetteSessionIterator()
	{
		$sections = $this->session->getIterator();

		return new Mapper($sections, function ($sectionName) {
			$data = $_SESSION['__NF']['DATA'][$sectionName];

			$section = (object) array(
				'title' => $sectionName,
				'data' => $data,
				'expiration' => 'inherited',
				'sectionType' => SessionPanel::NETTE_SESSION,
			);

			$meta = isset($_SESSION['__NF']['META'][$sectionName])
				? $_SESSION['__NF']['META'][$sectionName]
				: array();

			if (isset($meta['']['T'])) {
				$section->expiration = SessionPanel::time($meta['']['T'] - time());
			} elseif (isset($meta['']['B']) && $meta['']['B'] === TRUE) {
				$section->expiration = 'Browser';
			}

			return $section;
		});
	}



	/**
	 * @param string $file
	 * @param array $vars
	 * @return string
	 */
	public static function render($file, $vars)
	{
		return call_user_func(function() {
			ob_start();
			foreach (func_get_arg(1) as $__k => $__v) {
				$$__k = $__v;
			}
			unset($__k, $__v);
			require func_get_arg(0);
			return ob_get_clean();
		}, $file, $vars);
	}



	/**
	 * @param int $seconds
	 * @return string
	 */
	public static function time($seconds)
	{
		static $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
		static $lengths = array("60", "60", "24", "7", "4.35", "12", "10");

		$difference = $seconds > Nette\Utils\DateTime::YEAR ? time() - $seconds : $seconds;
		for ($j = 0; $difference >= $lengths[$j]; $j++) {
			$difference /= $lengths[$j];
		}
		$multiply = ($difference = round($difference)) != 1;
		return "$difference {$periods[$j]}" . ($multiply ? 's' : '');
	}



/****************** Registration *********************/



	/**
	 * Registers panel to debugger
	 *
	 * @param \Tracy\Bar $bar
	 */
	public function registerBarPanel(Tracy\Bar $bar)
	{
		$bar->addPanel($this);
	}


	/**
	 * @return Panel
	 */
	public static function register(SessionPanel $panel)
	{
		$panel->registerBarPanel(static::getDebuggerBar());
		return $panel;
	}



	/**
	 * @return Bar
	 */
	private static function getDebuggerBar()
	{
		return method_exists('Tracy\Debugger', 'getBar') ? Tracy\Debugger::getBar() : Tracy\Debugger::$bar;
	}


}
