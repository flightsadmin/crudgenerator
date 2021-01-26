<?php

namespace Flightsadmin\CrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GeneratorCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'crud:generate {name : Class (singular, e.g User} {fields?*}';

    protected $name;
    protected $fillables = [];
    protected $presets;
    protected $quantity;

    /**
     * The console command description.
     */
    protected $description = 'Create CRUD operations';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        $this->presets = config("crudgenerator.presets");
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $input = $this->arguments()['fields'];
        $no_cmd = count($input) !== 0;
        $this->fillables = $no_cmd ? $this->loadDataFromUI($input) : $this->loadDataFromCmd();
        $this->name = ucfirst($this->argument('name'));

        $this->model();
        $this->info('Model generated.');
        $this->livewire();
        $this->info('Livewire Component generated.');
        $this->indexBlade();
        $this->viewBlade();
        $this->componentBlade();
        $this->editBlade();
        $this->createBlade();
        $this->info('Blade files created.');

        $this->filesystem = new Filesystem;
        $routeFile = base_path('routes/web.php');
        $routeContents = $this->filesystem->get($routeFile);
        $routeItemStub = "\t".'Route::view(\''. Str::plural(strtolower($this->name)) . "','livewire.". Str::plural(strtolower($this->name)).".index')->middleware('" . config('crudgenerator.middlewares.createdViews') . "');";
		$routeItemHook = '//Route Hooks - Do not delete//';

		if (!Str::contains($routeContents, $routeItemStub)) {
            $newContents = str_replace($routeItemHook, $routeItemHook . PHP_EOL . $routeItemStub, $routeContents);
            $this->filesystem->put($routeFile, $newContents);
            $this->warn('Route inserted: <info>' . $routeFile . '</info>');
        } 
		
		$sidebarFile = 'resources/views/layouts/sidebar.blade.php';
        $layoutContents = $this->filesystem->get($sidebarFile);
        $navItemStub = "\t\t\t\t\t<li class=\"nav-item\">
                            <a href=\"{{ url('/".Str::plural(strtolower($this->name))."') }}\" class=\"nav-link\"><i class=\"fab fa-laravel text-info\"></i> ". Str::plural(ucfirst($this->name)) ."</a> 
                        </li>";
        $navItemHook = '<li class="nav-header">ADMIN NAVIGATION</li>';

        if (!Str::contains($layoutContents, $navItemStub)) {
            $newContents = str_replace($navItemHook, $navItemHook . PHP_EOL . $navItemStub, $layoutContents);
            $this->filesystem->put($sidebarFile, $newContents);
            $this->warn('Nav link inserted: <info>' . $sidebarFile . '</info>');
        }
		
		$this->migration();
        $this->info('Migration created.');
        $this->quantity = (int)$this->ask('How many rows do you want to seed? press 0 if you dont want to seed');
        if ($this->quantity > 0) {
            $this->seeder();
            $this->info('Seeder created.');
            $this->factory();
            $this->info('Factory created.');
            if ($no_cmd ? true : $this->confirm('Do you want to migrate?'))
                Artisan::call('migrate');
        }

        $this->comment($this->name . " CRUD has been created!\n" .
            'Be sure to confirm the content of database\\factories\\' . $this->name . 'Factory.php if you want to use the seeder.');
        return true;
    }


    /**
     * Get the stub with the $type.stub
     */
    protected function getStub($type)
    {
        return file_get_contents(file_exists(resource_path("/stubs/$type.stub")) ? resource_path("/stubs/$type.stub") : __dir__."/../stubs/$type.stub");
    }

    /**
     * String replace the Model.stub and makes Model.php
     */
    protected function model()
    {
        $template = str_replace(
            [
                '{{modelName}}',
				'{{modelNamePluralLowerCase}}',
                '{{fields}}'
            ],
            [
                $this->name,
				strtolower(Str::plural($this->name)),
                implode(',', array_map(function ($item) {
                    return "'" . $item["field"] . "'";
                }, $this->fillables))
            ],
            $this->getStub('Model')
        );
		(new Filesystem)->ensureDirectoryExists(app_path('Models'));
		
        file_put_contents(app_path("Models/{$this->name}.php"), $template);
    }

    /**
     * String replace the request.stub and makes Request.php
     */
    protected function livewire()
    {
        $template = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameLowerCase}}',
				'{{addfields}}',
				'{{updatefield}}',
				'{{editfields}}',
				'{{searchfield}}',
				'{{resetfields}}',
                '{{rules}}'
            ],
            [
                $this->name,
                strtolower(Str::plural($this->name)),
                strtolower($this->name),
                implode("," . PHP_EOL, array_map(function ($item) {
					return "\t\t\t'{$item['field']}' => \$this->{$item['field']}";
                }, $this->fillables)),
                implode(', ', array_map(function ($item) {
                    return "$" . $item["field"] . "";
                }, $this->fillables)),
                implode("" . PHP_EOL, array_map(function ($item) {
					return "\t\t\$this->{$item['field']} = \$record->{$item['field']};";
                }, $this->fillables)),
                implode("" . PHP_EOL, array_map(function ($item) {
					return "\t\t\t\t\t\t->orWhere('{$item['field']}', 'LIKE', \$keyWord)";
                }, $this->fillables)),
				implode("" . PHP_EOL, array_map(function ($item) {
					return "\t\t\$this->{$item['field']} = null;";
                }, $this->fillables)),
				implode("" . PHP_EOL, array_map(function ($item) {
                    $extend = $item['nullable'] ? 'nullable' : 'required';
                    return "\t\t\t'{$item['field']}' => '{$item['preset']["validation"]}|{$extend}',";
                }, $this->fillables))
            ],
            $this->getStub('Livewire')
        );
		(new Filesystem)->ensureDirectoryExists(app_path('Http/Livewire'));
        file_put_contents(app_path("/Http/Livewire/{$this->name}s.php"), $template);
    }

    /**
     * String replace the index.stub and makes index.blade.php
     */
    protected function indexBlade()
    {
        $template = str_replace(
            [
                '{{modelNamePluralLowerCase}}'
            ],
            [
                strtolower(Str::plural($this->name))
            ],

            $this->getStub('views/index')
        );
		(new Filesystem)->ensureDirectoryExists(resource_path('views/livewire'));
        if (!file_exists($path = resource_path('/views/livewire/' . strtolower(Str::plural($this->name)))))
            mkdir($path, 0777, true);

        file_put_contents(resource_path("views/livewire/" . strtolower(Str::plural($this->name)) . "/index.blade.php"), $template);
    }
    /**
     * String replace the view.stub and makes index.blade.php
     */
    protected function viewBlade()
    {
        $template = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePlural}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
                '{{dataHead}}',
                '{{dataBody}}'
            ],
            [
                $this->name,
                Str::plural($this->name),
                strtolower(Str::plural($this->name)),
                strtolower($this->name),
                implode("/", array_map(function ($item) {
					if ($item['preset']["input"] !=='textarea') {
						return '{{$row->' . $item["field"] . '}}';
					}
                }, $this->fillables)),
                implode(PHP_EOL, array_map(function ($item) {
					if ($item['preset']["input"] !=='textarea') {
						return "\t\t\t\t\t\t".'<div class="mx-3">'."\n\t\t\t\t\t\t\t".'<small>' . ucwords(str_replace('_', ' ', ($item['field']))) . '</small>'."\n\t\t\t\t\t\t\t".'<p>{{$row->' . $item["field"] . '}}</p>'."\n\t\t\t\t\t\t".'</div>';
					} 
                }, $this->fillables))
            ],
            $this->getStub('views/view')
        );
		(new Filesystem)->ensureDirectoryExists(resource_path('views/livewire'));
        if (!file_exists($path = resource_path('/views/livewire/' . strtolower(Str::plural($this->name)))))
            mkdir($path, 0777, true);

        file_put_contents(resource_path("views/livewire/" . strtolower(Str::plural($this->name)) . "/view.blade.php"), $template);
    }
	
    /**
     * String replace the view.stub and makes index.blade.php
     */
    protected function componentBlade()
    {
        $template = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePlural}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
                '{{tableHeader}}',
                '{{tableBody}}',
				'{{show}}'
            ],
            [
                $this->name,
                Str::plural($this->name),
                strtolower(Str::plural($this->name)),
                strtolower($this->name),
                implode(PHP_EOL, array_map(function ($item) {
                    return "\t\t\t<th>". ucwords(str_replace('_', ' ', ($item['field']))) ."</th>";
                }, $this->fillables)),
                implode(PHP_EOL, array_map(function ($item) {
                    return "\t\t\t".'<td>{{$row->' . $item["field"] . '}}</td>';
                }, $this->fillables)),
                implode("/", array_map(function ($item) {
					if ($item['preset']["input"] !=='textarea') {
						return "\t\t\t".'{{$row->' . $item['field'] . '}}';
					}
                }, $this->fillables))
            ],
            $this->getStub('views/component')
        );
		(new Filesystem)->ensureDirectoryExists(resource_path('views/livewire'));
        if (!file_exists($path = resource_path('/views/livewire/' . strtolower(Str::plural($this->name)))))
            mkdir($path, 0777, true);

        file_put_contents(resource_path("views/livewire/" . strtolower(Str::plural($this->name)) . "/component.blade.php"), $template);
    }

    /**
     * String replace the create.stub and makes create.blade.php
     */
    protected function createBlade()
    {
        $template = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
                '{{form}}'

            ],
            [
                $this->name,
                strtolower(Str::plural($this->name)),
                strtolower($this->name),
				implode(PHP_EOL, array_map(function ($item) {
                  if ($item['preset']["input"] =='select') {
					  return "\t\t\t".'<div class="form-group">'. "\n\t\t\t\t" . ucwords(str_replace('_', ' ', ($item['field']))) . "\n\t\t\t\t" .'<select wire:model="' . $item['field'] . '" class="form-control" id="' . $item['field'] . '">'. "\n\t\t\t\t" .'<option>Select '. $item['field'] .'</option>'. "\n\t\t\t\t" .' @foreach($'.strtolower(Str::plural($this->name)).' as $row)'. "\n\t\t\t\t" .'<option>{{ $row->'. $item['field'] .' }}</option>'. "\n\t\t\t\t" .'@endforeach '. "\n\t\t\t\t" .'</select>@error(\'' . $item['field'] . '\') <span class="error text-danger">{{ $message }}</span> @enderror'. "\n\t\t\t" .'</div>';
					} elseif ($item['preset']["input"] =='textarea') {
						return "\t\t\t".'<div class="form-group">'. "\n\t\t\t\t" . ucwords(str_replace('_', ' ', ($item['field']))) . "\n\t\t\t\t" .'<textarea wire:model="' . $item['field'] . '" class="form-control" id="' . $item['field'] . '" placeholder="' . ucwords(str_replace('_', ' ', ($item['field']))) . '"></textarea> @error(\'' . $item['field'] . '\') <span class="error text-danger">{{ $message }}</span> @enderror'. "\n\t\t\t" .'</div>';
					} else
					  return "\t\t\t".'<div class="form-group">'. "\n\t\t\t\t" . ucwords(str_replace('_', ' ', ($item['field']))) . "\n\t\t\t\t" .'<input wire:model="' . $item['field'] . '" type="' . $item['preset']["input"] . '" class="form-control" id="' . $item['field'] . '" placeholder="' . ucwords(str_replace('_', ' ', ($item['field']))) . '"> @error(\'' . $item['field'] . '\') <span class="error text-danger">{{ $message }}</span> @enderror'. "\n\t\t\t" .'</div>';
                }, $this->fillables))
            ],
            $this->getStub('views/create')
        );
		(new Filesystem)->ensureDirectoryExists(resource_path('views/livewire'));
        if (!file_exists($path = resource_path('/views/livewire/' . strtolower(Str::plural($this->name))))) {
            mkdir($path, 0777, true);
        }

        file_put_contents(resource_path("views/livewire/" . strtolower(Str::plural($this->name)) . "/create.blade.php"), $template);
    }

    /**
     * String replace the Edit.stub and makes edit.blade.php
     */
    protected function editBlade()
    {
        $template = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
                '{{form}}'
            ],
            [
                $this->name,
                strtolower(Str::plural($this->name)),
                strtolower($this->name),
                implode(PHP_EOL, array_map(function ($item) {
                  if ($item['preset']["input"] =='select') {
					  return "\t\t\t".'<div class="form-group">'. "\n\t\t\t\t" . ucwords(str_replace('_', ' ', ($item['field']))) . "\n\t\t\t\t" .'<select wire:model="' . $item['field'] . '" class="form-control" id="' . $item['field'] . '">'. "\n\t\t\t\t" .'<option>Select '. $item['field'] .'</option>'. "\n\t\t\t\t" .' @foreach($'.strtolower(Str::plural($this->name)).' as $row)'. "\n\t\t\t\t" .'<option>{{ $row->'. $item['field'] .' }}</option>'. "\n\t\t\t\t" .'@endforeach '. "\n\t\t\t\t" .'</select>@error(\'' . $item['field'] . '\') <span class="error text-danger">{{ $message }}</span> @enderror'. "\n\t\t\t" .'</div>';
					} elseif ($item['preset']["input"] =='textarea') {
						return "\t\t\t".'<div class="form-group">'. "\n\t\t\t\t" . ucwords(str_replace('_', ' ', ($item['field']))) . "\n\t\t\t\t" .'<textarea wire:model="' . $item['field'] . '" class="form-control" id="' . $item['field'] . '" placeholder="' . ucwords(str_replace('_', ' ', ($item['field']))) . '"></textarea> @error(\'' . $item['field'] . '\') <span class="error text-danger">{{ $message }}</span> @enderror'. "\n\t\t\t" .'</div>';
					} else
					  return "\t\t\t".'<div class="form-group">'. "\n\t\t\t\t" . ucwords(str_replace('_', ' ', ($item['field']))) . "\n\t\t\t\t" .'<input wire:model="' . $item['field'] . '" type="' . $item['preset']["input"] . '" class="form-control" id="' . $item['field'] . '" placeholder="' . ucwords(str_replace('_', ' ', ($item['field']))) . '"> @error(\'' . $item['field'] . '\') <span class="error text-danger">{{ $message }}</span> @enderror'. "\n\t\t\t" .'</div>';
                }, $this->fillables))
            ],
            $this->getStub('views/update')
        );
		(new Filesystem)->ensureDirectoryExists(resource_path('views/livewire'));
        if (!file_exists($path = resource_path('/views/livewire/' . strtolower(Str::plural($this->name))))) {
            mkdir($path, 0777, true);
        }
        file_put_contents(resource_path("views/livewire/" . strtolower(Str::plural($this->name)) . "/update.blade.php"), $template);
    }

    /**
     * String replace the Migration.stub and makes timestamp_create_names_table.php
     */
    protected function migration()
    {
        $template = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelNamePluralUpperCase}}',
                '{{fields}}'
            ],
            [
                $this->name,
                strtolower(Str::plural($this->name)),
                strtolower($this->name),
                ucfirst(strtolower(Str::plural($this->name))),
                $this->fields()
            ],
            $this->getStub('Migration')
        );
		(new Filesystem)->ensureDirectoryExists(database_path('migrations'));
        file_put_contents(database_path($this->formatMigrationName()), $template);
    }

    /**
     * Migrations fieldstrings are filled with preset, field & nullable
     */
    protected function fields()
    {
        $fields = "";
        foreach ($this->fillables as $f) {
            $fields .= "\n\t\t\t".'$table->' . $f['preset']["datatype"] . '(\'' . $f['field'] . '\')' . ($f['nullable'] ? '->nullable()' : '') . ';';
        }
        return $fields;
    }

    /**
     * Migrationsname is made with date_formats
     */
    protected function formatMigrationName()
    {
        $date = date_format(date_create(), "Y_m_d_His");
        return "migrations/{$date}_create_" . strtolower(Str::plural($this->name)) . "_table.php";
    }

    /**
     * String replace the Seeder.stub and makes NamesSeeder.php
     */
    protected function seeder()
    {
        $template = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralUpperCase}}',
                '{{quantity}}'
            ],
            [
                $this->name,
                ucfirst(strtolower(Str::plural($this->name))),
                $this->quantity
            ],
            $this->getStub('Seeder')
        );
		(new Filesystem)->ensureDirectoryExists(database_path('seeders'));
        file_put_contents(database_path('seeders/' . ucfirst(strtolower(Str::plural($this->name))) . 'TableSeeder.php'), $template);
    }

    /**
     * String replace the Faker.stub and makes NameFaker.php
     */
    protected function factory()
    {
        $template = str_replace(
            [
                '{{modelName}}',
                '{{fields}}'
            ],
            [
                $this->name,
                implode(',' . PHP_EOL, array_map(function ($item) {
                    if ($item['preset']["factory"] != null)
                        return "\t\t\t'" . $item["field"] . '\' => $this->faker->' . $item['preset']["factory"];
                    return false;
                }, $this->fillables))
            ],
            $this->getStub('Factory')
        );
        file_put_contents(database_path("/factories/{$this->name}Factory.php"), $template);
    }

    /**
     * Checks if the command is called from the cmd
     * @param $input
     * @return array|void
     */
    protected function loadDataFromCmd() {
        $fillables = [];
        do {
            $field = $this->ask('Field name');
            if (strtolower($field) !== 'done' && strtolower($field) !== "stop" && $field != null) {

                $fillables[] = [
                    'field' => strtolower(str_replace(' ', '_', $field)),
                    'preset' => $this->presets[$this->choice('Preset type', array_keys($this->presets))],
                    'nullable' => $this->confirm('Nullable?')
                ];

                $count = count($fillables);
                $this->info("$field successfully added. Currently {$count} fields added. Type \"done\" when finished.");
            };

            if ($field == null) {
                $this->warn('Invalid field name');
            }
        } while (strtolower($field) !== 'done' && strtolower($field) !== "stop");
        return $fillables;
    }
}


