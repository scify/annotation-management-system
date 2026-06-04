import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTranslations } from '@/hooks/use-translations';
import { Info } from 'lucide-react';

export interface PersonalInfoData {
    name: string;
    username: string;
    email: string;
    password: string;
    password_confirmation: string;
    status: 'active' | 'inactive' | 'pending';
}

interface PersonalInfoStepProps {
    data: PersonalInfoData;
    onChange: (updates: Partial<PersonalInfoData>) => void;
    errors?: Partial<Record<string, string>>;
    isEditing?: boolean;
}

interface FieldProps {
    label: string;
    required?: boolean;
    children: React.ReactNode;
}

function Field({ label, required, children }: FieldProps) {
    return (
        <div className="flex flex-col gap-1.5">
            <span className="text-sm font-semibold text-slate-800">
                {label}
                {required && (
                    <span aria-hidden="true" className="ml-0.5 text-red-500">
                        *
                    </span>
                )}
            </span>
            {children}
        </div>
    );
}

export function PersonalInfoStep({
    data,
    onChange,
    errors,
    isEditing = false,
}: PersonalInfoStepProps) {
    const { t } = useTranslations();

    return (
        <div className="flex flex-col gap-4">
            <h2 className="text-xl font-medium text-slate-800">{t('users.steps.personal_info')}</h2>
            <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div className="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-7">
                    <Field label={t('users.labels.name')} required>
                        <Input
                            type="text"
                            value={data.name}
                            onChange={(e) => onChange({ name: e.target.value })}
                            autoComplete="name"
                            spellCheck={false}
                            required
                        />
                        {errors?.name && (
                            <p role="alert" className="text-sm font-medium text-red-500">
                                {errors.name}
                            </p>
                        )}
                    </Field>
                    <Field label={t('users.labels.username')} required>
                        <Input
                            type="text"
                            value={data.username}
                            onChange={(e) => onChange({ username: e.target.value })}
                            autoComplete="username"
                            spellCheck={false}
                            required
                        />
                        {errors?.username && (
                            <p role="alert" className="text-sm font-medium text-red-500">
                                {errors.username}
                            </p>
                        )}
                    </Field>
                    <Field label={t('users.labels.email')} required>
                        <Input
                            type="email"
                            value={data.email}
                            onChange={(e) => onChange({ email: e.target.value })}
                            autoComplete="email"
                            spellCheck={false}
                            required
                        />
                        {errors?.email && (
                            <p role="alert" className="text-sm font-medium text-red-500">
                                {errors.email}
                            </p>
                        )}
                    </Field>
                    <Field label={t('users.labels.password')} required={!isEditing}>
                        <Input
                            type="password"
                            name="password"
                            value={data.password}
                            onChange={(e) => onChange({ password: e.target.value })}
                            autoComplete="new-password"
                            required={!isEditing}
                        />
                        <p className="text-xs text-slate-500">
                            {isEditing
                                ? t('users.validation.password_keep_hint')
                                : t('users.validation.password_hint')}
                        </p>
                        {errors?.password && (
                            <p role="alert" className="text-sm font-medium text-red-500">
                                {errors.password}
                            </p>
                        )}
                    </Field>
                    <Field label={t('users.labels.password_confirmation')} required={!isEditing}>
                        <Input
                            type="password"
                            name="password_confirmation"
                            value={data.password_confirmation}
                            onChange={(e) => onChange({ password_confirmation: e.target.value })}
                            autoComplete="new-password"
                            required={!isEditing}
                        />
                        {errors?.password_confirmation && (
                            <p role="alert" className="text-sm font-medium text-red-500">
                                {errors.password_confirmation}
                            </p>
                        )}
                    </Field>
                </div>

                <div className="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-7">
                    <Field label={t('users.labels.status')}>
                        {isEditing ? (
                            <Select
                                aria-label={t('users.labels.status')}
                                value={data.status}
                                onValueChange={(value) =>
                                    onChange({ status: value as PersonalInfoData['status'] })
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="active">
                                        {t('users.status.active')}
                                    </SelectItem>
                                    <SelectItem value="inactive">
                                        {t('users.status.inactive')}
                                    </SelectItem>
                                    <SelectItem value="pending">
                                        {t('users.status.pending')}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        ) : (
                            <>
                                <div className="flex h-10 w-full items-center rounded-md border border-slate-200 bg-white px-2.5 text-base text-slate-500">
                                    {t('users.status.pending')}
                                </div>
                                <div
                                    role="note"
                                    className="border-brand-blue-300 bg-brand-blue-50 flex items-start gap-2 rounded-md border p-4"
                                >
                                    <Info
                                        className="text-brand-blue-700 h-6 w-6 shrink-0"
                                        aria-hidden="true"
                                    />
                                    <p className="text-brand-blue-800 text-sm font-medium">
                                        {t('users.status.pending_note')}
                                    </p>
                                </div>
                            </>
                        )}
                    </Field>
                </div>
            </div>
        </div>
    );
}
