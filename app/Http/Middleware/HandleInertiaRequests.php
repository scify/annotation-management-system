<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\PermissionsEnum;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware {
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'app' => [
                'name' => config()->string('app.name'),
                'version' => config()->string('app.version'),
                'env' => config()->string('app.env'),
                'locale' => app()->getLocale(),
            ],
            'quote' => ['message' => mb_trim($message ?? ''), 'author' => mb_trim($author ?? '')],
            'translations' => fn (): array => [
                'auth' => trans('auth'),
                'pagination' => trans('pagination'),
                'passwords' => trans('passwords'),
                'users' => trans('users'),
                'roles' => trans('roles'),
                'validation' => trans('validation'),
                'common' => trans('common'),
                'dashboard' => trans('dashboard'),
                'client-applications' => trans('client-applications'),
                'navbar' => trans('navbar'),
                'settings' => trans('settings'),
                // add other translation files as needed
            ],
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'created_at' => $request->user()->created_at,
                    'updated_at' => $request->user()->updated_at,
                    'deleted_at' => $request->user()->deleted_at,
                    'can' => collect(PermissionsEnum::cases())
                        ->mapWithKeys(fn (PermissionsEnum $permissionsEnum): array => [
                            str_replace(' ', '_', $permissionsEnum->value) => $request->user()->can($permissionsEnum->value),
                            str_replace('-', '_', $permissionsEnum->value) => $request->user()->can($permissionsEnum->value),
                        ])
                        ->all(),
                ] : null,
            ],
            'ziggy' => fn (): array => [
                ...new Ziggy()->toArray(),
                'location' => $request->url(),
            ],
            'flash' => fn (): array => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
                'info' => $request->session()->get('info'),
            ],
        ];
    }
}
