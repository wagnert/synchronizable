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

        // check the object status
        $this->assertSame(10, $reference->value);
        $this->assertSame(2, Registry::refCount($reference));
        $this->assertTrue(Registry::hasData($reference->__serial()));

        // destroy the reference
        $reference->__destroy();

        // make sure the reference count has been decreased
        $this->assertSame(1, Registry::refCount($this->object));
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

    /**
     * Test object destruction when passing to a threads constructor.
     *
     * @return void
     */
    public function testPassThroughThreadConstructor()
    {

        // set the counter to ZERO
        $this->object->counter = 0;

        // check the reference counter
        $this->assertSame(1, Registry::refCount($this->object));

        // initialize the mutex
        $mutex = \Mutex::create();

        // execute the thread
        $thread = new PassThroughConstructorThread($this->object, $mutex);
        $thread->start();
        $thread->join();

        // check the reference counter
        $this->assertSame(1, Registry::refCount($this->object));
    }
}
