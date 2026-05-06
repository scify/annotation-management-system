import { ProjectDialog } from '@/components/project/project-dialog';
import { useTranslations } from '@/hooks/use-translations';
import { Mail, Send } from 'lucide-react';
import { useState } from 'react';

interface SendMessageDialogProps {
    open: boolean;
    onClose: () => void;
    /** Display name shown in the dialog description */
    targetName: string;
    /** Called with the message body when the user confirms send */
    onSend: (message: string) => void;
}

export function SendMessageDialog({ open, onClose, targetName, onSend }: SendMessageDialogProps) {
    const { t, trans } = useTranslations();
    const [message, setMessage] = useState('');

    const handleSend = () => {
        onSend(message);
        setMessage('');
        onClose();
    };

    const handleClose = () => {
        setMessage('');
        onClose();
    };

    return (
        <ProjectDialog
            open={open}
            onClose={handleClose}
            icon={<Mail />}
            title={t('common.send_message.title')}
            description={trans('common.send_message.description', { username: targetName })}
            cancelLabel={t('common.send_message.cancel')}
            actionLabel={t('common.send_message.send')}
            actionIcon={<Send className="size-4" aria-hidden="true" />}
            onAction={handleSend}
            actionStyle="standard"
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
