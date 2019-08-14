<?php

namespace Zschuessler\ModelJsonAttributeGuard;

use Zschuessler\ModelJsonAttributeGuard\Exceptions\JsonAttributeValidationFailedException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class JsonAttributeGuard
{
    /**
     * Model
     * 
     * The model this class will be bound to.
     * 
     * @var Model
     */
    protected $model;

    /**
     * Attribute
     * 
     * The model attribute this class will be bound to.
     * 
     * @var string
     */
    protected $attribute;

    /**
     * Schema 
     * 
     * A read-only attribute useful for when troubleshooting.
     * When needing to obtain the schema, the schema() method should be called instead.
     * 
     * @var array
     */
    protected $schema;

    /**
     * JsonColumnGuard constructor
     * 
     * @param Model $model
     * @param string $attribute
     */
    public function __construct(Model $model, $attribute)
    {
        $this->model     = $model;
        $this->attribute = $attribute;
        $this->schema    = $this->schema();
    }

    /**
     * Validate
     * 
     * Validates the given value according to the schema rules set.
     * 
     * @param $value
     * @return bool
     * @throws \Exception
     */
    public function validate($value)
    {
        // Cast any child objects to an array first, to be compatible with Laravel's Validator
        $validatorData = json_decode(json_encode($value), true);

        $validator = Validator::make(
            $validatorData,
            $this->schema()
        );

        // @todo: add custom exception to catch here
        if ($validator->fails()) {
            throw new JsonAttributeValidationFailedException(
                'Validation failed for json column: ' . $validator->errors()->first()
            );
        }

        return !$validator->fails();
    }

    /**
     * Get Value
     * 
     * Gets the value from the Model instance itself.
     * This method follows any mutators set.
     * 
     * @return mixed
     */
    public function getValue()
    {
        return $this->model->getAttribute(
            $this->getAttribute()
        );
    }

    /**
     * Get Value JSON
     * 
     * Calls getValue(), but casts to a JSON string if applicable.
     * 
     * @return false|mixed|string
     */
    public function getValueJson()
    {
        $currentValue = $this->getValue();

        if (!is_string($currentValue)) {
            return json_encode($currentValue);
        }

        return $currentValue;
    }

    /**
     * Schema
     * 
     * A key/value array of Laravel Validator rules.
     * Extend this method to set expectations for your JSON column.
     * 
     * Example:
     * 
     * return [
     *     // Column must have a top-most `name` attribute
     *     'name' => 'required', 
     * 
     *     // Column must have child objects with the `name` attribute
     *     '*.name' => 'required'
     * ];
     * 
     * @see https://laravel.com/docs/master/validation
     * 
     * @return array
     */
    public function schema() : array
    {
        return [];
    }

    /**
     * Get Attribute
     * 
     * Returns the attribute this class was bound to.
     * 
     * @return string
     */
    public function getAttribute() : string
    {
        return $this->attribute;
    }

    /**
     * Cast Attribute
     * 
     * Casts the given $value from a JSON string to a JSON decoded value, if applicable.
     * This method follows the same definition of the Illuminate model method of the same name:
     * Model::castAttribute()
     *
     * @param $attribute string
     * @param $value mixed
     * @return mixed
     */
    public function castAttribute($attribute, $value)
    {
        // Decode the database column automatically if it isn't already, as a matter of convenience
        if (is_string($value)) {
            return json_decode($value);
        }

        return $value;
    }

}
