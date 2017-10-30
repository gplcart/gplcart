<?php

namespace gplcart\core\handlers\validator\elements;

use gplcart\tests\phpunit\support\PhpUnitTest;

/**
 * @coversDefaultClass gplcart\core\handlers\validator\elements\Common
 */
class CommonTest extends PhpUnitTest
{

    /**
     * Object class instance
     * @var \gplcart\core\handlers\validator\elements\Common
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = $this->getInstance('gplcart\\core\\handlers\\validator\\elements\\Common');
    }

    /**
     * @covers gplcart\core\handlers\validator\elements\Common::required
     */
    public function testRequired()
    {
        $submitted = array();
        $options = array('field' => 'test_field', 'label' => 'Test');

        // Fails
        $result = $this->object->required($submitted, $options);
        $this->assertArrayHasKey('test_field', $result);

        // Passes
        $submitted['test_field'] = 'test_value';
        $this->assertTrue($this->object->required($submitted, $options));
    }

    /**
     * @covers gplcart\core\handlers\validator\elements\Common::numeric
     */
    public function testNumeric()
    {
        $options = array('field' => 'test_field', 'label' => 'Test');

        // Fails
        $submitted = array();
        $this->assertArrayHasKey('test_field', $this->object->numeric($submitted, $options));

        $submitted['test_field'] = 'test_value';
        $this->assertArrayHasKey('test_field', $this->object->numeric($submitted, $options));

        $submitted['test_field'] = '1.1a';
        $this->assertArrayHasKey('test_field', $this->object->numeric($submitted, $options));

        // Passes
        $submitted['test_field'] = '1.1';
        $this->assertTrue($this->object->numeric($submitted, $options));

        $submitted['test_field'] = '0';
        $this->assertTrue($this->object->numeric($submitted, $options));

        $submitted['test_field'] = 0;
        $this->assertTrue($this->object->numeric($submitted, $options));

        $submitted['test_field'] = 1.234;
        $this->assertTrue($this->object->numeric($submitted, $options));
    }

    /**
     * @covers gplcart\core\handlers\validator\elements\Common::integer
     */
    public function testInteger()
    {
        $options = array('field' => 'test_field', 'label' => 'Test');

        // Fails
        $submitted = array();
        $this->assertArrayHasKey('test_field', $this->object->integer($submitted, $options));

        $submitted['test_field'] = 'test_value';
        $this->assertArrayHasKey('test_field', $this->object->integer($submitted, $options));

        $submitted['test_field'] = '1.1';
        $this->assertArrayHasKey('test_field', $this->object->integer($submitted, $options));

        $submitted['test_field'] = 1.1;
        $this->assertArrayHasKey('test_field', $this->object->integer($submitted, $options));

        // Passes
        $submitted['test_field'] = '0';
        $this->assertTrue($this->object->integer($submitted, $options));

        $submitted['test_field'] = 123;
        $this->assertTrue($this->object->integer($submitted, $options));

        $submitted['test_field'] = 0;
        $this->assertTrue($this->object->integer($submitted, $options));
    }

    /**
     * @covers gplcart\core\handlers\validator\elements\Common::length
     */
    public function testLength()
    {
        $options = array('field' => 'test_field', 'label' => 'Test');

        // Test default values
        // Fails
        $submitted = array();
        $this->assertArrayHasKey('test_field', $this->object->length($submitted, $options));

        $submitted['test_field'] = $this->getRandomString(256);
        $this->assertArrayHasKey('test_field', $this->object->length($submitted, $options));

        $submitted['test_field'] = '';
        $this->assertArrayHasKey('test_field', $this->object->length($submitted, $options));

        // Set limits 10 - 50 characters
        $options['arguments'] = array(
            10, // Min 10 chars
            50 // Max 50 chars
        );

        $submitted['test_field'] = $this->getRandomString(51);
        $this->assertArrayHasKey('test_field', $this->object->length($submitted, $options));

        $submitted['test_field'] = $this->getRandomString(9);
        $this->assertArrayHasKey('test_field', $this->object->length($submitted, $options));

        // Passes
        $submitted['test_field'] = $this->getRandomString(50);
        $this->assertTrue($this->object->length($submitted, $options));

        $submitted['test_field'] = $this->getRandomString(10);
        $this->assertTrue($this->object->length($submitted, $options));
    }

    /**
     * @covers gplcart\core\handlers\validator\elements\Common::regexp
     * @todo   Implement testRegexp().
     */
    public function testRegexp()
    {
        $field = 'test_field';
        $options = array('field' => $field, 'label' => 'Test');

        // Fails
        $submitted = array();
        $this->assertArrayHasKey($field, $this->object->regexp($submitted, $options));

        $submitted[$field] = '123 abs';
        $options['arguments'] = array('/^[\w]+$/');
        $this->assertArrayHasKey($field, $this->object->regexp($submitted, $options));

        // Passes
        $submitted[$field] = '123_abs';
        $this->assertTrue($this->object->regexp($submitted, $options));
    }

    /**
     * @covers gplcart\core\handlers\validator\elements\Common::dateformat
     */
    public function testDateformat()
    {
        $field = 'test_field';
        $options = array('field' => $field, 'label' => 'Test');

        // Fails
        $submitted = array();
        $this->assertArrayHasKey($field, $this->object->dateformat($submitted, $options));

        $submitted[$field] = '5/ 12';
        $this->assertArrayHasKey($field, $this->object->dateformat($submitted, $options));

        $submitted[$field] = 'Mar ch';
        $this->assertArrayHasKey($field, $this->object->dateformat($submitted, $options));

        $submitted[$field] = '32-June 2008';
        $this->assertArrayHasKey($field, $this->object->dateformat($submitted, $options));

        // Passes
        $submitted[$field] = 'now';
        $this->assertTrue($this->object->dateformat($submitted, $options));

        $submitted[$field] = '30-June 2008';
        $this->assertTrue($this->object->dateformat($submitted, $options));

        $submitted[$field] = '12/22/78';
        $this->assertTrue($this->object->dateformat($submitted, $options));
    }

    /**
     * @covers gplcart\core\handlers\validator\elements\Common::json
     */
    public function testJson()
    {
        $field = 'test_field';
        $options = array('field' => $field, 'label' => 'Test');

        // Fails
        $submitted = array();
        $this->assertArrayHasKey($field, $this->object->json($submitted, $options));

        $submitted[$field] = '{test';
        $this->assertArrayHasKey($field, $this->object->json($submitted, $options));

        // Passes
        $submitted[$field] = "{}";
        $this->assertTrue($this->object->json($submitted, $options));

        $submitted[$field] = "[]";
        $this->assertTrue($this->object->json($submitted, $options));
    }

}
