<?php

return [

    'custom_template' => false,

    'with_address_model' => true,

    'with_file_model' => true,

    'with_api' => true,

    'with_web' => false,

    'with_geocode' => true,

    'with_push' => true,

    /*
    |--------------------------------------------------------------------------
    | Crud Generator Template Stubs Storage Path
    |--------------------------------------------------------------------------
    |
    | Here you can specify your custom template path for the generator.
    |
     */

    'path' => base_path('resources/crud_generator_admin_lte/'),

    /*
   |--------------------------------------------------------------------------
   | User Auth
   |--------------------------------------------------------------------------
   |
   | Aqui voce define quais campos caracteriza um usuario que é autenticavel no sistema.
   | por exemplo: Todas as tabelas que possuem email e password serão usuarios que entraram no sistema
   |
    */
    'auth_fields' => [
        'email',
        'password',
    ],

    'traits' => [
        'namespace' => 'App\Traits',
        'path' => app_path('Traits'),
    ],

    'models' => [
        'namespace' => 'App\Models',
        'path' => app_path('Models'),
    ],

    'resources' => [
        'namespace' => 'App\Http\Resources',
        'path' => app_path('Http/Resources'),
    ],

    'services' => [
        'namespace' => 'App\Services',
        'path' => app_path('Services'),
    ],

    'controllers_api' => [
        'namespace' => 'App\Http\Controllers\Api',
        'path' => app_path('Http/Controllers/Api'),
    ],


    'controllers' => [
        'namespace' => 'App\Http\Controllers',
        'path' => app_path('Http/Controllers'),
    ],

    'validators' => [
        'namespace' => 'App\Validators',
        'path' => app_path('Validators'),
    ],

    'routes' => [
        'path' => app_path('../routes'),
    ],

    'routes_api' => [
        'path' => app_path('../routes/api'),
    ],

    'searchable_fields' => [
        'name',
        'email',
        'ean',
        'corporate_name',
        'tranding_name',
        'last_name',
        'first_name',
        'full_name',
        'message',
        'title',
        'category',
        'type',
        'subcategory',
    ],


    /**
     * Columns number to show in view's table.
     */
    'view_columns_number' => 3,

];
