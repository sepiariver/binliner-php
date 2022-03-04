<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use SepiaRiver\Binliner;

final class ExamplesTest extends TestCase
{
    public function testHandleVerboseFlowTransparently(): void
    {
        $messages = [
            'verbose' => [],
            'binliner' => [],
        ];
        $doThing = function ($ns, $thing) use (&$messages) {
            $messages[$ns][] = (string)$thing;
        };

        $verboseFlow = function ($first, $second, $third, $fourth) use ($doThing) {
            $valid = null;
            $ns = 'verbose';
            if (!$first) {
                $valid = false; // first is required
                return $valid;
            }
            if (!$second && $third) {
                $valid = false; // third depends on second
                return $valid;
            }
            if ($second && !$third) {
                $valid = false; // third depends on second
                return $valid;
            }
            $valid = true;
            if ($second && $third) {
                $doThing($ns, 'second and third are both true, continue');
            }
            if (!$second && !$third) {
                $doThing($ns, 'second and third are both false, continue');
            }
            if ($fourth) {
                $doThing($ns, 'fourth is true, return foo = bar');
                return [
                    'valid' => $valid,
                    'foo' => 'bar'
                ];
            } else {
                $doThing($ns, 'fourth is false, return foo = baz');
                return [
                    'valid' => $valid,
                    'foo' => 'baz'
                ];
            }
        };
      
        $binLinerFlow = function ($first, $second, $third, $fourth) use ($doThing) {
            $valid = null;
            $ns = 'binliner';
            $bin = new Binliner([ // Represent conditions as binary stream: 1000, 1001, etc.
              'validation' => [8, 9, 14, 15] // Arbitrary validation rules
            ], $first, $second, $third, $fourth);
            $valid = $bin->isValid();
            if (!$valid) { // capture all invalid cases
                return $valid;
            }
            if ($bin->toInt() > 10) { // 1110: 14, 1111: 15
                $doThing($ns, 'second and third are both true, continue');
            } else {                // 1000: 8, 1001: 9
                $doThing($ns, 'second and third are both false, continue');
            }
            if ($bin->get(3) === 0) { // 1000: 8, 1110: 14
                $doThing($ns, 'fourth is false, return foo = baz');
                return [
                    'valid' => $valid,
                    'foo' => 'baz'
                ];
            } else {                // 1001: 9, 1111: 15
                $doThing($ns, 'fourth is true, return foo = bar');
                return [
                    'valid' => $valid,
                    'foo' => 'bar'
                ];
            }
        };
        /**
         * Truth table:
         * first | second | third | fourth | valid | foo
         * 1  | 0   | 0  | 0   | true  | baz
         * 1  | 0   | 0  | 1   | true  | bar
         * 1  | 1   | 1  | 0   | true  | baz
         * 1  | 1   | 1  | 1   | true  | bar
         * 
         * Any other conditions are invalid.
         */
        $verbose = $verboseFlow(false, true, true, true);
        $binliner = $binLinerFlow(false, true, true, true);
        $this->assertFalse($verbose);
        $this->assertFalse($binliner);
        $this->assertEquals($verbose, $binliner);
        $this->assertEquals($messages['verbose'], $messages['binliner']);
        $messages = [
            'verbose' => [],
            'binliner' => [],
        ];
        $verbose = $verboseFlow(true, true, false, true);
        $binliner = $binLinerFlow(true, true, false, true);
        $this->assertFalse($verbose);
        $this->assertFalse($binliner);
        $this->assertEquals($verbose, $binliner);
        $this->assertEquals($messages['verbose'], $messages['binliner']);
        $messages = [
            'verbose' => [],
            'binliner' => [],
        ];
        $verbose = $verboseFlow(true, false, true, true);
        $binliner = $binLinerFlow(true, false, true, true);
        $this->assertFalse($verbose);
        $this->assertFalse($binliner);
        $this->assertEquals($verbose, $binliner);
        $this->assertEquals($messages['verbose'], $messages['binliner']);
        $messages = [
            'verbose' => [],
            'binliner' => [],
        ];
        $verbose = $verboseFlow(true, true, true, true);
        $binliner = $binLinerFlow(true, true, true, true);
        $this->assertTrue($verbose['valid']);
        $this->assertTrue($binliner['valid']);
        $this->assertEquals($verbose, $binliner);
        $this->assertEquals($messages['verbose'], $messages['binliner']);
        $messages = [
            'verbose' => [],
            'binliner' => [],
        ];
        $verbose = $verboseFlow(true, false, false, false);
        $binliner = $binLinerFlow(true, false, false, false);
        $this->assertTrue($verbose['valid']);
        $this->assertTrue($binliner['valid']);
        $this->assertEquals($verbose, $binliner);
        $this->assertEquals($messages['verbose'], $messages['binliner']);
    }
}