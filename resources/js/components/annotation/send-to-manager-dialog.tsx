import { ProjectDialog } from '@/components/project/project-dialog';
import { useTranslations } from '@/hooks/use-translations';
import { apiFetchWithFlash } from '@/lib/api';
import { FolderIcon, MessageSquareIcon, SendIcon } from 'lucide-react';
import { useState } from 'react';

interface SendToManagerDialogProps {
    open: boolean;
    onClose: () => void;
    /** Sub-project the annotation belongs to (route binding). */
    subProjectId: number;
    /** Sub-project name shown in the dialog badge. */
    subProjectName: string;
    /** 1-based index of the instance this message refers to. */
    instanceIndex: number;
    /** Active annotation session; required to post (absent only on the initial-view fallback). */
    annotationSessionId?: number;
}

/**
 * "To Manager" dialog on the annotation page. Sends a message to the project
 * manager(s) about the current instance without interrupting the annotator's
 * workflow: it posts via `apiFetchWithFlash` (a plain JSON mutation, no
 * navigation), toasts the backend success message, then closes and leaves the
 * annotator on the same instance.
 */
export function SendToManagerDialog({
    open,
    onClose,
    subProjectId,
    subProjectName,
    instanceIndex,
    annotationSessionId,
}: SendToManagerDialogProps) {
    const { t, trans } = useTranslations();
    const [message, setMessage] = useState('');
    const [sending, setSending] = useState(false);

    const handleSend = async () => {
        const body = message.trim();
        if (!body || sending || annotationSessionId == null) return;

        setSending(true);
        try {
            await apiFetchWithFlash(
                route('annotation.send-to-manager', { subProject: subProjectId }),
                {
                    method: 'POST',
                    body: JSON.stringify({
                        message: body,
                        annotator_instance_index: instanceIndex,
                        annotation_session_id: annotationSessionId,
                    }),
                }
            );
            setMessage('');
            onClose();
        } catch {
            // The error was already surfaced as a toast by apiFetchWithFlash.
        } finally {
            setSending(false);
        }
    };

    const handleClose = () => {
        setMessage('');
        onClose();
    };

    const description = (
        <span className="flex flex-col gap-4">
            <span className="text-base font-semibold text-slate-800 underline">
                {trans('annotation.send_to_manager.instance', { index: instanceIndex })}
            </span>
            <span className="bg-brand-blue-100 inline-flex w-fit items-center gap-2 rounded-lg px-2.5 py-1 text-sm font-medium text-slate-800">
                <FolderIcon className="size-4 shrink-0" aria-hidden="true" />
                {subProjectName}
            </span>
            <span className="text-sm font-medium text-slate-500">
                {t('annotation.send_to_manager.description')}
            </span>
        </span>
    );

    return (
        <ProjectDialog
            open={open}
            onClose={handleClose}
            icon={<MessageSquareIcon />}
            title={t('annotation.send_to_manager.title')}
            description={description}
            cancelLabel={t('annotation.send_to_manager.cancel')}
            actionLabel={t('annotation.send_to_manager.send')}
            actionIcon={<SendIcon className="size-4" aria-hidden="true" />}
            onAction={() => void handleSend()}
            actionStyle="standard"
            actionDisabled={message.trim().length === 0}
            loading={sending}
        >
            <div className="flex flex-col gap-1.5">
                <label
                    htmlFor="send-to-manager-textarea"
                    className="px-2.5 text-sm font-semibold text-slate-800"
                >
                    {t('annotation.send_to_manager.label')}
                </label>
                <textarea
                    id="send-to-manager-textarea"
                    name="send-to-manager-textarea"
                    className="focus:border-brand-blue-500 mb-5 h-[120px] w-full resize-none rounded-lg border border-slate-200 bg-white px-3.5 py-3 text-base text-slate-800 placeholder:text-slate-400 focus:shadow-[0_0_0_3px_#cbd5e1] focus:outline-none"
                    placeholder={t('annotation.send_to_manager.placeholder')}
                    value={message}
                    onChange={(e) => setMessage(e.target.value)}
                />
            </div>
        </ProjectDialog>
    );
}
