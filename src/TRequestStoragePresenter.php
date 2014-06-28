<?php

namespace Enumag\Application;

use Nette\Application\IRouter;
use Nette\Application\Request;
use Nette\Application\Responses\ForwardResponse;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\UI\Presenter;
use Nette\Http\IRequest;
use Nette\Http\Url;

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

	/** @var IRouter */
	private $router;

	/** @var IRequest */
	private $httpRequest;

	final public function injectRequestStorage(RequestStorage $requestStorage, IRouter $router, IRequest $httpRequest)
	{
		$this->requestStorage = $requestStorage;
		$this->router = $router;
		$this->httpRequest = $httpRequest;
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

	/**
	 * Restores request from session.
	 * @param string $key
	 */
	public function redirectToRequest($key = NULL)
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
		$refUrl = new Url($this->httpRequest->getUrl());
		$refUrl->setPath($this->httpRequest->getUrl()->getScriptPath());
		$url = $this->router->constructUrl($request, $refUrl);
		$this->sendResponse(new RedirectResponse($url));
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
