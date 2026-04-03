<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\User\UserCreateRequest;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Requests\User\UserViewRequest;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller {
    use AuthorizesRequests;

    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Display a listing of users.
     */
    public function index(UserViewRequest $request): Response {
        $this->authorize('view', User::class);

        /** @var User $currentUser */
        $currentUser = $request->user();

        $search = $request->query('search');

        $users = $this->userService->getUsers(search: $search);

        $canRestore = $currentUser->can('restore', User::class);

        return Inertia::render('users/index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
            ],
            'abilities' => $users->mapWithKeys(fn (User $listedUser): array => [
                $listedUser->id => [
                    'update' => $currentUser->can('update', $listedUser),
                    'delete' => $currentUser->can('delete', $listedUser),
                    'restore' => $canRestore,
                ],
            ]),
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(UserCreateRequest $request): Response {
        // $this->authorize('create', [User::class, $type]);
        $request->authorize();
        // $inputData is an array of key-value pairs that has been validated by UserCreateRequest, so we can safely use it here.
        // we should use ->validated() instead of ->all() to ensure we only get the validated data.
        // for example, if an attacker tries to inject additional fields via HTML or JS, they won't be included in $inputData.
        $request->validated();

        return Inertia::render('users/create', [
            'roles' => $this->userService->getRolesForForm(),
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(UserStoreRequest $userStoreRequest): RedirectResponse {
        $this->authorize('create', User::class);

        $this->userService->create($userStoreRequest->validated());

        return to_route('users.index')
            ->with('success', __('users.messages.created'));
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): Response {

        $this->authorize('view', $user);

        return Inertia::render('users/show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): Response {
        $this->authorize('update', $user);

        return Inertia::render('users/edit', [
            'user' => $user,
            'roles' => $this->userService->getRolesForForm(),
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(UserUpdateRequest $userUpdateRequest, User $user): RedirectResponse {
        $this->authorize('update', $user);

        $this->userService->update($user, $userUpdateRequest->validated());

        return to_route('users.index')
            ->with('success', __('users.messages.updated'));
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): RedirectResponse {
        $this->authorize('delete', $user);

        $this->userService->delete($user);

        return to_route('users.index')
            ->with('success', __('users.messages.deleted'));
    }
}
