import Heading from '@/components/heading';
import { Link } from '@/components/ui/link';
import { Separator } from '@/components/ui/separator';
import { useAuth } from '@/hooks/use-auth';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import { type NavItem } from '@/types';
import { usePage } from '@inertiajs/react';
import { type PropsWithChildren } from 'react';

export default function SettingsLayout({ children }: PropsWithChildren) {
    const { t } = useTranslations();
    const { isAdmin } = useAuth();

    const sidebarNavItems: NavItem[] = [
        {
            title: t('settings.profile.title'),
            href: '/settings/profile',
            icon: null,
        },
        {
            title: t('settings.password.title'),
            href: '/settings/password',
            icon: null,
        },
        // {
        // 	title: t('settings.appearance.title'),
        // 	href: '/settings/appearance',
        // 	icon: null,
        // },
        ...(isAdmin()
            ? [
                  {
                      title: t('settings.annotator_password_policy.title'),
                      href: '/settings/annotator-password-policy',
                      icon: null,
                  },
              ]
            : []),
    ];

    const { url } = usePage();
    const currentPath = url.split('?')[0];

    return (
        <div className="px-4 py-6">
            <Heading title={t('settings.title')} description={t('settings.description')} />

            <div className="bg-card text-card-foreground overflow-hidden rounded-xl border shadow-sm">
                <div className="flex flex-col lg:flex-row">
                    <aside className="bg-muted/40 lg:border-border w-full p-4 lg:w-68 lg:border-r">
                        <nav className="flex flex-col space-y-1">
                            {sidebarNavItems.map((item) => (
                                <Link
                                    key={item.href}
                                    href={item.href}
                                    variant="ghost"
                                    size="sm"
                                    className={cn('w-full justify-start p-0', {
                                        'bg-muted': currentPath === item.href,
                                    })}
                                >
                                    {t(item.title)}
                                </Link>
                            ))}
                        </nav>
                    </aside>

                    <Separator className="lg:hidden" />

                    <div className="flex-1 p-6 lg:p-8">
                        <section className="max-w-xl space-y-12">{children}</section>
                    </div>
                </div>
            </div>
        </div>
    );
}
