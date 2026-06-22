<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RolesEnum;
use App\Exceptions\PresentableError;
use App\Http\Requests\User\ConnectAnnotatorsToManagerRequest;
use App\Http\Requests\User\UserCreateRequest;
use App\Http\Requests\User\UserStoreAdminRequest;
use App\Http\Requests\User\UserStoreAnnotatorRequest;
use App\Http\Requests\User\UserStoreManagerRequest;
use App\Http\Requests\User\UserUpdateAdminRequest;
use App\Http\Requests\User\UserUpdateAnnotatorRequest;
use App\Http\Requests\User\UserUpdateManagerRequest;
use App\Http\Requests\User\UserViewRequest;
use App\Models\User;
use App\Services\User\UserManagementService;
use App\Services\User\UserService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $this->dumpDebugJson($management, 'user-management-data.json');

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
                'admin_data' => $this->userManagementService->getDataForCreateNewAdmin($currentUser),
            ],
            RolesEnum::ANNOTATION_MANAGER => [
                'type' => RolesEnum::ANNOTATION_MANAGER->value,
                'manager_data' => $this->userManagementService->getDataForCreateNewManager($currentUser),
                ...$this->managerPrefillProps($request),
            ],
            RolesEnum::ANNOTATOR => [
                'type' => RolesEnum::ANNOTATOR->value,
                'annotator_data' => $this->userManagementService->getDataForCreateNewAnnotator(),
            ],
        };

        $this->dumpDebugJson($props, 'user-management-create-user-data-' . $type->value . '.json');

        return Inertia::render('users/create', $props);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): RedirectResponse {
        $type = $request->enum('type', RolesEnum::class);

        abort_if($type === null, 422);

        $requestClass = match ($type) {
            RolesEnum::ADMIN => UserStoreAdminRequest::class,
            RolesEnum::ANNOTATION_MANAGER => UserStoreManagerRequest::class,
            RolesEnum::ANNOTATOR => UserStoreAnnotatorRequest::class,
        };

        /** @var UserStoreAdminRequest|UserStoreAnnotatorRequest|UserStoreManagerRequest $storeRequest */
        $storeRequest = resolve($requestClass);

        /** @var User $creator */
        $creator = $request->user();

        try {
            $this->userService->create(
                array_merge($storeRequest->validated(), ['role' => $type->value]),
                $creator,
            );
        } catch (PresentableError $presentableError) {
            return back()->with('error', $presentableError->getUserMessage());
        }

        return to_route('users.index')
            ->with('success', __('users.messages.created'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(Request $request, User $user): Response {
        $this->authorize('update', $user);

        /** @var User $currentUser */
        $currentUser = $request->user();

        $roleValue = $user->getRoleNames()->first();
        $role = is_string($roleValue) ? RolesEnum::tryFrom($roleValue) : null;

        abort_if($role === null, 422);

        $props = match ($role) {
            RolesEnum::ADMIN => [
                'type' => RolesEnum::ADMIN->value,
                'admin_data' => $this->userManagementService->getDataForEditAdmin($currentUser, $user),
            ],
            RolesEnum::ANNOTATION_MANAGER => [
                'type' => RolesEnum::ANNOTATION_MANAGER->value,
                'manager_data' => $this->userManagementService->getDataForEditManager($currentUser, $user),
            ],
            RolesEnum::ANNOTATOR => [
                'type' => RolesEnum::ANNOTATOR->value,
                'annotator_data' => $this->userManagementService->getDataForEditAnnotator($user),
            ],
        };

        $props['can_delete'] = $user->id !== $currentUser->id && $currentUser->can('delete', $user);

        $this->dumpDebugJson($props, 'user-management-edit-user-data-' . $role->value . '.json');

        return Inertia::render('users/edit', $props);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): RedirectResponse {
        $this->authorize('update', $user);

        $type = $request->enum('type', RolesEnum::class);

        abort_if($type === null, 422);

        $requestClass = match ($type) {
            RolesEnum::ADMIN => UserUpdateAdminRequest::class,
            RolesEnum::ANNOTATION_MANAGER => UserUpdateManagerRequest::class,
            RolesEnum::ANNOTATOR => UserUpdateAnnotatorRequest::class,
        };

        /** @var UserUpdateAdminRequest|UserUpdateAnnotatorRequest|UserUpdateManagerRequest $updateRequest */
        $updateRequest = resolve($requestClass);

        try {
            $this->userService->update($user, array_merge($updateRequest->validated(), ['role' => $type->value]));
        } catch (PresentableError $presentableError) {
            return back()->with('error', $presentableError->getUserMessage());
        }

        return to_route('users.index')
            ->with('success', __('users.messages.updated'));
    }

    public function showConnectAnnotators(Request $request, User $user): Response {
        $this->authorize('connectAnnotators', User::class);

        /** @var User $currentUser */
        $currentUser = $request->user();

        $data = $this->userManagementService->getDataForConnectAnnotators($user, $currentUser);

        $this->dumpDebugJson($data, 'user-connect-annotators-data.json');

        return Inertia::render('users/connect-annotators', $data);
    }

    public function connectAnnotators(ConnectAnnotatorsToManagerRequest $request, User $user): RedirectResponse {
        /** @var array<int, int> $annotatorIds */
        $annotatorIds = $request->validated('annotator_ids');

        $this->userManagementService->connectAnnotatorsToManager($user->id, $annotatorIds);

        return to_route('users.index')->with('success', __('users.messages.annotators_connected'));
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): RedirectResponse {
        $this->authorize('delete', $user);

        $this->userService->handleDelete($user);

        return to_route('users.index')
            ->with('success', __('users.messages.deleted'));
    }

    /**
     * Optional pre-fill props for the create-manager form, sourced from query
     * params when arriving from a co-manager invite that matched no existing user.
     *
     * @return array<string, mixed>
     */
    private function managerPrefillProps(UserCreateRequest $request): array {
        $props = [];

        $email = $request->string('email')->toString();
        if ($email !== '') {
            $props['prefill_email'] = $email;
        }

        $projectId = $request->integer('project_id');
        if ($projectId > 0) {
            $props['prefill_project_id'] = $projectId;
        }

        return $props;
    }
}
