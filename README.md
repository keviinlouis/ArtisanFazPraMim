# Laravel 5 crud-generator-admin-lte
CRUD Generator for AdminLTE


[![Total Downloads](https://poser.pugx.org/goszowski/crud-generator-admin-lte/d/total.svg)](https://packagist.org/packages/goszowski/crud-generator-admin-lte)
[![Latest Stable Version](https://poser.pugx.org/goszowski/crud-generator-admin-lte/v/stable.svg)](https://packagist.org/packages/goszowski/crud-generator-admin-lte)
[![Latest Unstable Version](https://poser.pugx.org/goszowski/crud-generator-admin-lte/v/unstable.svg)](https://packagist.org/packages/goszowski/crud-generator-admin-lte)
[![License](https://poser.pugx.org/goszowski/crud-generator-admin-lte/license.svg)](https://packagist.org/packages/goszowski/crud-generator-admin-lte)

### Requirements
    Laravel >=5.1
    PHP >= 5.5.9

## Installation

1. Edit your project's composer.json file to require "goszowski/crud-generator-admin-lte"

    ```
    "require": {
      "goszowski/crud-generator-admin-lte": "dev-master",
    }
    ```
2. Run 
    ```
    composer update
    ```
3. Add service provider to **/config/app.php** file.
    ```php
    'providers' => [
        ...

        App\Generator\CrudGeneratorAdminLteServiceProvider::class,
    ],
    ```
4. Install **laravelcollective/html** package for form & html.
    * Run

    ```
    composer require laravelcollective/html
    // For laravel 5.1
    composer require laravelcollective/html "5.1.*"
    ```

    * Add service provider & aliases to **/config/app.php** file.
    ```php
    'providers' => [
        ...

        Collective\Html\HtmlServiceProvider::class,
    ],

    // Use the lines below for "laravelcollective/html" package otherwise remove it.
    'aliases' => [
        ...

        'Form'      => Collective\Html\FormFacade::class,
        'HTML'      => Collective\Html\HtmlFacade::class,
    ],
    ```
4. Run **composer dump-autoload**

5. Publish config file & generator template files.
    ```
    php artisan vendor:publish --provider="App\Generator\CrudGeneratorAdminLteServiceProvider::class"
    ```
Note: You should have configured database for this operation.

6. Copy the Admin LTE folder to public path and rename it to "admin-lte"


## Commands

#### Crud command:

```
php artisan crud:generate Posts --fields="title#string, body#text"
```

You can also easily include route, set primary key, set views directory etc through options **--route**, **--pk**, **--view-path** as belows:

```
php artisan crud:generate Posts --fields="title#string#required, body#text#required_with:title|alpha_num" --route=yes --pk=id --view-path="admin" --namespace=Admin --route-group=admin
```

Options:

- --fields : Fields name for the form & model.
- --route : Include Crud route to routes.php? yes or no.
- --pk : The name of the primary key.
- --view-path : The name of the view path.
- --namespace : Namespace of the controller.
- --route-group : Prefix of the route group.

-----------
-----------


#### Other commands (optional):

For controller generator:

```
php artisan crud:controller PostsController --crud-name=posts --model-name=Post --view-path="directory" --route-group=admin
```

For model generator:

```
php artisan crud:model Post --fillable="['title', 'body']" 
```

For migration generator:

```
php artisan crud:migration posts --schema="title#string, body#text"
```

For view generator:

```
php artisan crud:view posts --fields="title#string, body#text" --view-path="directory" --route-group=admin
```

By default, the generator will attempt to append the crud route to your *routes.php* file. If you don't want the route added, you can use the option ```--route=no```.

After creating all resources, run migrate command. *If necessary, include the route for your crud as well.*

```
php artisan migrate
```

If you chose not to add the crud route in automatically (see above), you will need to include the route manually.
```php
Route::resource('posts', 'PostsController');
```

### Supported Field Types

These fields are supported for migration and view's form:

* string
* char
* varchar
* password
* email
* date
* datetime
* time
* timestamp
* text
* mediumtext
* longtext
* json
* jsonb
* binary
* number
* integer
* bigint
* mediumint
* tinyint
* smallint
* boolean
* decimal
* double
* float

### Custom Generator's Stub Template

You can customize the generator's stub files/templates to achieve your need.

1. Make sure you've published package's assets.
    ```
    php artisan vendor:publish --provider="App\Generator\CrudGeneratorServiceProvider"
    ```

2. Turn on custom_template support on **/config/crudgenerator_admin_lte.php**
    ```
    'custom_template' => true,
    ```
3. From the directory **/resources/crud-generator-admin-lte/** you can modify or customize the stub files.

