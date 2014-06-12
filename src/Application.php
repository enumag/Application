<?php

namespace Enumag\Application;

use Nette\Application\AbortException;
use Nette\Application\Application as BaseApplication;
use Nette\Application\BadRequestException;
use Nette\Application\InvalidPresenterException;
use Nette\Application\IPresenterFactory;
use Nette\Application\IRouter;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Nette\Http\IRequest;
use Nette\Http\IResponse;

class Application extends BaseApplication
{

	/** @var IPresenterFactory */
	private $presenterFactory;

	/** @var IResponse */
	private $httpResponse;

	public function __construct(IPresenterFactory $presenterFactory, IRouter $router, IRequest $httpRequest, IResponse $httpResponse)
	{
		parent::__construct($presenterFactory, $router, $httpRequest, $httpResponse);
		$this->presenterFactory = $presenterFactory;
		$this->httpResponse = $httpResponse;
	}

	public function run()
	{
		try {
			$this->onStartup($this);
			$this->processRequest($this->createInitialRequest());
			$this->onShutdown($this);

		} catch (\Exception $e) {
			$this->onError($this, $e);

			// if catchExceptions is NULL, catch only BadRequestException
			if (($this->catchExceptions || ($this->catchExceptions === NULL && $e instanceof BadRequestException)) && $this->errorPresenter) {
				try {
					$this->processException($e);
					$this->onShutdown($this, $e);
					return;

				} catch (\Exception $e) {
					$this->onError($this, $e);
				}
			}
			$this->onShutdown($this, $e);
			throw $e;
		}
	}

	public function processException(\Exception $e)
	{
		if (!$this->httpResponse->isSent()) {
			$this->httpResponse->setCode($e instanceof BadRequestException ? ($e->getCode() ?: 404) : 500);
		}

		$requests = $this->getRequests();
		$request = end($requests);
		$args = array('exception' => $e, 'request' => $request ?: NULL);

		if ($request) {
			$name = $request->getPresenterName();
			$pos = strrpos($name, ':');
			$module = $pos !== FALSE ? substr($name, 0, $pos) : '';
			$errorPresenter = "$module:$this->errorPresenter";
		}

		try {
			$this->presenterFactory->getPresenterClass($errorPresenter);
		} catch (InvalidPresenterException $_) {
			$errorPresenter = $this->errorPresenter;
		}

		if ($this->presenter instanceof Presenter) {
			try {
				$this->presenter->forward(":$errorPresenter:", $args);
			} catch (AbortException $_) {
				$this->processRequest($this->presenter->getLastCreatedRequest());
			}
		} else {
			$this->processRequest(new Request($errorPresenter, Request::FORWARD, $args));
		}
	}

}
