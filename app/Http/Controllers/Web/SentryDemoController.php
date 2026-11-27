<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use RuntimeException;
use Sentry\State\Scope;

use function Sentry\configureScope;

class SentryDemoController extends Controller
{
    public function __invoke(Request $request): never
    {
        abort_unless(app()->isLocal() && config('app.sentry_demo_enabled'), 404);
        $user = $request->user();
        configureScope(function (Scope $scope) use ($user): void {
            $context = ['id' => (string) $user->id];
            if (str_ends_with($user->email, '.test')) {
                $context['email'] = $user->email;
            }
            $scope->setUser($context);
            $scope->setTag('scenario', 'lab9-demo');
        });

        throw new RuntimeException('Тестовий виняток Laravel для лабораторної 9');
    }
}
