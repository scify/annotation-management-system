import AppLayout from '@/layouts/app-layout';
import { useAuth } from '@/hooks/use-auth';
import { useTranslations } from '@/hooks/use-translations';
import { type User, type UserManagement } from '@/types';
import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { AdminsTab } from './components/tabs/admins-tab';
import { AnnotatorsTab } from './components/tabs/annotators-tab';
import { ManagersTab } from './components/tabs/managers-tab';
import { UsersTabs, type UserTab } from './components/tabs/users-tabs';

interface Props {
    users: User[];
    management: UserManagement;
    filters: {
        search: string | null;
    };
}

export default function UsersIndex({ management }: Props) {
    const { t } = useTranslations();
    const { isAdmin, isAnnotationManager } = useAuth();
    const [activeTab, setActiveTab] = useState<UserTab>(isAdmin() ? 'admins' : 'managers');

    const counts = {
        admins: management.admins?.length ?? 0,
        managers: management.all_managers?.length ?? 0,
        annotators: (management.all_annotators ?? management.my_annotators)?.length ?? 0,
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
                    {activeTab === 'admins' && !isAnnotationManager() && (
                        <AdminsTab admins={management.admins ?? []} />
                    )}
                    {activeTab === 'managers' && (
                        <ManagersTab
                            allManagers={management.all_managers ?? []}
                            myManagers={management.my_managers ?? []}
                        />
                    )}
                    {activeTab === 'annotators' && (
                        <AnnotatorsTab
                            allAnnotators={management.all_annotators}
                            myAnnotators={management.my_annotators ?? []}
                        />
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
