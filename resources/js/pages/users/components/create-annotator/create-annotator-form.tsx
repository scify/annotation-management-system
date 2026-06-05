import { InitialsAvatar } from '@/components/ui/initials-avatar';
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
import {
    type AnnotatorCreateData,
    type AnnotatorEditUser,
    type AnnotatorPasswordPolicyData,
    RolesEnum,
} from '@/types';
import { Link, useForm } from '@inertiajs/react';
import { Check, Circle, CircleCheck, CircleX, Info, LoaderCircle } from 'lucide-react';

export interface CreateAnnotatorFormData {
    name: string;
    username: string;
    password: string;
    password_confirmation: string;
    status: 'active' | 'inactive' | 'pending';
    manager_ids: number[];
}

interface CreateAnnotatorFormProps {
    annotatorData: AnnotatorCreateData;
    user?: AnnotatorEditUser;
}

interface ManagerRowProps {
    manager: AnnotatorCreateData['all_managers'][number];
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
                    <InitialsAvatar
                        initials={manager.username.charAt(0).toUpperCase()}
                        variant="admin"
                    />
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

function meetsPasswordPolicy(password: string, policy: AnnotatorPasswordPolicyData): boolean {
    if (password.length < policy.min_length) return false;

    switch (policy.composition_mode) {
        case 'letters_only':
            if (!/[a-zA-Z]/.test(password)) return false;
            break;
        case 'letters_and_numbers':
            if (!/[a-zA-Z]/.test(password) || !/[0-9]/.test(password)) return false;
            break;
        case 'letters_numbers_and_symbols':
            if (
                !/[a-zA-Z]/.test(password) ||
                !/[0-9]/.test(password) ||
                !/[^a-zA-Z0-9]/.test(password)
            )
                return false;
            break;
        case 'no_restriction':
        default:
            break;
    }

    if (policy.mixed_case_required) {
        if (!/[a-z]/.test(password) || !/[A-Z]/.test(password)) return false;
    }

    return true;
}

interface PasswordChecklistProps {
    password: string;
    passwordConfirmation: string;
    policy: AnnotatorPasswordPolicyData;
    isEditing: boolean;
}

function PasswordRequirementsChecklist({
    password,
    passwordConfirmation,
    policy,
    isEditing,
}: PasswordChecklistProps) {
    const { t, trans } = useTranslations();

    if (isEditing && password === '') return null;

    const hasTyped = password.length > 0;

    const requirements: { label: string; met: boolean }[] = [
        {
            label: trans('users.validation.password_requirements.min_length', {
                n: policy.min_length,
            }),
            met: password.length >= policy.min_length,
        },
    ];

    if (policy.composition_mode !== 'no_restriction') {
        requirements.push({
            label: t('users.validation.password_requirements.contains_letter'),
            met: /[a-zA-Z]/.test(password),
        });
    }

    if (
        policy.composition_mode === 'letters_and_numbers' ||
        policy.composition_mode === 'letters_numbers_and_symbols'
    ) {
        requirements.push({
            label: t('users.validation.password_requirements.contains_number'),
            met: /[0-9]/.test(password),
        });
    }

    if (policy.composition_mode === 'letters_numbers_and_symbols') {
        requirements.push({
            label: t('users.validation.password_requirements.contains_symbol'),
            met: /[^a-zA-Z0-9]/.test(password),
        });
    }

    if (policy.mixed_case_required) {
        requirements.push({
            label: t('users.validation.password_requirements.mixed_case'),
            met: /[a-z]/.test(password) && /[A-Z]/.test(password),
        });
    }

    if (passwordConfirmation !== '') {
        requirements.push({
            label: t('users.validation.password_requirements.passwords_match'),
            met: password === passwordConfirmation,
        });
    }

    return (
        <div
            role="note"
            className="border-brand-blue-300 bg-brand-blue-50 flex flex-col gap-2 rounded-md border p-4"
        >
            <p className="text-brand-blue-800 text-xs font-semibold tracking-wide uppercase">
                {t('users.validation.password_requirements.title')}
            </p>
            <ul className="flex flex-col gap-1">
                {requirements.map(({ label, met }, i) => (
                    <li key={i} className="flex items-center gap-1.5">
                        {!hasTyped ? (
                            <Circle
                                className="h-3.5 w-3.5 shrink-0 text-slate-400"
                                aria-hidden="true"
                            />
                        ) : met ? (
                            <CircleCheck
                                className="h-3.5 w-3.5 shrink-0 text-green-600"
                                aria-hidden="true"
                            />
                        ) : (
                            <CircleX
                                className="h-3.5 w-3.5 shrink-0 text-red-500"
                                aria-hidden="true"
                            />
                        )}
                        <span
                            className={cn(
                                'text-xs',
                                !hasTyped
                                    ? 'text-slate-500'
                                    : met
                                      ? 'font-medium text-slate-700'
                                      : 'text-red-500'
                            )}
                        >
                            {label}
                        </span>
                    </li>
                ))}
            </ul>
        </div>
    );
}

export function CreateAnnotatorForm({ annotatorData, user }: CreateAnnotatorFormProps) {
    const { t, trans } = useTranslations();
    const isEditing = user !== undefined;

    const form = useForm<CreateAnnotatorFormData>({
        name: user?.name ?? '',
        username: user?.username ?? '',
        password: '',
        password_confirmation: '',
        status: user?.status ?? 'pending',
        manager_ids: user?.manager_ids ?? [],
    });

    function handleChange(updates: Partial<CreateAnnotatorFormData>) {
        form.setData({ ...form.data, ...updates });
    }

    function toggleManager(id: number) {
        const current = form.data.manager_ids;
        form.setData(
            'manager_ids',
            current.includes(id) ? current.filter((m) => m !== id) : [...current, id]
        );
    }

    const policy = annotatorData.password_policy;

    const passwordValid = isEditing
        ? form.data.password === '' ||
          (meetsPasswordPolicy(form.data.password, policy) &&
              form.data.password === form.data.password_confirmation)
        : form.data.password !== '' &&
          form.data.password_confirmation !== '' &&
          meetsPasswordPolicy(form.data.password, policy) &&
          form.data.password === form.data.password_confirmation;

    const isValid =
        form.data.name.trim() !== '' &&
        form.data.username.trim() !== '' &&
        passwordValid &&
        form.data.manager_ids.length >= 1;

    function handleSubmit() {
        form.transform((data) => {
            const payload: Record<string, unknown> = { ...data, type: RolesEnum.ANNOTATOR };
            if (!payload.password) {
                delete payload.password;
                delete payload.password_confirmation;
            }
            return payload;
        });
        if (isEditing) {
            form.put(route('users.update', user.id));
        } else {
            form.post(route('users.store'));
        }
    }

    const title = isEditing
        ? t('users.actions.edit_annotator')
        : t('users.actions.create_annotator');

    return (
        <section aria-label={title} className="flex flex-col gap-6">
            <h1 className="text-3xl font-light text-slate-800">{title}</h1>

            <div className="grid grid-cols-1 gap-10 lg:grid-cols-2">
                <div className="flex flex-col gap-4">
                    <h2 className="text-xl font-medium text-slate-800">
                        {t('users.create_annotator.user_details')}
                    </h2>
                    <div className="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-7">
                        <Field label={t('users.labels.name')} required>
                            <Input
                                type="text"
                                value={form.data.name}
                                onChange={(e) => handleChange({ name: e.target.value })}
                                autoComplete="name"
                                spellCheck={false}
                                required
                            />
                            {form.errors.name && (
                                <p role="alert" className="text-sm font-medium text-red-500">
                                    {form.errors.name}
                                </p>
                            )}
                        </Field>
                        <Field label={t('users.labels.username')} required>
                            <Input
                                type="text"
                                value={form.data.username}
                                onChange={(e) => handleChange({ username: e.target.value })}
                                autoComplete="username"
                                spellCheck={false}
                                required
                            />
                            {form.errors.username && (
                                <p role="alert" className="text-sm font-medium text-red-500">
                                    {form.errors.username}
                                </p>
                            )}
                        </Field>
                        <Field label={t('users.labels.password')} required={!isEditing}>
                            <Input
                                type="password"
                                name="password"
                                value={form.data.password}
                                onChange={(e) => handleChange({ password: e.target.value })}
                                autoComplete="new-password"
                                required={!isEditing}
                            />
                            {isEditing && (
                                <p className="text-xs text-slate-500">
                                    {t('users.validation.password_keep_hint')}
                                </p>
                            )}
                            {form.errors.password && (
                                <p role="alert" className="text-sm font-medium text-red-500">
                                    {form.errors.password}
                                </p>
                            )}
                        </Field>
                        <Field
                            label={t('users.labels.password_confirmation')}
                            required={!isEditing}
                        >
                            <Input
                                type="password"
                                name="password_confirmation"
                                value={form.data.password_confirmation}
                                onChange={(e) =>
                                    handleChange({ password_confirmation: e.target.value })
                                }
                                autoComplete="new-password"
                                required={!isEditing}
                            />
                            {form.errors.password_confirmation && (
                                <p role="alert" className="text-sm font-medium text-red-500">
                                    {form.errors.password_confirmation}
                                </p>
                            )}
                        </Field>
                        <PasswordRequirementsChecklist
                            password={form.data.password}
                            passwordConfirmation={form.data.password_confirmation}
                            policy={policy}
                            isEditing={isEditing}
                        />
                        <Field label={t('users.labels.status')}>
                            {isEditing ? (
                                <Select
                                    aria-label={t('users.labels.status')}
                                    value={form.data.status}
                                    onValueChange={(value) =>
                                        handleChange({
                                            status: value as CreateAnnotatorFormData['status'],
                                        })
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

                <div className="flex flex-col gap-4">
                    <div className="flex flex-col gap-1">
                        <h2 className="text-xl font-medium text-slate-800">
                            {t('users.create_annotator.connect_managers')}
                        </h2>
                        <p className="text-sm font-bold text-slate-800">
                            {trans('users.create_annotator.selected_count', {
                                count: form.data.manager_ids.length,
                            })}
                        </p>
                    </div>
                    <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white p-4">
                        <div className="max-h-[380px] overflow-y-auto">
                            {annotatorData.all_managers.map((manager) => (
                                <ManagerRow
                                    key={manager.id}
                                    manager={manager}
                                    isSelected={form.data.manager_ids.includes(manager.id)}
                                    onToggle={() => toggleManager(manager.id)}
                                />
                            ))}
                        </div>
                    </div>
                    {form.errors.manager_ids && (
                        <p role="alert" className="text-sm font-medium text-red-500">
                            {form.errors.manager_ids}
                        </p>
                    )}
                    <div className="flex items-center justify-end gap-4">
                        {!isValid && (
                            <p role="alert" className="mr-auto text-sm text-slate-500">
                                {form.data.password !== '' &&
                                form.data.password_confirmation !== '' &&
                                form.data.password !== form.data.password_confirmation
                                    ? t('users.validation.password_mismatch')
                                    : form.data.manager_ids.length === 0 &&
                                        form.data.name.trim() !== '' &&
                                        form.data.username.trim() !== '' &&
                                        passwordValid
                                      ? t('users.create_annotator.min_one_required')
                                      : t('users.steps.personal_info_hint')}
                            </p>
                        )}
                        <Link
                            href={route('users.index')}
                            className="bg-brand-yellow-300 text-brand-blue-900 hover:bg-brand-yellow-400 focus-visible:ring-brand-yellow-300 inline-flex h-10 min-w-[100px] items-center justify-center rounded-lg px-3.5 text-sm font-semibold focus-visible:ring-2 focus-visible:outline-none"
                        >
                            {t('users.actions.cancel')}
                        </Link>
                        <button
                            type="button"
                            onClick={handleSubmit}
                            disabled={!isValid || form.processing}
                            className="bg-brand-blue-700 hover:bg-brand-blue-800 focus-visible:ring-brand-blue-700 inline-flex h-10 min-w-[100px] items-center justify-center gap-1.5 rounded-lg px-3.5 text-sm font-semibold text-white hover:cursor-pointer focus-visible:ring-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            {form.processing && (
                                <LoaderCircle className="h-4 w-4 animate-spin" aria-hidden="true" />
                            )}
                            {isEditing
                                ? t('users.actions.update')
                                : t('users.actions.create_simple')}
                        </button>
                    </div>
                </div>
            </div>
        </section>
    );
}
