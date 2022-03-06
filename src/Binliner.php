<?php

namespace SepiaRiver;

use SepiaRiver\BinlinerException;
use SepiaRiver\Validation;

interface Stringable {
    public function __toString(): string;
}

class Binliner implements Stringable
{
    protected $size = 0;
    protected $value = '';
    protected $validation = null;

    public function __construct($config = null, ...$args)
    {
        $this->size = count($args);
        $this->validation = new Validation(str_pad('', $this->size, '1'));
        if (is_array($config)) {
            // Size
            if (isset($config['size']) && is_int($config['size'])) {
                $this->size = abs($config['size']);
                if (count($args) > $this->size) {
                    throw new BinlinerException('Too many arguments for size: ' . $this->size);
                }
            }
            // Validation
            if (isset($config['validation'])) {
                $this->validation = new Validation($config['validation']);
            }
        }
        foreach ($args as $arg) {
            $this->value .= !($arg) ? '0' : '1';
        }
        $this->value = str_pad($this->value, $this->size, '0', STR_PAD_RIGHT);
    }

    public function juggle($input, string $type)
    {
        return $this->validation->juggle($input, $type);
    }

    public function __toString(): string
    {
        return $this->juggle($this->value, 'string');
    }

    public function toInt(): int
    {
        return $this->juggle($this->value, 'integer');
    }

    public function set($pos, $value)
    {
        $pos = abs($pos);
        if ($pos > ($this->size - 1)) {
            throw new BinlinerException("Illegal position: {$pos}");
        }
        $this->value[$pos] = !($value) ? '0' : '1';
        return $this;
    }

    public function get($pos, $type = 'integer')
    {
        $pos = abs($pos);
        if ($pos > ($this->size - 1)) {
            throw new BinlinerException("Illegal position: {$pos}");
        }
        return $this->juggle($this->value[$pos], $type);
    }

    public function isValid(): bool
    {
        return $this->validation->isValid($this->value);
    }
}