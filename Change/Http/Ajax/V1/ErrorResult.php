<?php
/**
 * Copyright (C) 2014 Proximis
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
namespace Change\Http\Ajax\V1;

/**
 * @name \Change\Http\Ajax\V1\ErrorResult
 */
class ErrorResult extends \Change\Http\Result
{

	/**
	 * @var string
	 */
	protected $errorCode;

	/**
	 * @var string
	 */
	protected $errorMessage;

	/**
	 * @var array
	 */
	protected $data;


	/**
	 * @param string|\Exception $errorCode
	 * @param string $errorMessage
	 * @param integer $httpStatusCode
	 */
	public function __construct($errorCode = null, $errorMessage = null, $httpStatusCode = \Zend\Http\Response::STATUS_CODE_500)
	{
		if ($errorCode instanceof \Exception)
		{
			if ($errorMessage === null)
			{
				$errorMessage = $errorCode->getMessage();
			}

			if (isset($errorCode->httpStatusCode))
			{
				$httpStatusCode = $errorCode->httpStatusCode;
			}
			$errorCode = $errorCode->getCode() ? 'EXCEPTION-' . $errorCode->getCode() : 'EXCEPTION';
		}

		$this->setHttpStatusCode($httpStatusCode);
		$this->errorCode = $errorCode;
		$this->errorMessage = $errorMessage;
	}



	/**
	 * @param string $errorCode
	 */
	public function setErrorCode($errorCode)
	{
		$this->errorCode = $errorCode;
	}

	/**
	 * @return string
	 */
	public function getErrorCode()
	{
		return $this->errorCode;
	}

	/**
	 * @param string $errorMessage
	 */
	public function setErrorMessage($errorMessage)
	{
		$this->errorMessage = $errorMessage;
	}

	/**
	 * @return string
	 */
	public function getErrorMessage()
	{
		return $this->errorMessage;
	}

	/**
	 * @param array $data
	 */
	public function setData(array $data = null)
	{
		$this->data = $data;
	}

	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 */
	public function addDataValue($name, $value)
	{
		if (is_string($name))
		{
			if ($this->data === null) {$this->data = array();}
			if ($value === null)
			{
				unset($this->data[$name]);
			}
			else
			{
				$this->data[$name] = $value;
			}
		}
		return $this;
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		$array = ['error' => true, 'code' => $this->getErrorCode(), 'message' => $this->getErrorMessage()];
		if (is_array($this->data) && count($this->data))
		{
			$array['data'] = $this->data;
		}
		return $array;
	}
}