<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RolesEnum;
use App\Http\Requests\User\UserCreateRequest;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Requests\User\UserViewRequest;
use App\Models\User;
use App\Services\User\UserManagementService;
use App\Services\User\UserService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller {
    use AuthorizesRequests;

    public function __construct(
        private readonly UserService $userService,
        private readonly UserManagementService $userManagementService,
    ) {}

    /**
     * Display a listing of users.
     */
    public function index(UserViewRequest $request): Response {
        /** @var User $currentUser */
        $currentUser = $request->user();

        $search = $request->query('search');

        $users = $this->userService->getUsers(search: $search);

        $management = $this->userManagementService->getUsersByRole($currentUser);

        $json = json_encode($management, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (is_string($json)) {
            Storage::disk('local')->put('user-management-data.json', $json);
        }

        $canRestore = $currentUser->can('restore', User::class);

        return Inertia::render('users/index', [
            'users' => $users,
            'management' => $management,
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
        $request->authorize();
        $request->validated();

        /** @var User $currentUser */
        $currentUser = $request->user();

        /** @var RolesEnum $type */
        $type = $request->enum('type', RolesEnum::class);

        $props = match ($type) {
            RolesEnum::ADMIN => [
                'type' => RolesEnum::ADMIN->value,
                'admin_data' => $this->userManagementService->getAdminDataForCreate($currentUser),
            ],
            RolesEnum::ANNOTATION_MANAGER => [
                'type' => RolesEnum::ANNOTATION_MANAGER->value,
                'manager_data' => $this->userManagementService->getManagerDataForCreate($currentUser),
            ],
            RolesEnum::ANNOTATOR => [
                'type' => RolesEnum::ANNOTATOR->value,
                'roles' => $this->userService->getRolesForForm(),
            ],
        };

        $json = json_encode($props, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (is_string($json)) {
            Storage::disk('local')->put('user-management-create-user-data-' . $type->value . '.json', $json);
        }

        return Inertia::render('users/create', $props);
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
