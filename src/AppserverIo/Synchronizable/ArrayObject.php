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

    /**
     * Constructor that'll initialize the synchronizable object.
     */
    public function __construct()
    {

        // actually we're using APCu to store the object properties
        parent::__construct();

        // initialize the property with the items
        $this->__set('items', array());
    }

    /**
     * Assigns a value to the specified offset.
     *
     * @param mixed $offset The offset to assign the value to
     * @param mixed $value  The value to set
     *
     * @return void
     * @see \ArrayAccess::offsetSet()
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     */
    public function offsetSet($offset, $value)
    {

        // load the property with the items data
        $data = $this->__get('items');

        // add the data depending an offset has been passed or not
        if (is_null($offset)) {
            $data[] = $value;
        } else {
            $data[$offset] = $value;
        }

        // store the data back to the property
        $this->__set('items', $data);
    }

    /**
     * Whether or not an offset exists.
     *
     * This method is executed when using isset() or empty() on objects implementing ArrayAccess.
     *
     * @param mixed $offset An offset to check for
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     * @see \ArrayAccess::offsetExists()
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     */
    public function offsetExists($offset)
    {
        $data = $this->__get('items');
        return isset($data[$offset]);
    }

    /**
     * Unsets an offset.
     *
     * @param mixed $offset The offset to unset
     *
     * @return void
     * @see \ArrayAccess::offsetUnset()
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     */
    public function offsetUnset($offset)
    {
        $data = $this->__get('items');
        unset($data[$offset]);
        $this->__set('items', $data);
    }

    /**
     * Returns the value at specified offset.
     *
     * This method is executed when checking if offset is empty().
     *
     * @param mixed $offset The offset to retrieve
     *
     * @return mixed The value
     * @see \ArrayAccess::offsetGet()
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     */
    public function offsetGet($offset)
    {
        $data = $this->__get('items');
        return isset($data[$offset]) ? $data[$offset] : null;
    }
}
