<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class MakeModule extends Command
{
    protected $signature = 'make:module {name} 
                            {--all : Create all components without prompts}
                            {--model : Create model}
                            {--migration : Create migration}
                            {--repository : Create repository}
                            {--service : Create service}
                            {--controller : Create controller}
                            {--requests : Create form requests}
                            {--transformer : Create transformer}
                            {--factory : Create factory}';
                            
    protected $description = 'Create full modular API V1 structure (Model, Repository, Service, Controller, Requests, Transformer, Migration, Factory)';

    protected string $moduleName;
    protected string $tableName;

    public function handle()
    {
        $this->moduleName = ucfirst($this->argument('name'));
        $this->tableName = Str::plural(Str::snake($this->moduleName));

        $this->createDirectories();

        $all = $this->option('all');

        if ($all || $this->option('model') || $this->shouldCreate('Model')) {
            $this->createModel();
        }

        if ($all || $this->option('migration') || $this->shouldCreate('Migration')) {
            $this->createMigration();
        }

        if ($all || $this->option('factory') || $this->shouldCreate('Factory')) {
            $this->createFactory();
        }

        if ($all || $this->option('repository') || $this->shouldCreate('Repository')) {
            $this->createRepository();
        }

        if ($all || $this->option('service') || $this->shouldCreate('Service')) {
            $this->createService();
        }

        if ($all || $this->option('controller') || $this->shouldCreate('Controller')) {
            $this->createController();
        }

        if ($all || $this->option('requests') || $this->shouldCreate('Requests')) {
            $this->createRequests();
        }

        if ($all || $this->option('transformer') || $this->shouldCreate('Transformer')) {
            $this->createTransformer();
        }

        $this->info("✅ {$this->moduleName} module created successfully!");
        $this->newLine();
        $this->warn("📝 Don't forget to:");
        $this->line("   1. Add repository binding to App\\Providers\\RepositoryServiceProvider");
        $this->line("   2. Add routes to routes/api.php");
        $this->line("   3. Run: php artisan migrate");
    }

    protected function shouldCreate(string $component): bool
    {
        return !$this->hasAnyComponentOption() && $this->confirm("Create {$component}?", true);
    }

    protected function hasAnyComponentOption(): bool
    {
        return $this->option('model') || $this->option('migration') || $this->option('repository') 
            || $this->option('service') || $this->option('controller') || $this->option('requests') 
            || $this->option('transformer') || $this->option('factory');
    }

    /* ---------------- DIRECTORIES ---------------- */

    protected function createDirectories(): void
    {
        $paths = [
            app_path("Repositories/{$this->moduleName}/Eloquent"),
            app_path("Services/{$this->moduleName}"),
            app_path("Transformers/{$this->moduleName}"),
            app_path("Http/Controllers/Api/V1"),
            app_path("Http/Requests/Api/V1/{$this->moduleName}"),
        ];

        foreach ($paths as $path) {
            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }
        }
    }

    /* ---------------- MODEL ---------------- */

    protected function getStub(string  $name): string{
        return file_get_contents( base_path("stubs/make-module/{$name}.stub")); 
    }

    protected function renderStub(string $stub, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $stub = str_replace("{{ {$key} }}", $value, $stub);
        }
        return $stub;
    }    
    protected function createModel(): void
    {
        $modelPath = app_path("Models/{$this->moduleName}.php");
        
        if (File::exists($modelPath)) {
            $this->warn("Model {$this->moduleName} already exists, skipping...");
            return;
        }
        $stub = $this->getStub('model');

        $content = $this->renderStub($stub, [
        'namespace' => 'App\Models',
        'class'     => $this->moduleName,
    ]);
        File::put($modelPath, $content);
        
        $this->info("✓ Model created: app/Models/{$this->moduleName}.php");
    }
    /* ---------------- MIGRATION ---------------- */
    protected function createMigration(): void
    {
        Artisan::call("make:migration create_{$this->tableName}_table --create={$this->tableName}");
        $this->info("✓ Migration created for table: {$this->tableName}");
    }
    /* ---------------- FACTORY ---------------- */
    protected function createFactory(): void
    {
        $factoryPath = database_path("factories/{$this->moduleName}Factory.php");
        if (File::exists($factoryPath)) {
            $this->warn("Factory {$this->moduleName}Factory already exists, skipping...");
            return;
        }
        $name = $this->moduleName;
        $stub = $this->getStub('factory');
        $content = $this->renderStub($stub, [
            'namespace' => "Database\Factories",
            'class'     => "{$name}Factory",
        ]);
        File::put($factoryPath, $content);   
        $this->info("✓ Factory created: database/factories/{$this->moduleName}Factory.php");
    }
    /* ---------------- REPOSITORY ---------------- */
   protected function createRepository(): void
{
    $module = $this->moduleName;
    $interfacePath = app_path("Repositories/{$module}/{$module}Repository.php");
    $eloquentPath  = app_path("Repositories/{$module}/Eloquent/Eloquent{$module}Repository.php");
    // ---------------- Interface ----------------
    if (File::exists($interfacePath)) {
        $this->warn("Repository interface already exists, skipping...");
    } else {
        $stub = $this->getStub('repository.interface');
        $content = $this->renderStub($stub, [
            'namespace' => "App\Repositories\\{$module}",
            'interface' => "{$module}Repository",
        ]);
        File::put($interfacePath, $content);
        $this->info("✓ Repository interface created");
    }
    // ---------------- Eloquent Implementation ----------------
    if (File::exists($eloquentPath)) {
        $this->warn("Eloquent repository already exists, skipping...");
    } else {
        $stub = $this->getStub('repository.eloquent');

        $content = $this->renderStub($stub, [
            'namespace' => "App\Repositories\\{$module}\\Eloquent",
            'class'     => "Eloquent{$module}Repository",
            'interface' => "{$module}Repository",
            'module'    => $module,
            'model'     => $module,
        ]);
        File::put($eloquentPath, $content);
        $this->info("✓ Eloquent repository created");
    }
}
    /* ---------------- SERVICE ---------------- */
  protected function createService(): void
{
    $module = $this->moduleName;
    $servicePath = app_path("Services/{$module}/{$module}Service.php");
    if (File::exists($servicePath)) {
        $this->warn("Service already exists, skipping...");
        return;
    }
    $stub = $this->getStub('service');
    $content = $this->renderStub($stub, [
        'namespace' => "App\Services\\{$module}",
        'class'     => "{$module}Service",
        'module'    => $module,
        'interface' => "{$module}Repository",
    ]);
    File::put($servicePath, $content);
    $this->info("✓ Service created");
}
    /* ---------------- CONTROLLER ---------------- */
    protected function createController(): void
    {
        $controllerPath = app_path("Http/Controllers/Api/V1/{$this->moduleName}Controller.php");
        
        if (File::exists($controllerPath)) {
            $this->warn("Controller already exists, skipping...");
            return;
        }
        $name = $this->moduleName;
        $varName = Str::camel($this->moduleName);
        $stub = $this->getStub('controller');
        $content = $this->renderStub($stub, [
            'namespace' => "App\Http\Controllers\Api\V1",
            'class'     => "{$name}Controller",
            'service'   => "{$name}Service",
            'varName'   => $varName,
            'module'    => $name,
        ]);
        File::put($controllerPath, $content);
        $this->info("✓ Controller created: app/Http/Controllers/Api/V1/{$this->moduleName}Controller.php");
    }
    /* ---------------- REQUESTS ---------------- */
    protected function createRequests(): void
    {
        $createPath = app_path("Http/Requests/Api/V1/{$this->moduleName}/Create{$this->moduleName}Request.php");
        $updatePath = app_path("Http/Requests/Api/V1/{$this->moduleName}/Update{$this->moduleName}Request.php");
        $name = $this->moduleName;
        if (!File::exists($createPath)) {
        $stub = $this->getStub('request.create');    
        File::put($createPath, 
            $this->renderStub($stub, [
                'namespace' => "App\Http\Requests\Api\V1\\{$name}",
                'class'     => "Create{$name}Request",
                'module'    => $name,
            ])
        );
            $this->info("✓ Create request created: app/Http/Requests/Api/V1/{$this->moduleName}/Create{$this->moduleName}Request.php");
        } else {
            $this->warn("Create request already exists, skipping...");
        }
        if (!File::exists($updatePath)) {
        $stub = $this->getStub('request.update');       
        File::put($updatePath,
                $this->renderStub($stub, [
                    'namespace' => "App\Http\Requests\Api\V1\\{$name}",
                    'class'     => "Update{$name}Request",
                    'module'    => $name,
                ])
            );
            $this->info("✓ Update request created: app/Http/Requests/Api/V1/{$this->moduleName}/Update{$this->moduleName}Request.php");
        } else {
            $this->warn("Update request already exists, skipping...");
        }
    }
    /* ---------------- TRANSFORMER ---------------- */
    protected function createTransformer(): void
    {
        $transformerPath = app_path("Transformers/{$this->moduleName}/{$this->moduleName}Transformer.php");
        if (File::exists($transformerPath)) {
            $this->warn("Transformer already exists, skipping...");
            return;
        }
        $name = $this->moduleName;
        $stub = $this->getStub('transformer');
        File::put($transformerPath, 
            $this->renderStub($stub, [
                'namespace' => "App\Transformers\\{$name}",
                'class'     => "{$name}Transformer",
                'module'    => $name,
            ])
        );
        $this->info("✓ Transformer created: app/Transformers/{$this->moduleName}/{$this->moduleName}Transformer.php");
    }
}
