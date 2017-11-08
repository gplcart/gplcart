<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\tests\phpunit\system\core\models;

use gplcart\tests\phpunit\support\UnitTest;

/**
 * @coversDefaultClass \gplcart\core\models\Address
 */
class AddressTest extends UnitTest
{

    /**
     * Address model class instance
     * @var \gplcart\core\models\Address $object
     */
    protected $object;

    /**
     * An array of fixture data
     * @var array
     */
    protected $fixture_data;

    /**
     * Returns an array of configuration used to mock the testing object
     * @return array
     */
    protected function getMockConfig()
    {
        return array(
            'gplcart\\core\\Database' => $this->getSystemDatabase(),
            'gplcart\\core\\models\\Country' => array(
                'methods' => array(
                    'getDefaultFormat' => array('return' => array())
                )
            )
        );
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = $this->getInstance('gplcart\\core\\models\\Address', $this->getMockConfig());
        $this->fixture_data = $this->getFixtureData('address');

        parent::setUp();
    }

    /**
     * @covers gplcart\core\models\Address::get
     */
    public function testGet()
    {
        $first = reset($this->fixture_data);
        $result = $this->object->get($first['address_id']);
        $this->assertEquals($first['address_id'], $result['address_id']);

        $result = $this->object->get(999999);
        $this->assertEmpty($result);
        $this->assertInternalType('array', $result);
    }

    /**
     * @covers gplcart\core\models\Address::add
     */
    public function testAdd()
    {
        $data = $this->fixture_data;
        $this->removeFixtureAutoincrementField($data);
        $first = reset($data);

        $result = $this->object->add($first);
        $this->assertInternalType('int', $result);
        $this->assertDbRecordExists('address', 'address_id', $result);
    }

    /**
     * @covers gplcart\core\models\Address::delete
     */
    public function testDelete()
    {
        $data = $this->fixture_data;
        $this->removeFixtureAutoincrementField($data);

        // Passes
        $added = $this->object->add(reset($data));
        $this->assertDbRecordExists('address', 'address_id', $added);
        $this->assertTrue($this->object->delete($added));
        $this->assertDbRecordNotExists('address', 'address_id', $added);

        // Fails
        // Try to delete by a fake ID
        $this->assertFalse($this->object->delete(999999));
        // Address referenced
        $this->assertFalse($this->object->delete(1));
    }

    /**
     * @covers gplcart\core\models\Address::getList
     */
    public function testGetList()
    {
        $result = $this->object->getList();
        $this->assertInternalType('array', $result);
        $this->assertCount(count($this->fixture_data), $result);

        $result = $this->object->getList(array('count' => true));
        $this->assertSame(count($this->fixture_data), $result);
    }

    /**
     * @covers gplcart\core\models\Address::update
     */
    public function testUpdate()
    {
        $address = reset($this->fixture_data);

        // Fails
        // Update by fake field
        $result = $this->object->update($address['address_id'], array('some_fake_field' => 'Changed'));
        $this->assertFalse($result);
        $this->assertDbRecordEquals($address, 'address', 'address_id', $address['address_id']);

        // Update by a fake ID
        $result = $this->object->update(999999, array('address_1' => 'Changed'));
        $this->assertFalse($result);
        $this->assertDbRecordEquals($address, 'address', 'address_id', $address['address_id']);

        // Passes
        $result = $this->object->update($address['address_id'], array('address_1' => 'Changed'));
        $this->assertTrue($result);
        $this->assertDbRecordNotEquals($address, 'address', 'address_id', $address['address_id']);
    }

}
