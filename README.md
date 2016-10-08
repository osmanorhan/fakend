# Fakend
-------
Fakend provides mock api for ember-data. It has been written in PHP and sits on a couple of great php libs such as thephpleague's fractal[1], fzaninotto's faker[2] and memio's generator lib[3].
1. <https://github.com/thephpleague/fractal>
2. <https://github.com/fzaninotto/Faker>
3. <https://github.com/memio/memio>

#### How to get

```sh
# Composer
composer require brewinteractive/fakend
```
#### How to define models for Fakend

You need to generate model files in ember first. Then you need to add class properties each line as comment as shown:

# post.js

```js
import Model from 'ember-data/model';

export default Model.extend({
	title: DS.attr('string'), /* {"type":"title", "parameters": {"length": 4}} */
	body: DS.attr('string'), /* {"type":"description", "parameters": {"length": 200, "html": true}} */
	tag: DS.attr('string'), /* {"type":"random", "parameters": {"values": ["human", "robot", "android"]}} */
	date: DS.attr('date'), /* {"type":"date", "parameters": {"from":"-4 year","to":"+1 year"}} */
	count: DS.attr('number'), /* {"type":"numberBetween", "parameters": {"min": 10,"max":1000}} */
	url: DS.attr('string'),  /* {"type":"url", "parameters": {}} */
	author: DS.belongsTo('author'), /* {"type":"author", "parameters": {"required": true}} */
	comments: DS.hasMany('comment'), /* {"type":"comment", "parameters": {"required": false}} */
});
```
# author.js
```js
import Model from 'ember-data/model';

export default Model.extend({
	firstName: DS.attr('string'), /* {"type":"firstName", "parameters": {}} */
	lastName: DS.attr('string'), /* {"type":"lastName", "parameters": {}} */
	avatar: DS.attr('string'), /* {"type":"imageURL", "parameters": {"type":"avatar","required":false}} */
});
```
# comment.js
```js
import Model from 'ember-data/model';

export default Model.extend({
	post: DS.belongsTo('post'), /* {"type":"post", "parameters": {"required": true}} */
	comment: DS.attr('string'), /* {"type":"description", "parameters": {"length":50}} */
});

```

### Parameters

Option  | Parameters | Description
------- | ------ | -----------
id | - | return id for record
title | length[integer] | title formatted string
description | length[integer], html[boolean] | long text formatted string
numberBetween | min[integer], max[integer] | provies a number given range
date | from[string], e.g: -4 year, to[string] e.g +1 day | returns a iso_8601 date in given range
boolean | - | returns boolean value in 50% change
random | vales[array] | return selected value in given array  
url | - | returns random rul 
json | - | -
imageUrl | required[boolean], type['avatar or default'] | returns an random image url from lorempixel.com
mimeType | - | returns mime type for files
firstname | - | returns real name
lastName | - | returns real lastname
html | - | returns html formatted text

#### How to generate model classes

parser.php in Fakend/Bin is used to generate php model classes. You need to navigate this folder first then you can execute following 
command to generate schema files.
```sh
parser.php generate path [modelName]
```
path is full path of ember model folder. It is required. If you provide just this option, fakend will generate php schema classes all models.
modelName is single model name to generate/update single model file. It is optional.

```sh
#Example command
php parser.php generate ../../../../app/models/ post.js
```
This will generate Schema/Post.php and will consist of ember model's attributes.

#### How to use 

Fakend is only provides GET metods currently. In order to generate json files, you need to add Fakend class in your PHP file first. 
```php
use Fakend\FakendFactory;
```
Fakend uses League's Fractal library for serializations. You can use following default fractal serializers or you can use your own custom serializer class to format your response data.
```php
use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\Serializer\DataArraySerializer;
use League\Fractal\Serializer\ArraySerializer;
```
A basic custom .net web api serializer has been added to project as an example.
```php
use Fakend\Presentation\Serializers\WebApiSerializer;
```

Then you need to initialize related model class and need to pass serializer.
```php
$post = FakendFactory::create('post');
$post->setSerializer(new JsonApiSerializer());
$return = $post->setMeta(array('totalCount' => 11))->getMany(5);
```
You can use:


Method  | Description
------ | ------------------
`setMeta(array)` |  to set metadata.
`get(id)` |  to get an record for provided id.
`getMany(limit)` |  to get number of records from api.
`setBelongsTo(belongsToObject)` |  to set same belongsTo item for all requested records.
`setBelongsToByName(name, belongsToObject)` |  to set belongsto property by name.
`setParentObject(parent)` |  to set same parent object for all recursive models.

### Silex example for above models

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

You can find sample outputs for posts as json api and data api formats in `samples/` folder.


