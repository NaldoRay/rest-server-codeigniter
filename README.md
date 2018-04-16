## Changelog

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