<?php

namespace Enumag\Application\UI;

use Doctrine\Common\Util\ClassUtils;
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
	 * @todo The var annotation has to be fully qualified.
	 * @link https://github.com/Kdyby/Autowired/issues/17
	 * @var \Arachne\EntityLoader\EntityLoader
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
