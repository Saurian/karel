<?php


namespace Devrun\Doctrine;

use Doctrine;

interface Exception
{

}



/**
 */
class InvalidStateException extends \RuntimeException implements Exception
{

}


/**
 */
class InvalidArgumentException extends \InvalidArgumentException implements Exception
{

}


/**
 * The exception that is thrown when a requested method or operation is not implemented.
 */
class NotImplementedException extends \LogicException implements Exception
{

}


class UnexpectedValueException extends \UnexpectedValueException implements Exception
{

}


class DuplicateEntryException extends \Kdyby\Doctrine\DuplicateEntryException implements Exception
{

}
