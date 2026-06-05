import AppLayout from '@/layouts/app-layout';
import { useTranslations } from '@/hooks/use-translations';
import {
    type AdminCreateData,
    type AnnotatorCreateData,
    type ManagerCreateData,
    RolesEnum,
} from '@/types';
import { Head } from '@inertiajs/react';
import { CreateAdminForm } from './components/create-admin/create-admin-form';
import { CreateAnnotatorForm } from './components/create-annotator/create-annotator-form';
import { CreateManagerForm } from './components/create-manager/create-manager-form';

interface CreateUserProps {
    type: RolesEnum;
    admin_data?: AdminCreateData;
    manager_data?: ManagerCreateData;
    annotator_data?: AnnotatorCreateData;
}

const EMPTY_ADMIN_DATA: AdminCreateData = {
    all_projects: [],
    my_projects: [],
    all_annotators: [],
    my_annotators: [],
};

const EMPTY_MANAGER_DATA: ManagerCreateData = {
    my_projects: [],
    my_annotators: [],
    annotation_tasks: [],
    all_projects: [],
    all_annotators: [],
};

const EMPTY_ANNOTATOR_DATA: AnnotatorCreateData = {
    all_managers: [],
    password_policy: {
        min_length: 8,
        composition_mode: 'letters_and_numbers',
        mixed_case_required: false,
    },
};

export default function Create({
    type,
    admin_data,
    manager_data,
    annotator_data,
}: Readonly<CreateUserProps>) {
    const { t } = useTranslations();

    const breadcrumbTitle = {
        [RolesEnum.ADMIN]: t('users.actions.create_admin'),
        [RolesEnum.ANNOTATION_MANAGER]: t('users.actions.create_manager'),
        [RolesEnum.ANNOTATOR]: t('users.actions.create_annotator'),
    }[type];

    return (
        <AppLayout
            breadcrumbs={[
                { title: t('users.title'), href: route('users.index') },
                { title: breadcrumbTitle, href: route('users.create', { type }) },
            ]}
        >
            <Head title={breadcrumbTitle} />
            <div className="p-6">
                {type === RolesEnum.ADMIN && (
                    <CreateAdminForm adminData={admin_data ?? EMPTY_ADMIN_DATA} />
                )}
                {type === RolesEnum.ANNOTATION_MANAGER && (
                    <CreateManagerForm managerData={manager_data ?? EMPTY_MANAGER_DATA} />
                )}
                {type === RolesEnum.ANNOTATOR && (
                    <CreateAnnotatorForm annotatorData={annotator_data ?? EMPTY_ANNOTATOR_DATA} />
                )}
            </div>
        </AppLayout>
    );
}
