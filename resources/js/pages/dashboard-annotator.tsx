import { AnnotatorSubProjectCard } from '@/components/sub-project/annotator-subproject-card';
import { useTranslations } from '@/hooks/use-translations';
import AppLayout from '@/layouts/app-layout';
import type { AnnotatorSubProject, BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    subprojects: AnnotatorSubProject[];
}

export default function DashboardAnnotator({ subprojects }: Props) {
    const { t } = useTranslations();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('dashboard.annotator.title'),
            href: '/dashboard',
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('dashboard.annotator.title')} />
            <div className="flex flex-col gap-6 px-6 py-6">
                <h1 className="text-slate-800">{t('dashboard.annotator.title')}</h1>

                {subprojects.length === 0 ? (
                    <p className="py-10 text-center text-sm text-slate-400">
                        {t('dashboard.annotator.empty')}
                    </p>
                ) : (
                    <div className="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        {subprojects.map((subProject) => (
                            <AnnotatorSubProjectCard key={subProject.id} subProject={subProject} />
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
