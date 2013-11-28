<?php

namespace Enumag\Application\DI;

use Nette\DI\CompilerExtension;

/**
 * @author Jáchym Toušek
 */
class ApplicationExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('requestStorage'))
			->setClass('Enumag\Application\RequestStorage');

		$builder->getDefinition('application')
			->setClass('Enumag\Application\Application');
	}

}
