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
import { type ManagedUser, RolesEnum } from '@/types';
import { Link } from '@inertiajs/react';
import { Mails, Plus } from 'lucide-react';
import { useState } from 'react';
import { RoleBadge } from '../shared/role-badge';
import { StatusBadge } from '../shared/status-badge';

interface AdminsTabProps {
    admins: ManagedUser[];
}

export function AdminsTab({ admins }: AdminsTabProps) {
    const { t } = useTranslations();
    const { can } = useAuth();
    const [search, setSearch] = useState('');
    const [messageTarget, setMessageTarget] = useState<ManagedUser | null>(null);

    const filtered = admins.filter((a) => {
        if (search.trim()) {
            const q = search.toLowerCase();
            if (
                !a.name.toLowerCase().includes(q) &&
                !a.username.toLowerCase().includes(q) &&
                !(a.email ?? '').toLowerCase().includes(q)
            ) {
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
                    <Link
                        href={route('users.create', { type: RolesEnum.ADMIN })}
                        className="bg-brand-blue-700 hover:bg-brand-blue-800 focus-visible:ring-brand-blue-700 inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2.5 text-sm font-semibold text-white focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
                    >
                        {t('users.actions.create_admin')}
                        <Plus className="h-4 w-4" aria-hidden="true" />
                    </Link>
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
                                {t('users.labels.status')}
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
                                    colSpan={5}
                                    className="py-10 text-center text-sm text-slate-400"
                                >
                                    {t('users.empty.admins')}
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
                                    <TableCell className="text-center">
                                        <StatusBadge status={admin.status} />
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex items-center justify-center gap-2">
                                            <Link
                                                href={route('users.edit', admin.id)}
                                                className="bg-brand-yellow-300 text-brand-blue-900 hover:bg-brand-yellow-400 focus-visible:ring-brand-yellow-300 inline-flex h-[30px] min-w-[100px] items-center justify-center rounded-lg px-3.5 text-sm font-semibold focus-visible:ring-2 focus-visible:outline-none"
                                            >
                                                {t('users.actions.view_edit')}
                                            </Link>
                                            <button
                                                type="button"
                                                aria-label={`Send message to ${admin.name}`}
                                                onClick={() => setMessageTarget(admin)}
                                                className="bg-brand-blue-50 text-brand-blue-700 hover:bg-brand-blue-100 focus-visible:ring-brand-blue-700 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg hover:cursor-pointer focus-visible:ring-2 focus-visible:outline-none"
                                            >
                                                <Mails className="h-5 w-5" aria-hidden="true" />
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
                recipientUserId={messageTarget?.id ?? 0}
            />
        </div>
    );
}
