import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTranslations } from '@/hooks/use-translations';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { cn } from '@/lib/utils';
import { type AnnotatorPasswordPolicyData, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { type FormEventHandler } from 'react';

interface CompositionModeOption {
    value: string;
    label: string;
}

interface Props {
    policy: AnnotatorPasswordPolicyData;
    composition_modes: CompositionModeOption[];
}

function buildPolicyPreview(
    data: AnnotatorPasswordPolicyData,
    t: (key: string) => string,
    trans: (key: string, params?: Record<string, string | number>) => string
): string {
    const parts: string[] = [
        trans('settings.annotator_password_policy.preview_min_length', { n: data.min_length }),
    ];

    parts.push(t(`settings.annotator_password_policy.composition_modes.${data.composition_mode}`));

    if (data.mixed_case_required) {
        parts.push(t('settings.annotator_password_policy.preview_mixed_case'));
    }

    return parts.join(' · ');
}

export default function AnnotatorPasswordPolicy({ policy, composition_modes }: Props) {
    const { t, trans } = useTranslations();

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: t('settings.annotator_password_policy.title'),
            href: '/settings/annotator-password-policy',
        },
    ];

    const { data, setData, put, errors, processing, recentlySuccessful } =
        useForm<AnnotatorPasswordPolicyData>({
            min_length: policy.min_length,
            composition_mode: policy.composition_mode,
            mixed_case_required: policy.mixed_case_required,
        });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('settings.annotator-password-policy.update'), { preserveScroll: true });
    };

    const preview = buildPolicyPreview(data, t, trans);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('settings.annotator_password_policy.title')} />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title={t('settings.annotator_password_policy.title')}
                        description={t('settings.annotator_password_policy.description')}
                    />

                    <form onSubmit={submit} className="space-y-6">
                        <div className="grid gap-2">
                            <Label htmlFor="min_length">
                                {t('settings.annotator_password_policy.min_length')}
                            </Label>
                            <Input
                                id="min_length"
                                type="number"
                                min={4}
                                max={128}
                                value={data.min_length}
                                onChange={(e) => setData('min_length', Number(e.target.value))}
                                className="w-32"
                            />
                            <InputError message={errors.min_length} />
                        </div>

                        <div className="grid gap-2">
                            <Label id="composition_mode_label" htmlFor="composition_mode">
                                {t('settings.annotator_password_policy.composition_mode')}
                            </Label>
                            <Select
                                aria-labelledby="composition_mode_label"
                                value={data.composition_mode}
                                onValueChange={(value) =>
                                    setData(
                                        'composition_mode',
                                        value as AnnotatorPasswordPolicyData['composition_mode']
                                    )
                                }
                            >
                                <SelectTrigger id="composition_mode" className="w-full max-w-xs">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {composition_modes.map((mode) => (
                                        <SelectItem key={mode.value} value={mode.value}>
                                            {t(
                                                `settings.annotator_password_policy.composition_modes.${mode.value}`
                                            )}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.composition_mode} />
                        </div>

                        <div className="flex items-center gap-3">
                            <input
                                id="mixed_case_required"
                                type="checkbox"
                                checked={data.mixed_case_required}
                                onChange={(e) => setData('mixed_case_required', e.target.checked)}
                                className="h-4 w-4 rounded border-slate-300"
                                role="checkbox"
                                aria-checked={data.mixed_case_required}
                            />
                            <Label htmlFor="mixed_case_required">
                                {t('settings.annotator_password_policy.mixed_case_required')}
                            </Label>
                            <InputError message={errors.mixed_case_required} />
                        </div>

                        <div
                            role="note"
                            aria-label={t('settings.annotator_password_policy.preview_title')}
                            className="rounded-md border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                        >
                            <p className="mb-1 font-semibold">
                                {t('settings.annotator_password_policy.preview_title')}
                            </p>
                            <p>{preview}</p>
                        </div>

                        <div className="flex items-center gap-4">
                            <Button type="submit" disabled={processing}>
                                {t('settings.annotator_password_policy.save_button')}
                            </Button>
                            <p
                                className={cn(
                                    'text-sm text-neutral-600 transition-opacity duration-300 motion-safe:transition-opacity',
                                    recentlySuccessful ? 'opacity-100' : 'opacity-0'
                                )}
                            >
                                {t('settings.annotator_password_policy.saved')}
                            </p>
                        </div>
                    </form>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
