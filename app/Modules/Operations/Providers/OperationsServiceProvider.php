<?php

declare(strict_types=1);

namespace App\Modules\Operations\Providers;

use App\Modules\Operations\Models\Project;
use App\Modules\Operations\Models\Task;
use App\Modules\Operations\Models\TaskComment;
use App\Modules\Operations\Observers\TaskObserver;
use App\Modules\Operations\Policies\ProjectPolicy;
use App\Modules\Operations\Policies\TaskCommentPolicy;
use App\Modules\Operations\Policies\TaskPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class OperationsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(TaskComment::class, TaskCommentPolicy::class);

        Task::observe(TaskObserver::class);
    }
}
