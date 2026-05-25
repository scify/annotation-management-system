import { useTranslations } from '@/hooks/use-translations';

export function ConnectProjectsStep() {
    const { t } = useTranslations();

    return (
        <div className="flex flex-col gap-4">
            <h2 className="text-xl font-medium text-slate-800">
                {t('users.steps.connect_projects')}
            </h2>
            <div className="flex items-center justify-center rounded-2xl border border-slate-200 bg-white p-14">
                <p className="text-sm text-slate-400">{t('users.steps.coming_soon')}</p>
            </div>
        </div>
    );
}
