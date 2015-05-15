<?php

namespace Enumag\Application\UI;

use Arachne\EntityLoader\EntityUnloader;
use Nextras\Application\UI\SecuredLinksPresenterTrait as BaseSecuredLinksPresenterTrait;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
trait SecuredLinksPresenterTrait
{

	use BaseSecuredLinksPresenterTrait {
		getCsrfToken as private parentGetCsrfToken;
	}

	/**
	 * @var EntityUnloader
	 * @autowire
	 */
	protected $entityUnloader;

	public function getCsrfToken($control, $method, $params)
	{
		array_walk($params, function (&$value) {
			if (is_object($value)) {
				$value = $this->entityUnloader->filterOut($value);
			}
		});
		return $this->parentGetCsrfToken($control, $method, $params);
	}

}
