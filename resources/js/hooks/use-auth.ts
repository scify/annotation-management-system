import { usePage } from '@inertiajs/react';
import { RolesEnum } from '@/types';
import type { AuthUser, ServerPermission, SharedData } from '@/types';

export interface UseAuthReturn {
    user: AuthUser | null;
    isAuthenticated: boolean;
    isAdmin: () => boolean;
    isAnnotationManager: () => boolean;
    isAnnotator: () => boolean;
    can: (permission: ServerPermission) => boolean;
}

export function useAuth(): UseAuthReturn {
    const { auth } = usePage<SharedData>().props;
    const user = auth.user;

    return {
        user,
        isAuthenticated: user !== null,
        isAdmin: () => user?.role === RolesEnum.ADMIN,
        isAnnotationManager: () => user?.role === RolesEnum.ANNOTATION_MANAGER,
        isAnnotator: () => user?.role === RolesEnum.ANNOTATOR,
        can: (permission) => user?.can[permission] ?? false,
    };
}
