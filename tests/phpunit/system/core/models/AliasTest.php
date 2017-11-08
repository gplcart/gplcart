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
        $this->fixture_data = $this->getFixtureData('alias');

        parent::setUp();
    }

    /**
     * @covers gplcart\core\models\Alias::add
     */
    public function testAdd()
    {
        $data = $this->fixture_data;
        $this->removeFixtureAutoincrementField($data);
        $first = reset($data);

        $added_id = $this->object->add($first);
        $this->assertInternalType('int', $added_id);
        $this->assertDbRecordExists('alias', 'alias_id', $added_id);
    }

    /**
     * @covers gplcart\core\models\Alias::get
     */
    public function testGet()
    {
        $first = reset($this->fixture_data);
        $result = $this->object->get($first['alias_id']);
        $this->assertEquals($first['alias_id'], $result['alias_id']);

        $result = $this->object->get($first['alias_id'], 'product');
        $this->assertEquals('product', $result['id_key']);

        // Get by fake arguments
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
        list($first, $second) = $this->fixture_data;

        $this->assertDbRecordExists('alias', 'alias_id', $first['alias_id']);
        $this->assertTrue($this->object->delete($first['alias_id']));
        $this->assertDbRecordNotExists('alias', 'alias_id', $first['alias_id']);

        $this->assertDbRecordExists('alias', 'alias_id', $second['alias_id']);
        $this->assertTrue($this->object->delete($second['id_key'], $second['id_value']));
        $this->assertDbRecordNotExists('alias', 'alias_id', $second['alias_id']);
    }

    /**
     * @covers gplcart\core\models\Alias::getList
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
     * @covers gplcart\core\models\Alias::getIdKeys
     */
    public function testGetIdKeys()
    {
        $result = $this->object->getIdKeys();

        $this->assertCount(count($this->fixture_data), $result);
        $this->assertContains('page', $result);
        $this->assertContains('product', $result);
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
        $this->assertEquals('product.html', $result['alias']);

        $result = $this->object->getByPath('some-fake-alias');
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

}
