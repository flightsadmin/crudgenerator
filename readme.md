# CrudGenerator

Crud Generator automatically generates all necessary files for a complete crud. This includes factories, migrations, models, blades,
requests & seeders. This package also has the possibility to create relations between models.  

For relations to work you'll need to manually add comments to the User Model otherwise it won't generate it for you.  
These comments will be automatically added by models created by this CrudGenerator. Refer to Relations section for more info.

## Installation

Via Composer

``` bash
$ composer require flightsadmin/crudgenerator
```

## Usage
If you would like to publish the other file such as config, stubs and views to customize them
you can do so with
```bash
$ php artisan vendor:publish --provider="Flightsadmin\CrudGenerator\CrudGeneratorServiceProvider"
```
