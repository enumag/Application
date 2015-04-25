<?php

namespace Enumag\Application\DI;

use Arachne\DIHelpers\CompilerExtension;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ApplicationExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		if ($this->getExtension('Nette\Bridges\HttpDI\SessionExtension', false) || $this->getExtension('Nette\Bridges\Framework\NetteExtension', false)) {
			$builder->addDefinition($this->prefix('requestStorage'))
				->setClass('Enumag\Application\UI\RequestStorage');
		}
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		$builder->getDefinition($builder->getByType('Nette\Application\Application') ?: 'application')
			->setFactory('Enumag\Application\Application');
	}

}
