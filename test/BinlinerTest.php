<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use SepiaRiver\Binliner;
use SepiaRiver\BinlinerException;

final class BinlinerTest extends TestCase
{
    public function testBasicValidation(): void
    {
        $bin = new Binliner(['size' => 2, 'validation' => [1, 2]]);
        $this->assertEquals('00', (string)$bin);
        $this->assertFalse($bin->isValid());
        $bin->set(0, 1);
        $this->assertEquals('10', (string)$bin);
        $this->assertTrue($bin->isValid());
        $bin->set(1, 1);
        $this->assertEquals('11', (string)$bin);
        $this->assertFalse($bin->isValid());
        // get + casting
        $this->assertEquals(1, $bin->get(1));
        $this->assertEquals('1', $bin->get(1, 'string'));
        $bin->set('0', '0');
        $this->assertEquals('01', (string)$bin);
        $this->assertTrue($bin->isValid());
    }

    public function testIsChainable()
    {
        $bin = new Binliner(['size' => 3, 'validation' => 5]);
        $this->assertEquals('000', (string)$bin);
        $this->assertFalse($bin->isValid());
        $bin->set(0, 1)->set(1, 0)->set(2, 1);
        $this->assertEquals('101', (string)$bin);
        $this->assertTrue($bin->isValid());
        $bin->set(0, 1)->set(1, 1)->set(2, 1);
        $this->assertEquals('111', (string)$bin);
        $this->assertFalse($bin->isValid());
    }

    public function testInstantiatesWithoutConfig()
    {
        $bin = new Binliner(null, 0, '', [], false, null);
        $this->assertEquals('00000', (string)$bin);
        $this->assertFalse($bin->isValid());
        $bin = new Binliner(null, 1, ' ', [''], true);
        $this->assertEquals('1111', (string)$bin);
        $this->assertTrue($bin->isValid());
    }

    public function testBasicValidationArrayTypeJuggling()
    {
        $config = ['size' => 2, 'validation' => [1, 2]];
        $bin = new Binliner($config, false, false);
        $this->assertEquals('00', (string)$bin);
        $this->assertFalse($bin->isValid());
        $bin = new Binliner($config, [false], null);
        $this->assertEquals('10', (string)$bin);
        $this->assertTrue($bin->isValid());
        $bin = new Binliner($config, 'true', 'false');
        $this->assertEquals('11', (string)$bin);
        $this->assertFalse($bin->isValid());
        $bin = new Binliner($config, 0, new stdClass());
        $this->assertEquals('01', (string)$bin);
        $this->assertTrue($bin->isValid());
    }

    public function testBasicValidationStringTypeJuggling()
    {
        $config = ['size' => 2, 'validation' => '11'];
        $bin = new Binliner($config, false, false);
        $this->assertEquals('00', (string)$bin);
        $this->assertFalse($bin->isValid());
        $bin = new Binliner($config, [false], null);
        $this->assertEquals('10', (string)$bin);
        $this->assertFalse($bin->isValid());
        $bin = new Binliner($config, 'true', 'false');
        $this->assertEquals('11', (string)$bin);
        $this->assertTrue($bin->isValid());
        $bin = new Binliner($config, 0, new stdClass());
        $this->assertEquals('01', (string)$bin);
        $this->assertFalse($bin->isValid());
    }

    public function testBasicValidationIntegerTypeJuggling()
    {
        $config = ['size' => 2, 'validation' => 1];
        $bin = new Binliner($config, false, false);
        $this->assertEquals('00', (string)$bin);
        $this->assertFalse($bin->isValid());
        $bin = new Binliner($config, [false], null);
        $this->assertEquals('10', (string)$bin);
        $this->assertFalse($bin->isValid());
        $bin = new Binliner($config, 'true', 'false');
        $this->assertEquals('11', (string)$bin);
        $this->assertFalse($bin->isValid());
        $bin = new Binliner($config, 0, new stdClass());
        $this->assertEquals('01', (string)$bin);
        $this->assertTrue($bin->isValid());
    }

    public function testBasicValidationCallableTypeJuggling()
    {
        $config = ['size' => 2, 'validation' => function ($value) {
            return intval($value, 2) > 2;
        }];
        $bin = new Binliner($config, false, false);
        $this->assertEquals('00', (string)$bin);
        $this->assertFalse($bin->isValid());
        $bin = new Binliner($config, [false], null);
        $this->assertEquals('10', (string)$bin);
        $this->assertFalse($bin->isValid());
        $bin = new Binliner($config, 'true', 'false');
        $this->assertEquals('11', (string)$bin);
        $this->assertTrue($bin->isValid());
        $bin = new Binliner($config, 0, new stdClass());
        $this->assertEquals('01', (string)$bin);
        $this->assertFalse($bin->isValid());
    }

    public function testBasicValidationMixedTypeJuggling()
    {
        $config = ['size' => 2, 'validation' => [2, '11']];
        $bin = new Binliner($config, false, false);
        $this->assertEquals('00', (string)$bin);
        $this->assertFalse($bin->isValid());
        $bin = new Binliner($config, [false], null);
        $this->assertEquals('10', (string)$bin);
        $this->assertTrue($bin->isValid()); // $bin->toInt() === 2
        $bin = new Binliner($config, 'true', 'false');
        $this->assertEquals('11', (string)$bin);
        $this->assertTrue($bin->isValid());
        $bin = new Binliner($config, 0, new stdClass());
        $this->assertEquals('01', (string)$bin);
        $this->assertFalse($bin->isValid());
    }

    public function testInstantiatesWithDifferentSize()
    {
        $config = ['size' => 5, 'validation' => '11010'];
        $bin = new Binliner($config, true, true);
        $this->assertEquals('11000', (string)$bin);
        $this->assertFalse($bin->isValid());
        $bin = new Binliner($config, [true], true, 0, '1');
        $this->assertEquals('11010', (string)$bin);
        $this->assertTrue($bin->isValid());
    }

    public function testThrowsExceptionOnInvalidSize()
    {
        $this->expectException(BinlinerException::class);
        $this->expectExceptionMessage('Too many arguments for size: 1');
        $config = ['size' => -1, 'validation' => '11010'];
        $bin = new Binliner($config, true, true);
    }

    public function testThrowsExceptionOnInvalidPosition()
    {
        $this->expectException(BinlinerException::class);
        $this->expectExceptionMessage('Illegal position: 5');
        $config = ['size' => 5, 'validation' => '11010'];
        $bin = new Binliner($config, true, true, -1);
        $bin->set(5, true);
    }

    public function testThrowsExceptionOnInvalidValidation()
    {
        $this->expectException(BinlinerException::class);
        $this->expectExceptionMessage('Invalid validation type: object');
        $config = ['size' => 5, 'validation' => new stdClass()];
        $bin = new Binliner($config);
    }
}