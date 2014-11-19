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
 * A thread implementation that raises a property of a synchronizable object.
 *
 * @category  Library
 * @package   Synchronizable
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://github.com/appserver-io/synchronizable
 * @link      http://www.appserver.io
 */
class RaiseCounterThread extends \Thread
{

    /**
     * Initialize the thread with the synchronizable object and the mutex
     *
     * @param \AppserverIo\Synchronizable\SynchronizableInterface $object The synchronizable counter instance
     * @param integer                                             $mutex  The mutex for locking/unlocking counter
     */
    public function __construct(SynchronizableInterface $object, $mutex)
    {
        $this->object = $object;
        $this->mutex = $mutex;
    }

    /**
     * Raise the counter inside the thread context.
     *
     * @return void
     */
    public function run()
    {

        // raise the counter by 1 till 5000
        for ($i = 0; $i < 5000; $i++) {
            \Mutex::lock($this->mutex);
            $this->object->counter++;
            \Mutex::unlock($this->mutex);
        }
    }
}
