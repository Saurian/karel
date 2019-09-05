<?php

namespace Kdyby\SessionPanel\DI;

use Nette;



/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class SessionPanelExtension extends Nette\DI\CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		if ($builder->parameters['debugMode']) {
			$builder->addDefinition($this->prefix('panel'))
				->setClass('Kdyby\SessionPanel\Diagnostics\SessionPanel');
		}
	}

	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		$builder = $this->getContainerBuilder();
		if ($builder->parameters['debugMode']) {
			$class->methods['initialize']->addBody(
				'Kdyby\SessionPanel\Diagnostics\SessionPanel::register($this->getService(?));',
				array($this->prefix('panel'))
			);
		}
	}

	public static function register(Nette\Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Nette\DI\Compiler $compiler) {
			$compiler->addExtension('debugger.session', new SessionPanelExtension());
		};
	}

}
