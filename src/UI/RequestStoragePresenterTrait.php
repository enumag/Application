<?php

namespace Enumag\Application\UI;

use Nette\Application\IRouter;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Nette\Http\IRequest;
use Nette\Http\Url;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
trait RequestStoragePresenterTrait
{

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
	public function storeRequest($request = null, $expiration = '+ 10 minutes')
	{
		if ($request === null) {
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
	public function restoreRequest($key)
	{
		$request = $this->requestStorage->loadRequest($key);
		if (!$request) {
			return;
		}
		$parameters = $request->getParameters();
		$parameters[Presenter::FLASH_KEY] = $this->getParameter(Presenter::FLASH_KEY);
		$request->setParameters($parameters);
		$this->forward($request);
	}

	/**
	 * Restores request from session.
	 * @param string $key
	 */
	public function redirectToRequest($key)
	{
		$request = $this->requestStorage->loadRequest($key);
		if (!$request) {
			return;
		}
		$parameters = $request->getParameters();
		$parameters[Presenter::FLASH_KEY] = $this->getParameter(Presenter::FLASH_KEY);
		$parameters[RequestStorage::REQUEST_KEY] = $key;
		$request->setParameters($parameters);
		$refUrl = new Url($this->httpRequest->getUrl());
		$refUrl->setPath($this->httpRequest->getUrl()->getScriptPath());
		$url = $this->router->constructUrl($request, $refUrl);
		$this->redirectUrl($url);
	}

	/**
	 * @link https://github.com/nette/nette/pull/1370
	 */
	public function canonicalize()
	{
		if (!$this->request->hasFlag(Request::RESTORED)) {
			parent::canonicalize();
		}
	}

	public function processSignal()
	{
		if (!$this->request->hasFlag(Request::RESTORED)) {
			parent::processSignal();
		}
	}

	/**
	 * @param Request $request
	 */
	public function run(Request $request)
	{
		if ($request->isMethod('get') && isset($request->getParameters()[RequestStorage::REQUEST_KEY])) {
			$stored = $this->requestStorage->loadRequest($request->getParameters()[RequestStorage::REQUEST_KEY]);
			/** @var Request $stored */
			if ($stored && $stored->getPresenterName() === $request->getPresenterName()) {
				$stored->setFlag(Request::RESTORED, true);
				$parameters = $stored->getParameters();
				if (isset($request->getParameters()[Presenter::FLASH_KEY])) {
					$parameters[Presenter::FLASH_KEY] = $request->getParameters()[Presenter::FLASH_KEY];
				} else {
					unset($parameters[Presenter::FLASH_KEY]);
				}
				$stored->setParameters($parameters);
				$request = $stored;
			}
		}
		return parent::run($request);
	}

}
