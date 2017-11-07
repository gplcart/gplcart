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
 * @coversDefaultClass \gplcart\core\models\Alias
 */
class AliasTest extends UnitTest
{

    /**
     * @var \gplcart\core\models\Alias $object
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
            'gplcart\\core\\Route' => array(
                'methods' => array(
                    'getList' => array('return' => array())
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
        $this->object = $this->getInstance('gplcart\\core\\models\\Alias', $this->getMockConfig());
        parent::setUp();
    }

    /**
     * @covers gplcart\core\models\Alias::add
     */
    public function testAdd()
    {
        $data = $this->getFixtureData('alias');
        $this->removeFixtureAutoincrementField($data);

        $result = $this->object->add(reset($data));
        $this->assertInternalType('int', $result);

        $added = $this->object->get($result);
        $this->assertEquals($result, $added['alias_id']);
    }

    /**
     * @covers gplcart\core\models\Alias::get
     */
    public function testGet()
    {
        $result = $this->object->get(1);
        $this->assertEquals(1, $result['alias_id']);

        $result = $this->object->get(1, 'product');
        $this->assertEquals('product', $result['id_key']);

        $result = $this->object->get(999999);
        $this->assertEmpty($result);
        $this->assertInternalType('array', $result);

        $result = $this->object->get(999999, 'fake_entity');
        $this->assertEmpty($result);
        $this->assertInternalType('array', $result);
    }

    /**
     * @covers gplcart\core\models\Alias::delete
     */
    public function testDelete()
    {
        $data = $this->getFixtureData('alias');
        $this->removeFixtureAutoincrementField($data);

        $added = $this->object->add(reset($data));
        $this->assertTrue($this->object->delete($added));
        $this->assertEmpty($this->object->get($added));

        $added = $this->object->get($this->object->add(reset($data)));
        $this->assertTrue($this->object->delete($added['id_key'], $added['id_value']));
        $this->assertEmpty($this->object->get($added['id_key'], $added['id_value']));
    }

    /**
     * @covers gplcart\core\models\Alias::getList
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
     * @covers gplcart\core\models\Alias::getIdKeys
     */
    public function testGetIdKeys()
    {
        $result = $this->object->getIdKeys();
        $this->assertCount(2, $result);
        $this->assertTrue(in_array('page', $result) && in_array('product', $result));
    }

    /**
     * @covers gplcart\core\models\Alias::exists
     */
    public function testExists()
    {
        $this->assertTrue($this->object->exists('product.html'));
        $this->assertFalse($this->object->exists('some-fake-alias'));
    }

    /**
     * @covers gplcart\core\models\Alias::getByPath
     */
    public function testGetByPath()
    {
        $result = $this->object->getByPath('product.html');
        $this->assertInternalType('array', $result);
        $this->assertTrue($result['alias'] === 'product.html');

        $result = $this->object->getByPath('some-fake-alias');
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

}
