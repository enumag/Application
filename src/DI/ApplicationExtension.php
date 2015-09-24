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

		if ($this->compiler->getExtensions('Nette\Bridges\HttpDI\SessionExtension') || $this->compiler->getExtensions('Nette\Bridges\Framework\NetteExtension')) {
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
