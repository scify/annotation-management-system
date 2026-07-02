import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    subProjectId: number;
    projectName: string;
    subProjectName: string;
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Annotation', href: '#' }];

export default function AnnotationsShow({ subProjectId, projectName, subProjectName }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${subProjectName} — Annotation`} />
            <div className="p-6">
                <p className="text-slate-500">
                    [{projectName} / {subProjectName} / subproject {subProjectId}] — placeholder
                </p>
            </div>
        </AppLayout>
    );
}
