## Changelog

v5.37.0
+ Add new function to read and return remote file content: `readRemoteFile()` to `FileViewer`, `File_manager`, and `readFile()` to `APP_Web_service`

v5.36.1
+ Fix `FieldsFilter::getFields()` only returns first-level field array (should return 'original' array)

v5.36.0
+ Add query log viewer
+ update request log viewer: fix hour format to 24H, skip empty log line

v5.35.0
+ Add new getter `MY_Model::getEntityFromRawQuery()` to get first row from raw query result

v5.34.0
+ All update and delete functions of `MY_Model` now check if field exists strictly i.e. throws error if filter/condition field is not found

v5.33.2
+ Add `OracleEqualsCondition` and `OracleNotEqualsCondition` to fix `ORA-01795: maximum number of expressions in a list is 1000` 
when using `EqualsCondition` and `NotEqualsCondition` on Oracle SQL with IN values more than 1,000 elements

v5.33.0
+ Fix `createdAt`, `updatedAt`, and `inupby` fields are not allowed on create/update if `allowedFields` param is used
+ Fix write-only fields are always allowed on create/update

v5.32.0
+ Fix bug filter with `null` value  is not used; fix bug cannot update/insert number field with `null` value
+ Add `InvalidStateException` that will return `409 Conflict` when thrown up to rest controller

v5.31.0
+ Add `MY_Model::getFirstEntityWithCondition()`

v5.30.0
+ Add validation for digit(s) only 

v5.29.0
+ Logs all error request-responses which have context. Access token / credentials in`Authorization` header won't be logged. 
+ Add simple UI/viewer to view error request logs
+ Change rest access config to allow regex (not supporting wildcard anymore)

v5.28.4
+ Allow `null` and empty value when parsing search param
+ Remove allowed fields param from `MY_Model::createOrUpdateEntity()`
+ Fix date field with `null` value formatted to 1 Jan 1970 in ISO-8601 format

v5.28.1
+ Fix 'missing right parenthesis' when using `ContainsCondition` and `NotContainsCondition` for case-sensitive operation 

v5.28.0
+ Add `APP_Model::updateEntitiesWithCondition()`
+ Fix `APP_Model::updateEntities()` didn't filter index field (`$indexField`) when updating `updateAt` field

v5.27.1
+ Fix `MY_Model::getAllEntities()` cannot sort some fields if the fields is not selected

v5.27.0
+ Add `MY_Model::getReadableFields()`
+ Fix `MY_Model::toTableCondition()` throws `BadFormatException`
+ Query param `expands` format now loosely support XPath syntax
+ Upgrade `MY_Model::getReadFieldMap()` visibility to `protected`

v5.26.3
+ Log remote address read by server and 'real' client's ip address from custom header `X-Client-IP` (if any)

v5.26.2
+ Fix `APP_Model::updateEntities()` didn't update `updatedAt` field
+ Fix `APP_Model::updateEntities()` doing some updates with missing filters 

v5.26.0
+ Allow log daily

v5.25.0
+ Fix update batch failed even though doing it with raw query is success (only on MySQL because number of affected rows is less than the enumber of update rows)
+ Update batch now allows partial success because MySQL won't update rows that don't change i.e. the affected rows from MySQL might be less than number of update rows, different than Oracle which always return number of update rows whether the rows changed or not. 
+ Add `APP_REST_Controller::getFirstGroupId()`

v5.24.0
+ Add `RawCondition` to support raw (query) condition
+ Fix method name in `MY_Web_service`
+ Add helper to check date (range) to `core_helper`

v5.23.0
+ Add configuration for query logging category (weekly, monthly, yearly)

v5.22.0
+ Rename core methods, add `createdAt`, add missing `inupby` on `APP_Model::updateEntities()`, always replace `inupby`
+ Remove `sortOnlyFieldMap` from `MY_Model`'s properties 
+ Add `hiddenReadFields` to `MY_Model`'s properties to hide entity fields

v5.21.0
+ Move core files to `application/core`, fix unit tests
+ Refactor to allow filter/get acl prodi/unit by group

v5.20.0
+ Add ip-based authorization, move client-specific authorization to `RestAccess`

v5.15.0
+ Set WebClient::reset() visibility to public
+ Update `APP_REST_Controller` to use token

v5.14.1
+ Add `MY_Model::getString()` to get localization string

v5.14.0
+ Add `NotCondition` to negate whole condition

v5.13.1
+ Update dependencies, fix [count on null](`https://github.com/guzzle/guzzle/pull/1686`)

v5.13.0
+ Add `FieldPairCondition` to compare field with another field

v5.12.3
+ Fix joined field name (add suffix) when join resource not found

v5.12.2
+ Throw `TransactionException` when `executeRawQuery(sql)` failed in `MY_Model`
+ Add helper method to escape table value in `MY_Model`
+ Extract helper method to convert date time value to table's date time value in `MY_Model`
+ Refactor controller helper method to get query param and search param
+ Fix `entityExistsWithCondition` haven't convert condition to table condition
+ Fix error message when failed to update one of entities
+ Add param to add suffix to  the joined fields

v5.11.0
+ `MY_Model::createEntities()` and `MY_Model::updateEntities` now doesn't allow partial success. Method will only returns when all entities area created/updated.  
+ `MY_Model::deleteEntity()` now returns number of deleted rows
+ `APP_Model::deleteEntity` throws `ResourceNotFoundException` if no entity deleted
+ `MY_Model::createEntities` throws `BadValueException` if data is empty, and TransactionException if failed to execute db
+ Fix query condition with `null` value (for `is null` / `is not null`)
+ Add authentication exception - returns 401 response

v5.10.0
+ Autoload model in subfolders without specifying subfolder path

v5.9.2
+ AuthorizationException  returns 403 forbidden
+ Add validation to check if value is array, numeric array, associative array
+ Move app helper function and log function to core/helpers
+ Load core helper on pre system hook
+ Load log helper in `MY_Loader
+ Fix auto-convert datetime/timestamp value from database to `DateTime::ISO8601` format
+ Override array validation's error messages to use language file

v5.8.0
+ Update rest-client to v2.2.0
+ Remove hidden T_UPDATE set on update, allows table without T_UPDATE and/or V_INUPBY by overriding $writeOnlyFieldMap
+ Set default prefix for log files to "log"
+ Merge new expands & sorts with previous array, add `resetExpand()`

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