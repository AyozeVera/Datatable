Datatable
=========

This is a __laravel 4 package__ for the server and client side of datatables at http://datatables.net/

I developed this package because i was not happy with the only existing package at https://github.com/bllim/laravel4-datatables-package
so i developed this package which in my opinion is superior.

![Image](https://raw.github.com/Chumper/Datatable/master/datatable.jpg)

##Known Issues

* None so far

##TODO

* make the two engines behave exactly the same.

##Features

This package supports:

*   Support for Collections and Query Builder
*   Easy to add and order columns
*   Includes a simple helper for the HTML side
*   Use your own functions and presenters in your columns
*   Search in your custom defined columns ( Collection only!!! )
*   Define your specific fields for searching and ordering
*   Add custom javascript values for the table
*   Tested! (Ok, maybe not fully, but I did my best :) )

##Please note!

There are some differences between the collection part and the query part of this package.
The differences are:

|  Difference  | Collection | Query |
| --- |:----------:| :----:|
|Speed| - | +
Custom fields | + | +
Search in custom fields | + | -
Order by custom fields | + | -
Search outside the shown data (e.g.) database | - | +

For a detailed explanation please see the video below.
http://www.youtube.com/watch?v=c9fao_5Jo3Y

Please let me know any issues or features you want to have in the issues section.
I would be really thankful if you can provide a test that points to the issue.

##Installation

This package is available on http://packagist.org, just add it to your composer.json
	
	"chumper/datatable": "dev-master"

It also has a ServiceProvider for usage in Laravel4. Add these lines to app.php:

```php
    // providers array:	
	'Chumper\Datatable\DatatableServiceProvider',

    // aliases array:
    'Datatable' => 'Chumper\Datatable\Facades\Datatable',
```

You can then access it under the `Datatable` alias.


##Basic Usage

* Create two routes: One to deliver the view to the user, the other for datatable data, eg:

```php
    Route::resource('users', 'UsersController');
    Route::get('api/users', array('as'=>'api.users', 'uses'=>'UsersController@getDatatable'));
```

* Your main route will deliver a view to the user. This view should include references to your local copy of [datatables](http://datatables.net/). In the example below, files were copied from the datatables/media directories and written to public/assets. Please note that the scripts must be located above the call to Datatable:

```php
    <link rel="stylesheet" type="text/css" href="/assets/css/jquery.dataTables.css">
    <script type="text/javascript" src="/assets/js/jquery.js"></script>
    <script type="text/javascript" src="/assets/js/jquery.dataTables.min.js"></script>

    {{ Datatable::table()
    ->addColumn('id','Name')       // these are the column headings to be shown  
    ->setUrl(route('api.users'))   // this is the route where data will be retrieved
    ->render() }}
```

* Create a controller function to return your data in a way that can be read by Datatables:

```php
    public function getDatatable()
    {
        return Datatable::collection(User::all(array('id','name')))
        ->showColumns('id', 'name')
        ->searchColumns('name')
        ->orderColumns('id','name')
        ->make();
    }
```

You should now have a working datatable on your page.

##HTML Example

```php
	Datatable::table()
    ->addColumn('id',Lang::get('user.lastname'))
	->setUrl(URL::to('auth/users/table'))
    ->render();
```

This will generate a HTML table with two columns (id, lastname -> your translation) and will set the URL for the ajax request.

>   Note: This package will **NOT** include the `datatable.js`, that is your work to do.
>   The reason is that for example i use Basset and everybody wants to do it their way...

If you want to provide your own template fpr the table just provide the path to the view in laravel style.

```php
	Datatable::table()
        ->addColumn('id',Lang::get('user.lastname'))
    	->setUrl(URL::to('auth/users/table'))
        ->render('views.templates.datatable');
```
##Server Example

```php
	Datatable::collection(User::all())
    ->showColumns('id')
    ->addColumn('name',function($model)
        {
            return $model->getPresenter()->yourProperty
        }
    )->make();
```

This will generate a server side datatable handler from the collection `User::all()`.
It will add the `id` column to the result and also a custom column called `name`.
Please note that we need to pass a function as a second parameter, it will **always** be called
with the object the collection holds. In this case it would be the `User` model.

You could now also access all relationship, so it would be easy for a book model to show the author relationship.

```php
	Datatable::collection(User::all())
    ->showColumns('id')
    ->addColumn('name',function($model)
        {
            return $model->author->name;
        }
    )->make();
```

>   Note: If you pass a collection of arrays to the `collection` method you will have an array in the function, not a model.

The order of the columns is always defined by the user and will be the same order the user adds the columns to the Datatable.

##Query or Collection?

There is a difference between query() and collection().
A collection will be compiled before any operation - like search or order - will be performed so that it can also include your custom fields.
This said the collection method is not as performing as the query method where the search and order will be tackled before we query the database.

So if you have a lot of Entries (100k+) a collection will not perform well because we need to compile the whole amount of entries to provide accurate sets.
A query on the other side is not able to perform a search or orderBy correctly on your custom field functions.

>   TLTR: If you have no custom fields, then use query() it will be much faster
>   If you have custom fields but don't want to provide search and/or order on the fields use query().
>   Collection is the choice if you have data from somewhere else, just wrap it into a collection and you are good to go.
>   If you have custom fields and want to provide search and/or order on these, you need to use a collection.

Please also note that there is a significant difference betweeen the search and order functionality if you use query compared to collections.
Please see the following video for more details.

http://www.youtube.com/watch?v=c9fao_5Jo3Y

##Available functions

This package is separated into two smaller parts:

1.  Datatable::table()
2.  Datatable::from()
    1. Datatable::collection()
    2. Datatable::query()

The second one is for the server side, the first one is a helper to generate the needed table and javascript calls.

###Collection & Query

**from($mixed)**

Will set the internal engine to the best fitting one based on the input.
If you want to set one explicitly just select one of the methods below.

**collection($collection)**

Will set the internal engine to the collection.
For further performance improvement you can limit the number of columns and rows, i.e.:

	$users = User::activeOnly()->get('id','name');
	Datatable::collection($users)->...

**query($query)**

This will set the internal engine to a Eloquent query...
E.g.:

	$users = DB::table('users');
	Datatable::query($users)->...

The query engine is much faster than the collection engine but also lacks some functionality,
for further information look at the table above.

**showColumns(...$columns)**

This will add the named columns to the result.
>   Note: You need to pass the name in the format you would access it on the model or array.
>   example: in the db: `last_name`, on the model `lastname` -> showColumns('lastname')

You can provide as many names as you like

**searchColumns(..$fields)**

Will enable the table to allow search only in the given columns.
Please note that a collection behaves different to a builder object.

Note: If you want to search on number columns with the query engine, then you can also pass a search column like this
 ```
    //mysql
    ->searchColumns(array('id:char:255', 'first_name', 'last_name', 'address', 'email', 'age:char:255'))

    //postgree
    ->searchColumns(array('id:text', 'first_name', 'last_name', 'address', 'email', 'age:text'))
 ```

 This will cast the columns int the given types when searching on this columns

**orderColumns(..$fields)**

Will enable the table to allow ordering only in the given columns.
Please note that a collection behaves different to a builder object.

**addColumn($name, $function)**

Will add a custom field to the result set, in the function you will get the whole model or array for that row
E.g.:
```php
	Datatable::collection(User::all())
    ->addColumn('name',function($model)
        {
            return $model->author->name;
        }
    )->make();
```
You can also just add a predefined Column, like a DateColumn, a FunctionColumn, or a TextColumn
E.g.:
```php
	$column = new TextColumn('foo', 'bar'); // Will always return the text bar
	//$column = new FunctionColumn('foo', function($model){return $model->bar}); // Will return the bar column
	//$column = new DateColumn('foo', DateColumn::TIME); // Will return the foo date object as toTimeString() representation
	//$column = new DateColumn('foo', DateColumn::CUSTOM, 'd.M.Y H:m:i'); // Will return the foo date object as custom representation

	Datatable::collection(User::all())
    ->addColumn($column)
    ->make();
```

PLease look into the specific Columns for further information.

**stripSearchColumns()**

If you use the search functionality ( Collection only ) then you can advice
all columns to strip any HTML and PHP tags before searching this column.

This can be useful if you return a link to the model detail but still want to provide search ability in this column.

**setCaseSensitiveSearchForPostgree($boolean)**

If you want to enable case sensitive search on your columns you should set this option to false for a postgree database.

Please note: Case sensitive searching with the querybuilder only works if you have a CASE SENSITIVE (_cs) collation on your mysql table.

**setSearchWithAlias()**

If you want to use an alias column on the query engine and you don't get the correct results back while searching then you should try this flag.
E.g.:
```php
		Datatable::from(DB::table("users")->select('firstname', "users.email as email2")->join('partners','users.partner_id','=','partners.id'))
        ->showColumns('firstname','email2')
        ->setSearchWithAlias()
        ->searchColumns("email2")
```

In SQL it is not allowed to have an alias in the where part (used for searching) and therefore the results will not counted correctly.

With this flag you enable aliases in the search part (email2 in searchColumns).

Please be aware that this flag will slow down your application, since we are getting the results back twice to count them manually.

**make()**

This will handle the input data of the request and provides the result set.
> Without this command no response will be returned.

**clearColumns()**

This will reset all columns, mainly used for testing and debugging, not really useful for you.
>   If you don't provide any column with `showColumn` or `addColumn` then no column will be shown.
>   The columns in the query or collection do not have any influence which column will be shown.

**getOrder()**

This will return an array with the columns that will be shown, mainly used for testing and debugging, not really useful for you.

**getColumn($name)**

Will get a column by its name, mainly used for testing and debugging, not really useful for you.

###Table

**setUrl($url)**

Will set the URL and options for fetching the content via ajax.

**setOptions($name, $value) OR setOptions($array)**

Will set a single option or an array of options for the jquery call.

**setCallbacks($name, $value) OR setCallbacks($array)**

Will set a single callback function or an array of callbacks for the jquery call. DataTables callback functions are described at https://datatables.net/usage/callbacks. For example, 

```php
    ->setCallbacks(
        'fnServerParams', 'function ( aoData ) {
            aoData.push( { "name": "more_data", "value": "my_value" } );
        }'
    )

```

**addColumn($name)**

Will add a column to the table, where the name will be rendered on the table head.
So you can provide the string that should be shown.

**countColumns()**

This will return the number of columns that will be rendered later. Mainly for testing and debugging.

**getData()**

Will return the data that will be rendered into the table as an array.

**getOptions()**

Get all options as an array.

**render($view = template.blade)**

Renders the table. You can customize this by passing a view name.
Please see the template inside the package for further information of how the data is passed to the view.

**setData($data)**

Expects an array of arrays and will render this data when the table is shown.

**setCustomValues($name, $value) OR setCustomValues($array)**

Will set a single custom value, or an array of custom values, which will be passed to the view. You can access these values in your custom view file. For example, if you wanted to click anywhere on a row to go to a record (where the record id is in the first column of the table):

In the calling view:

```php
{{ DataTable::table()
    ->addColumn($columns)
    ->setUrl($ajaxRouteToTableData)
    ->setCustomValues('table-url', $pathToTableDataLinks)
    ->render('my.datatable.template') }}
```

In the datatable view (eg, 'my.datatable.template'):

```js
    @if (isset($values['table-url']))
        $('.{{$class}}').hover(function() {
            $(this).css('cursor', 'pointer');
        });
        $('.{{$class}}').on('click', 'tbody tr', function(e) {
            $id = e.currentTarget.children[0].innerHTML;
            $url = e.currentTarget.baseURI;
            document.location.href = "{{ $values['table-url'] }}/" + $id;
        });
    @endif
```
##Contributors

* [jgoux](https://github.com/jgoux) for helping with searching on number columns in the database
* [jijoel](https://github.com/jijoel) for helping with callback options and documentation

##Applications

https://github.com/hillelcoren/invoice-ninja (by Hillel Coren)

##License

This package is licensed under the MIT License
