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

    protected function createModel(): void
    {
        $modelPath = app_path("Models/{$this->moduleName}.php");
        
        if (File::exists($modelPath)) {
            $this->warn("Model {$this->moduleName} already exists, skipping...");
            return;
        }

        $name = $this->moduleName;
        File::put($modelPath, <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {$name} extends Model
{
    use HasFactory;

    protected \$fillable = [
        // Add fillable fields
    ];

    protected function casts(): array
    {
        return [
            // Add casts
        ];
    }
}
PHP
        );
        
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
        File::put($factoryPath, <<<PHP
<?php

namespace Database\Factories;

use App\Models\\{$name};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\\{$name}>
 */
class {$name}Factory extends Factory
{
    protected \$model = {$name}::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Add factory definitions
        ];
    }
}
PHP
        );
        
        $this->info("✓ Factory created: database/factories/{$this->moduleName}Factory.php");
    }

    /* ---------------- REPOSITORY ---------------- */

    protected function createRepository(): void
    {
        $interfacePath = app_path("Repositories/{$this->moduleName}/{$this->moduleName}Repository.php");
        $eloquentPath = app_path("Repositories/{$this->moduleName}/Eloquent/Eloquent{$this->moduleName}Repository.php");
        
        $name = $this->moduleName;

        if (File::exists($interfacePath)) {
            $this->warn("Repository interface already exists, skipping...");
        } else {
            // Interface
            File::put($interfacePath, <<<PHP
<?php

namespace App\Repositories\\{$name};

interface {$name}Repository
{
    public function all();
    public function find(\$id);
    public function create(array \$data);
    public function update(\$model, array \$data);
    public function delete(\$model);
}
PHP
            );
            $this->info("✓ Repository interface created: app/Repositories/{$this->moduleName}/{$this->moduleName}Repository.php");
        }

        if (File::exists($eloquentPath)) {
            $this->warn("Eloquent repository already exists, skipping...");
        } else {
            // Eloquent Implementation
            File::put($eloquentPath, <<<PHP
<?php

namespace App\Repositories\\{$name}\\Eloquent;

use App\Models\\{$name};
use App\Repositories\\{$name}\\{$name}Repository;

class Eloquent{$name}Repository implements {$name}Repository
{
    public function __construct(protected {$name} \$model) {}

    public function all()
    {
        return \$this->model->query()->latest()->get();
    }

    public function find(\$id)
    {
        return \$this->model->findOrFail(\$id);
    }

    public function create(array \$data)
    {
        return \$this->model->create(\$data);
    }

    public function update(\$model, array \$data)
    {
        \$model->update(\$data);
        return \$model;
    }

    public function delete(\$model)
    {
        return \$model->delete();
    }
}
PHP
            );
            $this->info("✓ Eloquent repository created: app/Repositories/{$this->moduleName}/Eloquent/Eloquent{$this->moduleName}Repository.php");
        }
    }

    /* ---------------- SERVICE ---------------- */

    protected function createService(): void
    {
        $servicePath = app_path("Services/{$this->moduleName}/{$this->moduleName}Service.php");
        
        if (File::exists($servicePath)) {
            $this->warn("Service already exists, skipping...");
            return;
        }

        $name = $this->moduleName;
        File::put($servicePath, <<<PHP
<?php

namespace App\Services\\{$name};

use App\Repositories\\{$name}\\{$name}Repository;

class {$name}Service
{
    public function __construct(
        protected {$name}Repository \$repository
    ) {}

    public function all()
    {
        return \$this->repository->all();
    }

    public function find(\$id)
    {
        return \$this->repository->find(\$id);
    }

    public function create(array \$data)
    {
        return \$this->repository->create(\$data);
    }

    public function update(\$model, array \$data)
    {
        return \$this->repository->update(\$model, \$data);
    }

    public function delete(\$model)
    {
        return \$this->repository->delete(\$model);
    }
}
PHP
        );
        
        $this->info("✓ Service created: app/Services/{$this->moduleName}/{$this->moduleName}Service.php");
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

        File::put($controllerPath, <<<PHP
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\\{$name}\Create{$name}Request;
use App\Http\Requests\Api\V1\\{$name}\Update{$name}Request;
use App\Services\\{$name}\\{$name}Service;
use App\Transformers\\{$name}\\{$name}Transformer;
use Illuminate\Http\JsonResponse;

class {$name}Controller extends Controller
{
    public function __construct(
        protected {$name}Service \$service
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        \${$varName}s = \$this->service->all();
        
        return response()->json([
            'data' => {$name}Transformer::collection(\${$varName}s),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Create{$name}Request \$request): JsonResponse
    {
        \${$varName} = \$this->service->create(\$request->validated());
        
        return response()->json([
            'message' => '{$name} created successfully',
            'data' => new {$name}Transformer(\${$varName}),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int \$id): JsonResponse
    {
        \${$varName} = \$this->service->find(\$id);
        
        return response()->json([
            'data' => new {$name}Transformer(\${$varName}),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Update{$name}Request \$request, int \$id): JsonResponse
    {
        \${$varName} = \$this->service->find(\$id);
        \$updated = \$this->service->update(\${$varName}, \$request->validated());
        
        return response()->json([
            'message' => '{$name} updated successfully',
            'data' => new {$name}Transformer(\$updated),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int \$id): JsonResponse
    {
        \${$varName} = \$this->service->find(\$id);
        \$this->service->delete(\${$varName});
        
        return response()->json([
            'message' => '{$name} deleted successfully',
        ]);
    }
}
PHP
        );
        
        $this->info("✓ Controller created: app/Http/Controllers/Api/V1/{$this->moduleName}Controller.php");
    }

    /* ---------------- REQUESTS ---------------- */

    protected function createRequests(): void
    {
        $createPath = app_path("Http/Requests/Api/V1/{$this->moduleName}/Create{$this->moduleName}Request.php");
        $updatePath = app_path("Http/Requests/Api/V1/{$this->moduleName}/Update{$this->moduleName}Request.php");

        $name = $this->moduleName;

        if (!File::exists($createPath)) {
            File::put($createPath, <<<PHP
<?php

namespace App\Http\Requests\Api\V1\\{$name};

use Illuminate\Foundation\Http\FormRequest;

class Create{$name}Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Add validation rules
        ];
    }
}
PHP
            );
            $this->info("✓ Create request created: app/Http/Requests/Api/V1/{$this->moduleName}/Create{$this->moduleName}Request.php");
        } else {
            $this->warn("Create request already exists, skipping...");
        }

        if (!File::exists($updatePath)) {
            File::put($updatePath, <<<PHP
<?php

namespace App\Http\Requests\Api\V1\\{$name};

use Illuminate\Foundation\Http\FormRequest;

class Update{$name}Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Add validation rules
        ];
    }
}
PHP
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
        File::put($transformerPath, <<<PHP
<?php

namespace App\Transformers\\{$name};

use Illuminate\Http\Resources\Json\JsonResource;

class {$name}Transformer extends JsonResource
{
    public function toArray(\$request): array
    {
        return [
            'id' => \$this->id,
            // Add more fields
            'created_at' => \$this->created_at?->toISOString(),
            'updated_at' => \$this->updated_at?->toISOString(),
        ];
    }
}
PHP
        );
        
        $this->info("✓ Transformer created: app/Transformers/{$this->moduleName}/{$this->moduleName}Transformer.php");
    }
}
