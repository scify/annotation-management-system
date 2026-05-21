import AppLayout from '@/layouts/app-layout';
import { useAuth } from '@/hooks/use-auth';
import { useTranslations } from '@/hooks/use-translations';
import { type User, RolesEnum } from '@/types';
import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { AdminsTab } from './components/admins-tab';
import { AnnotatorsTab } from './components/annotators-tab';
import { ManagersTab } from './components/managers-tab';
import { UsersTabs, type UserTab } from './components/users-tabs';

interface Props {
    users: User[];
    filters: {
        search: string | null;
    };
}

export default function UsersIndex({ users }: Props) {
    const { t } = useTranslations();
    const { isAnnotationManager } = useAuth();
    const [activeTab, setActiveTab] = useState<UserTab>('managers');

    const counts = {
        admins: users.filter((u) => u.role === RolesEnum.ADMIN).length,
        managers: users.filter((u) => u.role === RolesEnum.ANNOTATION_MANAGER).length,
        annotators: users.filter((u) => u.role === RolesEnum.ANNOTATOR).length,
    };

    return (
        <AppLayout
            breadcrumbs={[
                { title: t('users.title'), href: route('users.index') },
                { title: t(`users.tabs.${activeTab}`), href: '#' },
            ]}
        >
            <Head title={t('users.index_page_title')} />
            <div className="flex flex-col gap-6 p-6">
                <h1 className="text-3xl font-light text-slate-800">{t('users.title')}</h1>
                <UsersTabs activeTab={activeTab} counts={counts} onChange={setActiveTab} />
                <div
                    role="tabpanel"
                    id={`tabpanel-${activeTab}`}
                    aria-labelledby={`tab-${activeTab}`}
                >
                    {activeTab === 'managers' && <ManagersTab />}
                    {activeTab === 'admins' && !isAnnotationManager() && <AdminsTab />}
                    {activeTab === 'annotators' && <AnnotatorsTab />}
                </div>
            </div>
        </AppLayout>
    );
}
