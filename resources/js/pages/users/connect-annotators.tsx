import { FilterableAnnotatorList } from '@/components/annotator/filterable-annotator-list';
import { useTranslations } from '@/hooks/use-translations';
import AppLayout from '@/layouts/app-layout';
import { type AnnotatorSelectOption } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { LoaderCircle, X } from 'lucide-react';

interface Props {
    manager: { id: number; username: string };
    my_annotators: AnnotatorSelectOption[];
    all_annotators?: AnnotatorSelectOption[];
    annotator_ids: number[];
}

export default function ConnectAnnotators({
    manager,
    my_annotators,
    all_annotators,
    annotator_ids,
}: Props) {
    const { t, trans } = useTranslations();
    const form = useForm({ annotator_ids });

    function handleConnect() {
        form.post(route('users.annotators.connect', manager.id));
    }

    return (
        <AppLayout
            breadcrumbs={[
                { title: t('users.title'), href: route('users.index') },
                { title: t('users.tabs.managers'), href: route('users.index') },
                { title: t('users.connect_annotators.title'), href: '#' },
            ]}
        >
            <Head title={t('users.connect_annotators.title')} />
            <div className="flex flex-col gap-6 p-6">
                <h1 className="text-3xl font-light text-slate-800">
                    {t('users.connect_annotators.title')}
                </h1>

                <div className="flex items-start justify-between gap-4">
                    <hgroup>
                        <h2 className="text-base font-semibold text-slate-800">
                            {trans('users.connect_annotators.manager', {
                                username: manager.username,
                            })}
                        </h2>
                        <p className="text-sm font-semibold text-slate-800">
                            {trans('users.select_annotators.selected_count', {
                                count: form.data.annotator_ids.length,
                            })}
                        </p>
                    </hgroup>

                    <div className="flex shrink-0 items-center gap-3">
                        <Link
                            href={route('users.index')}
                            className="focus-visible:ring-brand-yellow-300 bg-brand-yellow-300 text-brand-blue-900 hover:bg-brand-yellow-400 inline-flex h-10 items-center gap-1.5 rounded-lg px-4 text-sm font-semibold hover:cursor-pointer focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
                        >
                            <X className="h-4 w-4" aria-hidden="true" />
                            {t('users.actions.cancel')}
                        </Link>
                        <button
                            type="button"
                            onClick={handleConnect}
                            disabled={form.data.annotator_ids.length < 1 || form.processing}
                            className="focus-visible:ring-brand-blue-700 bg-brand-blue-700 hover:bg-brand-blue-800 inline-flex h-10 items-center gap-1.5 rounded-lg px-4 text-sm font-semibold text-white hover:cursor-pointer focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            {form.processing && (
                                <LoaderCircle className="h-4 w-4 animate-spin" aria-hidden="true" />
                            )}
                            {t('users.actions.connect')}
                        </button>
                    </div>
                </div>

                <FilterableAnnotatorList
                    annotators={all_annotators ?? my_annotators}
                    myAnnotators={my_annotators}
                    selectedAnnotatorIds={form.data.annotator_ids}
                    onSelectionChange={(ids) => form.setData('annotator_ids', ids)}
                    showMineToggle={!!all_annotators?.length}
                />
            </div>
        </AppLayout>
    );
}
