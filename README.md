# Laravel Transformer
Simple Eloquent and Collection transformation. Useful for ensuring that your API outputs are consistent and preventing code duplication.

## Installation

Install the package using composer:

```
composer require lukevear/laravel-transformer
```

To register the package with Laravel, add the collowing to `config/app.php`:

```php
LukeVear\LaravelTransformer\TransformerServiceProvider::class,
```

Optionally install the configuration file:

```
php artisan vendor:publish --provider="LukeVear\LaravelTransformer\TransformerServiceProvider"
```

## Creating Transformers
Creating transformers is easy. Simply extend the `AbstractTransformer` class and implement your required logic.

Transformers only require a `run` method. The `run` method is where you will implement any logic required to transform your model/collection.

For example, the below could be used to create a consistent 'User API model' for clients to consume.

**The Transformer**
```php
<?php

namespace App\Transformers;

use App\User;
use LukeVear\LaravelTransformer\AbstractTransformer;

class UserTransformer extends AbstractTransformer
{
        /**
         * Transform the supplied data.
         *
         * @param User $model
         * @return array
         */
        public function run($model)
        {
            return [
                'id'    => $model->id,
                'name'  => $model->first_name . ' ' . $model->last_name
            ];
        }
}
```

**Usage**

```php
return response()->json(
    transform($user, new UserTransformer)
);
```

## Automatic Transformation
You can optionally specify which transformer to use for each Model in your codebase, and specify which 'group' of transformers to use.

To setup automatic transformation, edit `config/laravel-transformer.php` (ensure you ran the vendor:publish command above).

For example, if you have a `v1` and `v2` API, you may have the following in your configuration file:

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Model <-> Transformer Binding Groups
    |--------------------------------------------------------------------------
    |
    | This allows you to specify which transformers should automatically be
    | used when the transform() function is provided a Eloquent model.
    |
    */
    'groups' => [
        'default' => [
            // App\User::class => App\Transformers\UserTransformer::class,
        ],
        'v1' => [
            App\Models\User::class                  => App\Transformers\v1\UserTransformer::class,
        ],
        'v2' => [
            App\Models\User::class                  => App\Transformers\v2\UserTransformer::class,
        ],
    ],

];
```

In your global middleware you can then specify which transformation group to use depending on the API version being consumed:

```php
public function handle($request, Closure $next)
{
    if ($request->is('v1*')) {
        TransformerEngine::setGroup('v1');
    } else {
        TransformerEngine::setGroup('v2');
    }

    return $next($request);
}
```

You can then transform your User model in any controller using the following:

```php
return response()->json(
    transform($user)
);
```

## Inclusions
When manually creating a transformer you can optionally supply additional 'includes' that provide context to the transformer.

For example, you may wish to include the user's settings model alongside the user model. To achieve this you could implement something like this:
 
 ```php
 <?php
 
 namespace App\Transformers;
 
 use App\User;
 use LukeVear\LaravelTransformer\AbstractTransformer;
 
 class UserTransformer extends AbstractTransformer
 {
         /**
          * Transform the supplied data.
          *
          * @param User $model
          * @return array
          */
         public function run($model)
         {
             $response = [
                 'id'    => $model->id,
                 'name'  => $model->first_name . ' ' . $model->last_name
             ];
             
             if ($this->hasInclude('settings')) {
                 $response['settings'] = transform($model->settings, new UserSettingsTransformer);
             }
             
             return $response;
         }
 }
 ```
 
To tell the transformer that you wish to include settings, you'd use the following in your route/controller:
 
```php
return response()->json(
    transform($user, (new UserTransformer)->setIncludes(['settings'])
);
```