import { AnnotationSidebar } from '@/components/annotation/annotation-sidebar';
import { Toaster } from '@/components/ui/sonner';
import { useFlashMessages } from '@/hooks/use-flash-messages';
import { useTranslations } from '@/hooks/use-translations';
import type { AnnotationData, AnnotationMode } from '@/types';
import type { ReactNode } from 'react';

interface AnnotationLayoutProps {
    mode: AnnotationMode;
    data: AnnotationData;
    /** Mode-specific top-bar controls (To Manager, Exit, Show Instances …). */
    headerRight?: ReactNode;
    onSubmitAllPending?: () => void;
    children: ReactNode;
}

/**
 * Standalone layout for the annotation tool. Unlike AppLayout, it renders the
 * custom annotation sidebar (no collapse toggle) instead of AppSidebar, and a
 * minimal top bar with the project/subproject context plus mode-specific
 * controls. Flash messages stay wired for parity with AppLayout.
 */
export default function AnnotationLayout({
    mode,
    data,
    headerRight,
    onSubmitAllPending,
    children,
}: AnnotationLayoutProps) {
    useFlashMessages();
    const { t } = useTranslations();

    return (
        <div className="bg-brand-blue-50 flex h-screen w-full overflow-hidden">
            <AnnotationSidebar mode={mode} data={data} onSubmitAllPending={onSubmitAllPending} />

            <div className="flex min-w-0 flex-1 flex-col overflow-hidden">
                {/* Top bar */}
                <header className="flex flex-wrap items-center justify-between gap-4 px-6 py-4">
                    <div className="flex flex-col gap-1">
                        <span className="text-sm font-medium text-slate-500">
                            {t('annotation.project')}: {data.projectName}
                        </span>
                        <span className="bg-brand-blue-100 flex h-[30px] w-fit items-center rounded-full px-3 text-sm font-semibold text-slate-800">
                            {t('annotation.subproject')}: {data.subProjectName}
                        </span>
                    </div>

                    {headerRight && <div className="flex items-center gap-2">{headerRight}</div>}
                </header>

                {/* Page content */}
                <main className="min-h-0 flex-1 overflow-y-auto px-6 pb-6">{children}</main>
            </div>

            <Toaster />
        </div>
    );
}
