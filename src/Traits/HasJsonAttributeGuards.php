<?php

namespace Zschuessler\ModelJsonAttributeGuard\Traits;

use Zschuessler\ModelJsonAttributeGuard\JsonAttributeGuard;
use Illuminate\Support\Str;

trait HasJsonAttributeGuards
{
    /**
     * JSON Attribute Guards
     *
     * A key/value array of all the found JsonAttributeGuard attributes.
     * You shouldn't access this directly, instead use the $casts property on your model for casting:
     *
     * public $casts = [
     *     'contact' => MyContactJsonColumnClass::class
     * ];
     *
     * @var array
     */
    protected $jsonAttributeGuards = [];

    /**
     * Boot: Has JSON Attribute Guards
     *
     * A Laravel magic method to automatically register an \Event listener when the model boots.
     * Specifically, when the model is booted we determine which of the `$casts` options are
     * custom JsonAttributeGuard classes, then register events for them.
     */
    public static function bootHasJsonAttributeGuards()
    {
        \Event::listen('eloquent.*: ' . get_called_class(), function ($event, $data) {
            // Parse event name
            $eventName = explode('.', explode(':', $event)[0])[1];

            /** @var self $model */
            $model = $data[0];

            // Call event for custom guard
            foreach ($model->getJsonAttributeGuards() as $attribute => $customCastClass) {
                $customGuard = $model->getJsonAttributeGuard($attribute);

                if (method_exists($customGuard, $eventName)) {
                    $customGuard->$eventName();
                }
            }
        });
    }

    /**
     * Set Attribute
     *
     * Extends the default Laravel Model's method to allow for custom casts to run.
     * Order of precedence in mutation is:
     *
     * 1. A model which has the 'setMyXAttribute` method will take first order of priority
     * 2. Any registered JsonAttributeGuard casts will be considered next
     * 3. If neither exist, follow default rules for a Laravel Model
     *
     * @param $attribute string
     * @param $value mixed
     * @return $this
     * @throws \Exception
     */
    public function setAttribute($attribute, $value)
    {
        // 1. Give a Model mutator method first priority
        if ($this->hasSetMutator($attribute)) {
            $methodName = sprintf('set%sAttribute', Str::studly($attribute) . 'Attribute');

            return $this->{$methodName}($value);
        }

        // 2. Any guards for the attribute get second priority
        if (array_key_exists($attribute, $this->getJsonAttributeGuards())) {
            /** @var $customGuard  JsonAttributeGuard */
            $customGuard = $this->getJsonAttributeGuard($attribute);
            $customGuard->validate($value);

            // A final decode/encode will strip whitespace and minify the json
            $cleanedValue = json_encode(json_decode($value));
            $this->attributes[$attribute] = $cleanedValue;

            return $this;
        }

        // Nothing special found, default to the parent Model method
        return parent::setAttribute($attribute, $value);
    }

    /**
     * Cast Attribute
     *
     * Extends the Laravel method for casting an attribute. Specifically for JSON attributes, as convenience
     * this method will export a JSON string value to an object. This functionality can be customized
     * on the JsonAttributeGuard class (see method of the same name)
     *
     * @param $attribute string
     * @param $value mixed
     * @return mixed
     */
    protected function castAttribute($attribute, $value)
    {
        // Cast JsonAttributeGuard values to objects/arrays automatically when called
        if (array_key_exists($attribute, $this->getJsonAttributeGuards())) {
            /** @var $customGuard JsonAttributeGuard */
            $customGuard = $this->getJsonAttributeGuard($attribute);

            return $customGuard->castAttribute($attribute, $value);
        }

        return parent::castAttribute($attribute, $value);
    }

    /**
     * Get JSON Attribute Guard
     *
     * Gets the registered JsonAttributeGuard for the given Model attribute.
     *
     * @param $attribute string
     * @return mixed
     */
    public function getJsonAttributeGuard($attribute)
    {
        $customGuardClass = $this->jsonAttributeGuards[$attribute];
        $customGuard = new $customGuardClass($this, $attribute);

        return $customGuard;
    }

    /**
     * Get JSON Attribute Guards
     *
     * Searches the Model `$casts` array for any attributes that are a subclass of JsonAttributeGuard.
     * Those that are found are placed into the read-only `jsonAttributeGuards` property this Trait introduces.
     *
     * @return array
     */
    public function getJsonAttributeGuards()
    {
        $customCasts = [];

        foreach ($this->casts as $attribute => $castClass) {
            if (is_subclass_of($castClass, JsonAttributeGuard::class)) {
                $customCasts[$attribute] = $castClass;
            }
        }
        $this->jsonAttributeGuards = $customCasts;

        return $customCasts;
    }
}
