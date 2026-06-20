<?php

namespace Zerofyi\LaraContext\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

class GenerateAIContext extends Command
{
    protected $signature = 'context:generate 
                            {--output=ai-context.md : The output filename} 
                            {--no-routes : Skip adding application and API routes to the context}';
                            
    protected $description = 'Generate a high-density, rock-solid architectural context map for AI models';

    public function handle(): int
    {
        $this->line('');
        $this->info('🚀 Compiling high-density application blueprint via LaraContext...');
        $this->line('');

        try {
            $markdown = $this->buildHeader();
            $markdown .= $this->compileModels();

            if ($this->option('no-routes')) {
                $this->comment('ℹ️ Skipping route compilation due to --no-routes flag.');
            } else {
                $markdown .= $this->compileRoutes();
            }

            $filename = $this->option('output');
            $outputPath = base_path($filename);
            File::put($outputPath, $markdown);

            $this->info("✨ Context saved successfully to: {$outputPath}");
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error('❌ Engine execution encountered a fatal failure: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function buildHeader(): string
    {
        return implode("\n", [
            "# Application Context Blueprint",
            "Generated: " . now()->toDateTimeString(),
            "Framework: Laravel v" . app()->version(),
            "Environment: PHP v" . PHP_VERSION,
            "--------------------------------------------------",
            "INSTRUCTION FOR AI: This file contains the structural architecture of the application.",
            "Use this schema map, endpoint list, and relationships to understand context before writing code.",
            "\n"
        ]);
    }

    private function compileModels(): string
    {
        $output = "## 1. Database Schemas & Eloquent Models\n\n";
        $modelPath = app_path('Models');

        if (!File::exists($modelPath)) {
            return $output . "No models detected in default `app/Models` directory.\n\n";
        }

        $files = File::files($modelPath);

        foreach ($files as $file) {
            $modelName = $file->getFilenameWithoutExtension();
            $fullClassName = "App\\Models\\{$modelName}";

            if (!class_exists($fullClassName)) {
                continue;
            }

            $output .= "### Model: `{$modelName}`\n";

            try {
                $instance = new $fullClassName;
                $table = $instance->getTable();
                $output .= "- **Table:** `{$table}`\n";

                if (Schema::hasTable($table)) {
                    $columns = Schema::getColumnListing($table);
                    $colDetails = [];
                    foreach ($columns as $column) {
                        $type = Schema::getColumnType($table, $column);
                        $colDetails[] = "`{$column}` ({$type})";
                    }
                    $output .= "- **Columns:** " . implode(', ', $colDetails) . "\n";
                }
            } catch (Throwable $e) {
                $output .= "- **Columns:** (Database connection unavailable or unmigrated table)\n";
            }

            $relations = $this->getModelRelationships($fullClassName);
            if (!empty($relations)) {
                $output .= "- **Relationships:** " . implode(', ', $relations) . "\n";
            }

            $output .= "\n";
        }

        return $output;
    }

    private function getModelRelationships(string $className): array
    {
        $relationships = [];
        try {
            $reflection = new ReflectionClass($className);
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                if ($method->class !== $className || $method->getNumberOfParameters() > 0) {
                    continue;
                }

                $methodName = $method->getName();
                $filename = $method->getFileName();
                if ($filename) {
                    $fileLines = file($filename);
                    $start = $method->getStartLine() - 1;
                    $end = $method->getEndLine();
                    $methodBody = implode('', array_slice($fileLines, $start, $end - $start));

                    $relationTypes = ['hasMany', 'belongsTo', 'hasOne', 'belongsToMany', 'morphTo', 'morphMany', 'hasManyThrough'];
                    foreach ($relationTypes as $type) {
                        if (Str::contains($methodBody, '$this->' . $type)) {
                            $relationships[] = "`{$methodName}()` [{$type}]";
                            break;
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            // Safe fallback
        }

        return $relationships;
    }

    private function compileRoutes(): string
    {
        $output = "## 2. API & Application Routing\n\n";
        $output .= "| Method | URI | Action | Validation / Request Form |\n";
        $output .= "|--------|-----|--------|----------------------------|\n";

        $routes = Route::getRoutes();

        foreach ($routes as $route) {
            $uri = $route->uri();

            if (Str::startsWith($uri, '_') || Str::contains($uri, 'sanctum')) {
                continue;
            }

            $methods = implode('|', array_diff($route->methods(), ['HEAD']));
            $action = $route->getActionName();
            $validationRules = $this->getFormRequestValidation($action);
            $actionString = $action !== 'Closure' ? "`" . class_basename($action) . "`" : "*Closure*";

            $output .= "| {$methods} | `/{$uri}` | {$actionString} | {$validationRules} |\n";
        }

        return $output;
    }

    private function getFormRequestValidation(string $action): string
    {
        if ($action === 'Closure' || !Str::contains($action, '@')) {
            return 'None';
        }

        list($controller, $methodName) = explode('@', $action);

        if (!class_exists($controller)) {
            return 'None';
        }

        try {
            $reflectionMethod = new ReflectionMethod($controller, $methodName);
            foreach ($reflectionMethod->getParameters() as $param) {
                $type = $param->getType();
                if ($type && !$type->isBuiltin()) {
                    $paramClassName = $type->getName();
                    
                    if (is_subclass_of($paramClassName, 'Illuminate\Foundation\Http\FormRequest')) {
                        if (method_exists($paramClassName, 'rules')) {
                            try {
                                $requestInstance = app($paramClassName);
                                $rules = $requestInstance->rules();
                                
                                $flatRules = [];
                                foreach ($rules as $field => $rule) {
                                    $ruleString = is_array($rule) ? implode('|', array_filter(array_map(fn($r) => is_string($r) ? $r : '', $rule))) : (string)$rule;
                                    $flatRules[] = "{$field}: [{$ruleString}]";
                                }
                                return !empty($flatRules) ? '`' . implode(', ', $flatRules) . '`' : 'Custom Request';
                            } catch (Throwable $t) {
                                return '`' . class_basename($paramClassName) . '`';
                            }
                        }
                        return '`' . class_basename($paramClassName) . '`';
                    }
                }
            }
        } catch (Throwable $e) {
            // Handled gracefully
        }

        return 'None';
    }
}