<?php

namespace Enumag\Application;

class Application extends AbstractApplication
{

	public function processException($e)
	{
		$this->doProcessException($e);
	}

}
