import { ProjectDialog } from '@/components/project/project-dialog';
import { useTranslations } from '@/hooks/use-translations';
import { router } from '@inertiajs/react';
import { CheckCheckIcon, FolderIcon } from 'lucide-react';
import { useState } from 'react';

interface SubmitPendingDialogProps {
    open: boolean;
    onClose: () => void;
    /** Sub-project the pending annotations belong to (route binding). */
    subProjectId: number;
    /** Sub-project name shown in the dialog badge. */
    subProjectName: string;
}

/**
 * "Submit All Pending" confirmation dialog on the annotation page. Finalizes
 * every draft (pending) annotation for the subproject in one shot — an action
 * that cannot be undone — so it is gated behind an explicit confirmation.
 * Posting takes no payload: the backend resolves the annotator's assignment and
 * bulk-flips pending annotations, then redirects to `annotation.show` with the
 * success flash.
 */
export function SubmitPendingDialog({
    open,
    onClose,
    subProjectId,
    subProjectName,
}: SubmitPendingDialogProps) {
    const { t } = useTranslations();
    const [submitting, setSubmitting] = useState(false);

    const handleConfirm = () => {
        if (submitting) return;

        router.post(
            route('annotation.submit-pending', { subProject: subProjectId }),
            {},
            {
                onStart: () => setSubmitting(true),
                onFinish: () => setSubmitting(false),
                onSuccess: () => onClose(),
            }
        );
    };

    const description = (
        <span className="flex flex-col gap-4">
            <span className="bg-brand-blue-100 inline-flex w-fit items-center gap-2 rounded-lg px-2.5 py-1 text-sm font-medium text-slate-800">
                <FolderIcon className="size-4 shrink-0" aria-hidden="true" />
                {subProjectName}
            </span>
            <span className="text-sm font-medium text-slate-500">
                {t('annotation.submit_all_pending_dialog.description')}
            </span>
        </span>
    );

    return (
        <ProjectDialog
            open={open}
            onClose={onClose}
            icon={<CheckCheckIcon />}
            title={t('annotation.submit_all_pending_dialog.title')}
            description={description}
            cancelLabel={t('annotation.submit_all_pending_dialog.cancel')}
            actionLabel={t('annotation.submit_all_pending_dialog.confirm')}
            actionIcon={<CheckCheckIcon className="size-4" aria-hidden="true" />}
            onAction={handleConfirm}
            actionStyle="standard"
            loading={submitting}
        />
    );
}
