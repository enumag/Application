<?php

namespace Enumag\Application\DI;

use Nette\DI\CompilerExtension;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ApplicationExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('requestStorage'))
			->setClass('Enumag\Application\UI\RequestStorage');
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		$builder->getDefinition($builder->getByType('Nette\Application\Application') ?: 'application')
			->setClass('Enumag\Application\Application');
	}

}
