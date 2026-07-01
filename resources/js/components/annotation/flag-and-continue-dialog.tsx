import { ProjectDialog } from '@/components/project/project-dialog';
import { useTranslations } from '@/hooks/use-translations';
import { router } from '@inertiajs/react';
import { FlagIcon, FolderIcon, SendIcon } from 'lucide-react';
import { useState } from 'react';

interface FlagAndContinueDialogProps {
    open: boolean;
    onClose: () => void;
    /** Sub-project the annotation belongs to (route binding). */
    subProjectId: number;
    /** Sub-project name shown in the dialog badge. */
    subProjectName: string;
    /** 1-based index of the instance being flagged. */
    instanceIndex: number;
    /** Active annotation session; required to post (absent only on the initial-view fallback). */
    annotationSessionId?: number;
    /** Active "Show Instances" filter, threaded through the redirect so it is preserved. */
    activeFilter: string;
}

/**
 * "Flag & Continue" dialog on the annotation page. Marks the current instance as
 * flagged and sends a message to the project manager(s). Unlike the "To Manager"
 * dialog (a fire-and-stay JSON mutation), flagging is a redirect-based Inertia
 * mutation: the backend persists the flag, then redirects to `annotation.show`,
 * so the page reloads onto the next instance and surfaces the success flash.
 */
export function FlagAndContinueDialog({
    open,
    onClose,
    subProjectId,
    subProjectName,
    instanceIndex,
    annotationSessionId,
    activeFilter,
}: FlagAndContinueDialogProps) {
    const { t, trans } = useTranslations();
    const [message, setMessage] = useState('');
    const [submitting, setSubmitting] = useState(false);

    const handleSend = () => {
        const body = message.trim();
        if (!body || submitting || annotationSessionId == null) return;

        router.post(
            route('annotation.flag-instance', { subProject: subProjectId }),
            {
                flag_message: body,
                annotator_instance_index: instanceIndex,
                annotation_session_id: annotationSessionId,
                active_filter: activeFilter,
            },
            {
                onStart: () => setSubmitting(true),
                onFinish: () => setSubmitting(false),
                onSuccess: () => {
                    setMessage('');
                    onClose();
                },
            }
        );
    };

    const handleClose = () => {
        setMessage('');
        onClose();
    };

    const description = (
        <span className="flex flex-col gap-4">
            <span className="text-base font-semibold text-slate-800 underline">
                {trans('annotation.flag_and_continue_dialog.instance', { index: instanceIndex })}
            </span>
            <span className="bg-brand-blue-100 inline-flex w-fit items-center gap-2 rounded-lg px-2.5 py-1 text-sm font-medium text-slate-800">
                <FolderIcon className="size-4 shrink-0" aria-hidden="true" />
                {subProjectName}
            </span>
            <span className="text-sm font-medium text-slate-500">
                {t('annotation.flag_and_continue_dialog.description')}
            </span>
        </span>
    );

    return (
        <ProjectDialog
            open={open}
            onClose={handleClose}
            icon={<FlagIcon className="text-red-600" />}
            title={t('annotation.flag_and_continue_dialog.title')}
            description={description}
            cancelLabel={t('annotation.flag_and_continue_dialog.cancel')}
            actionLabel={t('annotation.flag_and_continue_dialog.send')}
            actionIcon={<SendIcon className="size-4" aria-hidden="true" />}
            onAction={handleSend}
            actionStyle="standard"
            actionDisabled={message.trim().length === 0}
            loading={submitting}
        >
            <div className="flex flex-col gap-1.5">
                <label
                    htmlFor="flag-and-continue-textarea"
                    className="px-2.5 text-sm font-semibold text-slate-800"
                >
                    {t('annotation.flag_and_continue_dialog.label')}
                </label>
                <textarea
                    id="flag-and-continue-textarea"
                    name="flag-and-continue-textarea"
                    className="focus:border-brand-blue-500 mb-5 h-[120px] w-full resize-none rounded-lg border border-slate-200 bg-white px-3.5 py-3 text-base text-slate-800 placeholder:text-slate-400 focus:shadow-[0_0_0_3px_#cbd5e1] focus:outline-none"
                    placeholder={t('annotation.flag_and_continue_dialog.placeholder')}
                    value={message}
                    onChange={(e) => setMessage(e.target.value)}
                />
            </div>
        </ProjectDialog>
    );
}
