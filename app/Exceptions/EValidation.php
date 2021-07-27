<?php

namespace App\Exceptions;

class EValidation extends EBase
{
    public static $custom_message = 'Alguns campos precisam ser verificados. (%s)';

    public $errors;

    /**
     */
    public static function throw(...$args)
    {
        $validator = $args[0];
        $labels = $args[1] ?? [];

        $fields = [];
        foreach ($validator->errors()->keys() as $field) {
            $fields[] = $labels[$field] ?? $field;
        }

        $e = new static(vsprintf(static::$custom_message, [implode(', ', $fields)]));
        $e->errors = $validator->errors();
        throw $e;
        // parent::throw(implode(', ', $fields));
    }

    public function errors()
    {
        return $this->errors;
    }
}
