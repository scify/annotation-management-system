import { InitialsAvatar } from '@/components/ui/initials-avatar';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { SendMessageDialog } from '@/components/send-message-dialog';
import { useAuth } from '@/hooks/use-auth';
import { useTranslations } from '@/hooks/use-translations';
import { RolesEnum } from '@/types';
import { Link } from '@inertiajs/react';
import { Mail, Plus } from 'lucide-react';
import { useState } from 'react';
import { CreateAdminForm } from '../create-admin/create-admin-form';
import { RoleBadge } from '../shared/role-badge';

interface MockAdmin {
    id: number;
    username: string;
    name: string;
    email: string;
}

const MOCK_ADMINS: MockAdmin[] = [
    { id: 1, username: 'akosmo', name: 'Aris Kosmopoulos', email: 'akosmo@scify.org' },
    { id: 2, username: 'NellySav', name: 'Nelly Sav', email: 'nellysav@scify.org' },
];

export function AdminsTab() {
    const { t } = useTranslations();
    const { can } = useAuth();
    const [showCreateForm, setShowCreateForm] = useState(false);
    const [search, setSearch] = useState('');
    const [messageTarget, setMessageTarget] = useState<MockAdmin | null>(null);

    if (showCreateForm) {
        return <CreateAdminForm onCancel={() => setShowCreateForm(false)} />;
    }

    const filtered = MOCK_ADMINS.filter((a) => {
        if (search.trim()) {
            const q = search.toLowerCase();
            if (!a.name.toLowerCase().includes(q) && !a.email.toLowerCase().includes(q)) {
                return false;
            }
        }
        return true;
    });

    return (
        <div className="flex flex-col gap-4">
            <div className="flex items-start justify-between">
                <h2 className="text-xl font-medium text-slate-800">{t('users.tabs.admins')}</h2>
                {can('create_admins') && (
                    <button
                        type="button"
                        onClick={() => setShowCreateForm(true)}
                        className="bg-brand-blue-700 hover:bg-brand-blue-800 focus-visible:ring-brand-blue-700 inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2.5 text-sm font-semibold text-white hover:cursor-pointer focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
                    >
                        {t('users.actions.create_admin')}
                        <Plus className="h-4 w-4" aria-hidden="true" />
                    </button>
                )}
            </div>

            <div>
                <Input
                    type="search"
                    placeholder={t('users.placeholders.search')}
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    className="max-w-[294px] bg-white"
                />
            </div>

            <div className="overflow-hidden rounded-xl">
                <Table>
                    <TableHeader>
                        <TableRow className="bg-brand-blue-100 hover:bg-brand-blue-100 border-b border-slate-300">
                            <TableHead className="pl-4 text-sm font-semibold text-slate-800">
                                {t('users.labels.username_name')}
                            </TableHead>
                            <TableHead className="text-sm font-semibold text-slate-800">
                                {t('users.labels.email')}
                            </TableHead>
                            <TableHead className="text-center text-sm font-semibold text-slate-800">
                                {t('users.labels.role')}
                            </TableHead>
                            <TableHead className="text-center text-sm font-semibold text-slate-800">
                                {t('users.labels.actions')}
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {filtered.length === 0 ? (
                            <TableRow className="bg-white hover:bg-white">
                                <TableCell
                                    colSpan={4}
                                    className="py-10 text-center text-sm text-slate-400"
                                >
                                    No admins found.
                                </TableCell>
                            </TableRow>
                        ) : (
                            filtered.map((admin) => (
                                <TableRow key={admin.id} className="bg-white hover:bg-slate-50">
                                    <TableCell className="pl-4">
                                        <div className="flex items-center gap-3">
                                            <InitialsAvatar
                                                initials={admin.username.charAt(0).toUpperCase()}
                                                variant="admin"
                                            />
                                            <div className="flex flex-col gap-0.5">
                                                <span className="text-sm font-medium text-slate-800">
                                                    {admin.username}
                                                </span>
                                                <span className="text-sm text-slate-400">
                                                    {admin.name}
                                                </span>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell className="text-sm text-slate-800">
                                        {admin.email}
                                    </TableCell>
                                    <TableCell className="text-center">
                                        <RoleBadge role={RolesEnum.ADMIN} />
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex items-center justify-center gap-2">
                                            <Link
                                                href={route('users.show', admin.id)}
                                                className="bg-brand-yellow-300 text-brand-blue-900 hover:bg-brand-yellow-400 focus-visible:ring-brand-yellow-300 inline-flex h-[30px] min-w-[100px] items-center justify-center rounded-lg px-3.5 text-sm font-semibold focus-visible:ring-2 focus-visible:outline-none"
                                            >
                                                {t('users.actions.view')}
                                            </Link>
                                            <button
                                                type="button"
                                                aria-label={`Send message to ${admin.name}`}
                                                onClick={() => setMessageTarget(admin)}
                                                className="bg-brand-blue-50 text-brand-blue-700 hover:bg-brand-blue-100 focus-visible:ring-brand-blue-700 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg hover:cursor-pointer focus-visible:ring-2 focus-visible:outline-none"
                                            >
                                                <Mail className="h-5 w-5" aria-hidden="true" />
                                            </button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
            </div>

            <SendMessageDialog
                open={messageTarget !== null}
                onClose={() => setMessageTarget(null)}
                targetName={messageTarget?.name ?? ''}
                onSend={() => setMessageTarget(null)}
            />
        </div>
    );
}
