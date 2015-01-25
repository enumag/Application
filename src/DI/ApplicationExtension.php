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

		$builder->getDefinition('application')
			->setClass('Enumag\Application\Application');

		$builder->addDefinition($this->prefix('requestStorage'))
			->setClass('Enumag\Application\UI\RequestStorage');
	}

}
