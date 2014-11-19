<?php

/**
 * AppserverIo\Synchronizable\ObjectTest
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category  Library
 * @package   Synchronizable
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://github.com/appserver-io/synchronizable
 * @link      http://www.appserver.io
 */

namespace AppserverIo\Synchronizable;

/**
 * This is test implementation for Object class.
 *
 * @category  Library
 * @package   Synchronizable
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://github.com/appserver-io/synchronizable
 * @link      http://www.appserver.io
 */
class ArrayObject extends Object implements \ArrayAccess
{

    public function offsetSet($offset, $value)
    {

        $data = unserialize(apc_fetch($this->serial));

        if (is_null($offset)) {
            $data[] = $value;
        } else {
            $data[$offset] = $value;
        }

        apc_store($this->serial, serialize($data));
    }

    public function offsetExists($offset)
    {
        $data = unserialize(apc_fetch($this->serial));
        return isset($data[$offset]);
    }

    public function offsetUnset($offset)
    {
        $data = unserialize(apc_fetch($this->serial));
        unset($data[$offset]);
        apc_store($this->serial, serialize($data));
    }

    public function offsetGet($offset)
    {
        $data = unserialize(apc_fetch($this->serial));
        return isset($data[$offset]) ? $data[$offset] : null;
    }
}
