<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\tests\phpunit\system\core\models;

use gplcart\tests\phpunit\support\PhpUnitTest;

/**
 * @coversDefaultClass \gplcart\core\models\Address
 */
class AddressTest extends PhpUnitTest
{

    /**
     * Address model class instance
     * @var \gplcart\core\models\Address $object
     */
    protected $object;

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
        parent::setUp();
    }

    /**
     * @covers gplcart\core\models\Address::get
     */
    public function testGet()
    {
        $result = $this->object->get(1);
        $this->assertEquals(1, $result['address_id']);

        $result = $this->object->get(999999);
        $this->assertEmpty($result);
        $this->assertInternalType('array', $result);
    }

    /**
     * @covers gplcart\core\models\Address::add
     */
    public function testAdd()
    {
        $data = $this->getFixtureData('address');
        $this->removeFixtureAutoincrementField($data);

        $result = $this->object->add(reset($data));
        $this->assertInternalType('int', $result);

        $added = $this->object->get($result);
        $this->assertEquals($result, $added['address_id']);
    }

    /**
     * @covers gplcart\core\models\Address::delete
     */
    public function testDelete()
    {
        $data = $this->getFixtureData('address');
        $this->removeFixtureAutoincrementField($data);

        $added = $this->object->add(reset($data));

        $this->assertTrue($this->object->delete($added));
        $this->assertEmpty($this->object->get($added));

        $this->assertFalse($this->object->delete(999999));
        $this->assertFalse($this->object->delete(1));
    }

    /**
     * @covers gplcart\core\models\Address::getList
     */
    public function testGetList()
    {
        $result = $this->object->getList();
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);

        $result = $this->object->getList(array('count' => true));
        $this->assertSame(2, $result);
    }

    /**
     * @covers gplcart\core\models\Address::update
     */
    public function testUpdate()
    {
        $data = $this->getFixtureData('address');
        $address = reset($data);

        $result = $this->object->update($address['address_id'], array('address_1' => 'Changed'));
        $this->assertTrue($result);

        $updated = $this->object->get($address['address_id']);
        $this->assertNotEquals($address['address_1'], $updated['address_1']);

        $result = $this->object->update($address['address_id'], array('some_fake_field' => 'Changed'));
        $this->assertFalse($result);

        $result = $this->object->update(999999, array('address_1' => 'Changed'));
        $this->assertFalse($result);
    }

}
