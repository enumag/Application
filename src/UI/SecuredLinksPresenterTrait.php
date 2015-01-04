<?php

namespace Enumag\Application\UI;

use Arachne\EntityLoader\EntityLoader;
use Doctrine\Common\Util\ClassUtils;
use Nextras\Application\UI\SecuredLinksPresenterTrait as BaseSecuredLinksPresenterTrait;

/**
 * @author Jáchym Toušek
 */
trait SecuredLinksPresenterTrait
{

	use BaseSecuredLinksPresenterTrait {
		getCsrfToken as private parentGetCsrfToken;
	}

	/**
	 * @var EntityLoader
	 * @autowire
	 */
	protected $entityLoader;

	public function getCsrfToken($control, $method, $params)
	{
		array_walk($params, function (&$value) {
			if (is_object($value)) {
				$value = $this->entityLoader->filterOut(ClassUtils::getRealClass(get_class($value)), $value);
			}
		});
		return $this->parentGetCsrfToken($control, $method, $params);
	}

}
