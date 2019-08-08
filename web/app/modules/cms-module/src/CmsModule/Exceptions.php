<?php
/**
 * This file is part of the devrun
 * Copyright (c) 2016
 *
 * @file    Exceptions.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace CmsModule;

use Nette\IOException;


/**
 * @author Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */
interface Exception
{

}



/**
 * @author Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */
class InvalidStateException extends \RuntimeException implements Exception
{

}



/**
 * @author Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */
class OutOfRangeException extends \OutOfRangeException implements Exception
{

}



/**
 * @author Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */
class NotSupportedException extends \LogicException implements Exception
{

}


/**
 * @author Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */
class InvalidArgumentException extends \InvalidArgumentException implements Exception
{

}


/**
 * @author Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */
class ApplicationException extends \Nette\Application\ApplicationException implements Exception
{

}


/**
 * @author Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */
class FileNotFoundException extends IOException implements Exception
{
}
