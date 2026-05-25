import { Avatar, AvatarFallback } from '@/components/ui/avatar';
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
import { CreateAnnotatorForm } from '../create-annotator/create-annotator-form';
import { RoleBadge } from '../shared/role-badge';
import { StatusBadge } from '../shared/status-badge';

interface MockAnnotator {
    id: number;
    username: string;
    name: string;
    email: string;
    status: 'active' | 'inactive';
}

const MOCK_ANNOTATORS: MockAnnotator[] = [
    {
        id: 1,
        username: 'nazpapad',
        name: 'Nazeli Papadaki',
        email: 'nazpapad@scify.org',
        status: 'active',
    },
    {
        id: 2,
        username: 'fpapa',
        name: 'Fotis Papastergiou',
        email: 'fpapa@scify.org',
        status: 'active',
    },
    {
        id: 3,
        username: 'kChalaris',
        name: 'Kostas Chalaris',
        email: 'kchalaris@scify.org',
        status: 'inactive',
    },
    {
        id: 4,
        username: 'NellySav',
        name: 'Nelly Sav',
        email: 'nellysav@scify.org',
        status: 'active',
    },
];

type StatusFilter = 'all' | 'active' | 'inactive';

export function AnnotatorsTab() {
    const { t } = useTranslations();
    const { can } = useAuth();
    const [showCreateForm, setShowCreateForm] = useState(false);
    const [showOnlyMine, setShowOnlyMine] = useState(false);
    const [statusFilter, setStatusFilter] = useState<StatusFilter>('all');
    const [search, setSearch] = useState('');
    const [messageTarget, setMessageTarget] = useState<MockAnnotator | null>(null);

    if (showCreateForm) {
        return <CreateAnnotatorForm onCancel={() => setShowCreateForm(false)} />;
    }

    const filtered = MOCK_ANNOTATORS.filter((a) => {
        if (statusFilter !== 'all' && a.status !== statusFilter) return false;
        if (search.trim()) {
            const q = search.toLowerCase();
            if (
                !a.username.toLowerCase().includes(q) &&
                !a.name.toLowerCase().includes(q) &&
                !a.email.toLowerCase().includes(q)
            ) {
                return false;
            }
        }
        return true;
    });

    return (
        <div className="flex flex-col gap-4">
            <div className="flex items-start justify-between">
                <div className="flex flex-col gap-2">
                    <h2 className="text-xl font-medium text-slate-800">
                        {t('users.tabs.annotators')}
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
                            {t('users.filters.show_only_mine_annotators')}
                        </span>
                    </div>
                </div>

                {can('create_annotators') && (
                    <button
                        type="button"
                        onClick={() => setShowCreateForm(true)}
                        className="bg-brand-blue-700 hover:bg-brand-blue-800 focus-visible:ring-brand-blue-700 inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2.5 text-sm font-semibold text-white hover:cursor-pointer focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
                    >
                        {t('users.actions.create_annotator')}
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
                                {t('users.labels.username')}
                            </TableHead>
                            <TableHead className="text-sm font-semibold text-slate-800">
                                {t('users.labels.name')}
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
                                    No annotators found.
                                </TableCell>
                            </TableRow>
                        ) : (
                            filtered.map((annotator) => (
                                <TableRow key={annotator.id} className="bg-white hover:bg-slate-50">
                                    <TableCell className="pl-4">
                                        <div className="flex items-center gap-3">
                                            <Avatar className="size-[29px] shrink-0">
                                                <AvatarFallback className="bg-brand-blue-700 text-xs font-semibold text-white">
                                                    {annotator.username.charAt(0).toUpperCase()}
                                                </AvatarFallback>
                                            </Avatar>
                                            <span className="text-sm font-medium text-slate-800">
                                                @{annotator.username}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell className="text-sm text-slate-800">
                                        {annotator.name}
                                    </TableCell>
                                    <TableCell className="text-center">
                                        <RoleBadge role={RolesEnum.ANNOTATOR} />
                                    </TableCell>
                                    <TableCell className="text-center">
                                        <StatusBadge status={annotator.status} />
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex items-center justify-center gap-2">
                                            <Link
                                                href={route('users.show', annotator.id)}
                                                className="bg-brand-yellow-300 text-brand-blue-900 hover:bg-brand-yellow-400 focus-visible:ring-brand-yellow-300 inline-flex h-[30px] min-w-[100px] items-center justify-center rounded-lg px-3.5 text-sm font-semibold focus-visible:ring-2 focus-visible:outline-none"
                                            >
                                                {t('users.actions.view_edit')}
                                            </Link>
                                            <button
                                                type="button"
                                                aria-label={`Send message to ${annotator.name}`}
                                                onClick={() => setMessageTarget(annotator)}
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
