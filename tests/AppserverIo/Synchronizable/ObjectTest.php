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
     * Test to create a reference.
     *
     * @return void
     */
    public function testCreateReference()
    {

        // create a reference
        $reference = $this->object->__copy();
        $this->assertSame($reference, $this->object);

        // set a value
        $this->object->value = 10;

        // destroy the instance
        $this->object->__destroy();

        // check the object status
        $this->assertSame(10, $reference->value);
        $this->assertSame(1, Registry::refCount($reference));
        $this->assertTrue(Registry::hasData($reference->__serial()));
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

        // check the object status
        $this->assertSame(10000, $this->object->counter);
        $this->assertSame(1, Registry::refCount($this->object));
        $this->assertTrue(Registry::hasData($this->object->__serial()));
    }

    public function testPassThroughThreadConstructor()
    {

        // set the counter to ZERO
        $this->object->counter = 0;

        echo "Object reference counter on start ObjectTest::testPassThroughThreadConstructor: " . $this->object->__refCount() . PHP_EOL;

        // initialize the mutex
        $mutex = \Mutex::create();

        $thread = new PassThroughConstructorThread($this->object, $mutex);
        $thread->start();
        $thread->join();

        echo "Object reference counter on end ObjectTest::testPassThroughThreadConstructor: " . $this->object->__refCount() . PHP_EOL;
    }
}
