import { ProjectDialog } from '@/components/project/project-dialog';
import { useTranslations } from '@/hooks/use-translations';
import { ApiError, apiFetch } from '@/lib/api';
import { Megaphone, Send } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

interface MakeAnnouncementDialogProps {
    open: boolean;
    onClose: () => void;
    /** Display name shown in the dialog description (project or subproject name) */
    targetName: string;
    /** Project the announcement targets */
    projectId: number;
    /** Provided only for subproject announcements; omitted for project-level ones */
    subProjectId?: number;
}

export function MakeAnnouncementDialog({
    open,
    onClose,
    targetName,
    projectId,
    subProjectId,
}: MakeAnnouncementDialogProps) {
    const { t, trans } = useTranslations();
    const [message, setMessage] = useState('');
    const [sending, setSending] = useState(false);

    const handleSend = async () => {
        const body = message.trim();
        if (!body || sending) return;

        setSending(true);
        try {
            await apiFetch(route('notifications.send-announcement'), {
                method: 'POST',
                body: JSON.stringify({
                    project_id: projectId,
                    sub_project_id: subProjectId ?? null,
                    body,
                }),
            });
            toast.success(trans('common.make_announcement.success', { name: targetName }));
            setMessage('');
            onClose();
        } catch (error) {
            toast.error(
                error instanceof ApiError ? error.message : t('common.make_announcement.error')
            );
        } finally {
            setSending(false);
        }
    };

    const handleClose = () => {
        setMessage('');
        onClose();
    };

    return (
        <ProjectDialog
            open={open}
            onClose={handleClose}
            icon={<Megaphone />}
            title={t('common.make_announcement.title')}
            description={trans('common.make_announcement.description', { name: targetName })}
            cancelLabel={t('common.make_announcement.cancel')}
            actionLabel={t('common.make_announcement.send')}
            actionIcon={<Send className="size-4" aria-hidden="true" />}
            onAction={() => void handleSend()}
            actionStyle="standard"
            actionDisabled={message.trim().length === 0}
            loading={sending}
        >
            <textarea
                id="make-announcement-textarea"
                name="make-announcement-textarea"
                className="focus:border-brand-blue-500 mb-6 h-[120px] w-full resize-none rounded-lg border border-slate-200 bg-white px-3.5 py-3 text-base text-slate-800 placeholder:text-slate-400 focus:shadow-[0_0_0_3px_#cbd5e1] focus:outline-none"
                placeholder={t('common.make_announcement.placeholder')}
                value={message}
                onChange={(e) => setMessage(e.target.value)}
                aria-label={trans('common.make_announcement.description', { name: targetName })}
            />
        </ProjectDialog>
    );
}
