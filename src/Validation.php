<?php

namespace SepiaRiver;

use SepiaRiver\BinlinerException;

class Validation
{
    public function __construct($validation)
    {
        if (
            is_callable($validation) ||
            in_array(
                gettype($validation),
                ['string', 'integer', 'array']
            )
        ) {
            $this->validation = $validation;
        } else {         
            throw new BinlinerException('Invalid validation type: ' . gettype($validation));
        }
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

    public function isValid($input): bool
    {
        if (is_callable($this->validation)) {
            return call_user_func($this->validation, $input);
        }
        $type = gettype($this->validation);
        switch($type) {
            case 'string':
            case 'integer':
                return ($this->validation === $this->juggle($input, $type));
            case 'array':
                foreach ($this->validation as $valid) {
                    if ($valid === $this->juggle($input, gettype($valid))) {
                        return true;
                    }
                } // Intentional fallthrough
            default:
                return false;
        }
    }
}