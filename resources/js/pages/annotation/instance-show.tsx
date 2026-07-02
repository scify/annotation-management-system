import { Select, SelectContent, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Toaster } from '@/components/ui/sonner';
import { useFlashMessages } from '@/hooks/use-flash-messages';
import { useTranslations } from '@/hooks/use-translations';
import { toInstance } from '@/pages/annotation/map-annotation-data';
import type { AnnotationInstanceShowProps } from '@/types';
import { Head } from '@inertiajs/react';
import { CheckIcon } from 'lucide-react';

/**
 * Read-only preview of a single annotation instance, reached from notification
 * quick-links ("Instance#" / "Flagged Instance#"). A stripped-down cousin of
 * `annotation/show`: it renders the instance content only, with no session,
 * navigation, flagging, or working submission — the backend
 * (`AnnotationController@showInstance`) sends nothing to drive those. The answer
 * dropdown and Submit button are shown inert for visual parity with the Figma.
 */
export default function AnnotationInstancePage({
    annotationTaskData,
    projectName,
    subProjectName,
}: AnnotationInstanceShowProps) {
    useFlashMessages();
    const { t, trans } = useTranslations();

    const instance = toInstance(annotationTaskData);
    const word = annotationTaskData.word ?? '';

    // Sidebar description: "Meanings of word X:" followed by the numbered senses
    // (same shape as the full page's sidebar).
    const senses = annotationTaskData.senses ?? [];
    const description =
        senses.length === 0
            ? ''
            : [
                  trans('annotation.meanings_of_word', { word }),
                  ...senses.map((sense, i) => `${i + 1}. ${sense}`),
              ].join('\n');

    return (
        <div className="bg-brand-blue-50 flex h-screen w-full overflow-hidden">
            <Head title={t('annotation.title')} />

            {/* Sidebar — description only (no progress / flagged sections here). */}
            <aside className="from-brand-blue-700 to-brand-blue-950 flex h-screen w-[268px] shrink-0 flex-col gap-6 overflow-y-auto rounded-tr-[20px] rounded-br-[20px] bg-gradient-to-t px-5 py-6 text-white">
                <section className="border-brand-blue-500 flex flex-col gap-2 rounded-xl border px-3 py-6">
                    <h2 className="text-base font-semibold text-white">
                        {t('annotation.description')}
                    </h2>
                    <p className="text-sm whitespace-pre-line text-white/90">{description}</p>
                </section>
            </aside>

            <div className="flex min-w-0 flex-1 flex-col overflow-hidden">
                {/* Top bar */}
                <header className="flex flex-wrap items-center justify-between gap-4 px-6 py-8">
                    <div className="flex flex-col gap-1">
                        <span className="text-sm font-medium text-slate-500">
                            {t('annotation.project')}: {projectName}
                        </span>
                        <span className="bg-brand-blue-100 flex h-[30px] w-fit items-center rounded-full px-3 text-sm font-semibold text-slate-800">
                            {t('annotation.subproject')}: {subProjectName}
                        </span>
                    </div>
                </header>

                {/* Page content */}
                <main className="min-h-0 flex-1 overflow-y-auto px-6 pb-6">
                    <div className="mx-auto flex w-full max-w-7xl flex-col gap-6">
                        {/* Instance number + focus word */}
                        <div className="flex flex-col items-center gap-2">
                            <p className="text-base font-medium text-slate-800">
                                {trans('annotation.instance', { index: instance.index })}
                            </p>
                            <span className="bg-brand-yellow-400 rounded-full px-6 py-2 text-2xl font-bold text-slate-800">
                                {instance.focusWord}
                            </span>
                        </div>

                        {/* Context (two columns). The corpus sentences arrive with the focus word
                        wrapped in <b>…</b>; the backend must emit only safe markup here. */}
                        <div className="mb-12 grid gap-4 md:grid-cols-2">
                            <p
                                className="h-[30vh] overflow-y-auto rounded-xl bg-white p-5 text-sm leading-6 text-slate-600"
                                dangerouslySetInnerHTML={{ __html: instance.leftContext }}
                            />
                            <p
                                className="h-[30vh] overflow-y-auto rounded-xl bg-white p-5 text-sm leading-6 text-slate-600"
                                dangerouslySetInnerHTML={{ __html: instance.rightContext }}
                            />
                        </div>

                        {/* Question + inert answer control (read-only preview). */}
                        <div className="flex flex-col items-center gap-5">
                            <p className="text-brand-blue-700 text-center text-base font-bold">
                                {trans('annotation.same_meaning_question', { word })}
                            </p>
                            <Select
                                isDisabled
                                aria-label={trans('annotation.same_meaning_question', { word })}
                            >
                                <SelectTrigger className="h-11 w-[280px] rounded-lg bg-white text-base">
                                    <SelectValue placeholder={t('annotation.select_an_option')} />
                                </SelectTrigger>
                                <SelectContent />
                            </Select>
                        </div>

                        {/* Submit (disabled — no submission path on the preview). */}
                        <div className="flex justify-center">
                            <button
                                type="button"
                                disabled
                                className="bg-brand-blue-700 focus-visible:outline-brand-blue-700 flex h-11 min-w-[160px] cursor-not-allowed touch-manipulation items-center justify-center gap-1.5 rounded-full px-6 text-base font-semibold text-white opacity-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                            >
                                {t('annotation.submit')}
                                <CheckIcon className="size-4" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </main>
            </div>

            <Toaster />
        </div>
    );
}
