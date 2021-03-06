<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Reflection;

use Nette;
use Nette\Utils\ObjectMixin;


/**
 * Reports information about a function.
 * @property-read array $defaultParameters
 * @property-read bool $closure
 * @property-read Extension $extension
 * @property-read Parameter[] $parameters
 * @property-read bool $disabled
 * @property-read bool $deprecated
 * @property-read bool $internal
 * @property-read bool $userDefined
 * @property-read string $docComment
 * @property-read int $endLine
 * @property-read string $extensionName
 * @property-read string $fileName
 * @property-read string $name
 * @property-read string $namespaceName
 * @property-read int $numberOfParameters
 * @property-read int $numberOfRequiredParameters
 * @property-read string $shortName
 * @property-read int $startLine
 * @property-read array $staticVariables
 */
class GlobalFunction extends \ReflectionFunction
{
	/** @var string|\Closure */
	private $value;


	public function __construct($name)
	{
		parent::__construct($this->value = $name);
	}


	/**
	 * @deprecated
	 */
	public function toCallback()
	{
		return new Nette\Callback($this->value);
	}


	public function __toString()
	{
		return $this->getName() . '()';
	}


	public function getClosure()
	{
		return PHP_VERSION_ID < 50400
			? Nette\Utils\Callback::closure($this->value)
			: parent::getClosure();
	}


	/********************* Reflection layer ****************d*g**/


	/**
	 * @return Extension
	 */
	public function getExtension()
	{
		return ($name = $this->getExtensionName()) ? new Extension($name) : NULL;
	}


	/**
	 * @return Parameter[]
	 */
	public function getParameters()
	{
		foreach ($res = parent::getParameters() as $key => $val) {
			$res[$key] = new Parameter($this->value, $val->getName());
		}
		return $res;
	}


	/********************* Nette\Annotations support ****************d*g**/


	/**
	 * Has method specified annotation?
	 * @param  string
	 * @return bool
	 */
	public function hasAnnotation($name)
	{
		$res = AnnotationsParser::getAll($this);
		return !empty($res[$name]);
	}


	/**
	 * Returns an annotation value.
	 * @param  string
	 * @return IAnnotation
	 */
	public function getAnnotation($name)
	{
		$res = AnnotationsParser::getAll($this);
		return isset($res[$name]) ? end($res[$name]) : NULL;
	}


	/**
	 * Returns all annotations.
	 * @return IAnnotation[][]
	 */
	public function getAnnotations()
	{
		return AnnotationsParser::getAll($this);
	}


	/**
	 * Returns value of annotation 'description'.
	 * @return string
	 */
	public function getDescription()
	{
		return $this->getAnnotation('description');
	}


	/********************* Nette\Object behaviour ****************d*g**/


	public function __call($name, $args)
	{
		return ObjectMixin::call($this, $name, $args);
	}


	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}


	public function __set($name, $value)
	{
		ObjectMixin::set($this, $name, $value);
	}


	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}


	public function __unset($name)
	{
		ObjectMixin::remove($this, $name);
	}

}
