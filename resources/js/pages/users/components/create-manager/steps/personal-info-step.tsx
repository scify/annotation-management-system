import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTranslations } from '@/hooks/use-translations';

export interface PersonalInfoData {
    name: string;
    username: string;
    email: string;
    password: string;
    password_confirmation: string;
    status: 'active' | 'inactive';
}

interface PersonalInfoStepProps {
    data: PersonalInfoData;
    onChange: (updates: Partial<PersonalInfoData>) => void;
}

interface FieldProps {
    label: string;
    children: React.ReactNode;
}

function Field({ label, children }: FieldProps) {
    return (
        <div className="flex flex-col gap-1.5">
            <span className="text-sm font-semibold text-slate-800">{label}</span>
            {children}
        </div>
    );
}

export function PersonalInfoStep({ data, onChange }: PersonalInfoStepProps) {
    const { t } = useTranslations();

    return (
        <div className="flex flex-col gap-4">
            <h2 className="text-xl font-medium text-slate-800">{t('users.steps.personal_info')}</h2>
            <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div className="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-7">
                    <Field label={t('users.labels.name')}>
                        <Input
                            type="text"
                            value={data.name}
                            onChange={(e) => onChange({ name: e.target.value })}
                            autoComplete="name"
                            spellCheck={false}
                        />
                    </Field>
                    <Field label={t('users.labels.username')}>
                        <Input
                            type="text"
                            value={data.username}
                            onChange={(e) => onChange({ username: e.target.value })}
                            autoComplete="username"
                            spellCheck={false}
                        />
                    </Field>
                    <Field label={t('users.labels.email')}>
                        <Input
                            type="email"
                            value={data.email}
                            onChange={(e) => onChange({ email: e.target.value })}
                            autoComplete="email"
                            spellCheck={false}
                        />
                    </Field>
                    <Field label={t('users.labels.password')}>
                        <Input
                            type="password"
                            value={data.password}
                            onChange={(e) => onChange({ password: e.target.value })}
                            autoComplete="new-password"
                        />
                    </Field>
                    <Field label={t('users.labels.password_confirmation')}>
                        <Input
                            type="password"
                            value={data.password_confirmation}
                            onChange={(e) => onChange({ password_confirmation: e.target.value })}
                            autoComplete="new-password"
                        />
                    </Field>
                </div>

                <div className="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-7">
                    <Field label={t('users.labels.status')}>
                        <Select
                            value={data.status}
                            onValueChange={(v) =>
                                onChange({ status: v as PersonalInfoData['status'] })
                            }
                        >
                            <SelectTrigger className="bg-white">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active">{t('users.status.active')}</SelectItem>
                                <SelectItem value="inactive">
                                    {t('users.status.inactive')}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </Field>
                </div>
            </div>
        </div>
    );
}
