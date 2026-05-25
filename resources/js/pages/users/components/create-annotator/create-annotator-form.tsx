import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import { Check } from 'lucide-react';
import { useState } from 'react';

interface MockManager {
    id: number;
    username: string;
}

const MOCK_MANAGERS: MockManager[] = [
    { id: 1, username: 'ggiannakopoulos' },
    { id: 2, username: 'akosmo' },
    { id: 3, username: 'alextzoumas' },
    { id: 4, username: 'paulis' },
    { id: 5, username: 'vassilis_giannakopoulos' },
];

export interface CreateAnnotatorFormData {
    name: string;
    username: string;
    password: string;
    status: 'active' | 'inactive';
    manager_ids: number[];
}

interface ManagerRowProps {
    manager: MockManager;
    isSelected: boolean;
    onToggle: () => void;
}

function ManagerRow({ manager, isSelected, onToggle }: ManagerRowProps) {
    return (
        <label
            className={cn(
                'flex cursor-pointer items-center gap-1 select-none',
                isSelected ? 'bg-brand-blue-50' : 'bg-white'
            )}
        >
            <input type="checkbox" className="sr-only" checked={isSelected} onChange={onToggle} />
            <span className="flex size-[30px] shrink-0 items-center justify-center">
                <span
                    className={cn(
                        'flex size-[18px] items-center justify-center rounded-[4px]',
                        isSelected ? 'bg-brand-blue-700' : 'border-2 border-slate-300'
                    )}
                >
                    {isSelected && <Check className="h-3 w-3 text-white" strokeWidth={3} />}
                </span>
            </span>
            <span className="flex flex-1 items-center">
                <span className="flex h-[56px] w-[52px] shrink-0 items-center justify-center border-b border-slate-300">
                    <Avatar className="size-[29px]">
                        <AvatarFallback className="bg-brand-blue-700 text-xs font-semibold text-white">
                            {manager.username.charAt(0).toUpperCase()}
                        </AvatarFallback>
                    </Avatar>
                </span>
                <span className="flex h-[56px] flex-1 items-center border-b border-slate-300 pl-2 text-base font-medium text-slate-800">
                    @{manager.username}
                </span>
            </span>
        </label>
    );
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

interface CreateAnnotatorFormProps {
    onCancel: () => void;
}

export function CreateAnnotatorForm({ onCancel }: CreateAnnotatorFormProps) {
    const { t, trans } = useTranslations();
    const [formData, setFormData] = useState<CreateAnnotatorFormData>({
        name: '',
        username: '',
        password: '',
        status: 'active',
        manager_ids: [],
    });

    function handleChange(updates: Partial<CreateAnnotatorFormData>) {
        setFormData((prev) => ({ ...prev, ...updates }));
    }

    function toggleManager(id: number) {
        setFormData((prev) => ({
            ...prev,
            manager_ids: prev.manager_ids.includes(id)
                ? prev.manager_ids.filter((m) => m !== id)
                : [...prev.manager_ids, id],
        }));
    }

    return (
        <section aria-label={t('users.actions.create_annotator')} className="flex flex-col gap-6">
            <h1 className="text-3xl font-light text-slate-800">
                {t('users.actions.create_annotator')}
            </h1>

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div className="flex flex-col gap-4">
                    <h2 className="text-xl font-medium text-slate-800">
                        {t('users.create_annotator.user_details')}
                    </h2>
                    <div className="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-7">
                        <Field label={t('users.labels.name')}>
                            <Input
                                type="text"
                                value={formData.name}
                                onChange={(e) => handleChange({ name: e.target.value })}
                                autoComplete="name"
                                spellCheck={false}
                            />
                        </Field>
                        <Field label={t('users.labels.username')}>
                            <Input
                                type="text"
                                value={formData.username}
                                onChange={(e) => handleChange({ username: e.target.value })}
                                autoComplete="username"
                                spellCheck={false}
                            />
                        </Field>
                        <Field label={t('users.labels.password')}>
                            <Input
                                type="password"
                                value={formData.password}
                                onChange={(e) => handleChange({ password: e.target.value })}
                                autoComplete="new-password"
                            />
                        </Field>
                        <Field label={t('users.labels.status')}>
                            <Select
                                value={formData.status}
                                onValueChange={(v) =>
                                    handleChange({
                                        status: v as CreateAnnotatorFormData['status'],
                                    })
                                }
                            >
                                <SelectTrigger className="bg-white">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="active">
                                        {t('users.status.active')}
                                    </SelectItem>
                                    <SelectItem value="inactive">
                                        {t('users.status.inactive')}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </Field>
                    </div>
                </div>

                <div className="flex flex-col gap-4">
                    <div className="flex flex-col gap-1">
                        <h2 className="text-xl font-medium text-slate-800">
                            {t('users.create_annotator.connect_managers')}
                        </h2>
                        <p className="text-sm font-bold text-slate-800">
                            {trans('users.create_annotator.selected_count', {
                                count: formData.manager_ids.length,
                            })}
                        </p>
                    </div>
                    <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                        <div className="max-h-[380px] overflow-y-auto">
                            {MOCK_MANAGERS.map((manager) => (
                                <ManagerRow
                                    key={manager.id}
                                    manager={manager}
                                    isSelected={formData.manager_ids.includes(manager.id)}
                                    onToggle={() => toggleManager(manager.id)}
                                />
                            ))}
                        </div>
                    </div>
                    <div className="flex items-center justify-end gap-4">
                        <button
                            type="button"
                            onClick={onCancel}
                            className="bg-brand-yellow-300 text-brand-blue-900 hover:bg-brand-yellow-400 focus-visible:ring-brand-yellow-300 inline-flex h-10 min-w-[100px] items-center justify-center rounded-lg px-3.5 text-sm font-semibold hover:cursor-pointer focus-visible:ring-2 focus-visible:outline-none"
                        >
                            {t('users.actions.cancel')}
                        </button>
                        <button
                            type="button"
                            className="bg-brand-blue-700 hover:bg-brand-blue-800 focus-visible:ring-brand-blue-700 inline-flex h-10 min-w-[100px] items-center justify-center rounded-lg px-3.5 text-sm font-semibold text-white hover:cursor-pointer focus-visible:ring-2 focus-visible:outline-none"
                        >
                            {t('users.actions.create_annotator')}
                        </button>
                    </div>
                </div>
            </div>
        </section>
    );
}
