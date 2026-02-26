import AppLayout from '@/layouts/app-layout';
import { useTranslations } from '@/hooks/use-translations';
import { Head } from '@inertiajs/react';
import { UserForm } from './components/user-form';

interface CreateUserProps {
	roles: {
		name: string;
		label: string;
	}[];
}

export default function Create({ roles }: Readonly<CreateUserProps>) {
	const { t } = useTranslations();

	return (
		<AppLayout
			breadcrumbs={[
				{ title: t('users.title'), href: route('users.index') },
				{ title: t('users.actions.new'), href: route('users.create') },
			]}
		>
			<Head title={t('users.actions.new')} />
			<div className="p-6">
				<h1 className="mb-6 text-2xl font-semibold">{t('users.actions.new_big_button')}</h1>
				<UserForm action={route('users.store')} roles={roles} />
			</div>
		</AppLayout>
	);
}
