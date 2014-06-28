<?php

namespace Enumag\Application;

use Nette\Application\Request;
use Nette\Application\Responses\ForwardResponse;
use Nette\Application\UI\Presenter;

/**
 * @author Jáchym Toušek
 */
trait TRequestStoragePresenter
{

	/**
	 * @persistent
	 * @var string
	 */
	public $backlink;

	/** @var RequestStorage */
	private $requestStorage;

	final public function injectRequestStorage(RequestStorage $requestStorage)
	{
		$this->requestStorage = $requestStorage;
	}

	/**
	 * Stores request to session.
	 * @param Request $request
	 * @param mixed $expiration
	 * @return string
	 */
	public function storeRequest($request = NULL, $expiration = '+ 10 minutes')
	{
		if ($request === NULL) {
			$request = $this->request;
		} elseif (!$request instanceof Request) { // first parameter is optional
			$expiration = $request;
			$request = $this->request;
		}

		return $this->requestStorage->storeRequest($request, $expiration);
	}

	/**
	 * Restores request from session.
	 * @param string $key
	 */
	public function restoreRequest($key = NULL)
	{
		if ($key === NULL) {
			$key = $this->backlink;
		}
		$request = $this->requestStorage->loadRequest($key);
		if (!$request) {
			return;
		}
		$parameters = $request->getParameters();
		$parameters[Presenter::FLASH_KEY] = $this->getParameter(Presenter::FLASH_KEY);
		$request->setParameters($parameters);
		$this->sendResponse(new ForwardResponse($request));
	}

	public function beforeRender()
	{
		parent::beforeRender();

		$method = 'render' . $this->getAction();
		$element = $this->getReflection()->hasMethod($method) ? $this->getReflection()->getMethod($method) : NULL;
		if ($element && $element->getAnnotation('Backlink') && !$this->getSignal()) {
			$this->backlink = $this->storeRequest();
		}
	}

}
