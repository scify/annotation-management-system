import { useAuth } from '@/hooks/use-auth';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';

export type UserTab = 'admins' | 'managers' | 'annotators';

interface UserTabCounts {
    admins: number;
    managers: number;
    annotators: number;
}

interface UsersTabsProps {
    activeTab: UserTab;
    counts: UserTabCounts;
    onChange: (tab: UserTab) => void;
}

const TABS: { id: UserTab; labelKey: string }[] = [
    { id: 'admins', labelKey: 'users.tabs.admins' },
    { id: 'managers', labelKey: 'users.tabs.managers' },
    { id: 'annotators', labelKey: 'users.tabs.annotators' },
];

export function UsersTabs({ activeTab, counts, onChange }: UsersTabsProps) {
    const { t } = useTranslations();
    const { isAnnotationManager } = useAuth();

    const visibleTabs = TABS.filter((tab) => !(tab.id === 'admins' && isAnnotationManager()));

    return (
        <div
            role="tablist"
            aria-label={t('users.title')}
            className="flex h-[50px] items-center divide-x divide-slate-200 overflow-hidden rounded-lg border border-slate-200 bg-white"
        >
            {visibleTabs.map((tab) => {
                const isActive = activeTab === tab.id;
                const count = counts[tab.id];
                return (
                    <button
                        key={tab.id}
                        role="tab"
                        type="button"
                        aria-selected={isActive}
                        aria-controls={`tabpanel-${tab.id}`}
                        id={`tab-${tab.id}`}
                        onClick={() => onChange(tab.id)}
                        className={cn(
                            'focus-visible:ring-brand-blue-700 flex h-full flex-1 items-center justify-center px-3 py-1.5 text-sm transition-colors hover:cursor-pointer focus-visible:ring-2 focus-visible:outline-none focus-visible:ring-inset',
                            isActive
                                ? 'bg-white font-semibold text-slate-800'
                                : 'bg-slate-100 font-medium text-slate-500 hover:bg-slate-200 hover:text-slate-700'
                        )}
                    >
                        {t(tab.labelKey)}
                        {count > 0 && ` (${count})`}
                    </button>
                );
            })}
        </div>
    );
}
