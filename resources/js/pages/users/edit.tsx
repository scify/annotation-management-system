import AppLayout from '@/layouts/app-layout';
import { useTranslations } from '@/hooks/use-translations';
import {
    type AdminEditData,
    type AnnotatorEditData,
    type ManagerEditData,
    RolesEnum,
} from '@/types';
import { Head } from '@inertiajs/react';
import { CreateAdminForm } from './components/create-admin/create-admin-form';
import { CreateAnnotatorForm } from './components/create-annotator/create-annotator-form';
import { CreateManagerForm } from './components/create-manager/create-manager-form';

interface EditUserProps {
    type: RolesEnum;
    admin_data?: AdminEditData;
    manager_data?: ManagerEditData;
    annotator_data?: AnnotatorEditData;
}

export default function Edit({
    type,
    admin_data,
    manager_data,
    annotator_data,
}: Readonly<EditUserProps>) {
    const { t } = useTranslations();

    const breadcrumbTitle = {
        [RolesEnum.ADMIN]: t('users.actions.edit_admin'),
        [RolesEnum.ANNOTATION_MANAGER]: t('users.actions.edit_manager'),
        [RolesEnum.ANNOTATOR]: t('users.actions.edit_annotator'),
    }[type];

    const userId = admin_data?.user.id ?? manager_data?.user.id ?? annotator_data?.user.id ?? 0;

    return (
        <AppLayout
            breadcrumbs={[
                { title: t('users.title'), href: route('users.index') },
                { title: breadcrumbTitle, href: route('users.edit', userId) },
            ]}
        >
            <Head title={breadcrumbTitle} />
            <div className="p-6">
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
        </AppLayout>
    );
}
