import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useTranslations } from '@/hooks/use-translations';
import type { AnnotationTaskMode } from '@/types';
import { router } from '@inertiajs/react';
import { ArrowRightLeftIcon, ListOrderedIcon } from 'lucide-react';

interface AnnotationTaskModeDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    subProjectId: number;
}

/**
 * Preview-only chooser shown when an annotator clicks "Resume". It lets the user
 * open either mocked annotation layout (strict / flexible). Once the real flow
 * exists, Resume will skip this and route straight to the mode that matches the
 * subproject's `flexible` flag.
 */
export function AnnotationTaskModeDialog({
    open,
    onOpenChange,
    subProjectId,
}: AnnotationTaskModeDialogProps) {
    const { t } = useTranslations();

    const openMode = (mode: AnnotationTaskMode) => {
        router.visit(route('annotation-tasks.show', { subProject: subProjectId, mode }));
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-md">
                <DialogHeader>
                    <DialogTitle>{t('annotation-task.mode_dialog.title')}</DialogTitle>
                    <DialogDescription>
                        {t('annotation-task.mode_dialog.description')}
                    </DialogDescription>
                </DialogHeader>

                <div className="flex flex-col gap-3">
                    <ModeButton
                        icon={<ListOrderedIcon className="size-5" aria-hidden="true" />}
                        title={t('annotation-task.mode_dialog.strict')}
                        hint={t('annotation-task.mode_dialog.strict_hint')}
                        onClick={() => openMode('strict')}
                    />
                    <ModeButton
                        icon={<ArrowRightLeftIcon className="size-5" aria-hidden="true" />}
                        title={t('annotation-task.mode_dialog.flexible')}
                        hint={t('annotation-task.mode_dialog.flexible_hint')}
                        onClick={() => openMode('flexible')}
                    />
                </div>
            </DialogContent>
        </Dialog>
    );
}

function ModeButton({
    icon,
    title,
    hint,
    onClick,
}: {
    icon: React.ReactNode;
    title: string;
    hint: string;
    onClick: () => void;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            className="hover:border-brand-blue-500 hover:bg-brand-blue-50 focus-visible:outline-brand-blue-700 flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 text-left transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
        >
            <span className="bg-brand-blue-100 text-brand-blue-700 flex size-10 shrink-0 items-center justify-center rounded-lg">
                {icon}
            </span>
            <span className="flex flex-col">
                <span className="text-base font-semibold text-slate-800">{title}</span>
                <span className="text-sm text-slate-500">{hint}</span>
            </span>
        </button>
    );
}
