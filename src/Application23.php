<?php

namespace Enumag\Application;

class Application extends AbstractApplication
{

	public function processException(\Exception $e)
	{
		$this->doProcessException($e);
	}

}
