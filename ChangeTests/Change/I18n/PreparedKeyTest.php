<?php
namespace ChangeTests\Change\I18n;

class PreparedKeyTest extends \PHPUnit_Framework_TestCase
{
	public function testConstructor()
	{
		$key0 = new \Change\I18n\PreparedKey('');
		$this->assertEquals('', $key0->getKey());
		$this->assertEquals(array(), $key0->getFormatters());
		$this->assertEquals(array(), $key0->getReplacements());

		$key1 = new \Change\I18n\PreparedKey('m.website.fo.test');
		$this->assertEquals('m.website.fo.test', $key1->getKey());
		$this->assertEquals(array(), $key1->getFormatters());
		$this->assertEquals(array(), $key1->getReplacements());

		$key2 = new \Change\I18n\PreparedKey('m.website.fo.test-params', array('ucf'), array('param1' => 'Value 1',
			'param2' => 'Value 2'));
		$this->assertEquals('m.website.fo.test-params', $key2->getKey());
		$this->assertEquals(array('ucf'), $key2->getFormatters());
		$this->assertEquals(array('param1' => 'Value 1', 'param2' => 'Value 2'), $key2->getReplacements());
	}

	// Methods on key.

	/**
	 * Tests the following methods:
	 * - getKey
	 * - setKey
	 * - __toString
	 * @depends testConstructor
	 */
	public function testGetSetKey()
	{
		$key = new \Change\I18n\PreparedKey('');
		$key->setKey('c.date.default-format');
		$this->assertEquals('c.date.default-format', $key->getKey());
		$this->assertEquals('c.date.default-format', strval($key));

		$key->setKey('m.website.fo.test');
		$this->assertEquals('m.website.fo.test', $key->getKey());
		$this->assertEquals('m.website.fo.test', strval($key));

		$key->setKey('t.default.templates.tpl1');
		$this->assertEquals('t.default.templates.tpl1', $key->getKey());
		$this->assertEquals('t.default.templates.tpl1', strval($key));

		// Invalid keys are not modified.
		$key->setKey(' c.date.Default-Format');
		$this->assertEquals(' c.date.Default-Format', $key->getKey());
		$this->assertEquals(' c.date.Default-Format', strval($key));

		$key->setKey('C\'est l\'ÉTÉ. Ça va chauffer !');
		$this->assertEquals('C\'est l\'ÉTÉ. Ça va chauffer !', $key->getKey());
		$this->assertEquals('C\'est l\'ÉTÉ. Ça va chauffer !', strval($key));
	}

	/**
	 * @depends testGetSetKey
	 */
	public function testIsValid()
	{
		$key = new \Change\I18n\PreparedKey('');

		// Minimum 4 parts.
		$key->setKey('m');
		$this->assertFalse($key->isValid());
		$key->setKey('m.website');
		$this->assertFalse($key->isValid());
		$key->setKey('m.website.test');
		$this->assertFalse($key->isValid());
		$key->setKey('m.website.fo.test');
		$this->assertFalse($key->isValid());
		$key->setKey('m.website.fo.test.titi');
		$this->assertTrue($key->isValid());


		// For the first path, only 'c', 'm' or 't' are valid.
		$key->setKey('toto.website.test');
		$this->assertFalse($key->isValid());
		$key->setKey('t.website.test.tutu.titi');
		$this->assertTrue($key->isValid());
		$key->setKey('c.website.test');
		$this->assertTrue($key->isValid());
		$key->setKey('v.website.test');
		$this->assertFalse($key->isValid());
	}

	/**
	 * @depends testIsValid
	 */
	public function testGetPathGetId()
	{
		$key = new \Change\I18n\PreparedKey('m.website.fo.test.tutu');
		$this->assertEquals('m.website.fo.test.tutu', $key->getKey());
		$this->assertTrue($key->isValid());
		$this->assertEquals('m.website.fo.test', $key->getPath());
		$this->assertEquals('tutu', $key->getId());

		$key->setKey('t.default.templates.a.tpl1');
		$this->assertEquals('t.default.templates.a.tpl1', $key->getKey());
		$this->assertTrue($key->isValid());
		$this->assertEquals('t.default.templates.a', $key->getPath());
		$this->assertEquals('tpl1', $key->getId());

		$key->setKey('t.default');
		$this->assertEquals('t.default', $key->getKey());
		$this->assertFalse($key->isValid());
		$this->assertNull($key->getPath());
		$this->assertNull($key->getId());
	}

	// Methods on transformers.

	/**
	 * Tests the following methods:
	 * - getFormatters
	 * - setFormatters
	 * - hasFormatters
	 * @depends testConstructor
	 */
	public function testGetSetFormatters()
	{
		$key = new \Change\I18n\PreparedKey('m.website.fo.test');
		$this->assertFalse($key->hasFormatters());
		$this->assertEquals(array(), $key->getFormatters());

		$key->setFormatters(array('ucf', 'js'));
		$this->assertTrue($key->hasFormatters());
		$this->assertEquals(array('ucf', 'js'), $key->getFormatters());

		$key->setFormatters(array('html'));
		$this->assertTrue($key->hasFormatters());
		$this->assertEquals(array('html'), $key->getFormatters());
	}

	/**
	 * @depends testGetSetFormatters
	 */
	public function testAddFormatter()
	{
		$key = new \Change\I18n\PreparedKey('m.website.fo.test', array('html'));
		$this->assertEquals(array('html'), $key->getFormatters());
		$key->addFormatter('ucf');
		$this->assertEquals(array('html', 'ucf'), $key->getFormatters());
		$key->addFormatter('lab');
		$this->assertEquals(array('html', 'ucf', 'lab'), $key->getFormatters());
		$key->addFormatter('html');
		$key->addFormatter('ucf');
		$this->assertEquals(array('html', 'ucf', 'lab'), $key->getFormatters());
	}

	/**
	 * @depends testGetSetFormatters
	 */
	public function testMergeFormatters()
	{
		$key = new \Change\I18n\PreparedKey('m.website.fo.test', array('html'));
		$this->assertEquals(array('html'), $key->getFormatters());

		$key->mergeFormatters(array('ucf', 'lab'));
		$formatters = $key->getFormatters();
		sort($formatters); // Merge do not preserve order.
		$this->assertEquals(array('html', 'lab', 'ucf'), $formatters);

		$key->mergeFormatters(array('html', 'js'));
		$formatters = $key->getFormatters();
		sort($formatters);
		$this->assertEquals(array('html', 'js', 'lab', 'ucf'), $formatters);
	}

	// Methods on replacements.

	/**
	 * Tests the following methods:
	 * - getReplacements
	 * - setReplacements
	 * - hasReplacements
	 * @depends testConstructor
	 */
	public function testGetSetReplacements()
	{
		$key = new \Change\I18n\PreparedKey('m.website.fo.test');
		$this->assertFalse($key->hasReplacements());
		$this->assertEquals(array(), $key->getReplacements());

		$key->setReplacements(array('param1' => 'Value 1', 'param2' => 'Value 2'));
		$this->assertTrue($key->hasReplacements());
		$this->assertEquals(array('param1' => 'Value 1', 'param2' => 'Value 2'), $key->getReplacements());

		$key->setReplacements(array('toto' => 'titi'));
		$this->assertTrue($key->hasReplacements());
		$this->assertEquals(array('toto' => 'titi'), $key->getReplacements());
	}

	/**
	 * @depends testGetSetReplacements
	 */
	public function testSetReplacements()
	{
		$key = new \Change\I18n\PreparedKey('m.website.fo.test', array('html'), array('param1' => 'Value 1',
			'param2' => 'Value 2'));
		$this->assertEquals(array('param1' => 'Value 1', 'param2' => 'Value 2'), $key->getReplacements());
		$key->setReplacement('test1', 'test1value');
		$this->assertEquals(array('param1' => 'Value 1', 'param2' => 'Value 2', 'test1' => 'test1value'),
			$key->getReplacements());
		$key->setReplacement('cms', 'RBS Change');
		$this->assertEquals(array('param1' => 'Value 1', 'param2' => 'Value 2', 'test1' => 'test1value', 'cms' => 'RBS Change'),
			$key->getReplacements());
		$key->setReplacement('cms', 'RBS Change');
		$this->assertEquals(array('param1' => 'Value 1', 'param2' => 'Value 2', 'test1' => 'test1value', 'cms' => 'RBS Change'),
			$key->getReplacements());
		$key->setReplacement('test1', 'une autre valeur');
		$this->assertEquals(array('param1' => 'Value 1', 'param2' => 'Value 2', 'test1' => 'une autre valeur',
			'cms' => 'RBS Change'), $key->getReplacements());
	}

	/**
	 * @depends testGetSetReplacements
	 */
	public function testMergeReplacements()
	{
		$key = new \Change\I18n\PreparedKey('m.website.fo.test', array('html'), array('param1' => 'Value 1',
			'param2' => 'Value 2'));
		$this->assertEquals(array('param1' => 'Value 1', 'param2' => 'Value 2'), $key->getReplacements());

		$key->mergeReplacements(array('cms' => 'RBS Change 3.6', 'browser' => 'Firefox'));
		$replacements = $key->getReplacements();
		ksort($replacements); // Merge preserves keys but not order.
		$this->assertEquals(array('browser' => 'Firefox', 'cms' => 'RBS Change 3.6', 'param1' => 'Value 1',
			'param2' => 'Value 2'), $replacements);

		$key->mergeReplacements(array('cms' => 'RBS Change 4.0', 'browser' => 'Firefox', 'os' => 'windows'));
		$replacements = $key->getReplacements();
		ksort($replacements); // Merge preserves keys but not order.
		$this->assertEquals(array('browser' => 'Firefox', 'cms' => 'RBS Change 4.0', 'os' => 'windows', 'param1' => 'Value 1',
			'param2' => 'Value 2'), $replacements);
	}
}