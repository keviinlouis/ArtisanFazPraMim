<?php

return [
    'lang' => 'pt_br',

    'custom_template' => false,

    'with_address_model' => true,

    'with_file_model' => true,

    'with_api' => true,

    'with_web' => false,

    'with_geocode' => true,

    'with_push' => true,

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

    'observers' => [
        'namespace' => 'App\Observers',
        'path' => app_path('Observers'),
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

    'not_required_fields' => [
        'ip_when_accepted_terms',
        'email_verified_at',
        'device_model',
        'device_token',
        'average_ratings'
    ],


    /**
     * Columns number to show in view's table.
     */
    'view_columns_number' => 3,

];
