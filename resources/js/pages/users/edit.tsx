import { ProjectDialog } from '@/components/project/project-dialog';
import { Button } from '@/components/ui/button';
import { useAuth } from '@/hooks/use-auth';
import { useTranslations } from '@/hooks/use-translations';
import AppLayout from '@/layouts/app-layout';
import {
    type AdminEditData,
    type AnnotatorEditData,
    type ManagerEditData,
    RolesEnum,
} from '@/types';
import { Head, router } from '@inertiajs/react';
import { Trash2, UserMinus } from 'lucide-react';
import { useState } from 'react';
import { CreateAdminForm } from './components/create-admin/create-admin-form';
import { CreateAnnotatorForm } from './components/create-annotator/create-annotator-form';
import { CreateManagerForm } from './components/create-manager/create-manager-form';

interface EditUserProps {
    type: RolesEnum;
    admin_data?: AdminEditData;
    manager_data?: ManagerEditData;
    annotator_data?: AnnotatorEditData;
    can_delete: boolean;
}

export default function Edit({
    type,
    admin_data,
    manager_data,
    annotator_data,
    can_delete,
}: Readonly<EditUserProps>) {
    const { t } = useTranslations();
    const { isAdmin } = useAuth();
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
    const [deleting, setDeleting] = useState(false);

    const breadcrumbTitle = {
        [RolesEnum.ADMIN]: t('users.actions.edit_admin'),
        [RolesEnum.ANNOTATION_MANAGER]: t('users.actions.edit_manager'),
        [RolesEnum.ANNOTATOR]: t('users.actions.edit_annotator'),
    }[type];

    const userId = admin_data?.user.id ?? manager_data?.user.id ?? annotator_data?.user.id ?? 0;

    function handleDelete() {
        setDeleting(true);
        router.delete(route('users.destroy', userId), {
            onFinish: () => {
                setDeleting(false);
                setDeleteDialogOpen(false);
            },
        });
    }

    return (
        <AppLayout
            breadcrumbs={[
                { title: t('users.title'), href: route('users.index') },
                { title: breadcrumbTitle, href: route('users.edit', userId) },
            ]}
        >
            <Head title={breadcrumbTitle} />
            <div className="p-6">
                {isAdmin() && can_delete && (
                    <div className="mb-6 flex justify-end">
                        <Button variant="destructive" onClick={() => setDeleteDialogOpen(true)}>
                            <Trash2 className="size-4" aria-hidden="true" />
                            {t('users.actions.delete')}
                        </Button>
                    </div>
                )}
                {type === RolesEnum.ADMIN && admin_data && (
                    <CreateAdminForm adminData={admin_data} user={admin_data.user} />
                )}
                {type === RolesEnum.ANNOTATION_MANAGER && manager_data && (
                    <CreateManagerForm managerData={manager_data} user={manager_data.user} />
                )}
                {type === RolesEnum.ANNOTATOR && annotator_data && (
                    <CreateAnnotatorForm
                        annotatorData={annotator_data}
                        user={annotator_data.user}
                    />
                )}
            </div>

            <ProjectDialog
                open={deleteDialogOpen}
                onClose={() => setDeleteDialogOpen(false)}
                icon={<UserMinus />}
                title={t('users.delete.title')}
                description={t('users.delete.description')}
                cancelLabel={t('users.actions.cancel')}
                actionLabel={t('users.actions.delete')}
                onAction={handleDelete}
                loading={deleting}
            />
        </AppLayout>
    );
}
