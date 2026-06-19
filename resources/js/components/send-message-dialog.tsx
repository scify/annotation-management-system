import { ProjectDialog } from '@/components/project/project-dialog';
import { useTranslations } from '@/hooks/use-translations';
import { ApiError, apiFetch } from '@/lib/api';
import { Mails, Send } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

interface SendMessageDialogProps {
    open: boolean;
    onClose: () => void;
    /** Display name shown in the dialog description */
    targetName: string;
    /** Id of the user the message is delivered to (notifications.send) */
    recipientUserId: number;
    /** Called after a message is successfully sent */
    onSent?: () => void;
}

export function SendMessageDialog({
    open,
    onClose,
    targetName,
    recipientUserId,
    onSent,
}: SendMessageDialogProps) {
    const { t, trans } = useTranslations();
    const [message, setMessage] = useState('');
    const [sending, setSending] = useState(false);

    const handleSend = async () => {
        const body = message.trim();
        if (!body || sending) return;

        setSending(true);
        try {
            await apiFetch(route('notifications.send'), {
                method: 'POST',
                body: JSON.stringify({ recipient_user_id: recipientUserId, body }),
            });
            toast.success(trans('common.send_message.success', { username: targetName }));
            setMessage('');
            onSent?.();
            onClose();
        } catch (error) {
            toast.error(error instanceof ApiError ? error.message : t('common.send_message.error'));
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
            icon={<Mails />}
            title={t('common.send_message.title')}
            description={trans('common.send_message.description', { username: targetName })}
            cancelLabel={t('common.send_message.cancel')}
            actionLabel={t('common.send_message.send')}
            actionIcon={<Send className="size-4" aria-hidden="true" />}
            onAction={() => void handleSend()}
            actionStyle="standard"
            actionDisabled={message.trim().length === 0}
            loading={sending}
        >
            <textarea
                id="send-message-textarea"
                name="send-message-textarea"
                className="focus:border-brand-blue-500 mb-6 h-[120px] w-full resize-none rounded-lg border border-slate-200 bg-white px-3.5 py-3 text-base text-slate-800 placeholder:text-slate-400 focus:shadow-[0_0_0_3px_#cbd5e1] focus:outline-none"
                placeholder={t('common.send_message.placeholder')}
                value={message}
                onChange={(e) => setMessage(e.target.value)}
                aria-label={trans('common.send_message.description', { username: targetName })}
            />
        </ProjectDialog>
    );
}
