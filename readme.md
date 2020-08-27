
# JSON Column Guard

This package allows validation and custom casts for JSON columns.

Have you ever tried to use a json column, and:

1. Validating schema of the column was difficult or messy
2. Querying the column was onerous
3. Casting types when saving models wasn't fun

Well now you can rest easy!

## Example

You want to save user preferences. It's highly dynamic data, so you throw it into a json column:


<table>
<tr>
    <th>Database Table</th>
    <th>Database Column</th>
    <th>Desired Schema</th>
</tr>
<tr>
<td>users</td>
<td>preferences</td>
<td>
<pre lang="json">
[
  {
    "name": "Favorite Band",
    "value": "Slenderbodies",
    "date_created": "2019-09-15",
    "date_updated": "2020-01-01"
    }
]
</pre>
</td>
</tr>
</table>

Here are problems:

1. Can you enforce each key is valid?
2. Can you enforce the two dates are valid dates?
3. What about always ensuring the column is an array, regardless if a preference exists or not?

_We can._

## Step 1: Create The Validator Class

Let's use Laravel's own Validator syntax to describe what we want.

```
<?php
namespace App\Models\User;

use Zschuessler\ModelJsonAttributeGuard\JsonAttributeGuard;

class PreferencesJsonColumn extends JsonAttributeGuard
{

    public function schema() : array
    {
        return [
            // Use wildcard syntax to apply rules to all children
            '*.name'           => 'required',
            '*.value'          => 'required|min:3',
            '*.date_created'   => 'present|nullable|date',
            '*.date_updated'   => 'present|nullable|date',
        ];
    }
}

```

Under the hood [Laravel's Validators](https://laravel.com/docs/master/validation#available-validation-rules) are used: 
you can use _any_ rule you want. Go nuts.

## Step 2: Add a trait and cast to your Model

Let's tell Laravel we want our model to do a custom cast:

```
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Congress\SessionsJsonColumn;
use Zschuessler\ModelJsonAttributeGuard\Traits\HasJsonAttributeGuards;

class User extends Model
{
    use HasJsonAttributeGuards;

    public $casts = [
        'sessions' => SessionsJsonColumn::class
    ];

```

## Step 3: Success

This code will work wonderfully:

```
$user->preferences = [
    [
        'name' => 'Favorite Band',
        'value' => 'Slenderbodies',
        'date_created' => '2019-09-15',
        'date_updated' => '2020-01-01'
    ]
];
$user->save();
```

This code will throw a `JsonAttributeValidationFailedException` exception - oh no! Your model won't be saved.

```
$user->preferences = [
    'name' => null,
    'value' => null,
    'date_created' => null,
    'date_updated' => null,
];
$user->save();
```

This was a simple example. The possibilities are endless!
