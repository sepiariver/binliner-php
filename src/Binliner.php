<?php

namespace SepiaRiver;

use Exception\BinlinerException;
use Stringable;

class Binliner implements Stringable
{
    protected $size = 0;
    protected $value = '';
    protected $validation = '';

    public function __construct($config = null, ...$args)
    {
        $this->size = count($args);
        $this->validation = str_pad('', $this->size, '1');
        if (is_array($config)) {
            // Size
            if (isset($config['size']) && is_int($config['size'])) {
                $this->size = abs($config['size']);
                if (count($args) > $this->size) {
                    throw new BinlinerException('Too many arguments for size: ' . $this->size);
                }
            }
            // Validation
            if (
                isset($config['validation']) &&
                (
                    is_callable($config['validation']) ||
                    in_array(
                        gettype($config['validation']),
                        ['string', 'integer', 'array']
                    )
                )
            ) {
                $this->validation = $config['validation'];
            }
        }
        foreach ($args as $arg) {
            $this->value .= !($arg) ? '0' : '1';
        }
        $this->value = str_pad($this->value, $this->size, '0', STR_PAD_RIGHT);
    }

    public function juggle($input, string $type)
    {
        switch($type) {
            case 'integer':
                return intval($input, 2);
            case 'string':
            default:
                return (string)$input;
        }
    }

    public function __toString(): string
    {
        return $this->juggle($this->value, 'string');
    }

    public function intval(): int
    {
        return $this->juggle($this->value, 'integer');
    }

    public function set($pos, $value)
    {
        $pos = abs($pos);
        if ($pos > ($this->size - 1)) {
          throw new BinlinerException('Illegal position: ' . $pos);
        }
        $sequence = str_split($this->value);
        if (is_array($sequence)) {
            $sequence[$pos] = !($value) ? '0' : '1';
        }
        $this->value = implode('', $sequence);
        return $this;
      }
}