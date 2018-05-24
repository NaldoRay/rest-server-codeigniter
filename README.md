## How to use rest-client in the same project
1. Copy `vendor`, `application/core`, `application/services`, and `application/third_party/webclient` from `rest-client-x` 
to the respective directory in the project
2. Edit config for composer's autoload in `application/config.php`
```php
$config['composer_autoload'] = FCPATH . 'vendor/autoload.php';
```
3. Add helper method to load service class in `MY_Loader`
```php
public function service ($service, $object_name = null)
{
    $CI = get_instance();

    if (empty($object_name))
        $object_name = strtolower($service);

    if (!isset($CI->$object_name))
        $CI->$object_name = new $service();
}
```
4. Add `application/services` directory to autoloader in `hooks/Autoloader.php` 
by changing `Autoloader::__construct()`
```php
public function __construct ()
{
    $this->includeDirectories  = array(
        APPPATH.'core',
        APPPATH.'models',
        APPPATH.'services',
        APPPATH.'exceptions'
    );

    $this->excludeDirectories = array();
}
```

## Changelog

v5.6.0
+ Auto-convert ISO-8601 for timestamp data type from/to database
+ Update datetime format validation to use `DateTime::ISO8601` constant
+ Add rest-client library to call another rest api
+ Massive refactor directories, init language now set language in `config.php`
+ Rename `hiddenReadOnlyFieldMap` to `sortOnlyFieldMap`
+ Add helper method to (left) join an entity with another
+ Returns existing validator if field validator with same field name already exists
+ Fix database error to respond 500 internal error

v5.3.2
+ Move `app_helper` autoload from `config/autoload.php` to `MY_REST_Controller`

v5.3.1
+ Move general libraries to `application/third_party`
+ Update helper `includeClass()` and `requireClass` to support include/require all files `*` in the directory

v5.2.0
+ Add helper method to load service class
+ Update query condition library to support  json serialization

v5.1.0
+ Add default values for allowed fields on `limitFields()`
+ Add new query param `expands` to expand nested field(s)

v5.0.3
+ Fix missing `Content-Disposition` header if filename is not renamed on view/download
+ Fix `createOrUpdateEntity()` not passing table param
+ Support verification of SSL certificate when download/view remote file (defaults to no verification if no CA file is provided)

v5.0.0
+ Move database object to `MY_Model` that will be initialized in constructor. 
All query helper methods from model will use the database object

v4.0.0
+ Support nested field filtering

v3.0.0
+ Support advanced search/query, rename validation exception class
+ Add `Queriable` interface
+ Remove `unique` query param
+ Fix `fields` query param affecting only the first level of object/associative array
+ Swap param order between `sorts` and `fields`, `unique`
+ Prevent out of memory when querying single entity
+ Add `searches` query param
+ log path now relatives to `application` folder

v2.2.0
+ Add CI-specific validation, support custom default message
+ Support placeholders in validation error message
+ Add read-only field map on `MY_Model`
+ Fix `indonesian` language not found (should be `indonesia`)

v2.1.0
+ add method to view file as PDF
+ use base file path to view local files, 
support full/absolute path by setting base path to null

v2.0.0
+ update core files and library
+ move field mapping from controller to model
+ support message in English (en) and Indonesian (id) language

v1.2.8
+ include missing dependency `FilesValidator`
+ add CI library `FileViewer`
+ add not null validation
+ fix procedure call result always returns true/false

v1.2.5
+ add example endpoint
+ support forwarding response
+ autoload core files prefixed with `MY_`
+ support file validation