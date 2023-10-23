# PHP JSON Parser
## Introduction
Welcome to the PHP JSON Library, a simple tool for handling JSON data in your PHP applications. This library provides a set of functions for reading JSON data from strings or files, manipulating JSON data, and conveniently handling cases where a key doesn't exist in your JSON structure by returning a default value.

## Features

- **Read JSON from String**: Easily parse JSON data from a string and work with it within your PHP application.

- **Read JSON from File**: Quickly read JSON data from a file, simplifying the process of working with external JSON files.

- **Manipulate JSON Data**: Perform various operations on your JSON data, such as adding, updating, or removing keys, all with a simple and intuitive API.

- **Default Value Handling**: Handle cases where a JSON key doesn't exist by specifying a default value, preventing unexpected errors in your code.

## Installation

To get started, you can install the library using [Composer](https://getcomposer.org):

```bash
composer require gemul/jsonparser
```

## Usage
### 1. Load JSON Data

Reading JSON from String
```php
$json = new \Gemul\JsonParser($json_string);
```
or from JSON file
```php
$json = new \Gemul\JsonParser(storage_path('/app/public/dummy.json'));
```
you can alson define separator string used for traversing the json tree, in constructor's second parameter (default ".").
```php
$json = new \Gemul\JsonParser($json_string,'->');
```

### 2. Getting JSON data

Given this json structure:
```json
{
  "openapi": "3.0.3",
  "info": {
    "title": "Swagger Petstore - OpenAPI 3.0",
    "description": "This is a sample",
    "contact": {
      "email": "apiteam@swagger.io"
    }
  },
  "externalDocs": {
    "description": "Find out more about Swagger",
    "url": "http://swagger.io"
  },
  "servers": [
    {
      "url": "https://petstore2.swagger.io/api/v2"
    },
    {
      "url": "https://petstore3.swagger.io/api/v3"
    }
  ],
  ...
}
```
you can use ```getItem(path,[default value])``` to traverse the json tree and get the data.
```php
$json->getItem('openapi'); // 3.0.3
$json->getItem('info.title'); // Swagger Petstore - OpenAPI 3.0
$json->getItem('info.contact.email'); // apiteam@swagger.io
$json->getItem('servers.1.url'); // https://petstore3.swagger.io/api/v3
```
it can also return the rest of the json branches, for example
```php
$json->getItem('info.contact'); // stdClass {"email": "apiteam@swagger.io"}
```
#### Handling path that doesn't exist
if the path doesn't exist, the default value is returned which is either null (default) or anything you put on second parameter. It won't throw 'Undefined property' exception even if the whole path doesn't exist. So you can safely traverse without having to do check at every level.
```php
$json->getItem('info.contact.phone'); // null (path not found in tree, default to null)
$json->getItem('info.contact.phone',1234); // 1234 (set the default to 1234 instead of null)
$json->getItem('info.foo.bar',false); // false (doesn't throw 'Undefined property "foo"' exception)
```
### 3. Set a JSON data
In order to set a data to the tree, you can use ```setItem(path,value)``` for example
```php
$json->setItem('info.version','1.0.0');
```
the json would become
```json
{
  "openapi": "3.0.3",
  "info": {
    "title": "Swagger Petstore - OpenAPI 3.0",
    "description": "This is a sample",
    "contact": {
      "email": "apiteam@swagger.io"
    },
    "version": "1.0.0"
  },
  ...
```
You can safely make new depth to the path
```php
$json->setItem('info.foo.bar.baz','somevalue');
```
```json
{
  "openapi": "3.0.3",
  "info": {
    "foo": {
        "bar": {
            "baz": "somevalue"
        }
    },
    "title": "Swagger Petstore - OpenAPI 3.0",
  ...
```
#### Setting Array
For array data, you can explicitly use index, or use '[]' to change or append an element into either existing or new array.
```php
$json->setItem('servers.2.url', "new url");
//or
$json->setItem('servers.[].url', "new url");
```
will result in
```json
...
  "servers": [
    {
      "url": "https://petstore2.swagger.io/api/v2"
    },
    {
      "url": "https://petstore3.swagger.io/api/v3"
    },
    {
      "url": "new url"
    }
  ],
...
```
### 3. Get last valid path
After executing ```getItem()```, you can use ```getLastValidPath()``` to retrieve the last valid path that was successfully traversed by the getItem method.
```php
$json->getItem('info.contact.phone.home');
//will return "info.contact", because from 'phone' onward doesn't exist
```
### 4. Get the current full JSON object
To get the current json as an object, use ```getJsonObject()```. Or alternatively the json-encoded string using ```getJsonString()```.
### 5. Save json string to file
To save the json string, use ```saveAs(file_path)```
```php
$json->saveAs(storage_path('/app/public/result.json'));
```
make sure that the directory is writable.