import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';

interface ToggleSwitchProps {
    id: string;
    checked: boolean;
    onChange: (checked: boolean) => void;
    label?: string;
    description?: string;
    /** Accessible label used when no visible `label` text is provided */
    ariaLabel?: string;
}

export function ToggleSwitch({
    id,
    checked,
    onChange,
    label,
    description,
    ariaLabel,
}: Readonly<ToggleSwitchProps>) {
    const { t } = useTranslations();

    return (
        <div className="flex flex-col gap-0.5">
            <label htmlFor={id} className="flex cursor-pointer items-center gap-3">
                {label && (
                    <span className="text-sm font-semibold text-slate-800">{label}</span>
                )}
                <span className="relative inline-flex shrink-0">
                    <input
                        id={id}
                        type="checkbox"
                        role="switch"
                        aria-checked={checked}
                        aria-label={!label ? ariaLabel : undefined}
                        checked={checked}
                        onChange={(e) => onChange(e.target.checked)}
                        className="peer sr-only"
                    />
                    <span
                        aria-hidden="true"
                        className={cn(
                            'flex h-6 w-11 items-center rounded-full border-2 border-transparent transition-colors',
                            'peer-focus-visible:ring-brand-blue-700/30 peer-focus-visible:ring-4',
                            checked ? 'bg-brand-blue-700' : 'bg-slate-200'
                        )}
                    >
                        <span
                            className={cn(
                                'size-4 rounded-full bg-white shadow-sm transition-transform',
                                checked ? 'translate-x-5' : 'translate-x-1'
                            )}
                        />
                    </span>
                </span>
                <span className="text-sm font-medium text-slate-600">
                    {checked
                        ? t('sub-projects.configuration.toggle_on')
                        : t('sub-projects.configuration.toggle_off')}
                </span>
            </label>
            {description && <span className="text-sm text-slate-500">{description}</span>}
        </div>
    );
}
