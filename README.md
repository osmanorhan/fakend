# Fakend
-------
Fakend provides mock api from json schemas. It has been written in PHP and sits on a couple of great php libs such as thephpleague's fractal[1], fzaninotto's faker[2] and memio's generator lib[3].
1. <https://github.com/thephpleague/fractal>
2. <https://github.com/fzaninotto/Faker>
3. <https://github.com/memio/memio>

It had been designed to provide mock API specifically for ember-data but this update changes model notation from ember-data model to global JSON notation to provide more flexible mock API backend for all frontend apps.

#### How to get

```sh
# Install Fakend
curl -s https://osmanorhan.github.io/fakend/install.php | php
```
##### Quick Look:

![Fakend Install Gif](https://raw.githubusercontent.com/osmanorhan/fakend/master/docs/fakend-install.gif)

#### How to define models for Fakend

You need to generate model files first. You have to place model files to **/api/Models/** directory then you need to add class properties as shown:

# post.js

```js
{
    "attrs": [
        {
            "fieldName": "title",
            "attributeType": "title",
            "parameters": {"length":4}
        },
        {
            "fieldName": "body",
            "attributeType": "description",
            "parameters": {"length":200,"html":true}
        },
        {
            "fieldName": "tag",
            "attributeType": "random",
            "parameters": {"values": ["human","robot","android"]}
        },
        {
            "fieldName": "date",
            "attributeType": "date",
            "parameters": {"from":"-4 year","to":"+1 year"}
        },
        {
            "fieldName": "count",
            "attributeType": "numberBetween",
            "parameters": {"min":10,"max":1000}
        },
        {
            "fieldName": "url",
            "attributeType": "url",
            "parameters": "{}"
        }
    ],
    "belongsTo": [
        {
            "fieldName": "author",
            "attributeType": "author",
            "parameters": {"required":true}
        }
    ],
    "hasMany": [
        {
            "fieldName": "comments",
            "attributeType": "comment",
            "parameters": {"required":false}
        }
    ]
}

```
### To resolve belongsTo:
# author.js
```js
{
    "attrs": [
        {
            "fieldName": "firstName",
            "attributeType": "firstName",
            "parameters": {}
        },
        {
            "fieldName": "lastName",
            "attributeType": "lastName",
            "parameters": {}
        },
        {
            "fieldName": "avatar",
            "attributeType": "imageURL",
            "parameters": {"type":"avatar","required":false}
        }
    }
}
```
### To resolve hasMany:
# comment.js
```js
{
    "attrs": [
        {
            "fieldName": "comment",
            "attributeType": "description",
            "parameters": {"length":50}
        }
    },
    "belongsTo": [
        {
            "fieldName": "post",
            "attributeType": "post",
            "parameters": {"required":true}
        }
    ]
}
```

### Parameters

Option  | Parameters | Description
------- | ------ | -----------
id | - | return id for record
title | length[integer] | title formatted string
description | length[integer], html[boolean] | long text formatted string
numberBetween | min[integer], max[integer] | provides a number in given range
date | from[string], e.g: -4 year, to[string] e.g +1 day | returns a iso_8601 date in given range
boolean | - | returns boolean value in 50% change
random | vales[array] | return selected value from given array  
url | - | returns random rul 
json | - | -
imageUrl | required[boolean], type['avatar or default'] | returns a random image url from lorempixel.com
mimeType | - | returns mime type for files
firstname | - | returns real name
lastName | - | returns real lastname
html | - | returns html formatted text

#### How to generate model class
First, you
"parser" executable symbolic link has been generated during install progress in first step. When you navigate to root directory folder then you can execute following command to generate schema files.
```sh
php parser generate [modelName]
```
If you do not provide modelName, fakend will generate php schema classes for all models. **modelName** is single model name to generate/update single model file. 

```sh
#Example command
php parser post.js
```
This will generate **/api/Schema/Post.php** and will consist of model's attributes.

#### How to use 
Fakend ships with basic silex app at api/ directory and each Fakend provides CRUD metods(GET/POST/PUT/DELETE) endpoint will be generated and placed at this file. New endpoints will be append end of this file.

If you want to optimize or change methods you can use followings.
In index.php:

```php
use Fakend\FakendFactory;
```
This adds fakend base class.

Fakend uses League's Fractal library for serializations. You can use following default fractal serializers or you can use your own custom serializer class to format your response data.

```php
use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\Serializer\DataArraySerializer;
use League\Fractal\Serializer\ArraySerializer;
```

A basic custom .NET web api serializer has been added to project as an example.

```php
use Fakend\Presentation\Serializers\WebApiSerializer;
```

Then you need to initialize related model class and need to pass serializer.

```php
$post = FakendFactory::create('post');
$post->setSerializer(new JsonApiSerializer());
$return = $post->setMeta(array('totalCount' => 11))->getMany(5);
```
From **$post** variable, you can call:


Method  | Description
------ | ------------------
`setMeta(array)` |  to set metadata.
`get(id)` |  to get an record for provided id.
`getMany(limit)` |  to get number of records from api.
`setBelongsTo(belongsToObject)` |  to set same belongsTo item for all requested records.
`setBelongsToByName(name, belongsToObject)` |  to set belongsto property by name.
`setParentObject(parent)` |  to set same parent object for all recursive models.


### Docker 
We provide a docker container(PHP & NGINX) at root directory. You just need to build & up.

### Silex example 

```php
$app->match('/posts', function(Request $request) use ($app) {
    if($request->getMethod() == 'OPTIONS') {
        return new Response('', 200);
    }
    $post = FakendFactory::create('post');
    $post->setSerializer(new JsonApiSerializer());
    $return = $post->setMeta(array('totalCount' => 11))->getMany(5);
    return new Response($return, 200, array(
        'Content-Type' => 'application/json',
    ));
})->method('GET|OPTIONS');
$app->match('/posts/{id}', function($id, Request $request) use ($app) {
    if($request->getMethod() == 'OPTIONS') {
        return new Response('', 200);
    }
    $post = FakendFactory::create('post');
    $post->setSerializer(new JsonApiSerializer());
    $return = $post->get($id);
    return new Response($return, 200, array(
        'Content-Type' => 'application/json',
    ));
})->method('GET|OPTIONS');
$app->match('/posts/{id}', function($id, Request $request) use ($app) {
    if($request->getMethod() == 'OPTIONS') {
        return new Response('', 200);
    }
    $return = json_encode(array());
    return new Response($return, 200, array(
        'Content-Type' => 'application/json',
    ));
})->method('DELETE|OPTIONS');
```

You can find sample outputs for this example in `samples/` folder.


