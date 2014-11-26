<?php

/**
 * AppserverIo\Synchronizable\Registry
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
 * Synchronizable object registry.
 *
 * @category  Library
 * @package   Synchronizable
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://github.com/appserver-io/synchronizable
 * @link      http://www.appserver.io
 */
class Registry
{

    /**
     * Creates a new synchronizable instance, attaches it to the registry and returns it.
     *
     * @param string $className The class name of the synchronizable to create
     * @param array  $args      The arguments to be passed to the constructor
     *
     * @return \AppserverIo\Synchronizable\SynchronizableInterface The instance
     */
    public static function create($className, array $args = array())
    {

        // create the instance
        $reflectionClass = new \ReflectionClass($className);
        $synchronizable = $reflectionClass->newInstanceArgs($args);

        // attach the instance
        Registry::attach($synchronizable);

        // return the instance
        return $synchronizable;
    }

    /**
     * Queries, whether the instance with the passed serial has data in APCu or not.
     *
     * @param string $serial The serial of the instance we want to check for
     *
     * @return boolean TRUE if the instance has data in APCu, else FALSE
     */
    public static function hasData($serial)
    {
        return count(new \ApcIterator('user', '/^' . $serial . '\./')) > 0;
    }

    /**
     * Deattaches the passed synchronizable instance from the registry, destroys it, and
     * deletes all data from the APCu if no more instances are registered.
     *
     * @param \AppserverIo\Synchronizable\SynchronizableInterface $synchronizable The instance to be destroyed
     *
     * @return void
     * @throws \RuntimeException Is thrown if the data can't be deleted from APCu
     */
    public static function destroy(SynchronizableInterface $synchronizable)
    {

        // load the serial
        $serial = $synchronizable->__serial();

        // detach the instance
        Registry::detach($synchronizable);

        // destroy the instance
        unset($synchronizable);

        // check if we've to destroy the data here
        if (apc_exists($serial) && apc_fetch($serial) === 0 && Registry::hasData($serial)) {
            $iterator = new \ApcIterator('user', '/^' . $serial . '\./');
            foreach ($iterator as $key => $value) {
                if (apc_delete($key) === false) {
                    throw new \RuntimeException(
                        sprintf('Can\'t delete property for %s::%s (%s) instance', get_class($synchronizable), $key, $serial)
                    );
                }
            }
        }
    }

    /**
     * Returns the reference count for the instance with the passed serial.
     *
     * @param string $serial The serial to return the reference count for
     *
     * @return integer The reference counter for the instance
     */
    public static function refCountBySerial($serial)
    {

        // return the reference count if we know the serial
        if (apc_exists($serial)) {
            return apc_fetch($serial);
        }

        // else return 0
        return 0;
    }

    /**
     * Returns the reference count for the passed synchronizable instance.
     *
     * @param \AppserverIo\Synchronizable\SynchronizableInterface $synchronizable The synchronizable instance to return the reference count for
     *
     * @return integer The reference counter for the instance
     * @see \AppserverIo\Synchronizable\Registry::refCountBySerial()
     */
    public static function refCount(SynchronizableInterface $synchronizable)
    {
        return Registry::refCountBySerial($synchronizable->__serial());
    }

    /**
     * Attaches the passed synchronizable instance to the registry and
     * returns the actual reference count.
     *
     * @param \AppserverIo\Synchronizable\SynchronizableInterface $synchronizable The synchronizable instance to return the reference count for
     *
     * @return integer The reference counter for the instance
     */
    public static function attach(SynchronizableInterface $synchronizable)
    {

        // load the serial
        $serial = $synchronizable->__serial();

        // check if the instance has already been registered
        if (apc_exists($serial) === false) {
            apc_store($serial, 0);
        }

        // if not, regster it with a reference count of 1
        return apc_inc($serial);
    }

    /**
     * Attaches the passed synchronizable instance to the registry and
     * returns the actual reference count.
     *
     * @param \AppserverIo\Synchronizable\SynchronizableInterface $synchronizable The synchronizable instance to return the reference count for
     *
     * @return integer The reference counter for the instance
     * @throws \OutOfBoundsException Is thrown if the instance has no reference count
     * @throws \UnexpectedValueException Is thrown if the reference counter is already 0 and can't be decreased
     */
    public static function detach(SynchronizableInterface $synchronizable)
    {

        // load the serial
        $serial = $synchronizable->__serial();

        // synchronizable is NOT registered
        if (apc_exists($serial) === false) {
            throw new \OutOfBoundsException(sprintf('Can\'t detach synchronizable %s (%s), because it has no reference count', get_class($synchronizable), $serial));
        }

        // detach the synchronizable instance if possible
        if (($refCount = apc_fetch($serial)) < 1) {
            throw new \UnexpectedValueException(sprintf('Can\'t detach synchronizable %s (%s), because reference count %d < 1', get_class($synchronizable), $serial, $refCount));
        }

        // detach the synchronizable instance and return the reference counter
        return apc_dec($serial);
    }
}
