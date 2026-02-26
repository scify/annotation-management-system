import AppLayout from '@/layouts/app-layout';
import { useTranslations } from '@/hooks/use-translations';
import { User } from '@/types';
import { Head } from '@inertiajs/react';
import { UserForm } from './components/user-form';

interface EditUserProps {
	user: User;
	roles: {
		name: string;
		label: string;
	}[];
}

export default function Edit({ user, roles }: Readonly<EditUserProps>) {
	const { t } = useTranslations();

	return (
		<AppLayout
			breadcrumbs={[
				{ title: t('users.title'), href: route('users.index') },
				{ title: t('users.actions.edit'), href: route('users.edit', user.id) },
			]}
		>
			<Head title={t('users.actions.edit')} />
			<div className="p-6">
				<h1 className="mb-6 text-2xl font-semibold">
					{t('users.actions.edit_big_button')}
				</h1>
				<UserForm user={user} action={route('users.update', user.id)} roles={roles} />
			</div>
		</AppLayout>
	);
}
