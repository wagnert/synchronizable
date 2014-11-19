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
class ObjectTest extends \PHPUnit_Framework_TestCase
{

    /**
     * The synchronizable object implementation.
     *
     * @var \AppserverIo\Synchronizable\Object
     */
    protected $object;

    /**
     * Initializes the object we want to test.
     *
     * @return void
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        apc_clear_cache(); // clear the APCu cache
        $this->object = new Object();
    }

    /**
     * Cleans up before the the next test case will be invoked.
     *
     * @return void
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {

        // load the serial
        $serial = $this->object->__serial();

        // unset the object
        unset($this->object);

        // make sure that memory has been cleaned up
        $result = apc_exists($serial);

        var_export($result);
        // $this->assertFalse($result);
    }

    /**
     * Assigning a property to an object.
     *
     * @return void
     */
    public function testAssignProperty()
    {
        $this->object->test = 'test';
        $this->assertSame('test', $this->object->test);
    }

    /**
     * Try to access a not defined property of an object.
     *
     * @return void
     * @expectedException \Exception
     */
    public function testTryToAccessNotInitializedProperty()
    {
        $this->object->test;
    }

    /**
     * Tests synchronization by using a mutex.
     *
     * @return void
     */
    public function testSynchronizeCounterWithMutex()
    {

        // set the counter to ZERO
        $this->object->counter = 0;

        // initialize array containing the threads
        $threads = array();

        // initialize the mutex
        $mutex = \Mutex::create();

        // initialize the threads and start them
        for ($i = 0; $i < 2; $i++) {
            $threads[$i] = new RaiseCounterThread($this->object, $mutex);
            $threads[$i]->start();
        }

        // wait for the threads to be finished
        for ($i = 0; $i < 2; $i++) {
            $threads[$i]->join();
        }

        // check the counter
        $this->assertSame(10000, $this->object->counter);
    }
}
