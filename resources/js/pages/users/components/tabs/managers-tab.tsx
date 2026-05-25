import { InitialsAvatar } from '@/components/ui/initials-avatar';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
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
import { cn } from '@/lib/utils';
import { RolesEnum } from '@/types';
import { Link } from '@inertiajs/react';
import { Mail, Plus } from 'lucide-react';
import { useState } from 'react';
import { CreateManagerForm } from '../create-manager/create-manager-form';
import { RoleBadge } from '../shared/role-badge';
import { StatusBadge } from '../shared/status-badge';

interface MockManager {
    id: number;
    username: string;
    name: string;
    email: string;
    deleted_at: string | null;
}

const MOCK_MANAGERS: MockManager[] = [
    {
        id: 1,
        username: 'akosmo',
        name: 'Aris Kosmopoulos',
        email: 'akosmo@scify.org',
        deleted_at: null,
    },
    {
        id: 2,
        username: 'paulisar',
        name: 'Paul Isaris',
        email: 'paulisar@scify.org',
        deleted_at: null,
    },
    {
        id: 3,
        username: 'NellySav',
        name: 'Nelly Savvidou',
        email: 'nellysav@scify.org',
        deleted_at: null,
    },
];

type StatusFilter = 'all' | 'active' | 'inactive';

export function ManagersTab() {
    const { t } = useTranslations();
    const { can } = useAuth();
    const [showCreateForm, setShowCreateForm] = useState(false);
    const [showOnlyMine, setShowOnlyMine] = useState(false);
    const [statusFilter, setStatusFilter] = useState<StatusFilter>('all');
    const [search, setSearch] = useState('');
    const [messageTarget, setMessageTarget] = useState<MockManager | null>(null);

    const filtered = MOCK_MANAGERS.filter((m) => {
        if (statusFilter === 'active' && m.deleted_at !== null) return false;
        if (statusFilter === 'inactive' && m.deleted_at === null) return false;
        if (search.trim()) {
            const q = search.toLowerCase();
            if (!m.name.toLowerCase().includes(q) && !m.email.toLowerCase().includes(q)) {
                return false;
            }
        }
        return true;
    });

    if (showCreateForm) {
        return <CreateManagerForm onCancel={() => setShowCreateForm(false)} />;
    }

    return (
        <div className="flex flex-col gap-4">
            <div className="flex items-start justify-between">
                <div className="flex flex-col gap-2">
                    <h2 className="text-xl font-medium text-slate-800">
                        {t('users.tabs.managers')}
                    </h2>
                    <div className="flex items-center gap-2">
                        <button
                            role="switch"
                            type="button"
                            aria-checked={showOnlyMine}
                            onClick={() => setShowOnlyMine(!showOnlyMine)}
                            className={cn(
                                'focus-visible:ring-brand-blue-700 relative h-6 w-11 shrink-0 cursor-pointer rounded-full transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none',
                                showOnlyMine ? 'bg-brand-blue-700' : 'bg-slate-300'
                            )}
                        >
                            <span
                                className={cn(
                                    'absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow-sm motion-safe:transition-transform motion-safe:duration-200',
                                    showOnlyMine ? 'translate-x-5' : 'translate-x-0'
                                )}
                            />
                        </button>
                        <span className="text-sm font-medium text-slate-800">
                            {t('users.filters.show_only_mine')}
                        </span>
                    </div>
                </div>

                {can('create_managers') && (
                    <button
                        type="button"
                        onClick={() => setShowCreateForm(true)}
                        className="bg-brand-blue-700 hover:bg-brand-blue-800 focus-visible:ring-brand-blue-700 inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2.5 text-sm font-semibold text-white hover:cursor-pointer focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
                    >
                        {t('users.actions.create_manager')}
                        <Plus className="h-4 w-4" aria-hidden="true" />
                    </button>
                )}
            </div>

            <div className="flex items-center justify-between">
                <Input
                    type="search"
                    placeholder={t('users.placeholders.search')}
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    className="max-w-[294px]"
                />
                <Select
                    value={statusFilter}
                    onValueChange={(v) => setStatusFilter(v as StatusFilter)}
                >
                    <SelectTrigger className="w-[215px] bg-white">
                        <SelectValue
                            placeholder={t('users.filters.show_active')}
                            className="hover:cursor-pointer"
                        />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all" className="hover:cursor-pointer">
                            {t('users.filters.show_all')}
                        </SelectItem>
                        <SelectItem value="active" className="hover:cursor-pointer">
                            {t('users.filters.show_only_active')}
                        </SelectItem>
                        <SelectItem value="inactive" className="hover:cursor-pointer">
                            {t('users.filters.show_only_inactive')}
                        </SelectItem>
                    </SelectContent>
                </Select>
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
                                    No managers found.
                                </TableCell>
                            </TableRow>
                        ) : (
                            filtered.map((manager) => (
                                <TableRow key={manager.id} className="bg-white hover:bg-slate-50">
                                    <TableCell className="pl-4">
                                        <div className="flex items-center gap-3">
                                            <InitialsAvatar
                                                initials={manager.username.charAt(0).toUpperCase()}
                                                variant="admin"
                                            />
                                            <div className="flex flex-col gap-0.5">
                                                <span className="text-sm font-medium text-slate-800">
                                                    {manager.username}
                                                </span>
                                                <span className="text-sm text-slate-400">
                                                    {manager.name}
                                                </span>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell className="text-sm text-slate-800">
                                        {manager.email}
                                    </TableCell>
                                    <TableCell className="text-center">
                                        <RoleBadge role={RolesEnum.ANNOTATION_MANAGER} />
                                    </TableCell>
                                    <TableCell className="text-center">
                                        <StatusBadge
                                            status={manager.deleted_at ? 'inactive' : 'active'}
                                        />
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex items-center justify-center gap-2">
                                            <Link
                                                href={route('users.show', manager.id)}
                                                className="bg-brand-yellow-300 text-brand-blue-900 hover:bg-brand-yellow-400 focus-visible:ring-brand-yellow-300 inline-flex h-[30px] min-w-[100px] items-center justify-center rounded-lg px-3.5 text-sm font-semibold focus-visible:ring-2 focus-visible:outline-none"
                                            >
                                                {t('users.actions.view_edit')}
                                            </Link>
                                            <button
                                                type="button"
                                                aria-label={`Send message to ${manager.name}`}
                                                onClick={() => setMessageTarget(manager)}
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
