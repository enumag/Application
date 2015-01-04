<?php

namespace Enumag\Application\UI;

use Nette\Application\UI\InvalidLinkException;
use Nextras\Application\UI\SecuredLinksControlTrait as BaseSecuredLinksControlTrait;

/**
 * @author Jáchym Toušek
 */
trait SecuredLinksControlTrait
{

	use BaseSecuredLinksControlTrait {
		link as private parentLink;
	}

	/**
	 * Generates URL to presenter, action or signal.
	 * @param string $destination
	 * @param array|mixed $args
	 * @return string
	 * @throws InvalidLinkException
	 */
	public function link($destination, $args = array())
	{
		$first = substr($destination, 0, 1);
		if (strtolower($first) !== $first || $first === ':') {
			return $this->getPresenter()->link($destination, $args);
		}
		return $this->parentLink($destination, $args);
	}

}
